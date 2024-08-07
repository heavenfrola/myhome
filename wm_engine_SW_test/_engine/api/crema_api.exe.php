<?php
/**
 * 크리마 측에서 상품 / 주문 업데이트 활용 API
 * @박연경 <pyk87@wisa.co.kr>
 * @date 2016-01-20
 */

chdir($_SERVER['DOCUMENT_ROOT']);
set_time_limit(0);

$urlfix = 'Y';
$no_qcheck = true;
include '_config/set.php';
include_once $engine_dir.'/_engine/include/common.lib.php';
include $engine_dir."/_engine/include/rest_api.class.php";
include $engine_dir."/_engine/api/class/crema.api.php";

$crema = new cremaAPI();

$start_date = strtotime($_REQUEST['start_date']);
$end_date = strtotime($_REQUEST['end_date'])+86399;

switch($_REQUEST['mode']) {

	case 'productcreate' : // 상품 추가 및 수정
		$sql = "select * from $tbl[category] where ctype in (1, 2, 4, 5)";
		$rs = $pdo->iterator($sql);
        foreach ($rs as $row) {
			$crema->createCategory($row['no']);
		}

		$sql = "SELECT * FROM ".$tbl['product']." WHERE stat between 2 and 4 and (edt_date2>'".$start_date."' AND edt_date2<='".$end_date."') OR (reg_date>'".$start_date."' AND reg_date<='".$end_date."') OR (edt_date>'".$start_date."' AND edt_date<='".$end_date."')";

		$rs = $pdo->iterator($sql);
		foreach ($rs as $row) {
			if($row['no'] && $row['wm_sc']==0) {
				$crema->createProduct($row['no']);
			}
		}
		break;

	case 'ordercreate' : //주문 추가 및 수정
        $asql = '';
        if ($scfg->comp('crema_non_member', 'Y') == false) {
            $asql .= " and member_no > 0";
        }

		$date1s = strtotime($_REQUEST['date']); //오늘 ex)2015-10-02 00:00:00
		$date1e = strtotime('+1 days', $date1s)-1;//2015-10-02 23:59:59
		$sql = "SELECT ono, member_no FROM wm_order where stat <= 5 and date1 between $date1s and $date1e $asql";

		$page = numberOnly($_REQUEST['page']);
		$order_no = array();
		if($page > 0) {
			include_once $engine_dir."/_engine/include/paging.php";

			$NumTotalRec = $pdo->row("select count(*) from wm_order where member_no > 0 and stat <= 5 and date1 between $date1s and $date1e");
			$PagingInstance = new Paging($NumTotalRec, $page, 100, 10);
			$PagingInstance->addQueryString($QueryString);
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult['LimitQuery'];
		}

		$rs = $pdo->iterator($sql);

		foreach ($rs as $row) {
			if($row['member_no']>0) {
				$crema->createOrder($row['ono']);

				$sql = "SELECT no FROM ".$tbl['order_product']." WHERE ono = '".$row['ono']."'";
				$rs2 = $pdo->iterator($sql);

				foreach ($rs2 as $row2) {
					$crema->createOrderProduct($row2['no']);
				}

				$order_no[] = $row['ono'];
			}
		}
		exit(json_encode(array(
			'total_page' => $PagingInstance->end,
			'current_page' => $page,
			'total_rows' => $NumTotalRec,
			'order_no' => $order_no
		)));

		break;
	case 'productsingle' : //단일 상품 추가 및 수정
		$crema->createProduct($_REQUEST['code']);
		break;

	case 'ordersingle' : //단일 상품 추가 및 수정
		$crema->createOrder($_REQUEST['ono']);

		$sql = "SELECT no FROM ".$tbl['order_product']." WHERE ono = '".$_REQUEST['ono']."'";
		$rs = $pdo->iterator($sql);

		foreach ($rs as $row) {
			$crema->createOrderProduct($row['no']);
		}
		break;

	case 'reviewcreate' :
		$sql = "SELECT * FROM wm_review WHERE stat>1 and reg_date>'".$start_date."' AND reg_date<='".$end_date."' order by no";
		$rs = $pdo->iterator($sql);
		$ii = 0;
		foreach ($rs as $row) {
			$crema->createReview($row['no']);
			$sql = "SELECT no FROM ".$tbl['review_comment']." WHERE ref = '".$row['no']."'";
			$rs2 = $pdo->iterator($sql);
			$ii++;
			foreach ($rs2 as $row2) {
				$crema->createRComment($row2['no']);
			}
		}
	break;
	case 'cartitemcreate' :
        $asql = '';
        if ($scfg->comp('crema_non_member', 'Y') == false) {
            $asql .= " and member_no > 0";
        }
		$end_date += 86399;
		$res = $pdo->iterator("select no, pno, reg_date, member_no from $tbl[cart] where guest_no='' and reg_date between $start_date and $end_date $asql order by no asc");
        foreach ($res as $cart) {
			$id = $pdo->row("select crema_key from crema_matching where type='m' and w_key='$cart[member_no]'");
			if(!$id) {
				$mem = $pdo->assoc("select no, member_id, name, reg_date, cell, sms, email, mailing, level from $tbl[member] where no='$cart[member_no]'");
				$ret = $crema->createUser($mem);
				$id = $ret['id'];
			}
			$ret = $crema->createCartItem($cart, $id);
		}
	break;
	case 'userUpdateCallback' :
		header('Content-type:application/json; charset=utf-8;');

		$jsondata = file_get_contents("php://input");

		$pdo->query('alter table crema_matching change type type varchar(2) not null');

		$failed = array();
		$json = json_decode($jsondata);
		if(is_array($json->user_codes)) {
			foreach($json->user_codes as $mid) {
				$mid = trim(addslashes($mid));
				$data = $pdo->assoc("select no, member_id, name, reg_date, cell, sms, email, mailing, level from $tbl[member] where member_id='$mid'");

				$res = $crema->createUser($data);
				if(!$res['id']) {
					$failed[] = $mid;
				}
			}
		}

		exit(json_encode(array('failed_user_codes'=>$failed)));
	break;

	case 'gradeCreate' :
		$sql = "select * from $tbl[member_group] where use_group='Y' order by no desc";
		$rs = $pdo->iterator($sql);
        foreach ($rs as $row) {
			$crema->createGrade($row['no']);
		}
		break;
}

exit;

?>