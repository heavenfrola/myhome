<?

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페지이 다운로드 쿠폰 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp="";
	$_line=getModuleContent("mypage_coupon_list");
	while($cp=couponDownList(" ".__currency__)){
		$cp[idx]=$idx;
        if ($cp['sale_type'] === 'm') {
            /**
             * 금액할인
             * 최대할인액 : 할인금액과 동일하게 표기
             */
            $cp['sale_prc'] = number_format($cp['sale_prc']).' '.__currency__;
            $cp['sale_limit'] = $cp['sale_prc'];
        } else {
            /**
             * 비율할인 (sale_type : p)
             * 최대할인액 : 숫자인경우 해당금액 표기 or 무제한으로 표기
             */
            $cp['sale_prc'] = $cp['sale_prc']." %";
            $cp['sale_limit']=(numberOnly($cp['sale_limit'])) ? $cp['sale_limit'] : __lang_cpn_info_unlimited__;
        }
		$_tmp .= lineValues("mypage_coupon_list", $_line, $cp);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_coupon_list]=$_tmp;

?>