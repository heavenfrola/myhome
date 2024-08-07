<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  SNS 회원가입폼
	' +----------------------------------------------------------------------------------------------+*/


	include_once $engine_dir."/_engine/include/common.lib.php";


	//데이터 초기화
	$sns_type			= $_SESSION["sns_login"]["sns_type"];
	$cid				= $_SESSION["sns_login"]["cid"];
	$name				= $_SESSION["sns_login"]["name"];
	$email				= $_SESSION["sns_login"]["email"];
	$serialize			= "";
	$cfg['password_min']= $cfg['password_min'] ? $cfg['password_min'] : 4;
	$rURL = ($rURL) ? $rURL : $root_url;
	$rURL = $_SESSION["sns_login"]["rURL"] ? $_SESSION["sns_login"]["rURL"] : $rURL;
    unset($_SESSION["sns_login"]["rURL"]);

    // mypage를 통한 아이디 통합
    if ($member['no']) {
        require __ENGINE_DIR__.'/_engine/member/sns_integrate.exe.php';
        return;
    }

	//유효성 체크
	if($_SESSION["member_no"])msg(__lang_member_info_alreadyLogin__,$root_url);
	if(!$sns_type) msg(__lang_member_error_snsEssential__,"back");
	if(!$cid) msg(__lang_member_error_snsEssential__,"back");


	common_header();
	// 가입 여부 체크 ( 가입시 로그인 / 미가입시 회원가입 폼 )
	$sql = "SELECT * FROM $tbl[sns_join] AS A INNER JOIN  $tbl[member] AS B ON (A.member_no=B.no)  WHERE A.cid='$cid' and A.type = '$_sns_type[$sns_type]'";
	$snsData = $pdo->assoc($sql);
	if($snsData["no"]) {
		//탈퇴 회원 로그인 거부
		if($snsData["withdraw"]=='Y') {
			msg(__lang_member_info_w​ithdrawal__, $root_url);
			exit;
		}
		//이메일 인증확인 로그인 거부
		if($snsData['reg_email']=='W') {
			msg(__lang_member_error_notValidMember__, $root_url);
			exit;
		}
		//비밀번호 재발급 로그인 거부
		if($snsData['pwd']=="TEMP") {
			?>
			<script type="text/javascript">
				alert('\n<?=$snsData[member_id]?>님은 사이트 개편 이전 가입 고객으로 비밀번호를 재발급받으셔야합니다. \n\n고객님의 기본 정보를 입력하시면 핸드폰으로 새 비밀번호가 전송됩니다.\n지금 재발급 페이지로 이동합니다.\n');
				newSMSpwd();
			</script>
			<?php
			exit;
		}
		//휴면상태 로그인 거부
		if($snsData['withdraw'] == 'D2') {
			?>
			<script type="text/javascript">
				alert(_lang_pack.common_restore_sleep);
				$('iframe[name='+hid_frame+']').attr('src', '/main/exec.php?exec_file=member/login.exe.php&exec=removeDeleted&mno=<?=$snsData[no]?>&hash=<?=$snsData[pwd]?>');
			</script>
			<?php
			exit;
		}


		//SNS 가입자 로그인
		?>
		<script type="text/javascript">
			snsLogin('<?=$sns_type?>', '<?=$cid?>', '<?=$rURL?>');
		</script>
		<?php
		exit;
	}

?>
<script type='text/javascript'>
var password_min = '<?=$cfg['password_min']?>';

/**
 * SNS 간편 가입시 입력 없이 바로 가입
 **/
function snsJoin(rURL)
{
    var param = {
        'accept_json': 'Y',
        'sns_simplified': 'Y',
        'exec_file': 'member/join.exe.php',
        'rURL': rURL
    };
    $.ajax({
        url: root_url+'/main/exec.php',
        method: 'POST',
        dataType: 'json',
        data: param,
        success: function(r) {
            if (r.message) {
                window.alert(r.message);
            }
            if (!r.url) r.url = root_url;
            location.href = r.url;
        },
        error: function(r) {
            window.alert(r.statusText);
            location.href = root_url+'/member/login.php';
        }
    });
}

$(function() {
	setJoinFormResult();
	<?php if($cfg['member_join_id_email'] == 'Y') { ?>
	if($('.auto_complete_member_id').length > 0) {
		attachEmailAutoComplete();
	}
	<?php } ?>
});
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js?202006"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/emailAutoComplete.js"></script>
<?php

    // 약관 없는 SNS 가입 페이지 (카카오싱크)
    if ($_SESSION['sns_login']['agreed'] == true) {
        if (isset($_GET['check_mail']) == true) {
            if (file_exists($root_dir.'/_skin/'.$design['skin'].'/CORE/member_apijoin_noterms.wsr') == true) {
                $_tmp_file_name = 'member/apijoin_noterms.php';
            }
        } else {
            echo "<script>snsJoin('$rURL');</script>";
            exit;
        }
    }

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>