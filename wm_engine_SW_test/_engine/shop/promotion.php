<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	// 기본 프로모션
	$pno = numberOnly($_GET['pno']);
	$datetime = date('Y-m-d H:i:s');
	if(!$pno) {
		$pno = $pdo->row("
			select no from
			$tbl[promotion_list]
			where use_yn='Y'
			order by sort asc limit 1
		");
	}

	// 프로모션 정보 호출
	$pdata = $pdo->assoc("select * from $tbl[promotion_list] where no='$pno'");
	if($pno > 0 && !$pdata['no']) {
		msg(__lang_common_error_nodata__, 'back');
	}
	if($pdata['use_yn']=="N") msg(__lang_shop_promotion_usen__, $root_url);

	$pdata['status'] = null;
	$pdata['promotion_nm'] = stripslashes($pdata['promotion_nm']);
	$pdata['content'] = stripslashes($pdata['content']);
	$pdata['m_content'] = ($pdata['use_m_content']) ? stripslashes($pdata['m_content']) : $pdata['content'];
	if($pdata['period_type'] == 'N') {
		$pdata['status'] = 'progress';
		$pdata['period_s'] = __lang_shop_promotion_ing__;
	} else {
		$pdata['period_s']  = date('Y-m-d H:i', strtotime($pdata['date_start']));
		$pdata['period_e'] .= date('Y-m-d H:i', strtotime($pdata['date_end']));

		if($pdata['date_start'] > $datetime) {
			$pdata['status'] = 'standby';
		}
		else if($pdata['date_end'] < $datetime) {
			$pdata['status'] = 'finished';
			$pdata['period_s'] = __lang_shop_promotion_end__;
			$pdata['period_e'] = '';
		}
	}

	// 스킨 로딩
	common_header();
	include_once $engine_dir."/_engine/common/skin_index.php";

?>