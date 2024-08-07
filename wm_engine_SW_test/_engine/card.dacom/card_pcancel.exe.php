<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ 결제 부분취소 결과 저장
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$oid = addslashes($_REQUEST['oid']);
	$respcode = addslashes($_REQUEST['respcode']);
	$respmsg = addslashes(mb_convert_encoding($_REQUEST['respmsg'], 'UTF8', 'EUCKR'));
	$rev_amount = parsePrice($_REQUEST['rev_amount']);
	$mode = $_REQUEST['mode'];
	$admno = numberOnly($_REQUEST['admno']);
	$admin = $pdo->assoc("select * from $tbl[mng] where no='$admno'");

	$card = $pdo->assoc("select * from `$tbl[card]` where `wm_ono` = '$oid'");
	if($respcode == '0000') {
		$stat = 2;
		$msg = '거래취소성공!';

		if($mode != 'ret') { // db_url
			$cstat = ($card['wm_price'] == $rev_amount) ? 3 : 2;
			$pdo->query("update `$tbl[card]` set `stat`='$cstat', `wm_price` = `wm_price` - '$rev_amount' where `no`='$card[no]'");
		}
	} else {
		$stat = 1;
		$msg = '거래취소실패! ('.$respcode.' : '.$respmsg.')';
	}

	if($mode != 'ret') { // db_url
		$pdo->query("
			insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
			values ('$card[no]', '$stat', '$card[wm_ono]', '$rev_amount', '$card[tno]', '$respcode', '$respmsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
		");
		exit('OK');
	}

	msg($msg, 'reload', 'parent');

?>