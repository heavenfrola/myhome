<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문영수증 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	common_header();

	$cate = numberOnly($_GET['cate']);

	$array = array();
	$bank = array();

	if($cate != 1) {
		$ono = addslashes($_GET['ono']);
		$res = $pdo->iterator("select *, a.total_prc as ord_prc, a.dlv_prc from $tbl[order] a inner join $tbl[order_product] b using(ono) where ono='$ono' and b.stat < 6");
        foreach ($res as $cart) {
			$cart['sum_sell_prc'] = $cart['total_prc'];
			$cart['prd_prc_str'] = number_format($cart['sell_prc']);
			$cart['code'] = $pdo->row("select code from $tbl[product] where no='$cart[pno]'");
			if(!$cart['code']) $cart['code'] = $cart['pno'];
			$array[] = $cart;

			if(!$total_price) {
				$total_price = $cart['pay_prc'];
				$milage_prc = $cart['milage_prc'];
				$emoney_prc = $cart['emoney_prc'];
				$pay_type = $cart['pay_type'];
				$date1 = $cart['date1'];
				$buyer_name = stripslashes($cart['buyer_name']);
				$bank[] = array('bank'=>stripslashes($cart['bank']), 'bank_name'=>stripslashes($cart['bank_name']));
				$y = date('Y', $cart['date1']);
				$m = date('m', $cart['date1']);
				$d = date('d', $cart['date1']);
				if($pay_type == 2 || $cart['pay_type'] == 4) $cash_prc = $cart['pay_prc'];
				if($pay_type == 1 || $cart['pay_type'] == 5 || $cart['pay_type'] == 7) $card_prc = $cart['pay_prc'];
			}

			$dlv_prc = $cart['dlv_prc'];
		}
	}

	switch($cate) {
		case '1' :
			$receipt_title = '주문 계산서';
			$receipt_subtitle = '공급받는자 보관용';
			$checketype2 = 'checktype';
			$bank_section = '입금계좌 안내';
			$receipt_type = '청구';

			include_once $engine_dir.'/_engine/include/shop2.lib.php';
			$ptnOrd = new OrderCart();
			while($cart = cartList()) {
				$cart['code'] = $pdo->row("select code from $tbl[product] where no='$cart[pno]'");
                $cart['prd_prc_str'] = parsePrice($cart['sell_prc'], true);
				if(!$cart['code']) $cart['code'] = $cart['pno'];
				$array[] = $cart;

				$ptnOrd->addCart($cart);
			}
			$ptnOrd->complete();

			$y = date('Y');
			$m = date('m');
			$d = date('d');
			$total_price = $ptnOrd->getData('pay_prc');
			$date1 = time();
			$dlv_prc = $ptnOrd->getData('dlv_prc');
			$res = $pdo->iterator("select * from $tbl[bank_account] where type=1");
            foreach ($res as $data) {
				$bank[] = array('bank'=>$data['bank'].' '.$data['account'].' '.$data['owner']);
			}
		break;
		case '2' :
			if(!$admin['level']) msg('접근 권한이 없습니다.');
			echo "<style type='text/css'>* { color: red !important; border-color: red !important;}</style>";
			$receipt_title = '주문 영수증';
			$receipt_subtitle = '공급자 보관용';
			$checketype1 = 'checktype';
			$bank_section = '무통장 입금계좌';
			$receipt_type = '영수';
		break;
		case '3' :
			$receipt_title = '주문 영수증';
			$receipt_subtitle = '공급받는자 보관용';
			$checketype1 = 'checktype';
			$bank_section = '무통장 입금계좌';
			$receipt_type = '영수';
			break;
		break;
	}

	if($dlv_prc > 0) { // 배송비항목
		$array[] = array(
			'name' => '배송비',
			'buy_ea' => '1',
			'sum_sell_prc' => $dlv_prc,
			'prd_prc_str' => number_format($dlv_prc),
		);
	}

	$price = round($total_price / 1.1);
	$vat = $total_price - $price;
	$blank = (11-strlen($price));
	$rprice = addZero($price, 11);
	$rvat = addZero($vat, 10);

	function parse() {
		global $array;
		$data = current($array);
		if(!$data) return false;
		if(!$data['date1']) $data['date1'] = time();

		$data['y'] = date('Y', $data['date1']);
		$data['m'] = date('m', $data['date1']);
		$data['d'] = date('d', $data['date1']);

		$data['price'] = number_format(floor(($data['sum_sell_prc']/11)*10));
		$data['name'] = cutstr(strip_tags($data['name']), 40);

        next($array);
		return $data;
	}

	function parseBank() {
		global $bank;

		$data = current($bank);
		if(!$data) return false;

        next($bank);
		return $data;
	}

	$cfg['use_channel_plugin'] = 'N';
	$cfg['use_easemob_plugin'] = 'N';

