<?PHP

	// 샵링커 상품 재고 연동

	include_once '_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once  $engine_dir.'/_engine/api/shopLinker/shopLinker.class.php';
	include_once  $engine_dir.'/_engine/api/shopLinker/shopLinkerProduct.class.php';

	$no_qcheck = true;
	$prds = $cnt = 0;

	$last_idx = $pdo->row("select value from $tbl[default] where code='shoplinkerStock'");
	if($last_idx > 0) $w .= " and a.inout_no > $last_idx";
	else {
		$pdo->query("insert into $tbl[default] (code, value) values ('shoplinkerStock', '0')");
	}

	$send_pno = array();
	$res = $pdo->iterator("select pno, max(inout_no) as inout_no from erp_inout a inner join erp_complex_option b using(complex_no) where b.reg_date > '1900-01-01 00:00:00' $w group by pno order by inout_no asc");
    foreach ($res as $data) {
		$send_pno[$data['inout_no']] = $data['pno'];
	}

	if(count($send_pno) > 0) {
		$openmarket = new shopLinkerProduct();
		$openmarket->getMallProduct($send_pno);
	}

	foreach($send_pno as $inout_no => $pno) {
		$res = $pdo->iterator("select no from $tbl[product_openmarket] where pno='$pno' and mall_product_id!=''");
        foreach ($res as $data) {
			$openmarket->setProductPrice(array($data['no']));
			$cnt++;
		}
		$pdo->query("update $tbl[default] set value='$inout_no' where code='shoplinkerStock' and value < '$inout_no'");
		$prds++;
	}

	exit($prds.'개의 상품 '.$cnt.' 개의 판매처별 상품 정보가 수정되었습니다.');

?>