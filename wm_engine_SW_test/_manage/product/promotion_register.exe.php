<?php
	printAjaxHeader();

	$exec = $_POST['exec'];

	if($exec=='delete') {
		$check_pgrpno = numberOnly($_POST['check_pgrpno']);
		foreach($check_pgrpno as $key=>$val) {
			$pdo->query("delete from $tbl[promotion_pgrp_list] where no='$val'");
			$pdo->query("delete from $tbl[promotion_pgrp_link] where pgrp_no='$val'");
			$pdo->query("delete from $tbl[promotion_link] where pgrp_no='$val'");
		}
		msg("프로모션 상품그룹이 삭제되었습니다.","reload","parent");
		exit;
	}else if($exec=='pr_delete') {
		$check_prno = numberOnly($_POST['check_prno']);
		$all_prm = count($check_prno);
		foreach($check_prno as $key=>$val) {
			$pdo->query("delete from $tbl[promotion_list] where no='$val'");
			$pdo->query("delete from $tbl[promotion_link] where prm_no='$val'");
		}
		msg($all_prm."개의 프로모션 기획전을 삭제하였습니다","reload","parent");
	}else if($exec=='pr_sort') {
		$_no = numberOnly($_POST['no']);
		foreach($_no as $key=>$val) {
			$sort = $key+1;
			$pdo->query("update $tbl[promotion_list] set `sort`='$sort' where no='$val'");
		}
		echo "OK";
		exit;
	}else if($exec=='toggle') {
		$prno = numberOnly($_POST['prno']);
		$use_yn = $pdo->row("select use_yn from $tbl[promotion_list] where no='$prno'");
		$use_yn = ($use_yn == 'Y') ? 'N' : 'Y';
		$pdo->query("update $tbl[promotion_list] set use_yn='$use_yn' where no='$prno'");

		header('Content-type:application/json;');
		exit(json_encode(array(
			'changed' => $use_yn
		)));
	}else if($exec=='up_stat') {
		$_pno = numberOnly($_POST['pno']);
		$stat = numberOnly($_POST['stat']);
		foreach($_pno as $key=>$val) {
			$pdo->query("update $tbl[product] set `stat`='$stat' where no='$val'");
		}
		exit;
	}

	$reg_date = date("Y-m-d H:i:s", $now);
	$prno = numberOnly($_POST['prno']);
	$promotion_nm = addslashes($_POST['promotion_nm']);
	$content = addslashes(strip_script($_POST['content']));
	$mcontent = addslashes(strip_script($_POST['mcontent']));
	$neko_id = $_POST['neko_id'];
	$m_neko_id = $_POST['m_neko_id'];
	$use_m_content = addslashes($_POST['use_m_content']);
	$pgrp_merge = addslashes($_POST['pgrp_merge']);
	$admin_no = $admin['no'];
	$admin_id = $admin['admin_id'];
	$use_yn = ($_POST['use_yn'] == 'Y') ? 'Y' : 'N';
	$period_type = $_POST['period_type'];
	if($period_type=="Y") {
		$date_start = $_POST['ts_dates'].' '.$_POST['ts_times'].':00:00';
		$date_end = $_POST['ts_datee'].' '.$_POST['ts_timee'].':00:59';
	}

	if($prno) {
		$pdo->query("update $tbl[promotion_list] set `promotion_nm`='$promotion_nm', `content`='$content', `m_content`='$mcontent', `use_m_content`='$use_m_content', `period_type`='$period_type', `date_start`='$date_start', `date_end`='$date_end', `use_yn`='$use_yn', `admin_no`='$admin_no', `admin_id`='$admin_id', `reg_date`='$reg_date' where no='$prno'");
	}else {
		$pdo->query("insert into $tbl[promotion_list] (`promotion_nm`, `content`, `m_content`, `use_m_content`, `period_type`, `date_start`, `date_end`, `use_yn`, `sort`, `admin_no`, `admin_id`, `reg_date`) VALUES ('$promotion_nm', '$content', '$mcontent', '$use_m_content', '$period_type', '$date_start', '$date_end', '$use_yn', '1', '$admin_no', '$admin_id', '$reg_date')");
        $prno = $pdo->lastInsertId();
        $pdo->query("update $tbl[promotion_list] set sort=sort+1 where no!='$prno'");
	}

	if($pgrp_merge) {
		$sort = 0;
		$_pgrp_merge = explode(",", $pgrp_merge);
		foreach($_pgrp_merge as $key=>$val) {
			if($val) {
				$sort++;
				$plno = $pdo->row("select no from $tbl[promotion_link] where prm_no='$prno' and pgrp_no='$val'");
				if($plno) {
					$pdo->query("update $tbl[promotion_link] set `sort`='$sort' where no='$plno'");
				}else {
					$pdo->query("insert into $tbl[promotion_link] (`prm_no`, `pgrp_no`, `sort`) VALUES ('$prno', '$val', '$sort')");
				}
			}
		}
	}

	include_once $engine_dir."/board/include/lib.php";

	$real_neko_id = "promotion_".$prno;
	if(!$_POST['prno']) $pdo->query("update `$tbl[neko]` set `neko_id` = '$real_neko_id' where `neko_id` = '$neko_id'");
	neko_lock($real_neko_id);
	$real_m_neko_id = "mpromotion_".$prno;
	if(!$_POST['prno']) $pdo->query("update `$tbl[neko]` set `neko_id` = '$real_m_neko_id' where `neko_id` = '$m_neko_id'");
	neko_lock($real_m_neko_id);

	msg("프로모션 기획전이 등록/수정 완료되었습니다.", './?body=product@promotion_list', 'parent');
	exit;

?>