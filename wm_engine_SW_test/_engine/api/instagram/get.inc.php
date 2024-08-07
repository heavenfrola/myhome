<?PHP

	use Wing\HTTP\CurlConnection;

	if(isset($cfg['instagram_access_token']) == false) return;
	if(empty($cfg['instagram_access_token']) == true) return;

    $instagram_cache_path = $root_dir.'/_data/cache/instagram.cache.php';
    if (file_exists($instagram_cache_path) == false) {
        define('__INSTAGRAM_FORCE__', true);
    }

	if(defined('__INSTAGRAM_FORCE__') == false) {
        if (file_exists($instagram_cache_path) == true) {
            $instagram_time = filemtime($instagram_cache_path);
        } else {
            $instagram_time = 0;
        }
		if(isset($cfg['instagram_interval']) == false || empty($cfg['instagram_interval']) == true || $cfg['instagram_interval'] < 3600) {
			$cfg['instagram_interval'] = 3600;
		}
		if($instagram_time+$cfg['instagram_interval'] > $now) return;
	}

	// connect instagram
	$curl = new CurlConnection('https://graph.instagram.com/me/media?fields=id,caption,media_url,permalink,media_type&access_token='.$cfg['instagram_access_token']);
	$curl->exec();
	$json = json_decode($curl->getResult(true));
	if($json->error) {
		fwriteTo('_data/instagram.log', $curl->getResult(true)."\n");
		//echo "<div>Instagram Test Message : ".$curl->getResult(true)."</div>";
		return;
	}

	// make instagram cache file
	$cache = "";
	$cnt = 0;
    $scfg->def('instagram_get_mov', 'N');
	if(count($json->data) > 0) {
		foreach($json->data as $ival) {
			if (
                !(
                    $ival->media_type == 'IMAGE' ||
                    $ival->media_type == 'CAROUSEL_ALBUM' ||
                    ($scfg->comp('instagram_get_mov', 'Y') == true && $ival->media_type == 'VIDEO')
                )
            ) continue;

			$cache .= "
		\$_instagram_data[$cnt]['id'] = '$ival->id';
		\$_instagram_data[$cnt]['link'] = '$ival->permalink';
		\$_instagram_data[$cnt]['image'] = '$ival->media_url';
		\$_instagram_data[$cnt]['media_type'] = '$ival->media_type';
			";
			$cnt++;
		}
	}

	$cache = "<?PHP\n".$cache."?>";
	$fp = @fopen($instagram_cache_path, 'w');
	if($fp) {
		fwrite($fp, $cache);
		fclose($fp);
	}

?>