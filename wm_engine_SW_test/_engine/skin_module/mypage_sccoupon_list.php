<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 사용 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp="";
	$_line=getModuleContent("mypage_sccoupon_list");
	while($cp=sccouponDownList()) {
		$cp['idx']=$idx;
		$_tmp.=lineValues("mypage_sccoupon_list", $_line, $cp);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_sccoupon_list']=$_tmp;

?>