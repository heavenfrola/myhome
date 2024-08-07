<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문정보엑셀 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($exec){
		$_pg_type="order";
		include_once $engine_dir."/_manage/order/excel_set.php";
		msg($msg, "./?body=config@order_excel_config", "parent");
	}

	if($noexcel==false) {
		$file_name="주문_목록_".date("Y_m_d",$now);
		header( "Content-type: application/vnd.ms-excel;charset=KSC5601" );
		header( "Content-Disposition: attachment; filename=".$file_name.".xls" );
		header( "Content-Description: Wisamall Excel Data" );
	}

	if($_SESSION[admin_no] == "daum_how") include $engine_dir."/_manage/extension/daumhow/daum_list.php";
	else include $engine_dir."/_manage/order/order_list.php";
	include $engine_dir."/_manage/config/order_excel_config.php";

	if (!$xlsmode) $xlsmode = "order";

	if ($xlsmode == "product") {
		if ($ws_ok) {
			$ws2 = substr($ws2, 4);
			$ws2 = " and ($ws2)";
		}

		$sql = str_replace(" `", " b.`",$sql);
		$sql = str_replace("(`", "(b.`",$sql);
		$sql = str_replace("select * from b.`wm_order`","select b.*, a.`pno`,a.`stat` as `pstat`, a.`name` as `title`, a.`option`,a.`prd_type`,a.`buy_ea`,a.`sell_prc` from `$tbl[order_product]` a  inner join `$tbl[order]` b using (ono)",$sql);

		if (is_array($stat)) {
			$opw = implode(",", $stat);
			$opw = " and a.`stat` in ($opw)";
			$sql = str_replace("where 1", "where 1 $opw", $sql);
		}

		$sql2 = str_replace("b.*, a.`pno`,a.`stat` as `pstat`, a.`name` as `title`, a.`option`,a.`prd_type`,a.`buy_ea`,a.`sell_prc`","count(*)",$sql);
	}

	$sql = str_replace("`date1` desc", "`date1` asc", trim($sql));
	if($xlsmode == "product"){
		$sql = str_replace("`date1` asc", "`date1` asc, a.`no`", trim($sql));
	}

	$res = $pdo->iterator($sql);
	$idx = $pdo->row($sql2);
?>
<META HTTP-EQUIV= Pragma  CONTENT= no-cache >
<meta http-equiv="Content-Language" content="ko">
<META http-equiv="Content-Type" content="text/html; charset=euc-kr">
<style>
tr {
	mso-height-source:auto;
	mso-ruby-visibility:none;
}

