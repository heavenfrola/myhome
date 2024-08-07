<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이미지 관리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_manage/main/main_box.php";
	$_skin_name = editSkinName();

	$folder = addslashes($_GET['folder']);
	$view_type = numberOnly($_GET['view_type']);
	$title_type = numberOnly($_GET['title_type']);

	if(!$folder) $folder="logo";
	$view_type = $_COOKIE['common_img_view_type'];
	if(!$view_type) $view_type = 2;

	$_folder_dir = $root_dir."/_skin/".$_skin_name."/img/".$folder;
	$_folder_url = $manage_url."/_skin/".$_skin_name."/img/".$folder;

	if($folder == 'email') {
		$_folder_dir = $root_dir.'/_image/_default/logo';
		$_folder_url = $manage_url.'/_image/_default/logo';
	}

	preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
	if($agent[0]) {
		$ie_ver = floor($agent[1]);
		if($ie_ver > 0 && $ie_ver < 10) $uploader_disabled = true;
	}

	// 멀티 파일서버
	$_dir = str_replace($root_dir.'/', '', $_folder_dir);
	$use_multi_server = fsConFolder($_dir);
	if($use_multi_server) {
		$_folder_url  = getFileDir($_dir);
		$_folder_url .= '/'.$_dir;
		$_folder_dir = $_dir;
	}

?>
<style type="text/css">
.preview_thumb {
	max-width: 100%;
	max-height: 150px;
}

.preview_list_thumb {
	max-width: 80px;
	max-height: 80px;
}
</style>
<div class="box_title first">
	<h2 class="title">이미지 관리</h2>
</div>
<div class="box_bottom top_line">
	<ul class="list_msg left">
		<li><?=editSkinNotice()?></li>
		<li>첨부하시는 모든 파일명은 공백없는 <u>영문/숫자의 조합</u>으로 하셔야 문제없이 출력됩니다.</li>
		<li>사이트의 접속 최적화를 위하여 첨부하시는 파일의 사이즈는 한 파일당 <u>500KB 이하</u>로 한정됩니다.</li>
		<li>리스트형식으로 조회시에는 타이틀 이미지 입력, 이미지 개별 변경등의 더 많은 기능이 제공됩니다.</li>
		<?php if ($view_type == 1 && $folder == "title") { ?>
		<li>각 페이지별 타이틀은 페이지별, 상품 분류, 게시판으로 구분되어 선택하시면 해당 타이틀 이미지를 확인 및 수정하실 수 있습니다.</li>
		<?php } ?>
	</ul>
