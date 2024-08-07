<?PHP

	checkBasic();
	$ino = numberOnly($_POST["ino"]);
	checkBlank($ino, "필수값을 입력해주세요.");

	$data = get_info($tbl['product_image'], "no", $ino);
	checkBlank($data["no"], "자료값을 입력해주세요.");

	if($data['filetype'] == 8 || $data['filetype'] == 9) {
		include_once $engine_dir.'/_manage/product/product_wdisk.inc.php';

		$fullpath = $data['updir'].'/'.$data['filename'];

		$size = $wdisk_con->filesize($fullpath);
		$wdisk_con->delete('_thumb_'.$fullpath);
		$return = $wdisk_con->delete($fullpath);

		if(is_object($wec_account)) {

			$wec_account->queue('wdiskSize', $wec_account->config['account_idx'], 'delete', $size);
			$wec_account->send_clean();

			$wec_account->call('setFileUpload', array('act'=>'delete', 'filepath'=>$wdisk['home_dir'].$fullpath, 'admin_id' => $admin['admin_id']));
			$wec_account->call('setFileUpload', array('act'=>'delete', 'filepath'=>$wdisk['home_dir'].'/_thumb_'.$fullpath, 'admin_id' => $admin['admin_id']));
		}
	} else {
		if($data['ori_no'] < 1) {
			include_once $engine_dir."/_engine/include/file.lib.php";
			if($_use['file_server'] == "Y" && fsConFolder($data['updir'])){
				fsDeleteFile($data['updir'],$data['filename']);
			}else{
				$filename = $root_dir."/".$data["updir"]."/".$data['filename'];
				if(is_file($filename)) {
					@unlink($filename);
				}
			}
		}
	}
	if($data['ori_no'] > 0) {
		$pdo->query("update $tbl[product_image] set filetype='D' where no='$ino'");
	} else {
		$pdo->query("delete from `".$tbl['product_image']."` where `no`='$ino'");
	}

	if(($data['filetype'] == 2 || $data['filetype'] == 8) && $cfg['up_aimg_sort'] == "Y" && fieldExist($tbl['product_image'], "sort")) {
		$pdo->query("update `{$tbl['product_image']}` set `sort` = `sort`-1 where `pno` = {$data['pno']} and `sort` > {$data['sort']}");

		msg('', 'reload', 'parent');
	}else{
		msg("","reload","parent");
	}

?>