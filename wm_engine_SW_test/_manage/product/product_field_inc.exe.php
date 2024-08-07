<?PHP

	printAjaxHeader();

	if($_POST['from_ajax'] == 'true') {
		$pno = numberOnly($_POST['pno']);
		$data['fieldset'] = numberOnly($_POST['fieldset']);
	}

	$fvalues = array();
	$fres = $pdo->iterator("select fno, value from $tbl[product_filed] where pno='$pno'");
    foreach ($fres as $fdata) {
		$fvalues[$fdata['fno']] = htmlspecialchars(stripslashes($fdata['value']));
	}

	$fres = $pdo->iterator("select * from $tbl[product_filed_set] where category in ('0', '$data[fieldset]') order by sort asc, no asc");
    foreach ($fres as $fdata) {
		$fdata['name'] = stripslashes($fdata['name']);
		$fdata['value'] = $fvalues[$fdata['no']];
		$fdata['default_value'] = inputText($fdata['default_value']);

		if($fdata['ftype'] == 1) {
			if($fdata['category'] > 0) {
				$tmp = "<textarea name=\"field{$fdata[no]}\" class=\"txta\" style=\"height:50px;\" placeholder=\"{$fdata['default_value']}\">$fdata[value]</textarea>";
			} else {
				$tmp = "<input type=\"text\" name=\"field{$fdata['no']}\" value=\"$fdata[value]\" class=\"input input_full\">";
			}
		} else {
			$tmp  = "<select name=\"field{$fdata[no]}\">";
			$tmp .= "<option value=\"\">::$fdata[name]::</option>";
			$_options = explode(',', stripslashes($fdata['soptions']));
			foreach($_options as $key => $val) {
				if($val) $tmp .= "<option value=\"$val\" ".checked($fdata['value'], $val, 1).">$val</option>";
			}
			$tmp .= "</select>";
		}

		$_fno = ($fdata['category'] == 0) ? '1' : '2';
		${'prd_fieldset'.$_fno} .= "
			<tr>
				<th scope=\"row\" class=\"left\">$fdata[name]</th>
				<td class=\"left\">$tmp</td>
			</tr>
		";
	}

	if($_POST['from_ajax'] == 'true') exit($prd_fieldset2);

	unset($tmp, $fres, $_fno, $_options, $fvalues);

?>