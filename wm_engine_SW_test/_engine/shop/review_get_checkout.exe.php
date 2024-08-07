<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버페이 상품평 수집
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\CheckoutApi4;

    include_once __ENGINE_DIR__.'/_engine/include/shop_detail.lib.php';

	if(!is_object($checkout)) {
		unset($checkout);
	}
	$checkout = new CheckoutApi4();

	$date1 = ($_REQUEST['o1']) ? $_REQUEST['o1'] : date('Y-m-d', strtotime('-1 days'));
	$date2 = date('Y-m-d', $now);

	$reviews = array();
	$reviewClassType = array();
	$rev_cnt = 0;
	if($cfg['npay_review_general'] == 'Y') $reviewClassType[] = 'GENERAL';
	if($cfg['npay_review_premium'] == 'Y') $reviewClassType[] = 'PREMIUM';

	if(count($reviewClassType) > 0) {
		foreach($reviewClassType as $Type) {
			$result = $checkout->getReview($date1, $date2, $Type);
			$datas = $result->Data;
			if(is_array($result) && count($result) > 0) {
				foreach($result as $data) {
					$reviews[] = $data;
				}
			}
		}

		if(count($reviews) < 1) exit('<p>no review</p>');

		foreach($reviews as $idx => $data) {
            if ($data->MallID[0] != $cfg['checkout_id']) {
                continue;
            }
			$pno = $data->ProductID[0];
			$ono = $data->ProductOrderID[0];
			$reg_date = strtotime($data->CreateYmdt[0]);
			$rev_pt = $checkout->getRevPt($data->PurchaseReviewScore[0]);
			$name = addslashes($data->WriterId[0]);
			$title = addslashes($data->Title[0]);
			$content = addslashes($data->Content[0]);
			if($title && !$content) $content = $title; // 일반리뷰
			if(!$content) continue;

			if($cfg['product_review_use_editor'] != 'Y') {
				$content = strip_tags($content);
				$content = nl2br($content);
			}

			$exists = $pdo->row("select no from $tbl[review] where reg_date='$reg_date' and name='$name' and pno = '$pno' and ono = '$ono' and pwd='npay'");
			if($exists > 0) {
                $no = $exists;
				$pdo->query("update $tbl[review] set title='$title', content='$content' where no='$exists'");
			} else {
				$buy_date = $pdo->row("select date1 from $tbl[order] o inner join $tbl[order_product] p using(ono) where p.checkout_ono='$ono'");
				$stat = $cfg['npay_review_stat'];

				$pdo->query("
					insert into $tbl[review]
					(pno, name, pwd, rev_pt, title, content, reg_date, stat, ono, buy_date, npay)
					values
					('$pno', '$name', 'npay', '$rev_pt', '$title', '$content', '$reg_date', '$stat', '$ono', '$buy_date', 'Y')
				");
                $no = $pdo->lastInsertId(); //후기게시글 번호 취합
				setRevPt($pno);
			}

            if ($no && ($scfg->comp('use_review_image_cnt', 'Y') == true)) {
                //후기게시글 생성완료 및 포토후기 사용시, 본문내용의 img태그여부를 통해 포토 후기여부 결정
                $review_data = array(
                    'no' => $no,
                    'content' => $content
                );
                reviewImageCount($review_data);
            }

   			$rev_cnt += $pdo->lastRowCount();
		}
	}

?>