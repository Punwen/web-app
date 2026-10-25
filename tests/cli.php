<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/app/database.php';
$db=connect_database(test_config());$db->exec('DELETE FROM saves');
putenv('RESTART_CONFIG='.dirname(__DIR__).'/.local/test-config.php');
$file=dirname(__DIR__).'/.local/cli-test-'.bin2hex(random_bytes(6)).'.json';
function cli(array $args): int {
    $process=proc_open(array_merge([PHP_BINARY],$args),[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,dirname(__DIR__));
    fclose($pipes[0]);stream_get_contents($pipes[1]);fclose($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[2]);return proc_close($process);
}
try {
 check(cli(['scripts/seed.php'])===1,'seed requires confirmation');
 check(cli(['scripts/seed.php','--confirm'])===0,'seed empty database');
 check((int)$db->query('SELECT COUNT(*) FROM saves')->fetchColumn()===2,'two examples');
 check(cli(['scripts/seed.php','--confirm'])===1,'seed refuses nonempty database');
 check(cli(['scripts/backup.php','export',$file])===0,'export CLI');
 $before=file_get_contents($file);
 check(cli(['scripts/backup.php','export',$file])===1&&file_get_contents($file)===$before,'existing backup preserved');
 $db->exec('DELETE FROM saves');
 check(cli(['scripts/backup.php','restore',$file])===1&&(int)$db->query('SELECT COUNT(*) FROM saves')->fetchColumn()===0,'restore requires confirmation');
 $backups=count(glob(dirname(__DIR__).'/.local/before-restore-*.json'));
 check(cli(['scripts/backup.php','restore',$file,'--confirm'])===0,'restore CLI');
 check((int)$db->query('SELECT COUNT(*) FROM saves')->fetchColumn()===2,'restored examples');
 check(count(glob(dirname(__DIR__).'/.local/before-restore-*.json'))===$backups+1,'automatic backup created');
 done('CLI backup and optional samples');
}finally{$db->exec('DELETE FROM saves');if(is_file($file))unlink($file);}
