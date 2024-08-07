<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	define('_LOAD_AJAX_PAGE_', true);
	$_GET['striplayout'] = 1;

	$ono = addslashes($_GET['ono']);
	$ord = $pdo->assoc("select * from {$tbl['order']} where ono='$ono'");
    ordChgPart($ord['ono']);
    $ord['stat2'] = $pdo->row("select stat2 from {$tbl['order']} where ono='$ono'");

	if(empty($ord['no']) == true) jsonReturn(array('status' => 'error', 'message' => '존재하지 않는 주문번호입니다.'));
    if (strcmp($_SESSION['my_order'], $ord['ono']) !== 0) {
    	if(empty($member['no']) == true || $member['no'] != $ord['member_no']) jsonReturn(array('status' => 'error', 'message' => '권한이 없는 주문서입니다.'));
    }

    // 배송지 정보 체크
    $field_chekd = null;
    if (empty($ord['addressee_addr2']) == true) {
        $field_chekd = __lang_order_input_raddr2__;
    }
    if (empty($ord['addressee_addr1']) == true) {
        $field_chekd = __lang_order_input_raddr1__;
    }
    if (empty($ord['addressee_zip']) == true) {
        $field_chekd = __lang_order_input_rzip__;
    }
    if (empty($ord['addressee_cell']) == true) {
        $field_chekd = __lang_order_input_rcell__;
    }
    if (empty($ord['addressee_name']) == true) {
        $field_chekd = __lang_order_input_rname__;
    }
    if ($field_chekd) {
        jsonReturn(array('status' => 'error', 'message' => $field_chekd));
    }

	$_tmp_file_name = 'mypage/order_paytype.php';
	include_once $engine_dir."/_engine/common/skin_index.php";

?>