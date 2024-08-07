<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  위시리스트 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	memberOnly(1, "");

	$sql = "select p.*, w.`no` as wno, w.`pno`, w.`member_no`, w.`reg_date` from `".$tbl['product']."` p inner join `$tbl[wish]` w on p.`no`=w.`pno` where w.`member_no`='$member[no]' and p.`stat`!='4' order by w.`no` desc";

	function wishList($imgn=3, $w=10, $h=10, $_optdemo="") {
		global $tbl, $wishRes, $pdo;
		$data = $wishRes->current();
        $wishRes->next();
		if($data == false) return false;

		$data = shortCut($data);
		$data = prdOneData($data, $w, $h, $imgn);
		$data['sell_prc_str'] = $data['sell_prc'];
		if($data['sell_prc_consultation'] != '') {
			$data['sell_prc_str'] = $data['sell_prc'];
			if($cfg['currency_position'] == 'F') $data['sell_prc_str'] = $cfg['currency'].' '.$data['sell_prc_str'];
			else if($cfg['currency_position'] == 'B') $data['sell_prc_str'] .= ' '.$cfg['currency'];
		}

		$data['option'] = "<input type=\"hidden\" name=\"pno[".$data['wno']."]\" value=\"".$data['hash']."\">";
		if($data['stat'] == 2 && ($data['ea_type'] == 1 || $data['ea_type'] == 2 || ($data['ea_type'] == 3 && $data['ea'] > 0))){
			$_opt_sql = $pdo->iterator("select * from `$tbl[product_option_set]` where `stat`=2 and `pno`='$data[parent]' order by `sort`");
			$GLOBALS['prd']['ea_type'] = $data['ea_type']; // 품절된 옵션 표시
			$odx = 0;
            foreach ($_opt_sql as $_opt) {
				$odx++;
				$_opt['otype'] = ($_opt['otype'] == '4B') ? '4B' : '2A';
				$data['option'] .= printOption($_opt, $odx,'',1, '', $data['wno']).$_optdemo;
			}
		}
		if($data['stat'] == 3) $data['sold_out'] = "out";

		$GLOBALS['widx']++;
		$GLOBALS['prd']['parent'] = $data['pno'];
		return $data;
	}

	common_header();
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>