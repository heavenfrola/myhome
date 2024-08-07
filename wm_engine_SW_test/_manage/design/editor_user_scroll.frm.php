<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사용자 코드 편집 - 스크롤
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();

	include_once $engine_dir."/_manage/skin_module/_skin_module.php";
	unset($_user_code);

	$_code_num=$_POST[user_code] ? $_POST[user_code] : $_POST[new_code];

	foreach($_POST as $key=>$val){
		$_user_code[$_code_num][$key]=$val;
	}
	if($ctype == "2"){
		$_cate=@implode(",", $_POST[ebig]);
	}elseif($ctype == "1"){
		$_cate=$_POST[big];
		$_cate .= $_POST[mid] ? ",".$_POST[mid] : "";
		$_cate .= $_POST[small] ? ",".$_POST[small] : "";
	}elseif($ctype == "4"){
		$_cate=$_POST[xbig];
		$_cate .= $_POST[xmid] ? ",".$_POST[xmid] : "";
		$_cate .= $_POST[xsmall] ? ",".$_POST[xsmall] : "";
	}elseif($ctype == "5"){
		$_cate=$_POST[ybig];
		$_cate .= $_POST[ymid] ? ",".$_POST[ymid] : "";
		$_cate .= $_POST[ysmall] ? ",".$_POST[ysmall] : "";
	}
	$_user_code[$_code_num][cate]=$_cate;
	$_user_code[$_code_num][page_type]="a";
	$_user_code[$_code_num][image_link]=ucodeImageLink();

	$_user_code_name=userCodeName($_code_num);
	include_once $engine_dir."/_engine/skin_module/_skin_module.php";

?>
<div style="width:100%; padding:10px;">
	<h3>스크립트 효과 미리보기</h3> <div style="width:20px; height:20px; border:2px solid #330000; display:inline;"></div>
	<div style="display:inline;">&nbsp;안의 영역<br><br></div>
	<?=$_replace_code[$_file_name][$_code_name]?>
</div>
<style type="text/css">
<!--
#user_scroll_code<?=$_code_num?>{border:2px solid #330000;}
-->
</style>
