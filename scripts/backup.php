<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/database.php';require dirname(__DIR__).'/app/backup.php';
$command=$argv[1]??'';$file=$argv[2]??'';
if(!in_array($command,['export','restore'],true)||$file==='') {fwrite(STDERR,"Usage: php scripts/backup.php export FILE.json | restore FILE.json --confirm\n");exit(1);}
try {
    $config=require (getenv('RESTART_CONFIG')?:dirname(__DIR__).'/config.local.php');$db=connect_database($config);
    if($command==='export') {
        $handle=@fopen($file,'x');if(!$handle)throw new RuntimeException('Choose a new writable backup filename; existing files are not overwritten.');
        $data=export_backup($db);$written=fwrite($handle,$data);fclose($handle);if($written!==strlen($data))throw new RuntimeException('Backup could not be fully written.');
        echo "Backup exported. Keep it outside Git.\n";
    } else {
        if(($argv[3]??'')!=='--confirm')throw new RuntimeException('Restore replaces this ReStart database. Add --confirm after checking the target and backup.');
        if(!is_file($file)||filesize($file)>64*1024*1024)throw new RuntimeException('Backup is missing or exceeds 64 MB.');
        $before=dirname(__DIR__).'/.local/before-restore-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json';
        if(file_put_contents($before,export_backup($db),LOCK_EX)===false)throw new RuntimeException('Cannot save the automatic before-restore backup.');
        $count=restore_backup($db,(string)file_get_contents($file));
        echo "Restored $count saves. Previous data backup: ".basename($before)." in .local\n";
    }
}catch(Throwable $e){fwrite(STDERR,$e instanceof PDOException?"Database operation failed; check local service and configuration.\n":$e->getMessage()."\n");exit(1);}
