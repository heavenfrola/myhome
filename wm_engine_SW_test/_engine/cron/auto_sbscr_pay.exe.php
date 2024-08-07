<?php

	/*
	 * 정기배송 결제처리 페이지
	 */

	set_time_limit(0);
	ini_set('memory_limit', -1);

	chdir(dirname(__FILE__));
	if(defined('_wisa_set_included') == false) {
		include_once '../../../_config/set.php';
	}
	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

    addField($tbl['sbscr'], 'date3', 'int(10) not null default 0 after date2');
    addField($tbl['sbscr'], 'date5', 'int(10) not null default 0 after date3');

	$paywhere = '';

	$schno = numberOnly($_POST['schno']);
	if($schno > 0) {
		$bv = $pdo->assoc("select sbono, date from {$tbl['sbscr_schedule']} where no='$schno'");
		$make_sdate = $bv['date'];
		$make_edate = $bv['date'];
		$paywhere .= " and ss.sbono='{$bv['sbono']}'";
	} else {
		$paywhere .= " AND s.pay_type in (23, 27)";
	}
	$paywhere .= " AND ss.date >= '$make_sdate' AND ss.date <= '$make_edate'";

	// PG 모듈
	if(isset($cfg['autobill_pg']) == false || empty($cfg['autobill_pg']) == true) {
		$cfg['autobill_pg'] = 'dacom';
	}
	switch($cfg['autobill_pg']) {
		case 'dacom' : $pg_version = 'XpayAutoBilling/'; break;
		case 'nicepay' : $pg_version = 'autobill/'; break;
	}
	include_once $engine_dir.'/_engine/card.'.$cfg['autobill_pg'].'/'.$pg_version.'card_bill_pay.inc.php';

	// 결제 처리
	$ono_length = $pdo->row("select max(char_length(wm_ono)) from `$tbl[card]`");
	if($ono_length==15) {
		$pdo->query("alter table `$tbl[card]` modify `wm_ono` varchar(200) NOT NULL");
	}

	$sql = "SELECT
				ss.*, s.pay_type, s.member_no
			FROM
				$tbl[sbscr_schedule] as ss
			LEFT JOIN $tbl[sbscr_schedule_product] as ssp
			on ss.no=ssp.schno
			LEFT JOIN $tbl[sbscr] as s
			on ss.sbono=s.sbono
			WHERE
				ssp.stat = 1
				AND ssp.ono = ''
				AND ssp.opno = 0
				$paywhere
			GROUP BY schno
			ORDER BY date";
	$res = $pdo->iterator($sql);
    foreach ($res as $paydata) {
		if($paydata['pay_type'] == '23' || $paydata['pay_type'] == '27') {
			$oid= $paydata['sbono']."_".$paydata['no'];// 서브스크립션 주문번호 + 스케쥴번호
			$pay_prc = parsePrice($paydata['total_prc']);

            startOrderLog($paydata['sbono'], 'auto_sbscr_pay.exe.php');

			//SB 테이블 조회
			$ori_subscription = $pdo->assoc("SELECT * FROM ".$tbl['sbscr']." WHERE sbono = '$paydata[sbono]'");

            $card = $pdo->assoc("select pg from {$tbl['card']} where wm_ono='{$paydata['sbono']}'");
            if ($card['pg'] == 'nsp') {
                if (function_exists('nspBillPay') == false) {
                	include_once $engine_dir.'/_engine/card.naverSimplePay/card_bill_pay.inc.php';
                }
    			$ret = nspBillPay($paydata, $paydata['sbono']);
            } else {
    			$ret = BillPay($ori_subscription, $oid, $pay_prc);
            }

            $log_instance->writeln(print_r($ret, true), 'card return');

			if($ret['result'] == true) {
				//1.booking UPDATE
				$sql = "UPDATE ".$tbl['sbscr_schedule_product']." SET stat = '2' WHERE sbono = '$paydata[sbono]' and stat = '1' and schno='$paydata[no]'";
				$r = $pdo->query($sql);

				//상품조회 및 결제 입력 payment, card
				$sub_product_no = array();
				$pno_res = $pdo->iterator("select pno from $tbl[sbscr_product] where sbono = '$paydata[sbono]'");
                foreach ($pno_res as $pno_data) {
					$sub_product_no[] = $pno_data['pno'];
				}

				$_sno = explode('_', $oid);
				$sno = $_sno[0].$_sno[1];

				//card 입력
				$card_tbl = $tbl['card'];
				$ono = $sno;
				$pay_prc = $paydata['total_prc'];
				$member['no'] = $paydata['member_no'];
				list($os, $browser) = checkAgent();
				$cpn['no'] = 0;
				$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];
				switch($cfg['autobill_pg']) {
					case 'dacom' :
						$cfg['pg_version'] = 'XpayAutoBilling';
						$cfg['pg_mobile_version'] = 'XpayAutoBilling';
					break;
					case 'nicepay' :
						$cfg['pg_version'] = 'autobill';
						$cfg['pg_mobile_version'] = 'autobill';
					break;
				}
                if ($card['pg'] == 'nsp') {
                    $cfg['pg_version'] = 'autobill';
                    $cfg['pg_mobile_version'] = 'autobill';
                    cardDataInsert($card_tbl, 'nsp');
                } else {
                    cardDataInsert($card_tbl, $cfg['autobill_pg']);
                }

				$pdo->query("
					UPDATE $card_tbl set
						ordr_idxx='$sno', stat=2, tno='{$ret['tid']}',
						card_cd='{$ret['card_cd']}', card_name='{$ret['card_name']}',
						wm_price='{$ret['amount']}', good_mny='{$ret['amount']}',
						res_cd='{$ret['res_cd']}', res_msg='{$ret['res_msg']}'
					where `wm_ono`='$sno'
				");
                if ($paydata['taxfree_prc'] > 0) {
                    $pdo->query("
                        update $card_tbl set
                        wm_free_price='{$paydata['taxfree_prc']}'
    					where `wm_ono`='$sno'
    				");
                }

				//예약상태 변경시 상품상태 변경
				$sub_prd_cancel_cnt = $pdo->row("SELECT COUNT(*) FROM $tbl[sbscr_schedule_product] WHERE sbono = '$sno' and stat = '1' ");
				if(!$sub_prd_cancel_cnt) {
					//2.product UPDATE
					$pdo->query("UPDATE $tbl[sbscr_product] SET stat='2' WHERE sbono='$sno'");
					//3.subscription UPDATE
					sbscrChgPart($sno);
				}
                $log_instance->writeln('billpay success');
			} else {
                if ($schno) {
                    exit($ret['res_msg']);
                } else {
    				alert(php2java($ret['res_msg']));
                }
                $log_instance->writeln('billpay failed');
			}
		} else {
            startOrderLog($paydata['sbono'], 'auto_sbscr_pay.exe.php');

			addField($tbl['order_product'], 'sale8', 'double(8,2) not null default 0.00');
			addField($tbl['order'], 'sale8', 'double(8,2) not null default 0.00');

			$sno = $paydata['sbono'];
			$pdo->query("update $tbl[sbscr_schedule_product] set stat='2' WHERE sbono='$paydata[sbono]' and stat='1' and schno='$paydata[no]'");
			$sub_prd_cancel_cnt = $pdo->row("select count(*) from $tbl[sbscr_schedule_product] WHERE sbono='$sno' and stat='1'");
			if(!$sub_prd_cancel_cnt) {
				$pdo->query("update $tbl[sbscr_product] set stat='2' WHERE sbono='$sno'");
				sbscrChgPart($sno);
			}
           $log_instance->writeln('수동 입금처리');
		}
        $pdo->query("update {$tbl['sbscr']} set stat=3, date3='$now' where sbono='{$paydata['sbono']}' and stat=2");
	}

	if($schno) {
		autoSbscrCreate($schno);
		exit('선택하신 회차의 결제 및 주문서 생성이 완료되었습니다.');
	}

?>