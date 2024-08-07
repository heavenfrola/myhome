<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 레이어
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	common_header();

	$cart_sbscr_cnt = $pdo->row("select count(*) from `{$tbl['sbscr_cart']}` where 1 ".mwhere());

	// 공휴일처리
	$delivery_max_limit = strtotime("+2 months", $now);
	$hres = $pdo->iterator("select no, timestamp from $tbl[sbscr_holiday] where is_holiday='Y' and timestamp between $now and $delivery_max_limit");

	$_tmp_file_name = '/shop/subscription.php';
?>
<script type="text/javascript">
var cart_sbscr_cnt='<?=$cart_sbscr_cnt?>';

var holyday_input = new Array();
<?php foreach ($hres as $hdata) {?>
holyday_input[<?=$hdata['no']?>] = '<?=$hdata['timestamp']?>';
<?}?>

function liveCallExec(data) {
	$.ajax({
		type: 'post',
		url: root_url+'/main/exec.php?exec_file=shop/sbscr.exe.php',
		data: data,
		dataType : 'html',
		success: function(r) {
			var json = $.parseJSON(r);

			$(".sbscr_sell_prc").html(json.detail_sbscr_sell_prc);
			$(".sbscr_dlv_prc").html(json.detail_sbscr_dlv_prc);
			$(".sbscr_ea_sell_prc").html(json.detail_sbscr_ea_prc);
			$(".sbscr_dlv_cnt").html(json.detail_sbscr_dlv_cnt);
			$(".sbscr_total_prc").html(json.detail_sbscr_pay_prc);
			$(".sbscr_info_option").html(json.detail_sbscr_option_text);
			$("#sbscr_date_list").val(json.date_list);
			$("#sbscr_start_date").val(json.detail_start_date);
			$("#sbscr_end_date").val(json.detail_end_date);
			$("#start_date_text").html(json.detail_start_date);
			$("#end_date_text").html(json.detail_end_date);
			$("#start_yoil_text").html(json.detail_sbscr_start_yoil);
			$("#end_yoil_text").html(json.detail_sbscr_end_yoil);
			$('#sbscr_start_box').datepicker('setDate', json.detail_start_date);
			$('#sbscr_end_box').datepicker('setDate', json.detail_end_date);
		}
	});
}
</script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>