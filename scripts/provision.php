<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') {http_response_code(404); exit;}
function persist_local_config(string $file,array $value): void {
    $data="<?php\nreturn ".var_export($value,true).";\n";
    $temporary=$file.'.tmp';
    if(@file_put_contents($temporary,$data,LOCK_EX)!==strlen($data)) {
        @unlink($temporary);
        throw new RuntimeException('Cannot completely save local configuration; account changes stopped.');
    }
    if(!@rename($temporary,$file)) throw new RuntimeException('Cannot finalize local configuration; account changes stopped.');
}
try {
    $root=dirname(__DIR__);
    $adminFile=$root.'/.local/admin.php';
    $old=is_file($adminFile)?require $adminFile:null;
    $adminPassword=$old['password']??'';
    $db=null;$needsPassword=false;
    for($attempt=0;$attempt<30;$attempt++) {
        try {
            $db=new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4','root',$adminPassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            $needsPassword=!$old;break;
        } catch(PDOException $e) {
            // A previous start may have saved the password but stopped before ALTER USER.
            if($old&&(int)$e->getCode()===28000) {
                try {$db=new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$needsPassword=true;break;}
                catch(PDOException $ignored){}
            }
            if($attempt===29) throw new RuntimeException('Local MySQL is not ready. Check .local/mysql-error.log.');
            usleep(300000);
        }
    }
    $actual=strtolower(rtrim(str_replace('\\','/',(string)$db->query('SELECT @@datadir')->fetchColumn()),'/'));
    $expected=strtolower(str_replace('\\','/',$root.'/.local/mysql'));
    if($actual!==$expected) throw new RuntimeException('Database directory differs; provisioning refused.');
    if($needsPassword) {
        if(!$old) {
            $adminPassword=bin2hex(random_bytes(24));
            persist_local_config($adminFile,['password'=>$adminPassword]);
        }
        $db->exec('ALTER USER CURRENT_USER() IDENTIFIED BY '.$db->quote($adminPassword));
    }
    $configFile=$root.'/config.local.php';
    $config=is_file($configFile)?require $configFile:['host'=>'127.0.0.1','port'=>33079,'database'=>'restart','username'=>'restart_app','password'=>bin2hex(random_bytes(24)),'timezone'=>'Asia/Tokyo'];
    if($config['host']!=='127.0.0.1'||(int)$config['port']!==33079||$config['database']!=='restart'||$config['username']!=='restart_app') throw new RuntimeException('Existing config is for another environment; it was not changed.');
    if(!is_file($configFile)) persist_local_config($configFile,$config);
    foreach(['restart','restart_test'] as $database) $db->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("CREATE USER IF NOT EXISTS 'restart_app'@'localhost' IDENTIFIED BY ".$db->quote($config['password']));
    foreach(['restart','restart_test'] as $database) {
        $db->exec("GRANT SELECT,INSERT,UPDATE,DELETE,CREATE ON `$database`.* TO 'restart_app'@'localhost'");
        $db->exec("USE `$database`");
        $db->exec(file_get_contents($root.'/database/schema.sql'));
    }
    $test=$config;$test['database']='restart_test';
    persist_local_config($root.'/.local/test-config.php',$test);
    echo "ReStart databases ready; credentials saved locally, not printed.\n";
} catch(Throwable $e) {
    fwrite(STDERR,$e instanceof PDOException?"Local database provisioning failed; credentials were not printed.\n":$e->getMessage()."\n");exit(1);
}
