<?PHP

	checkBasic();

	$ono = addslashes($_POST['ono']);
	$check_ono = $pdo->row("select count(*) from $tbl[order] where stat not in (11, 31) and ono='$ono'");

	switch($_POST['exec']) {
		case 'checkONO' :
			if($check_ono > 0) {
				exit('OK');
			}
			exit('not found');
		break;
	}

	if($check_ono < 1) {
		msg('존재하지 않는 주문번호입니다.');
	}

	$cons_name = addslashes(trim($_POST['cons_name']));
	$ono = addslashes(trim($_POST['ono']));
	$prod_name = addslashes(trim($_POST['prod_name']));
	$amt1 = parsePrice($_POST['amt1']);
    $tax_amount = parsePrice($_POST['tax_amount']);
    $taxfree_amount = parsePrice($_POST['taxfree_amount']);
	$cash_reg_num = numberOnly($_POST['cash_reg_num']);
	$cons_tel = addslashes(trim($_POST['cons_tel']));
	$cons_email = addslashes(trim($_POST['cons_email']));
	$b_num = numberOnly($cfg['company_biz_num']);
    $pay_type = $_POST['pay_type'];

	checkBlank($cons_name,"주문자명을 입력해주세요.");
	checkBlank($ono,"주문 번호를 입력해주세요.");
	checkBlank($prod_name,"주문 상품명을 입력해주세요.");
	checkBlank($amt1,"상품 가격을 입력해주세요.");
	checkBlank($cash_reg_num,"발급번호를 입력해주세요.");

	$stat=1;
	$amt4 = round($tax_amount / 1.1) * 0.1; // 부가세 (과세상품 결제금액/1.1 의 0.1 )
    $amt2 = ($tax_amount - $amt4) + $taxfree_amount; //공급가액 (면세금액 + 과세금액의 부가세)
	$mcht_name="indv"; // 개별발급구분
	$reg_date=$now;

	$sql="insert into `$tbl[cash_receipt]` (`ono`, `stat`, `amt1`, `amt2`, `amt4`, taxfree_amt, b_num, `cash_reg_num`, `pay_type`, `mcht_name`, `prod_name`, `cons_name`, `cons_tel`, `cons_email`, `reg_date`) values('$ono', '$stat', '$amt1', '$amt2', '$amt4', '$taxfree_amount','$b_num', '$cash_reg_num', '$pay_type', '$mcht_name', '$prod_name', '$cons_name', '$cons_tel', '$cons_email', '$reg_date')";

	$r=$pdo->query($sql);

    cashReceiptLog(array(
        'cno' => $pdo->lastInsertId(),
        'ono' => $ono,
        'stat' => $stat,
        'ori_stat' => 0,
        'admin_id' => $admin['admin_id'],
        'system' => 'N'
    ));

	if(!$r) msg("처리 실패!");

	msg("발급 신청이 완료되었습니다", "reload", "parent");

?>