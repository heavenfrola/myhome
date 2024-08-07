<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 로그인 아이디/시크릿키 발급
	' +----------------------------------------------------------------------------------------------+*/


	include_once $engine_dir.'/_engine/include/common.lib.php';

	function commSns($url, $post_args = null, $header_args = null) {
		$result  = "";

		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt($ch, CURLOPT_REFERER, 'mywisa.com');
			if($post_args){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_args);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header_args);
			$g = curl_exec($ch);
			curl_close($ch);

			$user = simplexml_load_string($g, 'SimpleXMLElement', LIBXML_NOCDATA);

			$json = json_encode($user);
			$result = json_decode($json,TRUE);
		}
		return $result;
	}


	//데이터 초기화
	$sns_type		= trim($_REQUEST["sns_type"]) ? trim($_REQUEST["sns_type"]) : "NA"; //NA(네이버),FB(페북),KA(카카오)
	$cid			= trim($_REQUEST["cid"]);
	$name			= ($_REQUEST["name"]) ? trim($_REQUEST["name"]) : trim($_REQUEST["nickname"]);
	$email			= trim($_REQUEST["email"]);
	$state			= trim($_REQUEST["state"]);
	$code			= trim($_REQUEST["code"]);
	$rURL			= $_REQUEST["rURL"];
	$snsrurl = $_SESSION["sns_login"]["rURL"];
    unset($_SESSION['sns_login']);
	$_SESSION["sns_login"]["rURL"] = $snsrurl;

	// CSRF 방지를 위한 상태 토큰 검증
	if($_SESSION["sns_login_state"] != $state) {
		if($sns_type == "NA") {
			msg(__lang_member_error_snscsrf__, "close");
		} else if($sns_type == 'fb_token') {
			?>
			<script type="text/javascript">
			var redir = location.href.replace('#access_token=', '&access_token=');
			location.href = redir+'&state=<?=$_SESSION[sns_login_state]?>';
			</script>
			<?
			exit;
		} else {
			echo "**@N**@";
		}
	} else {
		//네이버 (팝업형태로 호출)
		if($sns_type == "NA" && $code ) {
			$rURL = $_SESSION["sns_login"]["rURL"];
			//시크릿키 인증
			$tempurl = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".urlencode($cfg[naver_login_client_id])."&client_secret=".urlencode($cfg[naver_login_client_secret])."&code=".urlencode($_GET['code'])."&state=".urlencode($_GET['state']);
			$sult = comm($tempurl);
			$sult = json_decode($sult, true);


			//토큰 리플래쉬
			$tempurl = "https://nid.naver.com/oauth2.0/token?grant_type=refresh_token&client_id=".urlencode($cfg[naver_login_client_id])."&client_secret=".urlencode($cfg[naver_login_client_secret])."&refresh_token=".$sult['refresh_token'];
			$sult = comm($tempurl);
			$sult = json_decode($sult, true);


			//XML형태로 사용자 프로필 조회
			$tempheader[] =  "Authorization: ".$sult['token_type']." ".$sult['access_token'];
			$sult = commSns('https://openapi.naver.com/v1/nid/getUserProfile.xml', null, $tempheader);
			if($sult['result']['resultcode']=='00') {
				//회원조회성공 후 회원정보 세션에 저장
                if ($_use['naver_login_short_cid'] == 'Y') { // cid 값을 짧은값(id)로 쓰는 경우
                    $_SESSION["sns_login"]["cid"] = $sult['response']['id'];
                } else {
                    $_SESSION["sns_login"]["cid"] = ($sult['response']['enc_id']) ? $sult['response']['enc_id'] : $sult['response']['id'];
                }
				$_SESSION["sns_login"]["name"]     = ($sult['response']['name']) ? $sult['response']['name'] : $sult['response']['nickname'];
				$_SESSION["sns_login"]["email"]    = $sult['response']['email'];
				$_SESSION["sns_login"]["sns_type"] = $sns_type;
                $_SESSION['sns_login']['cell']     = str_replace('-', '', $sult['response']['mobile']);

				common_header();
				?>
				<?php if($mMatches[0] == 'Mobile') { ?>
					<script type="text/javascript">
					window.location.href="/member/apijoin.php?rURL=<?=$rURL?>";
					</script>
				<?php } else { ?>
					<script type="text/javascript">
					opener.parent.window.location.href="/member/apijoin.php?rURL=<?=$rURL?>";
					self.close();
					</script>
				<?php } ?>
				<?php

			} else {
				msg("[ERROR]".$sult['result']['resultcode']." : ".$sult['result']['message'], "close");
			}
		//페이스북, 카카오 (AJAX형태 호출)
		} else if($sns_type == "FB" || $sns_type == "KA") {
				//회원조회성공 후 회원정보 세션에 저장
				$_SESSION["sns_login"]["cid"]         = $cid;
				$_SESSION["sns_login"]["name"]        = $name;
				$_SESSION["sns_login"]["email"]       = $email;
				$_SESSION["sns_login"]["sns_type"]    = $sns_type;
		} else if($sns_type == 'fb_token') {
			$ret = comm('https://graph.facebook.com/v2.9/me?fields=id%2Cname&access_token='.$_GET['access_token']);
			$ret = json_decode($ret);

			$_SESSION["sns_login"]["cid"] = $ret->id;
			$_SESSION["sns_login"]["name"] = $ret->name;
			$_SESSION["sns_login"]["email"] = $ret->email;
			$_SESSION["sns_login"]["sns_type"] = 'FB';

			msg('', '/member/apijoin.php?rURL=<?=$rURL?>');
		}
	}

?>