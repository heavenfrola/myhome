<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품이미지 설정
	' +----------------------------------------------------------------------------------------------+*/
	$preview1 = $cfg['noimg1'] ? "<span class=\"box_btn_s\"><a href=\"$cfg[noimg1]\" target=\"_blank\">미리보기</a></span>" : '';
	$preview2 = $cfg['noimg2'] ? "<span class=\"box_btn_s\"><a href=\"$cfg[noimg2]\" target=\"_blank\">미리보기</a></span>" : '';
	$preview3 = $cfg['noimg3'] ? "<span class=\"box_btn_s\"><a href=\"$cfg[noimg3]\" target=\"_blank\">미리보기</a></span>" : '';
    $preview_adult = '';
    if ($cfg['thumb_adult']) {
        $thumb = getListImgURL('_data/_default/prd', $cfg['thumb_adult']);
        $preview_adult = '<span class="box_btn_s"><a href="' . $thumb . '" target="_blank">미리보기</a></span>';
    }
	if(!$cfg['up_aimg_sort']) $cfg['up_aimg_sort'] = "N";
	if(!$cfg['use_opt_addimg']) $cfg['use_opt_addimg'] = 'N';

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="up_aimg">
	<div class="box_title first">
		<h2 class="title">상품 이미지 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 이미지 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">대체이미지</th>
			<td>
				<p class="explain">상품목록 등의 정보에서 업로드된 이미지가 존재하지 않을 경우 대신 출력하는 이미지를 설정합니다.</p>
				<ul>
					<li><span>대</span> <input type="file" name="upfile1" class="input input_full"> <?=$preview1?></li>
					<li style="padding:5px 0;"><span>중</span> <input type="file" name="upfile2" class="input input_full"> <?=$preview2?></li>
					<li><span>소</span> <input type="file" name="upfile3" class="input input_full"> <?=$preview3?></li>
				</ul>
			</td>
		</tr>
        <?php if ($scfg->comp('use_kcb', 'Y')) { ?>
        <tr>
            <th scope="row">성인인증 상품 대체 이미지</th>
            <td>
                <span>소</span> <input type="file" name="upfile_adult" class="input input_full">
                <?=$preview_adult?>
                <?php if ($preview_adult) { ?>
                <span class="box_btn_s"><input type="button" value="삭제" onclick="delThumbAdult()"></span>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
		<tr>
			<th scope="row">중이미지 사이즈<p class="explain">(상품상세)</p></th>
			<td>
				<span>가로</span>
				<input type="text" name="thumb2_w_mng" size="5" value="<?=$cfg['thumb2_w']?>" class="input"> px X
				<span>세로</span>
				<input type="text" name="thumb2_h_mng" size="5" value="<?=$cfg['thumb2_h']?>" class="input"> px
				<ul class="list_msg">
					<li>상품등록 시 중/소 자동생성에 반영되는 사이즈입니다.</li>
					<li>변경한 사이즈로 쇼핑몰에 적용하려면 <a href="?body=design@css" target="_blank">스타일 시트 편집</a>에서 수정이 필요합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">소이미지 사이즈<p class="explain">(상품목록)</p></th>
			<td>
				<span>가로</span>
				<input type="text" name="thumb3_w_mng" size="5" value="<?=$cfg['thumb3_w']?>" class="input"> px X
				<span>세로</span>
				<input type="text" name="thumb3_h_mng" size="5" value="<?=$cfg['thumb3_h']?>" class="input"> px
				<ul class="list_msg">
					<li>상품등록 시 중/소 자동생성에 반영되는 사이즈입니다.</li>
					<li>변경한 사이즈로 쇼핑몰에 적용하려면 <a href="?body=design@css" target="_blank">스타일 시트 편집</a>에서 수정이 필요합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">부가이미지<br>순서정렬 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="up_aimg_sort" value="Y" <?=checked($cfg['up_aimg_sort'], "Y")?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="up_aimg_sort" value="N" <?=checked($cfg['up_aimg_sort'], "N")?>> 미사용</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?
	$cfg['add_prd_img'] = ($cfg['add_prd_img'] > 3) ? $cfg['add_prd_img'] : 3;
	$total_add_prd_img = $_GET['mode'] ? $_GET['add_prd_img'] : $cfg['add_prd_img']-3;
?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="product@product_common.exe">
<input type="hidden" name="exec" value="add_prd_img">
	<div class="box_title">
		<h2 class="title">상품 이미지 추가 필드 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 이미지 추가 필드 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">이미지 추가 필드개수</th>
			<td>
				<ul class="list_msg">
					<li>대, 중, 소 이미지 외에 <u>추가로 이미지 필드가 필요할 경우</u> 설정 가능합니다</li>
					<li>만약 사용안함으로 설정한 경우에도 추가 필드가 설정되는 경우에는 계정 내부 점검이 필요하므로 1:1고객센터 문의 글로 접수 바랍니다.</li>
					<li>추가로 설정된 이미지 필드는 스킨내에서 사용되지 않으면 출력되지 않습니다.</li>
				</ul>

				<?=selectArray(array(1,2,3,4,5,6,7,8,9,10),"mng_add_prd_img",1,"사용안함",$total_add_prd_img,"location.href='./?body=$body&mode=1&add_prd_img='+this.value+'#add_prd_img';")?>
			</td>
		</tr>
		<?
			for($ii=1; $ii<=$total_add_prd_img; $ii++){
				$jj=$ii+3;
		?>
		<tr>
			<th scope="row">추가 <?=$ii?> 필드 정보</th>
			<td>
				필드명 : <input type="text" name="add_prd_img_name<?=$ii?>" class="input" maxlength="20" value="<?=$cfg["prd_img".$jj]?>"> &nbsp;
				가로 : <input type="text" name="add_prd_img_w<?=$ii?>" class="input" size="4" maxlength="4" value="<?=$cfg["thumb".$jj."_w"]?>"> px X
				세로 : <input type="text" name="add_prd_img_h<?=$ii?>" class="input" size="4" maxlength="4" value="<?=$cfg["thumb".$jj."_h"]?>"> px
			</td>
		</tr>
		<?
			}
		?>
		<tr>
			<th scope="row">옵션별 부가이미지 사용</th>
			<td>
				<label><input type="radio" name="use_opt_addimg" value="Y" <?=checked($cfg['use_opt_addimg'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_opt_addimg" value="N" <?=checked($cfg['use_opt_addimg'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script>
function delThumbAdult() {
    if (confirm('이미지를 삭제하시겠습니까?')) {
        $.get('?body=product@product_image.exe&exec=delThumbAdult', () => {
            location.reload();
        });
    }
}
</script>