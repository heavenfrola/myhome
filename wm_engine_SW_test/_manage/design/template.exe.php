<?PHP

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	if(!$page_mode) $page_mode = $_REQUEST['page_mode'];
	if(!$view_mode) $view_mode = $_REQUEST['view_mode'];
	if(!$rdir) $rdir = $_REQUEST['rdir'];
	if(!$exec) $exec = $_REQUEST['exec'];
	if(!$edt_content) $edt_content = $_REQUEST['edt_content'];
    $skinname = $_REQUEST['skinname'];
    $filename = $_REQUEST['filename'];

    if (preg_match('/\.{2,}|\//', $skinname) == true) exit('잘못된 스킨이름');
    if (preg_match('/\.{2,}|\//', $filename) == true) exit('잘못된 스킨파일');

    $file_url = $root_dir.'/board/_skin/'.$skinname.'/'.$filename;
    $file_url = str_replace(DIRECTORY_SEPARATOR, "/", $file_url);

	// 2008-09-19 : 공통페이지 구분
	if($page_mode == "common"){
		$_dir=$_oridir=$root_dir."/_include";
		$_bak_dir=$dir['upload']."/_include_bak";
	}elseif($page_mode == "board"){
		$_dir=$_oridir=$root_dir."/board/_skin";
		$_bak_dir=$dir['upload']."/_boardskin_bak";
	}else{
		$_dir=$_oridir=$root_dir."/_template";
		$_bak_dir=$dir['upload']."/template_bak";
	}
	makeFullDir($_bak_dir);
	$allow_ext="html|htm|php|css|js|wsm|wsn|wsr";

	include_once $engine_dir."/_manage/design/template_name.php";
	// 2008-09-19 : 공통페이지 구분
	if($page_mode == "common"){
		$dir_arr=$common_arr;
		$dir_sub_arr=$common_sub_arr;
	}

	function bakUrl($file_url){
		global $_oridir, $root_dir, $_bak_dir, $file_name, $dir_name, $bak_name, $bak_full_dir, $dir_sub_arr, $page_mode;
		$file_name=basename($file_url);
		$dir_name=str_replace($_oridir."/", "", $file_url);
		if($page_mode == "common"){
			foreach($dir_sub_arr as $key=>$val){
				foreach($dir_sub_arr[$key] as $key2=>$val2){
					if($key2 == $file_name){
						$dir_name=$key;
						break;
					}
				}
			}
			$dir_name=($dir_name == $file_name) ? "" : str_replace("/".$file_name, "", $dir_name);
		}else{
			$dir_name=($dir_name == $file_name) ? "" : str_replace("/".$file_name, "", $dir_name);
		}
		$bak_name=$dir_name."_".$file_name;
		$bak_full_dir=$root_dir."/".$_bak_dir."/".$bak_name;
	}

	$err_msg="계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.";

	if($exec){
		if($exec == "start"){

			ftpCon($ftp_id, $ftp_pwd, $ftp_port);
			if(!$ftp_login_result) msg("FTP 접속이 실패하였습니다");

			$edt_content="<?php\n".$ftp_id."\n".$ftp_pwd."\n".$ftp_port."\n?>";
			$_tmp_dir=$root_dir."/_data/ftpcon_tmp.php";
			$of=fopen($_tmp_dir, "w");
			$fw=fwrite($of, $edt_content);
			if(!$fw) msg("권한설정문제로 저장이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
			fclose($of);

			$file['name']="ftpcon.php";
			$file['tmp_name']=$_tmp_dir;
			ftpUploadFile($root_dir."/_config", $file, "php");
			unlink($file['tmp_name']);

			msg("", "reload", "parent");

		}elseif($exec == "modify" || $exec == "restore"){

			bakUrl($file_url);

			if($exec == "modify"){

				if(!@file_exists($bak_full_dir)){ // 백업파일이 없다..? 원본이라는 뜻
					$file['name']=$bak_name;
					$file['tmp_name']=$file_url;
					ftpUploadFile($root_dir."/".$_bak_dir, $file, $allow_ext);
				}

				$of=fopen($bak_full_dir."_tmp", "w");
				$edt_content=stripslashes($edt_content);

		        /*PHP 구문 삭제*/
                $edt_content = preg_replace('/<\?php|<\?|\?>/i', '', $edt_content);
                funcFilter($edt_content);

				$fw=fwrite($of, $edt_content);
				if(!$fw) msg($err_msg);
				fclose($of);

				$file['name']=$file_name;
				$file['tmp_name']=$bak_full_dir."_tmp";
				$_realdir=($page_mode == "common") ? $_dir : $_dir."/".$dir_name;
				ftpUploadFile($_realdir, $file, $allow_ext);
				unlink($file['tmp_name']);

?>
<script type="text/javascript">
	alert("저장되었습니다");
    parent.removeLoading();
	parent.page_mode='<?=$page_mode?>';
	parent.getEditList('<?=$file_url?>', '', 1);
    msg('저장되었습니다.');
</script>
<?php

		}elseif($exec == "restore"){

			if(!@file_exists($bak_full_dir)){
				msg("원본파일이 존재하지 않습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
			}
			$file['name']=$file_name;
			$file['tmp_name']=$bak_full_dir;
			$_realdir=($page_mode == "common") ? $_dir : $_dir."/".$dir_name;
			ftpUploadFile($_realdir, $file, $allow_ext);
			ftpDeleteFile($root_dir."/".$_bak_dir, $bak_name);
?>
<script type="text/javascript">
	alert("초기화 되었습니다");
	parent.getEditList('<?=$file_url?>', '', 1);
</script>
<?php
		}
		msg();

	// 2009-09-22 : 스킨 설정 변수 저장 - Han
	}elseif($exec == "board_skin_vals"){

		include_once $engine_dir."/_engine/include/design.lib.php";

		bakUrl($file_url);
		$_save_skin_dir=str_replace($file_name, "", $file_url);
		if(is_file($_save_skin_dir."skin_config.".$_skin_ext['g'])) include_once $_save_skin_dir."skin_config.".$_skin_ext['g'];
		foreach($_POST['skin'] as $key=>$val){
			if($val == "") continue;
			$_board_skin[$key]=$val;
			$_tmp .= $val.";";
		}
		include $engine_dir."/_manage/design/skin_config.exe.php";
		msg("설정이 저장되었습니다");

	}

	exit("=".$_dir);
}

// 메뉴별 보기 모드
$vm=($view_mode == "menu") ? "Y" : "N";

ob_start();

if(!$odir){
    $_dir = $file_url;
	$ofile=@is_file($_dir);
	if($ofile){
		$_dir=@realpath($_dir);
		$filename=@basename($_dir);
		$filedir=@str_replace($filename, "", $_dir);
		$file_content=@htmlspecialchars(@file_get_contents($_dir));
		bakUrl($_dir);
		if($vm == "Y") $filename=$dir_sub_arr[$dir_name][$filename];
		// 백업파일 존재여부. 한번이상 수정을 했다는 얘기
		if(@file_exists($bak_full_dir)) $restore_btn=" <a href=\"javascript:;\" onclick=\"restoreContent(document.editFrm);\">".btn2("복구하기")."</a>";

        $boards_list = $pdo->row("select group_concat(title) from mari_config where skin='$skinname'");
        $boards_list = str_replace(',', ', ', $boards_list);

?>
<input type="hidden" name="page_mode" value="<?=$page_mode?>">
<input type="hidden" name="skin" value="<?=$skinname?>">
<input type="hidden" name="file" value="<?=$file?>">
<ul class="list_info">
    <?php if ($boards_list) { ?>
    <li>현재 편집하시는 스킨은 <strong>[<?=$skinname?>]</strong>이며 <strong><?=$boards_list?></strong> 게시판에서 사용 중인 스킨입니다.</li>
    <?php } else { ?>
    <li>현재 편집하시는 스킨은 <strong>[<?=$skinname?>]</strong>입니다.</li>
    <?php } ?>
    <li>파일 명 : <b><?=$filename?></b></li>
</ul>
<?php
		// 2009-09-21 : 디자인 V3 버전 게시판 스킨 편집 시 변수 목록 - Han
		if($cfg['design_version'] == "V3" && $page_mode == "board"){
			$_skin_config_file_dir=str_replace($filename, "skin_config.cfg", $_dir);
			if(@is_file($_skin_config_file_dir)) include_once $_skin_config_file_dir;
			include_once $engine_dir."/_manage/design/editor_vals.php";
		}
?>
<div>
<textarea id="edt_content" name="edt_content" class="txta" style="width:98%; height:600px; font-size:9pt;" onkeydown="editorKeyUp(this);"><?=$file_content?></textarea>
</div>
<div class="center" style="margin-top: 10px">
	<span class="box_btn blue"><input type="submit" value="저장하기"></span>
	<?php if ($restore_btn) { ?>
	<span class="box_btn gray"><input type="button" onclick="getEditList('<?=$filedir?>');" value="취소" id="cancelBtn"></span>
	<?php } ?>
	<span class="box_btn gray"><input type="button" onclick="window.close();" id="closeBtn" style="display:none;" value="창닫기"></span>
</div>
<?php
		// 2009-06-12 : 디자인 V3 버전 게시판 스킨 편집시 제공 코드 목록 - Han
		if($cfg['design_version'] == "V3" && $page_mode == "board" && $filename != "style.css"){
?>
<div id="controlTab" style="position:relative">
	<ul class="tabs">
		<li onclick="getCodeList('edit_pg', '', '<?=$filename?>', this);" id="c_edit_pg" class="selected">현재 코드</li>
		<li onclick="getCodeList('common', '', '<?=$filename?>', this);" id="c_common">공통 코드</li>
		<li onclick="getCodeList('user_code', '', '<?=$filename?>', this);" id="c_user_code">사용자 코드</li>
		<li onclick="getCodeList('page_link', '', '<?=$filename?>', this);" id="c_page_link">페이지 링크</li>
	</ul>
</div>

<div id="code_list" style="position:relative"></div>

<form name="popFrm" action="./pop.php" method="get">
    <input type="hidden" name="body" value="design@editor.frm">
    <input type="hidden" name="design_edit_key">
    <input type="hidden" name="design_edit_code">
</form>

<script type="text/javascript">
    var edt_filename = '<?=$filename?>';
    var edt_skinname = '<?=$skinname?>';
    var edt_mode = 'board';
    var edit_pg = 'board_index';

    getCodeList('edit_pg', '', '<?=$filename?>');

    function getCodeList(code_key, txt, filename, w = ''){
        if(!code_key) code_key = '';
        if(!txt) txt = '';

        var param = {
            body: 'design@editor_code.exe',
            code_key: code_key,
            txt: txt,
            _edt_mode: edt_mode,
            _edit_pg: edit_pg,
            design_edit_key: '',
            design_edit_code: '',
            filename: filename
        }

        $.get('./', param, function(r) {
            if (r) {
                if (w) {
                    $('#controlTab ul li').removeClass('selected');
                    $('#' + w.id).addClass('selected');
                } else {
                    $('#' + code_key).addClass('selected');
                }

                var mdv = document.getElementById('code_list');
                mdv.innerHTML = r;
            }
        });
    }

    function insertCode(code){
        var fr;
        if (document.getElementById('frame_edt_content2')) {
            fr = document.getElementById('frame_edt_content2').contentWindow;
        } else {
            fr = document.getElementById('frame_edt_content').contentWindow;
        }

        var textarea = fr.document.getElementById('textarea');
        var nextfocus = textarea.selectionEnd + code.length;
        textarea.focus();
        fr.editArea.textareaFocused = true;
        textarea.value = textarea.value.substr(0, textarea.selectionStart)+code+textarea.value.substr(textarea.selectionEnd);

        if (textarea.setSelectionRange) {
            textarea.focus();
            textarea.setSelectionRange(nextfocus, nextfocus);
        } else if (textarea.createTextRange) {
            var range = textarea.createTextRange();
            range.collapse(true);
            range.moveEnd('character', nextfocus);
            range.moveStart('character', nextfocus);
            range.select();
        }
    }

    function editCode(key, code){
        f = document.popFrm;
        if (edt_filename) {
            if (!f.design_edit_filename) $(f).append("<input type='hidden' name='design_edit_filename'>");
            f.design_edit_filename.value = edt_filename;
        }
        if (edt_skinname) {
            if (!f.design_edit_skinname) $(f).append("<input type='hidden' name='design_edit_skinname'>");
            f.design_edit_skinname.value = edt_skinname;
        }

        f.design_edit_key.value=key;
        f.design_edit_code.value=code;
        window.open('about:blank','codePop','top=10,left=10,width=950,status=no,toolbars=no,scrollbars=yes,height=700');
        f.target = 'codePop';
        f.submit();
    }

    function userCode(w) {
        if (!w) w='';
        var viewId='userCode';
        if (getCookie('def_dmode') != '0') viewId+=w;
        url = './pop.php?body=design@editor_user.frm&type=<?=$_GET['type']?>&user_code='+w;
        window.open(url,viewId,'top=10,left=10,width=850,status=no,toolbars=no,scrollbars=yes,height=700');
    }

    function delCode(code) {
        if (confirm('삭제 후 복구가 불가능합니다.\t\n삭제하시겠습니까?')) {
            frm = document.getElementsByName(hid_frame);
            frm[0].src='./?body=design@editor.exe&del_code='+code+'&exec=delete&type=<?=$_GET['type']?>';
        }
    }
</script>
<?php
		}
	}else{
		echo "<center>$_dir<font color=#FF0000>※ 해당 파일 및 디렉토리가 존재하지 않습니다. <a href=\"./?body=support@1to1\" target=\"_blank\">[1:1 고객센터]</a>문의 글로 접수 바랍니다.</font>";
	}
}

$content=ob_get_contents();
ob_end_clean();

$content = mb_convert_encoding($content, 'utf-8', _BASE_CHARSET_);
echo $content;

?>