<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 일괄 적용 처리
	' +----------------------------------------------------------------------------------------------+*/
	$exec = numberOnly($_POST['exec']);
	checkBasic();
	if($exec==1) {
		$event_sale="Y";
	}
	else {
		$event_sale="N";
	}

	$sql="update `$tbl[product]` set `event_sale`='$event_sale' where prd_type='1'";
	$pdo->query($sql);

	msg("모든 상품을 수정하였습니다","reload","parent");

?>