<?PHP

	require_once __ENGINE_DIR__.'/_engine/include/file.lib.php';
	require_once __ENGINE_DIR__.'/_engine/card.nicepay/autobill//lib/NicepayLite.php';

	function BillPay($data, $ono, $pay_prc) {
		global $cfg, $engine_dir, $root_dir;

		makeFullDir('_data/nicepay_log');

		$nicepay = new NicepayLite;
		$nicepay->m_LicenseKey   = $cfg['card_auto_nicepay_key'];
		$nicepay->m_NicepayHome  = $root_dir.'/_data/nicepay_log';
		$nicepay->m_ssl          = "true";
		$nicepay->m_ActionType   = "PYO"; // 서비스모드 설정(결제(PY0), 취소(CL0)
		$nicepay->m_debug        = "DEBUG";         // 디버깅 모드
		$nicepay->m_MID          = $cfg['card_auto_nicepay_mid'];
		$nicepay->m_Amt          = $pay_prc;
		$nicepay->m_Moid         = $ono;
		$nicepay->m_MallIP       = $_SERVER['SERVER_ADDR'];
		$nicepay->m_PayMethod    = "BILL";          // 결제수단
		$nicepay->m_BillKey      = $data['billing_key'];
		$nicepay->m_BuyerName    = $data['buyer_name'];
		$nicepay->m_GoodsName    = stripslashes($data['title']);
		$nicepay->m_CardQuota    = '00';
		$nicepay->m_NetCancelPW  = $cfg['card_auto_nicepay_pwd'];
		$nicepay->m_NetCancelAmt = $pay_prc;
		$nicepay->m_charSet      = "UTF8";

		$nicepay->startAction();

		return array(
			'result' => ($nicepay->m_ResultData['ResultCode'] == '3001') ? true : false,
			'tid' => $nicepay->m_ResultData['TID'],
			'card_cd' => $nicepay->m_ResultData['CardCode'],
			'card_name' => $nicepay->m_ResultData['CardName'],
			'app_no' => $nicepay->m_ResultData['AuthCode'],
			'rec_cd' => $nicepay->m_ResultData['ResultCode'],
			'res_msg' => $nicepay->m_ResultData['ResultMsg'],
			'quota' => $nicepay->m_ResultData['CardQuota'],
			'amount' => $nicepay->m_ResultData['Amt'],
		);
	}

    function recurrentExpire($billkey)
    {
        global $cfg, $root_dir;

        $nicepay = new NicepayLite;
        $nicepay->m_LicenseKey  = $cfg['card_auto_nicepay_key'];
        $nicepay->m_NicepayHome = $root_dir.'/_data/nicepay_log';;
        $nicepay->m_MID         = $cfg['card_auto_nicepay_mid'];
        $nicepay->m_PayMethod   = 'BILL';
        $nicepay->m_BillKey     = $billkey;
        $nicepay->m_ssl         = 'true';
        $nicepay->m_ActionType  = 'PYO';
        $nicepay->m_debug       = 'DEBUG';
        $nicepay->m_charSet     = 'UTF8';
        $nicepay->m_CancelFlg   = '1';

        $nicepay->startAction();

        return array(
            'result' => ($nicepay->m_ResultData['ResultCode'] == 'F101') ? true : false,
            'message' => $nicepay->m_ResultData['ResultMsg']
        );
    }

?>