<?PHP

	use Wing\HTTP\CurlConnection;

	if($_POST['use_channel_plugin'] == 'Y') { // channel 서비스와 파트너 인증

		$plugin_id = trim($_POST['channel_plugin_id']);
		$accessSecret = trim($_POST['channel_accessSecret']);

		if(!$plugin_id) msg('Plugin Key 를 입력해주세요.');
		if(!$accessSecret) msg('accessSecret 을 입력해주세요.');

		$curl = new CurlConnection(
			sprintf('https://api.channel.io/partner/plugins/%s/%s/acquire', $plugin_id, $accessSecret),
			'POST'
		);
		$curl->setHeader(array(
			'Content-type: application/json',
			'X-Access-Key: 5a7164f702d6bf54',
			'X-Access-Secret: de673268f097f693c7274fd90d2515fb',
		));
		$curl->exec();
		$curl->close();

		$result = $curl->getResult(true);
		fwriteTo('_data/channel.txt', $result."\n\n");
		$result = json_decode($result);
		if(!$result->channelAcquisition->id || !$result->channelAcquisition->partnerId) {
			msg('채널 서비스 인증이 실패되었습니다.');
		}

		$wec = new weagleEyeClient($_we, 'account');
		$return = $wec->call('setChannel', array('id'=>$result->channelAcquisition->id));
	}

	require $engine_dir.'/_manage/config/config.exe.php';

?>