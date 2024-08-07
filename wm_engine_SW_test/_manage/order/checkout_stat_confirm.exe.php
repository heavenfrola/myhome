<?PHP

	use Wing\API\Naver\CheckoutApi4;

	if(!isset($checkout)) $checkout = new CheckoutApi4();

	$checkout_result=$checkout->api('GetProductOrderInfoList', $ono);

	if($_GET['ext'] == 'ClaimReason') {
		header('Content-type:text/html; charset=utf-8');
		$ClalmType = ucfirst(strtolower($checkout_result[0]->ClaimType[0]));
		$Reason = $checkout_result[0]->{$ClalmType.'Reason'}[0];
		$DetailReason = $checkout_result[0]->{$ClalmType.'DetailedReason'}[0];

		echo "사유 : ".iconv('euc-kr', 'utf-8', $checkout->getClaimReason($Reason))."\n\n";
		echo "$DetailReason\n";
		exit;
	}

	echo "상태 : ".$checkout_result[0]->ProductOrderStatus[0]."\n";
	echo "배송사 : ".$checkout_result[0]->DeliveryCompany[0]."\n";
	echo "송장번호 : ".$checkout_result[0]->TrackingNumber [0]."\n";
	echo "상품준비 : ".$checkout_result[0]->PlaceOrderStatus[0]."\n";
	echo "배송일 : ".$checkout_result[0]->DeliveredDate[0]."\n";
	echo "클레임타입 : ".$checkout_result[0]->ClaimType[0]."\n";
	echo "클레임상태 : ".$checkout_result[0]->ClaimStatus[0]."\n";
	echo "배송지연 : ".$checkout_result[0]->DelayedDispatchReason[0]."\n";

?>