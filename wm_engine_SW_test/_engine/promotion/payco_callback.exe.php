<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이코 로그인 아이디/시크릿키 발급
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	//데이터 초기화
	$sns_type = 'PC';

	if($_SESSION["sns_login_state"] != $_GET['state']) {
		msg(__lang_member_error_snscsrf__, "close");
	} else {
		if($sns_type == "PC" && $_GET['code']) {
			//시크릿키 인증
			$tempurl = "https://id.payco.com/oauth2.0/token?grant_type=authorization_code&client_id=".urlencode($cfg['payco_login_client_id'])."&client_secret=".urlencode($cfg['payco_login_client_secret'])."&code=".urlencode($_GET['code'])."&state=".urlencode($_GET['state']);
			$sult = comm($tempurl);
			$sult = json_decode($sult, true);

			//토큰 리플래쉬
			$tempurl = "https://id.payco.com/oauth2.0/token?grant_type=refresh_token&client_id=".urlencode($cfg['payco_login_client_id'])."&client_secret=".urlencode($cfg['payco_login_client_secret'])."&refresh_token=".urlencode($sult['refresh_token'])."&state=".urlencode($_GET['state']);;
			$sult = comm($tempurl);
			$sult = json_decode($sult, true);

			// 회원 프로필 수집
			$tempheader = array(
				"Content-type:application/json",
				"client_id: ".$cfg['payco_login_client_id'],
				"access_token: ".$sult['access_token']
			);
			$postdata = json_encode(array(
				'client_id' => $cfg['payco_login_client_id'],
				'access_token' => $sult['access_token']
			));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://apis3.krp.toastoven.net/payco/friends/getMemberProfileByFriendsToken.json');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $tempheader);
			$sult = curl_exec($ch);
			curl_close($ch);
			$sult = json_decode($sult, TRUE);

			if(is_array($sult['memberProfile'])) {
				$profile = $sult['memberProfile'];

				$snsrurl = $_SESSION["sns_login"]["rURL"];
				unset($_SESSION['sns_login']);
				$_SESSION["sns_login"]["rURL"] = $snsrurl;
				$_SESSION["sns_login"]["cid"] = $profile['idNo'];
				$_SESSION["sns_login"]["name"] = $profile['name'];
				$_SESSION["sns_login"]["email"] = $profile['id'];
				$_SESSION["sns_login"]["sns_type"] = 'PC';

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
				<?}?>
				<?
			}else {
				msg("페이코아이디 연동 오류", "close");
			}
		}
	}

?>