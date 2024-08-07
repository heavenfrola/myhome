<?PHP

	$ord_input_fd=array(no=>"번호", ono=>"주문번호", dlv_no=>"배송업체 (등록된 배송업체명과 정확히 일치)", dlv_code=>"송장번호", opno=>"주문상품번호", member_id=>"회원아이디", buyer_name=>"구매자명", buyer_email=>"구매자 이메일", buyer_phone=>"구매자 전화번호", buyer_cell=>"구매자 휴대폰번호", addressee_name=>"수취인명", addressee_phone=>"수취인 전화번호", addressee_cell=>"수취인 휴대폰번호", addressee_zip=>"수취인 우편번호", addressee_addr=>"수취인 주소", dlv_memo=>"배송메세지", total_prc=>"결제금액", pay_type=>"결제방법", etc1=>"기타1", etc2=>"기타2", etc3=>"기타3", etc4=>"기타4", etc5=>"기타5");
	$ord_input_fd_selected=(!$cfg[ord_input_fd_selected]) ? "no,ono,dlv_no,dlv_code" : $cfg[ord_input_fd_selected];
	$_ord_input_fd_selected = explode(",", $ord_input_fd_selected);
	$essencial_fd="/ono/dlv_no/dlv_code/";
	$essencial_color="#FF3300";

?>
<div class="box_middle add_fld">
	<div class="left">
		<h3>CSV 파일내용</h3>
		<ul style="border: solid 1px #ccc; padding: 10px;">
			<?foreach($_ord_input_fd_selected as $key => $val) {?>
			<li style="margin: 10px 0" class="<?=(strchr($essencial_fd, '/'.$val.'/')) ? 'p_color2' : ''?>"><?=($key+1)?>. <?=$ord_input_fd[$val]?></li>
			<?}?>
		</ul>
	</div>
</div>
<?PHP

	include $engine_dir.'/_manage/order/delivery_fileinput_result.php';

?>