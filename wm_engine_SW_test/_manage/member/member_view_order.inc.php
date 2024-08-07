<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 주문내역
	' +----------------------------------------------------------------------------------------------+*/

?>
<table class="tbl_col">
	<caption class="hidden">주문내역</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:140px;">
		<col>
		<col style="width:90px;">
		<col style="width:70px;">
		<col style="width:70px;">
		<col style="width:70px;">
		<col style="width:125px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">주문번호</th>
			<th scope="col">주문상품</th>
			<th scope="col">주문일시</th>
			<th scope="col">총주문액</th>
			<th scope="col">실결제</th>
			<th scope="col">결제방법</th>
			<th scope="col">상태</th>
		</tr>
	</thead>
	<tbody>
		<?
			include_once $engine_dir."/_engine/include/shop.lib.php";

			$sql="select *, (select group_concat(concat(name,'(',buy_ea,')') separator ' / ') from $tbl[order_product] where ono = a.ono) as `title` from $tbl[order] a where `stat` not in (11, 31, 32) and `member_no`='$mno' $id_where order by `date1` desc";
			// 페이징 설정
			include $engine_dir."/_engine/include/paging.php";

			$page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec = $pdo->row("select count(*) from {$tbl['order']} a where stat not in (11, 31, 32) and member_no='$mno' $id_where");
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString(makeQueryString('page'));
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult[LimitQuery];

			$pg_res=$PagingResult[PageLink];
			$res = $pdo->iterator($sql);
			$idx=$NumTotalRec-($row*($page-1));

            foreach ($res as $data) {
				$data=parseOrder($data);
				$date2=($data[date2]>0) ? date("Y/m/d h:i:s A",$data[date2]) : " -";
				$date3=($data[date3]>0) ? date("Y/m/d h:i:s A",$data[date3]) : " -";
				$date4=($data[date4]>0) ? date("Y/m/d h:i:s A",$data[date4]) : " -";
				$date5=($data[date5]>0) ? date("Y/m/d h:i:s A",$data[date5]) : " -";

				$dono=$data[ono];
				if($data['print']>0) {
					$dono="<span style=\"color:#3300cc\" onmouseover=\"showToolTip(event,'인쇄:".$data['print']."회')\" onmouseout=\"hideToolTip();\">$dono</span>";
				}

                $data['title'] = ($data['has_set'] == 'Y') ? makeOrderTitle($data['ono'], false, $prd_part) : $data['title'];
		?>
		<script language="JavaScript">helptext[<?=$idx?>]="<?=addslashes(strip_tags($data[title]))?>";</script>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><a href="javascript:;" onclick="viewOrder('<?=$data[ono]?>')"><strong><?=$dono?></strong></a></td>
			<td class="left order_title" onmouseover="showToolTip(event,'<?=$idx?>','1')" onmouseout="hideToolTip();">
				<a href="javascript:;" onclick="viewOrder('<?=$data['ono']?>')"><?=$data['title']?></a>
			</td>
			<td onmouseover="showToolTip(event,'<b>주문</b> : <?=date("Y/m/d h:i:s A",$data[date1])."<br><b>입금</b> : ".$date2."<br><b>상품준비</b> : ".$date3."<br><b>배송시작</b> : ".$date4."<br><b>배송완료</b> : ".$date5?>')" onmouseout="hideToolTip();"><?=date("m/d H:i:s",$data[date1])?></td>
			<td onmouseover="showToolTip(event,'<b>상품가격</b> : <?=number_format($data[prd_prc])?> 원<br><b>배송비</b> : <?=number_format($data[dlv_prc])?> 원<br>')" onmouseout="hideToolTip();"><?=number_format($data[total_prc])?></td>
			<td><?=number_format($data['pay_prc'])?></td>
			<td><?=$pay_type?></td>
			<td class='right'>
				<div class="list_common">
				<?=$data['stat']?>
				</div>
			</td>
		</tr>
		<?
			$idx--;
		}
		?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pg_res?></div>
