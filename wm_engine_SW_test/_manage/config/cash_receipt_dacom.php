<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  데이콤 현금영수증 가맹 등록
	' +----------------------------------------------------------------------------------------------+*/

	$_log_dir = $root_dir."/".$dir['upload']."/dacom_cash_reg_log";
	@mkdir($_log_dir);
	@chmod($_log_dir, 0777);

	// mall.conf 파일 생성
	$_content="server_id = 01
timeout = 60
log_level = 4
verify_cert = 1
verify_host = 1
report_error = 1
output_utf8 = 0
auto_rollback = 1
log_dir = ".$_log_dir."

t".$_dacom_mid." = ".$_dacom_mert_key."
".$_dacom_mid." = ".$_dacom_mert_key."
";

	$configPath = $engine_dir."/_engine/cash.dacom";

	$_filedir=$configPath."/conf/mall.conf";
	$of=fopen($_filedir, "w");
	$fw=fwrite($of, $_content);
	fclose($of);

	if(!$fw){
		msg("mall.conf 파일 등록이 실패하였습니다. 위사 1:1고객센터 문의 글로 접수 바랍니다.");
	}

?>