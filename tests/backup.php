<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/app/database.php';
check(is_file(dirname(__DIR__).'/app/backup.php'),'backup implementation exists');
require dirname(__DIR__).'/app/backup.php';
$db=connect_database(test_config());$repo=new SaveRepository($db);
$db->exec('DELETE FROM saves');$id=$repo->create(fixture());
$backup=export_backup($db);$repo->create(fixture(['title'=>'Later']));
check(restore_backup($db,$backup)===1,'restore count');
check($repo->find($id)['title']==='閱讀 📖','restore preserves original ID and Unicode');
check($repo->counts()['all']===1,'restore replaces only target data');
$bad=json_decode($backup,true);$bad['saves'][]=$bad['saves'][0];
try {restore_backup($db,json_encode($bad));check(false,'duplicate IDs rejected');}catch(InvalidArgumentException $e) {check($repo->counts()['all']===1,'invalid backup cannot delete current data');}
try {restore_backup($db,'{"saves":[]}');check(false,'wrong backup format rejected');}catch(InvalidArgumentException $e) {check(true,'format validated');}
$bad=json_decode($backup,true);$bad['saves'][0]['status']='invalid';
try {restore_backup($db,json_encode($bad));check(false,'invalid status rejected');}catch(InvalidArgumentException $e) {check($repo->find($id)!==null,'invalid record leaves current data intact');}
$db->exec('DELETE FROM saves');done('backup');
