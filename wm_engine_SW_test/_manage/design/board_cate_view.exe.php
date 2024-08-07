<?PHP

	printAjaxHeader();

	$selected_cate = $_GET['selected_cate'];
	if(isset($_GET['db']) == false) return;

    $param = array(':db' => $_GET['db']);

	$user_cate = $pdo->row('select use_cate from mari_config where db=:db', $param);
	if($user_cate == 'N' || !$user_cate) return;

	if($pdo->row('select count(*) from mari_cate where db=:db', $param) == 0) return;

	$bcsql = $pdo->iterator('SELECT * FROM mari_cate where db=:db order by sort asc', $param);
	$result  = '<select name="board_cate" style="min-width:100px;">';
	$result .= "<option value=''>:: 전체 ::</option>";
    foreach ($bcsql as $bcdata) {
		$bcdata['name'] = stripslashes($bcdata['name']);
		$selected = ($selected_cate == $bcdata['no']) ? 'selected' : '';
		$result .= "<option value='{$bcdata['no']}' $selected>{$bcdata['name']}</option>";
	}
	$result .= "</select>";

	exit($result);

?>