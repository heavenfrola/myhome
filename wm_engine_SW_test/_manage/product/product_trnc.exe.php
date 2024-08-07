<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품등록폼 윙디스크 즉시 정리 팝업
	' +----------------------------------------------------------------------------------------------+*/

	if($mode == 'trnc') {
		$pno = numberOnly($pno);
		$res = $pdo->query("select no from $tbl[product] where stat=1 and no != '$pno'");
		trncPrd(0, $pno);

		javac("
			parent.document.getElementById('up_fdisk').contentWindow.showDiskspace();
			parent.document.getElementById('up_wdisk').contentWindow.showDiskspace();
			parent.trnc.close();
		");
		exit;
	}

	printAjaxHeader();

	$size1 = $pdo->row("select sum(filesize) from $tbl[product_image] a inner join $tbl[product] b on b.no = a.pno where b.stat=1 and b.no != '$pno' and a.`filetype`='9'");
	$size_str1 = filesizeStr($size1, 2);
	$size2 = $pdo->row("select sum(filesize) from $tbl[product_image] a inner join $tbl[product] b on b.no = a.pno where b.stat=1 and b.no != '$pno' and a.`filetype` in (2,3)");
	$size_str2 = filesizeStr($size2, 2);
	$size = $size1 + $size2;

?>
<div id="popupContent" class="layerPop pop_width">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">윙Disk 정리</div>
	</div>
	<div id="popupContentArea">
		<ul class="list_msg">
			<li>
				상품 등록 도중 저장하지 않으시고 등록을 취소하시거나 오류등으로 인해 상품등록이 잘못된 경우, 업로드한 이미지는 24시간 이후에 자동으로 삭제됩니다.<br>
				단, 디스크 용량 부족 등으로 인해 자동 공간확보 이전에 바로 정리를 원하실 경우 '윙Disk 정리'에서 즉시 처리가 가능합니다.
			</li>
			<li>현재 등록중이신 상품 외에 <span class="p_color2">다른 창이나 다른 PC에서 아직 저장하지 않은 상품이 있으시다면</span> 반드시 저장 이후 기능을 사용해 주시기 바랍니다.</li>
		</ul>
		<form method="post" action="?" target="<?=$hid_frame?>">
			<input type="hidden" name="body" value="product@product_trnc.exe">
			<input type="hidden" name="pno" value="<?=$pno?>">
			<input type="hidden" name="mode" value="trnc">
			<table class="tbl_row">
				<caption class="hidden">윙Disk 정리</caption>
				<colgroup>
					<col style="width:12%">
					<col>
				</colgroup>
				<tr>
					<th scope="row">윙 Disk</th>
					<td scope="row"><strong class="p_color2"><?=$size_str1?></strong> 의 정리 할 파일이 있습니다.</td>
				</tr>
				<tr>
					<th>무료 Disk</th>
					<td><strong class="p_color2"><?=$size_str2?></strong> 의 정리 할 파일이 있습니다.</td>
				</tr>
			</table>
			<div class="pop_bottom">
				<?if($size > 0){?>
				<span class="box_btn_s blue"><input type="submit" value="윙Disk정리"></span>
				<?}?>
				<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="trnc.close()"></span>
			</div>
		</form>
	</div>
</div>