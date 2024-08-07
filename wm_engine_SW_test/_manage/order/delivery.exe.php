<?PHP

	use Wing\API\Naver\CheckoutApi4;
	use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;
	use Wing\API\Kakao\KakaoTalkPay;

	checkBasic();

	$dlv_no = numberOnly($_POST['dlv_no']);
	$ono = numberOnly($_POST['ono']);
	$chg_stat = numberOnly($_POST['chg_stat']);
	$mail_snd = numberOnly($_POST['mail_snd']);
	$prd_total_num = numberOnly($_POST['prd_total_num']);
	$check_ono = $_POST['check_ono'];
	$total1=$total2=$skip1=$skip2=0;

	checkBlank($dlv_no,"배송사를 입력해주세요.");
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	if($cfg['opmk_api']) {
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'.class.php';
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'Order.class.php';

		$apiname = $cfg['opmk_api'].'Order';
		$openmarket = new $apiname();
	}

	if(!isset($checkout)) $checkout = new CheckoutApi4();

	$log_content=date("Y-m-d H:i", $now)." | $admin[admin_id] | $admin[no] \n\n\n";

	set_time_limit(0);
	flush();
	ob_flush();

	function scriptMsg($msg=""){
		$msg=php2java($msg);
		echo "w.innerHTML+='".$msg."';";
		ob_flush();
	}

	echo "<script type=\"text/javascript\">
