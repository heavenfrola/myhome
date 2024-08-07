<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  원더 로그인 아이디/시크릿키 발급
	' +----------------------------------------------------------------------------------------------+*/


	include_once $engine_dir.'/_engine/include/common.lib.php';

	//데이터 초기화
	$sns_type		= trim($_REQUEST["sns_type"]) ? trim($_REQUEST["sns_type"]) : "WN";
	$state			= trim($_REQUEST["state"]);
	$code			= trim($_REQUEST["code"]);
	$error			= trim($_REQUEST["error"]);

	$error_description	= trim($_REQUEST["error_description"]);
	$snsrurl = $_SESSION["sns_login"]["rURL"];
    unset($_SESSION['sns_login']);
	$_SESSION["sns_login"]["rURL"] = $snsrurl;

	if($code) {
		$_SESSION['wn_code'] = $code;

		$client_id = base64_encode($cfg['wonder_login_client_id'].":".$cfg['wonder_login_client_secret']);
		$req_auth = 'Authorization: Basic '.$client_id;
		$req_cont = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';
		$wonder_header = array($req_auth, $req_cont);
		$return_url = urlencode($root_url.'/main/exec.php?exec_file=promotion/wonder_callback.exe.php&sns_type=WN');
		$wonder_params = array(
			'grant_type'    => 'authorization_code',     //고정
			'code'			=> $code,					// 코드
			'redirect_uri'  => $return_url             // 리턴URL
		);
		$post_args = "";
		$curl_fd['grant_type'] = "authorization_code";
		$curl_fd['code'] = $code;
		$curl_fd['redirect_uri'] = $return_url;

		foreach($curl_fd as $ck=>$cv){
			$post_args .= ($post_args) ? "&" : "";
			$post_args .= $ck."=".$cv;
		}

		$Result = comm('https://login.wonders.app/wauth/token', $post_args, '', $wonder_header);
		$result_json = json_decode($Result, true);
		if($result_json['access_token']) {
			$token = $result_json['access_token'];

			$req_auth = 'Authorization: Bearer '.$token;
			$wonder_header = array($req_auth);
			$Result2 = comm('https://login.wonders.app/wauth/me', '', '', $wonder_header);
			$result_json2 = json_decode($Result2, true);

			if($result_json2['mid']) {
				$_SESSION["sns_login"]["cid"] = $result_json2['mid'];
				$_SESSION["sns_login"]["name"] = $result_json2['name'];
				$_SESSION["sns_login"]["cell"] = $result_json2['mobile'];
				$_SESSION["sns_login"]["email"] = $result_json2['email'];
				$_SESSION["sns_login"]["gender"] = $result_json2['gender'];
				$_SESSION["sns_login"]["birth"] = $result_json2['birth'];
				$_SESSION["sns_login"]["sns_type"] = 'WN';

				common_header();
				?>
				<?if($_SESSION['browser_type'] == 'mobile') {?>
					<script type="text/javascript">
					window.location.href="/member/apijoin.php";
					</script>
				<?} else {?>
					<script type="text/javascript">
					opener.parent.window.location.href="/member/apijoin.php";
					self.close();
					</script>
				<?}
			}else {
				msg("[ERROR 1]".$result_json2['error']." : ".$result_json2['error_description'], "close");
			}
		}else {
			msg("[ERROR 2]".$result_json['error']." : ".$result_json['error_description'], "close");
		}
	}else {//코드없음
		//msg("[ERROR 3]".$error." : ".$error_description, "close");
		msg("", "close");
	}

	exit;

?>