<?PHP

	$site_cd       = $_GET[ "site_cd" ];
	$ordr_idxx     = $_GET[ "ordr_idxx"    ];
	$good_mny      = $_GET[ "good_mny"     ];
	$pay_method    = $_GET[ "pay_method"   ];
	$escw_used     = $_GET[ "escw_used"    ];
	$good_name     = mb_convert_encoding($_GET['good_name'], 'euc-kr', _BASE_CHARSET_);
	$Ret_URL       = $_GET[ "Ret_URL"      ];
	$good_name =str_replace("\"", "", $good_name);
	$good_name =str_replace("'", "", $good_name);
	$good_name =str_replace("&"," ", $good_name);
	$good_name =str_replace("%", "", $good_name);
	$good_name =str_replace(" ", "", $good_name);
	$good_name =strip_tags($good_name);

	$kcp_url = ($cfg['card_mobile_test'] == '_test') ? "devpggw.kcp.co.kr" : "smpay.kcp.co.kr";
	$port= ($cfg['card_mobile_test'] == '_test') ? 8080 : 80;

	$host = $kcp_url;
	$service_uri = "http://".$kcp_url."/jsp/php4/php4.jsp?site_cd=".$site_cd."&ordr_idxx=".$ordr_idxx."&good_mny=".$good_mny."&pay_method=".$pay_method."&escw_used=".$escw_used."&good_name=".$good_name."&Ret_URL=".urlencode($Ret_URL)."&Agent=".urlencode($_SERVER['HTTP_USER_AGENT']);

	$fp = fsockopen ($host, $port, $errno, $errstr, 30);
	if (!$fp) {
	    echo "$errstr ($errno)\n";
	} else {
	    fputs ($fp, "GET $service_uri HTTP/1.0\r\n\r\n");
	    while (!feof($fp)) {
	        $source .= fgets($fp, 128);
	    }
	    fclose ($fp);
	}
	$split = explode("\r\n\r\n", $source);

	echo($split[4]);

?>