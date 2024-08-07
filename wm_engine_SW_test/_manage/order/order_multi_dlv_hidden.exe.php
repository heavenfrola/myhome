<?PHP

	checkBasic();
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$search_pno = numberOnly($_POST['search_pno']);
	if($search_pno == '') msg("상품을 선택하세요.");

	$dlvhiddenmode = numberOnly($_POST['dlvhiddenmode']);		// 1:보류 2:해제
	if($dlvhiddenmode == 1){
		$dlvhiddenmode = 'Y';
	}elseif($dlvhiddenmode == 2){
		$dlvhiddenmode = 'N';
	}
	if($dlvhiddenmode == '') msg("변경형태를 선택하세요.");

	$all_ord = count($check_pno);
	if($all_ord<1) msg("하나 이상의 주문을 선택하세요");

	$oii=0;

	if($all_ord > 500) msg("500개 이상의 주문 처리시 지연될 수 있으므로 처리 주문수를 조절해주시기 바랍니다");

	$addq=" and `no` in (".implode(",", $check_pno).")";
	$multi_sql = $pdo->iterator("select ono from `$tbl[order]` where 1 $addq");
	$target_ono = array();
    foreach ($multi_sql as $data) {
		$target_ono[] = $data['ono'];
	}


	$all_ord = count($target_ono);
	if($all_ord<1) msg("하나 이상의 주문을 선택하세요");


	$addq=" and `ono` in ('".implode("','", $target_ono)."') and pno = '$search_pno'";
	$pdo->query("update $tbl[order_product] set dlv_hold = '$dlvhiddenmode' where 1 $addq");

	echo "update $tbl[order_product] set dlv_hold = '$dlvhiddenmode' where 1 $addq";


	//msg($all_ord.'개 주문의 상태를 변경였습니다.', 'reload', 'parent');

?>