</div>
<form name="imgFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="return imgListUp(this);" enctype="multipart/form-data">
	<input type="hidden" name="body" value="design@common_img.exe">
	<input type="hidden" name="exec" value="modify">
	<input type="hidden" name="img_num">
	<input type="hidden" name="folder" value="<?=$folder?>">
	<input type="hidden" name="_folder_dir" value="<?=$_folder_dir?>">
	<a name="img_list"></a>
	<div id="controlTab">
		<ul class="tabs">
			<?php
				foreach ($_skin_common_img as $key=>$val) {
			?>
			<li onclick="location.href='<?=$PHP_SELF?>?body=<?=$_GET['body']?>&type=<?=$_GET['type']?>&folder=<?=$key?>#img_list';" class="<?=($folder == $key) ? " selected" : ""?>" style="width:60px;"><?=$val?></li>
			<?php } ?>
		</ul>
	</div>
	<!-- 이미지 노출 설정 -->
	<div class="box_middle3 left">
		<strong>현재 이미지 폴더</strong> : <?=$_skin_common_img[$folder]?>&nbsp;&nbsp;
		<input type="radio" name="view_type" value="2" id="view_type2" onclick="chgViewType(2);" <?=checked($view_type, 2)?>> <label for="view_type2" class="p_cursor">갤러리형식</label>&nbsp;
		<input type="radio" name="view_type" value="1" id="view_type1" onclick="chgViewType(1);" <?=checked($view_type, 1)?>> <label for="view_type1" class="p_cursor">리스트형식</label>
		<span class="explain">(설정이 저장됨)</span>
	</div>
	<!-- //이미지 노출 설정 -->
	<!-- 이미지 업로드 -->
	<div class="box_middle left">
		<?php if ($uploader_disabled == true) { ?>
		<input type="file" name="upfile" class="input">
		<span class="box_btn_s blue"><input type="button" value="업로드" onclick="imgUp(this.form);"></span>
		<?php } else { ?>
		<span class="box_btn_up large">
			<input type="file" name="upfile[]" multiple onchange="commonAjaxUpload(this, './index.php?body=design@common_img_upload.exe', {'folder':'<?=$folder?>', '_folder_dir':'<?=$_folder_dir?>', exec:'upload'}, function(r){uploadCheck(r);})">
		</span>
		<span class="p_color2" style="position:Absolute; left:110px; top:43px;">버튼을 클릭하신 뒤 여러 파일들을 선택하실 수 있습니다. (드래그 또는 SHIFT 또는 CTRL 키 누른 상태에서 여러 파일 선택 가능)</span>
		<?php } ?>
	</div>
	<!-- //이미지 업로드 -->
	<?php
		if($view_type == 1 && $folder == "title") { // 리스트 형식&타이틀탭 일때 노출
			if(!$title_type) $title_type = 1;
	?>
	<div class="box_middle" style="background:#fafafa;">
		<input type="radio" name="title_type" id="title_type1" onclick="location.href='<?=$PHP_SELF?>?body=<?=$body?>&type=<?=$_GET['type']?>&folder=<?=$folder?>&title_type=1';" <?=checked($title_type, 1)?>> <label for="title_type1" class="p_cursor">페이지별 타이틀 이미지</label>
		<input type="radio" name="title_type" id="title_type2" onclick="location.href='<?=$PHP_SELF?>?body=<?=$body?>&type=<?=$_GET['type']?>&folder=<?=$folder?>&title_type=2';" <?=checked($title_type, 2)?>> <label for="title_type2" class="p_cursor">상품 분류 타이틀 이미지</label>
		<input type="radio" name="title_type" id="title_type3" onclick="location.href='<?=$PHP_SELF?>?body=<?=$body?>&type=<?=$_GET['type']?>&folder=<?=$folder?>&title_type=3';" <?=checked($title_type, 3)?>> <label for="title_type3" class="p_cursor">게시판 타이틀 이미지</label>
	</div>
	<?php } ?>
	<div id="fileProgressBar" style="position: absolute; height: 20px;"></div>
	<input type="hidden" name="folder_dir" value="<?=$_folder_dir?>">
	<table class="tbl_upload">
		<?php
			if($view_type == 2) {
		?>
		<tr>
		<?php
			}
			$_coln = 7;
			$_colw = floor(100/7)."%";

			$_img_name = array();
			$ii = 1;
			if($use_multi_server) {
				fileServerCon($use_multi_server);
				$odir = ftp_nlist($fs_ftp_con, $file_server[$use_multi_server]['file_dirname'].'/'.$_folder_dir);
				foreach($odir as $key => $val) {
					$_img_name[$ii] = basename($val);
					$ii++;
				}
			} else {
				$odir = @opendir($_folder_dir);
				while($arr=@readdir($odir)){
					if(@is_file($_folder_dir."/".$arr)){
						$_img_name[$ii] = $arr;
						$ii++;
					}
				}
				sort($_img_name);
			}

			function imgViewList($_pg_title=""){
				global $_folder_dir, $_folder_url, $view_type, $manage_url, $ii, $_skin_name, $folder, $arr, $_flash_id, $close_btn, $jj, $_coln, $_colw;
				$file = $_folder_dir."/".$arr;
				$ext = getExt($arr);
				$ext = @strtolower($ext);
				if(!@strchr("|jpg|jpeg|gif|bmp|png|swf|ttf|ttc|flv|", "|".$ext."|")) return;
				$_fexists = 0;

				if($ext == "swf"){
					$_flash_id++;
					$_wname = "플래시";
					$_imgurl = $manage_url."/_manage/image/design/flash_logo.gif";
					$_preview_url = "./?body=design@common_img_preview.frm&file=".urlencode($_folder_url."/".$arr)."&w=".$width."&h=".$height;
				}elseif($ext == "ttf" || $ext == "ttc"){
					$_wname = "폰트";
					$_imgsize = array($_imgw, $_imgw);
					$_imgurl = $manage_url."/_manage/image/design/font_logo.gif";
					$_preview_url = "";
				}else{
					$_wname = "이미지";
					$_imgurl = $_folder_url.'/'.$arr;
					$_preview_url = $_imgurl;
				}

				$_copy_word = "{{\$이미지경로}}/".$folder."/".$arr;
				$_put_word = ($close_btn) ? "<input type=\"button\" value=\"".$_wname."삽입하기\" onclick=\"putToEditor('".$_copy_word."', '".$ext."', '".$width."', '".$height."', 'flash_".$_flash_id."', '".str_replace(".".$ext, "", $arr)."');\" style=\"width:85px;\">" : "";

				$jj++;
				if($view_type == 1){
					$_td_bgcolor = ($jj%2 == 0) ? "#f0f0f0" : "#ffffff";
		?>
		<tr>
			<th>
				<input type="hidden" name="ori_img[<?=$ii?>]" value="<?=$file?>">
				<a class="clipboard p_cursor" data-clipboard-text="<?=$_copy_word?>"><span id="cimg<?=$ii?>"><?=($_pg_title) ? "<b>".$_pg_title."</b> (".$arr.")" : "/_skin/".$_skin_name."/img/".$folder."/".$arr?></span></a>
			</th>
		</tr>
		<tr>
			<td>
				<div>
					<?php if ($_preview_url) { ?><a href="<?=$_preview_url?>" target="_blank"><?php } ?>
                    <img src="<?=$_imgurl?>" style="border:0px solid #828282;" class="preview_list_thumb" rname="img_n<?=$ii?>">
                    <?php if ($_preview_url) { ?></a><?php } ?>
				</div>
			</td>
		</tr>
		<?php
			} else if ($view_type == 2) {
		?>
			<td class="center" style="width:<?=$_colw?>" onmouseover="this.style.backgroundColor='#f0f0f0';" onmouseout="this.style.backgroundColor='';">
				<input type="hidden" name="ori_img[<?=$ii?>]" value="<?=$file?>">
				<div onmouseover="showToolTip(event, '<?=$arr?>');" onmouseout="hideToolTip();">
					<?php if ($_preview_url) { ?><a href="<?=$_preview_url?>" target="_blank"><?php } ?>
                    <img src="<?=$_imgurl?>" name="img_n<?=$ii?>" class="preview_thumb" style="max-width:100%; height:auto;">
                    <?php if ($_preview_url) { ?></a><?php } ?>
				</div>
				<?php if ($_put_word) { ?>
				<div><span class="box_btn_s gray"><?=$_put_word?></span></div>
				<?php } ?>
				<div class="left"><a class="clipboard p_cursor" data-clipboard-text="<?=$_copy_word?>"><span id="cimg<?=$ii?>"><?=cutStr($arr,15,"..")?></span></a></div>
				<div class="left"><span class="box_btn_s gray"><input type="button" value="삭제" onclick="imgDelExec('<?=$ii?>');"></span></div>

			</td>
			<?php if (($ii+1)%$_coln == 0) { ?>
		</tr>
		<tr>
		<?php
			}
			}
				$ii++;
            }


			$ii = 0;
			// 리스트형식의 타이틀
			if($view_type == 1 && $folder == "title"){
				if($title_type == 1){
					foreach($_edit_list as $key=>$val){
						foreach($_edit_list[$key] as $key2=>$val2){
							list($_pg_title_name)=explode(".", $key2);
							if($_pg_title_name == "shop_big_section" || $_pg_title_name == "board_index" || $_pg_title_name == "shop_product_qna_mod_frm" || $_pg_title_name == "shop_product_qna_secret" || $_pg_title_name == "shop_product_review_mod_frm" || $_pg_title_name == "shop_zoom" || $_pg_title_name == "common_zip_search" || !$_pg_title_name) continue;

							$_pg_title_img=titleIMGName($_pg_title_name);
							$arr=$_pg_title_img ? $_pg_title_img : $_pg_title_name.".gif";

							imgViewList($val2);

							$_pg_title_key=array_search($_pg_title_img, $_img_name);
							unset($_img_name[$_pg_title_key]);
							$ii++;
						}
					}
				}elseif($title_type == 2){
					$csql = $pdo->iterator("select `no`, `name` from {$tbl['category']} order by `sort`");
                    foreach ($csql as $carr) {
						$_cate_title_img=titleIMGName($carr['no']);
						$arr=$_cate_title_img ? $_cate_title_img : $carr['no'].".gif";
						imgViewList($carr['name']);
					}

					unset($_img_name);
				}elseif($title_type == 3){
					$bsql = $pdo->iterator("select `db`, `title` from `mari_config`");
                    foreach ($bsql as $barr) {
						$_board_title_img=titleIMGName($barr['db']);
						$arr=$_board_title_img ? $_board_title_img : $barr['db'].".gif";
						imgViewList($barr['title']);
					}
					unset($_img_name);
				}
			}

			if(is_array($_img_name)){
				foreach($_img_name as $key=>$arr){
					imgViewList();
				}
				unset($_img_name);
			}

			if($view_type == 2){
				$ii2=($ii+$_coln)%$_coln;
				if($ii2 > 0){
					for($jj=0; $jj<($_coln-$ii2); $jj++){
						echo "<td width=\"".$_colw."\">&nbsp;</td>";
					}
				}
		?>
		</tr>
		<?php } ?>
	</table>
	<?php if ($close_btn) { ?>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="button" value="창닫기" onclick="window.close();"></span>
	</div>
	<?php } ?>
