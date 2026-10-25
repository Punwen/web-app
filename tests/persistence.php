<?php
require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/app/database.php';
$db=connect_database(test_config());$r=new SaveRepository($db);$mode=$argv[1]??'';$file=dirname(__DIR__).'/.local/persistence-id';
if($mode==='write'){file_put_contents($file,(string)$r->create(fixture(['title'=>'Restart persistence test'])));echo "Persistence fixture stored.\n";}
elseif($mode==='read'){check($r->find((int)file_get_contents($file))['title']==='Restart persistence test','saved record survives full service restart');done('restart persistence');}
elseif($mode==='cleanup'){$row=$r->find((int)file_get_contents($file));if($row)$r->delete((int)$row['id'],(int)$row['version']);echo "Persistence fixture removed.\n";}
