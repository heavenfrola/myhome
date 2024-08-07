<?PHP

	if($_POST['use_fb_ad_feed'] == 'Y') {
		include_once $engine_dir.'/_engine/include/file.lib.php';
		makeFullDir('_data/compare/fb');

		$feed = "<?PHP
			include '../../../_config/set.php';
			include \$engine_dir.'/_engine/promotion/fb_feed.csv.php';
		?>";

		$fp = fopen($root_dir.'/_data/compare/fb/product.csv.php', 'w');
		if($fp) {
			fwrite($fp, $feed);
			fclose($fp);
		} else {
			msg('Feed파일 생성 실패');
		}
	}

	$wec = new weagleEyeClient($_we, 'Etc');
	$wec->call('setExternalService', array(
		'service_name' => 'pixel',
		'use_yn' => ($_POST['use_fb_pixel'] == 'Y' ? 'Y' : 'N'),
		'root_url' => $root_url,
		'extradata' => $_POST['fb_pixel_id']
	));

	require $engine_dir.'/_manage/config/config.exe.php';

?>