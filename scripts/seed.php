<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(($argv[1]??'')!=='--confirm'){fwrite(STDERR,"Use --confirm to import demonstration saves into an empty ReStart database.\n");exit(1);}
require dirname(__DIR__).'/app/database.php';
try {
$config=require (getenv('RESTART_CONFIG')?:dirname(__DIR__).'/config.local.php');$db=connect_database($config);
if((int)$db->query('SELECT COUNT(*) FROM saves')->fetchColumn()!==0)throw new RuntimeException('Database is not empty; no examples were added.');
$db->exec(file_get_contents(dirname(__DIR__).'/database/demo.sql'));echo "Demonstration saves imported.\n";
}catch(Throwable $e){fwrite(STDERR,$e instanceof PDOException?"Database operation failed.\n":$e->getMessage()."\n");exit(1);}
