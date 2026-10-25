<?php
declare(strict_types=1);
final class HttpError extends RuntimeException {
    public function __construct(public int $status,public string $key) {parent::__construct($key);}
}
function e(mixed $value): string {return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function scalar_value(array $source,string $key,string $default=''): string {
    if(!array_key_exists($key,$source)) return $default;
    if(!is_string($source[$key])) throw new HttpError(400,'bad_request');
    return $source[$key];
}
function positive_id(array $source,string $key='id'): int {
    $raw=scalar_value($source,$key);
    if(!preg_match('/^[1-9][0-9]*$/D',$raw) || filter_var($raw,FILTER_VALIDATE_INT)===false) throw new HttpError(400,'bad_request');
    return (int)$raw;
}
function t(string $key,array $replace=[]): string {
    static $messages=null; $messages??=require __DIR__.'/translations.php';
    $index=['zh-Hant'=>0,'ja'=>1,'en'=>2][$GLOBALS['lang']??'zh-Hant'];
    $message=$messages[$key][$index]??$key;
    foreach($replace as $name=>$value) $message=str_replace('{'.$name.'}',(string)$value,$message);
    return $message;
}
function url(array $params=[]): string {
    return 'index.php?'.http_build_query(array_replace(['lang'=>$GLOBALS['lang']??'zh-Hant'],$params),'','&',PHP_QUERY_RFC3986);
}
function redirect_to(string $location): never {header('Location: '.$location,true,303);exit;}
function display_time(string $time): string {
    return (new DateTimeImmutable($time,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($GLOBALS['timezone']??'Asia/Tokyo'))->format('Y.m.d · H:i');
}
function form_fields(string $action,?int $id=null): string {
    $_SESSION['token']??=bin2hex(random_bytes(24));
    $_SESSION['forms']??=[];
    if(count($_SESSION['forms'])>=100) array_shift($_SESSION['forms']);
    $submission=bin2hex(random_bytes(16));
    $_SESSION['forms'][$submission]=['action'=>$action,'id'=>$id,'done'=>null];
    $fields=['action'=>$action,'token'=>$_SESSION['token'],'submission'=>$submission];
    if($id!==null) $fields['id']=(string)$id;
    $html='';foreach($fields as $name=>$value) $html.='<input type="hidden" name="'.$name.'" value="'.e($value).'">';
    return $html;
}
function check_submission(string $action,?int $id): string {
    $token=scalar_value($_POST,'token');$submission=scalar_value($_POST,'submission');
    if(!isset($_SESSION['token'])||!hash_equals($_SESSION['token'],$token)||!isset($_SESSION['forms'][$submission])) throw new HttpError(403,'csrf_error');
    $form=$_SESSION['forms'][$submission];
    if($form['action']!==$action||$form['id']!==$id) throw new HttpError(403,'csrf_error');
    if($form['done']!==null) redirect_to($form['done']);
    return $submission;
}
function icon(string $name): string {
    $paths=[
      'save'=>'<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2Z"/><path d="M7 3v6h10V3M7 21v-8h10v8"/>',
      'all'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
      'active'=>'<path d="m8 4 12 8-12 8Z"/>','paused'=>'<path d="M20.5 13A9 9 0 0 1 11 3.5 9 9 0 1 0 20.5 13Z"/>','completed'=>'<path d="m5 12 4 4L19 6"/>',
      'game'=>'<path d="M6 8h12c2 0 3 2 3.5 5l.5 4c.3 3-2 4-4 2l-2-2H8l-2 2c-2 2-4.3 1-4-2l.5-4C3 10 4 8 6 8Z"/><path d="M7 11v5M4.5 13.5h5M15 12h.01M18 15h.01"/>',
      'reading'=>'<path d="M12 5v16M3 4h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5v15h-5a4 4 0 0 0-4 2 4 4 0 0 0-4-2H3Z"/>',
      'creative'=>'<path d="m16 3 5 5-13 13H3v-5ZM13 6l5 5M3 21l5-1"/>','study'=>'<path d="m8 5-7 7 7 7m8-14 7 7-7 7M14 3l-4 18"/>',
      'other'=>'<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9Z"/>',
      'help'=>'<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .6-1.5 1-1.5 2M12 17h.01"/>',
      'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>','plus'=>'<path d="M12 5v14M5 12h14"/>',
    ];
    return '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['save']).'</svg>';
}