br {
	mso-data-placement:same-cell;
}
</style>
<table border=1>
<?
if($cfg[ord_excel_title_use] != "N"){
?>
	<tr>
<?
	foreach($_ord_excel_fd_selected as $key=>$val){
		if($val == "bank_name" && $cfg[bank_name] != "Y") continue;
		if($val == "recom_member" && $_use[recom_member] != "Y") continue;
		if($xlsmode != "product" && ($val == "seller" || $val == "origin_name")) continue;
		if($val == "1") $ord_excel_fd[$val]="";
?>
		<td style="mso-number-format:'\@'" align="center"><?=$ord_excel_fd[$val]?></td>
<?
	}
?>
	</tr>
<?php
}
?>
<?php
	$idx_a=1;
    foreach ($res as $data) {
		$data['title'] = preg_replace('/<br(\s*\/)?>/', '', $data['title']);
		$data['title'] = preg_replace('/<p(\s*[^>]+)?>(.*)?<\/p>/', '$2', $data['title']);
?>
	<tr>
<?
		unset($prod);
		foreach($_ord_excel_fd_selected as $key=>$val){
			if($val == "pay_type"){
				$data[pay_type]=$_pay_type[$data[pay_type]];
				if($data[milage_prc]>0) $data[pay_type].="+적립금";
			}

			if ($val == "prd_prc" && $xlsmode == "product") $data[prd_prc] = $data[sell_prc];

			if ($val == "seller" || $val == "origin_name" || $val == "code" || $val == "origin_prc" || $val == 'pstat') {
				if ($xlsmode != "product") continue;
				if (!$prod) $prod = $pdo->assoc("select `seller`, `origin_name`, `code`, `origin_prc` from `$tbl[product]` where `no` = '$data[pno]' limit 1");
				$data[$val] = $prod[$val];
			}

			if ($val == 'big' && $xlsmode != 'product') continue;

			if($val == "option" && $data['option']){
				$data['option']=str_replace("<split_big>",",",$data['option']);
				$data['option']=str_replace("<split_small>",":",$data['option']);
			}

			if(($val == "title" || $val == "buy_ea") && $xlsmode == "order"){
				$data[title]="";
				$data[option]="";
				$data[buy_ea] = 0;
				if(is_array($stat)) {
					$opw = implode(",", $stat);
					$opw = " and `stat` in ($opw)";
				}
				$pres = $pdo->iterator("select * from `$tbl[order_product]` where `ono`='$data[ono]' $opw");
                foreach ($pres as $pdata) {
					$pdata['name'] = preg_replace('/<br(\s*\/)?>/', '', $pdata['name']);
					$pdata['name'] = preg_replace('/<p(\s*[^>]+)?>(.*)?<\/p>/', '$2', $pdata['name']);
					$pdata['option_str']="";

					// 옵션이 있을 경우
					if($pdata['option']) {
						$pdata['option_str']=str_replace("<split_big>",",",$pdata['option']);
						$pdata['option_str']=str_replace("<split_small>",":",$pdata['option_str']);
						$pdata['option_str']="- ".$pdata['option_str'];
					}

					$data[title].=" / $pdata[name] $pdata[option_str] ($pdata[buy_ea])";
					//$data[title].=" / $pdata[name]";
					$data[option] .= " / ".$pdata[option];
					$data[buy_ea] += $pdata[buy_ea];
				}
				$data[title]=substr($data[title],3);
				$data[option]=substr($data[option],3);
				$data[title] = str_replace("/","|",$data[title]);
			}

			if($val == "recom_member"){
				if($_use[recom_member] != "Y") continue;
				$data[recom_member]=($data[member_no]) ? $pdo->row("select `recom_member` from `$tbl[member]` where `no`='$data[member_no]' limit 1") : "";
			}
			if($val == "dlv_no"){
				$data[dlv_no]=($data[dlv_no]) ? $pdo->row("select `name` from `$tbl[delivery_url]` where `no`='$data[dlv_no]' limit 1") : "";
			}
			if($val == "bank_name" && $cfg[bank_name] != "Y") continue;
			if(@strchr($val,"date")){
				$data[$val]=($data[$val]>0) ? date("Y-m-d h:i:s A",$data[$val]) : "";
			}
			if($val == "btop_ymd"){
				$data[btop_ymd_ap]=(date("a", $data[date1]) == "am") ? "오전" : "오후";
				$data[btop_ymd]=date("Y-m-d", $data[date1])." ".$data[btop_ymd_ap]." ".date("g:i:s", $data[date1]);
			}
			if($val == "btop_ono"){
				if($_tmp_ono == $data[ono]) $btop_num++;
				else $btop_num=1;
				$data[btop_ono]=$data[ono]."-".$btop_num;
			}
			if($val == "order_gift") {
				$data['order_gift'] = str_replace("@", ",", preg_replace("/^@|@$/", "", $data['order_gift']));
				if ($data['order_gift']) $data['order_gift'] = $pdo->row("select group_concat(`name`) from `$tbl[product_gift]` where `no` in ($data[order_gift])");
			}
			if($val == "ymd") $data[ymd]=date("Ymd",$now);
			if($val == "addressee_addr") $data[addressee_addr]=$data[addressee_addr1]." ".$data[addressee_addr2];
			if($val == "stat") {
				if ($xlsmode == "order") $data['stat']=$_order_stat[$data['stat']];
				if ($xlsmode == "product") $data['stat']=$_order_stat[$data['pstat']];
			}
			if(@strchr("addressee_cell@addressee_phone",$val)) $data[$val]=str_replace("--","",$data[$val]);
			if($val == "delivery_type") $data[delivery_type]=($cfg[delivery_type] == 2) ? "착불" : "선불";
			if($val == "hth_pay") $data[hth_pay]="신용";
			if($val == "dlv_prc") $data[dlv_prc]=$cfg[delivery_fee];
			if($val == "1") $data[1]="1";
			$data[idx]=$idx;
			$data[idx_a]=$idx_a;
			$data[$val]=stripslashes($data[$val]);


			$mso_number_format = (preg_match("/^[0-9]*$/", $data[$val])) ? "0" : "'\@'";
?>
		<td style="mso-number-format:<?=$mso_number_format?>" align="center"><?=$data[$val]?></td>
<?
		}
?>
	</tr>
<?
		$_tmp_ono=$data[ono];
		$idx_a++;
		$idx--;
	}
?>
</table>