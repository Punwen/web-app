<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
check(is_file(dirname(__DIR__).'/app/database.php'), 'repository implementation exists');
require dirname(__DIR__).'/app/database.php';
$db=connect_database(test_config());
$db->exec(file_get_contents(dirname(__DIR__).'/database/schema.sql'));
$db->beginTransaction();
try {
$db->exec('DELETE FROM saves');
$r=new SaveRepository($db);
$a=$r->create(fixture()); check($r->find($a)['title']==='閱讀 📖', 'Unicode persists in MySQL');
$b=$r->create(fixture(['status'=>'paused','title'=>'Paused']));
$c=$r->create(fixture(['status'=>'completed','title'=>'Done']));
check((int)$r->latestActive()['id']===$a, 'resume excludes newer paused and completed');
check($r->counts()===['all'=>3,'active'=>1,'paused'=>1,'completed'=>1], 'status counts exact');
check($r->update($b,1,fixture(['title'=>'Resume','category'=>'game'])), 'resume paused activity');
check((int)$r->latestActive()['id']===$b, 'resumed activity becomes latest');
check(!$r->update($b,1,fixture(['title'=>'Stale'])), 'stale edit rejected');
check($r->find($b)['title']==='Resume', 'stale edit leaves latest data');
check(!$r->delete($b,1), 'stale delete rejected');
check($r->listing('active','game',1)['total']===1, 'combined filters apply');
check($r->listing('paused','other',1)['total']===0, 'empty category');
for($i=0;$i<12;$i++) $r->create(fixture(['title'=>'Page '.$i]));
check(count($r->listing('all','all',1)['items'])===12, 'first page limited to12');
check(count($r->listing('all','all',2)['items'])===3, 'second page remainder');
$db->exec("UPDATE saves SET updated_at='2026-01-01 00:00:00.000000'");
check((int)$r->latestActive()['id']===($c+12), 'equal timestamp resolves by highest ID');
check($r->delete($b,2), 'current revision delete succeeds');
check($r->find($b)===null, 'deleted record absent');
check($r->find(999999)===null, 'missing record null');
$db->exec("UPDATE saves SET status='completed'");
check($r->latestActive()===null, 'no-active state');
} finally {$db->rollBack();}
done('repository');
