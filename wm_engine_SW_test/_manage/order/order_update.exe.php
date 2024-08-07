<?PHP

	use Wing\API\Naver\CheckoutApi4;
	use Wing\API\Naver\CommerceAPI;

	checkBasic();
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$ono = $_POST['ono'];
	$exec = $_POST['exec'];
	$ext = $_POST['ext'];
	$pno = $_POST['pno'];
	$stat = numberOnly($_POST['stat']);
	$no_reload = $_POST['no_reload'];
	$mode = $_POST['mode'];
	$check_pno = numberOnly($_POST['check_pno']);

	if($exec == 'getOpenmarketOrder') {
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'.class.php';
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'Order.class.php';

		$apiname = $cfg['opmk_api'].'Order';
		$openmarket = new $apiname();
		$onos = $openmarket->orderImport();
		echo number_format(count($onos)-1).'개의 주문이 수집되었습니다.';
		exit;
	}

    if ($exec == 'paytype_restore') { // 결제방식 원상 복구
        $ono = $_POST['ono'];
        startOrderLog($ono, 'order_update.exe.php / paytype_restore'); // 주문 로그 작성

        $ord = $pdo->assoc("select pay_type from {$tbl['order']} where ono=?", array($ono));
        $org_pay_type = $pdo->row("select pay_type from {$tbl['order_payment']} where ono=? order by no asc limit 1", array(
            $ono
        ));
        $pdo->query("update {$tbl['order']} set pay_type=? where ono=?", array(
            $org_pay_type, $ono
        ));

		$pdo->query("
            insert into {$tbl['order_memo']} (ono, content, type, admin_no, admin_id, reg_date) values (?, ?, '1', ?, ?, ?)
        ", array(
            $ono,
            '관리자 '.$admin['admin_id'].'에 의해 결제방식이 「'.$_pay_type[$ord['pay_type']].'」 결제에서 「'.$_pay_type[$org_pay_type].'」결제로 변경되었습니다.',
            $admin['no'],
            $admin['admin_id'],
            $now
        ));
		$pdo->query("update {$tbl['order']} set memo_cnt=memo_cnt+1 where ono=?", array($ono));
        exit;
    }

	if($ext=="all") {
		if($exec == 'truncate') {
			$check_pno = array();
			$tmpres = $pdo->iterator("select no from $tbl[order] where stat=32");
            foreach ($tmpres as $tmpdata) {
				$check_pno[] = $tmpdata['no'];
			}
            if(count($check_pno) == 0) msg('삭제할 게시물이 없습니다.');
		}

		$all_ord=count($check_pno);

		$checkout_check_pno=array();
		$sql="select * from `$tbl[order]` where `no` in (".implode(",", $check_pno).") and `checkout`='Y'";
		$res = $pdo->iterator($sql);
        foreach ($res as $checkout_data) {
			$checkout_check_pno[]=$checkout_data['no'];
		}
		$checkout_all_ord=count($checkout_check_pno);
		$check_pno=array_diff($check_pno, $checkout_check_pno);

		if($all_ord == $checkout_all_ord) msg('네이버 페이 주문은 삭제하실수 없습니다.');
		if($all_ord - $checkout_all_ord < 1) msg("하나 이상의 주문을 선택하세요");

		//스마트스토어
		if(getSmartStoreState() == true) {
			$smartstore_check_pno=array();
			$asql = '';
			if($cfg['n_smart_store'] == 'Y') $asql = " and `smartstore`='Y'";
			$sql="select * from `$tbl[order]` where `no` in (".implode(",", $check_pno).") $asql";
			unset($asql);
			$res = $pdo->iterator($sql);
            foreach ($res as $smartstore_data) {
				$smartstore_check_pno[]=$smartstore_data['no'];
			}
			$smartstore_all_ord=count($smartstore_check_pno);
			$check_pno=array_diff($check_pno, $smartstore_check_pno);

			if($all_ord == $smartstore_all_ord) msg('스마트스토어 주문은 삭제하실수 없습니다.');
			if($all_ord - $smartstore_all_ord < 1) msg("하나 이상의 주문을 선택하세요");
		}

		switch($exec) {
			case 'truncate' :
			case 'delete':
				foreach($check_pno as $key=>$val) {
					$data=get_info($tbl[order],"no",$val);
					if($data['taklstore_last']) continue;
					checkBlank($data[no],"주문정보를 입력해주세요.");
					$ono=$data[ono];
					delOrd($data[ono]);
					delete_log("O", "$data[ono]", "$data[ono] 주문서 삭제");
				}
				if($checkout_all_ord || $smartstore_all_ord){ //스마트스토어
					$tmp_msg_txt = '';
					if($checkout_all_ord){
						$tmp_msg_txt .= "네이버 페이 주문 ".$checkout_all_ord."개";
					}
					if($smartstore_all_ord){
						if($tmp_msg_txt) $tmp_msg_txt .= ', ';
						$tmp_msg_txt .= "스마트스토어 주문 ".$smartstore_all_ord."개";
					}
					msg($tmp_msg_txt."를 제외한 \\n\\n".($all_ord-$checkout_all_ord-$smartstore_all_ord)."개의 주문을 삭제하였습니다","reload","parent");
				}else{
					msg($all_ord."개의 주문을 삭제하였습니다","reload","parent");
				}
			break;
			case 'restore' :
				include_once $engine_dir.'/_engine/include/wingPos.lib.php';
				foreach($check_pno as $val) {
					$ord = $pdo->assoc("select ono, stat, del_stat from $tbl[order] where no='$val'");
					$ono = $ord['ono'];
					if($ord['stat'] != 32) continue;

					$res = $pdo->iterator("select no, pno, complex_no, buy_ea, stat, del_stat from $tbl[order_product] where ono='$ono'");
                    foreach ($res as $oprd) {
						$pdo->query("update $tbl[order_product] set stat='$oprd[del_stat]', del_stat='' where no='$oprd[no]'");
						if($cfg['erp_timing'] <= $oprd['del_stat'] && $oprd['del_stat'] < 10) {
							stockChange($oprd, '-', $oprd['buy_ea'], $ono.' 삭제주문 복구');
						}
					}
					$pdo->query("update $tbl[order] set stat=$ord[del_stat], del_stat='', del_date='', del_admin='' where ono='$ono'");
					ordChgPart($ono);
					ordStatLogw($ono, $ord['del_stat'], 'N');
				}
				msg('', 'reload', 'parent');
			break;
		}

		exit();
	}

	$ono = addslashes(trim($_REQUEST['ono']));
	$exec = $_REQUEST['exec'];

	checkBlank($ono,"주문번호를 입력해주세요.");
	$data=get_info($tbl[order],"ono",$ono);
	checkBlank($data[no],"주문정보를 입력해주세요.");

	switch($exec) {
		case 'delete':
			if($data['checkout'] == 'Y') msg('네이버 페이 주문은 삭제 하실 수 없습니다.');
			if($data['smartstore'] == 'Y') msg('스마트스토어 주문은 삭제 하실 수 없습니다.');
			delOrd($data[ono],$data[member_no]);
			delete_log("O", "$data[ono]", "$data[ono] 주문서 삭제");
			msg("삭제되었습니다","popup");
			break;
		case 'edit_addressee':
			$addressee_name = addslashes($_POST['addressee_name']);
			$addressee_phone = addslashes($_POST['addressee_phone']);
			$addressee_cell = addslashes($_POST['addressee_cell']);
			$addressee_zip = addslashes($_POST['addressee_zip']);
			$addressee_addr1 = addslashes($_POST['addressee_addr1']);
			$addressee_addr2 = addslashes($_POST['addressee_addr2']);
			$dlv_memo = addslashes(del_html($_POST['dlv_memo']));

			$adrsql="";
			if(fieldExist($tbl['order'],'addressee_addr3')) $adrsql .= ", `addressee_addr3`='".addslashes($addressee_addr3)."'";
			if(fieldExist($tbl['order'],'addressee_addr4')) $adrsql .= ", `addressee_addr4`='".addslashes($addressee_addr4)."'";
			if(fieldExist($tbl['order'],'addressee_id')) $adrsql .= ", `addressee_id`='".addslashes($addressee_id)."'";


			$sql="update `$tbl[order]` set `addressee_name`='$addressee_name' , `addressee_phone`='$addressee_phone' , `addressee_cell`='$addressee_cell' , `addressee_zip`='$addressee_zip' , `addressee_addr1`='$addressee_addr1' , `addressee_addr2`='$addressee_addr2' , `dlv_memo`='$dlv_memo' $adrsql where `ono`='$ono'";
			$pdo->query($sql);
			$pdo->query("update $tbl[order_product] set r_message='$dlv_memo' where ono='$ono' and r_message='$data[dlv_memo]'");

			if(is_object($erpListener)) {
				$erpListener->setOrder($ono);
			}

			msg("수정하였습니다","reload","parent");
			break;
		case 'mng_memo':
			$mng_memo=addslashes(del_html($mng_memo));
			$sql="update `$tbl[order]` set `mng_memo`='$mng_memo' where `ono`='$ono'";
			$pdo->query($sql);
			msg("","reload","parent");
			break;
		case 'mng_memo_new':
			$mng_memo=trim(addslashes(del_html($mng_memo)));
			if ($meno) {
				$memo = $pdo->assoc("select `admin_no`, `admin_id` from `$tbl[order_memo]` where `no` = '$meno'");
				if ($admin[level] > 2 && $memo[admin_no] != $admin[no]) msg("선택하신 메모는 \'$memo[admin_id]\'님 혹은 총관리자만 수정할수 있습니다");

				$sql = "update `$tbl[order_memo]` set `content` = '$mng_memo', `admin_no` = '$admin[no]', `admin_id` = '$admin[admin_id]' where `no` = '$meno'";
			} else {
				$sql="insert into `$tbl[order_memo]` (`ono`, `admin_no`, `admin_id`, `content`, `reg_date`) values ('$ono', '$admin[no]', '$admin[admin_id]', '$mng_memo', '$now')";
			}
			$pdo->query($sql);
			if(fieldExist($tbl['order'], 'memo_cnt')) {
				$mm = $pdo->row("select count(*) from `$tbl[order_memo]` where `ono` = '$ono'");
				$pdo->query("update `$tbl[order]` set `memo_cnt` = $mm where `ono` = '$ono'");
			}
			javac("parent.reloadMemo('$ono','')");
			exit;
			break;
		case 'mng_memo_del':
			$memo = $pdo->assoc("select `admin_no`, `admin_id` from `$tbl[order_memo]` where `no` = '$ext'");
			if ($admin[level] > 2 && $memo[admin_no] != $admin[no]) msg("선택하신 메모는 \'$memo[admin_id]\'님 혹은 총관리자만 삭제할수 있습니다");
			$sql="delete from `$tbl[order_memo]` where `no` = '$ext'";
			$pdo->query($sql);
			if(fieldExist($tbl['order'], 'memo_cnt')) {
				$mm = $pdo->row("select count(*) from `$tbl[order_memo]` where `ono` = '$ono'");
				$pdo->query("update `$tbl[order]` set `memo_cnt` = $mm where `ono` = '$ono'");
			}
			javac("parent.reloadMemo('$ono','')");
			break;
		case 'order_sms_chage' :
			$order_sms_chage=addslashes($_POST['order_sms_chage']);
			$sql="update `$tbl[order]` set `sms` = '$order_sms_chage' where `ono` = '$ono'";
			$pdo->query($sql);
			msg("수정하였습니다","reload","parent");
			break;
		case 'stat':
			if($data['stat']>10) {
				msg("\\n 취소/환불/반품 상태입니다\\n\\n 주문을 일반 상태로 돌린 후 재시도하세요      \\n");
			}
			include_once $engine_dir."/_manage/order/order_stat.php";

			if($_POST['no_reload']) msg($ems);
			else msg($ems,"reload","parent");
			break;
		case 'ext_stat':
			if($data['checkout'] == 'Y' && !$ext) {
				msg('네이버 페이 주문에서는 지원하지 않는 기능입니다.');
			}

			if($data['smartstore'] == 'Y' && !$ext) {
				msg('스마트스토어 주문에서는 지원하지 않는 기능입니다.');
			}

			if($data['checkout'] == 'Y') {
				msg('네이버 페이 주문의 취소/환불/반품은 부분취소 기능을 이용해 주세요.');
			}
			if($data['smartstore'] == 'Y') {
				msg('스마트스토어 주문의 취소/환불/반품은 부분취소 기능을 이용해 주세요.');
			}
			if($data['stat']==$ext) msg("변경 전후의 상태가 같습니다");

			if($cfg[repay_part] == "Y" && $data[stat] >= 13){
				$_mstat=$pdo->row("select min(`stat`) from `$tbl[order_product]` where `ono`='$data[ono]'");

				$norepay = $pdo->row("select count(*) from `$tbl[order_product]` where `repay_date` = '0' and `ono` = '$data[ono]'");
				if($norepay == 0 && $_mstat >= 13 && $data[stat2] && $data[repay_date]) msg("주문서 내의 모든 상품이 부분/취소 환불상품 상태인경우 변경 불가능합니다");
			}

			if ($cfg[cash_receipt_use]=="Y" && $ext > 10 && ($ext%2) == 1 && $stat < 10) {
				$pdo->query("update `$tbl[cash_receipt]` set `stat` = '3' where `ono` = '$data[ono]' and `stat` in (1,4)");
			}

			if(!$ext) {
				$ord_recovery=1;
				for($nii=1; $nii<=5; $nii++) {
					if(!$data['date'.$nii]) {
						break;
					}
					$ext=$nii;
				}
			}
			extDateReady();

			include_once $engine_dir.'/_engine/include/wingPos.lib.php';
			if($data['stat'] == 11){
				if(orderStock($data['ono'], $data['stat'], $ext)) exit;
				$ckh_stat=$pdo->row("select count(`no`) from `$tbl[order_stat_log]` where `ono`='$data[ono]' and `stat` != '11'");
				if(!$ckh_stat){
					$omember = $pdo->assoc("select * from `$tbl[member]` where `no`='$data[member_no]' and `member_id`='$data[member_id]' limit 1");
					if($omember[no]){
						autoOrderCheck($omember, $data); // 관리자 수동 승인대기 주문 복구시 적립금/쿠폰 처리
					}
				}
			}else{
				orderMilageChg(); // 획득적립금 반환
				cancelPoint(); // 포인트 사용취소/복구

				if(orderStock($data['ono'], $data['stat'], $ext)) exit; // 윙포스 재고복구
			}

			if($cfg[dlv_part]=="Y") {
				$asql.=",`stat2`='@$ext@'";
			}
			$sql="update `$tbl[order]` set `stat`='$ext', `ext_date`='$now' $asql where `ono`='$ono'";
			$r=$pdo->query($sql);
			if($r) ordStatLogw($ono, $ext);

			if($cfg['milage_api_id'] && $cfg['milage_api_key'] && $data['naver_milage_use'] == 'Y') { // 네이버마일리지 적립상태 변경
				include_once $engine_dir.'/_engine/include/naverMilage.class.php';
				$naverMilage=new naverMilage();
				$naverMilage->changeStatus($data['ono'], $ext);
			}

			// 부분 배송
			if($cfg[repay_part] == "Y" && $data[repay_date]) $add_q=" and `repay_date`=0";
			$sql="update `$tbl[order_product]` set `stat`='$ext' where `ono`='$data[ono]'".$add_q;
			$pdo->query($sql);
			if($cfg[repay_part] == "Y") ordChgPart($data[ono]);

			// 취소시 회원등급 강등
			setMemOrd($data[member_no],1);

			// 현금영수증 자동 취소
			$cash=cashReceiptAuto($data, $ext);

			msg("","reload","parent");
			break;
		case 'dlv':
			if($number_only=="Y") $dlv_code=numberOnly($dlv_code);
			checkBlank($dlv_no,"배송사를 입력해주세요.");
			checkBlank($dlv_code,"송장번호를 입력해주세요.");

			$tmp=explode("<wisamall>",$dlv_no);

			$auto_q=$auto_q2="";
			$_astat3=($cfg[auto_stat3_2_w] == 3) ? $cfg[auto_stat3_2_w] : 4;
			if($data['checkout'] == 'Y') $_astat3=4; // 네이버 페이 상품은 송장번호입력으로 인해 무조건 배송중으로 변경
			if($data['smartstore'] == 'Y') $_astat3=4; // 스마트스토어 상품은 송장번호입력으로 인해 무조건 배송중으로 변경
			if($cfg[auto_stat3_2] == "Y" && (($data['stat'] == 2 && $_astat3 == 3) || (($data['stat']>1 && $data['stat']<4) && $_astat3 == 4))){
				$auto_q=" , `stat`='".$_astat3."', `date".$_astat3."`='".$now."'";
				if(!$data[date3] && $_astat3 == 4) $auto_q .= ", `date3`='".$now."'";
				$auto_q2=" and (`stat` in (2,3))";
			}

			if($data['checkout'] == 'Y') {
				if(!isset($checkout)) $checkout = new CheckoutApi4();

				// 네이버 페이 상태변경
				$checkout_sql="select `checkout_ono` from `$tbl[order_product]` where `ono`='$ono'".$auto_q2." and `checkout_ono` <> ''";
				$checkout_res=$pdo->query($checkout_sql);
                foreach ($checkout_res as $checkout_data) {
					$checkout_result=$checkout->api('GetProductOrderInfoList', $checkout_data['checkout_ono']);
					if($checkout_result[0]->ProductOrderStatus[0] == 'DELIVERING') continue;

					$checkout_dlv_no=$checkout->getDlvCode($pdo->row("select `name` from `$tbl[delivery_url]` where `no`='$tmp[0]'"));
					$checkout->delivery($checkout_data['checkout_ono'], $dlv_code, $checkout_dlv_no);
					if($checkout->error) {
						alert(php2java($checkout->error));
						return;
					}
				}
			}

			if($data['smartstore'] == 'Y') {
                if (!isset($CommerceAPI)) {
                    $CommerceAPI = new CommerceAPI();
                }

				// 스마트스토어 상태변경
				$smartstore_sql="select `smartstore_ono` from `$tbl[order_product]` where `ono`='$ono'".$auto_q2." and `smartstore_ono` <> ''";
				$smartstore_res = $pdo->iterator($smartstore_sql);
                foreach ($smartstore_res as $smartstore_data) {
                    $smartstore_stat = $CommerceAPI->getCurrentStat($smartstore_data['smartstore_ono']);
                    if ($smartstore_stat == 4 || $smartstore_stat == 5) continue;

                    $ret = $CommerceAPI->ordersDispatch(
                        $smartstore_data['smartstore_ono'],
                        $dlv_no,
                        $dlv_code
                    );
                    if (!count($ret->data->successProductOrderInfos)) {
                        if ($ret->message) {
                            alert(php2java($ret->message));
                            return;
                        } else if (count($ret->data->failProductOrderInfos)) {
                            alert(php2java($ret->data->failProductOrderInfos[0]->message));
                            return;
                        }
                    }
				}
			}

			$sql="update `$tbl[order]` set `dlv_no`='$tmp[0]', `dlv_code`='$dlv_code'".$auto_q." where `ono`='$ono'".$auto_q2;
			$r=$pdo->query($sql);
			if($r){
				if($auto_q && $data['stat'] != $_astat3){
					ordStatLogw($ono, $_astat3);
					if(setSmsHistory($data['ono'], $_astat3)) {
						include_once $engine_dir."/_engine/sms/sms_module.php";
						$sms_replace['ono'] = $data['ono'];
						$sms_replace['buyer_name'] = $data['buyer_name'];
						$sms_replace['dlv_code'] = $dlv_code;
						$dlv_data = $pdo->assoc("select `name`, `url` from `$tbl[delivery_url]` where `no` = '$dlv_no'");
						$sms_replace['dlv_name'] = $dlv_data['name'];
						$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_data['url']);
						$sms_replace['dlv_link'] = $dlv_link;
						$sms_replace['title'] = stripslashes($data['title']);
						$sms_replace['address'] = stripslashes($data['addressee_addr1'].' '.$data['addressee_addr2']);
						$sms_case = $_astat3 + 1;
						SMS_send_case($sms_case, $data['buyer_cell']);
					}
				}
			}else msg("업데이트 오류발생!");
			if($cfg[dlv_part]=="Y" || $cfg[repay_part] == "Y") {
				$auto_q=($auto_q) ? ", `stat`='".$_astat3."'" : "";
				$sql="update `$tbl[order_product]` set `dlv_no`='$tmp[0]', `dlv_code`='$dlv_code'".$auto_q." where `ono`='$ono'".$auto_q2;
				$pdo->query($sql);
				ordChgPart($ono);
			}

			$ems = '';

			// 에스크로 배송등록
			escDlvRegist($data, $tmp[0], $dlv_code);

			// 현금영수증
			if($cfg['auto_stat3_2'] == 'Y') {
				$data = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
				cashReceiptAuto($data, $_astat3);
			}

			msg($ems,"reload","parent");
			break;
		case 'dlv_mail':
			checkBlank($data[dlv_no],"운송사를 입력해주세요.");
			checkBlank($data[dlv_code],"송장번호를 입력해주세요.");

			$dlv=getDlvUrl($data);

			if($ord['mail_send'] == 'Y') {
				$ord = $data;
				$mail_case=3;
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				sendMailContent($mail_case, $member_name, $to_mail);
			}

			// SMS
			if($data[sms]=="Y") {
				if(setSmsHistory($data['ono'], $data['stat'])) {
					include_once $engine_dir."/_engine/sms/sms_module.php";
					$sms_replace[ono]=$data[ono];
					$sms_replace[buyer_name]=$data[buyer_name];
					$sms_replace[dlv_code]=$data[dlv_code];
					$dlv_data=$pdo->assoc("select `name`, `url` from `$tbl[delivery_url]` where `no` = '$data[dlv_no]'");
					$sms_replace[dlv_name]=$dlv_data['name'];
					$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_data['url']);
					$sms_replace['dlv_link'] = $dlv_link;

					if(SMS_send_case(5,$data[buyer_cell])) {
						$ems="및 SMS ";
					}
				}
			}

			msg("메일".$ems."발송이 완료되었습니다      ","reload","parent");
			break;
		case 'dlv_repay' :
			include_once $engine_dir."/_manage/order/order_dlv_repay.exe.php";
			break;
		case 'bunch' :
			include_once $engine_dir."/_manage/order/order_bunch.exe.php";
			break;
		case 'prdAdd' :
			include_once $engine_dir."/_manage/order/order_add_prd.exe.php";
			break;
		case 'setDlvHold' :
			$pno = addslashes(trim($_POST['pno']));
			$val = $_POST['val'];

			if($admin['level'] == 4) {
				$tmp = $pdo->row("select partner_no from $tbl[order_product] where no='$pno'");
				if($tmp != $admin['partner_no']) {
					exit('처리 권한이 없는 상품입니다.');
				}
			}

			if(!$val) {
				$val = $pdo->row("select dlv_hold from $tbl[order_product] where no='$pno' and ono='$ono'");
				$val = $val == 'Y' ? 'N' : 'Y';
			}
			$type = $val == 'Y' ? '설정' : '해제';

			/*
			$res = $pdo->iterator("select stat, checkout_ono from $tbl[order_product] where no in ($pno) and ono='$ono'");
            foreach($res as $data) {
				if($data['stat'] > 3 && $data['stat'] < 10) exit('이미 배송처리된 상품은 배송보류 하실수 없습니다.');
				if($data['checkout_ono'] && $val == 'Y') {
					if(is_object($checkout) == false) {
						include_once $engine_dir.'/_engine/include/naverCheckout.class.php';
						$checkout = new CheckoutApi4();
					}
					$checkout->api('DelayProductOrder', $data['checkout_ono'], date('Y-m-d', strtotime('+3 days')));
					if($checkout->error) exit;
				}
			}
			*/

			// 스마트 스토어 배송 보류
			if($cfg['n_smart_store'] == 'Y') {
                if (!isset($CommerceAPI)) {
                    $CommerceAPI = new CommerceAPI();
                }

				$res = $pdo->iterator("select stat, smartstore_ono from $tbl[order_product] where no in ($pno) and ono='$ono'");
                foreach ($res as $data) {
					if($data['stat'] > 3 && $data['stat'] < 10) exit('이미 배송처리된 상품은 배송보류 하실수 없습니다.');
                    $ret = $CommerceAPI->ordersConfirm($data['smartstore_ono']);
                    if (!count($ret->data->successProductOrderInfos)) {
                        if ($ret->message) {
                            exit;
                        } else {
                            exit;
                        }
                    }
				}
			}

			$pdo->query("update $tbl[order_product] set dlv_hold='$val' where no in ($pno) and ono='$ono'");
			if($pdo->lastRowCount() > 0) {
				ordChgHold($ono);
			}

			// 배송보류 순위 지정
			if($cfg['erp_auto_hold'] == 'Y' || $cfg['erp_auto_release'] == 'Y') {
				$pnos = explode(',', $pno);
				foreach($pnos as $_pno) {
					if($val == 'Y') {
						$complex_no = $pdo->row("select complex_no from $tbl[order_product] where no='$_pno'");
						if(!$complex_no) continue;
						$hold_sort = $pdo->row("select max(dlv_hold_order) from $tbl[order_product] where complex_no='$complex_no' and dlv_hold='Y'");
						$hold_sort+=1;
					} else {
						$hold_sort = 0;
					}
					$pdo->query("update $tbl[order_product] set dlv_hold_order='$hold_sort' where no='$_pno'");
				}
			}

			if(is_object($erpListener)) {
				$erpListener->setOrder($ono);
			}

			exit($val);
		break;
		case 'partner_dlv_prc' :
			if($admin['level'] > 3) {
				msg('수정 권한이 없습니다.');
			}

			$total_dlv_prc = 0;
			$update_qry = array();
			foreach($_POST['dlv_prc'] as $_no => $prc) {
				$prc = numberOnly($prc);
				$total_dlv_prc+=numberOnly($prc);
				$pdo->query("update {$tbl['order_dlv_prc']} set dlv_prc='$prc' where no='$_no' and ono='$ono'");
			}

			msg('배송비 정산정보가 수정되었습니다.');
		break;
	}

?>