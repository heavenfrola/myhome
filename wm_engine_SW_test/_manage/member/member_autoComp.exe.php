<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  고객CRM 자동완성
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$keyword = addslashes(trim($_GET['keyword']));
	$keyword2 = str_replace('-', '', $keyword);

	if(!$keyword) return;
	if(in_array(substr($keyword, -3), array('ㄱ', 'ㄴ', 'ㄷ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅅ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'))) {
		exit('ing');
	}

	if($keyword2) $w .= " or replace(cell, '-', '') like '$keyword%'";

	$result = '';
	$res = $pdo->iterator("select no, member_id, name, cell, email from $tbl[member] where (member_id like '$keyword%' or name like '$keyword%' or email like '$keyword%' $w) order by name asc limit 15");
    foreach ($res as $data) {
		$data['name'] = stripslashes($data['name']);

		$result .= trim("
		<tr onclick=\"location.href='?body=member@member_view.frm&mno=$data[no]&mid=$data[member_id]'\">
			<th>$data[name]</th>
			<td>$data[email]</td>
			<td>$data[cell]</td>
		<tr>
		");
	}

	if(!$result) return;

?>
<table>
	<caption class="hidden">자동완성</caption>
	<?=$result?>
</table>