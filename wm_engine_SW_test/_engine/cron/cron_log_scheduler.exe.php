<?PHP

	define('__LOG_SCHEDULER__', true);

	$no_qcheck = true;
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$start_time = date('Y-m-d H:i:s');
	$queries = 0;
	$max_no = $pdo->row("select max(no) from {$tbl['log_schedule']}");
	$res = $pdo->iterator("select query from {$tbl['log_schedule']} where no <='$max_no' order by no asc");
    foreach ($res as $data) {
		$pdo->query($data['query']);
		$queries++;
	}

	$pdo->query("delete from {$tbl['log_schedule']} where no <= '$max_no'");

	echo json_encode(array(
		'start_time' => $start_time,
		'finish_time' => date('Y-m-d H:i:s'),
		'queries' => $queries,
	));

?>