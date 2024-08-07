<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사은품 관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	if(isset($_REQUEST['exec']) == true){
		switch($_REQUEST['exec']){
			case 'getPrdName' :
				include_once $engine_dir.'/_engine/include/wingPos.lib.php';

				$complex_no = numberOnly($_GET['complex_no']);
				$prd = $pdo->assoc("select p.name, c.opts from $tbl[product] p inner join erp_complex_option c on p.no=c.pno where c.complex_no='$complex_no'");

				echo stripslashes(trim($prd['name']));
				if($prd['opts']) echo ' '.getComplexOptionName($prd['opts']);
				exit;
			break;
			case 'toggle' :
				$no = numberOnly($_POST['no']);
				$use_gift = $pdo->row("select `use` from $tbl[product_gift] where no='$no'");
				$use_gift = ($use_gift == 'Y') ? 'N' : 'Y';
				$pdo->query("update $tbl[product_gift] set `use`='$use_gift' where no='$no'");

				header('Content-type:application/json;');
				exit(json_encode(array(
					'changed' => $use_gift,
				)));
			break;
		}
		msg("작업이 완료되었습니다","reload","parent");
	}

	checkBasic();
	$gno = numberOnly($_POST['gno']);
	if($gno > 0) {
		$data = $pdo->assoc("select * from $tbl[product_gift] where no='$gno'");
		checkBlank($data['no'], '원본 자료를 입력해주세요.');
	}

	if(!$data['no'] && !$_FILES["upfile"]['tmp_name']) {
		msg("사진을 등록해주세요.");
	}

	if($_FILES['upfile']['tmp_name']) {
		$updir = $dir['upload']."/".$dir['gift']."/".date("Ym",$now);
		makeFullDir($updir);

		$up_filename = md5($now);
		$up_info = uploadFile($_FILES["upfile"],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
	}

	$name = addslashes($_POST['name']);
	$content = addslashes($_POST['content']);
	$complex_no = numberOnly($_POST['complex_no']);
	$price_limit = parsePrice($_POST['price_limit']);
	$price_max = parsePrice($_POST['price_max']);
	$attach_type = numberOnly($_POST['attach_type']);
	$attach_items = addslashes($_POST['attach_items_'.$attach_type]);
	$partner_type = numberOnly($_POST['partner_type']);
	$partner_no = numberOnly($_POST['partner_no']);
	$order_gift_member = addslashes($_POST['order_gift_member']);
	$order_gift_first = addslashes($_POST['order_gift_first']);
	$use = ($_POST['use'] == 'Y') ? 'Y' : 'N';
	$sdate = ($_POST['sdate']) ? strtotime($_POST['sdate']) : 0;
	$edate = ($_POST['edate']) ? strtotime($_POST['edate'])+86399 : 0;
	$delfile = $_POST['delfile'];
	if($_POST['no_date'] == 'Y') {
		$sdate = 0;
		$edate = 0;
	}

	if(!fieldExist($tbl['product_gift'], 'complex_no')) {
		addField($tbl['product_gift'], 'complex_no', "int(10) not null default 0");
		$pdo->query("alter table $tbl[product_gift] add index complex_no (complex_no)");

		addField($tbl['product_gift'], 'sdate', "int(10) not null default 0");
		addField($tbl['product_gift'], 'edate', "int(10) not null default 0");
		$pdo->query("alter table $tbl[product_gift] add index use_date (sdate, edate)");
	}
	if(addField($tbl['product_gift'], 'attach_type', 'char(1) default "0"') == true) {
		addField($tbl['product_gift'], 'attach_items', 'text default ""');
		addField($tbl['product_gift'], 'partner_type', 'char(1) default "0"');
	}
	addField($tbl['product_gift'], 'partner_no', 'int(10) not null default "0"');
	if(addField($tbl['product_gift'], 'price_max', 'double(10,2) not null default "0" COMMENT "증정조건" after price_limit') == true) {
		$pdo->query("
			ALTER TABLE `{$tbl['product_gift']}`
				CHANGE COLUMN `price_limit` `price_limit` DOUBLE(10,2) NOT NULL DEFAULT 0 COMMENT '증정조건';
		");
	}

	if($data['no']) {
		if($up_info[0] || $delfile) { // 파일 삭제
			deleteAttachFile($data[updir], $data[upfile]);
			$asql=",`updir`='$updir', `upfile`='$up_info[0]'";
		}
		$sql="update `".$tbl['product_gift']."` set `name`='$name', `content`='$content', `price_limit`='$price_limit', price_max='$price_max', attach_type='$attach_type', attach_items='$attach_items', partner_type='$partner_type', partner_no='$partner_no', `use`='$use', complex_no='$complex_no', sdate='$sdate', edate='$edate', order_gift_member='$order_gift_member', order_gift_first='$order_gift_first' $asql where `no`='$gno'";
		$pdo->query($sql);

        $rURL=getListURL('prdgiftList');
        if(!$rURL) $rURL='./?body=promotion@product_gift_list';

		msg("사은품이 수정되었습니다", $rURL, "parent");
	}
	else {
		$sql="INSERT INTO `".$tbl['product_gift']."` ( `name` , `updir` , `upfile` , `content` , `reg_date` ,`price_limit`, price_max, attach_type, attach_items, partner_type, partner_no, `use`, complex_no, sdate, edate, `order_gift_member`, `order_gift_first`) VALUES ('$name','$updir','$up_info[0]','$content','$now','$price_limit', '$price_max', '$attach_type', '$attach_items', '$partner_type', '$partner_no', '$use', '$complex_no', '$sdate', '$edate', '$order_gift_member', '$order_gift_first')";
		$pdo->query($sql);

		msg("사은품이 추가되었습니다","./?body=promotion@product_gift_list","parent");
	}

?>