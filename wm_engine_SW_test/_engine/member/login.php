<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  로그인 프론트
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Kakao\KakaoSync;

	include_once $engine_dir."/_engine/include/common.lib.php";

	$rURL = strip_tags($_GET['rURL']);
	if($rURL == 'reload') {
		$rURL = '';
	}

	if(!$rURL) {
		if($cfg['member_return_page']=='3' && $cfg['member_return_page_custom']) {
			$rURL=$cfg['member_return_page_custom'];
            if(strpos($rURL, $root_url) !== 0) {
                $url_parse = parse_url($rURL);
                $rURL = $root_url;
                if ($url_parse['path']) $rURL .= $url_parse['path'];
                if ($url_parse['query']) $rURL .= '?'.$url_parse['query'];
            }
		}
		elseif($cfg['member_return_page']=='1' && $_SERVER['HTTP_REFERER']) {
			$rURL=$_SERVER['HTTP_REFERER'];
		} else {
			$rURL = $root_url;
		}
	}

	if($member['level']<10) {
		msg('', $rURL);
	}

	if(preg_match("@/shop/order.php@",$rURL)) $er_order=true;

    /*기존정보 삭제*/
    setcookie('wisamall_id', '', 0, '/');
    setcookie('wisamall_pw', '', 0, '/');

	if($_GET['err']) $err = numberOnly($_GET['err']);
	common_header();

    // 카카오싱크 자동 로그인
    if (!$member['no'] && $scfg->comp('kakao_login_use', 'S') == true && $scfg->comp('kakao_autologin_use', 'Y') == true) {
        $sync = new KakaoSync(
            $cfg['kakaoSync_StoreKey'],
            $cfg['kakao_rest_api']
        );
        $ret = $sync->autoLogin($rURL);
    }

	if($cfg['member_join_id_email'] == 'Y') {
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/emailAutoComplete.js"></script>
<script type="text/javascript">
$(function() {
	if($('.auto_complete_member_id').length > 0) {
		attachEmailAutoComplete();
	}
});
</script>
<?php
	}
    ?>
<script>
    $(function() {
        //자동로그인 처리
        autoLoginInfo('wisamall', 'get').then((autoLogin) => {
            if (autoLogin.id) {
                document.querySelector('#login_id').value = autoLogin.id;
                document.querySelector('#login_id').focus();
                document.querySelector('#member_id_save').checked = true
            }
            if (autoLogin.pwd) {
                document.querySelector('#login_pwd').value = autoLogin.pwd;
                document.querySelector('#login_pwd').focus();
                document.querySelector('#member_pwd_save').checked = true
            }
        });
    });
</script>
<?php

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>