<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 등록
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
	include_once $engine_dir."/_manage/product/product_register.inc.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	seVerchk();

	$file_url = getFileDir($data['updir']);

	if(!isTable($tbl['product_content_log'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['product_content_log']);
	}

	// 구매자 설명기입란 여부 초기값
	if (!$data['purchaser_explanation_yn']) $data['purchaser_explanation_yn'] = 'N';

	$add_urlfix = '';
	if($data['stat']==4) $add_urlfix = '&urlfix=Y';

	$rURL = getListURL('prdList');
	if(empty($rURL)) $rURL = './?body=product@product_list';

    $ptn_content_use = 'N';
    if ($admin['partner_no'] > 0) {
        $_def = getWMDefault(array('ptn_content_use_'.$admin['partner_no']));
        $ptn_content_use = $_def['ptn_content_use_'.$admin['partner_no']];
    }

    // 업로드 이미지 용량 제한 표시
    $img_prdBasic_limit = getWingUploadSize('prdBasic', true);
    $img_prdBasic_limit_byte = getWingUploadSize('prdBasic');
    $img_prdContent2_limit = getWingUploadSize('prdContent', true, 2);

?>
<style>
	#hs_link{position:absolute;right:50px;top:1px;}
	#hs_link a{color:red;}
</style>

<?php if ($admin['level'] == 4) { ?>
<!-- 입점사 -->
<style type="text/css">
.setup.btt {
    display: none;
}
<?php if ($ptn_content_use == 'Y') { ?>
.setup.btt.common_content {
    display: block;
}
<?php } ?>
</style>
<?php } ?>

<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<form name="prdFrm" id="prdFrm" method="post" enctype="multipart/form-data" action="./?body=product@product_register.exe" target="hidden<?=$now?>" onSubmit="return checkPrdReg(this)" style="width:1090px; margin:0 auto; position:relative;">
	<input type="hidden" name="pno" value="<?=$pno?>">
	<input type="hidden" name="ck_opt_ea" value="<?=$ck_opt_ea?>">
	<input type="hidden" name="ebig_old" value="<?=$data['ebig']?>">
	<input type="hidden" name="mbig_old" value="<?=$data['mbig']?>">

	<?php if (!$rURL) { ?><input type="hidden" name="new_prd" value="1"><?php } ?>
	<?php
		preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
		settype($agent[1], 'integer');
		if($agent[1] > 6 || $agent[1] == 0) {
	?>
	<div id="fastBtn">
		<span id="stpBtn" class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="PC 미리보기" onclick="detailPreview(2,<?=$pno?>)"></span>
		<?php if ($cfg['use_m_content_product'] == 'Y') { ?>
		<span class="box_btn gray"><input type="button" value="모바일 미리보기" onclick="detailPreview(2,<?=$pno?>, 'm')"></span>
		<?php } ?>
		<?php if ($rURL) { ?>
		<span class="box_btn gray"><a href="<?=$rURL?>">취소</a></span>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if ($cfg['partner_prd_accept'] == 'Y' && ($admin['level'] == 4 || $data['partner_stat'] == 1)) { ?>
	<div class="box_title_reg">
		<h2 class="title">입점사 상품 등록/수정 신청</h2>
	</div>
	<table class="tbl_row_reg">
		<caption class="hidden">입점사 상품 등록/수정 사유</caption>
		<colgroup>
			<col style="width:134px">
			<col>
		</colgroup>
		<tr>
			<th>변경 내용 및 사유</th>
			<td><textarea name="partner_cmt" class="txta"><?=stripslashes($partner_cmt)?></textarea></td>
		</tr>
		<?php if ($admin['level'] < 4) { ?>
		<tr>
			<th>관리자 코멘트</th>
			<td><textarea name="manager_cmt" class="txta"><?=stripslashes($manager_cmt)?></textarea></td>
		</tr>
		<?php } else if($manager_cmt) { ?>
		<tr>
			<th>관리자 코멘트</th>
			<td><?=nl2br(stripslashes($manager_cmt))?></td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">상태</th>
			<td class="stat">
				<label class="p_cursor p_color4"><input type="radio" name="req_stat" value="1" <?=checked($req_stat, 1)?>> 등록대기</label>
				<?php if ($admin['level'] < 4) { ?>
				<label class="p_cursor p_color"><input type="radio" name="req_stat" value="2" <?=checked($req_stat, 2)?>> 승인</label>
				<label class="p_cursor p_color2"><input type="radio" name="req_stat" value="3" <?=checked($req_stat, 3)?>> 반려</label>
				<?php } ?>
				<input type="hidden" name="ori_no" value="<?=$data['ori_no']?>" />
			</td>
		</tr>
	</table>
	<br>
	<?php } ?>

	<div class="left_reg">
		<!-- 기본정보 -->
		<div class="box_title_reg first">
			<h2 class="title">기본정보</h2>
			<?php if ($_GET['pno'] > 0) { ?>
			<div class="btns">
				시스템코드 <a href="/shop/detail.php?pno=<?=$data['hash']?><?=$add_urlfix?>" target="_blank"><?=$data['hash']?></a> (<?=$_GET['pno']?>)
				<span class="box_btn_s icon copy2"><input type="button" value="상품복사" onclick="productcopy()"></span>
			</div>
			<?php } ?>
		</div>
		<table class="tbl_row_reg">
			<caption class="hidden">기본 정보</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<tbody>
				<?php if ($admin['level'] < 4 || $cfg['partner_prd_accept'] == 'N') { ?>
				<tr>
					<th scope="row">상태</th>
					<td class="stat">
						<?php if ($cfg['use_partner_shop'] == 'Y' && 0) { ?>
						<label class="p_cursor p_color4"><input type="radio" name="stat" value="1" <?=checked($data['stat'],"1")?>> 등록대기</label>
						<?php } ?>
						<label class="p_cursor p_color"><input type="radio" name="stat" value="2" <?=checked($data['stat'],"2")?>> <?=$_prd_stat[2]?></label>
						<label class="p_cursor p_color3"><input type="radio" name="stat" value="3" <?=checked($data['stat'],"3")?>> <?=$_prd_stat[3]?></label>
						<label class="p_cursor p_color5"><input type="radio" name="stat" value="4" <?=checked($data['stat'],"4")?>> <?=$_prd_stat[4]?></label>
						<?php if ($data['stat'] == 5) { ?>
						<label class="p_cursor p_color2"><input type="radio" name="stat" value="5" <?=checked($data['stat'],"5")?>> <?=$_prd_stat[5]?></label>
						<?php } ?>
						<?php if ($pslrow > 0) { ?><span class="box_btn_s" style="float:right;"><a class="showlog">변경 로그 보기</a></span><?php } ?>
					</td>
				</tr>
                <?php } ?>
				<?php if ($prd_type != '1') { ?>
				<tr>
					<th scope="row">세트상품종류</th>
					<td>
						<label><input type="radio" name="prd_type" value="4" <?=checked($prd_type, 4)?>> 일반세트상품</label>
						<label>
                            <input type="radio" name="prd_type" value="6" <?=checked($prd_type, 6)?>>
                            <input type="text" name="set_pick_qty" class="input" size="3" value="<?=$set_pick_ea?>"> 개 골라담기
                        </label>
                        <!--
						<label><input type="radio" name="prd_type" value="5" <?=checked($prd_type, 5)?>> 담을수록 할인</label>
                        -->
					</td>
				</tr>
                <?php } ?>
				<?php if ($admin['level'] < 4 || $cfg['partner_prd_accept'] == 'N') { ?>
				<tr>
					<th scope="row">노출위치</th>
					<td>
						<label class="p_cursor"><input type="checkbox" class='perm_all'>전체</label>
						<label class="p_cursor"><input type="checkbox" name='perm_lst' value='Y' class='perm_sub' <?=checked($data['perm_lst'], 'Y')?>>상품 목록</label>
						<label class="p_cursor"><input type="checkbox" name='perm_dtl' value='Y' class='perm_sub' <?=checked($data['perm_dtl'], 'Y')?>>상품 상세</label>
						<label class="p_cursor"><input type="checkbox" name='perm_sch' value='Y' class='perm_sub' <?=checked($data['perm_sch'], 'Y')?>>검색 결과</label>
						<script type="text/javascript">
						chainCheckbox($('.perm_all'), $('.perm_sub'));
						</script>
					</td>
				</tr>
                <?php } ?>
				<?php if ($pslrow > 0) { ?>
				<tr class="stat_log">
					<th scope="row">상태변경 로그</th>
					<td>
						<?php
							$stat_no = 1;
							$psl_sql = $pdo->iterator("select * from `$tbl[product_stat_log]` where `pno`='$data[no]' order by `no`");
                            foreach ($psl_sql as $psl_arr) {
								echo date("Y-m-d H:i", $psl_arr['reg_date'])." : ";
								if($psl_arr['ono']){
									if($psl_arr['stat'] == 3) echo "<a href=\"javascript:;\" onclick=\"viewOrder('".$psl_arr['ono']."')\"><u>".$psl_arr['ono']."</u></a> 주문완료후 ";
									elseif($psl_arr['stat'] == 2) echo $psl_arr['admin_id']." 님에 의해 <u>".$psl_arr['ono']."</u> 주문취소/삭제후 ";
								} else {
									echo $psl_arr['admin_id']." 님에 의해 ";
								}
								echo $_prd_stat[$psl_arr['ori_stat']]." => <u style='color:#44affb'>".$_prd_stat[$psl_arr['stat']]."</u> (으)로 변경<br>";
							}
						?>
					</td>
				</tr>
				<?php } ?>
				<?php if ($_use['set_pre_use'] == 'Y') { ?>
				<tr>
					<th scope="row">상품종류</th>
					<td>
						<label><input type="radio" name="prd_type" value="1" <?=checked($data['prd_type'], 1)?>> 일반상품</label>
						<label><input type="radio" name="prd_type" value="3" <?=checked($data['prd_type'], 3)?>> 세트상품</label>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row">상품코드</th>
					<td>
						<input type="text" name="code" value="<?=inputText($data['code'])?>" class="input" style="width:352px;">
						<label class="p_cursor"><input type="checkbox" name="auto_code" value="Y" id="auto_code"> 자동생성</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><strong>상품명</strong></th>
					<td>
						<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_prdname" style="width:352px;">
						<?php if ($_GET['pno'] > 0) { ?>
						<span class="box_btn_s"><a href="/shop/detail.php?pno=<?=$data['hash']?><?=$add_urlfix?>" target="_blank">상품바로가기</a></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<th scope="row">참고 상품명</th>
					<td>
						<input type="text" name="name_referer" value="<?=inputText($data['name_referer'])?>" class="input input_prdname" style="width:352px;">
					</td>
				</tr>
				<tr>
					<th scope="row"><strong>분류</strong></th>
					<td>
						<select name="big" class="cate_multis" onchange="chgCateInfinite(this, 2, '');">
							<option value="">::대분류::</option>
							<?=$item_1_1?>
						</select>
						<select name="mid" class="cate_multis" onchange="chgCateInfinite(this, 3, '');">
							<option value="">::중분류::</option>
							<?=$item_1_2?>
						</select>
						<select name="small" class="cate_multis" onchange="chgCateInfinite(this, 4, '');">
							<option value="">::소분류::</option>
							<?=$item_1_3?>
						</select>
						<?php if ($cfg['max_cate_depth'] >= 4) { ?>
						<select name="depth4" class="cate_multis" >
							<option value="">::세분류::</option>
							<?=$item_1_4?>
						</select>
						<?php } ?>
					</td>
				</tr>
				<?php if ($cfg['xbig_mng'] == "Y") { ?>
				<tr>
					<th scope="row">
						<?=$cfg['xbig_name']?>분류
						<a href="?body=product@product_cate" target="_blank" class="setup btt" tooltip="설정"></a>
					</th>
					<td>
						<select name="xbig" class="cate_multis" onchange="chgCateInfinite(this, 2, 'x');">
							<option value="">::대분류::</option>
							<?=$item_4_1?>
						</select>
						<select name="xmid" class="cate_multis" onchange="chgCateInfinite(this, 3, 'x');">
							<option value="">::중분류::</option>
							<?=$item_4_2?>
						</select>
						<select name="xsmall" class="cate_multis" onchange="chgCateInfinite(this, 4, 'x');">
							<option value="">::소분류::</option>
							<?=$item_4_3?>
						</select>
						<?php if ($cfg['max_cate_depth'] >= 4) { ?>
						<select name="xdepth4" class="cate_multis" >
							<option value="">::세분류::</option>
							<?=$item_4_4?>
						</select>
						<?php } ?>
					</td>
				</tr>
				<?php } else if ($admin['partner_no']==0) { ?>
				<tr>
					<th scope="row">
						<?=$cfg['xbig_name']?>분류
						<a href="?body=product@product_cate" target="_blank" class="setup btt" tooltip="설정"></a>
					</th>
					<td>
						<ul class="list_msg">
							<li><?=$cfg['xbig_name']?>분류를 사용하시려면, 좌측 설정버튼을 활용하세요.</li>
						</ul>
					</td>
				</tr>
				<?php } ?>
				<?php if ($cfg['ybig_mng'] == "Y") { ?>
				<tr>
					<th scope="row">
						<?=$cfg['ybig_name']?>분류
						<a href="?body=product@product_cate" target="_blank" class="setup btt" tooltip="설정"></a>
					</th>
					<td>
						<select name="ybig" class="cate_multis" onchange="chgCateInfinite(this, 2, 'y');">
							<option value="">::대분류::</option>
							<?=$item_5_1?>
						</select>
						<select name="ymid" class="cate_multis" onchange="chgCateInfinite(this, 3, 'y');">
							<option value="">::중분류::</option>
							<?=$item_5_2?>
						</select>
						<select name="ysmall" class="cate_multis" onchange="chgCateInfinite(this, 4, 'y');">
							<option value="">::소분류::</option>
							<?=$item_5_3?>
						</select>
						<?php if ($cfg['max_cate_depth'] >= 4) { ?>
						<select name="ydepth4" class="cate_multis" >
							<option value="">::세분류::</option>
							<?=$item_5_4?>
						</select>
						<?php } ?>
					</td>
				</tr>
				<?php } else if ($admin['partner_no'] == 0) { ?>
				<tr>
					<th scope="row">
						<?=$cfg['ybig_name']?>분류
						<a href="?body=product@product_cate" target="_blank" class="setup btt" tooltip="설정"></a>
					</th>
					<td>
						<ul class="list_msg">
							<li><?=$cfg['ybig_name']?>분류를 사용하시려면, 좌측 설정버튼을 활용하세요.</li>
						</ul>
					</td>
				</tr>
				<?php } ?>

				<?php if ($data['stat'] > 1) { ?>
				<div id="shortcutArea">
					<?php include_once $engine_dir."/_manage/product/product_shortcut_frm.exe.php"; ?>
				</div>
				<?php } ?>

				<tr>
					<th scope="row"><?=$_ctitle[2]?><a href="?body=product@catework&ctype=2" target="_blank" class="setup btt" tooltip="설정"></a></th>
					<td>
						<?=$ebig_str?>
						<label class="p_color p_cursor"><input type="checkbox" name="ebig_first" value="Y" checked> 이상품이 기획전에 처음 등록될 경우 맨 앞으로 정렬</label>
					</td>
				</tr>
				<?php if($cfg['mobile_use'] == "Y") { ?>
				<tr>
					<th scope="row"><?=$cfg['mobile_name']?> <?=$_ctitle[2]?><a href="?body=wmb@category_config2" target="_blank" class="setup btt" tooltip="설정"></a></th>
					<td>
						<?=$mbig_str?>
						<label class="p_color p_cursor"><input type="checkbox" name="mbig_first" value="Y" checked> 이상품이 모바일 기획전에 처음 등록될 경우 맨 앞으로 정렬</label>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row">판매설정</th>
					<td class="sales_policy">
                        <?php if ($prd_type == '1') { // 일반 상품만 선택 가능한 상품 속성 ?>
						<label class="p_cursor"><input type="checkbox" name="event_sale" value="Y" <?=checked($data['event_sale'], "Y")?>> 이벤트</label> <a href="./?body=promotion@event_list" class="setup btt" tooltip="설정" target="_blank"></a>
						<label class="p_cursor"><input type="checkbox" name="member_sale" value="Y" <?=checked($data['member_sale'], "Y")?>> 회원혜택</label> <a href="./?body=member@member_group" class="setup btt" tooltip="설정" target="_blank"></a>
						<?php if($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') { ?>
						<label class="p_cursor"><input type="checkbox" name="oversea_free_delivery" value="Y" <?=checked($data['oversea_free_delivery'], "Y")?>> 해외 무료배송(무게 차감)</label>
						<?php } ?>
						<br>
						<label class="p_cursor"><input type="checkbox" name="dlv_alone" value="Y" <?=checked($data['dlv_alone'], "Y")?>> 단독배송</label>
                        <?php } ?>

                        <?php if ($scfg->comp('checkout_id') == true) { ?>
						<label class="p_cursor"><input type="checkbox" name="checkout" value="Y" <?=checked($data['checkout'], "Y")?>> 네이버페이 주문형</label>
                        <a href="#" class="tooltip_trigger" data-child="tooltip_npay"></a>
                        <div class="info_tooltip tooltip_npay">
                            상품상세페이지와 장바구니에서 결제가능한 네이버페이입니다.
                        </div>
                        <?php if ($prd_type == '4' || $prd_type == '5' || $prd_type == '6') { ?>
                        <ul class="list_info">
                            <li>네이버페이 이용 시 고객이 관리자 동의 없이 세트 상품의 부분 취소를 할 수 있습니다.</li>
                        </ul>
                        <?php } ?>
                        <?php } ?>

                        <?php if ($prd_type == '1') { ?>
                        <?php if ($scfg->comp('use_talkpay', 'Y') == true) { ?>
						<label class="p_cursor"><input type="checkbox" name="use_talkpay" value="Y" <?=checked($data['use_talkpay'], "Y")?>> 톡체크아웃</label></a>
                        <a href="#" class="tooltip_trigger" data-child="tooltip_talkpay"></a>
                        <div class="info_tooltip tooltip_talkpay">톡체크아웃 이용 시 재고관리를 필수적으로 설정하여야 하며, 개별 배송 정책을 설정할 수 없습니다.</div>
                        <?php } ?>
						<?php
                            if(
                                $scfg->comp('cash_receipt_use', 'Y') == true
                                || $cfg['card_pg'] == 'dacom'
                                || ($cfg['card_pg'] == 'kcp' && $cfg['kcp_use_taxfree'] == 'Y')
                                || ($cfg['card_pg'] == 'nicepay' && $cfg['nice_use_taxfree'] == 'Y')
                                || $cfg['card_pg'] == 'inicis'
                                || $scfg->comp('use_nsp', 'Y') == true
                            ) {
                        ?>
						<label class="p_cursor"><input type="checkbox" name="tax_free" value="Y" <?=checked($data['tax_free'], "Y")?>> 면세 상품</label>
                        <a href="#" class="tooltip_trigger" data-child="tooltip_taxfree"></a>
                        <div class="info_tooltip tooltip_taxfree" >PG사 지원 또는 면세계약에 따라 면세처리가 되지 않을 수 있습니다.</div>
						<?php } ?>
						<?php if ($cfg['import_flag_use'] == "Y") { ?>
						<label class="p_cursor"><input type="checkbox" name="import_flag" value="Y" <?=checked($data['import_flag'], "Y")?>> 해외구매대행</label>
						<?php } ?>
						<?php if ($cfg['compare_today_start_use']=="Y") { ?>
						<label class="p_cursor"><input type="checkbox" name="compare_today_start" value="Y" <?=checked($data['compare_today_start'], "Y")?>> 오늘출발</label>
						<?php } ?>
						<?php if ($cfg['use_no_mile/cpn'] == 'Y') { ?>
						<br>
						<label class="p_cursor first"><input type="checkbox" name="no_milage" value="Y" <?=checked($data['no_milage'], 'Y')?>> 적립금사용불가</label>
						<label class="p_cursor"><input type="checkbox" name="no_cpn" value="Y" <?=checked($data['no_cpn'], 'Y')?>> 쿠폰사용불가</label>
						<?php } ?>

						<?php if ($scfg->comp('use_kcb', 'Y')) { ?>
						<label class="p_cursor"><input type="checkbox" name="adult" value="Y" <?=checked($data['adult'], 'Y')?>> 성인인증 필요</label>
						<?php } ?>

                        <?php } // 일반 상품만 선택 가능한 상품 속성?>
                        <?php if ($scfg->comp('compare_explain', 'Y') == true) { ?>
                        <label class="p_cursor"><input type="checkbox" name="no_ep" value="N" <?=checked($data['no_ep'], 'N')?>> 쇼핑 검색엔진 포함</label>
                        <a href="#" class="tooltip_trigger" data-child="tooltip_no_ep"></a>
                        <div class="info_tooltip tooltip_no_ep" >
                            <div class="p_color">다음의 쇼핑 검색엔진에 포함됩니다.</div>
                            <ul class="list_msg">
                                <li>네이버쇼핑</li>
                                <li>카카오 쇼핑하우</li>
                                <li>지그재그</li>
                                <li>크리테오</li>
                                <li>페이스북 픽셀</li>
                                <li>구글쇼핑</li>
                            </ul>
                        </div>
                        <?php } ?>
					</td>
				</tr>

                <?php if($prd_type == 1 && $cfg['use_sbscr']=='Y') {
                    if(!$data['setno']) $data['setno'] = $pdo->row("select no from $tbl[sbscr_set] where `default`='Y'");
                ?>
                <tr >
                    <th scope="row">
                        정기배송
                        <a href="?body=config@subscription" target="_blank" class="setup btt" tooltip="설정"></a>
                    </th>
                    <td>
						<label><input type="radio" name="sub_use" value="Y" <?=checked($data['sub_use'], "Y")?>> 사용함</label>
						<label><input type="radio" name="sub_use" value="N" <?=checked($data['sub_use'], "N")?>> 사용안함</label>

                        <?php if($cfg['sbscr_type']=='P') { ?>
                        <div class="tbl_subscription hidden" style="margin-top: 5px;">
                        <?=selectArray($_sub_set, 'setno', null, '::세트선택::', $data['setno'], "chgFieldSet(this, $pno);")?>
                        </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($admin['level'] != 4 && $prd_type == '1') { ?>
				<tr>
					<th scope="row">
						한정시간판매<br>/타임세일
						<a href="./?body=promotion@timesale" target="_blank" class="setup btt" tooltip="설정"></a>
					</th>
					<td>
						<?php if ($cfg['ts_use'] == 'Y') { ?>
						<label><input type="radio" name="ts_use" value="Y" <?=checked($data['ts_use'], "Y")?> <?php if ($ts_field_exists == false) { ?>onclick="createTsField(this)"<?php } ?>> 사용함</label>
						<label><input type="radio" name="ts_use" value="N" <?=checked($data['ts_use'], "N")?>> 사용안함</label>
						<table class="tbl_timesale LimitSale hidden">
							<caption class="hidden">한정시간판매/타임세일</caption>
							<colgroup>
								<col style="width:150px">
							</colgroup>
							<tbody>
								<tr>
									<th scope="row">타임세일 설정</th>
									<td>
										<label><input type="radio" name="use_ts_set" value="N" <?=checked($use_ts_set, 'N')?>> 개별설정</label>
										<label><input type="radio" name="use_ts_set" value="Y" <?=checked($use_ts_set, 'Y')?>> 세트설정</label>
										<a href="?body=promotion@timesale_list" target="_blank" class="setup btt" tooltip="타임세일세트 설정"></a>
									</td>
								</tr>
								<tr class="timesale_N">
									<th scope="row">한정/타임세일 시간</th>
									<td>
										<input type="input" name="ts_dates" class="input datepicker" size="10" value="<?=$ts_dates?>">
										<select name="ts_times">
											<?php for ($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
											<option value="<?=$i?>" <?=checked($ts_times, $i, 1)?>><?=$i?> 시</option>
											<?php } ?>
										</select>
										<select name="ts_mins">
											<?php for ($i = 0; $i <= 59; $i++) {$i = sprintf('%02d', $i)?>
											<option value="<?=$i?>" <?=checked($ts_mins, $i, 1)?>><?=$i?> 분</option>
											<?php } ?>
										</select>
										~ <br>
										<input type="input" name="ts_datee" class="input datepicker" size="10" value="<?=$ts_datee?>">
										<select name="ts_timee">
											<?php for ($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
											<option value="<?=$i?>" <?=checked($ts_timee, $i, 1)?>><?=$i?> 시</option>
											<?php } ?>
										</select>
										<select name="ts_mine">
											<?php for ($i = 0; $i <= 59; $i++) {$i = sprintf('%02d', $i)?>
											<option value="<?=$i?>" <?=checked($ts_mine, $i, 1)?>><?=$i?> 분</option>
											<?php } ?>
										</select>
                                        <label><input type="checkbox" name="ts_unlimited" class="ts_unlimited" <?=$ts_unlimited?>> 무제한</label>
									</td>
								</tr>
								<tr class="timesale_N">
									<th scope="row">지정시간 상품명</th>
									<td>
										<input type="text" name="ts_names" class="input input_full" value="<?=inputText($data['ts_names'])?>">
										<ul class="list_info tp">
											<li>지정시간이 되면 상품명이 변경됩니다.</li>
											<li>미입력시 변경되지 않습니다.</li>
										</ul>
									</td>
								</tr>
                                <?php if ($prd_type == '1') { ?>
								<tr class="timesale_N">
									<th scope="row">지정시간 할인/적립</th>
									<td>
										<div class="bottom_line">
											<label><input type="radio" name="ts_event_type" value="1" <?=checked($data['ts_event_type'], '1')?>> 할인</label>
											<label><input type="radio" name="ts_event_type" value="2" <?=checked($data['ts_event_type'], '2')?>> 적립</label>
										</div>
										<input type="text" name="ts_saleprc" class="input right" size="5" value="<?=inputText($data['ts_saleprc'])?>">
										<label><input type="radio" name="ts_saletype" value="price" <?=checked($data['ts_saletype'], 'price')?>> 원</label>
										<label><input type="radio" name="ts_saletype" value="percent" <?=checked($data['ts_saletype'], 'percent')?>> %</label>
										<ul class="list_info tp">
											<li>미입력 또는 0 입력 시 할인/적립이 되지 않습니다.</li>
										</ul>
									</td>
								</tr>
								<tr class="timesale_N">
									<th scope="row">할인/적립 절사단위</th>
									<td>
										<label><input type="radio" name="ts_cut" value="1" <?=checked($data['ts_cut'], '1')?>> 절사 없음</label>
										<label><input type="radio" name="ts_cut" value="10" <?=checked($data['ts_cut'], '10')?>> 10원 단위</label>
										<label><input type="radio" name="ts_cut" value="100" <?=checked($data['ts_cut'], '100')?>> 100원 단위</label>
										<label><input type="radio" name="ts_cut" value="1000" <?=checked($data['ts_cut'], '1000')?>> 1,000원 단위</label>
									</td>
								</tr>
                                <?php } ?>
								<tr class="timesale_N">
									<th scope="row">시간종료 후 상품명</th>
									<td>
										<input type="text" name="ts_namee" class="input input_full" value="<?=inputText($data['ts_namee'])?>">
										<ul class="list_info tp">
											<li>지정시간이 종료되면 상품명이 변경됩니다.</li>
											<li>미입력시 변경되지 않습니다.</li>
										</ul>
									</td>
								</tr>
								<tr class="timesale_N">
									<th scope="row">시간종료 후 상태</th>
									<td>
										<select name="ts_state">
                                            <option value="">변경 없음</option>
											<?php for ($key = 3; $key <= 4; $key++) { ?>
												<?php if ($key != 1) { ?>
												<option value="<?=$key?>" <?=checked($data['ts_state'], $key, true)?>><?=$_prd_stat[$key]?></option>
												<?php } ?>
											<?php } ?>
										</select>
										<ul class="list_info tp">
											<li>지정시간이 종료되면 상품 상태가 변경됩니다.</li>
										</ul>
									</td>
								</tr>
								<tr class="timesale_Y">
									<th scope="row">타임세일 세트선택</th>
									<td>
										<?=selectArray($_ts_set, 'ts_set', false, ':: 세트선택 ::', $data['ts_set'], 'chgTsSet(this.value)')?>
									</td>
								</tr>
                                <?php if ($prd_type == '1') { ?>
								<tr class="timesale_Y">
									<th scope="row">할인/적립</th>
									<td><span class="ts_desc"><?=$ts_set['desc']?></span></td>
								</tr>
								<tr class="timesale_Y">
									<th scope="row">할인기간</th>
									<td><span class="ts_date"><?=$ts_set['ts_dates']?> ~ <?=$ts_set['ts_datee']?></span></td>
								</tr>
                                <?php } ?>
								<tr class="timesale_Y">
									<th scope="row">시간종료 후 상태</th>
									<td><span class="ts_state"><?=$ts_set['ts_state_str']?></span></td>
								</tr>
							</tbody>
						</table>
						<?php } else { ?>
						<ul class="list_msg">
							<li>한정시간판매/타임세일을 사용하시려면, 좌측 설정버튼을 활용하세요.</li>
						</ul>
						<?php } ?>
					</td>
				</tr>
                <?php } ?>
				<?php if ($item_9_1) { ?>
				<tr>
					<th scope="row">창고 위치</th>
					<td>
						<select name="sbig" class="cate_multis" style="width:129px;" onchange="chgCateInfinite(this, 2, 's');">
							<option value="">::대분류::</option>
							<?=$item_9_1?>
						</select>
						<select name="smid" class="cate_multis" style="width:129px;" onchange="chgCateInfinite(this, 3, 's');">
							<option value="">::중분류::</option>
							<?=$item_9_2?>
						</select>
						<select name="ssmall" class="cate_multis" style="width:129px;" onchange="chgCateInfinite(this, 4, 's');">
							<option value="">::소분류::</option>
							<?=$item_9_3?>
						</select>
						<select name="sdepth4" class="cate_multis" style="width:129px;">
							<option value="">::세분류::</option>
							<?=$item_9_4?>
						</select>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row">요약 설명</th>
					<td><textarea id="content1" name="content1" class="txta"><?=stripslashes($data['content1'])?></textarea></td>
				</tr>
				<tr>
					<th scope="row">검색 키워드</th>
					<td><input type="text" name="keyword" value="<?=inputText($data['keyword'])?>" class="input input_full"></td>
				</tr>
				<?php if ($cfg['use_prd_etc1'] == 'Y') { ?>
				<tr>
					<th scope="row"><?=$cfg['prd_etc1']?> <a href="?body=product@product_common#go_prdetc" target="_blank" class="setup btt" tooltip="설정"></a></th>
					<td><input type="text" name="etc1" value="<?=inputText($data['etc1'])?>" class="input input_full"></td>
				</tr>
				<?php } ?>
				<?php if ($cfg['use_prd_etc2'] == 'Y') { ?>
				<tr>
					<th scope="row"><?=$cfg['prd_etc2']?> <a href="?body=product@product_common#go_prdetc" target="_blank" class="setup btt" tooltip="설정"></a></th>
					<td><input type="text" name="etc2" value="<?=inputText($data['etc2'])?>" class="input input_full"></td>
				</tr>
				<?php } ?>
				<?php if ($cfg['use_prd_etc3'] == 'Y') { ?>
				<tr>
					<th scope="row"><?=$cfg['prd_etc3']?> <a href="?body=product@product_common#go_prdetc" target="_blank" class="setup btt" tooltip="설정"></a></th>
					<td><input type="text" name="etc3" value="<?=inputText($data['etc3'])?>" class="input input_full"></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<!-- //기본정보 -->

        <!-- 도서 정보 -->
        <?php
            if ($prd_type == '1' && $scfg->comp('use_navershopping_book', 'Y') == true) {
                require_once 'product_register_book.inc.php';
            }
        ?>

		<div class="box_title_reg">
			<h2 class="title">추가항목</h2>
			<a href="./?body=product@product_filed" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>
		<?php if ($prd_fieldset1) { ?>
		<table class="tbl_row_reg">
			<caption class="hidden">추가항목</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<?=$prd_fieldset1?>
		</table>
		<?php } ?>

		<div class="box_title_reg">
			<h2 class="title">상품정보제공고시</h2>
			<a href="./?body=product@product_definition" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>

		<table class="tbl_row_reg">
			<caption class="hidden">상품정보제공고시</caption>
			<colgroup>
				<col style="width:134px">
				<col>
			</colgroup>
			<thead>
				<tr>
					<td colspan="2" style="padding: 7px 20px;">
						<?=selectArray($_fieldsets, 'fieldset', null, ':: 선택 :;', $data['fieldset'], "chgFieldSet(this, $pno);")?>
					</td>
				</tr>
			</thead>
			<tbody id="fieldset2">
				<?=$prd_fieldset2?>
			</tbody>
		</table>

        <?php if ($scfg->comp('use_talkpay', 'Y') == true) { ?>
		<div class="box_title_reg">
			<h2 class="title">카카오페이구매 정보제공고시</h2>
			<a href="?body=product@product_definition&type=talkstore" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>

		<table class="tbl_row_reg">
			<caption class="hidden">카카오페이구매 정보제공고시</caption>
			<colgroup>
				<col style="width:134px">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<td colspan="2" style="padding: 7px 20px;">
						<?=selectArray($_kakao_annoucements, 'kakao_annoucement_idx', null, ':: 선택 :;', $kakao_info['annoucement_idx'])?>
					</td>
				</tr>
			</tbody>
		</table>
        <?php } ?>
	</div>

	<div class="right_reg">
		<?PHP
		if ($prd_type != '1') { // 세트상품 등록 다이얼로그
			require 'product_register_right_set.inc.php';
		}
		?>

		<div class="box_title_reg first">
			<h2 class="title">판매정보</h2>
		</div>
		<table class="tbl_row_reg">
			<caption class="hidden">판매정보</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<tr>
				<th scope="row"><?=$cfg['product_normal_price_name']?> <a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_nece_field','chgPrdOn')" class="setup btt" tooltip="명칭변경"></a></th>
				<td>
					<div class="input_money">
						<input type="text" name="normal_prc" class="input input_full input_won" value="<?=$data['normal_prc']?>" data-decimal="<?=$cfg['currency_decimal']?>" data-type="sell" data-prdtype="<?=$prd_type?>">
						<span><?=$cfg['currency_type']?></span>
					</div>
				</td>
			</tr>
			<?php if($prd_type == '1') { // 관리 소비자가 ?>
			<tr <?=($cfg['m_currency_type']=='N' || $cfg['currency_type'] == $cfg['m_currency_type'])?'style="display:none;"':''?>>
				<th scope="row"><?=$cfg['product_normal_price_name']?>(관리) <a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_nece_field','chgPrdOn')" class="setup btt" tooltip="명칭변경"></a></th>
				<td>
					<div class="input_money">
						<input type="text" name="m_normal_prc" class="input input_full input_won" value="<?=$data['m_normal_prc']?>" data-decimal="<?=$cfg['m_currency_decimal']?>" data-type="manage" data-prdtype="<?=$prd_type?>">
						<span><?=$cfg['m_currency_type']?></span>
					</div>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row"><strong><?=$cfg['product_sell_price_name']?></strong> <a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_nece_field','chgPrdOn')" class="setup btt" tooltip="명칭변경"></a></th>
				<td>
					<div class="input_money">
						<input type="text" name="sell_prc" class="input input_full input_won" value="<?=$data['sell_prc']?>" data-decimal="<?=$cfg['currency_decimal']?>" data-type="sell" data-prdtype="<?=$prd_type?>">
						<span><?=$cfg['currency_type']?></span>
					</div>
                    <?php if ($prd_type != '1') { ?>
                    <p class="explain set_method3" style="display: none">상품에 적용된 개별할인으로 판매가가 달라질 수 있습니다.</p>
                    <p class="explain set_method6" style="display: none">골라담기 세트로 구성된 상품은 할인정책이 적용되지 않습니다.</p>
                    <?php } ?>
				</td>
			</tr>
			<?php if($prd_type == '1') { // 관리 소비자가 ?>
			<tr <?=($cfg['m_currency_type']=='N' || $cfg['currency_type'] == $cfg['m_currency_type'])?'style="display:none;"':''?>>
				<th scope="row"><strong><?=$cfg['product_sell_price_name']?>(관리)</strong> <a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_nece_field','chgPrdOn')" class="setup btt" tooltip="명칭변경"></a></th>
				<td>
					<div class="input_money">
						<input type="text" name="m_sell_prc" class="input input_full input_won " value="<?=$data['m_sell_prc']?>" data-decimal="<?=$cfg['m_currency_decimal']?>" data-type="manage" data-prdtype="<?=$prd_type?>">
						<span><?=$cfg['m_currency_type']?></span>
					</div>
				</td>
			</tr>
			<?php }
			if ($prd_type == '1') { // 회원 그룹별 가격

				$memprcres = $pdo->iterator("select * from `$tbl[member_group]` where `use_group` = 'Y' order by `no` asc");
                foreach ($memprcres as $memprc_data) {
					if($cfg['group_price'.$memprc_data['no']] != 'Y') continue;
					$group_price = true;

					$data['sell_prc'.$memprc_data['no']] = parsePrice($data['sell_prc'.$memprc_data['no']]);
			?>
			<tr>
				<th scope="row"><?=$memprc_data['name']?> 가격 <a href="javascript:;" onclick="window.open('./pop.php?body=member@member_group_addinfo&no=<?=$memprc_data['no']?>', 'wm_saddinfoMemberGroup', 'top=10,left=200,width=500,height=250,status=no,toolbars=no,scrollbars=no');" class="setup btt" tooltip="그룹별가격 설정"></a></th>
				<td>
					<div class="input_money">
						<input type="text" name="sell_prc<?=$memprc_data['no']?>" class="input input_full input_won " value="<?=$data['sell_prc'.$memprc_data['no']]?>" data-decimal="<?=$cfg['currency_decimal']?>"> <span><?=$cfg['currency_type']?></span>
					</div>
				</td>
			</tr>
			<?php } ?>
			<?php if ($group_price) { ?>
			<tr>
				<td colspan="2">
					<ul class="list_msg">
						<li>그룹별 가격 입력 시 상품리스트에 <?=$cfg['product_sell_price_name']?>를 대신하여 해당 고객이 속한 그룹의 가격으로 표기 및 판매됩니다.</li>
						<li>그룹별 가격을 0으로 입력하시면 상품의 <?=$cfg['product_sell_price_name']?>로 표기 및 판매됩니다.</li>
					</ul>
				</td>
			</tr>
			<?php } ?>
			<?php } ?>
			<?php if ($prd_type != '1') { // 풀세트 할인 가격 ?>
			<tr class="set_method5">
				<th scope="row">세트 할인</th>
				<td>
					<div class="set_method3" style="display:none;">
						<input type="text" name="set_sale_prc" value="<?=$data['set_sale_prc']?>" class="input input_won" size="14">
						<select name="set_sale_type">
							<option value="p" <?=checked($data['set_sale_type'], 'p', true)?>>%</option>
							<option value="m" <?=checked($data['set_sale_type'], 'm', true)?>><?=$cfg['currency_type']?></option>
						</select>
					</div>
					<div class="set_method4" style="display:none;">
						<span class="box_btn"><input
							type="button"
							class="set_rate_btn"
							value="<?=(strlen($data['set_rate'])>0) ? '할인 수정' : '할인 등록'?>"
							onclick="setDiscount.open('field=set_rate')"
						></span>
					</div>
				</td>
			</tr>
			<?php }?>
			<?php if($prd_type == '1' && $cfg['use_prc_consult'] == 'Y') { // 판매가 대체문구 ?>
			<tr>
				<th scope="row">
					판매가 대체문구
					<a href="?body=product@product_common#prc_consultation" class="setup btt" tooltip="판매가 대체문구"></a>
				</th>
				<td>
					<input type="text" name="sell_prc_consultation" class="input input_full" value="<?=inputText($data['sell_prc_consultation'])?>" >
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="p_color">판매가 대체문구를 입력하시면 상품 구매가 불가능하며, 쇼핑몰에 가격대신 대체문구가 출력됩니다.</div>
				</td>
			</tr>
			<tr >
				<td colspan="2">
					<textarea name="sell_prc_consultation_msg" class="txta"><?=inputText($data['sell_prc_consultation_msg'])?></textarea>
				</td>
			</tr>
				<td colspan="2">
					<div class="p_color">판매가 대체문구가 입력된 상품을 장바구니에 넣거나 즉시구매시 입력하신 메시지가 경고창으로 출력됩니다.</p>
				</td>
			</tr>
			<?php } ?>
			<?php if ($prd_type == '1') { // 적립금, 재고관리 ?>
			<?php if (isset($cfg['use_qty_discount']) == true && $cfg['use_qty_discount'] == 'Y') { ?>
			<tr>
				<th scope="row">수량할인</th>
				<td>
					<span class="box_btn_s"><input
						type="button"
						class="qty_rate_btn"
						value="<?=(strlen($data['qty_rate'])>0) ? '할인 수정' : '할인 등록'?>"
						onclick="setDiscount.open('field=qty_rate')"
					></span>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">적립금</th>
				<td>
					<input type="text" name="milage" value="<?=parsePrice($data['milage'])?>" class="input input_won" style="width:78px;"> <?=$cfg['currency_type']?>
					<span class="box_btn_s"><a href="#" onclick="showMilageCal(); return false;">% 자동계산기</a></span>
					<?php if ($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) { ?>
					<ul class="list_msg">
						<li>현재 결제 금액 단위의 적립금 설정을 사용하고 계십니다.</li>
						<li class="p_color2">결제 금액 단위의 적립금을 사용하실때에는 상품별로 설정된 적립금은 무시됩니다.</li>
					</ul>
					<script type="text/javascript">
					$('input[name=milage]').attr('readonly', true);
					$('select[name=mil_per]').attr('disabled', true);
					</script>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><strong>재고관리</strong><a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_eatype_setting','chgPrdOn', 'Y', 450,100)" class="setup btt" tooltip="기본값 설정"></a></th>
				<td>
				<label class="p_cursor"><input type="radio" name="ea_type" id="ea_type" value="1" <?=checked($data['ea_type'], 1)?> onClick="chgEaType(this);"> 사용</label>
				<label class="p_cursor"><input type="radio" name="ea_type" id="ea_type" value="2" <?=checked($data['ea_type'], 2)?> onClick="chgEaType(this);"> 사용안함</label>
				</td>
			</tr>
			<?php } ?>
			<?php if($prd_type == '1') { ?>
			<tr>
				<th scope="row">1회 주문한도</th>
				<td>
					최소 <input type="text" name="min_ord" value="<?=$data['min_ord']?>" class="input" style="width:60px;"> ~ 최대 <input type="text" name="max_ord" value="<?=$data['max_ord']?>" class="input" style="width:60px;">
					<p class="explain right">(미입력시 무제한)</p>
				</td>
			</tr>
			<tr>
				<th scope="row">회원별 주문한도</th>
				<td>
					최대 <input type="text" name="max_ord_mem" value="<?=$data['max_ord_mem']?>" class="input" style="width:60px;"> 개 까지 주문 가능
					<p class="explain right">(미입력시 무제한)</p>
					<ul class="list_msg">
						<li>회원이 과거 구매했던 이력이 전부 포함됩니다.(취소 상품 제외)</li>
						<li>설정이 적용된 상품은 네이버페이를 포함한 비회원 구매가 불가능합니다.</li>
					</ul>
				</td>
			</tr>
			<?php } ?>
		</table>

		<?php if($prd_type == '1') { ?>
		<div class="box_title_reg">
			<h2 class="title">국내배송</h2>
			<a href="./?body=config@delivery" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>
		<table class="tbl_row_reg">
			<caption class="hidden">해외배송</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<tr>
				<th scope="row">배송비 선택</th>
				<td>
					<ul>
						<li><label><input type="radio" name="delivery_type" value="basic" <?=checked($delivery_type, 'basic')?>> 기본 배송비</label></li>
						<li><label><input type="radio" name="delivery_type" value="free_delivery" <?=checked($delivery_type, 'free_delivery')?>> 기본 배송비 - 무료배송</label></li>
						<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
						<li>
							<label><input type="radio" name="delivery_type" value="product" <?=checked($delivery_type, 'product')?>> 개별 배송비</label>
							<a href="?body=config@delivery_set" target="_blank" class="setup btt" tooltip="개별 배송비 설정"></a>
							<p style="margin:5px 0 0 25px;">
								<?=selectArray($_delivery_sets, 'delivery_set', false, ':: 배송정책 선택 :: ', $data['delivery_set'], 'setDeliverySet(this)')?>
								<ul class="list_msg">
									<li>개별 배송비 기능 사용시 네이버페이 판매가 불가능합니다.</li>
								</ul>
							</p>
							<script type="text/javascript">
							$(document).ready(function() {
                                (setDeliveryType = function() {
                                    var set = $('select[name=delivery_set]');
                                    if ($(':checked[name=delivery_type]').val() == 'product') {
                                        set.attr('disabled', false);
                                    } else {
                                        set.attr('disabled', true);
                                    }

                                    setDeliverySet(set[0]);
                                })();
                                $(':radio[name=delivery_type]').click(setDeliveryType);
							});
							</script>
						</li>
						<?php } ?>
					</ul>
				</td>
			</tr>
		</table>

		<div class="box_title_reg">
			<h2 class="title">해외배송</h2>
			<div id="hs_link"><a href="https://unipass.customs.go.kr/clip/index.do" target="_blank"><strong>관세율표</strong> <img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="새창"></a></div>
			<a href="./?body=config@multi_shop" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>
		<?php if ($cfg['delivery_fee_type'] == "O" || $cfg['delivery_fee_type'] == "A") { ?>
		<table class="tbl_row_reg">
			<caption class="hidden">해외배송</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<tr>
				<th scope="row">
					HS 코드
					<a href="./?body=product@hs_code" target="_blank" class="setup btt" tooltip="설정"></a>
				</th>
				<td>
					<input type="text" name="hs_code" value="<?=$data['hs_code']?>" class="input" style="width:148px;">
					<span class="box_btn_s blue"><a href="javascript:;" onclick="window.open('./pop.php?body=product@hs_code&from=popup','hs_code','status=no,width=900px,height=800px')">검색</a></span>
				</td>
			</tr>
			<tr>
				<th scope="row">무게(kg)</th>
				<td>
					<input type="text" name="weight" size="10" class="input" value="<?=$data['weight']?>"> kg
					<ul class="list_msg">
						<li>무게에 따라 요금이 책정되며 해외배송 가능상품으로 지정됩니다.</li>
						<li>해외고객이 무게가 입력되지 않은 상품을 함께 구매하는 경우 해외배송 불가 상품으로 주문시 경고가 출력됩니다.</li>
					</ul>
				</td>
			</tr>
		</table>
		<?php } ?>

		<div class="box_title_reg">
			<h2 class="title">입점사정보</h2>
			<a href="?body=config@partner_shop" class="setup btt" tooltip="관리" target="_blank"></a>
		</div>
		<table class="tbl_row_reg">
			<caption class="hidden">공급정보</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<?php if ($cfg['use_partner_shop'] == 'Y') { ?>
			<?php if ($admin['level'] < 4) { ?>
			<tr>
				<th>입점파트너</th>
				<td>
					<?=selectArray($_partners, 'partner_no', null, '본사', $data['partner_no'], 'chgPartnerRate(this)')?>
					<span class="box_btn_s blue"><input type="button" value="입점사 검색" onclick="ptn_search.open();"></span>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th>입점사 상품 수수료</th>
				<td>
					<select name="partner_rate">
						<option value=""> </option>
						<?=$partner_rate_select?>
					</select> %
				</td>
			</tr>
			<?php if ($cfg['use_partner_delivery'] == 'Y') { ?>
			<tr>
				<th>입점사 상품 배송처</th>
				<td>
					<label><input type="radio" name="dlv_type" value="0" <?=checked($data['dlv_type'], '0')?> /> 입점파트너 배송 (현재설정)</label>
					<label><input type="radio" name="dlv_type" value="1" <?=checked($data['dlv_type'], '1')?> /> 본사 배송</label>
				</td>
			</tr>
			<?php } ?>
			<?php } ?>
		</table>

		<div class="box_title_reg">
			<h2 class="title">공급정보</h2>
			<a href="?body=product@provider" class="setup btt" tooltip="관리" target="_blank"></a>
		</div>
		<table class="tbl_row_reg">
			<caption class="hidden">공급정보</caption>
			<colgroup>
				<col style="width:134px">
			</colgroup>
			<tr>
				<th scope="row">장기명</th>
				<td><input type="text" name="origin_name" value="<?=inputText($data['origin_name'])?>" class="input input_full"></td>
			</tr>
			<tr>
				<th scope="row">사입처</th>
				<td>
					<?=selectArray($_sellers, 'seller_idx', null, ':: 사입처 선택 ::', $data['seller_idx'])?>
					<span class="box_btn_s blue"><a href="javascript:;" onclick="window.open('./pop.php?body=product@provider_select','provider_select','status=no,width=10px,height=10px')">검색</a></span>
				</td>
			</tr>
			<tr>
				<th scope="row">사입원가</th>
				<td>
					<div class="input_money">
						<input type="text" name="origin_prc" value="<?=parsePrice($data['origin_prc'])?>" data-decimal="<?=$cfg['b_currency_decimal']?>" class="input input_full input_won">
						<span><?=$cfg['b_currency_type']?></span>
					</div>
				</td>
			</tr>
		</table>

		<?php if ($cfg['opmk_api'] && $has_opmks > 0 && !$data['ori_no']) { ?>
		<div class="box_title_reg">
			<h2 class="title">오픈마켓 가격 설정</h2>
			<a href="./?body=config@openmarket" target="_blank" class="setup btt" tooltip="설정"></a>
		</div>

		<table class="tbl_col">
			<colgroup>
				<col>
				<col style="width: 170px;">
			</colgroup>
			<tbody class="tbl_row_reg">
				<?php while ($opmk = parseOPMK($opmkres)) { ?>
				<tr>
					<td class="left"><?=stripslashes($opmk['name'])?></td>
					<td>
						<div class="input_money" style="width: 150px; margin: 0 10px;">
							<input type="text" name="opmk_price[<?=$opmk['api_code']?>]" class="input input_full input_won" value="<?=$opmk['sell_prc']?>" data-decimal="<?=$cfg['currency_decimal']?>" data-type="sell">
							<span><?=$cfg['currency_type']?></span>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<div class="box_bottom left">
			<ul class="list_msg">
				<li>마켓별 판매가가 다를 경우에만 입력해 주세요</li>
				<li>미입력시 기본 <?=$cfg['product_sell_price_name']?>로 등록됩니다.</li>
				<?php if ($cfg['opmk_api'] == 'shopLinker') { ?>
				<li>상품 저장 후, 샵링커 관리자에서 판매몰별로 상품 연동후 사용가능합니다.</li>
				<li class="p_color2">각 오픈마켓으로 데이터를 전송하므로 변경시 시간이 많이 소요될수 있습니다.</li>
				<?php } ?>
			</ul>
		</span>
		</div>
		<?php } ?>
		<?php } else { ?>
        <div><input type="hidden" name="partner_no" value="<?=$data['partner_no']?>"></div>
        <?php } ?>
	</div>

	<div class="clear"></div>

	<!-- 아이콘 -->
	<div class="box_title_reg">
		<h2 class="title">아이콘</h2>
		<a href="./pop.php?body=product@product_icon_list&pno=<?=$pno?>" onclick="wisaOpen($(this).attr('href')); return false" class="setup btt" tooltip="아이콘 추가관리"></a>
	</div>
	<div class="box_middle2" id="area_prdicon">
		<?php include $engine_dir."/_manage/product/product_icon_list.exe.php"; ?>
	</div>
	<!-- //아이콘 -->

	<?php if ($prd_type == '1') { ?>
	<!-- 관련상품 -->
	<?php for ($refkey = 1; $refkey <= $cfg['refprds']; $refkey++) {
		$refname = $cfg['refprd'.$refkey.'_name'];
		if(!$refname) $refname = '관련상품'.$refkey;
	?>
	<div class="box_title_reg">
		<input type="hidden" id="refhead_<?=$refkey?>" name="refhead_<?=$refkey?>" value="">
		<h2 class="title"><?=$refname?></h2>
		<?php if ($admin['partner_no']==0 || ($cfg['partner_prd_ref']=='Y' && $admin['partner_no']>0)) { ?>
		<span class="btns box_btn_s blue" <?php if ($admin['partner_no']==0) { ?>style="right:50px;"<?php } ?>><a href="javascript:;" onclick="psearch.opennew('exparam=<?=$refkey?>');">등록</a></span>
		<?php } ?>
		<?php if ($admin['partner_no']==0) { ?>
			<a href="?body=product@product_common#go_refprds" target="_blank" class="setup btt" tooltip="설정"></a>
		<?php } ?>
	</div>
	<div id="refArea<?=$refkey?>">
		<?php include $engine_dir."/_manage/product/product_ref_frm.exe.php"; ?>
	</div>
	<?php } ?>
	<!-- //관련상품 -->
	<?php } ?>

	<?php if ($prd_type == '1') { ?>
	<!-- 옵션 -->
	<div class="box_title_reg">
		<h2 class="title">옵션</h2>
	</div>
	<iframe id="optFrame" name="optFrame" src="about:blank" width="100%" height="50" scrolling="no" frameborder="0"></iframe>
	<!-- //옵션 -->
	<?php } ?>

	<!-- 상품이미지 -->
	<div class="box_title_reg">
		<h2 class="title">상품이미지</h2>
	</div>
	<table class="tbl_row_reg tbl_row_reg_line">
		<caption class="hidden">상품이미지</caption>
		<colgroup>
			<col style="width:16%">
			<col>
		</colgroup>
		<tr>
			<th>
                기본이미지 <?=$service_upgrade_info?>
                <div class="explain">(<?=$img_prdBasic_limit?>)</div>
            </th>
			<td>
				<ul class="thumb_register">
					<li>
						<div class="file_input_div">
							<input type="button" class="file_input_button">
							<input type="file" name="upfile1" data-limit="<?=$img_prdBasic_limit_byte?>" data-limit-str="<?=$img_prdBasic_limit?>" class="file_input_hidden" onchange='uploadPreview(event, 1)'>
							<div class="img"><img class='upfile_preview1 <?=$no_img[1]?>' src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile1']?>" alt=""></div>
						</div>
						<p class="summary">대 이미지</p>
						<p>확대보기 이미지</p>
						<div><?=delImgStr($data,1)?></div>
					</li>
					<li>
						<div class="file_input_div">
							<input type="button" class="file_input_button">
							<input type="file" name="upfile2" data-limit="<?=$img_prdBasic_limit_byte?>" data-limit-str="<?=$img_prdBasic_limit?>" class="file_input_hidden" onchange='uploadPreview(event, 2)'>
							<div class="img"><img class='upfile_preview2 <?=$no_img[2]?>' src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile2']?>" alt=""></div>
						</div>
						<p class="summary">중 이미지<span><?=$cfg['thumb2_w']?>X<?=$cfg['thumb2_h']?></span></p>
						<p>상세기본 이미지</p>
						<div><?=delImgStr($data,2)?></div>
					</li>
					<li>
						<div class="file_input_div">
							<input type="button" class="file_input_button">
							<input type="file" name="upfile3" data-limit="<?=$img_prdBasic_limit_byte?>" data-limit-str="<?=$img_prdBasic_limit?>" class="file_input_hidden" onchange='uploadPreview(event, 3)'>
							<div class="img"><img class='upfile_preview3 <?=$no_img[3]?>' src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile3']?>" alt=""></div>
						</div>
						<p class="summary">소 이미지<span><?=$cfg['thumb3_w']?>X<?=$cfg['thumb3_h']?></span></p>
						<p>상품리스트 이미지</p>
						<div><?=delImgStr($data,3)?></div>
					</li>
				</ul>
				<p class="auto_thumb"><label class="p_cursor"><input type="checkbox" name="auto_thumb" id="auto_thumb" value="Y" onClick="checkThumb()"> 중/소 자동생성</label></p>
			</td>
		</tr>
		<?php if ($cfg['add_prd_img'] > 3) { ?>
		<tr>
			<th>추가이미지<br><span class="explain">(<?=$_basic_img_size_limit?>)</span></th>
			<td>
				<ul class="thumb_register">
					<?php
						for($ii=4; $ii <= $cfg['add_prd_img']; $ii++) {
							$jj=$ii-3;
					?>
					<li>
						<div class="file_input_div">
							<input type="button" class="file_input_button">
							<input type="file" name="upfile<?=$ii?>" data-limit="<?=$img_prdBasic_limit_byte?>" data-limit-str="<?=$img_prdBasic_limit?>" class="file_input_hidden" onchange='uploadPreview(event, <?=$ii?>)'>
							<div class="img"><img class="upfile_preview<?=$ii?> <?=$no_img[$ii]?>" src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile'.$ii]?>" alt=""></div>
						</div>
						<p class="summary"><?=$cfg['prd_img'.$ii]?><span><?=$cfg['thumb'.$ii.'_w']?>X<?=$cfg['thumb'.$ii.'_h']?></span></p>
						<p>추가<?=$jj?> 이미지</p>
						<div><?=delImgStr($data,$ii)?></div>
					</li>
					<?php } ?>
				</ul>
			</td>
		</tr>
		<?php } ?>
		<?php if ($prd_type == '1' && $cfg['compare_image_no'] === '0') { ?>
		<tr>
			<th>네이버쇼핑 이미지</th>
			<td>
				<div class="thumb_register">
					<div class="file_input_div">
						<input type="button" class="file_input_button">
						<input type="file" name="upfile0" class="file_input_hidden" onchange='uploadPreview(event, 0)'>
						<div class="img"><img class='upfile_preview0 <?=$no_img[0]?>' src="<?=$file_url?>/<?=$data['updir']?>/<?=$data['upfile0']?>" alt=""></div>
						<!-- 수정시 예시) 이미지 삽입
						<div class="img"><img src="http://wing.freeimg.mywisa.com/_data/product/201505/15/836debc9cd900dbf75f6b481970c55b3.jpg" alt=""></div>
						-->
					</div>
					<p class="summary"><span>300 x 300</span></p>
					<div><?=delImgStr($data, 0)?></div>
				</div>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- //상품이미지 -->

	<!-- 부가이미지 -->
	<div class="box_title_reg">
		<h2 class="title">부가이미지</h2>
	</div>
	<table class="tbl_row_reg tbl_row_reg_line">
		<caption class="hidden">상품이미지</caption>
		<colgroup>
			<col style="width:16%">
			<col>
		</colgroup>
		<tr>
			<th>
                <?=($cfg['disable_wingdisk'] == true && $_SESSION['mall_goods_idx'] != '3') ? $_SESSION['disk_svc_name'] : '무료Disk'?>
                <?=str_replace('pro-master', 'pro-master2', $service_upgrade_info)?>
                <div class="explain">(<?=$img_prdContent2_limit?>)</div>
            </th>
			<td>
				<iframe id="up_aimg" src="about:blank" width="100%" height="50" scrolling="no" frameborder="0"></iframe>
			</td>
		</tr>
		<?php if ($_SESSION['mall_goods_idx'] == '3' && $asvcs[0]->type[0] != '10') { ?>
		<tr>
			<th><?=$_SESSION['disk_svc_name']?></th>
			<td>
				<iframe id="up_aimg_wdisk" src="about:blank" width="100%" height="50" scrolling="no" frameborder="0"></iframe>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- //부가이미지 -->

	<!-- 상품상세설명 -->
	<ul class="tab_pr">
		<li class="on">
			<a onclick="tabover(0); return false;" class="box">PC 상품상세설명</a>
			<span class="box_btn_s icon newpage"><a href='#' onclick='detailPreview(1, "<?=$pno?>"); return false;'>새창열기</a></span>
		</li>
		<li>
			<?php if ($cfg['use_m_content_product'] == 'Y') { ?>
			<a onclick="tabover(1); return false;" class="box">모바일 전용 상품상세설명</a>
			<label><input type="checkbox" name="use_m_content" value="Y" <?=checked($data['use_m_content'], 'Y')?>> 사용함</label>
			<span class="box_btn_s icon newpage"><a href='#' onclick='detailPreview(1, "<?=$pno?>", "m"); return false;'>새창열기</a></span>
			<?php } else { ?>
			<a class="box" onclick="useMobileContent(); return false;">모바일 전용 상품상세설명</a>
			<span class="setup" onclick="useMobileContent(); return false;" tooltip="설정"></span>
			<?php } ?>
		</li>
	</ul>
	<div class="tabcnt tabcnt0">
		<?php
			if(empty($_GET['pno'])) {
				include $engine_dir.'/_manage/product/product_content2.exe.php';
			} else {
		?>
		<div id="prdContent">
			<div class="box_bottom top_line">
				<textarea id="content22" name="content22" class="txta p_color p_cursor" style="height:40px; padding:10px; color:#00aeef; font-size:16px; font-weight:bold;">상세설명을 수정하시려면 클릭하세요</textarea>
			</div>
			<textarea id="content2" name="content2" style="display:none; width:100%;"><?=stripslashes($data['content2'])?></textarea>
		</div>
		<script type="text/javascript">
			$('#content22').click(function() {
				$.ajax({
					type: "POST",
					url: "./?body=product@product_content2.exe",
					data: "pno=<?=$pno?>&stat=<?=$stat?>",
					success: function(result) {
						$('#prdContent').html(result);
					}
				});
			});
		</script>
		<?php } ?>
	</div>
	<?php if ($cfg['use_m_content_product'] == 'Y') { ?>
	<div class="tabcnt tabcnt1" style="display:none;">
		<?php
			if(empty($_GET['pno'])) {
				include $engine_dir.'/_manage/product/product_m_content.exe.php';
			} else {
		?>
		<div id="prd_m_Content">
			<div class="box_bottom top_line">
				<textarea id="mcontent_22" name="mcontent" class="txta p_color p_cursor" style="height:40px; padding:10px; color:#00aeef; font-size:16px; font-weight:bold;">상세설명을 수정하시려면 클릭하세요</textarea>
			</div>
			<textarea id="mcontent_22" name="m_content" style="display:none; width:100%;"><?=stripslashes($data['m_content'])?></textarea>
		</div>
		<script type="text/javascript">
			$('#mcontent_22').click(function() {
				$.ajax({
					type: "POST",
					url: "./?body=product@product_m_content.exe",
					data: "pno=<?=$pno?>&stat=<?=$stat?>",
					success: function(result) {
						$('#prd_m_Content').html(result);
					}
				});
			});
		</script>
		<?php } ?>
	</div>
	<?php } ?>
	<!-- //상품상세설명 -->

	<!-- 공통정보 -->
	<div class="box_title_reg">
		<h2 class="title">공통정보</h2>
		<!--<span class="box_btn_s blue btns" style="right:50px;"><a href="./?body=product@product_common" target="_blank">변경</a></span>-->
		<a href="?body=product@product_common" target="_blank" class="setup btt common_content" tooltip="설정"></a>
	</div>
	<table class="tbl_row_reg">
		<caption class="hidden">공통정보</caption>
		<colgroup>
			<col style="width:134px">
		</colgroup>
		<?php for ($i = 3; $i <= 5; $i++) {
            $disabled = ( $data['content'.$i.'_default'] === 'Y' ) ? 'disabled' : '';
            ?>
		<tr>
			<th scope="row">
				<?=$cfg['content'.$i]?>
				<br><label class="p_cursor p_color"><input type="checkbox" name="content<?=$i?>_default" value="1" <?=checked($data['content'.$i.'_default'],'Y')?> onClick="defaultContent(<?=$i?>)"> 기본</label>
			</th>
			<td>
				<textarea id="content<?=$i?>" name="content<?=$i?>" <?=$disabled?> class="txta"><?=stripslashes($data['content'.$i])?></textarea>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- //공통정보 -->

	<!-- 상품메모 -->
	<div style="position: relative;">
		<div class="box_title_reg">
			상품메모
			<a href="#" onclick="toggleMemoList(this); return false;" class="btn_toggle <?=$toggle_list_memo?>"></a>
		</div>
		<div id="product_memo_list_in">
		<?PHP
			$memo_type = 3;
			require 'product_memo_list_in.exe.php';
		?>
		</div>
	</div>
	<!-- //상품메모 -->

	<?PHP
		if ($prd_type == '1' && $cfg['use_kakaoTalkStore'] == 'Y' && $admin['level'] < 4) {
			require 'product_register_kakaoTalkStore.inc.php';
		}
	?>

    <input type="hidden" name="n_store_check_hidden" id="n_store_check_hidden" value="">
	<?PHP
		if ($prd_type == '1' && getSmartStoreState() == true && $admin['level'] < 4) {
			include_once $engine_dir."/_manage/product/product_nstore_frm.php";
		}
	?>

	<?php loadPlugin('product_register_frm')?>

	<div id="reg_footer" class="center">
		<label class="msg p_cursor"><input type="checkbox" name="after_list" value="Y" id="after_list" <?=checked($_COOKIE['after_list'],"Y")?> onClick="checkAL(this)"> 등록후 목록으로 이동 <span class="explain">(설정이 저장됨)</span></label>
		<div class="btns">
			<span id="stpBtn" class="box_btn blue"><input type="submit" value="확인"></span>
			<span class="box_btn gray"><input type="button" value="PC 미리보기" onclick="detailPreview(2,<?=$pno?>)"></span>
			<?php if ($cfg['use_m_content_product'] == 'Y') { ?>
			<span class="box_btn gray"><input type="button" value="모바일 미리보기" onclick="detailPreview(2,<?=$pno?>, 'm')"></span>
			<?php } ?>
			<?php if ($rURL) { ?>
			<span class="box_btn"><a href="<?=$rURL?>">취소</a></span>
			<?php } ?>
		</div>
	</div>
</form>

<!-- 관리자메모 -->
<?php if ($admin['level'] < 4) { ?>
<div id="register_memo">
	<a class="memo memo_toggle btt" tooltip="메모"><span id="memo_cnt"><?=number_format($pdo->row("select count(*) from $tbl[order_memo] where type=3 and ono='$pno'"))?></span></a>
	<div class="box_memo">
		<div id="mng_memo_area">
			<?php include $engine_dir."/_manage/product/product_memo_list.exe.php"; ?>
		</div>
		<span class="close memo_toggle"></span>
	</div>
</div>
<?php } ?>
<!-- //관리자메모 -->

<div class="milage_cal">
	<p class="title">적립금 % 자동계산기</p>
	<div class="cal">
		실판매가 <strong><span class='_price'></span> <?=$cfg['currency_type']?></strong>의
		<input type="text" id="milage_cal_per" class="input" placeholder="적립률 예)10"> %는
		<input type="text" id="milage_cal_res" class="input readonly" readonly> <?=$cfg['currency_type']?> 입니다.
	</div>
	<div class="btn">
		<span class="box_btn blue"><input type="button" value="적용" onclick="milageSet();"></span>
		<span class="box_btn"><a href="#" onclick="hideMilageCal();">닫기</a></span>
	</div>
</div>

<script type="text/javascript">
	// 상품상세설명 탭
	function tabover(no) {
		var tabs = $('.tab_pr').find('li');
		tabs.each(function(idx) {
			var detail = $('.tabcnt'+idx);
			var img = $(this);
			if(no == idx) {
				detail.show();
				img.addClass('on');
			} else {
				detail.hide();
				img.removeClass('on');
			}
		})
		if($('#mcontent_22').length == 0 && no == 1) {
            var upf1 = document.getElementById('m_up_fdisk');
            var upf2 = document.getElementById('m_up_wdisk');
            var has_wdisk = parseInt('<?=$wdisk[0]->img_limit[0]?>');

			seCall('m_content', '', (upf2 && has_wdisk > 0) ? 'm_up_wdisk' : 'm_up_fdisk');
            if(upf1 && upf1.style.height.replace('px', '') == '0') upf1.contentWindow.location.reload();
            if(upf2 && upf1.style.height.replace('px', '') == '0') upf2.contentWindow.location.reload();
		}
	}

	// 메모 토글
	$(".memo_toggle").click(function(){
		if ($('.box_memo').css('display') == 'block') {
			$('.box_memo').hide();
			$('#up_aimg, #up_aimg_wdisk').css('visibility', '');
			removeDimmed();
		} else {
			setDimmed('#000', 0.5);
			$('.box_memo').show();
			$('#up_aimg, #up_aimg_wdisk').css('visibility', 'hidden');
			reloadMemo(null, 1);
		}
		$('#qdBackground').click(function() {
			$('.box_memo').hide();
			$('#up_aimg, #up_aimg_wdisk').css('visibility', '');
			removeDimmed();
		});
	});

	$("#ea_type").click(function(){
		 //set_n_store_stock($('[name = ea_type]:checked').val());
	});

	$(document).ready(function() {
		memoPosition();
	});
	$(window).resize(function() {
		memoPosition();
	});
	function memoPosition() {
		var memo_list = $('.left_reg').offset().left;
		var memo_count = memo_list-46;
		$('.memo').css('left',memo_count);
		$('.box_memo').css('left',memo_list);
	}

	// 상태 로그 토글
	$(".showlog").click(function(){
		if($('.stat_log').css('display') == 'none') {
			$('.stat_log').show();
			$(this).html('변경 로그 닫기');
		} else {
			$('.stat_log').hide();
			$(this).html('변경 로그 보기');
		}
	});

    /**
     * 붙여넣기 및 드래그 방식으로 업로드
     **/
    function pasteUpload(item, image_group)
    {
        var blob = item.getAsFile();
        var reader = new FileReader();
        reader.onload = function(event) {
            var frame = document.getElementById(image_group);
            if (frame) {
                frame.contentWindow.frameUpload(null, event.target.result);
            }
        }
        reader.readAsDataURL(blob);
    }

	var content2;
	var content1;
	var inputchanged = 0;
	var m_content;

	$(document).ready(function() {
		<?php if (empty($_GET['pno'])){ ?>
        var upf1 = document.getElementById('up_fdisk');
        var upf2 = document.getElementById('up_wdisk');
        var has_wdisk = parseInt('<?=$wdisk[0]->img_limit[0]?>');

		content2 = seCall('content2', '', (upf2 && has_wdisk > 0) ? 'up_wdisk' : 'up_fdisk');
		<?php } ?>
		<?php if ($_use['content1_editor']=="Y"){ ?>
		content1 = seCall('content1');
		<?php } ?>

		$('#optFrame').attr('src', './?body=product@product_option_list.frm&stat=<?=$stat?>&pno=<?=$pno?>');
		$('#up_aimg').attr('src', './?body=product@product_file.frm&filetype=2&stat=<?=$stat?>&pno=<?=$pno?>');
		$('#up_aimg_wdisk').attr('src', './?body=product@product_file.frm&filetype=8&stat=<?=$stat?>&pno=<?=$pno?>');

		$('input, select').change(function() {
			inputchanged++;
		});

		window.onbeforeunload = function() {
			if(inputchanged > 0) {
				return "변경내용이 저장되지 않았습니다.\n\n변경사항을 취소하고 이페이지에서 나가시겠습니까?";
			}
		}

		useLimitSale($(':checked[name=ts_use]').val());
		$(':radio[name=ts_use]').click(function() {
			useLimitSale(this.value);
		});

		useSubscription($(':checked[name=sub_use]').val());
		$(':radio[name=sub_use]').click(function() {
			useSubscription(this.value);
		});

		// 인풋박스 하이라이트
		$('.tbl_row_reg, .tbl_col').find('.input, .txta').each(function() {
			var o = $(this);
			if(this.value) checkInputData(o);
			o.bind({
				'change' : function() {checkInputData(o)},
				'keyup' : function() {checkInputData(o)}
			});
		});
	});

	function checkInputData(o) {
		if(o.val()) o.addClass('input_data');
		else o.removeClass('input_data');
	}

	var product_sell_price_name='<?=$cfg['product_sell_price_name']?>';
	var product_normal_price_name='<?=$cfg['product_normal_price_name']?>';
	var pf = document.getElementById('prdFrm');

	// 회원별 주문한도
	var setMaxOrdMem = function() {
		if(!pf.max_ord_mem) return false;
		if(parseInt(pf.max_ord_mem.value) > 0) {
			pf.checkout.disabled = true;
			pf.checkout.checked = false;

            if (pf.use_talkpay) {
                pf.use_talkpay.disabled = true;
                pf.use_talkpay.checked = false;
            }
		} else {
			pf.checkout.disabled = false;

            if (pf.use_talkpay) {
                pf.use_talkpay.disabled = false;
            }
		}
	}
	$(pf.max_ord_mem).change(setMaxOrdMem);

	<?php if ($_use['content1_editor']=="Y"){ ?>
		var imgFr=2;
	<?php } else { ?>
		var imgFr=1;
	<?php } ?>

	$(window).resize(function() {
		$('iframe').width('100%');
	});

	window.onload=function (){
		checkThumb();
		chgEaType();
		chgOcate();
		setMaxOrdMem();

		for (i=3; i<=5; i++) {
			defaultContent(i);
		}

        $(':checkbox[name=use_talkpay]').change(function() {
            if (this.checked == true) {
                if($(':checked[name=delivery_type][value=product]').length > 0) {
                    $(':radio[name=delivery_type]').eq(0).prop('checked', true);
                }
                $('select[name=delivery_set], :radio[name=delivery_type][value=product]').prop('disabled', true);
            } else {
                $('select[name=delivery_set], :radio[name=delivery_type][value=product]').prop('disabled', false);
            }
        });
		setDiscount.setPrdType();
	}

	// 아이콘 등록창
	function mngIcon() {
		var f = document.getElementById('iconFrm');
		f.contentWindow.wisaOpen('./pop.php?body=product@product_icon_list','mngIcon','false,resizable=no');
	}

	// 상품상세 미리보기
	function detailPreview(mode, pno, content_no) {
		switch(content_no) {
			case 'm' :
				var content_id = 'm_content';
				var content_nm = '모바일 전용 상품상세설명';
			break;
			default :
				var content_id = 'content2';
				var content_nm = 'PC 상품상세설명';
		}
		if(mode == 1 && (typeof oEditors.getById == 'undefined' || !oEditors.getById[content_id])) {
			window.alert(content_nm+' 편집중이 아닙니다.\n상세설명 수정 버튼을 눌러 편집창을 열어주세요.');
			return false;
		}

		// 프리뷰 로드
		var page = (mode == 1) ? 'fullEditor' : 'preview';
		var param = '&content_id='+content_id;
		if(pno) param += '&pno='+pno;
		if(content_no) param += '&content_no='+content_no;

        var height = screen.availHeight+'px';
        var width = '1300px';
        if (mode == 1) {
            width = Math.floor(screen.availWidth*0.9)+'px';
        } else if (content_no == 'm') {
            height = '900px';
            width = '520px';
        }

        if (window.d_preview) {
            window.d_preview.close();
        }
		window.d_preview = window.open('./?body=product@product_'+page+'.frm'+param,'detailPreview','top=10px,left=10px,height='+height+'px,width='+width+'px,status=yes,resizable=yes,scrollbars=yes,toolbar=no,menubar=no');
	}

	function contentHeight(type){
		var amount = 100;
		var min = 50;
		var max = 4000;

		var frame = content2.R2Na_area;
		var height = frame.offsetHeight;

		height = type == '+' ? height + amount : height - amount;
		if(height > max) height = max;
		if(height < min) height = min;
		frame.style.height = height;
	}

	function contentHeightSave(w, msg){
		cookie_name='product_content_height';
		if(w.checked == true){
			content_height=R2Na_area.style.height.replace('px', '');
			setCookie(cookie_name, content_height, 365);
			if(!msg) alert('저장되었습니다  ');
		}else{
			setCookie(cookie_name, '', 365);
		}
	}

	function setPrdUpbt(cname) {
		var cookie = getCookie('mode_'+cname);
		var val = cookie ? '' : '1';
		setCookie('mode_'+cname, val, 365);

		document.getElementById(cname).contentWindow.document.location.reload();
		$('#btn_'+cname).val(val == 1 ? '멀티업로드로 변경' : '일반업로드로 변경');
	}

	// 관련상품
    var _setret = null;
	var psearch = new layerWindow('product@product_inc.exe&exec=refprd&exclude=<?=$pno?>');
	psearch.addRefPrd = function(refkey, save_type, prd_no) {
		$.post('?body=product@product_ref.exe',{"save_type":save_type, "pno":<?=$pno?>, "prd_no":prd_no, "refkey":refkey}, function(ret){
			if(refkey == '99') {
                if (ret.result == 'success') {
                    _setret = ret;

                    $('#refArea'+refkey).html(ret.html);
                    if (pf.prd_type.value == '4') {
                        pf.normal_prc.value = ret.normal_prc;
                        pf.sell_prc.value = ret.sell_prc;
                    }
                    pf.partner_no.value = ret.partner_no;

                    // 상품 검색 후 입점사 검색 적용
                    var partner_serach = $('#search').find('select[name=partner_no]');
                    if (partner_serach.length == 1) {
                        if (partner_serach.val() != ret.partner_no) {
                            partner_serach.val(ret.partner_no);
                            psearch.fsubmit(document.getElementById('prdIncFrm'));
                        }
                    }

                    $('#refArea'+refkey).sortable('refresh');
                } else {
                    window.alert(ret.message);
                }
			} else {
				$('#refArea'+refkey).html(ret);
			}
		});
	}

	function refPrdSort(refkey, n, s){
		$.post('?body=product@product_ref.exe',
			{
				"exec":"sort",
				"refkey":refkey,
				"opno":n,
				"updown":s,
				"pno":<?=$pno?>
			}, function(result) {
				if (refkey == '99') {
					$('#refArea'+refkey).html(result.html);
					pf.normal_prc.value = result.normal_prc;
					pf.sell_prc.value = result.sell_prc;
				} else {
					$('#refArea'+refkey).html(result);
				}
			}
		);
	}

	function delRefPrd(refkey, n){
		if (confirm('선택한 상품을 제외하시겠습니까?') == false) {
			return false;
		}
		$.post('?body=product@product_ref.exe',
			{
				"refkey":refkey,
				"pno":"<?=$pno?>",
				"del_no":n
			}, function(ret) {
				if (refkey == '99') {
                    _setret = ret;
					$('#refArea'+refkey).html(ret.html);
                    if (pf.prd_type.value != '6') {
    					pf.normal_prc.value = ret.normal_prc;
    					pf.sell_prc.value = ret.sell_prc;
                    }
                    if (ret.html == null) {
                        pf.partner_no.value = '';
                    }
				} else {
					$('#refArea'+refkey).html(ret);
				}
			}
		);
	}

	// 입점사 관련상품
	psearch.addHeadRef = function(refkey, save_type, prd_no) {
		var refcnt = $('#refArea'+refkey).size();
		if(refcnt>0) {
			var prdArray = [];
			$('#refArea'+refkey+' tbody tr').each(function() {
				var pno = $(this).data('idx');
				if(!pno) return;
				prdArray.push(pno);
			});
			prdArray.push(prd_no);
			$.post('?body=product@product_head_ref.exe',{"save_type":save_type, "refkey":refkey, "prdArray":prdArray}, function(result){
				$('#refArea'+refkey).html(result);
				$('#refhead_'+refkey).val(prdArray);
			});
		}
	}

	function refHeadSort(el, refkey, n){
		if(n=='up') {
			var $tr = $(el).parent().parent();
			$tr.prev().before($tr);
		}else {
			var $tr = $(el).parent().parent();
			$tr.next().after($tr);
		}
		var prdArray = [];
		$('#refArea'+refkey+' tbody tr').each(function() {
			var pno = $(this).data('idx');
			if(!pno) return;
			prdArray.push(pno);
		});
		$.post('?body=product@product_head_ref.exe',{"refkey":refkey, "prdArray":prdArray}, function(result){
			$('#refArea'+refkey).html(result);
			$('#refhead_'+refkey).val(prdArray);
		});
	}

	function delHeadRef(refkey, no){
		if(confirm('선택한 관련상품을 삭제하시겠습니까?') == false) {
			return false;
		}
		$('#refArea'+refkey).find('#headtr_'+no).remove();
		var prdArray = [];
		$('#refArea'+refkey+' tbody tr').each(function() {
			var pno = $(this).data('idx');
			if(!pno) return;
			prdArray.push(pno);
		});
		$.post('?body=product@product_head_ref.exe',{"refkey":refkey, "prdArray":prdArray}, function(result){
			$('#refArea'+refkey).html(result);
			$('#refhead_'+refkey).val(prdArray);
		});
	}

	// 사입처 리스트 실시간 호출
	function readSellers(val) {
		var obj = document.querySelector('select[name=seller_idx]');
		if(obj.className == 'ready') {
			$.post('?body=product@provider.exe', {"exec":"getAllSeller"}, function(result) {
				if(!val) val = obj.value;
				obj.className = 'OK';
				var a = $(obj).find('option:gt(1)').remove();
				$(obj).append(result);
				$(obj).val(val);
			});
		} else if(val) {
			$(obj).val(val);
		}
	}

	// FIXED 슬라이드 저장버튼
	function refineFastBtn() {
		$('#fastBtn').css('left', $('#contentArea').css('margin-left')).width($('#contentTop').innerWidth()+100);
	}

	function toggleFastBtn() {
		var doc = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement : document.body;
		var fastBtn = $('#fastBtn');

		if(fastBtn.css('opacity') == 1) fastBtn.css('opacity', '.8');

		if(doc.scrollTop > $('#reg_footer').offset().top-$(window).height()) {
			if(fastBtn.css('opacity') > 0) {
				fastBtn.animate({"opacity":"0"}, {"queue":false}).css('display','none');
			}
		} else {
			if(fastBtn.css('opacity') == 0) {
				fastBtn.animate({"opacity":".8"}, {"queue":false}).css('display','');
			}
		}
	}

	function useLimitSale(v) {
		if(!v) v = 'N';
		if(v == 'Y') $('.LimitSale').removeClass('hidden');
		else $('.LimitSale').addClass('hidden');
	}

    function useSubscription(v)
    {
		if(!v) v = 'N';
		if(v == 'Y') $('.tbl_subscription').removeClass('hidden');
		else $('.tbl_subscription').addClass('hidden');
    }

	function uploadPreview(e, n) {
        $('.upfile_preview'+n).addClass('hidden').attr('src', '');

        var limit_size = parseInt(e.target.getAttribute('data-limit'));
        var limit_str = e.target.getAttribute('data-limit-str');
        if (limit_size > 0 && e.target.files[0].size > limit_size) {
            window.alert('이미지는 '+limit_str+'까지 업로드하실 수 있습니다.');
            e.target.value = '';
            return false;
        }

		var reader = new FileReader();
		if(e.target.files.length > 0) {
			reader.onload = function(e) {
				$('.upfile_preview'+n).removeClass('hidden').attr('src', e.target.result);
			}
			reader.readAsDataURL(e.target.files[0]);
		}
	}

	function setComplexOption(ori_no, pno) {
		$.post('?body=product@product_erp_modify.exe', {'ori_no':ori_no, 'pno':pno}, function(r) {
			$('#partnerStockArea').html(r);
		});
	}

	function chgPartnerRate(obj) {
		var f = document.getElementById('prdFrm');
		$.get('?body=product@product_join_shop.exe', {'exec':'getRate', 'no':obj.value}, function(r) {
			var rates = $.parseJSON(r);
			$(f.partner_rate).find('option:gt(0)').remove();
			for(var key in rates) {
				$(f.partner_rate).append("<option value='"+rates[key]+"'>"+rates[key]+"</option>");
			}
		});
	}

	function chgFieldSet(set, pno) {
		$.post('./index.php?body=product@product_field_inc.exe', {'pno':pno, 'fieldset':set.value, 'from_ajax':'true'}, function(r) {
			$('#fieldset2').html(r);
		});
	}

	if($('#fastBtn').length > 0) {
		$(document).ready(function() {
			toggleFastBtn();
			refineFastBtn();

			$('#contentArea').change(refineFastBtn);
		});

		$(window).bind({
			"resize": refineFastBtn,
			"scroll": toggleFastBtn
		});
	}


	//입점파트너 레이어 추가
	var ptn_search = new layerWindow('product@product_join_shop.inc.exe');
	ptn_search.psel = function(no,stat) {
		if(stat == "신청") {
			alert("선택한 입점파트너는 ["+stat+"] 상태입니다.");
			return false;
		}
		document.prdFrm.partner_no.value = no;
		chgPartnerRate(document.prdFrm.partner_no);
		ptn_search.close();
	}

	function useMobileContent() {
		var param = {
			'body':'config@config.exe',
			'config_code':'prc_consultation',
			'use_m_content_product':'Y',
			'no_reload_config':true
		}
		if(confirm('모바일 전용 상세설명 사용중이 아닙니다.\n모바일 전용 상품상세설명 입력기능을 사용하시겠습니까?\n설정완료시 화면이 새로고침 됩니다.')) {
			$.post('./index.php', param, function(r) {
				location.reload();
			});
		}
	}

	function toggleMemoList(btn) {
		var cookie = getCookie('toggle_list_memo');
		if(cookie != 'Y') {
			$('#list_memo').slideDown('fast', function() {
				$(btn).addClass('block');
			});
			setCookie('toggle_list_memo', 'Y', 365);
		} else {
			$('#list_memo').slideUp('fast', function() {
				$(btn).removeClass('block');
			});
			setCookie('toggle_list_memo', '');
		}
	}

	// 적립금 자동 계산기
	function showMilageCal() {
		setDimmed();

		var f = document.querySelector('#prdFrm');
		var prc = f.sell_prc.value;
		$('.milage_cal').fadeIn('fast').find('._price').html(setComma(prc));

		$('#milage_cal_per').keyup(function(e) {
			milageCal(e);
			if(e.keyCode == 13) {
				milageSet();
			}
		}).val('').focus();

		$('#qdBackground').click(function() {
			hideMilageCal();
		});
	}

	function hideMilageCal() {
		removeDimmed();
		$('.milage_cal').hide();
	}

	function milageCal(e) {
		var f = document.querySelector('#prdFrm');
		var per = e.target.value.toNumber();
		var prc = f.sell_prc.value.replace(/,/gi, '').toNumber();
		var res = (prc*(per/100)).toFixed('<?=$cfg['r_currency_decimal']?>');
		window.calc_milage = res;

		$('#milage_cal_res').val(setComma(res));
	}

	function milageSet() {
		var f = document.querySelector('#prdFrm');
		f.milage.value = setComma(window.calc_milage);
		window.calc_milage = null;
		hideMilageCal();
	}

	function productcopy() {
		var name = $("input[name=name]").attr("value");
		if(!confirm(name+' 상품을 복사하시겠습니까?')) {
			return false;
		}
        printLoading();

        $.post('?body=product@product_update.exe',
			{
				"exec":"fullcopy",
				"imgcopy":"Y",
				"detailcopy":"Y",
				"nums":<?=$pno?>,
			}, function(result) {
				if(confirm("복사된 상품으로 이동하시겠습니까?")) {
					window.location.href='./?body=product@product_register&pno='+result;
				}
                removeLoading();
			}
		);
	}

	function setDeliverySet(o) {
		var f = o.form;
		if(o.value && f.checkout && $(':checked[name=delivery_type]').val() == 'product') {
            if (f.checkout) {
    			f.checkout.disabled = true;
            }
			if (f.use_talkpay) {
                f.use_talkpay.disabled = true;
            }
		} else {
            if (f.checkout) {
    			f.checkout.disabled = false;
            }
			if (f.use_talkpay) {
                f.use_talkpay.disabled = false;
            }
		}
	}

	function setNstoreStock(val) {
		if($("#n_qty")) {
			if(val == 1) {
				$("#n_qty").removeClass('hidden');
				$("#n_qty_msg").addClass('hidden');
			} else {
				$("#n_qty").val('999999999');
				$("#n_qty").addClass('hidden');
				$("#n_qty_msg").removeClass('hidden');
			}
		}
	}

	// 수량할인 가격 입력
	var setDiscount = new layerWindow('product@discount_inc.exe&pno=<?=$pno?>');

	setDiscount.add = function() {
		var data = $('.discountLine>tr').eq(0).clone();
		data.find('input[type=text]').val('');
		data.find('[name="sale_type[]"]').val($('.discountLine>tr').find('[name="sale_type[]"]').val());

		$('#popupContent').height('auto');
		$('.discountLine').append(data);

		this.attachEvent();
	}

	setDiscount.remove = function(b) {
		if($('.discountLine>tr').length == 1) {
			$('.discountLine>tr').find(':text').val('');
			//window.alert('더 이상 삭제할 수 없습니다.');
			return false;
		}
		$('#popupContent').height('auto');
		$(b).parents('tr').remove();
	}

	setDiscount.submit = function() {
		$.post('./index.php', $('#setDiscountFrm').serialize(), function(r) {
			if(r.count > 0) {
				$('.'+r.field+'_btn').val('할인 수정');
			} else {
				$('.'+r.field+'_btn').val('할인 등록');
			}
			if(r.status == 'success') {
				setDiscount.close();
			} else {
				window.alert(r.message);
			}
		});
	}

	setDiscount.openEvent = function() {
		this.attachEvent();
	}

	setDiscount.attachEvent = function() {
		var sel = $('.discountLine').find('[name="sale_type[]"]');
		sel.change(function() {
			sel.val(this.value);
		});
	};

	setDiscount.setPrdType = function() {
        $('.set_method3').hide(); // 세트할인
        $('.set_method4').hide(); // 수량별 단계 할인
        $('.set_method5').hide(); // 세트할인 + 수량별 단계 할인
        $('.set_method6').hide(); // 골라담기

		if($(':checked[name=prd_type]').val() == '4') {
			$('.set_method3').show(); // 세트할인
			$('.set_method5').show(); // 세트할인 + 수량별 단계 할인
            $('input[name=set_pick_qty]').prop('disabled', true);

            $(pf.normal_prc).addClass('input_disabled').prop('readOnly', true);
            $(pf.sell_prc).addClass('input_disabled').prop('readOnly', true);

            if (_setret) {
                pf.normal_prc.value = _setret.normal_prc;
                pf.sell_prc.value = _setret.sell_prc;
            }
		} else if ($(':checked[name=prd_type]').val() == '5') { // 세트 수량별 단계 할인 (차후 이용)
			$('.set_method4').show();
			$('.set_method5').show();
            $('input[name=set_pick_qty]').prop('disabled', true);

            $(pf.normal_prc).removeClass('input_disabled').prop('readOnly', false);
            $(pf.sell_prc).removeClass('input_disabled').prop('readOnly', false);
		} else {
			$('.set_method6').show();

            $(pf.normal_prc).removeClass('input_disabled').prop('readOnly', false);
            $(pf.sell_prc).removeClass('input_disabled').prop('readOnly', false);
            $('input[name=set_pick_qty]').prop('disabled', false);
        }
	}

	$(':radio[name=prd_type]').click(function() {
		setDiscount.setPrdType();
	});
    setDiscount.setPrdType();

    // 세트 하위상품 별도 판매 불가 체크
    function setPerms(o)
    {
        var pno = $(o).data('pno');
        var checked = o.checked;

        <?php if ($admin['level'] == '4' && $scfg->comp('partner_prd_accept', 'N') == false) { ?>
        window.alert('권한이 없습니다.');
        return false;
        <?php } ?>

        $.post('./index.php', {'body': 'product@product_ref.exe', 'exec':'checkPerm', 'pno': <?=$pno?>, 'refpno': pno}, function(r) {
            if (checked == true || (checked == false && (r.count == 0 || confirm('다른 세트상품에 포함된 상품입니다.\n별도 판매 불가 설정을 모두 해제하시겠습니까?') == true))) {
                $.post('./index.php', {'body': 'product@product_ref.exe', 'exec':'setPerm', 'pno': pno, 'checked':checked});
            } else {
                o.checked = true;
            }
        });
    }

    // 도서 정보
    function isBook()
    {
        var is_book = $('form[name=prdFrm] [name=is_book]').val();
        if (is_book == 'N') {
            $('.book_info').hide();
        } else {
            $('.book_info').show();
        }
    }
    isBook();

	// 타임세일
	(setTsSet = function() {
		if($(':checked[name=use_ts_set]').val() == 'Y') {
			$('.timesale_N').hide();
			$('.timesale_Y').show();
		} else {
			$('.timesale_Y').hide();
			$('.timesale_N').show();
		}
	})();
	$(':radio[name=use_ts_set]').change(setTsSet);

    (tsUnlimited = function() {
        var checked = $('.ts_unlimited').prop('checked');
        var sel = $('select[name=ts_timee], select[name=ts_mine]');
        var date = $('input[name=ts_datee]');
        if (checked == true) {
            date.datepicker('option', 'disabled', true);
            sel.prop('disabled', true);

            date.css('background', '#f2f2f2');
            sel.css('background', '#f2f2f2');
        } else {
            date.datepicker('option', 'disabled', false);
            sel.prop('disabled', false);

            date.css('background', '');
            sel.css('background', '');
        }
    })();
    $('.ts_unlimited').on('click', function() {
        tsUnlimited();
    });
</script>