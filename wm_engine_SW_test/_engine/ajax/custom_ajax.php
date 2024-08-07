<?php
include_once $engine_dir."/_engine/include/common.lib.php";

$exec = $_POST['exec'];
if(!$exec) msg('잘못된 접근입니다.');

switch($exec) {
	case 'biz_api':
		$biz_num = $_POST['biz_num'];
		$biz_num_api = businessNumApi($biz_num);

		$msg = ($biz_num_api['match_cnt']>0) ? $biz_num_api['result_msg']:$biz_num_api['result_msg'].'관리자에게 문의주세요.';
		echo $msg;
		exit;
	break;
}
?>