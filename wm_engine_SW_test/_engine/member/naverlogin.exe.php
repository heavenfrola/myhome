<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버로그인처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

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

	if($_GET['error']) {
		//실패
		$login_type_fail = "네이버 로그인에 실패 하였습니다. 잠시 후 다시 시도해 주세요.1";
	}else {
		if($_GET['code']) {
			$tempurl = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".urlencode($cfg[naver_login_client_id])."&client_secret=".urlencode($cfg[naver_login_client_secret])."&code=".urlencode($_GET['code'])."&state=".urlencode($_GET['state']);
			$sult = comm($tempurl);
			$sult = json_decode($sult, true);

			if($sult['access_token']=="" || $sult['refresh_token']=="") {
				$login_type_fail = "네이버 로그인에 실패 하였습니다. 잠시 후 다시 시도해 주세요.2";
				return;
			}

			// 리플래시
			$tempurl = "https://nid.naver.com/oauth2.0/token?grant_type=refresh_token&client_id=".urlencode($cfg[naver_login_client_id])."&client_secret=".urlencode($cfg[naver_login_client_secret])."&refresh_token=".$sult['refresh_token'];
			$reresult = comm($tempurl);
			$reresult = json_decode($reresult, true);

			$tempheader[] =  "Authorization : ".$reresult['token_type']." ".$reresult['access_token'];
			$result = commSns('https://openapi.naver.com/v1/nid/getUserProfile.xml', null, $tempheader);

			$result['result']['message'] = iconv('utf-8','euc-kr',$result['result']['message']);
			if($result['result']['resultcode']=='00') {//성공

				addField($tbl[member],'login_type','varchar(20) default NULL');
				addField($tbl[member],'access_id','int(11) default NULL');
				addField($tbl[member],'access_key','varchar(100) default NULL');

				$naver_no = $pdo->row("select no from `$tbl[member]` where login_type='naver' and access_id='".$result[response][id]."' and access_key='".$result[response][enc_id]."'");
				if($naver_no) {
					$data = $pdo->assoc("select * from `$tbl[member]` where `no`='$naver_no'");
				}else {
					$sql="INSERT INTO `$tbl[member]` (`member_id`, `name` , `email` , `ip` , `reg_date`, `nick`, `login_type`, `access_id`, `access_key`) VALUES ('".$result[response][email]."', '".$result[response][name]."', '".$result[response][email]."', '".$_SERVER[REMOTE_ADDR]."', '$now' , '".$result[response][nickname]."', 'naver', '".$result[response][id]."', '".$result[response][enc_id]."')";
					$pdo->query($sql);
					$naver_no = $pdo->lastInsertId();
					$data = $pdo->assoc("select * from `$tbl[member]` where `no`='$naver_no'");
				}
				$login_type = "naver";
			}else {
				//실패
				$login_type_fail = $result['errorCode']."/".$result['errorMessage'];
			}
		}
	}
	return;

?>