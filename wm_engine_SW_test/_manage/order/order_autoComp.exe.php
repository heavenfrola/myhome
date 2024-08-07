<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  고객CRM 자동완성
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$keyword = addslashes(trim($_GET['keyword']));

	if(!$keyword) return;
	if(in_array(substr($keyword, -3), array('ㄱ', 'ㄴ', 'ㄷ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅅ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'))) {
		exit('ing');
	}

	$result = '';
	$res = $pdo->iterator("select ono, buyer_name, buyer_email, ono from $tbl[order] where (buyer_name like '$keyword%' or ono like '%$keyword%') order by date1 asc limit 15");
    foreach ($res as $data) {
		$data['name'] = stripslashes($data['name']);

		$result .= trim("
		<tr onclick=\"location.href='?body=order@order_view.frm&ono=$data[ono]'\">
			<th>$data[buyer_name]</th>
			<td>$data[buyer_email]</td>
			<td>$data[ono]</td>
		<tr>
		");
	}

	if(!$result) return;

?>
<table>
	<caption class="hidden">자동완성</caption>
	<?=$result?>
</table>