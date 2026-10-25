<?php
declare(strict_types=1);
require_once __DIR__.'/domain.php';
function export_backup(PDO $db): string {
    return json_encode(['format'=>'restart-backup','version'=>1,'exported_at'=>gmdate('c'),'saves'=>$db->query('SELECT * FROM saves ORDER BY id')->fetchAll(PDO::FETCH_ASSOC)],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
}
function restore_backup(PDO $db,string $json): int {
    try {$backup=json_decode($json,true,512,JSON_THROW_ON_ERROR);}catch(JsonException $e){throw new InvalidArgumentException('Invalid JSON backup.');}
    if(!is_array($backup)||($backup['format']??null)!=='restart-backup'||($backup['version']??null)!==1||!is_array($backup['saves']??null)||!array_is_list($backup['saves'])) throw new InvalidArgumentException('Unsupported backup format.');
    $records=[];$ids=[];
    foreach($backup['saves'] as $row) {
        if(!is_array($row)) throw new InvalidArgumentException('Invalid backup record.');
        $check=validate_save($row);
        if($check['errors']) throw new InvalidArgumentException('Invalid record fields.');
        foreach(['id','version'] as $field) {
            $value=$row[$field]??null;
            if((!is_int($value)&&!is_string($value))||!preg_match('/^[1-9][0-9]*$/D',(string)$value)||filter_var($value,FILTER_VALIDATE_INT)===false) throw new InvalidArgumentException('Invalid record number.');
        }
        if((int)$row['version']>4294967295||isset($ids[(int)$row['id']])) throw new InvalidArgumentException('Duplicate ID or invalid version.');
        $ids[(int)$row['id']]=true;
        foreach(['created_at','updated_at'] as $field) {
            $date=$row[$field]??null;
            $parsed=is_string($date)?DateTimeImmutable::createFromFormat('!Y-m-d H:i:s.u',$date,new DateTimeZone('UTC')):false;
            if(!$parsed||$parsed->format('Y-m-d H:i:s.u')!==$date) throw new InvalidArgumentException('Invalid timestamp.');
        }
        $records[]=$check['values']+['id'=>(int)$row['id'],'version'=>(int)$row['version'],'created_at'=>$row['created_at'],'updated_at'=>$row['updated_at']];
    }
    $db->beginTransaction();
    try {
        $db->exec('DELETE FROM saves');
        $insert=$db->prepare('INSERT INTO saves (id,title,category,status,progress,next_step,memo,created_at,updated_at,version) VALUES (:id,:title,:category,:status,:progress,:next_step,:memo,:created_at,:updated_at,:version)');
        foreach($records as $record)$insert->execute($record);
        $db->commit();
    }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
    return count($records);
}
