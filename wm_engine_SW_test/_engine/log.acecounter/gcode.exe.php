<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  에이스카운터 가입연동결과 수집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";


	// 에이스카운터 리턴값 체크
	if (strtoupper($success_yn) != "Y") msg("에러 : 에이스카운터 가입실패");
	if (!$s_gcode) msg("에러 : gcode 미전송");
	if ($s_userid != $cfg['ace_counter_id']) msg("에러 : 가입 아이디 불일치");

	// 설정 저장
	$wea = new weagleEyeClient($_we, 'account');
	$wea->queue('aceStatus', $wea->config['account_idx'], 3, $s_gcode);
	$wea->send_clean();
	if($wea->result != 'OK') msg($wea->result);

	$_POST['ace_counter_gcode'] = $s_gcode;
	$no_reload_config = 1;
	include $engine_dir."/_manage/config/config.exe.php";

	msg('에이스 카운터 신청이 완료되었습니다', 'reload', 'parent');

?>