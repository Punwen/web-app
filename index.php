<?php
declare(strict_types=1);
require __DIR__.'/app/database.php';
require __DIR__.'/app/web.php';
ini_set('display_errors','0');
session_start(['use_strict_mode'=>true,'cookie_httponly'=>true,'cookie_samesite'=>'Lax','cookie_secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);
header("Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self'; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store');
$lang='zh-Hant';$timezone='Asia/Tokyo';$view='list';$status='all';$category='all';$page=1;
$counts=['all'=>0,'active'=>0,'paused'=>0,'completed'=>0];$record=null;$latest=null;$message='';$errors=[];$id=null;
$values=['title'=>'','category'=>'game','status'=>'active','progress'=>'','next_step'=>'','memo'=>''];
$version=1;$listing=['items'=>[],'total'=>0,'page'=>1,'pages'=>1];
try {
    $candidate=scalar_value($_GET,'lang','zh-Hant');
    if(in_array($candidate,['zh-Hant','ja','en'],true)) $lang=$candidate;
    $view=scalar_value($_GET,'view','list');
    if(!in_array($view,['list','new','detail','edit','delete','help'],true)) throw new HttpError(404,'not_found');
    $method=$_SERVER['REQUEST_METHOD'];
    if(!in_array($method,['GET','POST'],true)) {header('Allow: GET, POST');throw new HttpError(405,'method_error');}
    if($method==='POST') {
        $action=scalar_value($_POST,'action');
        if(!in_array($action,['create','update','status','delete'],true)) throw new HttpError(400,'bad_request');
        $id=$action==='create'?null:positive_id($_POST);
        $submission=check_submission($action,$id);
        if(in_array($action,['create','update'],true)) {
            $view=$action==='create'?'new':'edit';$validation=validate_save($_POST);$values=$validation['values'];$errors=$validation['errors'];
        } else $view=$action==='delete'?'delete':'detail';
        if($id!==null) $version=positive_id($_POST,'version');
    }
    $configFile=getenv('RESTART_CONFIG')?:__DIR__.'/config.local.php';
    if(!is_file($configFile)) throw new HttpError(503,'database_error');
    $config=require $configFile;$timezone=$config['timezone']??'Asia/Tokyo';
    $repository=new SaveRepository(connect_database($config));
    if($method==='POST') {
        if($id!==null) {$record=$repository->find($id);if(!$record) throw new HttpError(404,'not_found');}
        if($errors) {http_response_code(422);$message='validation';}
        else {
            if($action==='create') {$id=$repository->create($values);$flash='saved';}
            elseif($action==='delete') {
                if(!$repository->delete($id,$version)) throw new HttpError(409,'conflict');
                $flash='deleted';
            } else {
                if($action==='status') {
                    $values=$record;$values['status']=scalar_value($_POST,'status');
                    if(!in_array($values['status'],SAVE_STATUSES,true)) throw new HttpError(400,'bad_request');
                }
                if(!$repository->update($id,$version,$values)) throw new HttpError(409,'conflict');
                $flash=$action==='status'?'status_changed':'updated';
            }
            $_SESSION['flash']=$flash;
            $location=$action==='delete'?url():url(['view'=>'detail','id'=>$id]);
            $_SESSION['forms'][$submission]['done']=$location;
            redirect_to($location);
        }
    } elseif(in_array($view,['detail','edit','delete'],true)) {
        $id=positive_id($_GET);$record=$repository->find($id);
        if(!$record) throw new HttpError(404,'not_found');
        $values=$record;$version=(int)$record['version'];
    }
    $counts=$repository->counts();
    if($view==='list') {
        $status=scalar_value($_GET,'status','all');$category=scalar_value($_GET,'category','all');
        if(!in_array($status,['all',...SAVE_STATUSES],true)||!in_array($category,['all',...SAVE_CATEGORIES],true)) throw new HttpError(400,'bad_request');
        $page=isset($_GET['page'])?positive_id($_GET,'page'):1;
        $listing=$repository->listing($status,$category,$page);$page=$listing['page'];$latest=$repository->latestActive();
    }
    if($method==='GET'&&isset($_SESSION['flash'])) {$message=$_SESSION['flash'];unset($_SESSION['flash']);}
} catch(HttpError $exception) {
    http_response_code($exception->status);$message=$exception->key;
    if($exception->status!==409 && !($exception->status===503&&in_array($view,['new','edit'],true))) $view='error';
} catch(PDOException $exception) {
    http_response_code(503);$message='database_error';
    error_log('ReStart database operation failed, SQLSTATE '.$exception->getCode());
    if(!in_array($view,['new','edit'],true)) $view='error';
}
$title=t(['new'=>'new_title','edit'=>'edit_title','detail'=>'view','delete'=>'delete','help'=>'help','error'=>'error'][$view]??'all');
require __DIR__.'/views/layout.php';
