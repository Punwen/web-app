<?php
// Fault injection subprocess: no real provisioning or credential writes.
namespace Probe;
use Throwable;use RuntimeException;use PDOException;
$mode=$argv[1]??'';
$root=dirname(__DIR__);
if($mode==='backup') {
    putenv('RESTART_CONFIG='.$root.'/.local/test-config.php');
    $argv=['backup.php','restore',$argv[2],'--confirm'];
    function file_put_contents($path,$data,$flags=0){return 1;}
    eval(str_replace('declare(strict_types=1);','declare(strict_types=1); namespace Probe; use \Throwable; use \RuntimeException; use \PDOException;',substr(\file_get_contents($root.'/scripts/backup.php'),5)));
} else {
    function is_file($path){if(getenv('RESTART_FAIL_WRITE')!=='recover'&&(str_ends_with($path,'admin.php')||str_ends_with($path,'config.local.php')))return false;return \is_file($path);}
    function file_put_contents($path,$data,$flags=0){
        $fail=getenv('RESTART_FAIL_WRITE');
        return str_contains($path,$fail)?1:strlen($data);
    }
    function rename($from,$to){return true;}
    function unlink($path){return true;}
    class PDO {
        const ATTR_ERRMODE=3;const ERRMODE_EXCEPTION=2;
        public function __construct(...$args){static $calls=0; if(getenv('RESTART_FAIL_WRITE')==='recover'&&$calls++===0)throw new PDOException('Injected pre-ALTER interruption',28000);}
        public function quote($text){return "'redacted-test-password'";}
        public function exec($sql){if(str_starts_with($sql,'ALTER USER')||str_starts_with($sql,'CREATE USER')) \file_put_contents(getenv('RESTART_PROBE_LOG'),strtok($sql,' ')."\n",FILE_APPEND);return 0;}
        public function query($sql){return new class{public function fetchColumn(){return dirname(__DIR__).'/.local/mysql/';}};}
    }
    eval(str_replace('declare(strict_types=1);','declare(strict_types=1); namespace Probe; use \Throwable; use \RuntimeException; use \PDOException;',substr(\file_get_contents($root.'/scripts/provision.php'),5)));
}
