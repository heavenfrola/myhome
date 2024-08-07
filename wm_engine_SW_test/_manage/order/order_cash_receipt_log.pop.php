<?php

    define('__pop_width__', '800px');

	include __ENGINE_DIR__.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 10;
	$block = 10;

	$NumTotalRec = $pdo->row(
        "select count(*) from {$tbl['cash_receipt_log']} where cno=?",
        array($_GET['cno'])
    );
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
    $pg_res = $PagingResult['PageLink'];
    $pg_res = preg_replace('/href="([^"]+)"/', 'href="#" onclick="log.open(\'$1\'); return false;"', $pg_res);
	$idx = $NumTotalRec-($row*($page-1));

    $res = $pdo->iterator(
        "select * from {$tbl['cash_receipt_log']} where cno=? order by no desc ".$PagingResult['LimitQuery'],
        array($_GET['cno'])
    );

    function parseReceiptLog(&$res)
    {
        $data = $res->current();
        if ($data == false) return false;

        if ($data['system'] == 'Y') {
            $data['admin_id'] = '';
        }
        $data['system_str'] = ($data['system'] == 'Y') ? '○' : '×';

        $res->next();
        return $data;
    }

?>
<style type="text/css">
.receipt_tbl td.stat2 {
    color: <?=$_order_color_def[5]?>;
}
.receipt_tbl td.stat3 {
    color: <?=$_order_color_def[13]?>;
}
</style>
<table class="tbl_col receipt_tbl">
    <caption>현금영수증 상태 변경 내역</caption>
    <thead>
        <th scope="col">순번</th>
        <th scope="col">변경 전</th>
        <th scope="col">변경 후</th>
        <th scope="col">금액</th>
        <th scope="col">처리자</th>
        <th scope="col">처리아이피</th>
        <th scope="col">처리일시</th>
        <th scope="col">자동</th>
    </thead>
    <tbody>
        <?php while ($data = parseReceiptLog($res)) { ?>
        <tr>
            <td><?=$idx--?></td>
            <td class="stat<?=$data['ori_stat']?>"><?=$_order_cash_stat[$data['ori_stat']]?> →</td>
            <td class="stat<?=$data['stat']?>"><?=$_order_cash_stat[$data['stat']]?></td>
            <td class="right"><?=parsePrice($data['price'], true)?><?=$cfg['currency']?></td>
            <td><?=$data['admin_id']?></td>
            <td><?=$data['remote_addr']?></td>
            <td><?=date('Y-m-d H:i', $data['reg_date'])?></td>
            <td><?=$data['system_str']?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="box_bottom">
    <?=$pg_res?>
</div>
<div class="box_bottom">
    <span class="box_btn_s gray"><input type="button" value="창닫기" onclick="log.close();"?></span>
</div>