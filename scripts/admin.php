<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root=dirname(__DIR__);$action=$argv[1]??'verify';
try {
    $admin=require $root.'/.local/admin.php';
    $db=new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4','root',$admin['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_TIMEOUT=>2]);
    $actual=str_replace('\\','/',(string)$db->query('SELECT @@datadir')->fetchColumn());
    $expected=str_replace('\\','/',realpath($root.'/.local/mysql')).'/';
    if(strcasecmp(rtrim($actual,'/'),rtrim($expected,'/'))!==0)throw new RuntimeException('Database does not belong to this ReStart project.');
    if($action==='shutdown') {$db->exec('SHUTDOWN');echo "ReStart database stopped.\n";}
    elseif($action==='verify')echo "ReStart database verified.\n";
    else throw new RuntimeException('Unknown administration operation.');
}catch(Throwable $e){fwrite(STDERR,"Could not verify this project's database service.\n");exit(1);}
