<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_manage/main/main_box.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];
	versionChk("V3");
	if(!$edit_pg) $edit_pg = $_GET['edit_pg'];
	if(!$edit_pg){
		include $engine_dir."/_manage/design/editor_main.php";
		return;
	}

	if($design_edit_code == "pageres"){
		include $engine_dir."/_manage/design/editor_etc.php";
		return;
	}

    $board_check = '';
	$skinname = $_GET['design_edit_skinname'];
	if($skinname) {
		$file_dir = $root_dir.'/board/_skin/'.$skinname.'/';
		$filename = 'list_top.php';
        $board_check = $skinname;
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/edit_area/edit_area_full.js"></script>
<div class="box_title first" style="display:none;">
	<h2 class="title">페이지 편집</h2>
</div>
<div class="box_middle">
	<ul class="list_msg left">
		<li><?=editSkinNotice()?></li>
		<li>잘못된 코드 삽입으로 발생한 문제에 대해서는 책임지지 않습니다.</li>
	</ul>
</div>
<form name="editFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="return ckFrm(this);">
<input type="hidden" name="body" value="design@editor.exe">
<input type="hidden" name="exec" value="modify">
<input type="hidden" name="_skin_ext[c]" value="<?=$_skin_ext['c']?>">
<input type="hidden" name="_skin_ext[p]" value="<?=$_skin_ext['p']?>">
<input type="hidden" name="_skin_ext[m]" value="<?=$_skin_ext['m']?>">
<input type="hidden" name="_skin_ext[g]" value="<?=$_skin_ext['g']?>">
<input type="hidden" name="edt_mode" value="<?=$_edt_mode?>">
<input type="hidden" name="type" value="<?=$_GET['type']?>">
	<?php
		if(!$_edt_mode){

			$edit_pg=$_GET['edit_pg'];
			if($edit_pg) list($pg1, $pg2)=explode("/", $edit_pg);
	?>
	<div id="controlTab" class="none_margin">
		<ul class="tabs design">
			<?php
				$ii=$layout_pg=1;
				$_pg_title="";
				foreach($_edit_list as $key=>$val){
					if($pg1 == $ii) $_pg_title=$key;
			?>
			<li onmouseover="document.getElementById('menu<?=$ii?>').style.display='block';" onmouseout="document.getElementById('menu<?=$ii?>').style.display='none';" class="<?=($pg1 == $ii) ? "selected" : "";?>">
				<?=$key?>
				<ul id="menu<?=$ii?>" style="display:none; position:absolute; top:<?=($pg1 == $ii) ? "61px" : "55px";?>; left:-1px; z-index:100; padding:10px; border:1px solid <?=($pg1 == $ii) ? "#00b4da" : "#c9c9c9";?>; background-color:#fff; font-size:11px; font-weight:normal; text-align:left;">
				<?php
					$jj=1;
					foreach($_edit_list[$key] as $key2=>$val){
						if($pg1 == $ii && $pg2 == $jj){
							$_pg_title .= " > ".$val;
							$_edit_pg=$key2;
							$_layout_pg=$layout_pg;
						}
						$_link=$PHP_SELF."?body=".$body."&type=".$_GET['type']."&edit_pg=".urlencode($ii."/".$jj);
						if($key == "게시판정보"){
							if(@preg_match("/^board_index/", $key2)) continue;
							if(!@preg_match("/.$_skin_ext[p]/", $key2)){
								$_link=$key2;
							}
						}
				?>
				<li style="display:block; width:220px; margin:0; padding:0; height:20px; border:0; background:none; text-align:left; line-height:20px;"><a href="<?=$_link?>"><?=($pg1 == $ii && $pg2 == $jj) ? "<strong>" : "";?><?=$val?><?=($pg1 == $ii && $pg2 == $jj) ? "</strong>" : "";?></a></li>
				<?php
						$jj++;
						$layout_pg++;
					}
				?>
				</ul>
			</li>
			<?php
					$ii++;
				}
			?>
		</ul>
	</div>
	<?php
		}

		if(!$file_dir) {
			$file_dir  = $root_dir."/_skin/".$_skin_name;
			$file_dir .= getExt($_edit_pg) == 'wsr' ? '/CORE/' : '/MODULE/';
		}
		if($_edit_pg == 'mail_order_product_list.wsm' && file_exists($file_dir.'/'.$_edit_pg) == false) { // 기본 스킨 복사
			include_once $engine_dir."/_engine/include/img_ftp.lib.php";
			$file['name'] = "mail_order_product_list.wsm";
			$file['tmp_name'] = $engine_dir.'/_engine/skin_module/default/MODULE/'.$_edit_pg;
			ftpUploadFile($file_dir, $file, "wsm");
		}
	?>
	<div class="box_middle">
		<input type="hidden" name="_file_src" value="<?=$_edit_pg?>">
        <input type="hidden" name="board_check" value="<?=$board_check?>">
		<?php
			if($_edit_pg){
				$file_content=getFContent($file_dir.$_edit_pg, 1);
				if($_edt_mode == "module"){
					if($file_content == ""){
						$file_content=$_replace_code[$design_edit_key][$design_edit_code];
					}
				}

				$popurl = './pop.php?';
				foreach($_GET as $key => $val) {
					$popurl .= '&'.$key.'='.urlencode($val);
				}
		?>
		<div style="position:relative; text-align:left;">
			<p style="padding:5px 0 20px;"><?=$_pg_title?></p>
			<div style="position:absolute; right:0; top:0;">
				<?php if (!$close_btn) { ?>
				<span class="box_btn_s gray"><input type="button" value="팝업창" onClick="templateZoom('', '<?=$popurl?>');"></span>
				<?php } ?>
				<span class="box_btn_s gray"><input type="button" value="이미지 FTP" onClick="imgFtpOpen();"></span>
			</div>
		</div>
		<?PHP
			}
			if($_edit_pg == 'shop_detail_popup.wsr' || $_edit_pg == 'shop_detail_frame.wsr') {
				$_real_edit_pg = $_edit_pg;
				$_edit_pg = 'shop_detail.wsr';
			}
			include_once $engine_dir."/_manage/design/editor_vals.php";
			if($design_edit_code != "" && @preg_match("/_list$/", $design_edit_code)) {
				$file_content=getListFContent($file_content, $design_edit_code);
				$_list_content=1;

				if($_edit_pg == 'detail_opt_img_list.wsm' || $_edit_pg == 'product_colorchip_list.wsm') {
					$_text_title[5] = '컬러코드 리스트 반복구문';
				}

                // 세트 추가 입력 폼
				if (
                    $cfg['use_set_product'] == 'Y' &&
                    preg_match('/(cart|cart_partner_sub|order_cart|order_cart_partner_sub|mypage_ord_cart|order_finish_prd)_list\.wsm/', $_edit_pg) == true
                ) {
					$_text_title[5] = '세트 메인 상품 반복 구문';
					$_text_title[6] = '세트 하위 상품 반복 구문';
				}
                if ($cfg['use_set_product'] == 'Y' && $_edit_pg == 'detail_multi_option_list.wsm') {
					$_text_title[5] = '세트 옵션 구문(선택사항)';
                }

		?>
		<br>
		<p style="padding:10px 0 5px; text-align:left;"><span class="p_color3">리스트 상단 (TABLE 선언 등 반복 구문의 상단 부분)</span></p>
		<textarea name="edt_content[1]" id="edt_content1" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:150px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[1])?></textarea>
		<p style="padding:10px 0 5px;text-align:left;"><span class="p_color3">리스트 반복 구문 (상품 목록 또는 게시물 목록 등 반복 구문)</span></p>
		<textarea name="edt_content[2]" id="edt_content2" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:200px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[2])?></textarea>
		<?php if ($_text_title[5]) { ?>
		<p style="padding:10px 0 5px;text-align:left;"><span class="p_color5"><?=$_text_title[5]?></span></p>
		<textarea name="edt_content[5]" id="edt_content5" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:200px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[5])?></textarea>
		<?php } ?>
		<?php if ($_text_title[6]) { ?>
		<p style="padding:10px 0 5px;text-align:left;"><span class="p_color5"><?=$_text_title[6]?></span></p>
		<textarea name="edt_content[6]" id="edt_content6" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:200px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[6])?></textarea>
		<?php } ?>
		<p style="padding:10px 0 5px;text-align:left;"><span class="p_color3">리스트 하단 (반복 구문의 하단 부분)</span></p>
		<textarea name="edt_content[3]" id="edt_content3" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:150px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[3])?></textarea>
		<p style="padding:10px 0 5px;text-align:left;"><span class="p_color3">데이터 없음 (출력 데이터가 존재하지 않을 경우 반복문 대신 출력)</span></p>
		<textarea name="edt_content[4]" id="edt_content4" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; height:150px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content[4])?></textarea>

		<script type="text/javascript">
		<?PHP
			$edit_areas = array('edt_content1', 'edt_content2', 'edt_content3', 'edt_content4', 'edt_content5', 'edt_content6');

			foreach($edit_areas as $area) {?>
			if($('#<?=$area?>').length == 1) {
				editAreaLoader.init({
					id: "<?=$area?>"
					,start_highlight: true
					,allow_resize: "both"
					,allow_toggle: false
					,word_wrap: true
					,replace_tab_by_spaces: false
					,language: "kr"
					,syntax: "html"
					,font_family: 'dotum'
				})
			};
		<?php } ?>
		</script>
		<?php } else { ?>
		<div>
			<textarea name="edt_content" id="edt_content" class="txta" onkeydown="editorKeyUp(this);" style="height:600px; width:800px; font-size:9pt;" <?=(!$edit_pg) ? " disabled" : ""?>><?=htmlspecialchars($file_content)?></textarea>
			<script type="text/javascript">
			editAreaLoader.init({
				id: "edt_content"
				,start_highlight: true
				,allow_resize: "both"
				,allow_toggle: false
				,word_wrap: true
				,replace_tab_by_spaces: false
				,language: "kr"
				,syntax: "html"
				,font_family: 'dotum'
			});
			</script>
		</div>
		<?php
			}
		?>
	</div>
	<?php
		if($_edit_pg){
			if(!$_edt_mode){
				$_ori_pg=oriPageUrl($_edit_pg);
				$_ori_pg=strchr($_ori_pg_content, "skin_".str_replace(".".$_skin_ext['p'], ".php", $_edit_pg)."_big_table") ? $_ori_pg : "";
			}
	?>
	<div id="button_footer" class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="저장하기"></span>
		<span class="box_btn gray"><input type="button" value="복구하기" onclick="restoreFile();"></span>
		<?php
			if($_layout_pg){
		?>
			<?php if($_GET['type'] != 'mobile' && $_inc[0] != 'wmb') { ?>
			<span class="box_btn gray" style="display:none;"><input type="button" value="레이아웃설정" onclick="window.open('./?body=design@layout&edit_pg=<?=$_layout_pg?>#pls');"></span>
			<?php } ?>
		<?php
			}
			if($close_btn){
		?>
		<span class="box_btn gray"><input type="button" value="창닫기" onclick="window.close();"></span>
		<?php
			}
		?>
	</div>
	<div id="restore_div">
	</div>
	<?php
		}
	?>
