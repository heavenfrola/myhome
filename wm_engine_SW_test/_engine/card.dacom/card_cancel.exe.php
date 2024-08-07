<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ PG 취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$oid = addslashes($_REQUEST['oid']);
	$respcode = trim($_REQUEST['respcode']);
	$respmsg = mb_convert_encoding($_REQUEST['respmsg'], _BASE_CHARSET_, array('euckr', 'utf8'));
	$transaction = addslashes($_REQUEST['transaction']);;

	$card = $pdo->assoc("select * from $tbl[card] where wm_ono='$oid'");
	$stat = 1;
	if($respcode == '0000' || $respcode == 'RF00') {
		$pdo->query("update `$tbl[card]` set `stat`='3' where `no`='$card[no]'");
		$msg = '거래취소성공!';
		$stat = 2;
	}else{
		$msg='거래취소실패! ('.$respcode.' : '.addslashes($respmsg).')';
	}
	$pdo->query("
		insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$stat', '$oid', '$transaction', '$respcode', '$respmsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

	makeOrderLog($oid, 'card_cancel.exe.php');

	exit('OK');

?>