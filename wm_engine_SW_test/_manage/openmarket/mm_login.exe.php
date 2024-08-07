<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  검색광고 접속처리
	' +----------------------------------------------------------------------------------------------+*/
	$type = addslashes($_POST['type']);

	checkBlank($type, "기본값을");

	if($type == "common_script"){

		checkBasic(1);
		include_once $engine_dir."/_engine/include/file.lib.php";
		include_once $engine_dir."/_manage/manage2.lib.php";
		// 2008-09-19 : 금지함수
		$_POST["common_script"]=stripslashes($_POST["common_script"]);

		funcFilter($_POST["common_script"], true);

		$updir=$dir[upload]."/".$dir[compare];
		makeFullDir($updir);

		$file_dir=$root_dir."/".$updir."/common_script.php";
		$fp=@fopen($file_dir, "w");
		$wr=fwrite($fp, $_POST["common_script"]);
		fclose($fp);
		if($wr === false) msg("저장이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

	}elseif($type == "save_site_id"){

		checkBasic(1);
		updateWMCode("ysm_accountid", addslashes(trim($_POST["ysm_accountid"])));
		updateWMCode("google_conversion_id", addslashes(trim($_POST["google_conversion_id"])));
		updateWMCode("auction_clickid_id", addslashes(trim($_POST["auction_clickid_id"])));
		updateWMCode("kakao_url_code", addslashes(trim($_POST['kakao_url_code'])));

		$no_reload_config = true;
		include $engine_dir.'/_manage/config/config.exe.php';

	}elseif($type == "output"){

		include_once $engine_dir."/_engine/include/shop_detail.lib.php";
		echo "<wisa>\n";
		$_fd=explode("/", $data_fd);
		$data=array();
		$data=getWMDefault($_fd);
		foreach($data as $key=>$val){
			echo $key.":".$val."/";
		}
		exit();

	}else{
		checkBasic(1);
		updateWMCode($type."_id", addslashes(trim($_POST[$type."_id"])));
		updateWMCode($type."_pwd", addslashes(trim($_POST[$type."_pwd"])));

	}

	msg("저장되었습니다", "reload", "parent");

?>