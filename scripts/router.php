<?php
$root=dirname(__DIR__);
$instanceFile=$root.'/.local/web-instance';
if(is_file($instanceFile)) header('X-ReStart-Instance: '.trim(file_get_contents($instanceFile)));
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if($path==='/'||$path==='/index.php') {require $root.'/index.php';return true;}
$resolved=realpath($root.$path);$assets=realpath($root.'/assets').DIRECTORY_SEPARATOR;
if($resolved&&str_starts_with($resolved,$assets)&&is_file($resolved)&&preg_match('/\.(css|svg|webp|png|jpg|jpeg|ico|woff2?)$/i',$resolved))return false;
http_response_code(404);header('Content-Type: text/plain; charset=utf-8');echo 'Not found';return true;
