<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 1대1 상담내역
	' +----------------------------------------------------------------------------------------------+*/

?>
<?if($smode == 'main'){?>
<div class="box_title">
	<h3 class="title">최근 1대1 상담 내역</h3>
	<span class="box_btn_s btns icon counsel"><a href="?body=member@member_view.frm&smode=1to1&mno=<?=$mno?>&mid=<?=$mid?>">전체1:1상담</a></span>
</div>
<?}?>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">최근 1대1 상담 내역</caption>
	<colgroup>
		<col style="width:50px">
		<col style="width:100px">
		<col style="width:150px">
		<col>
		<col style="width:120px">
		<col style="width:120px">
	</colgroup>
	<?PHP

		$sql="select * from `$tbl[cs]` where `member_no`='$mno' $id_where order by `reg_date` desc $limitq";
		if(!$limitq) {
			include $engine_dir."/_engine/include/paging.php";

			$page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec=$pdo->row("select count(*) from `$tbl[cs]` where `member_no`='$mno' $id_where");
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString(makeQueryString('page'));
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult[LimitQuery];
			$pg_res=$PagingResult[PageLink];
		} else {
			$NumTotalRec = $pdo->row("select count(*) from ($sql) a");
		}
		$res = $pdo->iterator($sql);
		$idx=$NumTotalRec-($row*($page-1));

	?>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">분류</th>
			<th scope="col">주문번호</th>
			<th scope="col">제목</th>
			<th scope="col">등록일시</th>
			<th scope="col">답변일시</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				if(!$data[member_id]) $data[member_id]="비회원";
				if($data[reply_date]) {
					$data[reply_date]=date("Y/m/d H:i",$data[reply_date]);
				}
				else {
					$data[title]="<b>$data[title]</b>";
					$data[reply_date]="-";
				}
		?>
		<tr>
			<td><?=$idx?></td>
			<td><?=$_cust_cate[$data[cate1]][$data[cate2]]?></td>
			<td><a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><?=$data[ono]?></a></td>
			<td class="left"><a href="javascript:;" onClick="wisaOpen('pop.php?body=member@1to1_view.frm&no=<?=$data[no]?>','','yes')"><?=cutStr(stripslashes($data[title]),50)?></a></td>
			<td><?=date("Y/m/d H:i",$data[reg_date])?></td>
			<td><?=$data[reply_date]?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>

<?if($smode != 'main'){?>
<div class="pop_bottom"><?=$pg_res?></div>
<?}?>