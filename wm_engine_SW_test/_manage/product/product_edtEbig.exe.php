<?PHP

	printAjaxHeader();

	$pno = numberOnly($_GET['pno']);
	$exec = addslashes($_GET['exec']);

	$prd = $pdo->assoc("select name, ebig from $tbl[product] where no='$pno'");
	$ebig = explode('@', preg_replace('/^@|@#/', '', $prd['ebig']));

	if($exec == 'update') {
		$nebig = numberOnly($_GET['nebig']);
		$nebig_all = implode(',', $nebig);

		// 해제된 기획전 링크 삭제
		$w = ($nebig_all) ? "and nbig not in ($nebig_all)" : '';
		$pdo->query("delete from $tbl[product_link] where ctype=2 and pno='$pno' $w");

		// 새로운 기획전 추가
		foreach($nebig as $cno) {
			if($pdo->row("select count(*) from $tbl[product_link] where ctype=2 and pno='$pno' and nbig='$cno'") > 0) continue;

			if($_GET['sortupdate'] == 'Y') {
				$sort = 1;
				$pdo->query("update $tbl[product_link] set sort_big=sort_big+1 where nbig='$cno'");
			} else {
				$sort = $pdo->row("select max(sort_big) from $tbl[product_link] where nbig='$cno'")+1;
			}
			$pdo->query("
				insert into $tbl[product_link] (pno, ctype, nbig, nmid, nsmall, sort_big, sort_mid, sort_small)
				values ('$pno', '2', '$cno', '0', '0', '$sort', '0', '0')
			");
		}

		// 기획전 캐시 저장
		$nebig_all = '@'.implode('@', $nebig).'@';
		$pdo->query("update $tbl[product] set ebig='$nebig_all' where no='$pno'");
		javac('parent.esearch.close()');
	}

	$res = $pdo->iterator("select no, name from $tbl[category] where ctype=2 order by sort asc");
	$name = stripslashes(strip_tags($prd['name']));

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">기획전 선택</div>
	</div>
	<div id="popupContentArea">
		<form method="get" action="?" target="<?=$_GET['hid_frame']?>">
			<input type="hidden" name="exec" value="update">
			<input type="hidden" name="body" value="product@product_edtEbig.exe">
			<input type="hidden" name="pno" value="<?=$pno?>">
			<p class="explain p_color2">
				<label><input type="checkbox" name="sortupdate" value="Y" checked> 이 상품이 새로운 기획전에 포함될 경우 기획전의 최상위에 배열합니다.</label>
			</p>
			<div id="popupContentArea">
				<ul class="box_scroll" style="max-height: 400px;">
					<?php foreach ($res as $data) {?>
					<li><label class="p_cursor"><input type="checkbox" name="nebig[]" value="<?=$data['no']?>" <?=checked(true, in_array($data['no'], $ebig))?>> <?=stripslashes($data['name'])?></label></li>
					<?}?>
				</ul>
			</div>
			<div class="pop_bottom top_line">
				<span class="box_btn_s blue"><input type="submit" value="기획전적용"></span>
				<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="esearch.close()"></span>
			</div>
		</form>
	</div>
</div>