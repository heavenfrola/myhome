<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네비게이터 편집
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_manage/design/template_name.php";
	if(file_exists($root_dir."/_config/content_add.php")) include $root_dir."/_config/content_add.php";
	if(file_exists($root_dir."/_config/title_name.php")){
		include $root_dir."/_config/title_name.php";
		$mod_title=1;
	}

	$_set1_dir = array('member' => 'Membership', 'mypage' => 'Mypage');
	$_set1_no = array('big_section.php', 'zoom.php', 'detail.php', 'product_qna_secret.php', 'product_review_mod_frm.php', 'product_qna_mod_frm.php');
	$_set2_no = array('join_frm_oversea.php', 'msg_send.php', 'sms_find.php');
	if($cfg['design_version'] == 'V3') {
		$_dir = $root_dir.'/';
		$_copyw = '{{$네비게이터}}';
		$_set2_dir = array('/_template/content', 'shop');
	} else {
		$_dir = $root_dir.'/_template';
		$_set2_dir = array('content', 'shop');
		$_copyw = '&lt;?=getPageName()?&gt;';
	}

?>
<div class="box_title first">
	<h2 class="title">네비게이터 편집</h2>
</div>
<div class="box_middle left">
	<ul class="list_msg left">
		<li>타이틀을 삽입해 주시면 현재 사용중인 페이지의 위치를 이용자들이 쉽게 알 수 있도록 도와줍니다.</li>
		<li>출력을 원하시는 위치, 각 스킨 페이지들의 상단 또는 공통 페이지의 상단정보에 네비게이터 코드를 입력해주시면 바로 이용이 가능합니다.</li>
	</ul>
	<span class="box_btn_s blue"><input type="button" value="네비게이션 코드 복사하기" onClick="window.clipboardData.setData('Text','<?=$_copyw?>'); alert('복사되었습니다');"></span> &nbsp;클릭하여 복사하신 후 페이지 편집기능을 이용하여 원하시는 위치에 삽입해주시면 자동으로 출력됩니다.
</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs square">
		<li onclick="location.href='./?body=design@board';" class="selected">일반 타이틀</li>
		<li onclick="window.open('./?body=product@catework');">상품 타이틀</li>
		<li onclick="window.open('./?body=board@board_new_list');">게시판 타이틀</li>
	</ul>
</div>
<form name="editFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="return confirm('현재 설정대로 저장하시겠습니까?');">
	<input type="hidden" name="body" value="design@title.exe">
	<input type="hidden" name="exec" value="">
	<div class="box_middle left">
		<ul class="list_common2">
			<li><input type="text" name="home" value="<?=$mod_title ? inputText($_page_title['home']) : 'Home';?>" size="15" class="input">
			디렉토리 연결문자 <input type="text" name="joint" value="<?=$mod_title ? htmlspecialchars($_page_title['joint']) : htmlspecialchars(" &gt; ");?>" size="5" class="input"></li>
			<?php
				foreach($_set2_dir as $key=>$val){
					$val=trim($val);
					$odir=@opendir($_dir."/".$val);
					while($arr=@readdir($odir)){
						$arr=trim($arr);
						if(@is_file($_dir."/".$val."/".$arr)){
							if(in_array($arr, $_set1_no)) continue;
							$_ext=getExt($arr);
							$_arr=str_replace(".".$_ext, "", $arr);
							$_val=preg_replace("/\/(.*?)\//", "", $val);
							$value=($mod_title) ? $_page_sub_title[$_val."/".$_arr] : $dir_sub_arr[$_val][$arr];
							if($val == "content" && !$value){
								$value=$_content_add_info[$_arr]['name'];
							}
							$value = stripslashes($value);
			?>
			<li style="padding-left:20px;"><input type="text" name="<?=$_val?>/<?=$_arr?>" value="<?=$value?>" size="25" class="input"> <?=$_val?>/<?=$arr?></li>
			<?php
						}
					}
					@closedir($odir);
				}
				foreach($_set1_dir as $key=>$val){
			?>
			<li style="padding-left:20px;"><input type="text" name="<?=$key?>" value="<?=$mod_title ? inputText($_page_title[$key]) : $val?>" size="24" class="input"></li>
			<?php
				$odir=@opendir($_dir."/".$key);
				while($arr=@readdir($odir)){
					if(@is_file($_dir."/".$key."/".$arr)){
						if(in_array($arr, $_set2_no)) continue;
						$_ext=getExt($arr);
						$_arr=str_replace(".".$_ext, "", $arr);
						$value=($mod_title) ? $_page_sub_title[$key."/".$_arr] : $dir_sub_arr[$key][$arr];
			?>
			<li style="padding-left:40px;"><input type="text" name="<?=$key?>/<?=$_arr?>" value="<?=$value?>" size="25" class="input"> <?=$key?>/<?=$arr?></li>
			<?php
						}
					}
					@closedir($odir);
				}
			?>
		</ul>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="설정완료"></span>
		<?php if ($mod_title) { ?><span class="box_btn"><input type="button" value="초기화" onclick="resetTitle();"></span><?php } ?>
	</div>
</form>

<script type="text/javascript">
	function resetTitle(){
		if(!confirm('타이틀명을 초기화하시겠습니까?')) return;
		f=document.editFrm;
		f.exec.value='reset';
		f.submit();
	}
</script>