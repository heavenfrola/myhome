<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입시 SMS 번호 인증
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

if(function_exists('getSkinCfg')) {
    $_skin = getSkinCfg();
    $jquery_ver = ($_skin['jquery_ver']) ? $_skin['jquery_ver'] : 'jquery-1.4.min.js';
}
$jquery_ui_ver = str_replace('jquery-', 'jquery-ui-', $_skin['jquery_ver']);

$data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
if ($data_type != 'varchar(100)') {
    modifyField($tbl['join_sms'], 'reg_code', 'VARCHAR(100)');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>휴대폰번호 인증</title>
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_manage/css/manage.css">
<?if($_SESSION['browser_type'] == 'mobile') {?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densitydpi=medium-dpi">
<?}?>
<script type="text/javascript">
var currency = "<?=__currency__?>";
var uip = '<?=$_SERVER['REMOTE_ADDR']?>';
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/lang/lang_<?=$cfg['language_pack']?>.js"></script>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery/<?=$jquery_ver?>'></script>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery/<?=$jquery_ui_ver?>'></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/common.js"></script>
<script type='text/javascript'>

function formCheck(f) {
    <?php if(is_array($_POST['cell']) == false) { ?>
	if(!checkBlank(f.cell2, _lang_pack.member_input_cell)) return false;
	if(!checkBlank(f.cell3, _lang_pack.member_input_cell)) return false;

	return true;
    <?php } ?>
}

function regComplete(reg_code, phone) {
	var f = parent.document.getElementsByName('joinFrm')[0];
	if(f.reg_code) {
		f.reg_code.value = reg_code;
	} else {
		$(f).append('<input type=\"hidden\" name=\"reg_code\" value=\"'+reg_code+'\">');
	}

	var temp = phone.split('-');
	var cell = ($(f).find('input[name="cell"]').length > 0) ? $(f).find('input[name="cell"]') : $(f).find("input[name^='cell[']");

	if(cell.length == 3) {
		cell.eq(0).val(temp[0]);
		cell.eq(1).val(temp[1]);
		cell.eq(2).val(temp[2]);
	}else if(cell.length == 1) {
		cell.val(temp[0]+temp[1]+temp[2])
	} else {
		f.cell[0].value = temp[0];
		f.cell[1].value = temp[1];
		f.cell[2].value = temp[2];
	}

	window.alert(_lang_pack.common_info_certcomplete);
    parent.removeFLoading();
	parent.removeCertFrm();
}

function regComplete2(reg_code, phone, postdata, device) { // 관리자 중요설정 2차인증
	var f = null;
	$('form', parent.document).each(function() {
		if(this.config_code) {
			if(this.card_pg && device =='pc') {
				if(this.card_pg.value == "<?=$_GET['postdata']?>") {
					f = this;
				}
			}else if(this.card_mobile_pg && device == 'mobile') {
				if(this.card_mobile_pg.value == "<?=$_GET['postdata']?>") {
					f = this;
				}
			} else {
				if(this.config_code.value == "<?=$_GET['postdata']?>") {
					f = this;
				}
			}
		}
	});
	if(f == null) {
		alert('설정 변경 중 오류가 발생하였습니다. 고객센터에 문의해 주세요.');
		parent.removeCertFrm();
		return false;
	}
	if(f.reg_code) {
		f.reg_code.value = reg_code;
	} else {
		$(f).append('<input type=\"hidden\" name=\"reg_code\" value=\"'+reg_code+'\">');
	}
	f.submit();

	window.alert(_lang_pack.common_info_certcomplete);

    parent.removeFLoading();
	parent.removeCertFrm();
}
</script>
<body id="manage">
<style type="text/css" title="">
body {background:#fff;}
</style>
<?PHP

	// 인증완료 처리
	if($_POST['reg_code']) {
		$phone = addslashes($_POST['phone']);
		$reg_code = $_POST['reg_code'];
        $repstr = smsCertificateNumCheckstr();
        if ($repstr) $reg_code = str_replace($repstr,'',$reg_code);
		$data = $pdo->assoc("select * from wm_join_sms where phone='$phone'");
        $reg_code_enc = aes128_encode($reg_code, 'join');
		if(($now-$data['reg_date']) > 300 && $postdata) {
			alert('입력 시간이 초과되었습니다. 인증번호를 다시 받아주세요');
		} else if ($reg_code_enc != $data['reg_code'] && !$postdata) {
			alert(php2java(__lang_member_error_wrongAuthcode__));
		} else if ($reg_code_enc != $data['reg_code'] && $postdata) {
			alert('잘못된 인증번호 입니다.\n인증번호를 확인한 다음 다시 입력해 주세요.');
		} else {
			if($postdata) {
				javac("regComplete2('$reg_code', '$phone', '$postdata', '$device')");
				exit;
			}
			javac("regComplete('$reg_code', '$phone')");
			exit;
		}
	}
	// 문자 발송
	if($_POST['cell2'] && $_POST['cell3']) {
		$phone = addslashes($_POST['cell1'].'-'.$_POST['cell2'].'-'.$_POST['cell3']);

        $reg_code = smsCertificateNum(); // 인증번호 생성

		// 중복가입 체크
		if($pdo->row("select count(*) from $tbl[member] where cell='$phone' and no!='$member[no]'") > 0 && !$postdata) {
			alert(__lang_member_error_existsCell__);
			unset($_POST, $phone, $reg_code);
		} else {
			if($postdata) {
				$phone2 = addslashes($_POST['cell1'].$_POST['cell2'].$_POST['cell3']);
					$res = $pdo->iterator("select cell from `wm_mng` where `cfg_confirm` = 'Y'");
					$cell = array();
                    foreach ($res as $mdata) {
						$cell[] .= $mdata['cell'];
					}
					foreach($cell as $key => $val) {
						if($val == $phone || $val == $phone2) {
							$reg_cell = 'Y';
						}
					}
					if($reg_cell != 'Y') {
						msg('등록된 인증 휴대폰 번호와 일치하지 않습니다', 'back');
					}
				}

			$limit = time()-3600;

			if(!istable($tbl['join_sms'])) {
				include_once $engine_dir.'/_config/tbl_schema.php';
				$pdo->query($tbl_schema['join_sms']);
			}

			$pdo->query("delete from wm_join_sms where phone='$phone' || reg_date < $limit");
            $reg_code = numberOnly($reg_code);
            $reg_code_enc = aes128_encode($reg_code, 'join');
			$pdo->query("insert into wm_join_sms (phone, reg_code, reg_date) values (:phone, :reg_code_enc, :now)", array(
                ':phone' => $phone,
                ':reg_code_enc' => $reg_code_enc,
                ':now' => $now
            ));

			include_once $engine_dir."/_engine/sms/sms_module.php";

			$sms_replace['pwd'] = $reg_code;
            $ret = SMS_send_case(22, $phone);

            if($we_mms->result != 'OK' && $we_mms->result != '') {
				msg(__lang_member_error_sendSms__, 'back');
			}
		}
	}
	$data = $pdo->assoc("select * from wm_join_sms where phone='$phone'");

    if(empty($data['reg_date'])) $data['reg_date'] = 0;
?>
<form method="post" id="popupContent" onsubmit="return formCheck(this)">
	<div class="popupContent">
		<div id="header" class="popup_hd_line">
		<?php if($postdata) { ?>
			<div id="mngTab_pop" style="top: 10px; left:10px;">중요설정 2단계 인증</div>
        <?php } else { ?>
			<div id="mngTab_pop" style="top: 10px; left:10px;"><?=__lang_member_info_smsAuth__?></div>
		<?php } ?>
		</div>
		<div style="padding:10px;">
			<?php if(($_POST['cell2'] && $_POST['cell3']) || ($phone && $reg_code)) { ?>
			<p class="explain">
				<?=sprintf(__lang_member_info_authSmsComp__, $phone)?><br>
				<?=__lang_member_info_authSmsComp2__?>
			</p>
			<div style="padding:10px; background:#f2f2f2; border:1px solid #ccc;">
				<strong>인증번호</strong>
				<input type="hidden" name="phone" value="<?=$phone?>">
				<?php if($postdata) { ?>
				<input type="text" name="reg_code" onKeyup="this.value=this.value.replace(/[^-0-9\s]/g,'');" value="" class="input" size="10"> <span id="counter"></span>
                <?php } else { ?>
                <input type="text" name="reg_code" onKeyup="this.value=this.value.replace(/[^-0-9\s]/g,'');" value="" class="input" size="10">
				<?php } ?>
               <span id="counter"></span>
			</div>
			<?php } else { ?>
			<?php if(!$postdata) {?><p class="explain"><?=__lang_member_input_authCellnum__?></p>
			<?php } ?>
			<div style="padding:10px; background:#f2f2f2; border:1px solid #ccc;">
				<strong><?=__lang_member_info_cellNum__?></strong>
				<?php if($postdata) {
					$cell = numberOnly($pdo->row("select `cell` from `wm_mng` where `no`  ='".$admin['no']."'"));
					$cell1 = substr($cell, 0,3);
					$cell2 = substr($cell, 3,4);
					$cell3 = substr($cell, 7,4);?>
					<input type="text" name="cell1" class="input" value = "<?=$cell1?>" size="5" maxlength="4" readonly style="background:#ffecec">
					- <input type="text" name="cell2" class="input" value = "<?=$cell2?>" size="5" maxlength="4" readonly style="background:#ffecec">
					- <input type="text" name="cell3" class="input" value = "<?=$cell3?>"size="5" maxlength="4" readonly style="background:#ffecec">
				<?php } else { ?>
					<select name="cell1">
						<option value="010">010</option>
						<option value="011">011</option>
						<option value="016">016</option>
						<option value="017">017</option>
						<option value="018">018</option>
						<option value="019">019</option>
					</select>
					- <input type="text" name="cell2" class="input" value = "<?=$cell2?>" size="5" maxlength="4" >
					- <input type="text" name="cell3" class="input" value = "<?=$cell3?>"size="5" maxlength="4" >
				<?php } ?>
			</div>
			<?php } ?>
			<div class="pop_bottom">
				<span class="box_btn blue"><input type="submit" value="<?=__lang_common_btn_confirm__?>"></span>
				<span class="box_btn"><input type="button" value="<?=__lang_common_btn_close__?>" onclick="parent.removeCertFrm();"></span>
			</div>
		</div>
	</div>
</form>
</body>
</html>
<script type='text/javascript'>
function init() {
        if ($("#counter").is(':visible')) {
            var reg_date = <?=$data['reg_date']?>;
            var now = <?=$now?>;
            if (reg_date) {
                cnt = (reg_date + 300) - now;
            } else {
                cnt = 5 * 60;
            }
            tid = setInterval("counter()", 1000);
        }
    }

function counter() {
	$("#counter").html("<img src='<?=$engine_url?>/_manage/image/icon/clock_icon.png' style= 'vertical-align:top; margin-top:8px;'>  잔여시간 <strong>"+padZero(parseInt(cnt/60))+"</strong>분 <strong>"+padZero(parseInt(cnt%60))+"</strong>초");
	cnt--;
	if (cnt<0) {
	  clearInterval(tid);
      $(".certFrm", parent.document).css({"visibility":"hidden"});
        parent.dialogConfirm(null, '인증 시간이 초과되었습니다.\n본인인증을 다시 진행해 주시기 바랍니다.', {
            Ok: function() {
                parent.dialogConfirmClose();
                parent.openCertFrm();
            }
        });
	}
}

function padZero(n) {
	return n>9?n:"0"+n;
}

init();

</script>