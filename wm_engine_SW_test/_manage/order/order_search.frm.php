<?PHP

	function wCk($type = 'radio', $arr, $name = '', $ck = '', $none = false) {
		if($type == 'radio' && $none == true) {
			$checked = (!$ck) ? 'checked' : '';
			$r .= "<label><input type=\"$type\" name=\"$name\" value=\"\" $checked> 전체</label>";
		}

		foreach($arr as $key => $val) {
			$r .= "<label><input type=\"$type\" name=\"$name".(($type == 'checkbox') ? '[]' : '')."\" value=\"$key\"";
			if($type == "checkbox") {
				if(strchr($ck, "@".$key."@")) $r .= ' checked';
			} else if($type == 'radio') {
				if($ck == $key) $r .= ' checked';
			}
			$r .= "> $val</label>";
		}
		return $r;
	}

	$_period_fd=array(1=>"주문일", 2=>"입금일", 4=>"배송일", 5=>"배송완료일");
	$_orderby_fd=array(1=>"주문일역순", 2=>"주문일순", 3=>"입금일역순", 4=>"입금일순", 5=>"상품준비일역순", 6=>"상품준비일순", 7=>"배송시작일역순", 8=>"배송시작일순", 9=>"배송완료일역순", 10=>"배송완료일순");

	$_search_fd = array();
	$_search_fd['ono'] = '주문번호';
	if($cfg['checkout_id']) {
		$_search_fd['checkout_ono'] = '네이버페이 상품주문번호';
	}
	if($cfg['use_kakaoTalkStore'] == 'Y') {
		$_search_fd['talkstore_ono'] = '톡스토어 주문번호';
	}
	$_search_fd['member_id'] = '회원아이디';
	$_search_fd['name'] = '주문자/입금자/수령인';
	$_search_fd['phone'] = '주문자 연락처';
	$_search_fd['buyer_email'] = '주문자 이메일';
	$_search_fd['dlv_code'] = '송장번호';
	$_search_fd['pname'] = '상품명';
	$_search_fd['addressee_addr'] = '배송지 주소';

	$_sort_fd=array(10=>"10", 20=>"20", 30=>"30", 50=>"50", 60=>"70", 100=>"100");
	$_date_type = array(
		'-1 weeks' => '1주일',
		'-15 days' => '15일',
		'-1 months' => '1개월',
		'-3 months' => '3개월',
		'-6 months' => '6개월',
		'-1 years' => '1년',
		'-2 years' => '2년',
		'-3 years' => '3년',
	);

	$_set = mySearchSet("ordersearch");
	if(!$_set['period']) $_set['period'] = 1;
	if(!$_set['paytype']) $_set['paytype'] = null;
	if(!$_set['orderby']) $_set['orderby'] = 1;
	if(!$_set['search']) $_set['search'] = 'buyer_name';
	if(!$_set['sort_fd']) $_set['sort_fd'] = 20;
	if(!$_set['seach_date_period']) $_set['seach_date_period'] = '-15 days';

?>
<style type="text/css">
label {display:inline-block; width:20%; white-space: nowrap;}
.search_fd label {width:auto; padding-right:15px;}
</style>
<form name="searchFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="order@order_search.exe">
	<div class="box_middle">
		<div class="list_info left">
			<p>검색 시 자주 사용하는 필드를 기본으로 선택되도록 할 수 있으며, 관리자 별로 설정이 가능합니다.</p>
		</div>
	</div>
	<table class="tbl_row" style="width:700px;">
		<caption class="hidden">주문 검색 세부 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">기간</th>
			<td>
				<?=wCk("radio", $_period_fd, "period", $_set[period]);?><br><br>
				<input type="checkbox" name="period_all" value="Y" id="period_all" <?=checked($_set[period_all], "Y")?>> <label for="period_all" class="p_cursor">전체기간</label><br>
				<?=wCk("radio", $_date_type, "seach_date_period", $_set[seach_date_period]);?>
			</td>
		</tr>
		<tr>
			<th scope="row">거래상태</td>
			<td><?=wCk("checkbox", $_order_stat, "ostat", $_set[ostat]);?></td>
		</tr>

		<tr>
			<th scope="row">결제수단</th>
			<td><?=wCk("radio", $_pay_type, "paytype", $_set[paytype], true);?></td>
		</tr>
		<tr>
			<th scope="row">검색필드</th>
			<td class="search_fd"><?=wCk("radio", $_search_fd, "search", $_set[search]);?></td>
		</tr>
		<tr>
			<th scope="row">정렬</th>
			<td><?=wCk("radio", $_orderby_fd, "orderby", $_set[orderby]);?></td>
		</tr>
		<tr>
			<th scope="row">주문서 개수</th>
			<td><?=wCk("radio", $_sort_fd, "sort_fd", $_set[sort_fd]);?></td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>
<script type="text/javascript">
var check_all = function() {
	if($('#period_all').prop('checked') == true) {
		$('[name=seach_date_period]').attr('disabled', true);
	} else {
		$('[name=seach_date_period]').attr('disabled', false);
	}
}
$('#period_all').change(check_all);
$(function() {
	check_all();
});
</script>