<?PHP

	if($gno) {
		$data=get_info($tbl['product_gift'],"no",$gno);
		checkBlank($data[no],"원본 자료를 입력해주세요.");

		if($data[upfile]) {
			$upfile="../".$data[updir]."/".$data[upfile];
			if(is_file($upfile)) $upfile_link=delImgStr($data,"");
		}
	}

	if(!$listURL) {
		$listURL="./?body=product@product_gift_list";
	}

?>
<form name="mngIconFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="margin:0px" enctype="multipart/form-data" onSubmit="return checkPrdGiftReg(this)">
	<input type="hidden" name="body" value="product@product_gift_register.exe">
	<input type="hidden" name="gno" value="<?=$gno?>">

	<table width="100%" border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td colspan="4" class="btitle"><img src="<?=$engine_url?>/_manage/image/b3.gif" border="0" align="top"> 사은품 기본 정보</b></td>
		</tr>
		<tr>
			<td>
			<table align="center" border=0 cellspacing=3 cellpadding=0 class="btbl">
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 사은품 이름</td>
					<td class="bcol2"><input type="text" name="name" value="<?=inputText(stripslashes($data[name]))?>" class="input"></td>
				</tr>
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 증정 조건</td>
					<td class="bcol2"><input type="text" name="price_limit" value="<?=$data[price_limit]?>" class="input"> 원 이상 구매시 (실 결제액)</td>
				</tr>
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 구매포인트</td>
					<td class="bcol2"><input type="text" name="point_limit" value="<?=$data[point_limit]?>" class="input"> 포인트 차감</td>
				</tr>
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 사용여부</td>
					<td class="bcol2">
					<input type="radio" name="use" value="Y" <?=checked($data['use'],"Y")?>> 예
					<input type="radio" name="use" value="N" <?=checked($data['use'],"N")?>> 아니오
					</td>
				</tr>
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 이미지</td>
					<td class="bcol2"><input type="file" name="upfile" class="input"> <?=$upfile_link?></td>
				</tr>
				<tr>
					<td class="bcol"><img src="<?=$engine_url?>/_manage/image/b5.gif" border="0" align="top"> 설명</td>
					<td class="bcol2">
					<textarea name="content" class="txta"><?=stripslashes($data[content])?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
					<?=btn2("확인")?>
					<a href="<?=$listURL?>"><?=btn2("취소")?></a>
					</td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
</form>