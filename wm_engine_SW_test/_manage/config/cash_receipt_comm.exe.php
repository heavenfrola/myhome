<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  데이콤 현금영수증 가맹 등록
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/ext.lib.php";
	$manage_url = $manage_url ? $manage_url : 'http://'.$_SERVER['HTTP_HOST'];

	$_direct=($_POST[body] == "config@cash_receipt_comm.exe") ? 1 : 0;

	$cfg[company_biz_num]=$_direct ? trim($cfg[company_biz_num]) : trim($_POST[company_biz_num]);
	$cfg[company_biz_num]=numberOnly($cfg[company_biz_num]);
	$cfg[company_name]=$_direct ? trim($cfg[company_name]) : trim($_POST[company_name]);
	$cfg[company_phone]=$_direct ? trim($cfg[company_phone]) : trim($_POST[company_phone]);
	$cfg[company_owner]=$_direct ? trim($cfg[company_owner]) : trim($_POST[company_owner]);
	$cfg[company_addr1]=$_direct ? trim($cfg[company_addr1]) : trim($_POST[company_addr1]);
	$cfg[company_addr2]=$_direct ? trim($cfg[company_addr2]) : trim($_POST[company_addr2]);
	$cfg[cash_receipt_auto]=$_direct ? $_POST[cash_receipt_auto] : $cfg[cash_receipt_auto];

	checkBlank($cfg[company_biz_num], "사업자번호를 입력해주세요.");
	checkBlank($cfg[company_name], "상호를 입력해주세요.");
	checkBlank($cfg[company_phone], "사업자 전화번호를 입력해주세요.");
	checkBlank($cfg[company_owner], "사업자 대표자 성명을 입력해주세요.");
	checkBlank($cfg[company_addr1], "사업장 주소를 입력해주세요.");

	$b_num_ck=checkBizNo($cfg[company_biz_num]);
	if(!$b_num_ck) msg("유효하지 않은 사업자번호입니다");

	$curl_fd[body]="config@cash_receipt.exe";
	$curl_fd[CST_PLATFORM]="service";
	$curl_fd[CST_MID]="wisadesign";
	$curl_fd[dacom_mert_key]="62fb968f97ba3e02b12a08b8b524b093";
	$curl_fd[LGD_METHOD]="REG_REQUEST";
	$curl_fd[LGD_REG_BUSINESSNUM]=$cfg[company_biz_num];
	$curl_fd[LGD_REG_MERTNAME]=$cfg[company_name];
	$curl_fd[LGD_REG_MERTPHONE]=$cfg[company_phone];
	$curl_fd[LGD_REG_CEONAME]=$cfg[company_owner];
	$curl_fd[LGD_REG_MERTADDRESS]=$cfg[company_addr1]." ".$cfg[company_addr2];
	$curl_fd[cash_receipt_auto]=$cfg[cash_receipt_auto];
	$curl_fd[cash_receipt_stat]=$_POST[cash_receipt_stat];
	if($_direct) $curl_fd[cash_config_update]="Y";
	$curl_fd[urlfix]="Y";

	$post_args="";
	foreach($curl_fd as $ck=>$cv){
		$post_args .= ($post_args) ? "&" : "";
		$post_args .= $ck."=".$cv;
	}
	$r=comm($manage_url."/_manage/", $post_args);
	if($_direct){
		echo $r;
		msg();
	}

?>