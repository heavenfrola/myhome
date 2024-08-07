<?PHP

    /**
     * 상품 가격 일괄 변경
     **/

	set_time_limit(0);
	ini_set('memory_limit', -1);

	use Wing\API\Kakao\KakaoTalkPay;
    use Wing\common\WorkLog;

	checkBasic();

	$where = $_POST['where'];
	$nums = $_POST['nums'];
	$exec = $_POST['exec'];

    $prd_join = '';

    $log = new WorkLog();

	// 상품 조건
	if($where==1) {
		$w="";
		$_nums = numberOnly(explode('@', trim($nums, '@')));
		$_nums = implode(',', $_nums);
		$w .= " and p.no in ($_nums)";
	}
	else {
		$w=stripslashes($_POST['w']);

        if (preg_match('/pr\.pgrp_no/', str_replace('`', '', $w)) == true) {
            $prd_join .= " inner join {$tbl['promotion_pgrp_link']} pr on p.no=pr.pno";
        }
	}

	if($exec=="milage") {
		$o1="sell_prc";
		$o2="milage";
	}else if($exec=="sell_prc") {
		$o1 = $_POST['o1'];
		$o2 = $_POST['o2'];
		$o3 = $_POST['o3'];
		$replace_prc = numberOnly($_POST['replace_prc']);
		$prc_chg_type = $_POST['prc_chg_type'];
	}

	$p1 = $_POST['p1'];
	$p2 = $_POST['p2'];
	$p3 = $_POST['p3'];
	$r1 = $_POST['r1'];
	$r2 = $_POST['r2'];

	$sql="select p.* from `$tbl[product]` p $prd_join where 1 $w";
	$res = $pdo->iterator($sql);

    $productIds = array();
    foreach ($res as $data) {
		if($data[content2]=='wm_sc') {
			$data=get_info($tbl[product],"no",$data[content1]);
		}

		$r=$data[$o1];
		if($p1 != null) {
			if($p2 == 1) { // 퍼센트
				$p = ($p1 == 0) ? 0 : $r*($p1/100);
			} else { // 원
				$p = $p1;
			}

			if($exec=="milage") {
				$r=$p;
			}
			else {
				if($p3=="+") $r+=$p;
				else $r-=$p;
			}

			// 절사
			$r/=$r1;
			if($r2==1) {// 내림
				$r=floor($r)*$r1;
			}
			elseif($r2==3) {// 올림
				$r=ceil($r)*$r1;
			}
			else {// 반올림
				$r=round($r)*$r1;
			}
		}

		if($exec == 'stat') {
			// 노출 위치
			$stat_q = '';
			foreach(array('lst', 'dtl', 'sch') as $key) {
				$val = $_POST['perm_'.$key];
				if(empty($val) == false) {
					if($val != 'Y') $val = 'N';
					$stat_q .= ", perm_$key='$val'";
				}
			}

			$stat = numberOnly($_POST['change_stat']); // 상태변경
			if(empty($stat) == false && $data['stat'] != $stat) {
				$stat_q .= ", stat='$stat'";
			}

			if($stat_q) {
				$stat_q = substr($stat_q, 1);
				$_r = $pdo->query("update `{$tbl['product']}` set $stat_q where `no`='{$data['no']}' or (wm_sc='{$data['no']}' and `stat` != 5)");
				if($data['stat'] != $stat && $_r) prdStatLogw($data['no'], $stat, $data['stat']);
			}
		}

		if($exec!="stat") {
			if($prc_chg_type == '2') {
				$sql = "update `$tbl[product]` p set `$o3`='$replace_prc' where prd_type='1' and `no`='$data[no]' or `wm_sc`='$data[no]'";
			} else {
				$sql = "update `$tbl[product]` p set `$o2`='$r' where prd_type='1' and `no`='$data[no]' or `wm_sc`='$data[no]'";
			}
			$pdo->query($sql);
		}

        $log->createLog(
            $tbl['product'],
            (int) $data['no'],
            'name',
            $data,
            $pdo->assoc("select * from {$tbl['product']} where no=?", array($data['no']))
        );

        $productIds[] = $data['hash'];
	}

    // 상품 정보 즉시 동기화
    if ($scfg->comp('use_talkpay', 'Y') == true) {
        if (count($productIds) > 0) {
            $kko = new KakaoTalkPay($scfg);
            $kko->syncProduct($productIds);
        }
    }

	msg("모두 변경하였습니다","reload","parent");

	exit;

?>