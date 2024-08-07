<?PHP

	printAjaxHeader();

	switch($_REQUEST['exec']) {
		case 'getStorageName' :
			$level = numberOnly($_GET['level']);
			$nlevel = $level+1;
			$_name = ${'name_'.$level};

			if($level > 0) $w .= " and name{$level}='$_name'";

			$res = $pdo->iterator("select distinct name{$nlevel} from $tbl[erp_storage] where 1 $w order by name{$nlevel} asc");
            foreach ($res as $data) {
				$nm = stripslashes($data['name'.$nlevel]);
				echo "<option value='$nm'>$nm</option>";
			}
			exit;
		break;
		case 'move' :
			$obig = numberOnly($_POST['obig']);
			$omid = numberOnly($_POST['omid']);
			$osmall = numberOnly($_POST['osmall']);
			$odepth4 = numberOnly($_POST['odepth4']);
			$nbig = numberOnly($_POST['nbig']);
			$nmid = numberOnly($_POST['nmid']);
			$nsmall = numberOnly($_POST['nsmall']);
			$ndepth4 = numberOnly($_POST['ndepth4']);

			$ori = $pdo->row("select no from $tbl[erp_storage] where big='$obig' and mid='$omid' and small='$osmall' and depth4='$odepth4'");
			$new = $pdo->row("select no from $tbl[erp_storage] where big='$nbig' and mid='$nmid' and small='$nsmall' and depth4='$ndepth4'");
			if(!$ori) msg('입력한 위치에 해당하는 원본 창고가 등록되지 않았습니다.');
			if(!$new) msg('입력한 위치에 해당하는 이전처 창고가 등록되지 않았습니다.');

			$pdo->query("update $tbl[product] set storage_no='$new[no]' where storage_no='$ori[no]'");
			msg('', 'reload', 'parent');
		break;
		case 'remove' :
			foreach($_POST['no'] as $val) {
				$val = numberOnly($val);
				if(!$val) continue;
				$pdo->query("delete from $tbl[erp_storage] where no='$val'");
				$pdo->query("update $tbl[product] set storage_no='0' where storage_no='$val'");
			}
			msg('', 'reload', 'parent');
		break;
		case 'searchBarcode' :
			header('Content-type:application/json; charset='._BASE_CHARSET_);

			$barcode = trim(addslashes($_GET['barcode']));
			$pno = numberOnly($_GET['pno']);

			if($pno > 0) {
				$prd = $pdo->assoc("select p.no, p.name, p.hash, p.updir, p.upfile3, p.sell_prc from $tbl[product] p where p.no='$pno'");
			} else {
				$prd = $pdo->assoc("select p.no, p.name, p.hash, p.updir, p.upfile3, p.sell_prc from erp_complex_option c inner join $tbl[product] p on c.pno=p.no where 1 and c.barcode='$barcode'");
			}

			if($prd['no'] < 1) {
				exit(json_encode(array('result'=>'notFound')));
			} else {
				$prd['result'] = 'success';
				$prd['pno'] = $prd['no'];
				$prd['mng_link'] = './index.php?body=product@product_register&no='.$prd['pno'];
				$prd['front_link'] = $root_ur.'/shop/detail.php?pno='.$prd['hash'];
				$prd['thumb'] = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile3'];
				$prd['price'] = parsePrice($prd['sell_prc'], true).' '.$cfg['currency'];
				$prd['name'] = stripslashes($prd['name']);

				exit(json_encode($prd));
			}
		break;
		case 'getStorageNo' :
			header('Content-type:application/json; charset='._BASE_CHARSET_);

			$big = numberOnly($_GET['big']);
			$mid = numberOnly($_GET['mid']);
			$small = numberOnly($_GET['small']);
			$depth4 = numberOnly($_GET['depth4']);
			if(!$mid) $mid = 0;
			if(!$small) $small = 0;
			if(!$depth4) $depth4 = 0;

			$storage = $pdo->assoc("select * from $tbl[erp_storage] where big='$big' and mid='$mid' and small='$small' and depth4='$depth4'");
			$storate['no'] = $storate['no'];
			$storate['name'] = stripslashes($storate['name']);
			$storage['location'] = getStorageLocation($storage);

			exit(json_encode($storage));
		break;
		case 'storage_in' :
			$pno = $_POST['pno'];
			$storage_no = $_POST['storage_no'];

			if(count($pno) == 0) msg('배치할 상품을 선택해주세요.');
			if(count($pno) != count($storage_no)) msg('창고가 설정되지 않은 상품정보가 있습니다.');

			foreach($pno as $key => $_pno) {
				$_pno = numberOnly($_pno);
				$_storage_no = numberOnly($storage_no[$key]);
				$pdo->query("update $tbl[product] set storage_no='$_storage_no' where no='$_pno'");
			}
			msg('바코드 창고 배치가 완료되었습니다.');
		break;
		case 'createStorage' :
			function loopStorage($parent = 0, $level = 1, $chain = array()) {
				global $tbl, $_cate_colname, $now, $pdo;

				if($level < 5) {
					if($level > 1) $w = " and ".$_cate_colname[1][($level-1)]."='$parent'";
					$res = $pdo->iterator("select no from {$tbl['category']} where ctype=9 and level=$level $w order by sort asc");
				}
				if($level < 5 && $res->rowCount() > 0) {
                    foreach ($res as $data) {
						$_tmp = $chain;
						$_tmp[$level] = $data['no'];
						loopStorage($data['no'], $level+1, $_tmp);
					}
				} else {
					$r = $pdo->query("
						insert into {$tbl['erp_storage']} (big, mid, small, depth4, reg_date) values ('$chain[1]', '$chain[2]', '$chain[3]', '$chain[4]', '$now')
					");
					if($r) $GLOBALS['cnt']++;
				}
			}

			$cnt = 0;
			loopStorage();

			header('Content-type:application/json;');
			exit(json_encode(array('result' => $cnt, 'message' => $cnt.'개의 창고가 생성되었습니다.')));
		break;
	}

	$no = numberOnly($_POST['no']);
	$sbig = numberOnly($_POST['sbig']);
	$smid = numberOnly($_POST['smid']);
	$ssmall = numberOnly($_POST['ssmall']);
	$sdepth4 = numberOnly($_POST['sdepth4']);
	$name = addslashes(trim($_POST['name']));
	$dam = addslashes(trim($_POST['dam']));
	$content = addslashes(trim($_POST['content']));

	if($no > 0) {
		$data = $pdo->assoc("select * from $tbl[erp_storage] where no='$no'");
	}

	checkBlank($sbig, '창고위치 대분류를 선택해주세요.');

	$updir = $data['updir'];
	for($i = 1; $i <= 2; $i++) {
		$file = $_FILES['upfile'.$i];

		if($updir && ($_POST['delfile'.$i] == 'Y' || $file['size'] > 0)) {
			deletePrdImage($data, $i, $i);
			$asql .= " , `upfile{$i}`=''";
		}

		if($file['size'] > 0) {
			$img = getimagesize($file['tmp_name']);

			if(!$updir) {
				$updir = $dir['upload']."/erp_storage/".date("Ym/d",$now);
				makeFullDir($updir);
				$asql .= " , `updir`='$updir'";
			}

			$up_filename = md5($i+time()); // 새파일명
			$up_info = uploadFile($file, $up_filename, $updir, "jpg|jpeg|gif|png");
			$up_filename = $_up_filename[$i] = $up_info[0];
			$asql .= " , `upfile{$i}`='$up_filename'";
		}
	}

	if($no > 0) {
		$pdo->query("
			update $tbl[erp_storage] set
				big='$sbig', mid='$smid', small='$ssmall', depth4='$sdepth4', name='$name',
				dam='$dam', content='$content' $asql
			where no='$no'
		");
	} else {
		$pdo->query("
			insert into $tbl[erp_storage]
				(big, mid, small, depth4, name, dam, content, reg_date, updir, upfile1, upfile2)
				values
				('$sbig', '$smid', '$ssmall', '$sdepth4', '$name', '$dam', '$content', '$now', '$updir', '$_up_filename[1]', '$_up_filename[2]')
		");
	}

	// 창고 사용 함
	$pdo->query("insert into $tbl[config] (name, value, reg_date) values ('use_erp_storage', 'Y', $now)");

	msg('', getListURL('?body=erp@storage'), 'parent');

?>