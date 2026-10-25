<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') {http_response_code(404); exit;}
$root=dirname(__DIR__);
$adminFile=$root.'/.local/admin.php';
$old=is_file($adminFile)?require $adminFile:null;
$adminPassword=$old['password']??'';
$db=null;
for($attempt=0;$attempt<30;$attempt++) {
    try {$db=new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4','root',$adminPassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);break;}
    catch(PDOException $e) {if($attempt===29) {fwrite(STDERR,"Local MySQL is not ready. Check .local/mysql-error.log.\n");exit(1);} usleep(300000);}
}
if (!$old) {
    $adminPassword=bin2hex(random_bytes(24));
    file_put_contents($adminFile,"<?php\nreturn ".var_export(['password'=>$adminPassword],true).";\n");
    $db->exec('ALTER USER CURRENT_USER() IDENTIFIED BY '.$db->quote($adminPassword));
}
foreach(['restart','restart_test'] as $database) $db->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$configFile=$root.'/config.local.php';
$config=is_file($configFile)?require $configFile:['host'=>'127.0.0.1','port'=>33079,'database'=>'restart','username'=>'restart_app','password'=>bin2hex(random_bytes(24)),'timezone'=>'Asia/Tokyo'];
if($config['host']!=='127.0.0.1'||(int)$config['port']!==33079||$config['database']!=='restart'||$config['username']!=='restart_app') {fwrite(STDERR,"Existing config is for another environment; it was not changed.\n");exit(1);}
$db->exec("CREATE USER IF NOT EXISTS 'restart_app'@'localhost' IDENTIFIED BY ".$db->quote($config['password']));
foreach(['restart','restart_test'] as $database) {
    $db->exec("GRANT SELECT,INSERT,UPDATE,DELETE,CREATE ON `$database`.* TO 'restart_app'@'localhost'");
    $db->exec("USE `$database`");
    $db->exec(file_get_contents($root.'/database/schema.sql'));
}
if(!is_file($configFile)) file_put_contents($configFile,"<?php\nreturn ".var_export($config,true).";\n");
$test=$config; $test['database']='restart_test';
file_put_contents($root.'/.local/test-config.php',"<?php\nreturn ".var_export($test,true).";\n");
echo "ReStart databases ready; credentials saved locally, not printed.\n";
