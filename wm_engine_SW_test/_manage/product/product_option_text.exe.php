<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  텍스트옵션 수정/저장
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/file.lib.php';

	$otype = $_POST['otype'];
	$stat = numberOnly($_POST['stat']);
	$opno = numberOnly($_POST['opno']);
	$name = addslashes(trim($_POST['name']));
	$necessary = ($_POST['necessary'] == 'Y') ? 'Y' : 'N';
	$stat = numberOnly($_POST['stat']);
	$pno = numberOnly($_POST['pno']);
	$desc = addslashes(trim($_POST['desc']));
	$add_price = numberOnly($_POST['add_price'], true);
	$add_price_option = numberOnly($_POST['add_price_option'], true);
	$min_val = numberOnly($_POST['min_val']);
	$max_val = numberOnly($_POST['max_val']);
	$attr1 = addslashes(implode(',', $_POST['attr1']));
	$attr2 = addslashes(trim($_POST['attr2']));

	checkBlank($name, '옵션명을 입력해주세요.');
	checkBlank($necessary, '필수옵션 여부를 선택해주세요.');

	$opt_q = '';
	if($opno > 0) {
		$data =  $pdo->assoc("select updir, upfile1 from $tbl[product_option_set] where no='$opno'");
	}

	// 옵션명 이미지
	if($_POST['delfile1'] == 'Y') {
		deletePrdImage($data, 1, 1);
		if($_FILES['upfile1']['size'] < 1) {
			$opt_q .= ", updir='', upfile1=''";
		}
	}
	if($_FILES['upfile1']['size'] > 0) {
		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		wingUploadRule($_FILES, 'prdOption');
		if(!$data['updir']) {
			$data['updir'] = $dir['upload'].'/prd_common/'.date('Ym/d');
			makeFullDir($data['updir']);
		}

		if($data['upfile1']) {
			deletePrdImage($data, 1, 1);
		}

		$up_filename = md5($ii+time());
		$up_info = uploadFile($_FILES['upfile1'], $up_filename, $data['updir'], 'jpg|jpeg|gif|png');
		$opt_q .= ", updir='$data[updir]', upfile1='$up_info[0]'";
	}

	if($opno > 0) {
		$pdo->query("
			update $tbl[product_option_set] set
				name='$name', necessary='$necessary', deco1='$attr1', deco2='$attr2', `desc`='$desc' $opt_q where no='$opno'
		");

		$pdo->query("
			update $tbl[product_option_item] set
				iname='$name', add_price='$add_price', add_price_option='$add_price_option', max_val='$max_val', min_val='$min_val' where opno='$opno'
		");
	} else {
		$sort = $pdo->row("select max(sort) from $tbl[product_option_set] where pno='$pno'");

		$pdo->query("
			insert into $tbl[product_option_set]
				(name, necessary, otype, how_cal, deco1, deco2, pno, stat, reg_date, sort, updir, upfile1, `desc`)
				values
				('$name', '$necessary', '$otype', '1', '$attr1', '$attr2', '$pno', '$stat', '$now', '$sort', '$data[updir]', '$up_info[0]', '$desc')
		");
		$opno = $pdo->lastInsertId();

		if(!fieldExist($tbl['product_option_item'], 'max_val')) {
			addField($tbl['product_option_item'], 'max_val', 'int(10)');
			addField($tbl['product_option_item'], 'min_val', 'int(10)');
			addField($tbl['product_option_item'], 'add_price_option', 'int(10)');
			addField($tbl['product_option_item'], 'min_area', 'int(10)');
			addField($tbl['product_option_item'], 'min_area_option', 'enum("N","Y") default "N"');
			$pdo->query("alter table `$tbl[product_option_set]` change `how_cal` `how_cal` enum('1', '2', '3', '4') not null default '1' ");
		}
		$pdo->query("
			insert into $tbl[product_option_item]
				(pno, opno, iname, add_price, add_price_option, max_val, min_val)
				values
				('$pno', '$opno', '$name', '$add_price', '$add_price_option', '$max_val', '$min_val')
		");
	}

	msg('적용되었습니다.', 'popup');

?>