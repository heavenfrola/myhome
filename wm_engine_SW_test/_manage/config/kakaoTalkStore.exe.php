<?PHP

	use Wing\API\Kakao\KakaoTalkStore;

	if($_POST['kakaoTalkStore_key']) {
		$_POST['kakaoTalkStore_key'] = $cfg['kakaoTalkStore_key'] = addslashes(trim($_POST['kakaoTalkStore_key']));
	}

    if (empty($_POST['kakaoTalkStore_key']) == true) {
        $_POST['use_kakaoTalkStore'] = 'N';
    } else {
        $kts = new KakaoTalkStore();

        if($_POST['use_kakaoTalkStore'] == 'Y') {
            if(!$_POST['kakaoTalkStore_key']) {
                msg('카카오톡 스토어 seller_app_key 를 입력해주세요.');
            }

            include $engine_dir.'/_config/tbl_schema.php';
            $pdo->query($tbl_schema['product_talkstore']);
            $pdo->query($tbl_schema['product_talkstore_announce']);
            $pdo->query($tbl_schema['talkstore_api_log']);

            addField($tbl['product'], 'use_talkstore', 'enum("N","Y") not null default "N"');
            addField($tbl['order'], 'talkstore', 'enum("N","Y") not null default "N"');
            addField($tbl['order'], 'talkstore_last', 'varchar(14) not null');
            addField($tbl['order_product'], 'talkstore_ono', 'varchar(50) not null default "0"');
            addField($tbl['order_product'], 'talkstore_deliveryAmount', 'int(10) not null default "0"');
            addField($tbl['order_product'], 'talkstore_deliveryId', 'varchar(15) not null default ""');
            addField($tbl['qna'], 'talkstore_qnaId', 'varchar(25) not null comment "카카오톡 스토어 qnaId"');

            $pdo->query("alter table $tbl[product] add index use_talkstore(use_talkstore)");
            $pdo->query("alter table $tbl[order] add index talkstore(talkstore)");
            $pdo->query("alter table $tbl[order] add index talkstore_last(talkstore_last)");
            $pdo->query("alter table $tbl[order_product] add index talkstore_ono(talkstore_ono)");
            $pdo->query("alter table $tbl[qna] add index talkstore_qnaId(talkstore_qnaId)");

            // 스토어와 셀러 연결
            $kts->storeRegister();
        }

        $kts->setCron($_POST['use_kakaoTalkStore']);
    }

	$wec = new weagleEyeClient($_we, 'Etc');
	$wec->call('setExternalService', array(
		'service_name' => 'kakaoTalkStore',
		'use_yn' => ($_POST['use_kakaoTalkStore'] == 'Y' ? 'Y' : 'N'),
		'root_url' => $root_url,
		'extradata' => $_POST['kakaoTalkStore_key']
	));

	if(!$_POST['kakaoTalkStore_msale']) $_POST['kakaoTalkStore_msale'] = 'N';
	if(!$_POST['kakaoTalkStore_esale']) $_POST['kakaoTalkStore_esale'] = 'N';

	include 'config.exe.php';

?>