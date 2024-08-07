<?PHP

	$show_dir = $root_dir.'/_data/compare/daumDB';
	makeFullDir('_data/compare/daumDB');


	// 로고이미지 확인
	foreach($_FILES as $field => $resource) {
		$size = getimagesize($resource['tmp_name']);
		if($size[0] != 65 || $size[1] != 15 || $size[2] != 1) msg("$field 이미지 사이즈가 정확하지 않습니다.\\t\\n이미지는 반드시 가로65 x 세로15 픽셀의 gif 파일로 업로드 해주시기 바랍니다.");
	}

	move_uploaded_file($_FILES['logoimg1']['tmp_name'], $show_dir."/$_POST[shopid]_6515.gif");
	move_uploaded_file($_FILES['logoimg2']['tmp_name'], $show_dir."/$_POST[shopid]_6515_c.gif");
	chmod($show_dir."/$_POST[shopid]_6515.gif", 0777);
	chmod($show_dir."/$_POST[shopid]_6515_c.gif", 0777);


	// xml파일 생성
	$_POST['corppt'] = trim($_POST['corppt']);
	if(strlen($_POST['corppt']) > 200) msg("회사소개는 공백포함 200자 이내로 입력해 주셔야 합니다.\\t\\n(한글은 2자로 계산됩니다)");

	foreach($_POST as $key => $val) {
		if($key == 'body' || $key == 'url') continue;
		if(!$val) continue;

		$xml .= "<$key><![CDATA[$val]]></$key>\n";
	}

	ob_start();
	echo "<?xml version='1.0' encoding='EUC-KR' ?>\n";

?>
<daumSHow>
	<info>
		<modify><?=date('Y-m-d')?></modify>
		<description>위사몰 다음쇼핑하우 가입양식 XML</description>
	</info>
	<data>
		<?=$xml?>
	</data>
</daumSHow>
<?

	$xml = ob_get_contents();
	ob_end_clean();

	$fp = fopen($show_dir.'/account.xml', 'w');
	fwrite($fp, $xml);
	fclose($fp);

	$wec_account = new weagleEyeClient($_we, 'account');
	$wec_account->queue('daumSHowAccount', $wec->config['account_idx'], $_POST['url']);
	$wec_account->send_clean();


	if($wec_account->result != 'OK') {
		alert(php2java($wec_account->result));
		exit;
	}

	msg("입점 신청이 완료되었습니다.\\n\\n입점 승인에는 2~3일이 소요되며, 승인이후 다음쇼핑하우 사용여부를 설정하실수 있습니다.\\t", '?body=openmarket@show_setup', 'parent');

?>