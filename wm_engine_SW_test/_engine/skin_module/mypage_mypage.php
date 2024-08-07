<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 메인
	' +----------------------------------------------------------------------------------------------+*/

	$_mm_arr=array("order", "member", "wish", "milage", "emoney", "point", "coupon", "1to1", "withdraw");
	foreach($_mm_arr as $key=>$val){
		$_replace_code[$_file_name]["mypage_main_".$val]=getModuleContent("mypage_main_".$val);;
	}
	unset($_mm_arr);

	if($member['no']){
		$_tmp="";
		$nowTime=time();//현재시간
		$threeMonthTime=strtotime("-3 month");//3개월전
		$_line=getModuleContent("mypage_3ord_list");
		while($ord=orderList(" and `date1` <= $nowTime and `date1` >= $threeMonthTime")) {
			$ord['oidx']=$oidx;
			$ord['ono']="<a href=\"".$ord['link']."\">".$ord['ono']."</a>";
			if($ord['dlv_code']) $ord['stat']="<a href=\"".$dlv['url']."\" target=\"_blank\">".$ord['stat']."</a>";
			$ord['pay_prc'] = parsePrice($ord['pay_prc'], true);
			$ord['pay_r_prc'] = showExchangeFee($ord['pay_prc']);
			$_tmp .= lineValues("mypage_3ord_list", $_line, $ord);
		}
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['mypage_3ord_list']=$_tmp;

	}else{
		if(!$rURL || $rURL==1) $rURL=getURL();
		$_return_script="<script type='text/javascript'>location.href='".$root_url."/member/login.php?rURL=".$rURL."';</script>";
		$_replace_code[$_file_name]['mypage_member_login']=$_return_script;

		if(checkCodeUsed('비회원로그인')) {
			exit($_return_script);
		}
	}

	$_member_order=$pdo->row("select count(*) from `{$tbl['order']}` where `stat` not in (11, 31, 32) and `member_no`='{$member['no']}' ");
	$_replace_code[$_file_name]['member_order']=$_member_order;

	$_member_wish = $pdo->row("select count(*) from {$tbl['wish']} w inner join {$tbl['product']} p on w.pno=p.no where w.member_no='{$member['no']}' and p.stat in (2, 3)");
	$_replace_code[$_file_name]['member_wish']=$_member_wish;

?>