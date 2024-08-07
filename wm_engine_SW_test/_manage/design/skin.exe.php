<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\Design\BannerGroup;

	checkBasic();

	$exec = addslashes($_POST['exec']);
    $type = addslashes($_POST['type']);

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	$connection=ftpCon();
	if(!$connection) msg("FTP 접속이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

	if($exec == "skin_comment"){
		$skin_name = $_POST['skin_name'];
		$prefix = $_POST['prefix'];
		$prefix2 = ($prefix) ? $prefix.'#_' : '';

		checkBlank($skin_name,"스킨을 선택해주세요.");
		$_cm=$_POST['skin_comment'][$prefix2.$skin_name];
		$_cm=stripslashes($_cm);
		$_cm=strip_tags($_cm);
		$_cm=str_replace("'", "", $_cm);
		$_cm=str_replace("\"", "", $_cm);

		$design["sn_".$skin_name]=$_cm;

		include $engine_dir."/_manage/design/config.exe.php";

		//if($_cm) $_cm = '| '.$_cm;
?>
<script type="text/javascript">
	var w = parent.$('.ctext_<?=$skin_name?>');
	if(w.length > 0){
		w.html('<?=$_cm?>');
		parent.skinComment('<?=$skin_name?>', '2', '<?=$prefix?>');
	}
</script>
<?php
		exit();

	// 스킨별 환경설정
	}elseif($exec == "cfg"){
		$_skin_name=$_POST['edit_skin'];
		include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

		if($config_code == 'qd') {
			switch($_POST['qd2_ctype']) {
				case '1' :
					if($_POST['big']) $ctemp = $_POST['big'];
					if($_POST['mid']) $ctemp = $_POST['mid'];
					if($_POST['small']) $ctemp = $_POST['small'];
				break;
				case '2' :
					$ctemp = numberOnly($_POST['ebig']);
				break;
				case '4' :
					$ctemp = numberOnly($_POST['xbig']);
				break;
				case '5' :
					$ctemp = numberOnly($_POST['ybig']);
				break;
				case '6' :
					$ctemp = preg_replace('/[^0-9_,]/', '', $_POST['qd2_manual']);
				break;
			}
			if($_POST['qd2_ctype'] > 0) {
				if(!$ctemp) msg('기본상품을 검색할 카테고리를 선택해 주세요.');
			}
			$_POST['qd2_cno'] = $ctemp;
			unset($_POST['big'], $_POST['mid'], $_POST['small'], $_POST['ebig'], $_POST['xbig'], $_POST['ybig']);
		}

		foreach($_POST as $key => $val) {
			$_skin[$key] = $val;
		}

		include $engine_dir."/_manage/design/skin_config.exe.php";
		$_url="reload";
		$_target="parent";

	}elseif($exec == "skin_config"){

		$_skin_name=$_POST['edit_skin'];

		include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

		if($_FILES['background']['size']){
			$ext=getExt($_FILES['background']['name']);
			$ext=strtolower($ext);
			$file['name']="background.".$ext;
			$file['tmp_name']=$_FILES['background']['tmp_name'];
			ftpUploadFile($root_dir."/_skin/".$_skin_name."/img/bg", $file, "jpg|jpeg|gif|bmp|png");
			unlink($file['tmp_name']);
			$_skin['background']=$file['name'];
		}
		// 스킨 변수 재설정
		$_skin['site_align']=$_POST['site_align'];
		$_skin['intro_use']=$_POST['intro_use'];
		$_skin['intro_url']=$_POST['intro_url'];
		$_skin['body_color']=$_POST['body_color'];
		$_skin['background_use']=$_POST['background_use'];
		$_skin['background_fixed']=$_POST['background_fixed'];
		$_skin['background_type']=$_POST['background_type'];
		$_skin['jquery_ver'] = $_POST['jquery_ver'];

		include $engine_dir."/_manage/design/skin_config.exe.php";
		$_url="reload";
		$_target="parent";

	} elseif($exec == 'skin_select') {
		$skin = $_POST['skin'];
		$fd = ($_POST['prefix']) ? $_POST['prefix'].'_skin' : 'skin';
		$design[$fd]  = $skin;

        // 스킨 배너 있을 경우 리모트 폴더로 배너 이미지 복사
        getSkinBanner($skin);
        foreach ($skinbanner_cfg as $data) {
            $filepath = $root_dir.'/_skin/'.$skin.'/'.$data['updir'].'/'.$data['upfile1'];
            $up_filename = pathinfo($data['upfile1'], PATHINFO_FILENAME);
            $fileinfo = getimagesize($filepath);

            uploadFile(array(
                'tmp_name' => $filepath,
                'name' => $data['upfile1'],
                'type' => $fileinfo['mime'],
                'error' => 0,
                'size' => filesize($filepath)
            ), $up_filename, '_data/internal_banner/'.$skin, 'jpg|jpeg|gif|png');
        }

        // 그룹 배너가 있을 경우 리모트 폴더로 그룹배너 이미지 복사
        $group_banner_path = $root_dir.'/_skin/'.$skin.'/img/user_group_banner';
        $dir = opendir($group_banner_path);
        while($bn = readdir($dir)) {
            if (preg_match('/^[0-9]+$/', $bn) == true) {
                $bn = new BannerGroup($type, $bn);
                $bn->migration();
            }
        }

		checkBlank($skin,"스킨을 선택해주세요.");
		include $engine_dir."/_manage/design/config.exe.php";
	}

	$_url=$_url ? $_url : "reload";
	$_target=$_target ? $_target : "parent";

	msg("", $_url, $_target);

?>