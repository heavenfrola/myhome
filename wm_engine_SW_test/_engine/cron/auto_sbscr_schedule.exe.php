<?php

	/*
	 * 정기배송 스케줄 생성 페이지
	 */

	$sql = "SELECT
				sp.*
			FROM
				$tbl[sbscr_product] as sp
			LEFT JOIN $tbl[sbscr] as s
			on sp.sbono=s.sbono
			WHERE
				sp.stat = 2
				AND sp.dlv_finish_date = '0000-00-00'
				AND s.pay_type = 23
				AND sp.`stop`='N'
			ORDER BY s.date1";
	$res = $pdo->iterator($sql);
    foreach ($res as $spdata) {
		$end_date = strtotime('+7 days', $now);

        $dlv_week = ($spdata['dlv_week']) ? explode('|', $spdata['dlv_week']) : array();
		$date_list = getsbscrDate(strtotime($spdata['dlv_start_date']), $end_date, $spdata['period'], $dlv_week);

        if(count($date_list)>0) {
			$insert_date = '';
			$tmp_data = '';
			$tmp_ssno = '';
			foreach($date_list as $key=>$val) {
				if($insert_date) continue;
				$_date = date('Y-m-d', $val);
				$ssno = $pdo->row("select no from $tbl[sbscr_schedule] where sbono='$spdata[sbono]' and `date`='$_date'");
				if(!$ssno) {
					$insert_date = $_date;
				} else {
					$tmp_data = $pdo->assoc("select * from $tbl[sbscr_schedule] where no='$ssno'");
					$tmp_ssno = $ssno;
				}
			}
		}

		if($insert_date) {
			$pdo->query("
				insert into $tbl[sbscr_schedule]
				(`sbono`, `date`, `date_org`, `product_cnt`, total_prc, prd_prc, dlv_prc)
				values
				('$spdata[sbono]', '$insert_date', '$insert_date', '$tmp_data[product_cnt]', '$tmp_data[total_prc]', '$tmp_data[prd_prc]', '$tmp_data[dlv_prc]')
			");
			$nschno = $pdo->lastInsertId();

			$sspres = $pdo->iterator("select * from $tbl[sbscr_schedule_product] where schno='$tmp_ssno'");
            foreach ($sspres as $sspdata) {
				$pdo->query("
					insert into $tbl[sbscr_schedule_product]
					(`schno`, `sbono`, sbpno, `pno`, partner_no, delivery_type, delivery_base, delivery_free_limit, delivery_fee, `stat`)
					values
					('$nschno', '$sspdata[sbono]', '$sspdata[sbpno]', '$sspdata[pno]', '$sspdata[partner_no]', '$sspdata[delivery_type]', '$sspdata[delivery_base]', '$sspdata[delivery_free_limit]', '$sspdata[delivery_fee]', '1')
				");
			}
		}
	}

?>