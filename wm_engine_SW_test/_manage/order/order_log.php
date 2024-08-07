<?php

use Wing\common\OrderLog;

// 권한 체크
$weca = new weagleEyeClient($_we, 'account');
$asvcs = $weca->call('getSvcs',array('key_code'=>$wec->config['wm_key_code']));

if ($admin['level'] > 2) {
    if($asvcs[0]->type[0] == '10') {
        msg('접근 권한이 없습니다.', 'close');
    } else {
        if ($admin['admin_id'] != 'wisa') {
            msg('접근 권한이 없습니다.', 'close');
        }
    }
}

// 로그 읽기
$ono = $_GET['ono'];
$log = new OrderLog($ono);

// 로그 내용 편집
$content = mb_convert_encoding($log->content, 'utf8', 'utf8');
$content = htmlspecialchars($content);
$content = preg_replace('/\/\* \*\*\* (.*) \*\*\* \*\/\n/', "<h2 class='p_color'>🖨 $1</h2>", $content);
$content = preg_replace('/\/\* (.*) \*\//', "<span class='section'>/* $1 */</span>", $content);
$content = preg_replace('/(\[[0-9-]+ [0-9:.]+\])/', "<span class='p_color'>$1</span>", $content);
$content = str_replace("  ", '&nbsp;&nbsp;', $content);
$content = str_replace("\t", '&nbsp;&nbsp;&nbsp;', $content);
$content = nl2br($content);

?>
<style>
.log_area {
    padding: 20px;
    line-height: 160%;
    text-align: justify;
    font-size: 13px;
    font-family: "verdana";
}

.log_area h2 {
    padding: 20px 0;
    border-top: dashed 1px #000;
    font-size: 35px;
    font-family: "impact";
}

.log_area .section {
    font-weight: bold;
    color: #ff8400;
}
</style>
<div class="log_area">
    <?=$content?>
</div>