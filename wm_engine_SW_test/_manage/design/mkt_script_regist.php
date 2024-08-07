<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스크립트 매니저
	' +----------------------------------------------------------------------------------------------+*/

	$page_list = array(
		'scr_header' => array(
			'공통헤더',
			'{회원아이다} {회원나이} {성별(M/F)} {성별(남/여)} {성별(1/2)}'
		),
		'scr_top' => array(
			'공통상단',
			'{회원아이다} {회원나이} {성별(M/F)} {성별(남/여)} {성별(1/2)}'
		),
		'scr_bottom' => array(
			'공통하단',
			'{회원아이다} {회원나이} {성별(M/F)} {성별(남/여)} {성별(1/2)}',
		),
		'scr_join' => array(
			'회원가입 완료',
			'{회원아이다} {회원나이} {성별(M/F)} {성별(남/여)} {성별(1/2)}',
		),
		'scr_detail' => array(
			'상품상세',
			'{상품코드} {시스템코드} {상품명} {상품가격} {카테고리명} {상품이미지} {상품일련번호}',
		),
		'scr_cart' => array(
			'장바구니',
			'{상품금액} {총주문금액} {배송비}',
		),
		'scr_cartlist' => array(
			'장바구니 상품반복',
			'{상품명} {상품코드} {시스템코드} {상품금액} {총상품금액} {주문수량}',
		),
		'scr_order' => array(
			'주문완료',
			'{상품금액} {총주문금액} {배송비} {주문상품수량} {주문번호}',
		),
		'scr_orderlist' => array(
			'주문완료 상품반복',
			'{상품명} {상품코드} {시스템코드} {상품금액} {총주문금액} {주문수량}',
		),
	);

	if(count($_POST) > 0) return;

	$no = numberOnly($_GET['no']);
	if($no) {
		$data = $pdo->assoc("select * from {$tbl['mkt_script']} where `no`='$no'");
		if(!$no) msg('존재하지 않는 스크립트입니다.', 'back');
		$data['reg_date'] = date('Y-m-d', $data['reg_date']);
	} else {
		$data['use_yn'] = 'N';
	}

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return scriptChk(this)">
	<input type="hidden" name="body" value="design@mkt_script.exe">
	<input type="hidden" name="no" value="<?=$no?>">
		<div class="box_title first">
		<h2 class="title">스크립트 기본정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">스크립트 기본정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>제목</strong></th>
			<td><input type="text" name="name" value="<?=$data['name']?>" class="input" style="width:500px;" maxlength="50"></td>
		</tr>
		<tr id="target_type">
			<th scope="row"><strong>사용여부</strong></th>
			<td>
				<label><input type="radio" name="use_yn" value="Y" <?=checked($data['use_yn'], 'Y')?>>사용함</label>
				<label><input type="radio" name="use_yn" value="N" <?=checked($data['use_yn'], 'N')?>>사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">담당자 및 연락처</th>
			<td><input type="text" name="info" value="<?=$data['info']?>" class="input" style="width:500px;" maxlength="50"></td>
		</tr>
		<tr>
			<th scope="row">메모</th>
			<td><textarea name="memo" class="txta"><?=$data['memo']?></textarea></td>
		</tr>
		<?php if ($no > 0) { ?>
		<tr>
			<th scope="row">등록 일시</th>
			<td><?=$data['reg_date']?></td>
		</tr>
		<?php } ?>
	</table>

	<div class="box_title">
		<h2 class="title">스크립트 상세내용</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">스크립트 상세내용</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">삽입 위치</th>
			<td>
				<?php
				foreach($page_list as $_page => $_data) {
					$tag_exists = ($data[$_page] || $data[$_page.'_pc'] || $data[$_page.'_mb']) ? 'p_color' : '';
				?>
				<label class="<?=$tag_exists?>"><input type="radio" name="target" onclick="contentview('<?=$_page?>')"><?=$_data[0]?></label>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row">내용</th>
			<td>
				<div class="box_tab" style="margin-top: 0;">
					<ul>
						<li><a href="#" class="common" onclick="chgDevice('common'); return false;">공통</a></li>
						<li><a href="#" class="pc" onclick="chgDevice('pc'); return false;">PC</a></li>
						<li><a href="#" class="mobile" onclick="chgDevice('mobile'); return false;">Mobile</a></li>
					</ul>
				</div>

				<?php foreach ($page_list as $_page => $_data) { ?>
				<div class="strre <?=$_page?>" style="display:none;">
					<div class="box_middle3 left">
						<ul class="list_info">
							<li><?=$_data[1]?></li>
						</ul>
					</div>
					<textarea name="<?=$_page?>" rows="30" class="txta common" style="height:500px;"><?=$data[$_page]?></textarea>
					<textarea name="<?=$_page?>_pc" rows="30" class="txta pc hidden" style="height:500px;"><?=$data[$_page.'_pc']?></textarea>
					<textarea name="<?=$_page?>_mb" rows="30" class="txta mobile hidden" style="height:500px;"><?=$data[$_page.'_mb']?></textarea>
				</div>
				<?php } ?>

				<ul class="list_info">
					<li>치환문자를 삽입하면 각 상황별 문자로 치환됩니다.</li>
					<li>script 태그를 포함한 모든 내용을 입력해주세요.</li>
					<li>입력한 내용에 오류가 있는 경우 정상적으로 주문이 이루어지지 않을 수 있습니다. 입력 후 반드시 테스트를 진행해주세요.</li>
					<li class="warning">잘못된 입력으로 문제가 발생할 경우 모든 책임은 쇼핑몰에 있습니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="취소" onclick="history.back();"></span>
	</div>
	</table>
</form>

<script type="text/javascript">
	function scriptChk(f){
		if(!checkBlank(f.name, '광고명을 입력해주세요.')) return false;
	}

	function contentview(val){
		var target = $('.strre.'+val);
		$('.strre').not(target).hide();
		chgDevice('common');
		target.show();

		window.currentTarget = val;
	}

	function chgDevice(device) {
		var target = $('.strre.'+window.currentTarget);
		target.find('.txta').addClass('hidden');
		target.find('.txta.'+device).removeClass('hidden');

		$('.box_tab a').removeClass('active');
		$('.box_tab .'+device).addClass('active');
	}

	$(function() {
		$(':radio[name=target]').eq(0).prop('checked', true);
		contentview('scr_header');
	});
</script>