</form>
<div id="temp"></div>
<?php
	if($_edt_mode != "css" && $_edt_mode != "script" && !($_edt_mode == "module" && $design_edit_key == "common")){
		$_code_output=1;
		if($_edt_mode == "common") $_edit_pg=""; // 공통 코드 출력하기 위해..
?>
<div id="controlTab">
	<ul class="tabs">
		<?php if ($_edit_pg) { ?>
		<li onclick="getCodeList('edit_pg', '', this);" id="c_edit_pg" class="selected"><?=($_edt_mode == "module") ? "모듈" : "페이지"?> 코드</li>
		<?php } ?>
		<li onclick="getCodeList('common', '', this);" id="c_common">공통 코드</li>
		<?php if($_edt_mode != "module") { ?>
		<li onclick="getCodeList('user_code', '', this);" id="c_user_code">사용자 코드</li>
		<?php } ?>
		<li onclick="getCodeList('page_link', '', this);" id="c_page_link">페이지 링크</li>
	</ul>
</div>
<div id="code_list"></div>
<?php
	}
	designValUnset();
?>
<script type="text/javascript">
var edt_filename = '<?=$filename?>';
var edt_skinname = '<?=$skinname?>';
</script>
<form name="popFrm" action="./pop.php" method="get">
<input type="hidden" name="body" value="design@editor.frm">
<input type="hidden" name="design_edit_key">
<input type="hidden" name="design_edit_code">
<input type="hidden" name="type" value="<?=$_GET['type']?>">
</form>
<script type="text/javascript">
	edt_mode='<?=$_edt_mode?>';
	edit_pg='<?=$_edit_pg?>';
	design_edit_key='<?=$design_edit_key?>';
	design_edit_code='<?=$design_edit_code?>';
	skinZoom='';
	popup_edit='';
	<?php if($close_btn) { ?>
	popup_open=1;
	<?php } elseif(!$_edt_mode) { ?>
	window.onfocus=function (){
		popEditCk();
	}
	window.onblur=function (){
		popEditCk();
	}
	<?php } ?>
	function ckFrm(f){
		f.exec.value='modify';
		<?php if(!$close_btn && !$_edt_mode) { ?>
			if(popup_edit){
				alert(' 팝업창에서 편집중이므로 팝업창을 닫고 시도하여 주시기 바랍니다    ');
				return false;
			}
		<?php } ?>
        printLoading();
	}
	function popEditCk(){
		f=document.editFrm;
		if(typeof skinZoom.popup_open == 'number'){
			f.edt_content.disabled=true;
			popup_edit=1;
		}else{
			f.edt_content.disabled=false;
			popup_edit='';
		}
	}
	function printData(){
		result=req.responseText;
		var mdv=document.getElementById('code_list');
		mdv.innerHTML=result;
	}
	function getCodeList(code_key, txt, w){
		if(!code_key) code_key='';
		if(!txt) txt='';

		var param = {
			body: 'design@editor_code.exe',
			code_key: code_key,
			txt: txt,
			_edt_mode: edt_mode,
			_edit_pg: edit_pg,
			design_edit_key: design_edit_key,
			design_edit_code: design_edit_code,
			type: '<?=$_GET['type']?>',
			filename: edt_filename
		}

		$.get('./', param, function(r) {
			if(r) {
				tmp=document.getElementById(c_tmp);
				tmp.className='';

				$('#c_'+code_key).addClass('selected');
				if(w){
					c_tmp=w.id;
				}

				var mdv=document.getElementById('code_list');
				mdv.innerHTML=r;
			}
		});
	}
	function restoreFile(filename){
		filename=filename ? filename : '';
		f=document.editFrm;
		if(filename != ''){
			date=f.restore_file[f.restore_file.selectedIndex].text;
			if(!confirm(date+' 에 저장된 파일로 복구를 실행하시겠습니까?')) return;
			f.exec.value='restore';
			f.submit();
			return;
		}
		f.exec.value='restore_load';
		f.submit();
	}
	function insertCode(code){
		var fr;
		if(document.getElementById('frame_edt_content2')) {
			fr = document.getElementById('frame_edt_content2').contentWindow;
		} else {
			fr = document.getElementById('frame_edt_content').contentWindow;
		}

		var textarea = fr.document.getElementById('textarea');
		var nextfocus = textarea.selectionEnd + code.length;
		textarea.focus();
		fr.editArea.textareaFocused = true;
		textarea.value = textarea.value.substr(0, textarea.selectionStart)+code+textarea.value.substr(textarea.selectionEnd);

		if(textarea.setSelectionRange) {
			textarea.focus();
			textarea.setSelectionRange(nextfocus, nextfocus);
		} else if(textarea.createTextRange) {
			var range = textarea.createTextRange();
			range.collapse(true);
			range.moveEnd('character', nextfocus);
			range.moveStart('character', nextfocus);
			range.select();
		}
	}
	function editCode(key, code){
		f=document.popFrm;
		f.design_edit_key.value=key;
		f.design_edit_code.value=code;
		var viewId = 'codePop';
		if(getCookie('def_dmode') != '0') viewId+=code;

		var a = window.open('about:blank',viewId,'top=10,left=10,width=950,status=no,toolbars=no,scrollbars=yes,height=700');
		if(a) a.focus();
		f.target=viewId;
		f.submit();
	}
	function skinSetVals(){
		f=document.editFrm;
		f.exec.value='skin_vals';
		f.submit();
	}
	function imgFtpOpen(){
		window.open('about:blank','commonIMG','top=10,left=10,width=900,status=no,toolbars=no,scrollbars=yes,height=800');
		f=document.popFrm;
		f.body.value='design@common_img';
		f.target='commonIMG';
		f.submit();
		f.body.value=f.body.defaultValue;
	}
	function userCode(w){
		if(!w) w='';
		var viewId='userCode';
		if(getCookie('def_dmode')!='0') viewId+=w;
		url='./pop.php?body=design@editor_user.frm&type=<?=$_GET['type']?>&user_code='+w;
		window.open(url,viewId,'top=10,left=10,width=850,status=no,toolbars=no,scrollbars=yes,height=700');
	}
	function editorKeyUp(w){
		if(event.keyCode == 9){
			(w.selection=document.selection.createRange()).text='\t';
			event.returnValue = false;
		}
	}
	function editorFS(del){
		if(popup_edit){
			alert(' 팝업창에서 편집중이므로 팝업창을 닫고 시도하여 주시기 바랍니다    ');
			return;
		}
		del=del ? del : '';
		if(del){
			div1=document.getElementById('dmMainBgDiv');
			div2=document.getElementById('dmMainDiv');
			document.body.removeChild(div1);
			document.body.removeChild(div2);
			return;
		}
		bw=(document.body.clientWidth)-20;
		bh=(document.body.clientHeight)-5;
		txth=bh-80;
		createBackDiv(this, '#FFFFFF', bw, bh);
		div2=document.getElementById('dmMainDiv');
		txt2_html='<form name="full_editFrm"><textarea name="full_edt_content" id="full_edt_content" class="txta" onkeydown="editorKeyUp(this);" style="width:100%; margin:5px; height:'+txth+'; background-color:#FFFFFF; border:1px solid #DDDDDD;"></textarea></form><center><a href="javascript:;" onclick="sendEditorData(1);"><?=btn2("전송", 2)?></a> <a href="javascript:;" onclick="sendEditorData(2);"><?=btn2("새로고침")?></a> <a href="javascript:;" onclick="editorFS(1);"><?=btn2("창닫기")?></a>';
		div2.innerHTML=txt2_html;
		sendEditorData(2);
	}
	function sendEditorData(w){
		f1=document.editFrm;
		txt1=f1.edt_content;
		f2=document.full_editFrm;
		txt2=f2.full_edt_content;
		if(!f2) return;
		if(w == 1){
			txt1.value=txt2.value;
			editorFS(1);
		}else if(w == 2){
			txt2.value=txt1.value;
		}
	}
	function txtResize(){
		textarea_width=document.getElementById('button_footer').offsetWidth-30;
    	<?php if ($_list_content == 1) { ?>
		document.getElementById('edt_content1').style.width=textarea_width+'px';
		document.getElementById('edt_content2').style.width=textarea_width+'px';
		document.getElementById('edt_content3').style.width=textarea_width+'px';
		document.getElementById('edt_content4').style.width=textarea_width+'px';
    	<?php } else { ?>
		document.getElementById('edt_content').style.width=textarea_width+'px';
    	<?php } ?>
	}
	window.onload=function (){
		this.focus();
	}
	window.onresize=function (){
		txtResize();
	}

	<?php if($_code_output){ ?>
		getCodeList('<?=($_edt_mode == "common") ? "common" : "edit_pg";?>');
		c_tmp='<?=($_edt_mode == "common") ? "c_common" : "c_edit_pg";?>';
	<?php  } ?>
	txtResize();

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});
</script>