<?PHP

	set_time_limit(0);
	ini_set('memory_limit', -1);

    use Wing\common\WorkLog;

    $log = new WorkLog();

	foreach($_POST as $key => $val) {
		${$key}=$_POST[$key]=urldecode($val);
	}

	$_QueryString1=explode("&", $query_string);
	foreach($_QueryString1 as $key => $val) {

		$_QueryString2=explode("=", $val);
		if($_QueryString2[0] == 'body' || !$_QueryString2[0]) continue;
		${$_QueryString2[0]} = $_GET[$_QueryString2[0]] = $_REQUEST[$_QueryString2[0]] = $_QueryString2[1];
	}

	include_once $engine_dir."/_manage/product/product_search.inc.php";

	$stype=$_POST['stype'];
	$exec=$_POST['exec'];
	$icons=$_POST['icons'];

	$icons = preg_replace("/^,/", "", $icons);
	$_icons = explode(",", $icons);
	if (!is_array($_icons)) msg('아이콘이 선택되지 않았습니다.');

	switch($stype) {

		case '1' : // 전체
			$where="";
		break;
		case '2' : // 검색
			$where=$w;
		break;
		case '3' : // 선택
			$pnos=preg_replace("/[^0-9,]+/", "", $check_pno);
			$pnos=preg_replace("/^,+|/", "", $pnos);
			$where=" and p.no in ($pnos)";
		break;
	}

	// 대상 상품 체크
	if ($admin['level'] == 4) {
        $where .= " and p.partner_no='$admin[partner_no]'";
    }
	$res = $pdo->iterator("select p.no, p.name, p.icons from {$tbl['product']} p $prd_join where 1 $where");
	if ($res->rowCount() == 0) msg('대상 상품이 없습니다.');
    foreach ($res as $data) {
        switch($exec) {
            case 'register' :
                foreach($_icons as $key => $val) {
                    $r = $pdo->query("update `$tbl[product]` p set `icons`=if(replace(`icons`,'@$val@','') = '', replace(`icons`,'@$val@',''), replace(`icons`,'@$val@','@')) where no='{$data['no']}'");
                    $r = $pdo->query("update `$tbl[product]` p set `icons`=if(`icons` = '', '@$val@', concat(`icons`,'$val@')) where no='{$data['no']}'");
                }
            break;
            case 'change' :
                $r = $pdo->query("update `$tbl[product]` p set `icons`=if(`icons` like '%@$_icons[1]@%', `icons`, replace(`icons`,'@$_icons[0]@','@$_icons[1]@')) where no='{$data['no']}'");
            break;
            case 'delete' :
                foreach($_icons as $key => $val) {
                    $r=$pdo->query("update `$tbl[product]` p set `icons`=if(replace(`icons`,'@$val@','') = '', replace(`icons`,'@$val@',''), replace(`icons`,'@$val@','@')) where no='{$data['no']}'");
                }
            break;
        }

        $log->createLog(
            $tbl['product'],
            (int) $data['no'],
            'name',
            $data,
            $pdo->assoc("select no, name, icons from {$tbl['product']} where no=?", array($data['no']))
        );
	}

	$ems=($r) ? '정상 처리되었습니다.' : '처리된 내역이 없습니다.';
	alert($ems);

?>
<script type="text/javascript">
	parent.iconConfig.close();
    parent.removeLoading();
</script>