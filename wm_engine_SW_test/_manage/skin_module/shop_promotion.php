<?PHP

	$_replace_code[$_file_name]['promotion_list'] = '';
	$_replace_hangul[$_file_name]['promotion_list'] = '프로모션기획전목록';
	$_code_comment[$_file_name]['promotion_list'] = '사용중인 프로모션 기획전 목록입니다.';
	$_replace_datavals[$_file_name]['promotion_list'] = '프로모션기획전명:promotion_nm;프로모션번호:no;프로모션링크:link;시작일시:date_start_s;종료일시:date_end_s;선택된프로모션:is_active:현재 클릭된 프로모션일 경우 "active" 문자열이 출력됩니다.;';

	$_replace_code[$_file_name]['promotion_nm'] = $pdata['promotion_nm'];
	$_replace_hangul[$_file_name]['promotion_nm'] = '프로모션명';
	$_auto_replace[$_file_name]['promotion_nm'] = 'Y';
	$_code_comment[$_file_name]['promotion_nm'] = '선택 된 프로모션기획전의 이름';

	$_replace_code[$_file_name]['promotion_date_start'] = $pdata['period_s'];
	$_replace_hangul[$_file_name]['promotion_date_start'] = '프로모션시작일';
	$_auto_replace[$_file_name]['promotion_date_start'] = 'Y';
	$_code_comment[$_file_name]['promotion_date_start'] = '선택 된 프로모션 기획전의 시작일. 무제한일 경우 "무제한"이 출력됩니다.';

	$_replace_code[$_file_name]['promotion_date_end'] = $pdata['period_e'];
	$_replace_hangul[$_file_name]['promotion_date_end'] = '프로모션종료일';
	$_auto_replace[$_file_name]['promotion_date_end'] = 'Y';
	$_code_comment[$_file_name]['promotion_date_end'] = '선택 된 프로모션 기획전의 종료일. 무제한일 경우 출력되지 않습니다.';

	$_replace_code[$_file_name]['promotion_nm'] = $pdata['promotion_nm'];
	$_replace_hangul[$_file_name]['promotion_nm'] = '프로모션명';
	$_auto_replace[$_file_name]['promotion_nm'] = 'Y';
	$_code_comment[$_file_name]['promotion_nm'] = '선택 된 프로모션기획전의 이름';

	$_replace_code[$_file_name]['promotion_content'] = '';
	$_replace_hangul[$_file_name]['promotion_content'] = '프로모션내용';
	$_auto_replace[$_file_name]['promotion_content'] = 'Y';
	$_code_comment[$_file_name]['promotion_content'] = '선택 된 프로모션의 상세 설명';

	$_replace_code[$_file_name]['promotion_m_content'] = '';
	$_replace_hangul[$_file_name]['promotion_m_content'] = '프로모션내용모바일';
	$_auto_replace[$_file_name]['promotion_m_content'] = 'Y';
	$_code_comment[$_file_name]['promotion_m_content'] = '선택 된 프로모션의 모바일 상세 설명(미사용시 PC프로모션 내용이 출력됩니다)';

	$_replace_code[$_file_name]['promotion_pgrp_list'] = '';
	$_replace_hangul[$_file_name]['promotion_pgrp_list'] = '프로모션상품그룹목록';
	$_code_comment[$_file_name]['promotion_pgrp_list'] = '현재 프로모션 기획전에 등록된 프로모션 상품그룹을 출력합니다.';
	$_replace_datavals[$_file_name]['promotion_pgrp_list'] = '그룹명:pgrp_nm;띠배너명:banner_text;배너이미지PC:upfile1:PC용배너이미지;배너이미지모바일:upfile2:모바일용배너이미지;상품목록:product_list;배너링크:banner_link;배너이미지PC(링크포함):banner1;배너이미지모바일(링크포함):banner2;';

	$_replace_code[$_file_name]['promotion_product_list'] = '';
	$_replace_hangul[$_file_name]['promotion_product_list'] = '상품목록';
	$_code_comment[$_file_name]['promotion_product_list'] = '프로모션상품그룹목록에 의해 호출되며, 등록된 상품들이 출력됩니다.';
	$_replace_datavals[$_file_name]['promotion_product_list'] = $_replace_datavals['common_module']['product_box'];

?>