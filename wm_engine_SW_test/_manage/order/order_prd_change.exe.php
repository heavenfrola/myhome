<?

	checkBasic(2);

	if($cfg[order_prd_change] != "Y") msg("주문변경 설정이 되어있지 않습니다");

	$prd=get_info($tbl[order_product], "no", $no);
	if(!$prd[no]) msg("존재하지 않는 상품정보입니다");

	$ori_option=explode("<split_big>", $prd[option]);
	$ord = $pdo->assoc("select `no`, `total_prc`, `pay_prc`, `prd_prc`, `total_milage`, `member_no`, `milage_down`, `stat` from `$tbl[order]` where `ono`='$prd[ono]'");
	$emember=get_info($tbl[member], "no", $ord[member_no]);

	$cprd=get_info($tbl[product],"no",$prd[pno]);
	$prd['name'] = stripslashes(strip_tags($prd['name']));

	if($exec == "process"){
		$add_q = '';
		$_option = array();
		$option_str = array();
		$option_idx = array();
		$option_prc = array();
		$option_com = array();
		$add_prc = 0;

		for($ii=0; $ii < $option_no; $ii++) {
			if($_POST["opt".$ii]){
				list($opno, $itemno) = explode('@', $_POST["opt".$ii]);
				$_option[$opno] = $itemno;
			}
		}

		$sql = $pdo->iterator("select a.*, b.`no` as `itemno`, b.`iname`, b.`add_price`, b.`ea` from `$tbl[product_option_set]` a inner join `wm_product_option_item` b on a.`no` = b.`opno` where a.`pno`='$prd[pno]'");
        foreach ($sql as $data) {
			if($_option[$data['no']] == $data['itemno']) {
				$option_str[] = "$data[name]<split_small>$data[iname]";
				$option_idx[] = "$data[no]<split_small>$data[itemno]";
				$option_prc[] = "$data[add_price]<split_small>1";
				$option_com[] = $data['itemno'];
				$add_prc += $data['add_price'];
			}
			if($data['necessary'] == 'Y' && !$_option[$data['no']]) msg("[$data[name]]옵션은 필수 선택옵션입니다.\t");
		}
		$option_str = implode('<split_big>', $option_str);
		$option_idx = implode('<split_big>', $option_idx);
		$option_prc = implode('<split_big>', $option_prc);
		$prd_prc = $cprd['sell_prc'] + $add_prc;

		$add_q .= ", `option`='$option_str', `option_idx`='$option_idx', `option_prc`='$option_prc'";

		if($cprd['ea_type'] == 1) { // 윙포스옵션
			sort($option_com);
			$complex_no = $pdo->row("select `complex_no` from `erp_complex_option` where `opt1`='$option_com[0]' and `opt2`='$option_com[1]'");
			if($complex_no) $add_q .= ", `complex_no`='$complex_no'";

			include_once $engine_dir.'/_engine/include/wingPos.lib.php';
			if($ord['stat'] < 10 && $cfg['erp_timing'] <= $ord['stat']) {
				stockChange($prd, '+', $prd['buy_ea'], '주문상품 옵션/수량 변경');

				$prd['complex_no'] = $complex_no;
				stockChange($prd, '-', $buy_ea, '주문상품 옵션/수량 변경');
			}
		}

		$_total_prc = numberOnly($stotal_prc);
		$add_q .= ", `total_prc`='$_total_prc'";
		if($buy_ea != $prd[buy_ea] && $prd[milage]){
			$_total_milage = $prd['milage'] * $buy_ea;
			$add_q .= ", `total_milage`='$_total_milage'";
		}

		$add_q .= ", dlv_hold='$hold'";

		$psql = "update `$tbl[order_product]` set `buy_ea`='$buy_ea' $add_q where `no`='$prd[no]'";
		$r=$pdo->query($psql);
		if($r){
			if($prd[total_prc] != $_total_prc){
				$gap=$_total_prc-$prd[total_prc];
				$_oprd_prc=$ord[prd_prc]+$gap;
				$_ototal_prc=$ord[total_prc]+$gap;
				$_opay_prc=$ord[pay_prc]+$gap;
				$ord_sql="update `$tbl[order]` set `prd_prc`='$_oprd_prc', `total_prc`='$_ototal_prc', `pay_prc`='$_opay_prc' where `no`='$ord[no]'";
				$pdo->query($ord_sql);
				setMemOrd($ord[member_no]);

				if($cfg['milage_api_id'] && $cfg['milage_api_key'] && $ord['naver_milage_use'] == 'Y') {
					// 네이버 적립상태변경 2012-07-13 cham
					include_once $engine_dir.'/_engine/include/naverMilage.class.php';
					$naverMilage=new naverMilage();
					$res=$naverMilage->approvalMilage($ord['ono'], 'reapproval', 0, 0);
				}
			}
			if($buy_ea != $prd[buy_ea] && $prd[milage]){
				include_once $engine_dir."/_engine/include/milage.lib.php";
				$chg_milage=abs($_total_milage-$prd[total_milage]);
				if($ord[milage_down] == "Y" && $prd['stat'] == 5){
					if($emember[no] && $buy_ea > $prd[buy_ea]) ctrlMilage('+', 3, $chg_milage, $emember, $prd[name]." 추가", "", $admin[admin_id]);
					if($emember[no] && $buy_ea < $prd[buy_ea]) ctrlMilage('-', 3, $chg_milage, $emember, $prd[name]." 취소", "", $admin[admin_id]);
				}
				$_ototal_milage=$pdo->row("select sum(`total_milage`) from `$tbl[order_product]` where `ono`='$prd[ono]'");
				$om_sql="update `$tbl[order]` set `total_milage`='$_ototal_milage' where `no`='$ord[no]'";
				$pdo->query($om_sql);
			}

			$log_sql="insert into `$tbl[order_product_log]`(`ono`, `pno`, `admin_id`, `ori_stat`, `ori_sell_prc`, `ori_total_milage`, `ori_buy_ea`, `ori_option`, `ori_option_prc`, `sell_prc`, `total_milage`, `buy_ea`, `option`, `option_prc`, `ori_ototal_prc`, `ori_opay_prc`, `ori_oprd_prc`, `ori_ototal_milage`, `ototal_prc`, `opay_prc`, `oprd_prc`, `ototal_milage`, `ori_hold`,`dlv_hold`, `reg_date`) values('$prd[ono]', '$prd[no]', '$admin[admin_id]', '$prd[stat]', '$prd[sell_prc]', '$prd[total_milage]', '$prd[buy_ea]', '$prd[option]', '$prd[option_prc]', '$prd[sell_prc]', '$_total_milage', '$buy_ea', '$option_str', '$_option_prc', '$ord[total_prc]', '$ord[pay_prc]', '$ord[prd_prc]', '$ord[total_milage]', '$_ototal_prc', '$_opay_prc', '$_oprd_prc', '$_ototal_milage', '$prd[dlv_hold]', '$hold', '$now')";
			$pdo->query($log_sql);
		}
		msg("", "reload", "parent");

	} else if($exec == "log"){ // 변경내역

		$script="<p style='padding:5px;'><span class='p_color2'>$prd[name]</span> 변경내역</td></p><table class='tbl_row'>";
		$log_sql = $pdo->iterator("select * from `$tbl[order_product_log]` where `pno`='$prd[no]'");
		$cc=1;
        foreach ($log_sql as $log) {
			$chg="";
			if($log[ori_buy_ea] != $log[buy_ea]) $chg .= " 수량 <u>$log[ori_buy_ea] => $log[buy_ea]</u> 개로 변경.";
			if($log[ori_option] != $log[option]){
				$_ori_option=str_replace("<split_big>",", ",$log[ori_option]);
				$_ori_option=str_replace("<split_small>",":",$_ori_option);
				$_option=str_replace("<split_big>",", ",$log[option]);
				$_option=str_replace("<split_small>",":",$_option);
				if(!$_ori_option) $_ori_option="없음"; if(!$_option) $_option="없음";
				$chg .= " 옵션 <u>$_ori_option => $_option</u> (으)로 변경.";
			}
			if($log['dlv_hold'] != $log['ori_hold']) {
				if($log['dlv_hold'] == 'Y') $chg .= " 배송지연 상품으로 변경.";
				else  $chg .= " 배송지연 상품 취소.";
			}
			$script .= "<tr>\
				<th style='width:20%;'>".date("y-m-d H:i", $log[reg_date])."</th>\
			   <td>관리자[".$log[admin_id]."]님 에 의해 $chg</td>\
			</tr>\
			";
			$cc++;
		}
		$script .= "<tr>\
			 <td colspan=2 class='center'><span class='box_btn gray'><input type=button value='닫기' onclick='layTgl(prdChgDetail);'></span></td>\
		   </tr>\
		</table>";
	}else{
		$sel_options = array();
		$option_idx = explode('<split_big>', $prd['option_idx']);
		foreach($option_idx as $val) {
			list($opno, $itemno) = explode('<split_small>', $val);
			$sel_options[$opno] = $itemno;
		}

		$options="해당상품의 옵션정보가 존재하지 않습니다";
		if($cprd[no]){
			$opt_q = $pdo->iterator("select `no`, `name`, `items`, `how_cal` from `$tbl[product_option_set]` where `pno`='$cprd[no]' order by `sort` asc");
			$option_no = $opt_q->rowCount();
			$jj=0;
            foreach ($opt_q as $_option) {
				$_em = $pdo->iterator("select * from `wm_product_option_item` where `opno`='$_option[no]' order by `sort` asc");
				if($_option['how_cal'] == 3 || $_option['how_cal'] == 4) {
                    foreach ($_em as $item) {
						$item['iname'] = stripslashes($item['iname']);
						//$options .= "$item[iname] : <input type='text' name='opt{$jj}[]' class='input' size='$_option[deco2]' value='$sel_options[$opno]'> ";
					}

				} else {
					if($jj == 0) $options="";
					$_opt = "<select name='opt{$jj}' style='width:100px;' onchange='jsPrdCount(this.form);'><option value=''>== $_option[name] ==</option>";
                    foreach ($_em as $item) {
						$selected = ($sel_options[$item['opno']] == $item['no']) ? 'selected' : '';
						$iname = str_replace("::", " +", $item['iname']);
						$_opt .= "<option value='$item[opno]@$item[no]@$item[add_price]' $selected>$iname</option>";
					}

					$_opt .= "</select>";
					$options .= $_opt." ";
				}
					$jj++;
			}
		}else{
			$prd_deleted=" - (삭제된 상품)";
		}

		if(!$prd['complex_no']) { // 윙포스 상품이 아닌 경우에만 재고 수동
			$ea_msg = "단 변경 상품의 <u>재고는 수동</u>으로 변경해 주시기 바랍니다.";
		}

		$is_hold = $prd['dlv_hold'] == 'Y' ? 'checked' : '';

		$input_s=" style='border:none; text-align:center;' class=input size=10 readonly";
		$script="<p style='padding:5px;'>변경할 상품 : <span class='p_color2'>$prd[name]</span>$prd_deleted</p>\
		<table class='tbl_row'>\
		<tr>\
		   <input type=hidden name=modified value=''>\
		   <input type=hidden name=no value='$prd[no]'>\
		   <input type=hidden name=option_no value='$option_no'>\
		   <input type=hidden name=sell_prc value='$prd[sell_prc]'>\
		   <input type=hidden name=ori_prc value='$cprd[sell_prc]'>\
		   <input type=hidden name=milage value='$prd[milage]'>\
		   <th style='width:20%;'>&nbsp;수량변경&nbsp;</th>\
		   <td><input type=text name='buy_ea' value='$prd[buy_ea]' class=input size=5 style='text-align:right;' maxlength=5 onkeypress='numCk();' onkeyup='jsPrdCount(this.form);' onclick='this.select();'> 개</td>\
		</tr>\
		<tr>\
		   <th scope='row'>&nbsp;옵션변경&nbsp;</td>\
		   <td>$options</td>\
		</tr>\
		<tr>\
		   <th scope='row'>&nbsp;배송지연</th>\
		   <td>\
			<label class='p_cursor'><input type='checkbox' name='hold' value='Y' onclick='jsPrdCount(this.form);' $is_hold/> 배송이 지연됩니다. 주문일괄배송 처리시 이 상품을 제외하고 부분 배송됩니다.</label>\
		   </td>\
		</tr>\
		<tr>\
		   <td colspan=2>수량에 따라 <u>적립금은 자동지급/반환</u>됩니다. $ea_msg</td>\
		</tr>\
		<tr>\
		   <td colspan=2>변경 후 상품금액 : (<input name=ssell_prc type=text value='변경전' $input_s>";
		if($option_no > 0) $script .= "+ <input name=soption_prc type=text value='변경전' $input_s>";
		$script .= ") x <input name=sbuy_ea type=text value='변경전' $input_s> = <input name=stotal_prc type=text value='".number_format($prd[total_prc])."' $input_s></td>\
		</tr>\
		";
		$script .= "<tr>\
			 <td colspan=2 height=30 class='center'>\
				<span class='box_btn blue'><input type=button value='변경' class=btn1 onclick='jsPrdChange(0, this.form);'></span>\
				<span class='box_btn gray'><input type=button value='닫기' onclick='layTgl(prdChgDetail);' class=btn5></span>\
			</td>\
		   </tr>\
		</table>";
	}

?>

<script language="JavaScript">
	<?if($script) {?>
		obj=parent.document.getElementById("prdChgDetail");
		if(obj){
			obj.innerHTML="<?=$script;?>";
			obj.style.display="block";
		}
	<?}?>
</script>