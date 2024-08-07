<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  고개윙문자 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	include_once $engine_dir."/_engine/sms/sms_module.php";

	$partner_sms = addslashes($_POST['partner_sms']);

	$we_mms = new WeagleEyeClient($GLOBALS['_we'], $cfg['sms_module']);
	$mms_config = $we_mms->call('getMmsConfig');

	addField($tbl['sms_case'], 'sms_night', "enum ('N','H','Y') default 'N'");
	addField($tbl['sms_case'], 'alimtalk_code', "varchar(50) not null default ''");
	addField($tbl['sms_case'], 'mng_push', "enum('N', 'Y', 'A') not null default 'N'");

	$case = $_POST['case'];
	$msg = $_POST['msg'];
	$use_check = $_POST['use_check'];
	$sms_night = $_POST['sms_night'];
	$equipt_alimtalk = $_POST['equipt_alimtalk'];
	$mng_push = $_POST['mng_push'];

	$sms_privay = (!$use_check[31]) ? "N" : "Y";
	$wec_acc = new weagleEyeClient($_we, 'etc');
	$wec_acc->call('setMemberPrivacyCron', array(
		'sms_use' => $sms_privay,
		'root_url' => $root_url,
	));

	foreach($case as $key=>$val) {
		if($msg[$val]) {
			if($val==22 || $val==28) $use_check[$val]="Y";
			if(!$use_check[$val]) $use_check[$val]="N";
			$msg[$val]=addslashes($msg[$val]);
			$equipt_alimtalk[$val] = addslashes($equipt_alimtalk[$val]);
			$data=get_info($tbl['sms_case'],"case",$val);
			if($admin['level'] > 3) {
				$data = $pdo->assoc("select * from `$tbl[partner_sms]` where `partner_no`='$admin[partner_no]' and `case` ='$val' ");
			}
			if($data['case']) {
				if($admin['level'] > 3) {
					$sql = "update `$tbl[partner_sms]` set `msg`='$msg[$val]', `use_check`='$use_check[$val]', `sms_night`='$sms_night[$val]', alimtalk_code='$equipt_alimtalk[$val]' where `case`='$val' and `partner_no` = '$admin[partner_no]'";
				}else {
					$sql="update `$tbl[sms_case]` set `msg`='$msg[$val]', `use_check`='$use_check[$val]', `sms_night`='$sms_night[$val]', alimtalk_code='$equipt_alimtalk[$val]', mng_push='$mng_push[$val]' where `case`='$val'";
				}
			}
			else {
				if($admin['level'] > 3) {
					$sql="insert into `$tbl[partner_sms]` (`case`,`msg`,`use_check`,`sms_night`, `alimtalk_code`, `partner_no`) values ('$val','$msg[$val]','$use_check[$val]','$sms_night[$val]', '$equipt_alimtalk[$val]', '$admin[partner_no]')";
				} else {
					$sql="insert into `$tbl[sms_case]` (`case`,`msg`,`use_check`,`sms_night`, alimtalk_code, mng_push) values ('$val','$msg[$val]','$use_check[$val]','$sms_night[$val]', '$equipt_alimtalk[$val]', '$mng_push[$val]')";
				}
			}
		}
		else {
			if($admin['level'] > 3) {
				$sql="delete from `$tbl[partner_sms]` where `case`='$val' and `partner_no`='$admin[partner_no]'";
			} else {
				$sql="delete from `$tbl[sms_case]` where `case`='$val'";
			}
		}

		$pdo->query($sql);
	}

	include $engine_dir."/_manage/config/config.exe.php";

?>