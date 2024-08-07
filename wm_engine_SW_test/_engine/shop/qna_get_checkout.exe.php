<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버페이 상품문의 수집
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\CheckoutApi4;

	if(!is_object($checkout)) {
		unset($checkout);
	}
	$checkout = new CheckoutApi4();

	$date1 = ($_REQUEST['o1']) ? $_REQUEST['o1'] : date('Y-m-d', strtotime('-5 days'));
	$date2 = date('Y-m-d', $now);

	$datas = array();
	$qna_cnt = 0;

	$datas = $checkout->getInquiry($date1, $date2);
	if(count($datas) < 1) return;

	foreach($datas as $idx => $data) {
		$checkout_no = $data->InquiryID[0];
		$ono = $data->OrderID[0];
		$pno = $data->ProductID[0];
		$member_id = $data->CustomerID[0].'(npay)';
		$title = addslashes($data->Title[0]);
		$category = addslashes($data->Category[0]);
		$reg_date = strtotime($data->InquiryDateTime[0]);
		$content = addslashes($data->InquiryContent[0]);
		$answer = addslashes($data->AnswerContent[0]);
		$answer_ok = ($data->IsAnswered[0] == 'true') ? 'Y' : 'N';
		$answer_date = ($answer_ok == 'Y') ? $reg_date : 0;
		$answer_id = $data->AnswerContentID[0];
		$name = addslashes($data->CustomerName[0]);
		$pwd = sql_password($ono);

		if(!$checkout_no) continue;

		$exists = $pdo->assoc("select no, answer_date from $tbl[qna] where checkout_no='$checkout_no'");
		if($exists['answer_date'] > 0) $answer_date = $exists['answer_date']; // 윙에서 답변 작성 했을 경우 기존 답변일자 있음
		if($exists['no'] > 0) {
			if($exists['answer_ok'] == "Y" && !$answer) continue;

			$pdo->query("update $tbl[qna] set title='$title', cate='$category', content='$content', answer='$answer', answer_ok='$answer_ok', answer_date='$answer_date', checkout_ans_no='$answer_id', secret='Y', pwd='$pwd' where checkout_no='$checkout_no'");
		} else {
			$buy_date = $pdo->row("select date1 from $tbl[order] where ono='$ono'");

			$pdo->query("
				insert into $tbl[qna]
				(pno, member_id, name, pwd, title, cate, content, reg_date, answer, answer_ok, answer_date, buy_date, checkout_no, checkout_ans_no, secret)
				values
				('$pno', '$member_id', '$name', '$pwd', '$title', '$category', '$content', '$reg_date', '$answer', '$answer_ok', '$answer_date', '$buy_date', '$checkout_no', '$answer_id', 'Y')
			");
		}
		$qna_cnt += $pdo->lastRowCount();
	}

	return $qna_cnt;

?>