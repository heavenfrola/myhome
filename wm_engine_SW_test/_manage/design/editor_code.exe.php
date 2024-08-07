<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

	printAjaxHeader();
	$_edt_mode = addslashes($_GET['_edt_mode']);
	$code_key = addslashes($_GET['code_key']);
	$txt = addslashes($_GET['txt']);
	$_edit_pg = addslashes($_GET['_edit_pg']);
	$design_edit_key = addslashes($_GET['design_edit_key']);
	$design_edit_code = addslashes($_GET['design_edit_code']);
	$type = addslashes($_GET['type']);
	$filename = addslashes($_GET['filename']);
	$code_page = $_GET['code_page'];

	$_code_key = array('edit_pg', 'common', 'user_code', 'page_link');
	if(in_array($code_key, $_code_key) == false) {
		$code_key = 'common';
	}

	if($_edt_mode == "board"){
		$_edit_pg .= ".".$_skin_ext['p'];
	}
	$_ori_pg = $_edit_pg;

	$_edit_pg = preg_replace("/_(eng|chn|jpn)\./", ".", $_edit_pg);
	$_edit_pg=$design_edit_key ? $design_edit_key : $_edit_pg;
	include_once $engine_dir."/_manage/skin_module/_skin_module.php";

	$_code_arr=array("edit_pg"=>$_file_name, "common"=>"|common|common_module|", "user_code"=>"user_code");
	$_code_form_arr=array("_url$"=>"주소", "_form$"=>"삽입 폼 (일부페이지 사용)", "_list$|_none$"=>"반복문", "form_start$|form_end$"=>"폼 선언", "etc"=>"기타");
	if($_edt_mode == "module"){
		unset($_replace_code['common_module'], $_replace_code[$design_edit_key]);
		$_file_name=$_edit_pg;
		$_module_values=$_replace_datavals[$design_edit_key][$design_edit_code];
		if($_module_values == "") $_file_name="";
		else{
			$_module_values=explode(";", $_module_values);
			foreach($_module_values as $key=>$val){
				if(!$val) continue;
				list($_hangul, $_val, $_comment, $_attr)=explode(":", $val);
				$_replace_code[$_file_name][$_val]="";
				$_replace_hangul[$_file_name][$_val]=$_hangul;
				$_code_comment[$_file_name][$_val]=$_comment;
				$_auto_replace[$_file_name][$_val] = ($_attr == 'editable') ? '' : 'Y';
			}
		}
	}

	$txt=trim($txt);
	$txt=str_replace("'", "", $txt);
	$txt=str_replace('"', "", $txt);
	$td_style=" onmouseover=\"this.style.backgroundColor='#f0f1ff';\" onmouseout=\"this.style.backgroundColor='';\"";

	if($code_key == "page_link"){

?>
<table class="tbl_col nonbd_top">
	<thead>
		<tr>
			<th scope="col" style="width:300px;">페이지명</th>
			<th>주소</th>
			<?php if (!$code_page) { ?>
			<th style="width:100px">삽입</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php
			include_once $engine_dir."/_manage/design/editor_page.inc.php";
			$_edit_list['회원관련']["{{\$로그아웃}}"]="로그아웃";

			$ii=0;
			foreach($_edit_list as $key=>$val){
		?>
		<tr>
			<td class="left"><strong class="p_color3"><?=$key?></strong></td>
			<td></td>
			<?php if (!$code_page) { ?>
			<td></td>
			<?php } ?>
		</tr>
		<?php
			foreach($_edit_list[$key] as $key2=>$val2){
				if(preg_match("/\?body=/", $key2) == true) continue;
				if(preg_match("/^\.\/?body=/", $key2)) continue;
				if(in_array($key2, $_idvy_not_used_list)) continue;
				$_ori_pg=(preg_match("/\//", $key2)) ? $key2 : oriPageUrl($key2);
				$_cont_pg=@preg_replace("/(content\/)|(\.php)$/", "", $_ori_pg);
				if(preg_match("/^content\//", $_ori_pg) && !preg_match("/\//", $key2)) $_ori_pg="content/content.php?cont=".$_cont_pg;
				if($key2 == 'content_customer.wsr') $_ori_pg = 'content/customer.php';
				$_pg_link=(@strchr($key2, "\$")) ? $key2 : "{{\$사이트주소}}/".$_ori_pg;
		?>
		<tr<?=$td_style?>>
			<td<?=$td_style?> class="left"><span id="<?=$key.$ii?>"><?=$val2?></span></td>
			<td<?=$td_style?> class="left"><span class="clipboard" data-clipboard-text="<?=$_pg_link?>"><?=$_pg_link?></span></td>
			<?php if(!$code_page) { ?>
			<td><span class="box_btn_s gray"><input type="button" value="삽입하기" onClick="insertCode('<?=$_pg_link?>');"></span></td>
			<?php } ?>
		</tr>
		<?php
					$ii++;
				}
			}
		?>
	</tbody>
</table>
<?php } else { ?>
<table class="tbl_col nonbd_top">
	<caption class="hidden">코드 리스트</caption>
	<thead>
		<tr>
			<th scope="col" style="width:300px;">코드명</th>
			<th scope="col">코드설명</th>
			<?php if ($_edt_mode != "module") { ?>
			<th scope="col" style="width:170px">편집</th>
			<?php } if (!$code_page){ ?>
			<th scope="col" style="width:100px">기타</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php
		$_code_key=explode("|", $_code_arr[$code_key]);
		for($jj=0; $jj<count($_code_key); $jj++){
			if($_code_key[$jj] == "") continue;
			$key=$_code_key[$jj];
			if(!is_array($_replace_code[$key])) continue;
			$_etc_form="";
			foreach($_code_form_arr as $ckey=>$cval){
				${"_cf_line_".$ckey}=0;
				foreach($_replace_code[$key] as $key2=>$val2){
					if($_hidden_code[$key][$key2] == "Y") continue;
					if($code_key == "user_code" && !$_replace_user_code[$key][$key2]) continue;
					if($ckey == "etc"){
						if(preg_match("/$_etc_form/", $key2)) continue;
					}else{
						if(!preg_match("/$ckey/", $key2)) continue;
					}
					if(!${"_cf_line_".$ckey} && $txt == ""){
						${"_cf_line_".$ckey}=1;
	?>
	<tr>
		<td class="left"><strong class="p_color3"><?=($key == "common" && $ckey == "etc") ? "정보" : $cval?></strong></td>
		<td></td>
		<?php if ($_edt_mode != "module") { ?>
		<td></td>
		<?php } if (!$code_page) { ?>
		<td></td>
		<?php } ?>
	</tr>
	<?php
					}
		$_hangul=$_replace_hangul[$key][$key2];
		$_comment=$_code_comment[$key][$key2];
		$_comment=$_comment ? $_comment : $_hangul;
		$_code_o="{{\$".$_hangul."}}";
		if($txt != "" && !@strchr($_code_o, $txt)) continue;
		if($_edt_mode != 'module' && $_code_sub[$key][$key2] == 'Y') continue;
		$_design_edit_key=str_replace(".php", ".tmp", $key);
		$_design_edit_code=urlencode($key2);
		$_edit_script=($_replace_user_code[$key][$key2]) ? "userCode(".$_replace_user_code[$key][$key2].");" : "editCode('".$_design_edit_key."', '".$_design_edit_code."');";
		if($_auto_replace[$key][$key2] == "") {
			$_edt_btn = "<span class=\"box_btn_s gray\"><input type=\"button\" value=\"편집하기\" onClick=\"".$_edit_script."\"></span>";
		} else {
			$_edt_btn = ($_edt_mode != "module") ? "<span class=\"sblue\">자동출력</span>" : '';
		}
		if($code_key == 'user_code') $_del_btn="<span class=\"box_btn_s gray\"><input type=\"button\" value=\"삭제하기\" onClick=\"delCode('".$key2."');\"></span>";
	?>
	<tr<?=$td_style?>>
		<td class="left">
			<span id="<?=$key.$kk?>" class="clipboard" data-clipboard-text="<?=$_code_o?>"><?=$_code_o?></span>
		</td>
		<td class="left">
			<?=$_comment?>
			<?=($admin['admin_id'] == "wisa" && !$_edt_mode && $test) ? "<br><span style=\"color:#cc00ff\">[위사전용] ".$key2.".".$_skin_ext['m']."</span>" : "";?>
		</td>
		<?php if ($_edt_mode != "module") { ?>
		<td><?=$_edt_btn?> <?=$_del_btn?></td>
		<?php } if (!$code_page) { ?>
		<td>
			<?php if ($_edt_mode == "module") { ?>
			<div style="margin-bottom:3px;"><?=$_edt_btn?></div>
			<?php } ?>
			<span class="box_btn_s gray"><input type="button" value="삽입하기" onClick="insertCode('<?=$_code_o?>');"></span>
		</td>
		<?php } ?>
	</tr>
	<?php
					$kk++;
				}
				$_etc_form .= $_etc_form ? "|".$ckey : $ckey;
			}
		}
		if($_edt_mode != "module" && $_edt_mode != "board"){
	?>
	</tbody>
</table>
<div class="box_bottom">
	<input type="text" name="code_search" class="input" size="40">
	<span class="box_btn gray"><input type="button" value="검색" onClick="getCodeList('<?=$code_key?>', $('input[name=code_search]').val());"></span>
	<span class="box_btn gray"><input type="button" value="코드생성하기" onclick="userCode();"></span>
</div>
	<?php
		}
	?>

<?php
	}

	designValUnset();
?>