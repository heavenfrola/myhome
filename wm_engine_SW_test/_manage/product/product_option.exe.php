<?PHP

	checkBasic();

	$exec = $_POST['exec'];
	$opno = numberOnly($_POST['opno']);
	$check_pno = numberOnly($_POST['check_pno']);

	if($opno) {
		$data=get_info($tbl['product_option_set'],"no",$opno);
		if(!$data[no]) msg("존재하지 않는 자료입니다.","popup");
	}

	function optionAttachDel($opno, $idx, $field) {
		global $tbl, $pdo;

		$data = $pdo->assoc("select `no`,`updir`,`upfile1`,`upfile2` from `$tbl[product_option_img]` where `opno`='$opno' and `idx`='$idx'");
		fsConFolder($data['updir']);
		fsDeleteFile($data['updir'], $data['upfile'.$field]);
		if(($field == 1 && !$data['upfile2']) || ($field == 2 && !$data['upfile1'])) $pdo->query("delete from `$tbl[product_option_img]` where `no`='$data[no]'");
		else {
			$pdo->query("update `$tbl[product_option_img]` set `upfile{$field}`='', `w{$field}`='', `h{$field}`='' where `no`='$data[no]'");
		}
	}

	if($exec=='attach') {
		include_once $engine_dir.'/_engine/include/file.lib.php';
		include_once $engine_dir.'/_engine/include/ftp.lib.php';

		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		wingUploadRule($_FILES, 'prdBasic');

		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$pno'");
		$updir = $prd['up_dir'] ? $prd['up_dir'] : '_data/prd_option/'.date('Ym/d');
		makeFullDir($updir);

		if($delete1 == 'ok') optionAttachDel($opno, $idx, 1);
		if($delete2 == 'ok') optionAttachDel($opno, $idx, 2);

		for($i = 1; $i <= 2; $i++) {
			$file = $_FILES['upfile'.$i];
			if($file['size']) {
				$filename = "opt_".md5("{$pno}_{$opno}_{$idx}_{$i}_{$now}");
				$size = filesize($file['tmp_name']);
				list($width, $height) = getimagesize($file['tmp_name']);

				optionAttachDel($opno, $idx, $i);
				$upload = uploadFile($file, $filename, $updir);
				$filename = $upload[0];

				unlink($file['tmp_name']);

				if($pdo->row("select count(*) from `$tbl[product_option_img]` where `opno`='$opno' and `idx`='$idx'")) {
					$pdo->query("update `$tbl[product_option_img]` set `upfile{$i}`='{$filename}', `w{$i}`='$width', `h{$i}`='$height', `size{$i}`='$size' where `pno`='$pno' and `opno`='$opno' and `idx`='$idx'");
				} else {
					$pdo->query("insert into `$tbl[product_option_img]` (`pno`,`opno`,`idx`,`updir`,`upfile{$i}`,`w{$i}`,`h{$i}`,`size{$i}`,`reg_date`) values ('$pno','$opno','$idx','$updir','{$filename}','$width','$height','$size','$now')");
				}
			}
		}
		if($file) alert('옵션 부가사진 업로드가 완료되었습니다');

		javac('parent.optAttach()');
		exit;
	}

	if($exec=="delete") {
		if(!is_array($check_pno) && $opno) {
			$check_pno = array($opno);
			checkBlank($data[no],"필수값을 입력해주세요.");
		}
		foreach($check_pno as $opno) {
			// 윙POS 옵션 제거
			$ires = $pdo->iterator("select no, pno from {$tbl['product_option_item']} where opno='$opno'");
            foreach ($ires as $idata) {
				// 장바구니 삭제
				$complex_no = $pdo->row("select group_concat(complex_no) from erp_complex_option where del_yn='N' and opts like '%#_{$idata[no]}#_%' ESCAPE '#'");
				if($complex_no) {
					$pdo->query("delete from $tbl[cart] where complex_no in ($complex_no)");
				}
				$pdo->query("update `erp_complex_option` set `del_yn`='Y' where opts like '%#_{$idata[no]}#_%' ESCAPE '#'");

				$pno = $idata['pno'];
			}

			$pdo->query("delete from `$tbl[product_option_set]` where `no`='$opno'");
			$pdo->query("delete from `$tbl[product_option_item]` where `opno`='$opno'");

			if(isset($cfg['use_colorchip_cache']) == true || $cfg['use_colorchip_cache'] == 'Y') {
				makeColorchipCache($pno);
			}

			if($stat=="5") {
				$url="reload";
				$target="parent";
			}
			else {
				$prd=get_info($tbl[product],"no",$pno);
				if($prd['stat']==1) $stat=1;
				else $stat=2;
				$url="reload";
				$target='parent';
			}
		}
		msg("삭제되었습니다",$url,$target);
	}
	elseif($exec=="sort") {
		$sort = numberOnly($_POST['sort']);
		foreach($sort as $key => $opno) {
			$sort = ($key+1);
			$pdo->query("UPDATE {$tbl['product_option_set']} SET sort='$sort' where no='$opno'");
		}
		exit;
	}
	elseif($exec=="copy") {
		$pno = numberOnly($_POST['pno']);
		$check_pno = $_POST['check_pno'];
		$stat = numberOnly($_POST['stat']);

		if($stat != 5) {
			$sel = implode(',', $check_pno);
			$cnt1 = $pdo->row("select count(*) from `$tbl[product_option_set]` where `no` in ($sel) and how_cal in (3,4)");
			$cnt2 = $pdo->row("select count(*) from `$tbl[product_option_set]` where `pno`='$pno' and how_cal in (3,4)");
			if($cnt1+$cnt2 > 1) msg('한 상품에 면적옵션은 최대 1개 까지만 등록 가능합니다.');
		}
		for($ii=0, $total=count($check_pno); $ii<$total; $ii++) {
			$data=get_info($tbl[product_option_set],"no",$check_pno[$ii]);
			if(!$data[no]) msg("존재하지 않는 자료가 있습니다");

			$asql1 = $asql2 = '';
			if($data['how_cal'] == 3 || $data['how_cal'] == 4) {
				$asql1 .= ", `desc`";
				$asql2 .= ", '$data[desc]'";
			}
			if($cfg['use_partner_shop'] == 'Y') {
				$asql1 .= ", partner_no";
				$asql2 .= ", '$admin[partner_no]'";
			}
			$sql="INSERT INTO `".$tbl['product_option_set']."` (`name`,`necessary`,`otype`,`how_cal`,`unit`,`items`,`pno`,`stat`,`reg_date`,`deco1`,`deco2`,`deco_use`,`updir`,`upfile1` $asql1) VALUES ('$data[name]','$data[necessary]','$data[otype]','$data[how_cal]','$data[unit]','$data[items]','$pno','$stat','$now','$data[deco1]','$data[deco2]','$data[deco_use]','$data[updir]','$data[upfile1]' $asql2)";
			$pdo->query($sql);
			$opno = $pdo->lastInsertId();

			$res = $pdo->iterator("select * from `$tbl[product_option_item]` where `opno`='$check_pno[$ii]'");
            foreach ($res as $idata) {
				$asql3 = $asql4 = '';
				if($data['how_cal'] == 3 || $data['how_cal'] == 4) {
					$asql3 .= ", `add_price_option`, `min_area`, `min_area_option`, `max_val`, `min_val`";
					$asql4 .= ", '$idata[add_price_option]', '$idata[min_area]', '$idata[min_area_option]', '$idata[max_val]', '$idata[min_val]'";
				}
				if($cfg['use_option_product'] == 'Y' && $idata['complex_no'] > 0) {
					$asql3 .= ", complex_no";
					$asql4 .= ", '$idata[complex_no]'";
				}
				if($data['otype'] == "5A") {
					$asql3 .= ", chip_idx";
					$asql4 .= ", '$idata[chip_idx]'";
				}
				$pdo->query("insert into `$tbl[product_option_item]` (`pno`,`opno`, `iname`, `add_price`, hidden, `sort`, `reg_date` $asql3) values ('$pno','$opno', '$idata[iname]', '$idata[add_price]', '$idata[hidden]', '$idata[sort]', '$now' $asql4)");
			}
		}
		if(isset($cfg['use_colorchip_cache']) == true || $cfg['use_colorchip_cache'] == 'Y') {
			makeColorchipCache($pno);
		}
		if($stat==5) {
			msg("적용되었습니다","reload","parent");
		}
		else {
			msg("적용되었습니다","popup");
		}

	}
	elseif($exec == 'remove_item') {
		$no = numberOnly($_POST['no']);

		// 장바구니 삭제
		$complex_no = $pdo->row("select group_concat(complex_no) from erp_complex_option where del_yn='N' and opts like '%#_{$no}#_%' ESCAPE '#'");
		if($complex_no) {
			$pdo->query("delete from $tbl[cart] where complex_no in ($complex_no)");
		}
		// 컬러칩 캐시 수정
		if(isset($cfg['use_colorchip_cache']) == true || $cfg['use_colorchip_cache'] == 'Y') {
			makeColorchipCache($pdo->row("select pno from {$tbl['product_option_item']} where no='$no'"));
		}
		// 옵션 삭제
		$pdo->query("delete from `$tbl[product_option_item]` where `no`='$no'");
		// 재고 삭제
		$pdo->query("update `erp_complex_option` set `del_yn`='Y' where opts like '%#_{$no}#_%' ESCAPE '#'");
		exit("OK");
	}
	elseif($exec == 'initial_complex') {
		include_once $engine_dir.'/_engine/include/wingPos.lib.php';

		$pno = numberOnly($_POST['pno']);
		$complex_optno = $_POST['complex_optno'];
		$complex_no = $_POST['complex_no'];
		$barcode = $_POST['barcode'];
		$bstock = $_POST['bstock'];
		$force_soldout = $_POST['force_soldout'];
		$rurl = $_POST['rurl'];

		foreach($complex_optno as $key => $val) {
			if($complex_no[$key]) continue;
			if(!$barcode[$key]) $barcode[$key] = true;
			if($bstock[$key] === '') continue;

			createComplex($pno, $val, $barcode[$key], numberOnly($bstock[$key]), '기초재고 등록', $force_soldout[$key]);
		}

		if(count($_POST['multi_regist']) > 0) {
			include 'product_export.exe.php';
		}

		if($rurl == 'session_reload') {
			msg('', 'reload', 'parent');
		} else {
			msg('', $rurl, 'parent.parent');
		}
		exit;
	}
	elseif($exec == 'getColorChipList') {
		printAjaxHeader();

		$res = $pdo->iterator("select no, name from $tbl[product_option_colorchip] order by name asc");
        foreach ($res as $data) {
			$data['name'] = stripslashes($data['name']);
			$selected = ($_POST['value'] == $data['name']) ? 'selected' : '';
			echo "<option value='$data[no]' $selected>$data[name]</option>";
		}
		exit;
	}
	elseif($exec == 'sortItem') {
		$direction = ($_POST['direction'] > 0) ? '>' : '<';
		$order = ($_POST['direction'] > 0) ? 'sort asc' : 'sort desc';
		$item_no = numberOnly($_POST['item_no']);

		$item_o = $pdo->assoc("select no, pno, opno, sort from $tbl[product_option_item] where no='$item_no'");
		$pno = $item_o['pno'];
		$opno = $item_o['opno'];

		$item_n = $pdo->assoc("select no, sort from $tbl[product_option_item] where opno='$opno' and sort $direction $item_o[sort] order by $order limit 1");
		$pdo->query("update $tbl[product_option_item] set sort='$item_n[sort]' where no='$item_o[no]'");
		$pdo->query("update $tbl[product_option_item] set sort='$item_o[sort]' where no='$item_n[no]'");

		// 컬러칩 캐시 수정
		if(isset($cfg['use_colorchip_cache']) == true || $cfg['use_colorchip_cache'] == 'Y') {
			makeColorchipCache($pno);
		}

		include 'product_option_item.frm.php';

		exit;
	}

	$pno = numberOnly($_POST['pno']);
	$name = addslashes(del_html($_POST['name']));
	$otype = addslashes(trim($_POST['otype']));
	$unit = addslashes(del_html($_POST['unit']));
	$desc = addslashes(del_html($_POST['desc']));
	$how_cal = numberOnly($_POST['how_cal']);
	$necessary = addslashes($_POST['necessary']);
	$ino = array_map('numberOnly', $_POST['ino']);
	$item = $_POST['item'];
	$colorchip = $_POST['colorchip'];
	$stat = numberOnly($_POST['stat']);
	$complex_no = numberOnly($_POST['complex_no']);
	$deco_use = ($_POST['deco_use'] == '1') ? '1' : '';
	$deco1 = addslashes($_POST['deco1']);
	$deco2 = addslashes($_POST['deco2']);

	checkBlank($name,"옵션명을 입력해주세요.");
	checkBlank($otype,"속성(2)을 입력해주세요.");

	// 삭제된 아이템의 부가사진 삭제
	$total_item = count($item);
	$sql = $pdo->iterator("select `opno`,`idx` from `$tbl[product_option_img]` where `opno`='$opno' and `idx` >= $total_item");
    foreach ($sql as $idata) {
		optionAttachDel($idata['opno'], $idata['idx'],1);
		optionAttachDel($idata['opno'], $idata['idx'],2);
	}

	$opt_q=""; $opt_q1=""; $opt_q2="";
	$_item_str="";
	$_item_ea=0; // 옵션수량 총합계를 체크
	$_ea_str=""; // 수량필드에 '@' 로 구분하여 업데이트
	$ick=0;
	foreach ($item as $key => $val) {
		if ($item[$key]) {
			$_item_str.=$item[$key];
			if ($item_ea[$key]){
				$_ea_str.=$item_ea[$key];
				$_item_ea += $item_ea[$key];
			}
			if ($item_price[$key]) {
				$_item_str.="::".$item_price[$key];
			}
			$_item_str.="@";
			$_ea_str.="@";
			$ick++;
		}
		if ($necessary == 'P' && !$complex_no[$key]) {
			msg(($ick).'번째 옵션에 부속상품을 선택해 주세요.');
		}
	}
	$_item_str = addslashes($_item_str);
	if($ick==0) msg("하나 이상의 옵션 항목을 입력하세요");

	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	wingUploadRule($_FILES, 'prdOption');

	$ii=1;

	if($data['upfile1'] && !$data['updir']) $data['updir'] = $dir['upload'].'/prd_common';
	$updir = $data['updir'] ? $data['updir'] : '_data/prd_option/'.date('Ym/d');

	$up_filename=$data['upfile'.$ii];
	if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
		$used=$pdo->row("select count(*) from `$tbl[product_option_set]` where `upfile1`='$data[upfile1]' and `no` != '$data[no]'");
		if(!$used) deletePrdImage($data,1,1);
		$up_filename="";
		$opt_q .= ", `upfile1`=''";
	}
	if($_FILES['upfile'.$ii][tmp_name]) {
    	makeFullDir($updir);

		$up_filename=md5($ii+time()); // 새파일명
		$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
		$up_filename=$up_info[0];
		$opt_q .= ",`updir`='$updir', `upfile1`='$up_filename'";
		$opt_q1 .= ", `upfile1`";
		$opt_q2 .= ", '$up_filename'";
	}

	if($how_cal == '3' || $how_cal == '4') {
		if(!fieldExist($tbl['product_option_item'], 'max_val')) {
			addField($tbl['product_option_item'], 'max_val', 'int(10)');
			addField($tbl['product_option_item'], 'min_val', 'int(10)');
			addField($tbl['product_option_item'], 'add_price_option', 'int(10)');
			addField($tbl['product_option_item'], 'min_area', 'int(10)');
			addField($tbl['product_option_item'], 'min_area_option', 'enum("N","Y") default "N"');
			$pdo->query("alter table `$tbl[product_option_set]` change `how_cal` `how_cal` enum('1', '2', '3', '4') not null default '1' ");
		}
	}
	if(!fieldExist($tbl['product_option_set'], 'desc')) addField($tbl['product_option_set'], 'desc', 'text');

	if($data[no]) {
		if($necessary == 'N' && $data['necessary'] == 'Y') { // 윙포스 옵션 삭제
			$ires = $pdo->iterator("select no from `$tbl[product_option_item]` where `opno`='$data[no]'");
            foreach ($ires as $idata) {
				$pdo->query("update erp_complex_option set del_yn='Y' where opts like '%#_{$idata[no]}#_%' ESCAPE '#'");
			}
		}

		$sql="update `".$tbl['product_option_set']."` set `name`='$name', `necessary`='$necessary', `how_cal`='$how_cal', `otype`='$otype', `unit`='$unit', `items`='$_item_str', `deco1`='$deco1', `deco2`='$deco2', `deco_use`='$deco_use', `desc`='$desc' $opt_q where `no`='$data[no]'";
		$pdo->query($sql);

		$msg = '옵션이 수정되었습니다.';
		$url = 'popup';
		if($ea_ck) $target = 'parent.opener.parent';
	}
	else {
		if($cfg['use_partner_shop'] == 'Y') {
			$opt_q1 .= ", partner_no";
			$opt_q2 .= ", '$admin[partner_no]'";
		}

		$sql="INSERT INTO `".$tbl['product_option_set']."` (`name`,`necessary`,`otype`,`how_cal`,`unit`,`items`,`pno`,`stat`, `reg_date`,`deco1`,`deco2`,`deco_use`,`updir`, `desc` $opt_q1) VALUES ('$name', '$necessary', '$otype', '$how_cal', '$unit', '$_item_str', '$pno', '$stat', '$now', '$deco1', '$deco2', '$deco_use', '$updir', '$desc' $opt_q2)";
		$pdo->query($sql);

		$opno = $pdo->lastInsertId();

		$msg = '옵션이 추가되었습니다';
		if($stat=="5") { // 세트
			$url="reload";
			$target="parent";
		}
		else {
			$url="popup";
			$target="";
		}
		if($necessary == 'Y') {
			$pdo->query("update erp_complex_option set del_yn='Y' where pno='$pno'");
		}
	}

	foreach($item as $key => $iname) {
		$asql = $asql1 = $asql2 = '';

		$item_idx = $ino[$key];
		$iname = addslashes(trim($iname));
		$item_price[$key] = numberOnly($_POST['item_price'][$key], true);
		$item_hidden[$key] = ($_POST['item_hidden'][$key] == 'Y') ? 'Y' : 'N';

		if($how_cal == '3' || $how_cal == '4') {
			$max_val[$key] = numberOnly($_POST['max_val'][$key]);
			$min_val[$key] = numberOnly($_POST['min_val'][$key]);
			$add_price_option = $_POST['add_price_option'];
			$min_area = numberOnly($_POST['min_area']);
			$min_area_option = $_POST['min_area_option'];

			$asql  .= ",max_val='$max_val[$key]', min_val='$min_val[$key]'";
			$asql1 .= ",max_val, min_val";
			$asql2 .= ",'$max_val[$key]', '$min_val[$key]'";

            $item_price[$key] = numberOnly($_POST['add_price'], true);
		}

		if($otype == '5A') {
			$colorchip[$key] = addslashes($colorchip[$key]);
			$iname = addslashes(trim($pdo->row("select name from $tbl[product_option_colorchip] where no='$colorchip[$key]'")));
			$asql  .= ", chip_idx='$colorchip[$key]'";
			$asql1 .= ", chip_idx";
			$asql2 .= ", '$colorchip[$key]'";
		} else {
		    $asql  .= ", chip_idx=''";
		}

		if($iname == '') continue;

		if($cfg['use_option_product'] == 'Y') {
			$asql  .= " , complex_no='$complex_no[$key]'";
			$asql1 .= ", complex_no";
			$asql2 .= ", '$complex_no[$key]'";
		}

		if($item_idx) $pdo->query("update `$tbl[product_option_item]` set `iname`='$iname', `add_price`='$item_price[$key]', hidden='$item_hidden[$key]', `sort`='$key', `add_price_option`='$add_price_option', `min_area`='$min_area', `min_area_option`='$min_area_option' $asql where `no`='$item_idx'");
		else $pdo->query("insert into `$tbl[product_option_item]` (`pno`, `opno`, `iname`, `add_price`, `hidden`, `sort`, `reg_date`, `add_price_option`, `min_area`, `min_area_option` $asql1) values ('$pno','$opno', '$iname', '$item_price[$key]', '$item_hidden[$key]', '$key', '$now', '$add_price_option', '$min_area', '$min_area_option' $asql2)");
	}

	// 컬러칩 캐시 수정
	if(isset($cfg['use_colorchip_cache']) == true || $cfg['use_colorchip_cache'] == 'Y') {
		makeColorchipCache($pno);
	}

	msg($msg ,$url, $target);

?>