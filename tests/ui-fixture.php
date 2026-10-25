<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
require dirname(__DIR__).'/app/database.php';
$db=connect_database(test_config());
$db->exec('DELETE FROM saves');
if(($argv[1]??'')==='populate') {
    $repo=new SaveRepository($db);
    for($i=1;$i<=13;$i++)$repo->create(fixture(['title'=>'Pagination '.$i]));
    $repo->create(fixture(['title'=>'Paused game','category'=>'game','status'=>'paused']));
}
