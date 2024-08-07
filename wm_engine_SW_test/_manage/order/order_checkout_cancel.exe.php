<?PHP

	$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
	if($nprd[0]->ProductOrderStatus[0] != 'PAYED') {}
	$checkout->error = '';

	$claim_type = $nprd[0]->ClaimType[0];
	$claim_status = $nprd[0]->ClaimStatus[0];
	$holdback_status = $nprd[0]->HoldbackStatus[0];
	$claim_fee_pay_method = $nprd[0]->ClaimDeliveryFeePayMethod[0];

	$ckout1 = $ckout2 = $ckout3 = null;
	switch($_REQUEST['stat']) {
		case '13' :
			if($claim_status == 'CANCEL_REQUESTED' || $claim_status == 'CANCEL_REQUEST' || $claim_status == 'CANCELING') {
				$checkout->api('ApproveCancelApplication', $prd['checkout_ono']);
			} else {
				$ckout1 = $_POST['ckout1'];

				if(!$ckout1) {
					msg('네이버페이 취소사유를 선택해주세요.');
				}
				$checkout->api('CancelSale', $prd['checkout_ono'], $ckout1);
			}
		break;
		case '16' :
			$ckout1 = $_POST['ckout1']; // 반품처리 택배사
			$ckout2 = $_POST['ckout2']; // 반품시 송장번호
			$ckout3 = $_POST['ckout3']; // 반품사유코드
			$ckout4 = $_POST['ckout4']; // 반품배송방법 코드
			if($ckout4 != 'DELIVERY') {
				$ckout1 = $ckout2 = '';
			}

			$checkout->api('RequestReturn', $prd['checkout_ono'], $ckout1, $ckout2, $ckout3, $ckout4);
		break;
		case '17' :
			if($holdback_status == 'HOLDBACK' && $claim_fee_pay_method != '환불금에서 차감') {
				$checkout->error('네이버페이 반품보류중인 상품입니다.');
				return;
			}
			$checkout->api('ApproveReturnApplication', $prd['checkout_ono'], 0, $_POST['reason']);

			// 전체상품이 반품승인 되어야 반품수거완료에서 반품완료로 바뀌므로 마지막상품 승인 후 전체 반품수거완료를 반품완료로
			$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
			$nstat = $checkout->getStat($nprd[0]);
			if($nstat == 17) {
				$pdo->query("update $tbl[order_product] set stat='17' where ono='$ono' and stat='23'");
			}
		break;
		case '25' :
			$checkout->api('ApproveCollectedExchange', $prd['checkout_ono'], $ckout1, $ckout2);
		break;
		case '26' :
			$ckout1 = $_POST['ckout1']; // 반품처리 택배사
			$ckout2 = $_POST['ckout2']; // 반품시 송장번호

			$checkout->api('ReDeliveryExchange', $prd['checkout_ono'], $ckout1, $ckout2);
			$stat = 4;
		break;
		case '27' : // 반품 거부
			$ckout1 = $_POST['ckout1']; // 반품거부 사유
			if(!$ckout1) {
				msg('반품 거부 사유를 입력해 주세요.');
			}

			$checkout->api('RejectReturn', $prd['checkout_ono'], $ckout1);

			// 처리 후 원래 주문 상태 다시 가져오기 (배송중, 배송완료)
			$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
			$stat = $checkout->getStat($nprd[0]);
		break;
		case '171' : // 반품 보류
			$ckout1 = $_POST['ckout1']; // 반품 보류 사유
			$ckout2 = $_POST['ckout2']; // 반품 보류 상세 사유
			$ckout3 = $_POST['ckout3']; // 기타 반품 비용
			$stat = $prd['stat'];

			$checkout->api('WithholdReturn', $prd['checkout_ono'], $ckout1, $ckout2, $ckout3);
		break;
		case '172' : // 반품 보류 해제
			$api1 = 'ReleaseReturnHold';
			$stat = $prd['stat'];

			$checkout->api($api1, $prd['checkout_ono']);
		break;
		case '28' : // 교환 거부
			$ckout1 = $_POST['ckout1']; // 교환거부 사유
			if(!$ckout1) {
				msg('교환 거부 사유를 입력해 주세요.');
			}

			$checkout->api('RejectExchange', $prd['checkout_ono'], $ckout1);

			// 처리 후 원래 주문 상태 다시 가져오기 (배송중, 배송완료)
			$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
			$stat = $checkout->getStat($nprd[0]);
		break;
		case '191' : // 교환 보류
			$ckout1 = $_POST['ckout1']; // 교환 보류 사유
			$ckout2 = $_POST['ckout2']; // 교환 보류 상세 사유
			$ckout3 = $_POST['ckout3']; // 기타 교환 비용
			$stat = $prd['stat'];

			$checkout->api('WithholdExchange', $prd['checkout_ono'], $ckout1, $ckout2, $ckout3);
		break;
		case '192' : // 교환보류 해제
			$stat = $prd['stat'];

			$checkout->api('ReleaseExchangeHold', $prd['checkout_ono']);
		break;
		case '401' : // 발송 지연
			$ckout1 = $_POST['ckout1']; // 발송 기한
			$ckout2 = $_POST['ckout2']; // 기타 지연 상세 사유
			$ckout3 = $_POST['ckout3']; // 발송 지연 사유 코드
			$stat = 3;

			$checkout->api('DelayProductOrder', $prd['checkout_ono'], $ckout1, $ckout2, $ckout3);
		break;
	}

?>