<?PHP

	$_type_arr=array(1=>"ysm_accountid", 2=>"google_conversion_id", 3=>"auction_clickid_id");

	$fd=$_type_arr[$mkt_type];

	if($fd){
		include_once $engine_dir."/_engine/include/shop_detail.lib.php";
		$mkt_data=getWMDefault(array($fd));
	}

	if($mkt_data[$fd]) {
		if($mkt_type == 1){ // 오버추어 구매전환 스크립트 출력
			if($ord[ono] && $ord[total_prc]){

?>
<script language="JavaScript">
	window.ysm_customData = new Object();
	window.ysm_customData.conversion = "transId=<?=$ord[ono]?>,currency=,amount=<?=str_replace(",","",$ord[total_prc])?>";
	var ysm_accountid = "<?=$mkt_data[$fd]?>";
	document.write("<SCR" + "IPT language='JavaScript' type='text/javascript' " + "SRC=//" + "srv1.wa.marketingsolutions.yahoo.com" + "/script/ScriptServlet" + "?aid=" + ysm_accountid + "></SCR" + "IPT>");
</script>
<?
			}
		}elseif($mkt_type == 3){ // 옥션쇼핑 구매전환
			$_auction_paytype=array(1=>"CARD", 2=>"CASH", 3=>"PONT");
			$_auction_prd_sql = $pdo->iterator("select `pno`, `sell_prc` from `$tbl[order_product]` where `ono`='$ord[ono]'");
			$_auction_mcode=$_auction_cost="";
            foreach ($_auction_prd_sql as $_auction_prd_data) {
				$_auction_mcode .= $_auction_mcode ? ",".$_auction_prd_data[pno] : $_auction_prd_data[pno];
				$_auction_cost .= $_auction_cost ? ",".$_auction_prd_data[sell_prc] : $_auction_prd_data[sell_prc];
			}
			$_auction_send_url="http://openshopping.auction.co.kr/ordercomp.aspx?clickid=".$mkt_data[$fd]."&mcode=".$_auction_mcode."&cost=".$_auction_cost."&pay_type=".$_auction_paytype[$ord[pay_type]];
			$_auction_fp=@fopen($_auction_send_url, $r);
			@fclose($_auction_fp);
		}
	}
?>