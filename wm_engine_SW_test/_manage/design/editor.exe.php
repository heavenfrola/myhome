<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();

    if($_GET['exec'])$exec = $_GET['exec'];
	if($_POST['exec']) $exec = $_POST['exec'];
	if($_POST['page_mode']) $page_mode = $_POST['page_mode'];
	if($_POST['dir_name']) $dir_name = $_POST['dir_name'];
	if($_POST['_dir']) $_dir = $_POST['_dir'];
	if($_POST['edt_content']) $edt_content = $_POST['edt_content'];
	if($_POST['_file_src']) {
        if (preg_match('/\.{2,}|\//', $_POST['_file_src']) == true) {
            msg('사용할수 없는 파일명입니다.');
        }
        if ($_POST['board_check'] != '') { // 게시판 스킨
            $file_src = $root_dir.'/board/_skin/'.$_POST['board_check'].'/'.$_POST['_file_src'];
        } else {
            $ext = getExt($_POST['_file_src']);
            switch($ext) {
                case $_skin_ext['c'] : $_dir = 'COMMON'; break;
                case $_skin_ext['p'] : $_dir = 'CORE'; break;
                case $_skin_ext['m'] : $_dir = 'MODULE'; break;
            }
            $file_src = $root_dir.'/_skin/'.$_skin_name.'/'.$_dir.'/'.$_POST['_file_src'];
        }
    }

	if($exec == "delete") {

		$del_code = $_REQUEST['del_code'];
		$_user_code = $_REQUEST['_user_code'];

		if(empty($del_code)) msg("삭제할 코드가 정의되지 않았습니다.");

		$del_code=str_replace("user", "", $del_code);
		$del_code=str_replace("_list", "", $del_code);

		unlink($root_dir."/_skin/".$_skin_name."/MODULE/user".$del_code."_list.wsm");

		if(file_exists($root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'])) include_once $root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];

		$file_content="<?php\n// 사용자 코드 설정파일 : ".date("Y-m-d H:i", $now)." 변경됨 - ".$admin['admin_id']."\n\n";
		if(is_array($_user_code)){
			foreach($_user_code as $key=>$val){
				if($key == $del_code) continue;
				foreach($_user_code[$key] as $key2=>$val2){
					if(!$val2) continue;
					$val2=stripslashes($val2);
					$val2=addslashes($val2);
					$val2=str_replace("\$", "\\$", $val2);
					$file_content .= "\$_user_code[$key][$key2]=\"".$val2."\";\n";
				}
				$file_content .= "\n";
			}
		}
		$file_content .= "?>";

		$_filedir=$root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];
		$_filebakdir=$root_dir."/_data/user_code_tmp.".$_skin_ext['g'];
		$of=fopen($_filebakdir, "w");
		$fw=fwrite($of, $file_content);
		if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
		fclose($of);

		$file['name'] = 'user_code.'.$_skin_ext['g'];
		$file['tmp_name'] = $_filebakdir;
		ftpUploadFile($root_dir."/_skin/".$_skin_name, $file, $_skin_ext['g']);
		unlink($file['tmp_name']);

		$body1=($_GET['type'] == 'mobile') ? 'wmb' : 'design';
		msg("삭제되었습니다.", "./?body=".$body1."@editor_code&type=".$_GET['type']."&default_code=user_code", "parent");
	}

	if($exec == "skin_vals"){
		$connection=ftpCon();
		if(!$connection) msg("FTP 접속이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

		include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

		if($_POST['skin']['pageres_design_use'] == 'Y') {
			if($_POST['skin']['rev_year_split'] != 'Y') $_POST['skin']['rev_year_split'] = 'N';
			if($_POST['skin']['qna_year_split'] != 'Y') $_POST['skin']['qna_year_split'] = 'N';
		}

		$_tmp = '';
		foreach ($_POST['skin'] as $key=>$val) {
			if($val == "") continue;
			$_skin[$key]=$val;
			$_tmp .= $val.";";
		}

		include $engine_dir."/_manage/design/skin_config.exe.php";

		msg("설정이 저장되었습니다", "reload", "parent");
	}

	$_bak_dir=$dir['upload']."/skin_".$_skin_name."_bak";
	makeFullDir($_bak_dir);

	$err_msg="계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.";
	$allow_ext=$_skin_ext['c']."|".$_skin_ext['p']."|".$_skin_ext['m']."|css|js";

	if(!$file_src) msg("편집하실 파일을 선택하시기 바랍니다");
	$file_name=basename($file_src);
	$file_dir=str_replace("/".$file_name, "", $file_src);
	$file_dir_arr=explode("/", $file_dir);
	$last_dir=$file_dir_arr[count($file_dir_arr)-1];
	if(preg_match("/core|common|module/i", $last_dir)){
		$_bak_dir .= "/".$last_dir;
		makeFullDir($_bak_dir);
	}

	$date_info="_".date("YmdHis");

	$bak_full_dir=$root_dir."/".$_bak_dir."/".$file_name.$date_info;

	function getBakFile(){
		global $root_dir, $_bak_dir, $file_name;
		$dir=$root_dir."/".$_bak_dir;
		$fo=opendir($dir);
		$bak_arr=array();
		while($file=readdir($fo)){
			$filesrc=$dir."/".$file;
			if(!strchr($file, $file_name)) continue;
			$mtime=filemtime($filesrc);
			if($mtime){
				$bak_arr[$mtime]=$file;
			}
		}
		krsort($bak_arr);
		$ii=1;
		foreach($bak_arr as $key=>$val){
			if($ii > 5){
				@unlink($dir."/".$val);
				unset($bak_arr[$key]);
			}
			$ii++;
		}
		return $bak_arr;
	}

	if($exec == "modify"){
		if(@filesize($file_src)){
			@copy($file_src, $bak_full_dir);
			@chmod($file_src, 0777);
			getBakFile();
		}
		$of=fopen($bak_full_dir."_tmp", "w");
		if(is_array($edt_content)){
			$edt_content=getListFContent($edt_content, str_replace(".".$_skin_ext['m'], "", $file_name), 1);
		}
		$edt_content=$edt_content;

        /*PHP 구문 삭제*/
        $edt_content = preg_replace('/<\?php|<\?|\?>/i', '', $edt_content);
        funcFilter($edt_content);

		$fw=fwrite($of, $edt_content);
		if(!$fw && $edt_content) msg($err_msg);
		fclose($of);

		$file['name']=$file_name;
		$file['tmp_name']=$bak_full_dir."_tmp";
		$_realdir=($page_mode == "common") ? $_dir : $_dir."/".$dir_name;
		ftpUploadFile($file_dir, $file, $allow_ext);
		unlink($file['tmp_name']);

		$msg="저장되었습니다";

		switch($exec2) {
			case 'preview' :
				javac("parent.skinPreview.location.href='?body=design@skin_preview.frm&skin_name=$skin_name&viewpage=$viewpage'");
				exit;
			break;
			case 'exit' :
				exit;
			break;
		}

	}elseif($exec == "restore"){

		$bak_file_dir=$root_dir."/".$_bak_dir."/".basename($_POST['restore_file']);

		if(!@file_exists($bak_file_dir)){
			msg("원본파일이 존재하지 않습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
		}
		$file['name']=$file_name;
		$file['tmp_name']=$bak_file_dir;
		ftpUploadFile($file_dir, $file, $allow_ext);

		$msg="파일 내용이 복구 되었습니다";

	}elseif($exec == "restore_load"){

		$bak_arr=getBakFile();

?>
<script type="text/javascript">
	w=parent.document.getElementById('restore_div');
	if(w){
		content='\
		<div class="box_bottom left">\
		<table cellspacing="0" cellpadding="0" style="width:100%;">\
			<caption class="hidden">복구하기</caption>\
			<tr>\
				<td>\
					<ul class="list_msg">\
						<li>복구 기능은 최근까지 수정된 최대 5개까지의 파일이 지원됩니다.</li>\
						<li>저장된 날짜를 확인하신 후 해당 파일을 선택하신 뒤 <u class="p_color2">더블 클릭</u> 하여 실행해 주시기 바랍니다.</li>\
					</ul>\
                    <?php if (count($bak_arr)) { ?>
					<select name="restore_file" style="width:100%; height:75px;" multiple ondblclick="restoreFile(this.value)">\
                        <?php foreach ($bak_arr as $key=>$val) { ?>
						<option value="<?=$val?>"><?=date("Y-m-d H:i:s", $key)?></option>\
                        <?php } ?>
					</select>\
                    <?php } else { ?>
	    			<div style="line-height:40px;" class="p_color2">! <u>현재 복구가능한 파일이 존재하지 않습니다.</u></div>\
                    <?php } ?>
				</td>\
			</tr>\
		</table>\
		</div>\
';
		w.innerHTML=content;
	}
</script>

<?php
		exit();
		msg();
	}

	if(!$no_reload) msg($msg, "reload", "parent");

?>