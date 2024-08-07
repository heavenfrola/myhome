<?PHP

	header("Content-Type: image/png;");

	// 사이즈 계산
	$used = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where `filetype` = 3");
	$total = $_SESSION['h_spec']['img_limit']*1024*1024;
	$per = ceil(number_format(($used / $total) * 100));
	$left = 100 - $per;

	$point2 = ceil($_SESSION['h_spec']['img_limit'] / 2);
	$point3 = $_SESSION['h_spec']['img_limit'];

	$per_width = $width * ($per * 0.01);

	$used_str = filesizeStr($used);
	$total_str = filesizeStr($total);


	// 그래프 출력
	$graph = imagecreatetruecolor($width, $height);
	imagefill($graph, 2, 2, hexdec('#ffffff'));

	$g_height = $height - 40;

	imagerectangle($graph, 0, 0, $width-1, $g_height - 1, hexdec('#385d8a'));
	imagerectangle($graph, 1, 1, $width-2, $g_height - 2, hexdec('#385d8a'));
	imagefill($graph, 2, 2, hexdec('#4f81bd'));

	imagerectangle($graph, 0, 0, $per_width, $g_height - 1, hexdec('#71893f'));
	imagerectangle($graph, 1, 1, $per_width-1, $g_height - 2, hexdec('#71893f'));
	imagefill($graph, 3, 3, hexdec('#9bbb59'));


	list ($l, $dummy, $r) = imagettfbbox (20, 0, $engine_dir.'/_manage/image/font/ARIAL.TTF', $per.'%');
	$fontwidth = abs($l) + abs($r);
	imagettftext($graph, 12, 0, ($width - $fontwidth - 10), 16, hexdec('#ffffff'), $engine_dir.'/_manage/image/font/ARIAL.TTF', $per.'%');

	imageline($graph, ($width * 0.25)-1, $g_height-5, ($width * 0.25)-1, $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width * 0.25), $g_height-5, ($width * 0.25), $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width * 0.25)+1, $g_height-5, ($width * 0.25)+1, $g_height+4, hexdec('#c0504d'));

	imageline($graph, ($width / 2)-1, $g_height-5, ($width / 2)-1, $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width / 2), $g_height-5, ($width / 2), $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width / 2)+1, $g_height-5, ($width / 2)+1, $g_height+4, hexdec('#c0504d'));

	imageline($graph, ($width * 0.75)-1, $g_height-5, ($width * 0.75)-1, $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width * 0.75), $g_height-5, ($width * 0.75), $g_height+4, hexdec('#c0504d'));
	imageline($graph, ($width * 0.75)+1, $g_height-5, ($width * 0.75)+1, $g_height+4, hexdec('#c0504d'));

	imagettftext($graph, 10, 0, 0, $g_height+18, hexdec('#000000'), $engine_dir.'/_manage/image/font/ARIAL.TTF', '0');

	list ($l, $dummy, $r) = imagettfbbox (10, 0, $engine_dir.'/_manage/image/font/ARIAL.TTF', $point2.'MB');
	$fontwidth = abs($l) + abs($r);
	imagettftext($graph, 10, 0, (($width / 2) - ($fontwidth / 2)), $g_height+18, hexdec('#000000'), $engine_dir.'/_manage/image/font/ARIAL.TTF', $point2.'MB');

	list ($l, $dummy, $r) = imagettfbbox (10, 0, $engine_dir.'/_manage/image/font/ARIAL.TTF', $point3.'MB');
	$fontwidth = abs($l) + abs($r);
	imagettftext($graph, 10, 0, ($width - $fontwidth), $g_height+18, hexdec('#000000'), $engine_dir.'/_manage/image/font/ARIAL.TTF', $point3.'MB');

	imagettftext($graph, 9, 0, 0, 55, hexdec('#000000'), $engine_dir.'/_manage/image/font/MALGUN.TTF', iconv(_BASE_CHARSET_, 'utf-8', '무료 본문삽입사진 사용량 : '.$used_str.'/'.$total_str.' ('.$per.'%)'));

	echo imagepng($graph);

?>