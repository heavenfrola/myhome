<?PHP

	// 입점몰 사용시 관련 테이블 설정
	if($_POST['use_partner_shop'] == 'Y') {
		include_once $engine_dir.'/_config/tbl_schema.php';

		// 테이블 추가
		$pdo->query($tbl_schema['partner_shop']);
		$pdo->query($tbl_schema['partner_delivery']);
		$pdo->query($tbl_schema['partner_product_log']);
		if(!fieldExist($tbl['order_account'], 'startdate')) {
			$pdo->query("drop table $tbl[order_account]");
		}
		$pdo->query($tbl_schema['order_account']);
		$pdo->query($tbl_schema['order_account_log']);
		$pdo->query($tbl_schema['order_account_refund']);

		// 필드 추가
		addField($tbl['mng'], 'partner_no', 'varchar(20) not null default ""');
		addField($tbl['delivery_area'], 'partner_no', 'varchar(20) not null default ""');
		addField($tbl['delivery_area_detail'], 'partner_no', 'varchar(20) not null default ""');
		addField($tbl['delivery_url'], 'partner_no', 'int(10) not null default "0"');
		addField($tbl['product'], 'partner_no', 'int(10) not null default "0" comment "파트너 코드"');
		addField($tbl['product'], 'partner_stat', 'char(1) not null default "0" comment "1.대기 2.승인 3.반려"');
		addField($tbl['product'], 'partner_rate', 'double(4, 2) not null default "0" comment "입점상품 수수료"');
		addField($tbl['product'], 'ori_no', 'int(10) not null default "0", comment "원본 상품 번호"');
		addField($tbl['product'], 'dlv_type', 'char(1) not null default "0" comment "0.일반 1.본사직배송"');
		addField($tbl['product_refprd'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['product_filed'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['product_icon'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['product_image'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['product_option_item'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['product_option_set'], 'partner_no', 'int(10) not null default "0"');
		addField($tbl['product_option_set'], 'ori_no', 'int(10) not null default "0"');
		addField($tbl['order_product'], 'partner_no', 'int(10) not null default "0"');
		addField($tbl['order_product'], 'fee_rate', 'double(3,1) not null default "0"');
		addField($tbl['order_product'], 'fee_prc', 'double(10,2) not null default "0"');
		addField($tbl['order_product'], 'dlv_type', 'char(1) not null default "0" comment "0.일반 1.본사직배송"');
		addField($tbl['order_product'], 'cpn_rate', 'double(5,2) not null default "0"');
		addField($tbl['order_product'], 'cpn_fee', 'double(10,2) not null default "0"');
		addField($tbl['order_product'], 'account_idx', 'int(10) not null default "0" comment "정산서 인덱스"');
		addField($tbl['order'], 'account_stat', 'char(1) not null default "0"');
		addField($tbl['coupon'], 'partner_type', 'char(1) not null default "0" comment "0.전체 1.본사 2.파트너, 3.본사,파트너"');
		addField($tbl['coupon'], 'partner_no', 'int(10) not null default "0" comment "적용입점파트너"');
		addField($tbl['coupon'], 'partner_fee', 'double(8,2) not null default "0" comment "파트너 부담율"');
		addField($tbl['coupon_download'], 'partner_type', 'char(1) not null default "0" comment "0.전체 1.본사 2.파트너, 3.본사,파트너"');
		addField($tbl['coupon_download'], 'partner_no', 'int(10) not null default "0" comment "적용입점파트너"');
		addField($tbl['coupon_download'], 'partner_fee', 'double(8,2) not null default "0" comment "파트너 부담율"');
		addField($tbl['order_memo'], 'partner_no', 'int(10) not null default "0" comment "작성파트너"');
		addField('erp_complex_option', 'ori_no', 'int(10) not null default "0"');
		$pdo->query("alter table $tbl[mng] change level level char(1) not null default '3'");

		// 인덱스 추가
        addIndex($tbl['product'], 'partner_no', 'partner_no');
        addIndex($tbl['order_product'], 'partner_no', 'partner_no');
        addIndex($tbl['order_product'], 'account_idx', 'account_idx');
        addIndex($tbl['order'], 'account_stat', 'account_stat');
        addIndex($tbl['order_memo'], 'partner_no', 'partner_no');
        addIndex($tbl['delivery_url'], 'partner_no', 'partner_no');
	}

	if($_POST['use_partner_shop'] != 'Y') {
		$_POST['partner_prd_accept'] = 'N';
		$_POST['use_partner_delivery'] = 'N';
	}

	if($_POST['use_partner_shop'] == 'Y') {
		$_POST['delivery_free_milage'] = 'N';
		$_POST['prdprc_sale_use'] = 'N';
	}

	include $engine_dir.'/_manage/config/config.exe.php';

?>