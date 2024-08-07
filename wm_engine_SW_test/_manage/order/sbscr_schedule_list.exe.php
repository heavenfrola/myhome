<?PHP

	printAjaxHeader();

	if($data['no']) $_GET['no'] = $data['no'];
	else $data['no'] = $_GET['idno'];

	if($data['sbono']) $_GET['sbono'] = $data['sbono'];
	if(!$_GET['sbono']) $data['sbono'] = $pdo->row("select sbono from $tbl[sbscr] where no='$data[no]'");

	$bvsql = "select *, sum(total_prc) AS tot_pay_prc, count(*) as cnt from $tbl[sbscr_schedule] where sbono='{$data['sbono']}' group by `date`";

	// 페이징
	include_once $engine_dir."/_engine/include/paging.php";

	$bvpage = numberOnly($_GET['bvpage']);
	if(!$bvrow) $bvrow = numberOnly($_GET['bvrow']);
	if($bvpage <= 1) $bvpage = 1;
	if($bvrow < 1  || $bvrow > 30) $bvrow = 5;
	$bvblock=5;

	$NumTotalRec2 = $pdo->rowCount($bvsql);

	$PagingInstance2 = new Paging($NumTotalRec2, $bvpage, $bvrow, $bvblock, '', 'sbscrSchSearch', $data['no']);
	$PagingInstance2->addQueryString(makeQueryString('bvpage'));
	$PagingResult2 = $PagingInstance2->result('ajax_admin2');
	$bvsql .= $PagingResult2['LimitQuery'];

	$pg_res2 = $PagingResult2['PageLink'];
	$bres2 = $pdo->iterator($bvsql);
	$ssidx = ($bvrow*($bvpage-1))+1;

	if(!$_GET['paging']) {
?>
<tr id="list_detail_tr_<?=$data['no']?>" style="display:none;">
<?php } ?>
	<td colspan="11" style="padding:20px">
		<table class="tbl_col">
			<caption class="hidden">예약내역리스트</caption>
			<colgroup>
				<col style="width:150px;">
				<col style="width:150px;">
				<col>
				<col style="width:150px;">
				<col style="width:150px;">
				<col style="width:150px;">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">회차</th>
					<th scope="col">배송예약일</th>
					<th scope="col">주문번호</th>
					<th scope="col">결제금액</th>
					<th scope="col">상태</th>
					<th scope="col">결제/취소</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($bres2 as $bv) {
						$sspdata = $pdo->assoc("select * from $tbl[sbscr_schedule_product] where schno='$bv[no]'");
						if($sspdata['ono']) $stat_text = "주문서 생성완료";
						else $stat_text = $_order_stat[$sspdata['stat']];
				?>
				<tr>
					<td><?=$ssidx?>회차</td>
					<td><?=$bv['date']?></td>
					<td class="left"><a style="cursor:pointer;" onclick="viewOrder('<?=$sspdata['ono']?>')"><?=$sspdata['ono']?></a></td>
					<td><?=parsePrice($bv['tot_pay_prc'], true)?></td>
					<td><?=$stat_text?></td>
					<td class="center" style='white-space:nowrap;'>
						<?php if($sspdata['stat'] == "1") { ?>
							<span class="box_btn_s blue"><input type="button" value="결제" onClick="chgBooking('<?=$bv['no']?>', 1);return false;" ></span>
							<span class="box_btn_s blue"><input type="button" value="취소" onClick="chgBooking('<?=$bv['no']?>', 2);return false;" ></span>
						<?php
						} else if($sspdata['stat'] == "2" && !$sspdata['ono']) {
							echo $_order_sub_stat[$bv['stat']];
						}
						?>
					</td>
				</tr>
				<?php
					$ssidx++;
					}
				?>
			</tbody>
		</table>
		<div class="box_bottom"><?=$pg_res2?></div>
	</td>
<?php if (!$_GET['paging']) { ?>
</tr>
<?php } ?>
