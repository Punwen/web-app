<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/app/database.php';require dirname(__DIR__).'/app/backup.php';
function probe(array $args): int {
 $p=proc_open(array_merge([PHP_BINARY,__DIR__.'/io-probe.php'],$args),[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
 fclose($pipes[0]);stream_get_contents($pipes[1]);fclose($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[2]);return proc_close($p);
}
$mode=$argv[1]??'backup';$root=dirname(__DIR__);
if($mode==='backup'){
 $db=connect_database(test_config());$db->exec('DELETE FROM saves');$repo=new SaveRepository($db);$id=$repo->create(fixture(['title'=>'Must survive failed recovery backup']));
 $file=$root.'/.local/io-empty-test.json';file_put_contents($file,json_encode(['format'=>'restart-backup','version'=>1,'saves'=>[]]));
 try{$exit=probe(['backup',$file]);check($exit!==0&&$repo->find($id)!==null,'partial automatic backup must abort before restore');done('backup write failure');}finally{$db->exec('DELETE FROM saves');unlink($file);}
}else{
 $log=$root.'/.local/io-provision-test.log';file_put_contents($log,'');putenv('RESTART_PROBE_LOG='.$log);putenv('RESTART_FAIL_WRITE='.$mode);
 $exit=probe(['provision']);$events=file_get_contents($log);unlink($log);
 if($mode==='recover'){check($exit===0&&str_contains($events,'ALTER'),'saved password recovers interruption before ALTER');done('interrupted provisioning recovery');exit;}
 check($exit!==0,'credential write failure must fail provisioning');
 check($mode==='admin.php'?!str_contains($events,'ALTER'):!str_contains($events,'CREATE'),'credentials must be persisted before account change');done('credential write failure '.$mode);
}
