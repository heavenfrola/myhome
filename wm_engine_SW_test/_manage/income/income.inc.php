<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 매출통계 공통 필드
	' +----------------------------------------------------------------------------------------------+*/

	$add_cpn = '';
	if(fieldExist($tbl['order_product'], 'sale7')) {
		$add_cpn = '+sale7';
	}

	$string_sales = getOrderSalesField('', '+');

	$income_field = "
				sum(if(stat between 1 and 5 and mobile='N', 1, 0)) as pc_order_cnt,
				sum(if(stat between 1 and 5 and mobile='Y', 1, 0)) as mb_order_cnt,
				sum(if(stat between 1 and 5 and mobile='A', 1, 0)) as ap_order_cnt,
				sum(if(stat = 1 and mobile='N', 1, 0)) as pc_1_cnt,
				sum(if(stat = 1 and mobile='N', pay_prc+point_use, 0)) as pc_1_prc,
				sum(if(stat = 1 and mobile='Y', 1, 0)) as mb_1_cnt,
				sum(if(stat = 1 and mobile='Y', pay_prc+point_use, 0)) as mb_1_prc,
				sum(if(stat = 1 and mobile='A', 1, 0)) as ap_1_cnt,
				sum(if(stat = 1 and mobile='A', pay_prc+point_use, 0)) as ap_1_prc,
				sum(if((stat between 2 and 5 or stat=17) and mobile='N', dlv_prc, 0)) as pc_dlv_prc,
				sum(if((stat between 2 and 5 or stat=17) and mobile='Y', dlv_prc, 0)) as mb_dlv_prc,
				sum(if((stat between 2 and 5 or stat=17) and mobile='A', dlv_prc, 0)) as ap_dlv_prc,
				sum(if(stat between 2 and 5 and mobile='N', ($string_sales), 0)) as pc_sale_prc,
				sum(if(stat between 2 and 5 and mobile='Y', ($string_sales), 0)) as mb_sale_prc,
				sum(if(stat between 2 and 5 and mobile='A', ($string_sales), 0)) as ap_sale_prc,
				sum(if(stat between 2 and 5 and mobile='N', sale5 $add_cpn, 0)) as pc_cpn_prc,
				sum(if(stat between 2 and 5 and mobile='Y', sale5 $add_cpn, 0)) as mb_cpn_prc,
				sum(if(stat between 2 and 5 and mobile='A', sale5 $add_cpn, 0)) as ap_cpn_prc,
				sum(if(stat between 2 and 5 and mobile='N', milage_prc, 0)) as pc_milage_prc,
				sum(if(stat between 2 and 5 and mobile='Y', milage_prc, 0)) as mb_milage_prc,
				sum(if(stat between 2 and 5 and mobile='A', milage_prc, 0)) as ap_milage_prc,
				sum(if(stat between 2 and 5 and mobile='N', emoney_prc, 0)) as pc_emoney_prc,
				sum(if(stat between 2 and 5 and mobile='Y', emoney_prc, 0)) as mb_emoney_prc,
				sum(if(stat between 2 and 5 and mobile='A', emoney_prc, 0)) as ap_emoney_prc,
				sum(if(pay_type=2 and stat between 2 and 5 and mobile='N', pay_prc+point_use, 0)) as pc_bank_prc,
				sum(if(pay_type=2 and stat between 2 and 5 and mobile='Y', pay_prc+point_use, 0)) as mb_bank_prc,
				sum(if(pay_type=2 and stat between 2 and 5 and mobile='A', pay_prc+point_use, 0)) as ap_bank_prc,
				sum(if(stat between 2 and 5 and mobile='N', pay_prc+point_use, 0)) as pc_pay_prc,
				sum(if(stat between 2 and 5 and mobile='Y', pay_prc+point_use, 0)) as mb_pay_prc,
				sum(if(stat between 2 and 5 and mobile='A', pay_prc+point_use, 0)) as ap_pay_prc,
				sum(if(stat = 13 and date2 = 0 and mobile='N', 1, 0)) as pc_cancel1_cnt,
				sum(if(stat = 13 and date2 = 0 and mobile='Y', 1, 0)) as mb_cancel1_cnt,
				sum(if(stat = 13 and date2 = 0 and mobile='A', 1, 0)) as ap_cancel1_cnt,
				sum(if((stat = 15 or (stat = 13 and date2 > '0')) and mobile='N', 1, 0)) as pc_cancel2_cnt,
				sum(if((stat = 15 or (stat = 13 and date2 > '0')) and mobile='Y', 1, 0)) as mb_cancel2_cnt,
				sum(if((stat = 15 or (stat = 13 and date2 > '0')) and mobile='A', 1, 0)) as ap_cancel2_cnt,
				sum(if(stat in (17, 19) and mobile='N', 1, 0)) as pc_cancel3_cnt,
				sum(if(stat in (17, 19) and mobile='Y', 1, 0)) as mb_cancel3_cnt,
				sum(if(stat in (17, 19) and mobile='A', 1, 0)) as ap_cancel3_cnt,
				sum(if(stat between 2 and 5 and mobile='N', repay_prc, 0)) as pc_part_repay_prc,
				sum(if(stat between 2 and 5 and mobile='Y', repay_prc, 0)) as mb_part_repay_prc,
				sum(if(stat between 2 and 5 and mobile='A', repay_prc, 0)) as ap_part_repay_prc
	";

?>