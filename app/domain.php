<?php
declare(strict_types=1);
const SAVE_CATEGORIES = ['game','reading','creative','study','other'];
const SAVE_STATUSES = ['active','paused','completed'];
/** Normalize submitted text and return translation keys for field errors. */
function validate_save(array $input): array {
    $values=[]; $errors=[];
    foreach (['title'=>100,'progress'=>2000,'next_step'=>2000,'memo'=>4000,'category'=>20,'status'=>20] as $key=>$limit) {
        $raw=$input[$key]??'';
        if (!is_string($raw) || !mb_check_encoding($raw,'UTF-8')) { $values[$key]=''; $errors[$key]='invalid_field'; continue; }
        $values[$key]=preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u','',$raw)??'';
        if (in_array($key,['title','progress'],true) && $values[$key]==='') $errors[$key]='required_field';
        elseif (mb_strlen($values[$key],'UTF-8')>$limit) $errors[$key]='too_long';
    }
    if (!in_array($values['category'],SAVE_CATEGORIES,true)) $errors['category']='invalid_field';
    if (!in_array($values['status'],SAVE_STATUSES,true)) $errors['status']='invalid_field';
    return ['values'=>$values,'errors'=>$errors];
}
