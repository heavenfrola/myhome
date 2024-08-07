<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  payco API URL
	' +----------------------------------------------------------------------------------------------+*/
	if($cfg['payco_testmode'] == 'Y') {
		$URL_reserve			= "https://alpha-api-bill.payco.com/outseller/order/reserve";
		$URL_cancel_check		= "https://alpha-api-bill.payco.com/outseller/order/cancel/checkAvailability";
		$URL_cancel				= "https://alpha-api-bill.payco.com/outseller/order/cancel";
		$URL_upstatus			= "https://alpha-api-bill.payco.com/outseller/order/updateOrderProductStatus";
		$URL_cancelMileage		= "https://alpha-api-bill.payco.com/outseller/order/cancel/partMileage";
		$URL_checkUsability		= "https://alpha-api-bill.payco.com/outseller/code/checkUsability";
		$URL_detailForVerify	= "https://alpha-api-bill.payco.com/outseller/payment/approval/getDetailForVerify";  // alpha(개발) 결제상세 조회(검증용)API URL
	}else{
		$URL_reserve			= "https://api-bill.payco.com/outseller/order/reserve";
		$URL_cancel_check		= "https://api-bill.payco.com/outseller/order/cancel/checkAvailability";
		$URL_cancel				= "https://api-bill.payco.com/outseller/order/cancel";
		$URL_upstatus			= "https://api-bill.payco.com/outseller/order/updateOrderProductStatus";
		$URL_cancelMileage		= "https://api-bill.payco.com/outseller/order/cancel/partMileage";
		$URL_checkUsability		= "https://api-bill.payco.com/outseller/code/checkUsability";
		$URL_detailForVerify	= "https://api-bill.payco.com/outseller/payment/approval/getDetailForVerify"; 		// (운영)결제상세 조회(검증용)API URL
	}

?>