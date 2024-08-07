<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스마트스토어 상품문의 수집
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Naver\CommerceAPI;

	include_once __ENGINE_DIR__ . '/_engine/include/common.lib.php';

	if ($cfg['n_smart_store'] != 'Y' || $cfg['use_n_smart_qna'] != 'Y') {
		return;
	}

	$CommerceAPI = new CommerceAPI();

	addField($tbl['qna'], 'smartstore_no', 'int(12) not null default "0" comment "스마트스토어 문의번호"');

	$date1 = ($_REQUEST['o1']) ? $_REQUEST['o1'] : date('Y-m-d', strtotime('-1 days'));
	$date2 = ($_REQUEST['o1']) ? $_REQUEST['o2'] : date('Y-m-d H:i:s');

    $qna_new = $qna_changed = $page = 0;
    while (1) {
        $page++;
        $res = $CommerceAPI->contentsQnas($date1, $date2, $page);
        if ($CommerceAPI->getError()) return;

        if(isset($res->contents) && is_array($res->contents)){
            foreach($res->contents as $data) {
                $smartstore_no = $data->questionId;
                $pno = $data->productId;
                $member_id = $data->maskedWriterId;
                $title = '스마트스토어 상품 문의';
                $reg_date = strtotime($data->createDate);
                $content = $data->question;
                $answer_ok = ($data->answered == true) ? 'Y' : 'N';
                $answer_date = ($answer_ok == 'Y') ? $reg_date : 0;
                $name = $data->maskedWriterId;
                $pwd = sql_password(time() . $smartstore_no);

                if (!$smartstore_no) continue;

                $exists = $pdo->assoc("select no from {$tbl['qna']} where smartstore_no=?", array($smartstore_no));
                if ($exists['no'] > 0) {
                    $pdo->query("
                        update {$tbl['qna']} set 
                             title=?, content=?, answer_ok=?, answer_date=?, secret='Y', pwd=? 
                         where smartstore_no='$smartstore_no'
                     ", array(
                         $title, $content, $answer_ok, $answer_date, $pwd
                    ));
                    if($pdo->lastRowCount() > 0) {
                        $qna_changed++;
                    }
                } else {
                    $pno = $pdo->row("select no from {$tbl['product']} where nstoreId='$pno'");

                    $pdo->query("
                        insert into {$tbl['qna']}
                        (pno, member_id, name, pwd, title, content, reg_date, answer_ok, smartstore_no, secret)
                        values
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", array(
                        $pno, $member_id, $name, $pwd, $title, $content, $reg_date, $answer_ok, $smartstore_no, 'Y'
                    ));
                    $qna_new++;
                }
            }
            if ($res->last == $page) break;
        }
	}

?>