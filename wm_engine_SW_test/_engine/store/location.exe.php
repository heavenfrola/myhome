<?php
/* +----------------------------------------------------------------------------------------------+
' |  [매장지도]
' +----------------------------------------------------------------------------------------------+*/
include_once $engine_dir."/_engine/include/common.lib.php";

$reciveData = json_decode(file_get_contents('php://input'), true);
if($reciveData) $_POST = $reciveData;
$exec = $_POST['exec'];


if(!$exec) msg("잘못 된 접근 입니다.");

printAjaxHeader();

if( $exec == "wishlist" ) {

	$_result = array();
	$sno = numberOnly($_POST['sno']);

	$_type = 'success';

	if(!$sno) {
		$_type = 'nonkey';
		$_result['msg'] = '선택된 매장이 존재하지 않습니다.';
		$_result['return_url'] = $root_url."/store/location.php";
	}

	if(!$member['no']) {
		$_type = 'nonlogin';
		$_result['msg'] = '로그인 후 이용 가능합니다.';
		$_result['return_url'] = $root_url."/member/login.php?rURL=".urlencode($_SERVER['HTTP_REFERER']);
	}

	if($_type == 'success') {
		$_where = array();

		$_sql_arr = array(
			'member_no' => $member['no'],
			'sno' => $sno
		);

		$_wish = $pdo->row("select sno from {$tbl['store_wish']} where sno=:no and member_no=:member_no", array(':no' => $sno, ':member_no' => $member['no']));
		if ($_wish) {
			$_where['sno'] = $_wish;
			$_where['member_no'] = $member['no'];
		} else {
			$_sql_arr = array_merge($_sql_arr, array('reg_date'=>$now, 'ip'=>$_SERVER['REMOTE_ADDR']));
		}

		//쿼리 병합
		$_mqry = qryResult($_sql_arr, $_where);

		if (!$_wish) {
			$msql = "insert into {$tbl['store_wish']} (" . $_mqry['i'] . ") values (" . $_mqry['v'] . ")";
		} else {
			$msql = "delete from {$tbl['store_wish']} where " . $_mqry['w'];
		}

		$a = $pdo->query($msql, $_mqry['a']);
		$_type = (!$pdo->getError()) ? $_type : 'fail';
	}

	$_result['type'] = $_type;
	exit(json_encode($_result));
}

?>