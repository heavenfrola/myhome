<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기본 텍스트 편집
	' +----------------------------------------------------------------------------------------------+*/

    include_once $engine_dir."/_engine/include/design.lib.php";
    include_once $engine_dir."/_manage/design/version_check.php";
    $_skin_name=editSkinName();
    include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

    versionChk("V3");

    include_once $engine_dir."/_manage/main/main_box.php";

    $text_type=$_GET['text_type'] ? $_GET['text_type'] : 1;

?>
<div class="box_title first">
	<h2 class="title">기본 텍스트 편집</h2>
</div>
<div class="box_middle">
	<ul class="list_msg left">
		<li><?=editSkinNotice()?></li>
		<li>개별 텍스트 스타일을 설정하실 경우 소스내에서 미리 설정된 스타일시트(기본)보다 우선적으로 적용됩니다.</li>
		<li>일부만 변경을 원하실 경우 부분만 설정하셔도 적용됩니다.</li>
	</ul>
</div>
<form name="styleFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="return confirm('\n소스내에 삽입된 스타일보다 우선적으로 적용됩니다.     \n\n적용하시겠습니까?');">
	<input type="hidden" name="body" value="design@text_edit.exe">
	<input type="hidden" name="text_type" value="<?=$text_type?>">
	<div id="controlTab" class="none_margin">
		<ul class="tabs square">
			<?php
				$ii=1;
				foreach($_basic_text_style as $key=>$val){
					if($ii == $text_type) $_skey=$key;
			?>
			<li onclick="location.href='./?body=<?=$body?>&text_type=<?=$ii?>';" class="<?=($text_type == $ii) ? "selected" : "";?>"><?=$key?></li>
			<?php
				$ii++;
				}
			?>
		</ul>
	</div>
	<table class="tbl_col nonbd_top">
		<caption class="hidden">기본 텍스트 편집</caption>
		<thead>
			<tr>
				<th scope="col">분류</th>
				<th scope="col">텍스트명</th>
				<th scope="col">글꼴</th>
				<th scope="col">색상</th>
				<th scope="col">크기</th>
				<th scope="col">두께</th>
				<th scope="col">스타일</th>
				<th scope="col">라인</th>
			</tr>
		</thead>
		<tbody>
			<?php
				function textStyle() {
					global $_fd_key, $text_type, $_skin;
					$_skin_val=$_skin["text_edit_".$text_type."_".$_fd_key];
					$_style=array("family", "size", "weight", "style", "color", "decoration");
					foreach($_style as $key=>$val){
						${"_".$val}=(strpos($_skin_val, $val.":") && $_skin_val) ? preg_replace("/(.*)(".$val.":)([^;]*);(.*)/", "$3", $_skin_val) : "";
				}
			?>
			 <td nowrap="nowrap">
				<input type="text" name="family[<?=$_fd_key?>]" class="input" style="width:85px;" maxlength="20" value="<?=$_family?>" onchange="selectedTxt('<?=$_fd_key?>');">
				 <span class="box_btn_s gray">
					 <input type="button" value="선택" onClick="divSelector('<?=$_fd_key?>');">
				 </span>
				 <div style="position:relative;"><div class="selector" id="family_selector_<?=$_fd_key?>"></div></div>
			 </td>
			 <td nowrap="nowrap">
				<div class="colorpicker_marker" style="position:absolute; display:none;"></div>
				<input type="text" name="color[<?=$_fd_key?>]" class="input colorpicker" style="background-color:<?=$_color?>; color:#000;" size="7" maxlength="7" value="<?=$_color?>">
				<span class="box_btn_s gray colorbtn"><input type="button" value="선택"></span>
			</td>
			<td>
				<input type="text" name="size[<?=$_fd_key?>]" class="input" style="width:40px;" maxlength="10" value="<?=$_size?>">
				<span class="box_btn_s gray">
					<input type="button" value="선택" onClick="divSelector('<?=$_fd_key?>', 'size');">
				</span>
				<div style="position:relative;"><div class="selector" id="size_selector_<?=$_fd_key?>"></div></div>
			</td>
			<td><?=selectArray(array("Normal", "Bold", "Bolder", "Lighter"), "weight[$_fd_key]", 1, "기본", $_weight)?></td>
			<td><?=selectArray(array("Normal", "Italic"), "style[$_fd_key]", 1, "기본", $_style)?></td>
			<td><?=selectArray(array("underline"=>"아래", "overline"=>"위", "line-through"=>"중간"), "deco[$_fd_key]", 2, "기본", $_decoration)?></td>
			<?php
				}
				foreach($_basic_text_style[$_skey] as $key=>$val){
					$_page=explode("|", $key);
					$_value=explode(";", $val);
					foreach($_page as $pkey=>$pval){
						$_page_name=getEditPageName($pval);
						foreach($_value as $vkey=>$vval){
							if(!$vval) continue;
							list($_title, $_fd)=explode(":", $vval);
							$_fd_key=$pval.":".$_fd;
			?>
			<tr onmouseover="this.style.backgroundColor='#efefef';" onmouseout="this.style.backgroundColor='';">
				<td><b><?=($_tmp != $_page_name) ? $_page_name."" : "&nbsp;"?></b></td>
				<td><?=$_title?></td>
				<?php textStyle(); ?>
			</tr>
			<?php
							$_tmp=$_page_name;
						}
					}
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php designValUnset(); ?>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.js"></script>
<script type="text/javascript" charset="utf-8">
	var familyarr = new Array('돋움', '굴림', '바탕', '궁서', '명조', 'Arial', 'Arial Black', 'Arial Narrow', 'Verdana', 'Tahoma', 'System', 'Courier New', 'Georgia');
	var sizearr = new Array('8pt', '9pt', '10pt', '12pt', '14pt', '16pt', '9px', '10px', '11px', '12px', '14px', '16px', '18px', '20px');
	function divSelector(fname, type){
		type=type ? type : 'family';
		var width = type == 'family' ? '80' : '50';
		var w = document.getElementById(type+'_selector_'+fname);
		if(w.style.display == 'block'){
			w.style.display='none';
			w.innerHTML='';
			return;
		}

		var content='';
		var arr = eval(type+'arr');
		for(ii=0; ii<arr.length; ii++) {
			content += '<div><a href="javascript:;" onclick="selectedTxt(\''+fname+'\', \''+arr[ii]+'\', \''+type+'\');"><font onmouseover="this.id=\'selected\'" onmouseout="this.id=\'\'" style="width:'+width+'px; font-'+type+':'+arr[ii]+';">'+arr[ii]+'</font></a></div>';
		}
		w.innerHTML = content;
		w.style.display = 'block';
	}

	function selectedTxt(fname, font, type){
		type = type ? type : 'family';
		font = (!font) ? document.styleFrm[type+'['+fname+']'].value : font;
		document.styleFrm[type+'['+fname+']'].value = font;
		var w = document.getElementById(type+'_selector_'+fname);
		w.style.display = 'none';
		w.innerHTML = '';
	}

	function orderColorChg() {

	}

	$(document).ready(function() {
		$('.colorpicker').focus(function() {
			if(!this.value) this.value = ' ';
			$('.colorpicker_marker').hide();
			$(this).parent().find('.colorpicker_marker').show();
		});
		$('.colorpicker').blur(function() {
			$('.colorpicker_marker').hide();
		});
		$('.colorpicker_marker').each(function(idx) {
			$(this).farbtastic($('.colorpicker').eq(idx));
		});
		$('.colorbtn').click(function() {
			$(this).parent().find('.colorpicker').focus();
		});
	});
</script>
<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?=$engine_url?>" type="text/css">
<style type="text/css">
#selected{
	background-color: #e7e7e7;
	cursor: pointer;
}

.selector {
	position:absolute;
	top:0px;
	left:0px;
	padding: 3px;
	background-color: #fff;
	border:1px solid #e4e4e4;
	border-top:none;
	padding:2px;
	display:none;
}

.colorpicker_marker {
	z-index: 2;
}
</style>