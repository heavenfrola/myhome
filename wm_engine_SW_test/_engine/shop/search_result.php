<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  검색결과 출력
	' +----------------------------------------------------------------------------------------------+*/

	function putSearchLog($keyword) {
		global $now,$tbl, $pdo;

		$key_check = iconv(_BASE_CHARSET_, _BASE_CHARSET_, $keyword);
		if($key_check != $keyword) return;

		$ymd=date("Ymd",$now);
		$keyword=trim(addslashes($keyword));
		if($_SESSION[last_keyword]==$keyword) {
			return;
		}
		else {
			$_SESSION[last_keyword]=$keyword;
		}
		$search_no = $pdo->row("select `no` from `$tbl[log_search]` where keyword='$keyword'");
		if($search_no > 0) {
			$pdo->query("update `$tbl[log_search]` set `hit`=`hit`+1 where `no`='$search_no'");
		}
		else {
			$pdo->query("INSERT INTO `$tbl[log_search]` ( `keyword` , `hit` ) VALUES ( '$keyword', '1')");
		}

		$s = strtotime(date('Y-m-d 00:00:00'));
		$e = $s+86399;
		$search_no = $pdo->row("select `no` from `$tbl[log_search_day]` where keyword='$keyword' and date between $s and $e");
		if($search_no) {
			$pdo->query("update `$tbl[log_search_day]`set `hit`=`hit`+1 where `no`='$search_no'");
		}
		else {
			$pdo->query("INSERT INTO `$tbl[log_search_day]` ( `date` , `keyword` , `hit` ) VALUES ( '$now', '$keyword', '1')");
		}
	}

	function searchRank($limit=10) {
		global $sr_res,$ridx,$tbl,$now, $pdo;
		if(!$sr_res) {
			$ymd1 = strtotime(date("Y-m-d 00:00:00",strtotime("-1 month")));
			$sr_res = $pdo->iterator("select sum(`hit`) as shit, `keyword` from `$tbl[log_search_day]` WHERE date between $ymd1 and $now group by `keyword` order by shit desc limit $limit");
		}
		$data = $sr_res->current();
        $sr_res->next();
		if($data == false) return false;

		$ridx++;
		$data['keyword']=stripslashes(strip_tags($data['keyword']));
		$data['keyword2']=urlencode($data['keyword']);
		return $data;
	}


	function cateSearchLoop($c,$deco1="",$deco2="") {
		global $cRes,$tbl,$prdWhere1,$search_str,$cate,$ctype, $pdo, $scfg;
		if(!$cRes) {
			if ($prdWhere1 && $scfg->comp('use_prd_perm', 'Y') == true) $prdWhere1 .= "and perm_sch='Y'";
			$cRes = $pdo->iterator("select distinct(`$c`) as cno from `$tbl[product]` p where 1 $prdWhere1");
		}
		$cate_no = $cRes->current();
		if($cate_no == false) {
            unset($cRes);
            return false;
        }

		$_cate = $pdo->assoc("select no, name from `$tbl[category]` where no='{$cate_no['cno']}' and `hidden` != 'Y'");
		$data['no'] = $_cate['no'];
		$data['name']=$_cate['name'];
		if($cate==$_cate['no']) {
			$data['name']=$deco1.$data['name'].$deco2;
		}
		$data['link'] = makeQueryString(true, 'page', 'cate', 'ctype').'&cate='.$_cate['no'].'&ctype='.$c;

		$data['total']=totalCatItem($prdWhere1." and `$c`='{$cate_no['cno']}'");

        $cRes->next();
		return $data;
	}

    // 페이스북 전환 API
    if ($scfg->comp('use_fb_conversion', 'Y') == true) {
        include __ENGINE_DIR__.'/_engine/promotion/fd_conversion_search.inc.php';
    }

?>