<?PHP

	checkBasic();

	if($tmp_no && !$no) {
		$no=$tmp_no;
	}
	else {
		$data=$pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
		if(!$data[no]) msg(__lang_common_error_nodata__);
		$auth=getDataAuth($data,1);
	}

	$updir="/board/_data/$db/$no";
	if(!is_dir($root_dir."/".$updir)) {
		msg(__lang_common_error_ilconnect__);
	}

	if($exec=="delete") {
		if(!$del_file) {
			msg(__lang_common_error_required__);
		}
		deleteAttachFile($updir, $del_file);
	}
	else {
		if(!$_FILES["upfile"][tmp_name]) {
			msg(__lang_board_select_upload__);
		}
		uploadFile($_FILES["upfile"],md5($now),$updir,$config[upfile_ext],$config[upfile_size]);
	}

	msg("","reload","parent");

?>