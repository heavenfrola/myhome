<?PHP

	checkBasic();
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$check_pno = numberOnly($_POST['check_pno']);
	$ext1 = numberOnly($_POST['ext1']);
	$ext2 = numberOnly($_POST['ext2']);
	$exec = $_POST['exec'];

	$all_ord = count($check_pno);
	if($all_ord<1) msg("하나 이상의 주문을 선택하세요");
	$oii = $total_ea = $success_ea = 0;

	if($exec=="dlv") {

	}
	else {
        function makeChanageResult($ono, $opno, $msg) {
            global $process_result, $total_ea, $success_ea;

			if (isset($process_result[$ono][$opno]) == true) {
                return;
            }

            $process_result[$ono][$opno] = $msg;
            $total_ea++;
            if($msg == 'OK') {
                $success_ea++;
            }
        }

		$order_multi=true;
        $process_result = array();
		$ext=$ext2;
		if($all_ord > 500) msg("500개 이상의 주문 처리 시 지연될 수 있으므로 처리 주문 수를 조절해주시기 바랍니다.");
		$addq=" and `no` in (".implode(",", $check_pno).")";
		$multi_sql = $pdo->iterator("select * from `$tbl[order]` where 1 $addq");
		foreach ($multi_sql as $data) {
            $total++;
            if($data['stat'] > 10 && $data['stat'] != 40) {
                makeChanageResult($data['ono'], $opno, '취소상태의 주문서입니다.');
                continue;
            }
            /*
            if($data['stat'] == $ext) {
                $process_result[$data['ono']] = '변경 전후의 상태가 같습니다.';
                continue;
            }
            */

			include $engine_dir."/_manage/order/order_stat.php";
		}

		if($oii == 0) msg('변경된 주문이 없습니다.');
	}

    $result = array(
        'total' => $total_ea,
        'success' => $success_ea,
        'datas' => $process_result,
    );

?>
<form id="resultFrm" method="POST" action="./index.php" target="_parent">
    <input type="hidden" name="body" value="order@order_multi_result">
    <input type="hidden" name="result" value="<?=inputText(rawurlencode(json_encode($result)))?>">
</form>
<script>
    document.getElementById('resultFrm').submit();
</script>