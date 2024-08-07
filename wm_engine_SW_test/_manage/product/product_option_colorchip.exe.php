<?PHP

	switch($_POST['exec']) {
		case 'add' :
			addField($tbl['product_option_colorchip'], 'type', 'enum("file", "code") not null default "file"');
			addField($tbl['product_option_colorchip'], 'code', 'varchar(7) not null default ""');

			foreach($_POST['name'] as $key => $val) {
				if(empty($val) == true) {
					msg('컬러칩 이름을 입력해주세요.');
				}
				$_type = ($_POST['type'][$key] == 'file') ? 'file' : $_POST['type'][$key];
				/*
				if($_type == 'file' && $_FILES['upfile1']['size'][$key] == 0) {
					msg('컬러칩 이미지를 업로드해주세요.');
				}
				*/
				if($_type == 'code' && empty($_POST['code'][$key]) == true) {
					msg('컬러코드를 입력해주세요.');
				}
			}
			foreach($_POST['name'] as $key => $val) {
				$_name = trim(addslashes($val));
				$_type = ($_POST['type'][$key] == 'file') ? 'file' : $_POST['type'][$key];
				$_no = numberOnly($_POST['no'][$key]);
				$_file = array(
					'tmp_name' => $_FILES['upfile1']['tmp_name'][$key],
					'name' => $_FILES['upfile1']['name'][$key],
					'size' => $_FILES['upfile1']['size'][$key],
				);
				$_code = addslashes($_POST['code'][$key]);

				$data = $pdo->assoc("select updir, upfile1 from $tbl[product_option_colorchip] where no='$_no'");
				if ($_type == 'file' && $_file['size'] > 0) {
					if($_no > 0) {
						deleteAttachFile($data['updir'], $data['upfile1']);
					}
					$updir = $dir['upload'].'/product/colorchip';
					makeFullDir($updir);

					list($w, $h) = getImagesize($_file['tmp_name']);
					$up_info = uploadFile($_file, md5($_name.$now), $updir, 'jpg|jpeg|gif|png');
					$asql .= ", upfile1='$up_info[0]', w1='$w', h1='$h', updir='$updir'";
				} else if($_type == 'code' && $data['upfile1']) {
					deletePrdImage($data,1,1);
					$asql .= ", upfile1='', w1='0', h1='0', updir=''";
				}

				if($_no > 0) {
					$pdo->query("update $tbl[product_option_colorchip] set name='$_name', type='$_type', code='$_code' $asql where no='$_no'");
					if($pdo->lastRowCount() > 0) {
						$pdo->query("update $tbl[product_option_item] set iname='$_name' where chip_idx='$_no'");
					}
				} else {
					$pdo->query("insert into $tbl[product_option_colorchip] (name, type, updir, upfile1, w1, h1, code, reg_date) values ('$_name', '$_type', '$updir', '$up_info[0]', '$w', '$h', '$_code', '$now')");
				}

				$asql = '';
			}

			javac("parent.reloadChips()");
		break;
		case 'remove' :
			$no = numberOnly($_POST['no']);
			$data = $pdo->assoc("select updir, upfile1 from $tbl[product_option_colorchip] where no='$no'");
			deleteAttachFile($data['updir'], $data['upfile1']);

			$pdo->query("delete from $tbl[product_option_colorchip] where no='$no'");
			$res = $pdo->iterator("select no from $tbl[product_option_item] where chip_idx='$no'");
            foreach ($res as $data) {
				$pdo->query("delete from $tbl[product_option_item] where no='$data[no]'");
				$pdo->query("update erp_complex_option set del_yn='Y' where opts like '%_$data[no]_%' ESCAPE '#'");
			}

			include 'product_option_colorchip.php';
			exit;
		break;
		case 'reload' :
			include 'product_option_colorchip.php';
			exit;
		break;
		case 'migration' :
			if($_POST['use_colorchip_cache'] == 'Y') {
				if(fieldExist($tbl['product'], 'colorchip_cache') == false) {
					if(addField($tbl['product'], 'colorchip_cache', 'varchar(200) not null') == false) {
						msg('상품 데이터베이스를 수정할수 없습니다.');
					}
				}

				$res = $pdo->iterator("select distinct pno from {$tbl['product_option_item']} where chip_idx > 0");
                foreach ($res as $data) {
					makeColorchipCache($data['pno']);
				}
			}

			require $engine_dir.'/_manage/config/config.exe.php';
		break;
	}

?>