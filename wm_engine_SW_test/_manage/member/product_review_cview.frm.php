<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	$from_ajax = $_GET['from_ajax'];
	$fixbtn = ($from_ajax) ? "" : "fixbtn";

	$data = $pdo->assoc("select a.*, b.`pno` from `$tbl[review_comment]` a inner join `$tbl[review]` b on a.`ref` = b.`no` where a.`no`='$no'");
	if (!$data) msg("존재하지 않는 코멘트입니다");

	if($data['pno']) {
		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$data[pno]'");
		$prd['name'] = cutStr(strip_tags(stripslashes($prd['name'])), 60);
	}
?>
<script language="JavaScript" src="<?=$engine_url?>/_engine/common/resize.js"></script>
<?if($from_ajax) {?>
<div id="popupContent" class="popupContent layerPop" style="z-index:1001;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
	</div>
<?}?>
<form name="reviewFrm2" method="post" action="./index.php" onSubmit="return checkPrdReview(this)" class="pop_width <?=$fixbtn?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="comment_edit">
	<input type="hidden" name="body" value="member@product_review_update.exe">
	<input type="hidden" name="mode" value="single">
	<table class="tbl_row">
		<caption class="hidden">상품평</caption>
		<colgroup>
			<col style="width:15%;">
			<col style="width:85%;">
		</colgroup>
		<tr>
			<th scope="row">상품명</th>
			<td>
				<?if($data['pno']){?>
				<a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=cutStr(stripslashes($prd['name']), 60)?></a>
				<a href="./?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:top;"></a>
				<?}else{?>
				연동된 상품이 없습니다.
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">이름</th>
			<td>
				<input type="text" name="name" value="<?=inputText($data[name])?>" class="input" size="20">
				<?if($data[member_no]){?>
				<a href="javascript:viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')">(<?=$data[member_id]?>)</a>
				<?}else{?>(비회원)
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">등록일</th>
			<td><?=date('Y/m/d', $data['reg_date'])?></td>
		</tr>
		<tr>
			<th scope="row">내용</th>
			<td><textarea name="content" class="txta" rows="10" cols="80"><?=stripslashes($data[content])?></textarea></td>
		</tr>
		<?if($data[ip]){?>
		<tr>
			<th scope="row">아이피</th>
			<td><?=$data[ip]?></td>
		</tr>
		<?}?>
	</table>
	<?if($from_ajax == "Y") {?>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="targetSelector.close();removeDimmed();"></span>
	</div>
	<?} else {?>
	<div class="fb_btn">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="wclose();"></span>
	</div>
	<?}?>
</form>
<?if($from_ajax) {?>
</div>
<?}?>

<script language="JavaScript">
	this.focus();
	var f =document.reviewFrm2;
	function checkPrdReview(){
		f.target=hid_frame;
		if (f.exec.value!='edit') return true;
		if (!checkBlank(f.name,"이름을 입력해주세요.")) return false;
		if (!checkBlank(f.content,"내용을 입력해주세요.")) return false;
	}
</script>