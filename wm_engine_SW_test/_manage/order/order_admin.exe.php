<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 수동 주문서 생성
	' +----------------------------------------------------------------------------------------------+*/

	if($_REQUEST['exec'] == 'prd' || $_GET['body'] == 'order@order_exchg.frm') {
		printAjaxHeader();
		include_once $engine_dir.'/_engine/include/shop_detail.lib.php';

		if($_GET['pno']) $pno = numberOnly($_GET['pno']);
		if(!$multi && $_GET['multi']) $multi = numberOnly($_GET['multi']);
        if (isset($_GET['ono']) == true) {
            $ono = addslashes($_GET['ono']);
            $ord = $pdo->assoc("select member_no, member_id from {$tbl['order']} where ono='$ono'");
            $member = $pdo->assoc("select no, level from {$tbl['member']} where no='{$ord['member_no']}' and member_id='{$ord['member_id']}'");
        }

		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$pno'");
		$prd = shortCut($prd);
		$prd['name'] = stripslashes($prd['name']);
		$prd['thumb'] = getFileDir($prd['updir'])."/$prd[updir]/$prd[upfile3]";
		if(!$prd['milage']) $prd['milage'] = 0;
		$mmile = floor($prd['sell_prc']*($mmile_per/100));
		if($cfg['milage_use'] != '1') {
			$prd['milage'] = 0;
			$mmile = 0;
		}
		$buy_ea = (int)$_GET['buy_ea'];
		if($buy_ea < 1) $buy_ea = 1;

		if($oprd['buy_ea']) $prd['min_ord'] = $oprd['buy_ea'];

		foreach($_order_sales as $s => $n) { // 상품별 할인가 출력
			$sale_hidden[$s] = ($oprd[$s] != 0) ? '' : 'hidden';
			$oprd[$s] = parsePrice($oprd[$s]);
			if(!$oprd[$s]) $oprd[$s] = 0;
		}

		$partner_dlv_type = ($prd['dlv_type'] == '1') ? 0 : $prd['partner_no'];
		$is_max_ord_mem = ($prd['max_ord_mem'] > 0) ? 'is_max_ord_mem' : '';

?>
			<tr class="<?=$disable?> add_products partner_dlv_<?=$partner_dlv_type?> <?=$is_max_ord_mem?>">
				<td class="left">
					<input type="hidden" name="prd_disable[]" value="<?=$disable?>">
					<input type="hidden" name="pno[]" value="<?=$prd['no']?>">
					<input type="hidden" name="m[]" value="<?=$multi?>">
					<input type="hidden" name="order_product_no[]" value="<?=$oprd['no']?>">
					<div class="box_setup btn_none">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><img src="<?=$prd['thumb']?>" width="50px"></a></div>
						<dl>
							<dt class="title"><a href="./index.php?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><?=$prd['name']?></a></dt>
							<dd class="sctr"><?=$prd['origin_name']?></dd>
						</dl>
					</div>
				</td>
				<?php if ($disable == 'disable') { ?>
				<td><?=parseOrderOption($oprd['option'], '<br>', ' : ')?></td>
				<td>
					<?=parsePrice($oprd['total_prc']/$oprd['buy_ea'], true)?>
				</td>
				<td><?=$oprd['buy_ea']?></td>
				<td>
					<?php foreach ($_order_sales as $fn => $fv) { ?>
					<?php if($oprd[$fn] != 0) { ?>
                    <div class="p_color explain"><?=$fv?> : <span class="product_<?=$fn?>"><?=parsePrice(-($oprd[$fn]), true)?></span></div>
                    <?php } else { ?>
                    <span class="product_<?=$fn?>"></span>
                    <?php } ?>
					<input type="hidden" name="<?=$fn?>[]" class="admin_order_<?=$fn?> saleobj" value="<?=$oprd[$fn]?>">
					<?php } ?>
				</td>
				<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
				<td>
					<?=parsePrice($oprd['prd_dlv_prc'], true)?>
					<input type="hidden" name="prd_dlv_prc[]" value="<?=$oprd['prd_dlv_prc']?>">
				</td>
				<?php } ?>
				<td>
					<span class="sell_prc_preview"><?=parsePrice(($oprd['total_prc']-getOrderTotalSalePrc($oprd)+$oprd['prd_dlv_prc']), true)?></span>
					<input type="hidden" name="sell_prc[]" value="<?=$prd['sell_prc']?>">
					<input type="hidden" name="sell_prc_org[]" value="<?=$prd['sell_prc']?>">
					<input type="hidden" name="milage[]" class="a_prd_milage"value="<?=$prd['milage']?>">
					<input type="hidden" name="member_milage[]" class="a_mem_milage" value="<?=$mmile?>">
					<input type="hidden" name="buy_ea[]" value="<?=$prd['min_ord']?>">
				</td>
				<td><?=parsePrice(($oprd['total_milage']-$oprd['member_milage']))?></td>
				<td><?=parsePrice(($oprd['member_milage']))?></td>
				<td>교환대상</td>
				<?php } else { ?>
				<td>
					<?PHP
						$opt_no = 1;
						$option_str = '';
						$option_names = array();
						$_ores = $pdo->iterator("select no, otype, name from {$tbl['product_option_set']} where pno='{$prd['no']}' and necessary!='P' order by sort asc");
                        foreach ($_ores as $_odata) {
							if(is_array($_GET['option'.$opt_no]) == false) continue;

							$option_val = inputText(array_shift($_GET['option'.$opt_no]));
							list($iname, $add_price, $dummy, $ino) = explode('::', $option_val);

							// 옵션 명
							if($option_val) {
								if($_odata['otype'] == '4B') { // 텍스트옵션
									$_otmp = getTextOptionPrc($_odata['no'], $option_val);
									$prd['sell_prc'] += $_otmp['price'];
									$option_names[] = stripslashes($_odata['name']).'<split_small>'.$option_val;
								} else {
									$prd['sell_prc'] += $add_price;
									$option_names[] = stripslashes($_odata['name']).'<split_small>'.$iname;
								}
							}
							// 옵션필드
							$option_str .= "<input type=\"hidden\" name=\"option{$opt_no}[$multi]\" value=\"$option_val\">";

							$opt_no++;
						}
						echo parseOrderOption(implode('<split_big>', $option_names), '<br>', ' : ').$option_str;
					?>
				</td>
				<td>
					<?=parsePrice($prd['sell_prc'], true)?>
					<input type="hidden" name="sell_prc[]" class="a_sell_prc" value="<?=$prd['sell_prc']?>">
					<input type="hidden" name="sell_prc_org[]" value="<?=$prd['sell_prc']?>">
				</td>
				<td>
					<?=number_format($buy_ea)?>
					<input type="hidden" name="buy_ea[]" class="a_buy_ea" value="<?=$buy_ea?>">
				</td>
				<td>
					<div>
						<span class="box_btn_s blue"><input type="button" value="+ 할인추가" onclick="setOrderProductSale($(this))"></span>
					</div>
                    <ul style="margin: 5px">
					<?php foreach ($_order_sales as $fn => $fv) { ?>
					<li class="admin_order_<?=$fn?> <?=$sale_hidden[$fn]?>">
                        <input type="hidden" name="<?=$fn?>[]" value="<?=$oprd[$fn]?>">
						<span class="p_color explain">- <?=$fv?></span>
                        <span class="product_<?=$fn?>"><?=parsePrice($oprd[$fn], true)?></span>
                        <a href="#" onclick="removeProductSale(this, '<?=$fn?>'); return false;"><img src="<?=$engine_url?>/_manage/image/btn/icon_delete.png" style="width: 12px; vertical-align: middle;"></a>
					</li>
					<?php } ?>
                    </ul>
				</td>
				<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
				<td>
					<input
						type="text"
						name="prd_dlv_prc[]"
						value="<?=parsePrice($oprd['prd_dlv_prc'])?>"
						class="input prd_dlv_prc right readOnly"
						size="7"
						onchange="getPrd_prc('prd_dlv_prc')"
						readOnly
					>
				</td>
				<?php } ?>
				<td><span class="sell_prc_preview"></span></td>
				<td><input type="text" name="milage[]" class="input a_prd_milage right" data-pmile='<?=parsePrice($prd['milage'])?>' size="6" value="<?=$prd['milage']?>"></td>
				<td>
					<input type="text" name="member_milage[]" class="input a_mem_milage right" size="6" value="<?=$mmile?>">
				</td>
				<td>
					<?php if (empty($_REQUEST['exchange_same_prd_only']) == true) { ?>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="pdel($(this))"></span>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
		<?php
		if($_GET['body'] == 'order@order_exchg.frm' || $_GET['body'] == 'order@order_exchg.exe') return;
		exit;
	}

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/wingPos.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";


	// form-check
	foreach($_POST as $key => $val) {
		if(getType($val) == 'string') ${$key} = addslashes(trim($val));
		if(getType($val) == 'array') ${$key} = $val;
	}

	checkBlank($buyer_name, "주문자 이름을 입력해주세요.");
	//checkBlank($buyer_phone, "주문자 전화번호를 입력해주세요.");
	checkBlank($buyer_cell, "주문자 휴대폰 번호를 입력해주세요.");
	checkBlank($buyer_email, "주문자 메일주소를 입력해주세요.");
    /*
	checkBlank($addressee_name, "수령자 이름을 입력해주세요.");
	checkBlank($addressee_phone, "수령자 전화번호를 입력해주세요.");
	checkBlank($addressee_cell, "수령자 휴대폰을 입력해주세요.");
	checkBlank($addressee_zip, "배송지 우편번호를 입력해주세요.");
	checkBlank($addressee_addr1, "배송지 주소를 입력해주세요.");
	checkBlank($addressee_addr2, "배송지 상세 주소를 입력해주세요.");
    */

    startOrderLog($ono, 'order_admin.exe.php'); // 주문 로그 작성


	// 무통장 계좌정보
	if($pay_type == 2) {
		checkBlank($bank, "입금 은행을 입력해주세요.");
		checkBlank($bank_name, "입금자 이름을 입력해주세요.");

		$bank = $pdo->row("select concat(`bank`, ' ', `account`, ' ', `owner`) from $tbl[bank_account] where no='$bank'");
		$bank_name = addslashes($bank_name);
	} else {
		$bank = $bank_name = "";
	}

	if(!$emoney_prc) $emoney_prc = 0;
	if(!$milage_prc) $milage_prc = 0;

	// 적립금&예치금 체크
	$member_id = addslashes($_POST['member_id']);
	if($member_id) {
		$amember = $pdo->assoc("select no, member_id, milage, emoney from $tbl[member] where member_id='$member_id' and withdraw!='Y'");
		if(!$amember['member_id']) msg('존재하지 않거나 탈퇴신청중인 회원 아이디입니다.');

		if($amember['emoney'] < $emoney_prc) msg("회원의 보유 예치금보다 사용 예치금이 많습니다.");
		if($amember['milage'] < $milage_prc) msg("회원의 보유 적립금보다 사용 적립금이 많습니다.");
	} else {
		if($emoney_prc > 0 || $milage_prc > 0) msg("비회원 구매는 적립금/예치금을 사용하실 수 없습니다.");
	}


	// 주문 저장
	$ono1 = preg_replace("/-[a-z0-9]+$/i", "", $ono);
	while(!$ono) {
		$ono=makeOrdNo($ono1);
	}

	$order_product_no = array();
	$exists = array();
	$partners = array();
	$ord_total_milage = 0;
    $total_prd_dlv_prc = 0;
	foreach($pno as $key => $val) {
		$pasql1 = $pasql2 = '';

		// 입력된 상품별 할인가격
		$total_sale_prc = 0;
		foreach($_order_sales as $fn => $fv) {
			${$fn} = $tmp = numberOnly($_POST[$fn][$key]);
			if($tmp != 0) {
				$pasql1 .= ", $fn";
				$pasql2 .= ", '$tmp'";
				$total_sale_prc += $tmp;
			}
		}

		$ea = numberOnly($buy_ea[$key]);
		$prc = parsePrice($sell_prc[$key]);
		$total_milage = parsePrice($milage[$key]+$member_milage[$key]);
		$mil = parsePrice($milage[$key]/$ea);
		$mem_mil = parsePrice($member_milage[$key]);
		$multi = numberOnly($m[$key]);
		$total_prc = parsePrice($prc*$ea);
		$total_prd_prc += ($total_prc-$total_sale_prc);

		$val = numberOnly($val);
		$prd = $pdo->assoc("select * from $tbl[product] where no='$val'");
		$prd = shortCut($prd);
		$prd['name'] = addslashes(strip_tags(stripslashes($prd['name'])));

		$opt = prdCheckStock($prd, $ea, $multi);
		$option			= $opt['option'];
		$option_prc		= $opt['option_prc'];
		$option_idx		= $opt['option_idx'];
		$complex_no		= $opt['complex_no'];

		$ck = $val.$option_idx;
		if(in_array($ck, $exists)) {
			$pdo->query("delete from $tbl[order_product] where ono='$ono'");
			msg('같은 상품(옵션)이 두개 이상 등록되어 있습니다.\n중복되는 상품을 정리해 주십시오.');
		}
		$exists[] = $ck;
		$partners[] = $prd['partner_no'];
		if(!$title) $title = "$prd[name] ($ea)";

		if($cfg['use_partner_shop'] == 'Y') {
			if($prd['partner_rate'] > 0) $prd['fee_prc'] = getPercentage($total_prc, $prd['partner_rate']);
			$pasql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
			$pasql2 .= ", '$prd[partner_no]', '$prd[partner_rate]', '$prd[fee_prc]', '$prd[dlv_type]'";
		}

        if (empty($prd_dlv_prc[$key]) == false) {
            $pasql1 .= ", prd_dlv_prc";
            $pasql2 .= ", '{$prd_dlv_prc[$key]}'";

            $total_prd_dlv_prc += $prd_dlv_prc[$key];
        }

		$option = addslashes($option);
		$pdo->query("insert into `$tbl[order_product]` ".
				  "(`ono`, `pno`, `name`, `sell_prc`, `milage`, `member_milage`, `buy_ea`, `total_prc`, `total_milage`, `option`, `option_prc`, `option_idx`, `complex_no`, `stat` $pasql1) ".
				  " values ('$ono', '$prd[no]', '$prd[name]', '$prc', '$mil', '$mem_mil', '$ea', '$total_prc', '$total_milage', '$option', '$option_prc', '$option_idx', '$complex_no', '1' $pasql2)"
				 );
		$order_product_no[] = $pdo->lastInsertId();
		$ord_total_milage += $total_milage;
	}

	$stat = 1;
	if(count($pno) > 1) $title .= " 外 ".count($pno);

	$dlv_prc = (int) $dlv_prc;
	$total_prc = $total_prd_prc + $dlv_prc;
	$pay_prc = $total_prc - $emoney_prc - $milage_prc;

	if($emoney_prc + $milage_prc > $total_prd_prc + $dlv_prc) {
		$pdo->query("delete from $tbl[order_product] where ono='$ono'");
		msg('결제 할 금액보다 사용 적립금(예치금)이 더 많습니다.');
	}

	if($emoney_prc > 0 || $milage_prc > 0) {
		$pdo->query("update $tbl[member] set emoney = emoney - '$emoney_prc', milage = milage - '$milage_prc' where no='$amember[no]");
		if($emoney_prc > 0) ctrlEmoney("-",11, $emoney_prc, $amember, "[$ono] 주문서 수동생성", false, $admin['admin_id'], $ono);
		if($milage_prc > 0) ctrlMilage("-",11, $milage_prc, $amember, "[$ono] 주문서 수동생성", false, $admin['admin_id'], $ono);
	}

	$aisql1 = '';
	$aisql2 = '';
	foreach($_order_sales as $key => $val) {
		$tmp = $pdo->row("select sum(`$key`) from $tbl[order_product] where `ono`='$ono'");
		if($tmp) {
			$aisql1 .= ", `$key`";
			$aisql2 .= ", '$tmp'";
		}
	}

    $_ord_add_info = array();
    $add_field_file = $root_dir.'/_config/order.php';
    if (file_exists($add_field_file) == true) {
        include_once $add_field_file;
        foreach ($_ord_add_info as $key => $val) {
            if (empty($_POST['add_info'.$key]) == true) continue;

            $addval = $_POST['add_info'.$key];
            if ($val['type'] == 'checkbox' && is_array($addval) > 0) {
                $_addval = '@';
                foreach($addval as $key2=>$val2){
                    $_addval .= $val2.'@';
                }
                $addval = $_addval;
            } else if ($val['type'] == 'date' && $val['format'] == '2') {
                if (empty($_POST['add_info'.$key.'_h']) == false) {
                    $addval = $addval.' '.$_POST['add_info'.$key.'_h'].'시';
                }
            }
            $addval = addslashes($addval);

            $aisql1 .= ", add_info{$key}";
            $aisql2 .= ", '$addval'";

        }
    }

    if ($total_prd_dlv_prc > 0) {
        $aisql1 .= ", prd_dlv_prc";
        $aisql2 .= ", '$total_prd_dlv_prc'";
        $dlv_prc += $total_prd_dlv_prc;
        $total_prc += $total_prd_dlv_prc;
        $pay_prc += $total_prd_dlv_prc;
    }

	$pdo->query("insert into $tbl[order] (".
			  " `ono`,`title`,`date1`,`stat`,`member_no`,`member_id` ".
			  ",`buyer_name`,`buyer_email`,`buyer_phone`,`buyer_cell` ".
			  ",`addressee_name`,`addressee_phone`,`addressee_cell`,`addressee_zip`,`addressee_addr1`,`addressee_addr2` ".
			  ",`total_prc`,`pay_prc`,`prd_prc`,`dlv_prc`,`milage_prc`,`emoney_prc`,`pay_type`,`bank`,`bank_name`,`total_milage`,`dlv_memo` $aisql1 ".
			  ") values (".
			  " '$ono','$title','$now','$stat','$amember[no]','$amember[member_id]' ".
			  ",'$buyer_name','$buyer_email','$buyer_phone','$buyer_cell' ".
			  ",'$addressee_name','$addressee_phone','$addressee_cell','$addressee_zip','$addressee_addr1','$addressee_addr2' ".
			  ",'$total_prc','$pay_prc','$total_prd_prc','$dlv_prc','$milage_prc','$emoney_prc','$pay_type','$bank','$bank_name','$ord_total_milage','$dlv_memo' $aisql2".
			  ")");

	// 업체별 배송비 저장
	if($cfg['use_partner_delivery'] == 'Y') {
		$partners = array_unique($partners);
		if(!isTable($tbl['order_dlv_prc'])) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['order_dlv_prc']);
		}
		foreach($partners as $ptn_no) {
			$pdo->query("insert into {$tbl['order_dlv_prc']} (ono, partner_no, dlv_prc, first_prc) values ('$ono', '$ptn_no', '0', '0')");
		}
	}

	orderStock($ono, 0.99, 1); // 윙포스 재고변경
	ordChgPart($ono);

	if(is_object($erpListener)) {
		$erpListener->setOrder($ono);
	}

	$payment_no = createPayment(array(
		'type' => 0,
		'ono' => $ono,
		'pno' => $order_product_no,
		'pay_type' => $pay_type,
		'amount' => $pay_prc,
		'bank' => $bank,
		'bank_name' => $bank_name,
		'dlv_prc' => $dlv_prc,
		'emoney_prc' => $emoney_prc,
		'milage_prc' => $milage_prc,
		'cpn_no' => $cpn_no,
	), 1);

	ordStatLogw($ono, 1);
	if($ono) {
		$ord = $pdo->assoc("SELECT od.ono, od.title, od.pay_prc, od.buyer_name, od.buyer_cell, od.buyer_email, od.milage_prc, od.emoney_prc,
	                                od.pay_type, od.dlv_prc
	                                FROM " . $tbl['order'] . " AS od  
	                                WHERE od.ono = '" . $ono . "'");
		$ord = array_map('stripslashes', $ord);
		//배송비
		$dlv_prc = $ord['dlv_prc'];
		//예치금+적립금 사용액
		$point_prc = $ord['milage_prc'] + $ord['emoney_prc'];

		//사용중인 할인 필드 체크
		$sales_fields_sql = getOrderSalesField('op', ' + ');

		//주문금액중 면/과세 금액 취합
		$sql = "SELECT 
	                SUM(IF(prd.tax_free = 'Y', op.total_prc - (".$sales_fields_sql."), 0)) AS tot_taxfree,
	                SUM(IF(prd.tax_free != 'Y', op.total_prc - (".$sales_fields_sql."), 0)) AS tot_tax                        
	                FROM 
	                ".$tbl['order_product']." op
	                JOIN ".$tbl['product']." prd ON op.pno = prd.no            
	                WHERE
	                op.ono = '".$ono."'";
		$tax_data = $pdo->assoc($sql);

		$tax_data['tot_tax'] += $dlv_prc; //배송비는 과세에 추가

		$diff = $tax_data['tot_tax'] - $point_prc; //과세액에 예+적립금 우선 차감 적용
		if ($diff < 0) {
			//차액이 음수인경우 (차감할금액이 남은경우)
			$tax_data['tot_tax'] = 0; //과세액은 0원으로 처리
			$tax_data['tot_taxfree'] -= abs($diff); //면세액에서 추가 차감 (음수이므로 절대값 적용)
		} else {
			$tax_data['tot_tax'] -= $point_prc; //과세액만 차감
		}
	}
	// 현금영수증 등록
	$cash_reg_num = numberOnly($_POST['cash_reg_num']);
	if($cfg['cash_receipt_use'] == 'Y' && $cash_reg_num && $pay_prc > 0 && $pay_type == 2) {
        $taxfree_amount = $tax_data['tot_taxfree'];
        $tax_amount = parsePrice($pay_prc-$taxfree_amount);
        $amt1 = $pay_prc;
        $amt4 = round($tax_amount / 1.1) * 0.1; // 부가세 (과세상품 결제금액/1.1 의 0.1 )
        $amt2 = ($tax_amount - $amt4) + $taxfree_amount; //공급가액 (면세금액 + 과세금액의 부가세)
        $amt3 = 0;
        $prod_name = mb_strimwidth(strip_tags($title), 0, 28, _BASE_CHARSET_);

		$pdo->query("
			insert into $tbl[cash_receipt] set
			`ono`			= '$ono',
			`cash_reg_num`	= '$cash_reg_num',
			`pay_type`		= '$pay_type',
			`reg_date`		= $now,
			`amt1`			= '$amt1',
			`amt2`			= '$amt2',
			`amt3`			= '$amt3',
			`amt4`			= '$amt4',
            `taxfree_amt`	= '$taxfree_amount',
			`b_num`			= '".numberOnly($cfg['company_biz_num'])."',
			`prod_name`		= '$prod_name',
			`cons_name`		= '$buyer_name',
			`cons_tel`		= '$buyer_cell',
			`cons_email`	= '$buyer_cell'
		");
	}

	makeOrderLog($ono, "order_admin.exe.php"); // 주문로그

	// 메일, SMS 발송
	$ord = get_info($tbl['order'],'ono',$ono);

    $sms = $_POST['sms'];
	if($sms == "Y") {
		include_once $engine_dir."/_engine/sms/sms_module.php";
		$sms_replace['ono'] = $ord['ono'];
		$sms_replace['buyer_name'] = $ord['buyer_name'];
		$sms_replace['pay_prc'] = parsePrice($ord['pay_prc']);
		$sms_replace['pay_type'] = $_pay_type[$ord['pay_type']];
		$sms_replace['title'] = strip_tags(stripslashes($ord['title']));
		$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);

		if($ord['pay_type'] == 2) $sms_replace['account'] = $ord['bank'];
		else $sms_replace['account'] = "결제완료";

		SMS_send_case(2,$ord['buyer_cell']);
		if($pay_type == 2) {
			SMS_send_case(13,$ord['buyer_cell']);
		}
	}

	if($sms == "Y") {
		$pdo->query("update `$tbl[order]` set `sms`='Y' where `ono`='$ord[ono]'");
	}

	if($mail_send == "Y"){
		$mail_case = 2;
		include $engine_dir."/_engine/include/mail.lib.php";
		$r = sendMailContent($mail_case,$member_name,$to_mail);
		$pdo->query("update `$tbl[order]` set `mail_send`='Y' where `ono`='$ord[ono]'");
	}

?>
<script type="text/javascript">
	window.alert('주문서 수동 등록이 완료되었습니다.');
	parent.viewOrder('<?=$ono?>');
	parent.location.reload();
</script>