<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  가입추가항목 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = $_POST['exec'];
	$no = numberOnly($_POST['no']);
	$name = addslashes(trim($_POST['name']));
	$cate = addslashes(trim($_POST['cate']));

	if(!$exec){
		checkBasic();
		checkBlank($name,"항목명을 입력해주세요.");
	}

	$_infofile=$root_dir."/_config/member.php";

	$_mbr_add_info=array();
	if(@file_exists($_infofile)){ // 수정전 파일 백업
		$bak_dir=$dir[upload]."/member_addinfo";
		makeFullDir($bak_dir);
		@copy($_infofile, $root_dir."/".$bak_dir."/member_".date("ymdHis",$now)."_".$admin[no].".php");
		include_once $_infofile;
	}

	foreach($_mbr_add_info as $key=>$val){
		$max=$key+1;
	}

	if(!$no){
		$add_num=(count($_mbr_add_info) < 1) ? 0 : $max;
		$no=$add_num+1;
	}

	if($no) $no--;
	$_mbr_add_info[$no][name]="ADD";

	$updir = "_data/member_addinfo";

	if($_POST[dell_img]=='on') {  //삭제
		deletePrdImage($_mbr_add_info[$no],1);
		$up_filename=$width=$height="";
	}

	if($_FILES[upfile1][tmp_name]) {
		if(!is_dir($root_dir."/".$updir)) makeFullDir($updir);
		$up_filename = md5(time());
		$up_info=uploadFile($_FILES[upfile1],$up_filename,$updir,"jpg|jpeg|gif|png|swf");
		$filename=$up_info[0];
		chmod($up_info[2],0777);
	}

	$_file_contents="<?\n// 파일정보 : ".date("Y-m-d H:i")." - ".$admin[admin_id];
	foreach($_mbr_add_info as $key=>$val){
		if($exec == "delete" && $no == $key) continue;
		if($no == $key){
			$_mbr_add_info[$key]['name'] = inputText($_POST['name']);
			$_mbr_add_info[$key]['cate'] = inputText($_POST['cate']);
			$_mbr_add_info[$key]['type'] = $_POST['type'];
			$_mbr_add_info[$key]['ncs'] = $_POST['ncs'];
			$_mbr_add_info[$key]['text'] = explode(",",$_POST['text']);
			$_mbr_add_info[$key]['ext'] = explode(",",$_POST['ext']);
			$_mbr_add_info[$key]['size'] = $_POST['size'];
			$_mbr_add_info[$key]['class'] = $_POST['class'];
			$_mbr_add_info[$key]['updir'] = $updir;
			$_mbr_add_info[$key]['upfile1'] = $filename;
			$_mbr_add_info[$key]['review_link'] = ($_POST['review_link'] == 'Y') ? 'Y' : 'N';
		}
		$_file_contents .= "\n\n\$_mbr_add_info[".$key."]['name']=\"".$_mbr_add_info[$key][name]."\";";
		$_file_contents .= "\n\n\$_mbr_add_info[".$key."]['cate']=\"".$_mbr_add_info[$key]['cate']."\";";
		$_file_contents .= "\n\$_mbr_add_info[".$key."]['type']=\"".$_mbr_add_info[$key][type]."\";";
		$_file_contents .= "\n\$_mbr_add_info[".$key."]['ncs']=\"".$_mbr_add_info[$key][ncs]."\";";
		if($_mbr_add_info[$key]['type'] == "text"){
			$_file_contents .= "\n\$_mbr_add_info[".$key."]['size']=\"".$_mbr_add_info[$key][size]."\";";
			$_file_contents .= "\n\$_mbr_add_info[".$key."]['class']=\"".$_mbr_add_info[$key]['class']."\";";
        } else if ($_mbr_add_info[$key]['type'] == "file") {
			$_file_contents .= "\n\$_mbr_add_info[".$key."]['ext']=array(\"".@implode("\",\"",$_mbr_add_info[$key]['ext'])."\");";
		}else{
			$_file_contents .= "\n\$_mbr_add_info[".$key."]['text']=array(\"".@implode("\",\"",$_mbr_add_info[$key][text])."\");";
		}
		$_file_contents .= "\n\$_mbr_add_info[".$key."]['updir']=\"".$_mbr_add_info[$key][updir]."\";";
		$_file_contents .= "\n\$_mbr_add_info[".$key."]['upfile1']=\"".$_mbr_add_info[$key][upfile1]."\";";
		$_file_contents .= "\n\$_mbr_add_info[".$key."]['review_link']=\"".$_mbr_add_info[$key]['review_link']."\";";

		if($_POST[dell_img]=='on' && $key==$no) $_file_contents .= "\n\$_mbr_add_info[".$no."]['upfile1']=\"\";";
	}
	$_file_contents .= "\n?>";

	@unlink($_infofile);
	$fp=fopen($_infofile, "w");
	fwrite($fp, $_file_contents);
	fclose($fp);
	chmod($_infofile,0777);

	msg("","reload","parent");

?>