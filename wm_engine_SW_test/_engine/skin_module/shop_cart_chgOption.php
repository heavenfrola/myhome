<?PHP

	$prdCart = new OrderCart();
	$prdCart->skip_dlv = 'Y';
	$prdCart->addCart($prd);
	$prdCart->complete();

	$prd['parent'] = $prd['pno'];
	$option_list_asql = " and necessary!='P'";
	$_line = getModuleContent('cart_chgopt_list');
	$_tmp = '';
	while($opt = prdOptionList()) {
		$_tmp .= $opt['hidden_str'];
		$_tmp .= lineValues('cart_chgopt_list', $_line, $opt);
	}
	$_replace_code[$_file_name]['cart_options'] = listContentSetting($_tmp, $_line);

	$_replace_code[$_file_name]['cart_form_start'] = "
	<form name=\"prdFrm\" class='cart_option_change_form' method=\"post\" action='/main/exec.php' target='hidden$now' onsubmit='this.target=hid_frame'>
		<input type=\"hidden\" name=\"exec_file\" value=\"cart/cart.exe.php\">
		<input type=\"hidden\" name=\"exec\" value=\"add\">
		<input type=\"hidden\" name=\"pno\" value=\"$prd[hash]\">
		<input type=\"hidden\" name=\"total_prc\" value=\"$prd[sell_prc]\">
		<input type=\"hidden\" name=\"new_total_prc\" value=\"$prd[sell_prc]\">
		<input type=\"hidden\" name=\"ea_type\" value=\"$prd[ea_type]\">
		<input type=\"hidden\" name=\"ori_cno\" value=\"$prd[no]\">
		<input type=\"hidden\" name=\"min_ord\" value=\"{$prd['min_ord']}\">
	";
	$_replace_code[$_file_name]['cart_form_end'] = '<input type="hidden" name="opt_no" value="'.$opt_no.'"></form>';
	$_replace_code[$_file_name]['cart_prd_nm'] = $prd['name'];
	$_replace_code[$_file_name]['cart_prd_opt'] = $prd['option'];
	$_replace_code[$_file_name]['cart_prd_prc'] = parsePrice(($prdCart->getData('pay_prc')/$prd['buy_ea']), true);
	$_replace_code[$_file_name]['cart_prd_total_prc'] = parsePrice($prdCart->getData('pay_prc'), true);
	$_replace_code[$_file_name]['cart_prd_img'] = $prd['img'];
	$_replace_code[$_file_name]['cart_buy_ea'] = $prd['buy_ea'];
	$_replace_code[$_file_name]['cart_detail1'] = prdContent(1);

?>