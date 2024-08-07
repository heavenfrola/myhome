<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  크리마 리뷰
	' +----------------------------------------------------------------------------------------------+*/

	$wec_acc = new weagleEyeClient($_we, 'mall');
	$result = $wec_acc->call('getAutoCrema');
	$auto_crema_use = $result[0]->auto_use[0];

	if(!$cfg['crema_image_no']) $cfg['crema_image_no'] = 2;
    $scfg->def('crema_non_member', 'N');
    $scfg->def('crema_responsive_skin', 'N');

?>
<form name="cremaFrm" method="post" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="crema">
	<div class="box_title first">
		<h2 class="title">크리마 리뷰 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">크리마 리뷰 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<div class="box_middle">
			<ul class="list_msg left">
				<li>App_ID와 Secret 값은 <a href='http://cre.ma' target="_blank">크리마 홈페이지</a> 서비스 신청 후에 발급 가능합니다.</li>
			</ul>
		</div>
		<tr>
			<th scope="row">App ID</th>
			<td>
				<input type="text" name="crema_app_id" value="<?=$cfg["crema_app_id"]?>" class="input" style="ime-mode:disabled;" size="50"> <span class="explain">* Crema API Key는 Crema API를 사용하기 위해 필요한 App ID와 Secret으로 구성되어 있습니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">Secret</th>
			<td>
				<input type="text" name="crema_secret" value="<?=$cfg["crema_secret"]?>" class="input" style="ime-mode:disabled;" size="50"> <span class="explain">* Crema API Key는 Crema API를 사용하기 위해 필요한 App ID와 Secret으로 구성되어 있습니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">자동API 사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" value="Y" name="crema_auto_use" <?=checked($auto_crema_use,"Y")?>>사용함 </label>
				<label class="p_cursor"><input type="radio" value="N" name="crema_auto_use" <?=checked($auto_crema_use,"N").checked($auto_crema_use,"")?>>사용안함 </label>
				<p class="explain">* 상품(최근 수정된 상품 OR 추가된 상품에 한하여) / 주문(배송중,배송완료의 주문) 하루에 한번 자동으로 API 전송이 됩니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td>
				<input type="radio" name="crema_image_no" id="img1" value="2" <?=checked($cfg['crema_image_no'],"2")?>> <label for="img1" class="p_cursor">중간 이미지</label>
				<input type="radio" name="crema_image_no" id="img2" value="3" <?=checked($cfg['crema_image_no'],"3")?>> <label for="img2" class="p_cursor">작은 이미지</label>
				<input type="radio" name="crema_image_no" id="img3" value="1" <?=checked($cfg['crema_image_no'],"1")?>> <label for="img3" class="p_cursor">큰 이미지</label>
				<ul class="list_msg">
					<li>사용하지 않거나 업로드되지 않은 이미지를 선택하시면 상품의 이미지 정보가 크리마에 정상적으로 제공되지 않습니다.</li>
					<li>이미 전송이 완료된 상품에는 변경사항이 반영되지 않을 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">비회원 주문 연동</th>
			<td>
				<label class="p_cursor"><input type="radio" value="Y" name="crema_non_member" <?=checked($cfg['crema_non_member'], 'Y')?>>사용함 </label>
				<label class="p_cursor"><input type="radio" value="N" name="crema_non_member" <?=checked($cfg['crema_non_member'], 'N')?>>사용안함 </label>
				<p class="explain">* 상품(최근 수정된 상품 OR 추가된 상품에 한하여) / 주문(배송중,배송완료의 주문) 하루에 한번 자동으로 API 전송이 됩니다.</span>
			</td>
		</tr>
        <tr>
            <th scope="row">반응형 스킨 사용여부</th>
            <td>
                <label class="p_cursor"><input type="radio" value="Y" name="crema_responsive_skin" <?=checked($scfg->get('crema_responsive_skin'), 'Y')?>>사용함 </label>
                <label class="p_cursor"><input type="radio" value="N" name="crema_responsive_skin" <?=checked($scfg->get('crema_responsive_skin'), 'N')?>>사용안함 </label>
            </td>
        </tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="저장"></span>
	</div>
</form>
<?php if($cfg["crema_app_id"] && $cfg["crema_secret"] && !isTable('crema_matching')) { ?>
<form name="cremadataFrm" method="post" target="hidden<?=$now?>" action="/main/exec.php?exec_file=api/crema.exe.php">
	<div class="box_title">
		<h2 class="title">크리마 리뷰 설정</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>＊데이터 전송 시 크리마에 초기 데이터가 전송됩니다.</li>
			<li>＊카테고리(숨김제외) / 상품(전체) / 리뷰(전체) / 주문(최근 1개월)</li>
			<li style="color:red;">＊최초 1회 만 데이터 전송 버튼을 클릭 후 10~20분 후 크리마와 데이터 확인 해주세요.</li>
		</ul>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="데이터 전송"></span>
	</div>
</form>
<?php } ?>