<?PHP

	if($_POST['exec'] == 'generate_key') {
		unset($_POST);

		$_POST['use_zigzag'] = 'Y';
		$_POST['zigzag_apikey'] = base64_encode(md5(time().rand(0,99999)));

		$no_reload_config = true;
		include $engine_dir.'/_manage/config/config.exe.php';

		exit($_POST['zigzag_apikey']);
	}

	if($_POST['use_zigzag'] == 'Y') {
		if(isset($cfg['zigzag_apikey']) == false || empty($cfg['zigzag_apikey']) == true) {
			$_POST['zigzag_apikey'] = base64_encode(md5(time().rand(0,99999)));
		}

		makeFullDir('_data/compare/zigzag');

		fwriteTo('_data/compare/zigzag/engine.php', "<?PHP\n\n\tinclude '../../../_config/set.php';\n\tinclude \$engine_dir.'/_engine/promotion/zigzag_ep.exe.php';\n\n?>", 'w');
		fwriteTo('_data/compare/zigzag/category.php', "<?PHP\n\n\tinclude '../../../_config/set.php';\n\tinclude \$engine_dir.'/_engine/promotion/zigzag_cate.exe.php';\n\n?>", 'w');

        $wec = new weagleEyeClient($_we, 'account');
        $wec->call('setZigzag', array(
            'use_zigzag' => $_POST['use_zigzag'],
        ));
	}

	include $engine_dir.'/_manage/config/config.exe.php';

?>