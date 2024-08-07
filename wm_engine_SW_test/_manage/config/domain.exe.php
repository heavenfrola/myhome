<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  대표도메인 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

    use TrueBV\Punycode;
    use Wing\API\Kakao\KakaoSync;

	checkBasic();

	$exec = $_POST['exec'];

	// 신규 도메인 추가
	if($exec == 'regist') {
		$domains = ($_POST['domain_type'] == 'wisa') ? $_POST['domains'] : array($_POST['domain']);
		if(count($domains) == 0 || $domains[0] == '') {
			msg('추가할 도메인을 입력해주세요.');
		}
		foreach($domains as $domain) {
			$domain = strtolower($domain);
			$domain = preg_replace('/^https?:\/\//', '', $domain);

            // 한글도메인을 punycode 로 변환
            if (preg_match("/\p{Hangul}/u", $domain) == true) {
                $Punycode = new Punycode();
                $domain = $Punycode->encode($domain);
            }

			if(preg_match('/[^0-9a-z-\.]/', $domain)) {
				msg('도메인에 사용할수 없는 문자가 있습니다.');
			}
			/*
			if(gethostbyname($domain) != $_SERVER['SERVER_ADDR']) {
				msg('입력한 도메인이 현재 서버를 향하고 있지 않습니다.');
			}
			*/
			// 도메인 Suffix 를 구해 서브도메인인지 아닌지 확인
			$prefix = array('co.kr', 'or.kr', 'pe.kr', 'ne.kr', 'com', 'net', 'me', 'kr', 'biz', 'org', 'info', 'jp', 'us', 'in', 'eu');
			foreach($prefix as $val) {
				if(preg_match("/\.$val$/", $domain)) {
					$suffix = preg_replace("/\.$val$/", '', $domain);
					break;
				}
			}

			if($cfg['mobile_use']=='Y' && preg_match('/\./', $suffix) == true) {
				$sub_domain="m.".$domain;
			}

			$wecaccount = new weagleEyeClient($_we, 'account');
			$wecaccount->queue('domAdd',$wecaccount->config['account_idx'], $domain, $_SERVER['SERVER_ADDR']);
			$wecaccount->send_clean();
			$wecaccount->result = mb_convert_encoding($wecaccount->result, _BASE_CHARSET_, array('euckr', 'utf8'));

			if(!empty($sub_domain)) {
				$wecaccount->queue('domAdd',$wecaccount->config['account_idx'], $sub_domain, $_SERVER['SERVER_ADDR'], 'sub', $domain);
				$wecaccount->send_clean();
			}

			if($wecaccount->result == 'OK') $success++;
			else msg(php2java($wecaccount->result));
		}

		msg('도메인이 추가되었습니다.', 'reload', 'parent');
	}

	// 서브도메인 제거
	if($exec == 'remove') {
		$wecaccount = new weagleEyeClient($_we, 'account');
		$wecaccount->queue('domRemove',$wecaccount->config['account_idx'], $_POST['domain']);
		$wecaccount->send_clean();

		msg("변경이 완료되었습니다.\\n\\n최대 5분 이내까지 삭제된 도메인으로도 접속될수 있습니다.", "reload", "parent");

		exit;
	}

	// 메인도메인 변경
	if($exec == 'main') {
		$domain=trim($_POST['doms']);
		checkBlank($domain, '변경할 도메인을 입력해주세요.');

		foreach($_POST['target_domain'] as $key => $val) {
			$domain_expire[$key] = trim(addslashes($_POST['domain_expire'][$key]));
			$domain_organization[$key] = trim(addslashes($_POST['domain_organization'][$key]));

			if($pdo->row("select * from `$tbl[domain_expire]` where `domain` = '$val'")) {
				$pdo->query("update `$tbl[domain_expire]` set `expire` = '$domain_expire[$key]', `organization` = '$domain_organization[$key]' where `domain` = '$val'");
			} else {
				$pdo->query("insert into `$tbl[domain_expire]` (`domain`,`expire`,`organization`,`reg_date`) values ('$val', '$domain_expire[$key]', '$domain_organization[$key]', '$now')");
			}
		}

		if(preg_replace('/^https?:\/\//', '', $root_url) != $domain) {
			$host = $_SERVER['SERVER_ADDR'];
			$ckhost = gethostbyname($domain);
			if($host != $ckhost) msg("변경할 도메인이 현재 서버를 가리키고 있지 않습니다.\\n도메인 설정을 먼저 변경해 주셔야 합니다.");

			$wecaccount = new weagleEyeClient($_we, 'account');
			$wecaccount->queue('domRefresh',$wecaccount->config['account_idx'], $domain);
			$wecaccount->send_clean();
			$wecaccount->result = mb_convert_encoding($wecaccount->result, _BASE_CHARSET_, array('euckr', 'utf8'));

			if ($wecaccount->result == 'OK') {
                if ($scfg->comp('kakao_login_use', 'S') == true && $cfg['kakaoSync_StoreKey'] && $cfg['kakao_rest_api']) {
                    $kkosync = new KakaoSync(
                        $cfg['kakaoSync_StoreKey'],
                        $cfg['kakao_rest_api']
                    );
                    $ret = $kkosync->modification('edit');
                }

				msg("변경이 완료되었습니다.\\n\\n변경은 5분 이내에 이루어지며, 변경 시 세션이 종료되므로 다시 로그인해 주셔야 합니다.", "reload", "parent");
			} else {
				alert(php2java($wecaccount->result));
				exit;
			}
		}

		msg("변경이 완료되었습니다", "reload", "parent");
	}

?>