parent.window.scrollTo(0,0);
w=parent.document.getElementById('process_info');
w.innerHTML='';
";

	scriptMsg("<div class=\"box_title\">배송 시작 처리를 진행합니다...</dlv>");

	foreach($ono as $key=>$val) {
		if (!$check_ono) continue;
		if (!in_array($_POST['ono2'][$val], $check_ono)) continue;

		$total2++;

		scriptMsg("&nbsp;".($total2).". <a href=\javascript:viewOrder(\"".$_POST[ono2][$val]."\");><strong>".$_POST[ono2][$val]."</strong></a> : ");

		$dlv_code=$_POST['dlv_code'.$val];
		if(!$dlv_code){
			scriptMsg("<font style=\"font-size:20pt; color:#FF0000\"><b>ⅹ</b></font>송장번호 미입력<hr>");
			$skip1++;
			continue;
		}

		$data = $pdo->assoc("select * from $tbl[order] where no='$val'");

		// 변경할 주문인지 체크
		if(!$data[no] || $data['stat']>3) {
			if(!$data[no]) scriptMsg("실패 : 해당 주문 정보가 존재하지 않습니다.<hr>");
			if($data['stat']>3) scriptMsg("실패 : 배송 처리된 주문입니다.<hr>");
			continue;
		}

		// 변경할 상품 체크 (부분배송)
		$prd_asql=($chg_stat) ? ", `stat`='4'" : "";
		$_prd=$_POST['dlv_prd'.$data[no]];
		$_prd_q="";
		$tprd=count($_prd);
		if($tprd > 0){
			unset($_prd_num);
			foreach($_prd as $key2=>$val2) {
				orderStock($data['ono'], $data['stat'], 4, $val2);
				$_prd_num[]=$val2;
			}
			$_prd_total_num = count($_POST['total_prd'.$data['no']]);
			$_prd_q=" and `no` in (".implode(",", $_prd_num).")";

			// 네이버 체크아웃 상태변경
			if($data['checkout'] == 'Y') {
				$checkout_process=0;
				$checkout_sql="select `checkout_ono` from `$tbl[order_product]` where `stat`<'4'".$_prd_q." and `checkout_ono` <> ''";
				$checkout_res=$pdo->iterator($checkout_sql);
                foreach ($checkout_res as $checkout_data) {
					$checkout_result=$checkout->api('GetProductOrderInfoList', $checkout_data['checkout_ono']);
					if($checkout_result[0]->ProductOrderStatus[0] == 'DELIVERING') continue;

					$checkout_dlv_no=$checkout->getDlvCode($pdo->row("select `name` from `$tbl[delivery_url]` where `no`='$dlv_no'"));
					$checkout->delivery($checkout_data['checkout_ono'], $dlv_code, $checkout_dlv_no);
					if(!$checkout->error) $checkout_process++;
				}

				if(empty($checkout_process)) continue;
			}

            // 카카오페이 구매 상태 변경
            if ($data['external_order'] == 'talkpay') {
                $taklbuy_process = 0;
				$talkpay_res = $pdo->iterator("select external_id from {$tbl['order_product']} where stat < 4 ".$_prd_q);
                foreach ($talkpay_res as $tData) {
                    if (is_object($talkpay) == false) {
                        $talkpay = new KakaoTalkPay($scfg);
                    }
                    $ret = $talkpay->delivery($tData['external_id'], $dlv_no, $dlv_code);
                    if ($ret == 'OK') {
                        $taklbuy_process++;
                    }
                }
                if(empty($taklbuy_process)) continue;
            }

			// 스마트스토어 상태변경
			if($data['smartstore'] == 'Y') {
                if (!isset($CommerceAPI)) {
                    $CommerceAPI = new CommerceAPI();
                }
				$smartstore_process=0;
				$smartstore_sql="select `smartstore_ono` from `$tbl[order_product]` where `stat`<'4'".$_prd_q." and `smartstore_ono` <> ''";
				$smartstore_res = $pdo->iterator($smartstore_sql);
                foreach ($smartstore_res as $smartstore_data) {
                    $smartstore_stat = $CommerceAPI->getCurrentStat($smartstore_data['smartstore_ono']);
                    if($smartstore_stat == 4 || $smartstore_stat == 5) continue;

                    $ret = $CommerceAPI->ordersDispatch(
                        $smartstore_data['smartstore_ono'],
                        $dlv_no,
                        $dlv_code
                    );
                    fwriteTo('_data/ss.txt', print_r($ret, true));
                    if (count($ret->data->successProductOrderIds)) {
                        $smartstore_process++;
                    }
				}

				if(empty($smartstore_process)) continue;
			}

			if($data['talkstore'] =='Y') {
				if(is_object($kts) == false) {
					$kts = new KakaoTalkStore();
				}

				$kts_process=0;
				$res = $pdo->iterator("select talkstore_ono from $tbl[order_product] where stat<4 $_prd_q and talkstore_ono!=''");
                foreach ($res as $opData) {
					if($opData < 3) { // 상품준비중 처리
						$kts->setShippingWait($opData['talkstore_ono']);
					}

					$dlv_name = $pdo->row("select name from $tbl[delivery_url] where no='$dlv_no'");
					$kes->setShipping($dlv_name, $dlv_code, $opData['talkstore_ono']);
					if($ret == 'OK') {
						$kts_process++;
					}
				}
				if($kts_process < 1) continue;
			}

			if(is_object($openmarket)) {
				$_dlv_pno = $pdo->row("select group_concat(no) from $tbl[order_product] where openmarket_ono!='' and stat<'4' ".$_prd_q);
				if($_dlv_pno && $openmarket->setDeilvery($_dlv_pno, $dlv_no, $dlv_code) == false) {
					scriptMsg("<font style=\"font-size:20pt; color:#FF0000\"><b>ⅹ</b></font>오픈마켓 송장등록 오류<hr>");
					continue;
				}
			}

			$sql="update `$tbl[order_product]` set `dlv_no`='$dlv_no', `dlv_code`='$dlv_code' $prd_asql where `stat`<'4'".$_prd_q;
			$pdo->query($sql);

			if($prd_total_num[$data[no]] == $tprd){ // 실제 주문수량과 배송수량이 일치할 경우
				scriptMsg("<font style=\"font-size:15pt; color:#3366FF;\"><b>○</b></font>");
				scriptMsg("<li><strong>".count($_prd)."</strong> 개의 주문상품 (".$dlv_code.") 배송시작 처리 완료됨");
			}else{
				scriptMsg("<font style=\"font-size:15pt; color:#339900;\"><b>△</b></font>");
				scriptMsg("<li><strong>".$prd_total_num[$data[no]]." 개의 주문상품 중 ".count($_prd)."</strong> 개의 주문상품 (".$dlv_code.") <u>부분 배송시작 처리</u> 완료됨");
			}
		}else{
			scriptMsg("<font style=\"font-size:20pt; color:#FF0000\"><b>ⅹ</b></font>체크된 배송 상품 없음<hr>");
			$skip2++;
			continue;
		}

		// SMS 및 이메일
		if($mail_snd) {
			if($tprd != $_prd_total_num) {
				$tmp_title = $pdo->row("select name from {$tbl['order_product']} where `ono`='{$data['ono']}' ".$_prd_q);
				$data['title'] = strip_tags(stripslashes($tmp_title));
				$dlv_cnt = $_prd_total_num-$tprd;
				if($tprd > 1) {
					$data['title'] = $data['title'].' 외 '.($tprd-1);
				}
			}

			$data[dlv_no]=$dlv_no;
			$data[dlv_code]=$dlv_code;
			$dlv=getDlvUrl($data);
			$ord=$data;
			$mail_case=3;

			if($chg_stat && preg_match('/@'.$mail_case.'/', $cfg['email_checked'])) {
				scriptMsg("<li>".$cfg['email_checked']);
				include $engine_dir."/_engine/include/mail.lib.php";

				if($data['mail_send'] == 'Y') {
					sendMailContent($mail_case,$member_name,$to_mail);
					if($r) scriptMsg("<li>".$member_name." (".$to_mail.") 메일 발송 완료됨");
				}
			}

			if($data[sms]=="Y") {
				if(setSmsHistory($data['ono'], 5)) {
					include_once $engine_dir."/_engine/sms/sms_module.php";
					$sms_replace['ono']=$data['ono'];
					$sms_replace['buyer_name']=$data['buyer_name'];
					$sms_replace['dlv_code']=$dlv_code;
					$dlv_data=$pdo->assoc("select `name`, `url` from `$tbl[delivery_url]` where `no` = '$data[dlv_no]'");
					$sms_replace['dlv_name']=$dlv_data['name'];
					$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_data['url']);
					$sms_replace['dlv_link'] = $dlv_link;
					$sms_replace['title'] = stripslashes($data['title']);
					$sms_replace['address'] = stripslashes($data['addressee_addr1'].' '.$data['addressee_addr2']);
                    $sms_replace['pay_prc'] = number_format($data['pay_prc']);

					if(count($_prd) != $prd_total_num[$data['no']]) SMS_send_case(14,$data['buyer_cell']);
					else SMS_send_case(5,$data['buyer_cell']);
					scriptMsg("<li>".$member_name." (".$data['buyer_cell'].") SMS 발송 완료됨");
				}
			}
		}

		$asql="";
		if($chg_stat) {
			$asql=",`stat`='4',`date4`='$now'";
			if(!$data['date3']) {
				$asql.=",`date3`='$now'";
			}
		}

		// 에스크로 배송등록
		if(($ord['pay_type'] == 4 || $ord['pay_type'] == 17) && $dlv_no && $dlv_code) {
			escDlvRegist($data, $dlv_no, $dlv_code);
		}

		$asql.=",`dlv_no`='$dlv_no', `dlv_code`='$dlv_code'";

		if($asql) {
			$asql=substr($asql,1);
			$sql="update `$tbl[order]` set $asql where `no`='$data[no]'";
			$r=$pdo->query($sql);
			if($r) {
				ordStatLogw($data[ono], 4);

				if($cfg['milage_api_id'] && $cfg['milage_api_key'] && $data['naver_milage_use'] == 'Y') {
					// 네이버 적립상태변경
					include_once $engine_dir.'/_engine/include/naverMilage.class.php';
					if(!isset($naverMilage)) $naverMilage=new naverMilage();
					$naverMilage->changeStatus($data['ono'], $data['stat']);
				}
			}
			scriptMsg("<li><font color=\"#0066FF\"><u>".$data[ono]." (".$dlv_code.") 배송시작 처리 완료됨</u></font>");
		}

		ordChgPart($data[ono]);
		scriptMsg("<hr>");
		$total1++;

		if(is_object($erpListener)) {
			$erpListener->setOrder($data['ono']);
		}
	}

	$ems="<div class=\"box_bottom left\"><b>".$total1."</b> 개의 주문을 배송 처리하였습니다.</div>";

	scriptMsg($ems);
	if($skip1) scriptMsg("<div class=\"box_bottom left\"><b>".$skip1."</b> 개의 주문은 송장번호 미입력으로 미처리되었습니다.</div>");
	if($skip2) scriptMsg("<div class=\"box_bottom left\"><b>".$skip2."</b> 개의 주문은 체크된 배송상품이 없으므로 미처리되었습니다.<div>");

    echo "parent.removeLoading();";
	echo "</script>";

	exit;

?>