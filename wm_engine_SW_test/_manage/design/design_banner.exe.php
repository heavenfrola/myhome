 <?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배너 관리 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\HTTP\CurlConnection;

    include_once $engine_dir.'/_engine/include/img_ftp.lib.php';

	$exec = $_POST['exec'];
	$use_banner = addslashes($_POST['use_banner']);
	$no = numberOnly($_POST['no']);
	$big = numberOnly($_POST['big']);
	$mid = numberOnly($_POST['mid']);
	$small = numberOnly($_POST['small']);
	$depth4 = numberOnly($_POST['depth4']);
	$link = $_POST['link'];
	$target = $_POST['target'];
	$obj_type = $_POST['obj_type'];
	$pgCode = $_POST['pgCode'];
	$bn = addslashes($_POST['bn']);
	$link_type = numberOnly($_POST['link_type']);
	$maptext = addslashes(trim($_POST['maptext']));

    // 스킨배너 저장
    if ($_POST['source'] != '') {
        require 'design_banner_skin.exe.php';
        return;
    }

	modifyField($tbl['banner'], "start_date", "varchar(16) NOT NULL DEFAULT '2016-01-01-00' COMMENT '시작일'");
	modifyField($tbl['banner'], "finish_date", "varchar(16) NOT NULL DEFAULT '2016-01-01-00' COMMENT '시작일'");

	if($exec == "delete"){
		for($ii=0; $ii<sizeof($no); $ii++){
			$data=get_info($tbl['banner'], "no", $no[$ii]);
			deleteAttachFile($data['updir'], $data['upfile1']);
			deleteAttachFile($data['updir'], $data['upfile2']);
			$pdo->query("delete from {$tbl['banner']} where `no`='{$data['no']}'");
		}
		msg("", "reload", "parent");
		exit;
	}

	if($exec == 'toggle') {
		$cnt_qry = base64_decode($_POST['cnt_qry']);
		$use_banner = $pdo->row("select use_banner from {$tbl['banner']} where no='$no'");
		$use_banner = ($use_banner == 'Y') ? 'N' : 'Y';
		$pdo->query("update {$tbl['banner']} set use_banner='$use_banner' where no='$no'");

		$cntres = $pdo->iterator($cnt_qry);
        foreach ($cntres as $tmp) {
			$cnt[$tmp['use_banner']] = $tmp['cnt'];
		}

		header('Content-type:application/json;');
		exit(json_encode(array(
			'changed' => $use_banner,
			'Y' => number_format($cnt['Y']),
			'N' => number_format($cnt['N'])
		)));
	}

    if ($exec == 'copy') { // 스킨배너로 복사
        $_GET = $_POST;
        require 'design_banner.php';

        $target = $_POST['target'];
        $skin_folder = '_skin/'.$target;
        $internal_dir = $skin_folder.'/img/internal_banner';
        $external_dir = '_data/internal_banner/'.basename($skin_folder);

        $selected = explode(',', $_GET['selected']);
        foreach ($sql as $data) {
            if ($_GET['cpmode'] == '1') {
                if (in_array($data['no'], $selected) == false) continue;
            }

            // 첨부 이미지 저장
            if (is_dir($root_dir.'/'.$internal_dir) == false) {
                ftpMakeDir($skin_folder.'/img', 'internal_banner');
            }
            foreach(array('upfile1', 'upfile2') as $fn) {
                if (!$data[$fn]) continue;

                $up_filename = md5($fn.microtime());
                $filepath = $root_dir.'/_data/'.$data[$fn];

                $curl = new CurlConnection(getListImgURL($data['updir'], $data[$fn]));
                $curl->exec();
                file_put_contents($filepath, $curl->getResult());
                $imginfo = getimagesize($filepath);
                $_FILES[$fn] = array(
                    'name' => $up_filename.'.'.getExt($data[$fn]),
                    'tmp_name' => $filepath,
                    'size' => filesize($filepath),
                    'type' => $imginfo['mime']
                );
            }

            // 대상 스킨배너 정보 읽기
            unset($skinbanner_cfg);
            getSkinBanner($target);
            $banner_no = array();
            foreach ($skinbanner_cfg as $key => $val) {
                $banner_no[] = $key;
            }

            $data['no'] = $no = max($banner_no)+1;
            $data['source'] = $target;
            $data['link'] = array($data['link']);
            $_POST = $data;
            require 'design_banner_skin.exe.php';

            // 임시 파일 삭제
            foreach ($_FILES[$fn] as $file) {
                unlink($file['tmp_name']);
                unset($_FILES[$fn]);
            }
        }
        exit;
    }

	$name = trim(addslashes($_POST['name']));
	if($_POST['use_date'] != 'N') {
		$start_date = trim(addslashes($_POST['start_date']));
		$finish_date = trim(addslashes($_POST['finish_date']));
		checkBlank($start_date,"시작일을 입력해주세요.");
		checkBlank($finish_date,"종료일을 입력해주세요.");
	} else {
		$start_date = $finish_date = '';
	}

	checkBlank($name,"배너명을 입력해주세요.");


	$data=get_info($tbl['banner'], 'no', $no);

	$updir=$data['updir'];
	// 파일업디렉토리
	$asql="";
	if(!$updir) {
		if($cfg['use_icb_storage'] == 'Y') {
			$dir['upload'] = $cfg['current_icb_updir'];
			$asql .= ", upurl='{$cfg['current_icb_upurl']}'";

			$asql1 .= ", upurl";
			$asql2 .= ", '{$cfg['current_icb_upurl']}'";
		}

		$updir=$dir['upload']."/".$dir['banner'];
		makeFullDir($updir);
		$asql.=" , `updir`='/$updir'";
		$asql1.=" , `updir`";
		$asql2.=" , '/$updir'";
	}
	if($_FILES['upfile1']['tmp_name']) {
		deleteAttachFile($data['updir'], $data['upfile1']);
		$up_filename=md5($no+time());
		$up_info=uploadFile($_FILES['upfile1'],$up_filename,$updir,"jpg|jpeg|gif|png|bmp|swf|flv");
		$up_filename=$up_info[0];
		$asql.=" , `upfile1`='".$up_filename."'";
		$asql1.=" , `upfile1`";
		$asql2.=" , '".$up_filename."'";
	}
	if($_FILES['upfile2']['tmp_name']) {
		deleteAttachFile($data['updir'], $data['upfile2']);
		$up_filename1=md5($no+1+time());
		$up_info1=uploadFile($_FILES['upfile2'],$up_filename1,$updir,"jpg|jpeg|gif|png|bmp|swf|flv");
		$up_filename1=$up_info1[0];
		$asql.=" , `upfile2`='".$up_filename1."'";
		$asql1.=" , `upfile2`";
		$asql2.=" , '".$up_filename1."'";
	}

	if(!$use_banner) $use_banner="Y";
	if($obj_type == 4) {
		$maptext = addslashes($_POST['content']);
	}

	if(!fieldExist($tbl['banner'], 'big')) {
		addField($tbl['banner'], 'big', 'int(5) not null default "0"');
		addField($tbl['banner'], 'mid', 'int(5) not null default "0"');
		addField($tbl['banner'], 'small', 'int(5) not null default "0"');
	}
	addField($tbl['banner'], 'depth4', 'int(5) not null default "0"');

	if($data['no'] != null){
		$sql="UPDATE {$tbl['banner']} SET `name`='$name', `link` = '$link[0]',`link_type` = '$link_type',`obj_type` = '$obj_type',`text` = '$text',`target` = '$target',`use_banner` = '$use_banner', `maptext`='$maptext' , `start_date`='$start_date' , `finish_date`='$finish_date', big='$big', mid='$mid', small='$small', depth4='$depth4' $asql WHERE `no` ='$no'";
		$ems="수정되었습니다"; $lk="reload";
	}else{
		$sql="insert into {$tbl['banner']} (`no`, `name`, `link`, `link_type`, `obj_type`, `text`, `target`, `use_banner`, `maptext`, `cate` , `start_date` , `finish_date`, big, mid, small, depth4 $asql1) values('$no', '$name', '$link[0]', '$link_type', '$obj_type', '$text', '$target', '$use_banner', '$maptext', '$bn', '$start_date', '$finish_date', '$big', '$mid', '$small', '$depth4' $asql2)";
		$ems=""; $lk="./?body=design@design_banner&pgCode=$pgCode&bn=$bn";
	}

	$pdo->query($sql);

	unset($_POST);
	$_POST['banner_minute'] = "Y";
	$no_reload_config = "Y";
	include $engine_dir.'/_manage/config/config.exe.php';

	msg($ems,$lk,"parent");

?>