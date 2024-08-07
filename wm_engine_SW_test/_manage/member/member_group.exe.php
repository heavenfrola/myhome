<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원그룹 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();
	$exec = $_POST['exec'];

	if($_POST['addInfo'] == 'Y') {
		$no = numberOnly($_POST['no']);
		if(!$no || $no>9) msg('잘못된 그룹코드입니다.', 'close');
		$data = $pdo->assoc("select * from $tbl[member_group] where no='$no'");
		if(!$data['no']) msg('존재하지 않는 그룹입니다.');

		$updir = $data['updir'];
		if($updir && ($_POST['delfile1']=="Y" || $_FILES['upfile1']['tmp_name'])) {
			deletePrdImage($data,1,1);
			$up_filename="";
			$chg_file=1;
		}
		if($_FILES['upfile1']['tmp_name']) {
			include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
			wingUploadRule($_FILES, 'memGroup');

			if(!$updir) {
				$updir=$dir['upload']."/".$dir['member'];
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}
			$up_info=uploadFile($_FILES["upfile1"],$data['no'],$updir,"jpg|jpeg|gif|png|bmp");
			$up_filename=$up_info[0];
			$chg_file=1;
		}
		if($chg_file) $asql.=" , `upfile1`='".$up_filename."'";

		$content=addslashes(trim($_POST['content']));
		$group_msg=addslashes(trim($_POST['group_msg']));

		if($_POST['all'] == 'Y') $pdo->query("update `$tbl[member_group]` set `group_msg`='$group_msg'");

		$sql="update `$tbl[member_group]` set `content`='$content', `group_msg`='$group_msg' $asql where `no`='$no'";
		$pdo->query($sql);

		if(!$_POST['group_price'.$no]) $_POST['group_price'.$no] = 'N';

		if($_FILES['csv']['size'] > 0) {
			$pdo->query("SET @member_chg_ref='manage';");

			$affected = 0;
			$ids = array();
			$fp = fopen($_FILES['csv']['tmp_name'], 'r');
			while($data = fgetcsv($fp, 128)) {
				$ids[] = "'".trim($data[0])."'";
				if(count($ids) > 500) {
					$ids = implode(',', $ids);
					$pdo->query("update $tbl[member] set level='$no' where member_id in ($ids)");
					$affetced += $pdo->lastRowCount();
					$ids = array();
				}
			}
			$ids = implode(',', $ids);
			if($ids) {
				$pdo->query("update $tbl[member] set level='$no' where member_id in ($ids)");
				$affetced += $pdo->lastRowCount();
			}

		}

		$cfg_msg = $affetced > 0 ? number_format($affetced).'명의 회원등급이 변경 되었습니다.' : '회원 그룹 추가정보가 수정되었습니다.';

		if($_POST['group_price'.$no] == 'Y' && $no) addField($tbl['product'], 'sell_prc'.$no, 'double(10,2) unsigned not null default 0 after `sell_prc`');
		include $engine_dir."/_manage/config/config.exe.php";

		exit;
	}
	elseif($exec=="delete") {

	}
	elseif($exec=="move") {
		$exec_gno1 = numberOnly($_POST['exec_gno1']);
		$exec_gno2 = numberOnly($_POST['exec_gno2']);
		if(!$exec_gno1 || !$exec_gno2) {
			msg("필수값이 없습니다");
		}
		if($exec_gno1==$exec_gno2) {
			msg("변경 전후의 그룹이 동일합니다");
		}

		$data=get_info($tbl['member_group'],"no",$exec_gno2);
		if($data['use_group']!="Y") {
			msg("이동하려는 그룹 $data[name] 은 사용중이 아닙니다");
		}

		$level1=$exec_gno1;
		$level2=$exec_gno2;

		$pdo->query("SET @member_chg_ref='manage';");

		$sql="update `$tbl[member]` set `level`='$level2' where `level`='$level1'";
		$pdo->query($sql);

		msg("회원을 이동하였습니다","reload","parent");
	}
	else {
		$use_group = $_POST['use_group'];
		$name = $_POST['name'];
		$milage = numberOnly($_POST['milage'], true);
		$milage2 = numberOnly($_POST['milage2'], true);
		$msale_type = $_POST['member_event_type'];
		$milage_cash = $_POST['milage_cash'];
		$move_price = numberOnly($_POST['move_price']);
		$move_qty = numberOnly($_POST['move_qty']);
		$free_delivery = $_POST['free_delivery'];
		$protect = $_POST['protect'];
		$gno = numberOnly($_POST['gno']);

		if(!count($use_group)) {
			msg("회원 그룹은 반드시 하나 이상 사용해야합니다");
		}

		if(!fieldExist($tbl['member_group'], 'protect')) {
			addField($tbl['member_group'], 'protect', 'enum("N","Y") default "N"');
		}
		if(!fieldExist($tbl['member_group'], 'move_qty')) {
			addField($tbl['member_group'], 'move_qty', 'int(5) not null default 0 after move_price');
		}

		foreach($gno as $key=>$val) {
			if(!$val) continue;

			$data=get_info($tbl['member_group'],"no",$val);

			$name[$key] = addslashes(trim($name[$key]));
			$use_group[$key] = ($use_group[$key] == 'Y') ? 'Y' : 'N';
			$milage_cash[$key] = ($milage_cash[$key] == 'Y') ? 'Y' : 'N';
			$free_delivery[$key] = ($free_delivery[$key] == 'Y') ? 'Y' : 'N';
			$protect[$key] = ($protect[$key] == 'Y') ? 'Y' : 'N';

			if($data['use_group'] == 'Y' && $use_group[$key] == 'N') {
				$pdo->query("update `$tbl[member]` set `level`='9' where `level`='$data[no]'");
			}

			$msql = '';
			if($msale_type == 2 || $msale_type == 3) $msql .= ", `milage`='$milage[$key]'";
			if($msale_type == 1 || $msale_type == 3) $msql .= ", `milage2`='$milage2[$key]'";

			$sql="update `$tbl[member_group]` set `use_group`='".$use_group[$key]."', `name`='".$name[$key]."', `milage_cash`='".$milage_cash[$key]."',  `move_price`='".$move_price[$key]."',  move_qty='$move_qty[$key]', `free_delivery`='".$free_delivery[$key]."', protect='$protect[$key]' $msql where `no`='$data[no]'";
			$pdo->query($sql);
		}

		if($_POST['member_auto_move'] == 'Y') {
			$cfg['member_level_limit'] = $_POST['member_level_limit'];
			$cfg['member_auto_move_down'] = $_POST['member_auto_move_down'];
			$cfg['member_level_day_down'] = $_POST['member_level_day_down'];
			$cfg['member_level_field'] = $_POST['member_level_field'];

			include $engine_dir.'/_engine/member/group_redefine.exe.php';
		}

		$wec_acc = new weagleEyeClient($_we, 'account');
		$wec_acc->call('setAutoMemberGroup', array('day'=>$_POST['member_level_day']));
		if($wec_acc->error) {
			alert(php2java($wec_acc->error));
			exit;
		}

		include $engine_dir."/_manage/config/config.exe.php";
	}

?>