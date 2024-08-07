<?PHP

    $w = $biz_w = '';

	$qs_without_row = makeQueryString(true, 'page', 'row');
	$qs_without_sort = makeQueryString(true, 'page', 'sort');
	$sort = numberOnly($_GET['sort']);
	if($sort === '') $sort = 0;
	for ($i = 1; $i <= 2; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	//검색프리셋
	$spno = numberOnly($_GET['spno']);
	unset($_GET['spno']);
	if($spno) {
		$spdata = $pdo->assoc("select * from $tbl[search_preset] where no='$spno'");
		if($spdata['querystring']) {
			$_GET = array_merge($_GET, json_decode($spdata['querystring'], true));
		}
	}

	$_msort=array('`reg_date` desc','', 'substr(withdraw_content,-10)', 'substr(withdraw_content,-10) desc');
	if(!$sort || !$_msort[$sort]) $sort=0;

	$withdraw = addslashes($_GET['withdraw']);
	if(!$withdraw) {
		$w.=" and x.`withdraw` in ('N', 'D1')";
	}
	else {
		$w.=" and `withdraw`='Y'";
	}

	$date1 = $_GET['date1'];
	$date2 = $_GET['date2'];
	$all_date = $_GET['all_date'];
	if(!$date1 && !$date2) {
		$all_date = "Y";
		$date1=$date2=date("Y-m-d",$now);
	}

	$ord1 = $_GET['ord1'];
	$ord2 = $_GET['ord2'];
	$all_ord = $_GET['all_ord'];
	if(!$ord1 && !$ord2) {
		$all_ord = "Y";
	}

	$prc1 = $_GET['prc1'];
	$prc2 = $_GET['prc2'];
	$all_prc = $_GET['all_prc'];
	if(!$prc1 && !$prc2) {
		$all_prc= "Y";
	}

	$milage1 = $_GET['milage1'];
	$milage2 = $_GET['milage2'];
	$all_milage = $_GET['all_milage'];
	if(!$milage1 && !$milage2) {
		$all_milage = "Y";
	}

	$con1 = $_GET['con1'];
	$con2 = $_GET['con2'];
	$all_con = $_GET['all_con'];
	if(!$con1 && !$con2) {
		$all_con = "Y";
	}

	$with1 = $_GET['with1'];
	$with2 = $_GET['with2'];
	$all_with = $_GET['all_with'];
	if(!$with1 && !$with2) {
		$all_with = "Y";
	}

	if(!$all_date){
		$_date1 = strtotime($_GET['date1']);
		$_date2 = strtotime($_GET['date2'])+86399;
		$w .= " and reg_date >= '$_date1'";
		$w .= " and reg_date <= '$_date2'";
	}

	if(fieldExist($tbl['member'],"mobile")) {
		$mobile = $_GET['mobile'];
		if(is_array($mobile) && count($mobile) > 0){
			$tmp = array();
			foreach($mobile as $key => $val) {
				$val = addslashes($val);
				$tmp[] = "'$val'";
			}
			$tmp = implode(',', $tmp);
			$w .= " and mobile in ($tmp)";
		} else {
			$mobile = array();
		}
	}

	$cfg['member_local_cut'] = (_BASE_CHARSET_ == 'utf-8') ? 4 : 2;
	$local = addslashes(trim($_GET['local']));
	if($local) {
		if ($local == '충청북도' || $local == '충북') {
			$w .= " and (addr1 like '충청북도%' or addr1 like '충북%')";
		} else if ($local == '충청남도' || $local == '충남') {
		    $w .= " and (addr1 like '충청남도' or addr1 like '충남%')";
		} else if ($local == '전라북도' || $local == "전북") {
		    $w .= " and (addr1 like '전라북도' or addr1 like '전북%')";
		} else if ($local == '전라남도' || $local == '전남') {
		    $w .= " and (addr1 like '전라남도' or addr1 like '전남%')";
		} else if ($local == '경상북도' || $local == '경북') {
		    $w .= " and (addr1 like '경상북도' or addr1 like '경북%')";
		} else if ($local == '경상남도' || $local == '경남') {
		    $w .= " and (addr1 like '경상남도%' or addr1 like '경남%')";
		} else {
		    $local2 = cutStr($local, $cfg['member_local_cut'], '');
			$w .= " and addr1 like '$local2%'";
		}
	}
	$age = numberOnly($_GET['age']);
	if($age){
		$st=date("Y",$now)-($age*10)-10;
		$fn=$st+10;
		if($cfg[join_jumin_use] != "N") $w .= " and (1900+(left(`jumin`, 2))<=$fn and 1900+(left(`jumin`, 2))>$st)";
		elseif($cfg[join_jumin_use] == "N" && $cfg[join_birth_use] == "Y") $w .= " and (left(`birth`,4)<=$fn and left(`birth`,4)>$st)";
	}
	$sex = addslashes(trim($_GET['sex']));
	if($sex) {
		$_sex=array(1=>"남", 2=>"여");
		if($cfg[join_jumin_use] != "N") $w .= " and mid( jumin, 8, 1)='$sex'";
		elseif($cfg[join_jumin_use] == "N" && $cfg[join_sex_use] == "Y") $w .= " and (`sex`='$_sex[$sex]')";
	}

	if($cfg['use_whole_mem'] == "Y") {
		$whole_mem = addslashes($_GET['whole_mem']);
		if($whole_mem) {
			$w.=" and `whole_mem`='$whole_mem'";
		}
	}

    if ($scfg->comp('join_14_limit', 'B') == true) { // 14세 이하 가입 승인 여부 체크
        $under14 = $_GET['under14'];
        if (empty($under14) == false) {
            $under14 = ($under14 == 'Y') ? 'Y' : 'N';
            $w .= " and 14_limit='Y' and 14_limit_agree='$under14'";
        }
    }

    $buniness = $_GET['buniness'];
    if (empty($buniness) == false) {
        $buniness = ($buniness == 'Y') ? 'Y' : 'N';
        $w .= " and x.level=8";
        $biz_w .= " and biz.auth='$buniness'";
    }

	$mailing = addslashes($_GET['mailing']);
	if($mailing) {
		$w.=" and `mailing`='$mailing'";
	}

	$sms = addslashes($_GET['sms']);
	if($sms) {
		$w .= " and `sms` = '$sms'";
	}

	$_mng_sset=mySearchSet("membersearch");

	$search_type = addslashes($_GET['search_type']);
	if(!$search_type) $search_type=$_mng_sset['search'];
	unset($_mng_sset);

	$s_group = $_GET['s_group'];
	if($s_group && is_array($s_group) == false) {
		$s_group = array(numberOnly($s_group));
	}

	$s_group = ($s_group) ? $s_group : array();
	if(count($s_group)) {
		$tmp = implode(',', numberOnly($s_group));
		if($tmp) {
			$w .= " and x.level in ($tmp)";
		}
	}

	if(!$all_ord) {
		$ord1 = numberOnly($ord1);
		$ord2 = numberOnly($ord2);
		if($ord1 && $ord2) $w .= " and `total_ord` between $ord1 and $ord2";
		elseif(isset($ord1)) $w .= " and `total_ord` >= '$ord1'";
		elseif(isset($ord2)) $w .= " and `total_ord` <= '$ord2'";
	}

	if(!$all_prc) {
		$prc1 = numberOnly($prc1);
		$prc2 = numberOnly($prc2);
		if($prc1 > 0 && $prc2 > 0) $w .= " and `total_prc` between $prc1 and $prc2";
		elseif($prc1 > 0) $w .= " and `total_prc` >= '$prc1'";
		elseif($prc2 > 0) $w .= " and `total_prc` <= '$prc2'";
	}

	if(!$all_milage) {
		$milage1 = numberOnly($milage1);
		$milage2 = numberOnly($milage2);
		if($milage1 && $milage2) $w .= " and `milage` between $milage1 and $milage2";
		elseif($milage1) $w .= " and `milage` >= '$milage1'";
		elseif($milage2) $w .= " and `milage` <= '$milage2'";
	}

	if(!$all_con) {
		if($con1 && $con2) $w .= " and `total_con` between $con1 and $con2";
		elseif($con1) $w .= " and `total_con` >= '$con1'";
		elseif($con2) $w .= " and `total_con` <= '$con2'";
	}

	if(!$all_with) {
		if($with1 && $with2) $w .= " and FROM_UNIXTIME(substr(`withdraw_content`, -10),'%Y-%m-%d') between '$with1' and '$with2'";
		elseif($with1) $w .= " and FROM_UNIXTIME(substr(`withdraw_content`, -10),'%Y-%m-%d') >= '$with1'";
		elseif($with2) $w .= " and FROM_UNIXTIME(substr(`withdraw_content`, -10),'%Y-%m-%d') <= '$with2'";
	}

	$lastcon1 = date('Y-m-d', strtotime('-1 weeks'));
	$lastcon2 = date('Y-m-d', strtotime('-15 days'));
	$lastcon3 = date('Y-m-d', strtotime('-30 days'));
	$lastcon4 = date('Y-m-d', strtotime('-3 months'));
	$lastcon5 = date('Y-m-d', strtotime('-6 months'));
	$lastcon6 = date('Y-m-d', strtotime('-1 years'));
	$lastcon = addslashes($_GET['lastcon']);
	if($lastcon) {
		$_lastcon = strtotime($lastcon);
		$w .= " and last_con <= '$_lastcon'";
	}
	for($i = 1; $i <= 6; $i++) {
		${'lastcon_btn'.$i} = (${'lastcon'.$i} == $lastcon) ? 'blue' : 'gray';
	}

	$_search_type['name']='이름';
	$_search_type['member_id']='아이디';
	$_search_type['email']='이메일';
	$_search_type['addr1']='주소';
	$_search_type['addr2']='상세 주소';
	$_search_type['phone']='전화번호';
	$_search_type['cell']='휴대폰';
	$_search_type['pno']='상품코드';
	if($cfg['join_birth_use'] == "Y") $_search_type['birth']='생일';

	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str!="") {
		if ($search_type == "birth") $w.=" and substring(`birth`,6,5) = '$search_str'";
		if ($search_type == "pno") {
			$sellers = $pdo->iterator("select `member_no` from `$tbl[order]` o inner join `$tbl[order_product]` p using(`ono`) where `pno` = '$search_str' and `member_no` > 0 and p.`stat` between 1 and 5 group by `ono`");
            foreach ($sellers as $smno) {
				$_str .= ",$smno[member_no]";
			}
			$_str = preg_replace("/^,|,$/", "", $_str);
			if (!$_str) $_str = "0";
			$w .= " and `no` in ($_str)";
		}
		else $w.=" and `$search_type` like '%$search_str%'";
	}

	$_blacklist[0]="일반회원";
	$_blacklist[1]="블랙리스트회원";

	$blacklist = numberOnly($_GET['blacklist']);
	if($_blacklist[$blacklist]) $w .= " and `blacklist`='$blacklist'";

	$conversion_s = $_GET['conversion_s'];
	if(is_array($conversion_s)) {
		foreach($conversion_s as $key => $val) {
			$val = addslashes($val);
			$_w[] = "conversion like '%@$val%'";
		}
		$w .= " and (".implode(" or ", $_w).")";
	}

	$login_type = $_GET['login_type'];
	if(is_array($login_type)) {
		foreach($login_type as $key => $val) {
            if ($val == 'n') $_w2[] = "login_type=''";
			else $_w2[] = "`login_type` like '%@$val%'";
		}
		$w .= " and (".implode(" or ", $_w2).")";
	}

	// 특별 회원그룹
	$mchecker = array();
	$mc = $_GET['mc'];
	if(is_array($mc) == false) $mc = array();
	if(isTable($tbl['member_checker'])) {
		$mcres = $pdo->iterator("select no, name from `$tbl[member_checker]` order by name asc");
        foreach ($mcres as $mcdata) {
			$mchecker[$mcdata['no']] = stripslashes($mcdata['name']);
		}

		if(is_array($mc)) {
			foreach($mc as $val) {
				$w .= " and checker_$val='Y'";
			}
		}
	}

    // 선택 엑셀 다운로드
    if (isset($_GET['check_pno']) == true) {
        $check_pno = trim(preg_replace('/[^0-9,]/', '', $_GET['check_pno']), ',');
        if ($check_pno) {
            $w .= " and no in ($check_pno)";
        }
    }

	$xls_query = makeQueryString('page', 'body');

	$sql_f = "from {$tbl['member']} x ";
    if ($biz_w) {
        $sql_f .= " inner join {$tbl['biz_member']} biz on x.no=biz.ref ";
        $w .= $biz_w;
    }
    $sql_f .= " where 1 $w";
	$sql="select * $add_f ".$sql_f." order by $_msort[$sort]";
	$sql_t="select count(*) ".$sql_f;

?>