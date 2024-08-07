<?PHP

	if($_POST['uptype'] != 'swf') $form_mode = 'http';

	// 업로드 제한
	include_once $engine_dir.'/_manage/product/product_wdisk.inc.php';
	$home_dir = $wdisk['home_dir'];
	$wec->service = 'account';

	include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
	include_once $engine_dir.'/_config/set.upload.php';

	if($_REQUEST['ea']) $ea = $_REQUEST['ea'];
	if(!$ea) $ea = null;

	$pno = numberOnly($_POST['pno']);
	$filetype = numberOnly($_POST['filetype']);

	wingUploadRule($_FILES, 'prdContent', $ea, $wdisk[0]->img_used[0], $filetype);

	if($filetype == 8 && $cfg['up_aimg_sort'] == "Y" && fieldExist($tbl['product_image'], "sort")) {
		$sort = $pdo->row("select max(`sort`) FROM `{$tbl['product_image']}` where `pno` = '$pno'") + 1;
		$asql = ", `sort`";
		$asql2 = ", '$sort'";
	}

	// 업로드
	$dir = '/__manage__/product_'.$pno;
	$limit_size = $wdisk[0]->img_limit[0] * 1024 * 1024;

    if ($_POST['base64']) { // 붙여넣기 및 드래그
        $_FILES[0] = array('base64' => $_POST['base64']);
    }

	foreach($_FILES as $key => $val) {
		$file = $_FILES[$key];

        if ($file['base64']) {
            $tmp = explode(';base64,', $file['base64']);
            $ext = str_replace('data:image/', '', $tmp[0]);
            if ($ext == 'jpeg') $ext = 'jpg';
            $file['name'] = md5(microtime(true)).'.'.$ext;
            $file['tmp_name'] = $root_dir.'/_data/'.$file['name'];
            $file['size'] = strlen($tmp[1]);
            $file_content = base64_decode($tmp[1]);

            if (function_exists('getimagesizefromstring') == true) {
                $check = getimagesizefromstring($file_content); // 이미지만 업로드 가능
                if ($check == false) {
                    msg('잘못된 파일형식입니다.');
                }
            }

            fwriteTo('/_data/'.$file['name'], $file_content);
        }

		$ext = getExt($file['name']);
		if($file['size'] == 0) msg("파일사이즈가 0 입니다");
		if($limit_size > 0 && ($wdisk[0]->img_used[0]+$file['size']) > $limit_size) msg('계정 용량이 초과되었습니다');
		$img = getimagesize($file['tmp_name']);
		if ($ext != 'flv' && (!$img[2] || $img[2] > 4)) msg('업로드 가능한 파일 형식은 GIF, JPG, PNG, SWF, FLV 입니다\nBMP 등의 다른 포맷은 업로드 하실수 없습니다');

		$thumb = md5($file['name'].$key.$now).'.gif';

		if($img[0] > 60 || $img[1] > 60) $imagic_option = '-resize 60x60';
		//shell_exec("/usr/bin/convert {$imagic_option} {$file['tmp_name']} {$root_dir}/_data/{$thumb}");

		$osize = $wdisk_con->filesize($dir.'/'.$file['name']);
		if($osize < 2) $osize = 0;

		$file['name'] = md5($file['name'].$key.$now).'.'.$ext;
		$wdisk_con->upload($file['tmp_name'], $dir.'/'.$file['name']);
		//$wdisk_con->upload($root_dir.'/_data/'.$thumb, '_thumb_'.$dir.'/'.$thumb);
		//unlink($root_dir.'/_data/'.$thumb);

		$size = $file['size'] - $osize;
		if(is_object($wec_account)) $wec_account->queue('wdiskSize', $wec_account->config['account_idx'], 'upload', $size);

		if(is_object($wec_account)) {
			$wec_account->call('setFileUpload', array('filepath'=>$home_dir.$dir.'/'.$file['name'], 'member_id'=>$GLOBALS['member']['member_id'], 'admin_id' => $GLOBALS['admin']['admin_id']));
			$wec_account->call('setFileUpload', array('filepath'=>$home_dir.'/_thumb_'.$dir.'/'.$thumb, 'member_id'=>$GLOBALS['member']['member_id'], 'admin_id' => $GLOBALS['admin']['admin_id']));
		}

		$sql="INSERT INTO `".$tbl['product_image']."` (`pno`, `updir`, `filename`, `ofilename`, `stat`, `reg_date`, `width`, `height`, `filetype`, `filesize`$asql) values ('$pno', '$dir', '$file[name]' , '$file[name]' , '2' , '$now' , '$img[0]', '$img[1]', $filetype, '$file[size]'$asql2)";
		$pdo->query($sql);

		//echo "FILEID:" . $file['name'];
	}

	if(is_object($wec_account)) $wec_account->send_clean();

	if($form_mode == 'http') {
		msg('', 'reload', 'parent');
	}

	exit;

?>