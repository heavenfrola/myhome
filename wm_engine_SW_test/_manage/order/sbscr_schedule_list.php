<?PHP

	printAjaxHeader();

	if($data['sbono']) $_GET['sbono'] = $data['sbono'];
	else $data['sbono'] = $_GET['sbono'];
	if($data['no']) $_GET['no'] = $data['no'];
	else $data['no'] = $_GET['no'];

	$sql2 = "select * , sum(total_prc) AS tot_pay_prc from $tbl[sbscr_schedule] as ss inner join $tbl[sbscr_schedule_product] as ssp on ss.no=ssp.schno where ss.sbono = '".$data['sbono']."' and `stat`<=2 group by date";

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	$bvpage = numberOnly($_GET['bvpage']);
	if(!$bvrow) $bvrow = numberOnly($_GET['bvrow']);
	if($bvpage <= 1) $bvpage = 1;
	if($bvrow < 1  || $bvrow > 30) $bvrow = 5;
	$bvblock=5;

	$NumTotalRec2 = $pdo->rowCount($sql2);

	$PagingInstance2 = new Paging($NumTotalRec2, $bvpage, $bvrow, $bvblock, '', 'sbscrSchSearch', $data['no']);
	$PagingInstance2->addQueryString(makeQueryString('bvpage'));
	$PagingResult2 = $PagingInstance2->result('ajax_admin2');
	$sql2 .= $PagingResult2['LimitQuery'];

	$pg_res2 = $PagingResult2['PageLink'];
	$bres2 = $pdo->iterator($sql2);
	$idx = $NumTotalRec2-($bvrow*($bvpage-1));

?>
<tr id="list_detail_tr_<?=$data['no']?>" style="display:none;">
	<td colspan="20" style="padding:0px">
		<table class="tbl_col">
			<caption class="hidden">예약내역리스트</caption>
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
				$ssidx = 1;

                foreach ($bres2 as $bv) {
					if($bv['stat']==1) $stat_text = $_order_stat[1];
					else if($bv['stat']==2) $stat_text = $_order_stat[2];
				?>
				<tr>
					<td><?=$ssidx?>회차</td>
					<td><?=$bv['date']?></td>
					<td class="left"><a style="cursor:pointer;" onclick="viewSbscr('<?=$bv[ono]?>')"><?=$bv['ono']?></a></td>
					<td><?=parsePrice($bv['tot_pay_prc'], true)?></td>
					<td><?=$stat_text?></td>
					<td class="center" style='white-space:nowrap;'>
						<? if($bv['stat'] == "1") { ?>
							<span class="box_btn_s blue"><input type="button" value="결제" onClick="chgBooking('<?=$bv['no']?>');return false;" ></span>
							<span class="box_btn_s blue"><input type="button" value="취소" onClick="chgBooking('<?=$bv['no']?>');return false;" ></span>
						<?
						} else if($bv['stat'] == "2") {
							echo $_order_sub_stat[$bv['stat']];
						}
						?>
					</td>
				</tr>
				<?php

					$ssidx++;
				}
				echo $pg_res2;
				?>
			</tbody>
		</table>
	</td>

</tr>