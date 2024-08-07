<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 수정요청
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$ono = addslashes($_REQUEST['ono']);
	$cate1 = numberOnly($_REQUEST['cate1']);
	$cate2 = numberOnly($_REQUEST['cate2']);
	$sbscr = ($_REQUEST['sbscr']=='Y') ? 'Y':'N';
	$phone = preg_replace('/[^0-9-]/', '', $_REQUEST['phone']);

	// 주문 관련 문의
	if($ono) {
		if($sbscr=='Y') {
			$ord=get_info($tbl['sbscr'],'sbono',$ono);
			$ord['ono'] = $ono;
		}else {
			$ord=get_info($tbl['order'],'ono',$ono);
		}
		if(!$ord[no]) msg(__lang_mypage_error_orderNotExist__, 'back');

		$opstat = $pdo->row("select count(*) from $tbl[order_product] where ono='$ord[ono]' and stat in (2, 3, 4, 5)");
		if($ord['stat'] == 1 && $cate2 == 12 && $sbscr != 'Y' && $scfg->comp('order_cancel_type_1', array('Y', 'C')) && $cfg['stat1_direct_cancel'] == 'N' && $opstat == 0) {
			include_once $engine_dir.'/_manage/manage2.lib.php';
			$_SERVER['REQUEST_METHOD']='POST';
			$stat = 13;
			$ono = $_POST['ono'] = $ord['ono'];
			$exec = $_POST['exec'] = 'process';
			$repay_no = array();
			$tres = $pdo->iterator("select no from $tbl[order_product] where ono='$ord[ono]' and stat = 1");
            foreach ($tres as $tmp) {
				$repay_no[] = $tmp['no'];
			}
			$_POST['stat'] = $stat;
			$_POST['repay_no'] = $repay_no;
			$_POST['reason'] = $reason = '사용자 취소';
			$_POST['cpn_no'] = $cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$ono'");
			$_POST['emoney_repay'] = $emoney_repay = $ord['emoney_prc'];
			$_POST['milage_repay'] = $milage_repay = $ord['milage_prc'];
			$_POST['repay_dlv_prc'] = $repay_dlv_prc = $ord['dlv_prc'];
			$_POST['total_repay_prc'] = $total_repay_prc = $ord['pay_prc'];
			$is_counsel = true;

			include $engine_dir.'/_manage/order/order_prd_stat.exe.php';
			msg(__lang_mypage_direct_cancel__, 'back');
		}

		if($ord['stat'] == 3 && $cate2 == 12) $cate2 = 14;
		if($ord['stat'] == 2 && $cate2 == 12) $cate2 = 14;
		if($ord['stat'] == 1 && $cate2 == 14) $cate2 = 12;

		if($ord['stat'] == 5) {
			if($cfg['deny_decided_cancel'] != 'N') {
				msg(__lang_mypage_error_aleadyDecided__, 'back');
			}
			if($cfg['deny_decided_cancel'] == 'N' && strtotime('-'.$cfg['deny_decided_cancel_date'].' days') > $ord['date5']) { // 반품요청 가능
				msg(__lang_mypage_error_aleadyDecided__, 'back');
			}
		}

		// 회원의 주문
		if($ord[member_no]) {
			memberOnly();
			if($ord[member_no]!=$member[no]) msg(__lang_mypage_error_notOwnOrd__, '/');
			if(!$rURL) $rURL=$root_url."/mypage/order_list.php";

		}
		else {
			checkBasic();
			checkBlank($phone, __lang_member_input_phone__);
			if(numberOnly($phone)!=numberOnly($ord[buyer_phone]) && numberOnly($phone)!=numberOnly($ord[buyer_cell])) msg(__lang_mypage_error_wrongPhoneNum__, 'back');
		}
	}

	// 분류
	$_lang_cust_cate = eval(__lang_cust_cate);
	$cate_str=$_lang_cust_cate[$cate1][$cate2];
	if(!$cate_str) $cate_str = $_cust_cate[$cate1][$cate2];
	if($cate_str) {
		$cate_str.="\n<input type=\"hidden\" name=\"cate1\" value=\"$cate1\">";
		$cate_str.="\n<input type=\"hidden\" name=\"cate2\" value=\"$cate2\">\n";
	}

	if($ord['stat'] == 3 && $cfg['deny_placeorder_cancel'] == 'Y' && ($cate2 == 12 || $cate2 == 14 || $cate2 == 1 )) {
		msg(__lang_mypage_error_aleadyShipping__, 'back');
	}

	// 에디터
    $editor_code = '';
    if(isset($cfg['counsel_use_editor']) == true && $cfg['counsel_use_editor'] == 'Y') {
        $editor_code = 'counsel_temp_'.$now;
    }

	common_header();

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js?000001"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript">
var editor_code = '<?=$editor_code?>';
var editor = null;
$(function() {
	if(editor_code) {
		editor = new R2Na('counsel_cnt', {
			'editor_gr': 'counsel',
			'editor_code': editor_code
		});
		editor.initNeko(editor_code, 'counsel', 'img');
	}
});
</script>
<?PHP

	include_once $engine_dir."/_engine/common/skin_index.php";

?>