?>
<link rel="stylesheet" type="text/css" href="<?=$root_url?>/_manage/?body=css@manage.css&script=index.php&mdir=main&mfile=main&dm=">
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/mypage/receipt.css">

<div class='receipt_container'>
	<div id='receipt_btn' style='margin: 5px;'>
		<span class="box_btn blue"><input type="button" value="인쇄" onclick='printReceipt(<?=$cate?>)' /></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick='self.close()' /></span>
	</div>
	<div class='right'><?=$root_url?></div>
	<div class='receipt'>
		<!-- Block1 : 타이틀 -->
		<table>
			<col /><col width='60px' /><col width='160px' />
			<tr>
				<td class='center' rowspan='2' style='height: 40px;'><h1><?=$receipt_title?></h1> (<?=$receipt_subtitle?>)</td>
				<td style='border: 0'><?=$receipt_type?>일시</td>
				<td style="text-align:right"><?=date('Y년 m월 d일', $date1)?></td>
			</tr>
			<tr>
				<td style='border-left: 0'>주문번호</td>
				<td colspan='2' class='border right'><?=$ono?></td>
			</tr>
		</table>
		<!-- Block 2 : 공급자/공급받는자 -->
		<table>
			<col width='20px' /><col width='60px' /><col width='100px' /><col width='60px' /><col width='100px' />
			<col width='20px' /><col width='60px' /><col />
			<tr>
				<th rowspan='4'>공급자</th>
				<th>등록번호</th>
				<td colspan='3'><?=$cfg['company_biz_num']?></td>
				<th rowspan='4'>공급받는자</th>
				<th>성명</th>
				<?if($cate == 1) {?>
				<td><input type="text" id='buyer_name' size='15' class="input" /> 님</td>
				<?} else {?>
				<td><?=$buyer_name?> 님</td>
				<?}?>

			</tr>
			<tr>
				<th class='align_2'>상<span class='spacing2'></span>호<br /><span class='verysmall'>(법인명)</span></th>
				<td><?=$cfg['company_mall_name']?></td>
				<th>성명<br /><span class='verysmall'>(대표자)</span></th>
				<td><?=$cfg['company_owner']?></td>
				<td rowspan='3' colspan='2'></td>
			</tr>
			<tr>
				<th class='letter5'>사업장<br />소재지</th>
				<td colspan='3'><?=$cfg['company_addr1']?> <?=$cfg['company_addr2']?></td>
			</tr>
			<tr>
				<th class='align_2'>업<span class='spacing2'></span>태</th>
				<td><?=$cfg['company_biz_type1']?></td>
				<th>종목</th>
				<td><?=$cfg['company_biz_type2']?></td>
			</tr>
		</table>

		<!--
		<table>
			<col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' />
			<col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' />
			<col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col width='20px' /><col />

			<tr>
				<th colspan='4' class='center'>작성</th>
				<th colspan='12' class='center'>공급가액</th>
				<th colspan='10' class='center'>세액</th>
				<th>비고</th>
			</tr>
			<tr>
				<td colspan='2'>년</td>
				<td>월</td>
				<td>일</td>
				<td class='ultrasmall'>공란수</td>
				<td>백</td>
				<td>십</td>
				<td>억</td>
				<td>천</td>
				<td>백</td>
				<td>십</td>
				<td>만</td>
				<td>천</td>
				<td>백</td>
				<td>십</td>
				<td>일</td>
				<td>십</td>
				<td>억</td>
				<td>천</td>
				<td>백</td>
				<td>십</td>
				<td>만</td>
				<td>천</td>
				<td>백</td>
				<td>십</td>
				<td>일</td>
				<td rowspan='2'></td>
			</tr>
			<tr>
				<td colspan='2'><span><?=$y?></span></td>
				<td><span><?=$m?></span></td>
				<td><span><?=$d?></span></td>
				<td><span><?=$blank?></span></td>
				<?for($i=0; $i < strlen($rprice); $i++) {?>
				<td><span><?=$rprice[$i]?></span></td>
				<?}?>
				<?for($i=0; $i < strlen($rvat); $i++) {?>
				<td><span><?=$rvat[$i]?></span></td>
				<?}?>
			</tr>
		</table>
		-->

		<!-- BLOCK 4 : 개별 품목 -->
		<table>
			<col width='40px' /><col width='40px' /><col width='160px' /><col width='80px' /><col width='40px' /><col width='80px' /><col width='80px' />
			<tr>
				<th>월</th>
				<th>일</th>
				<th class='center'>품목</th>
				<th class='center'>상품코드</th>
				<th class='center'>수량</th>
				<th class='center'>단가</th>
				<th class='center'>공급가액</th>
				<th class='center'>비고</th>
			</tr>
			<?while($data = parse()){?>
			<tr>
				<td class='center'><span><?=$data['m']?></span></td>
				<td class='center'><span><?=$data['d']?></span></td>
				<td><span><?=$data['name']?></span></td>
				<td class='center'><span><?=$data['code']?></span></td>
				<td class='center'><span><?=$data['buy_ea']?></span></td>
				<td style="text-align:right"><span><?=$data['prd_prc_str']?></span></td>
				<td style="text-align:right"><span><?=$data['price']?></span></td>
				<td class='center'>&nbsp;</td>
			</tr>
			<?}?>
		</table>

		<!-- BLOCK 5 : 합계처리 -->
		<table>
			<col width='100px' /><col width='100px' /><col width='100px' /><col width='100px' /><col width='100px' /><col width='60px' /><col />
			<?if($pay_type){?>
			<tr>
				<th class='center'>결제방법</th>
				<td colspan='7' class='left'><?=$_pay_type[$pay_type]?></td>
			</tr>
			<?}?>
			<tr>
				<th class='center'>합계금액</th>
				<th class='center'>현금</th>
				<th class='center'>카드/이체</th>
				<th class='center'>적립금</th>
				<th class='center'>예치금</th>
				<td rowspan='2'>위 금액을</td>
				<td class='<?=$checketype1?>' style='border: 0;'>영수</td>
				<td style='border-left: 0' rowspan='2'>함</td>
			</tr>
			<tr>
				<td style="text-align:right"><span><?=number_format($total_price)?></span></td>
				<td><?=number_format($cash_prc)?></td>
				<td><?=number_format($card_prc)?></td>
				<td><?=number_format($milage_prc)?></td>
				<td><?=number_format($emoney_prc)?></td>
				<td class='<?=$checketype2?>' style='border-left: 0'>청구</td>
			</tr>
		</table>

		<?if($cate == 1 || $pay_type == 2 || $pay_type == 4) {?>
		<table>
			<col width='100px' /><col width='300px' />
			<tr>
				<th rowspan='<?=1+count($bank)?>'><?=$bank_section?></th>
				<th>계좌</th>
				<th>입금자명</th>
			</tr>
			<?while($data = parseBank()) {?>
			<tr>
				<td><?=$data['bank']?></td>
				<td class='left'><?=$data['bank_name']?></td>
			</tr>
			<?}?>
		</table>
		<?}?>
	</div>

	<div class='left' style='margin: 5px 0;'>
		<?=nl2br(trim($pdo->row("select value from $tbl[default] where code='receipt_footer'")))?>
	</div>
</div>
<script type="text/javascript">
window.onload = function() {
	var w = $(window);
	window.resizeBy($('.receipt_container').width()-w.width()+20, $('.receipt_container').height()-w.height()+20);
}

function printReceipt(cate) {
	if(cate == 1 && !$('#buyer_name').val()) {
		window.alert('공급받는분 성명을 입력해 주세요.');
		$('#buyer_name').focus();
		return false;
	}
	$('#receipt_btn').hide();
	window.print();
	$('#receipt_btn').show();
}
</script>
<?close(1);?>