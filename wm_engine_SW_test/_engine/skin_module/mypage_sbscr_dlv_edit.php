<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 배송지변경
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['form_start'] = "<form name='dlv_edit' method='POST' action='$root_url/main/exec.php' target='hidden$now'>
<input type='hidden' name='exec_file' value='mypage/mypage_sbscr.exe.php'/>
<input type='hidden' name='type' value='edit'/>
<input type='hidden' name='sbono' value='$sno'/>
<input type='hidden' name='order_type' value='$order_type'/>
";
	$_replace_code[$_file_name]['form_end'] = "</form>";

	$_replace_code[$_file_name]['old_addr'] = $sdata['addressee_addr1'].' '.$sdata['addressee_addr2'];

	$_replace_code[$_file_name]['addressee_name'] = $sdata['addressee_name'];

	$_replace_code[$_file_name]['addressee_phone']=$sdata['addressee_phone'];
	$_replace_code[$_file_name]['addressee_cell']=$sdata['addressee_cell'];
	$_replace_code[$_file_name]['addressee_zip']=$sdata['addressee_zip'];
	$_replace_code[$_file_name]['addressee_addr1']=$sdata['addressee_addr1'];
	$_replace_code[$_file_name]['addressee_addr2']=$sdata['addressee_addr2'];

?>