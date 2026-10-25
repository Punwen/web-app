<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
check(is_file(dirname(__DIR__).'/app/domain.php'), 'domain implementation exists');
require dirname(__DIR__).'/app/domain.php';
$r=validate_save(fixture()); check($r['errors']===[], 'valid Unicode entry accepted');
check($r['values']['memo']==='日本語メモ', 'text retained');
foreach (['title'=>100,'progress'=>2000,'next_step'=>2000,'memo'=>4000] as $field=>$limit) {
    check(validate_save(fixture([$field=>str_repeat('😀',$limit)]))['errors']===[], "$field exact Unicode limit accepted");
    check(isset(validate_save(fixture([$field=>str_repeat('😀',$limit+1)]))['errors'][$field]), "$field over limit rejected");
}
foreach (['title','progress'] as $field) check(isset(validate_save(fixture([$field=>" \n　"]))['errors'][$field]), "$field Unicode blank rejected");
foreach (array_keys(fixture()) as $field) check(isset(validate_save(fixture([$field=>['bad']]))['errors'][$field]), "$field array rejected");
check(isset(validate_save(fixture(['status'=>'unknown']))['errors']['status']), 'unknown status rejected');
check(isset(validate_save(fixture(['category'=>'sql']))['errors']['category']), 'unknown category rejected');
check(validate_save(fixture(['next_step'=>'','memo'=>'']))['errors']===[], 'optional fields empty accepted');
check(validate_save(fixture(['title'=>" <b>'hello'</b> "]))['values']['title']==="<b>'hello'</b>", 'raw text trimmed, not HTML interpreted');
check(validate_save([])['errors']!==[], 'missing required fields rejected');
done('domain');
