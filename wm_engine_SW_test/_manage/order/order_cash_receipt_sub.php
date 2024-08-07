<div class="box_title first">
	<h2 class="title">현금 영수증 개별 발급</h2>
</div>
<?PHP
	include_once $engine_dir."/_engine/include/shop.lib.php";
	if($cfg[cash_receipt_use]!="Y") {
	?>
	<div class="box_full">
		현재 현금영수증 발급신청기능이 설정되어 있지 않습니다.
		<span class="box_btn blue"><a href="./?body=config@cash_receipt" target="_blank">설정변경하기</a></span>
	</div>
	<?
		return;
	}

	if($cfg[cash_r_pg] != "dacom"){
	?>
	<div class="box_full">
		현재 현금영수증 발급신청기능 서비스가 신청되어 있지 않습니다. 가맹점 등록 및 등록 확인을 해주시기 바랍니다.
		<span class="box_btn blue"><a href="./?body=config@cash_receipt" target="_blank">신청하기</a></span>
	</div>
	<?
		return;
	}

	$ono = addslashes($_GET['ono']);
	if($ono) {
        $ord = $pdo->assoc("SELECT od.ono, od.title, od.pay_prc, od.buyer_name, od.buyer_cell, od.buyer_email, od.milage_prc, od.emoney_prc,
                                od.pay_type, od.dlv_prc
                                FROM " . $tbl['order'] . " AS od  
                                WHERE od.ono = '" . $ono . "'");
        $ord = array_map('stripslashes', $ord);
        //배송비
        $dlv_prc = $ord['dlv_prc'];
        //예치금+적립금 사용액
        $point_prc = $ord['milage_prc'] + $ord['emoney_prc'];

        //사용중인 할인 필드 체크
        $sales_fields_sql = getOrderSalesField('op', ' + ');

        //주문금액중 면/과세 금액 취합
        $sql = "SELECT 
                SUM(IF(prd.tax_free = 'Y', op.total_prc - (".$sales_fields_sql."), 0)) AS tot_taxfree,
                SUM(IF(prd.tax_free != 'Y', op.total_prc - (".$sales_fields_sql."), 0)) AS tot_tax                        
                FROM 
                ".$tbl['order_product']." op
                JOIN ".$tbl['product']." prd ON op.pno = prd.no            
                WHERE
                op.ono = '".$ono."'";
        $tax_data = $pdo->assoc($sql);

        $tax_data['tot_tax'] += $dlv_prc; //배송비는 과세에 추가

        $diff = $tax_data['tot_tax'] - $point_prc; //과세액에 예+적립금 우선 차감 적용
        if ($diff < 0) {
            //차액이 음수인경우 (차감할금액이 남은경우)
            $tax_data['tot_tax'] = 0; //과세액은 0원으로 처리
            $tax_data['tot_taxfree'] -= abs($diff); //면세액에서 추가 차감 (음수이므로 절대값 적용)
        } else {
            $tax_data['tot_tax'] -= $point_prc; //과세액만 차감
        }
    }
?>
<form id="receiptFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return cashChk(this);">
	<input type="hidden" name="body" value="order@order_cash_receipt_sub.exe">
    <input type="hidden" name="pay_type" value="<?=$ord['pay_type']?>">
	<table class="tbl_row">
		<caption class="hidden">현금영수증 개별 발급</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><strong>주문 번호</strong></th>
			<td>
				<input type="text" name="ono" class="input" maxlength="14" size="30" onkeyup="checkONO(this)" onpaste="checkONO(this)" value="<?=$ord['ono']?>">
				<span class="box_btn_s blue"><input type="button" value="주문번호 검색" onclick="osearch.open();"></span>
				<span id="vorder" class="box_btn_s blue"><input type="button" value="주문서 상세보기" onclick='viewOrder($("[name=ono]").val())'></span>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>주문 상품명</strong></th>
			<td>
				<input type="text" name="prod_name" class="input" maxlength="50" size="30" value="<?=inputText($ord['title'])?>" >
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>발급 금액</strong></th>
			<td>
                <input type="hidden" name="amt1" value="<?=$ord['pay_prc']?>">
				<input type="text" name="amt1_display" class="input" disabled value="<?=$ord['pay_prc']?>">
				<span class="explain">현금영수증 발급 금액은 [과세 금액 + 면세 금액]으로 계산됩니다.</span>
			</td>
		</tr>
        <tr>
            <th scope="row"><strong>과세 금액</strong></th>
            <td>
                <input type="text" name="tax_amount" class="input" value="<?=$tax_data['tot_tax']?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><strong>면세 금액</strong></th>
            <td>
                <input type="text" name="taxfree_amount" class="input" value="<?=$tax_data['tot_taxfree']?>">
            </td>
        </tr>
		<tr>
			<th scope="row"><strong>발급번호</strong></th>
			<td>
				<input type="text" name="cash_reg_num" class="input" maxlength="20" size="30" value="<?=$ord['buyer_cell']?>">
				<span class="explain">주민등록번호/현금영수증카드번호/휴대폰번호/사업자번호</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>주문자명</strong></th>
			<td>
				<input type="text" name="cons_name" class="input" maxlength="20" size="30" value="<?=$ord['buyer_name']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">주문자 이메일</th>
			<td>
				<input type="text" name="cons_email" class="input" maxlength="50" size="30" value="<?=$ord['buyer_email']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">주문자 휴대전화</th>
			<td>
				<input type="text" name="cons_tel" class="input" maxlength="15" size="30" value="<?=$ord['buyer_cell']?>">
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<ul class="list_msg left">
			<li>입력하시는 정보는 국세청에 통보되므로 정확하게 입력하여 주시기 바랍니다.</li>
			<li>해당 기능은 실제로 발급이 이루어지지 않습니다.</li>
			<li>'개별 현금영수증 등록' 버튼을 누르시면 <u><a href="./?body=order@order_cash_receipt" target="_blank">현금영수증 관리</a> 페이지를 통해 발급이 가능한 [신청] 상태</u>로 입력이 됩니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="개별 현금영수증 등록"></span>
	</div>
</form>

<script type="text/javascript">
	function cashChk(f){
		if(!checkBlank(f.cons_name, '주문자명을 입력해주세요.')) return false;
		if(!checkBlank(f.ono, '주문 번호를 입력해주세요.')) return false;
		if(!checkBlank(f.prod_name, '주문 상품명을 입력해주세요.')) return false;
		if(!checkBlank(f.amt1, '발급금액을 입력해주세요.')) return false;
		if(!checkBlank(f.cash_reg_num, '발급번호를 입력해주세요.')) return false;
        if ((parseInt(f.tax_amount.value) + parseInt(f.taxfree_amount.value)) != f.amt1.value) {
            alert('면세금액과 과세금액의 합이 발급금액과 다릅니다.');
            return false;
        }
		return true;
	}

	var f = document.getElementById('receiptFrm');
	var osearch = new layerWindow('order@order_inc.exe');

	function checkONO(o) {
		setTimeout(function() {
			if(o.value.length == 14) {
				$.post('?body=order@order_cash_receipt_sub.exe', {'exec':'checkONO', 'ono':o.value}, function(r) {
					if(r == 'OK') $('#vorder').removeClass('hidden');
					else $('#vorder').addClass('hidden');
				});
			} else {
				$('#vorder').addClass('hidden');
			}
		}, 100);
	}

	checkONO(f.ono);

    /**
     * 과/면세 금액 변경시 발급금액 계산
     */
    function sumAmtPrice() {
        const receiptFrm = $('form#receiptFrm'); //발금폼
        const tax_input = $('[name=tax_amount]', receiptFrm); //과세금액 input
        const taxfree_input = $('[name=taxfree_amount]', receiptFrm); //면세금액 input
        const amt1 = $('[name=amt1]', receiptFrm); //발급금액 input
        const amt1_display = $('[name=amt1_display]', receiptFrm); //발급금액 표시 영역
        tax_input.add(taxfree_input).on('keyup', function() {
            //과세, 면세금액 키입력 이벤트 처리
            const tax_price = parseInt(tax_input.val()) || 0; //미입력시 0으로 대체
            const taxfree_price = parseInt(taxfree_input.val()) || 0; //미입력시 0으로 대체
            amt1.val( tax_price + taxfree_price );
            amt1_display.val( tax_price + taxfree_price );
        });
    }

    $(document).ready(function() {
        sumAmtPrice();
    });
</script>