</form>

<script type="text/javascript">
	<!--
	function chgViewType(w){
		setCookie('common_img_view_type', w, 365);
		location.reload();
	}
	f=document.imgFrm;
	function imgDelExec(w){
		if(!confirm('해당 파일을 삭제하시겠습니까?      ')) return;
		f.img_num.value=w;
		f.exec.value='delete';
		f.submit();
	}
	function imgUploadExec(f){
		file=0;
		for(ii=0; ii<10; ii++){
			if(f['upfile'+ii].value) file=1;
		}
		if(file == 0){
			alert('업로드하실 파일을 첨부하여 주시기 바랍니다');
			return;
		}
		f.exec.value='upload';
		f.submit();
	}
	function imgListUp(f){
		return confirm('\n첨부하신 파일을 저장하시겠습니까?\n\n변경하실 파일을 많을 경우에는 전송시간이 지연될 수 있습니다      ');
		f.submit();
	}
	function imgUp(f) {
		if(!f.upfile.value) {
			window.alert('업로드 할 파일을 선택해주세요.');
			return false;
		}
		f.exec.value = 'upload';
		f.submit();
		f.exec.value = 'modify';
	}
	function putToEditor(w, ext, width, height, flash_id, flash_name){
		editor_ck=(opener.document.getElementById('edt_content')) ? 'Y' : 'N';

		if(editor_ck == 'Y'){
			if(ext == 'swf'){
				w='<div id="'+flash_id+'" style="z-index:100;"></div><script type="text/javascript">flashMovie(\''+flash_id+'\',\''+w+'\',\''+width+'\',\''+height+'\',\'xmlPath={{$이미지경로}}/flash/xml/'+flash_name+'.xml\',\'transparent\');</script>';
			}else{
				w='<img src="'+w+'" border="0">';
			}
			if(opener.document.getElementById('edt_content').disabled == true) editor_ck='N';
			else{
				opener.insertCode(w);
				return;
			}
		}

		if(editor_ck == 'N'){
			alert('현재 입력가능한 편집기가 존재하지 않습니다');
			return;
		}
	}

	function uploadCheck(r) {
		if(r != 'OK' && r != '') {
			window.alert(r);
		} else {
			location.reload();
		}
	}

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});

	window.onload=function (){
		this.focus();
	}
	//-->
</script>