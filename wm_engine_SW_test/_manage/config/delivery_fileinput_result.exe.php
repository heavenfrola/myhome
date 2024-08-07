<?PHP

	if($_POST['exec'] == 'download') {
		if(count($_POST['csv']) == 0) {
			msg('다운로드 가능한 배송지가 없습니다.');
		}
		$csv = mb_convert_encoding(implode($_POST['csv']), 'EUC-KR', _BASE_CHARSET_);

		Header('Content-Type: text/csv');
		Header('Content-Disposition: attachment; filename=실패내역'.date('Ymd_His').'.csv');
		Header('Content-Length: '.strlen($csv));
		Header('Pragma: no-cache');
		Header('Expires: 0');
		flush();

		exit($csv);
	}

?>