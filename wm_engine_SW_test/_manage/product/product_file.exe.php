<?PHP

	if($_POST['uptype'] != 'swf') $form_mode = 'http';

	if($cfg['use_icb_storage'] == 'Y') {
		$dir['upload'] = $cfg['current_icb_updir'];
		$asql  = ", upurl";
		$asql2 = ", '$cfg[current_icb_upurl]'";
	}

	$pno = numberOnly($_POST['pno']);
	$filetype = numberOnly($_POST['filetype']);

    if (count($_POST) == 0) {
        exit('업로드 중 오류가 발생하였습니다.');
    }

	if(!$pno) msg("잘못된 접속입니다");
	$prd = get_info($tbl['product'], "no", $pno);
	$stat = ($prd['stat'] == 1) ? 1 : 2;
	$updir = $dir['upload']."/".$dir['attach']."/".date("Ym",$now)."/".date("d",$now);
	$completed = array();

	$file_server_num = fsConFolder($updir);
	if($file_server_num){
		$file_dir = $file_server[$file_server_num]['url'];
		$file_dir = $file_server[1]['url']; // 2008-09-17 : 파일서버 설정 - Han
	} else {
		$file_dir = $root_url;
	}
	makeFullDir($updir);

	// 업로드 제한
	include $GLOBALS['engine_dir'].'/_config/set.upload.php';
	$ea = $pdo->row("select count(*) from `$tbl[product_image]` where `pno` = '$pno' and `filetype` = '$filetype'");
	$totalsize = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where `filetype` in (2,3,6)");
	wingUploadRule($_FILES, 'prdContent', $ea, $totalsize, $filetype);

	if($filetype == 2 && $cfg['up_aimg_sort'] == "Y" && fieldExist($tbl['product_image'], "sort")) {
		$sort = $pdo->row("select max(`sort`) FROM `{$tbl['product_image']}` where `pno` = '$pno'") + 1;
		$asql  .= ", `sort`";
		$asql2 .= ", '$sort'";
	}

	if($filetype == 4) {
		$ino = numberOnly($_POST['ino']);
		$asql  .= ", option_item_no";
		$asql2 .= ", '$ino'";
	}

    if ($_POST['base64']) { // 붙여넣기 및 드래그
        $_FILES[0] = array('base64' => $_POST['base64']);
    }

	foreach($_FILES as $ii => $upfile) {
        if ($upfile['base64']) {
            $tmp = explode(';base64,', $upfile['base64']);
            $ext = str_replace('data:image/', '', $tmp[0]);
            if ($ext == 'jpeg') $ext = 'jpg';
            $upfile['name'] = md5(microtime(true)).'.'.$ext;
            $upfile['tmp_name'] = $root_dir.'/_data/'.$upfile['name'];
            $upfile['size'] = strlen($tmp[1]);
            $file_content = base64_decode($tmp[1]);

            if (function_exists('getimagesizefromstring') == true) {
                $check = getimagesizefromstring($file_content); // 이미지만 업로드 가능
                if ($check == false) {
                    msg('잘못된 파일형식입니다.');
                }
            }

            fwriteTo('/_data/'.$upfile['name'], $file_content);
        }

        $up_filename = md5($upfile['tmp_name'].time());
        if(!$upfile['tmp_name']) continue;

		list($width, $height) = getimagesize($upfile['tmp_name']);
        if(!$width || !$height) {
            unlink($upfile['tmp_name']);
            exit('이미지만 업로드 할수있습니다');
        }
		$up_info = uploadFile($upfile, $up_filename, $updir, "jpg|jpeg|gif|png|bmp|swf|flv", $upfile_size_limit);
		if(empty($upload_one)) $up_info[1] = iconv("UTF-8", _BASE_CHARSET_, $up_info[1]);
		$up_filename = $up_info[0];
		$ofilename = $up_info[1];
		$filesize = $upfile['size'];
        unlink($upfile['tmp_name']);

		$sql = "INSERT INTO `".$tbl['product_image']."` ( `pno` , `updir` , `filename` , `ofilename` , `stat` , `reg_date` , `width` , `height` , `filetype`, `filesize`$asql) VALUES  ( '$pno' , '$updir' , '$up_filename' , '$ofilename' , '$stat' , '$now' , '$width', '$height', '$filetype', '$filesize'$asql2)";
		$pdo->query($sql);

		$completed[] = array(
            'no' => $pdo->lastInsertId(),
            'name' => $file_dir.'/'.$updir.'/'.$up_filename
        );
	}

	if($_POST['from_ajax'] == 'true') {
		header('Content-type:application/json');
		exit(json_encode(array("count"=>count($completed), "files"=>$completed)));
	}

	if($form_mode == 'http') {
		msg('', 'reload', 'parent');
	}

?>