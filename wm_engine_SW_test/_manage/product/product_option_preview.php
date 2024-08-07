<?PHP

	$opno = numberOnly($_GET['opno']);
	checkBlank($opno,"필수값을 입력해주세요.");

	$data=get_info($tbl['product_option_set'],"no",$opno);
	if(!$data[no]) msg("존재하지 않는 자료입니다","close");

	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$data[name]=stripslashes($data[name]);
	$imgsrc="/".$GLOBALS['dir'][upload]."/prd_common/".$data[upfile1]; // 2007-01-29 : 옵션타이틀 이미지검색 - Han
	if($data[upfile1] && is_file($GLOBALS[root_dir].$imgsrc)){ $data[name]="<img src=\"".$GLOBALS[root_url]."$imgsrc\">"; }

?>
<table class="tbl_row pop_width">
	<caption class="hidden">옵션미리보기</caption>
	<colgroup>
		<col style="width:30%">
		<col>
	</colgroup>
	<tr>
		<th scope="row"><?=$data[name]?></th>
		<td><?=printOption($data)?></td>
	</tr>
</table>
<div class="pop_bottom"><?=$close_btn?></div>