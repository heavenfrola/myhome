<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 상품Q&A 내역
	' +----------------------------------------------------------------------------------------------+*/

?>
<?if($smode == 'main'){?>
<div class="box_title">
	<h3 class="title">최근 상품 Q&amp;A 내역</h3>
	<span class="box_btn_s btns icon qna"><a href="?body=member@member_view.frm&smode=qna&mno=<?=$mno?>&mid=<?=$mid?>">전체상품Q&amp;A</a></span>
</div>
<?}?>
<style type="text/css">
.qna_title {
	cursor: pointer;
}
</style>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">최근 상품 Q&amp;A 내역</caption>
	<colgroup>
		<col style="width:50px">
		<col style="width:80px">
		<col style="width:200px">
		<col>
		<col style="width:120px">
		<col style="width:120px">
	</colgroup>
	<?PHP

		$sql="select * from `$tbl[qna]` where `member_no`='$mno' $id_where order by `reg_date` desc  $limitq";
		if(!$limitq) {
			include_once $engine_dir."/_engine/include/paging.php";

			$page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec = $pdo->row("select count(*) from `$tbl[qna]` where `member_no`='$mno' $id_where");
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString(makeQueryString('page'));
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult[LimitQuery];
			$pageRes=$PagingResult[PageLink];
		} else {
			$NumTotalRec = $pdo->row("select count(*) from ($sql) a");
		}
		$res = $pdo->iterator($sql);
		$idx=$NumTotalRec-($row*($page-1));

	?>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col" colspan="2">상품명</th>
			<th scope="col">제목</th>
			<th scope="col">등록일시</th>
			<th scope="col">답변일시</th>
		</tr>
	</thead>
	<tbody>
		<?PHP

            foreach ($res as $data) {

				$data[rname]=$data[name];
				if($data[member_no]) {
					$data[rname]="<a onclick=\"viewMember('$data[member_no]','$data[member_id]')\" href=\"javascript:;\">$data[rname]($data[member_id])</a>";
				}

				$rclass=($idx%2==0) ? "tcol2" : "tcol3";

				if($data[answer_date]) {
					$data[answer_date]=date("Y/m/d H:i",$data[answer_date]);
				}
				else {
					$data[title]="<b>$data[title]</b>";
					$data[answer_date]="-";
				}

				$prd = $pdo->assoc("select no, hash, name, updir, upfile3, w3, h3 from `$tbl[product]` where no='$data[pno]'");
				$data['pname']=$prd['name'];
				$data['hash']=$prd['hash'];
				$data['rno']=$data['no'];
				$data['rreg_date']=$data['reg_date'];
				$img = prdImg(3, $prd, 50, 50);
		?>
		<tr>
			<td>
				<input type="hidden" name="pno[]" value="<?=$data[no]?>">
				<?=$idx?>
			</td>
			<td class="nobd">
				<?if($prd['no'] > 0) {?><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><img src="<?=$img[0]?>" <?=$img[1]?>></a><?}?>
			</td>
			<td class="left">
				<?if($prd['no'] > 0) {?><a href="?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><?=$data['pname']?></a><?}?>
			</td>
			<td class="left qna_title" title="" data-rno="<?=$data['rno']?>" data-windowwidth="800">
			<img src="<?=$engine_url?>/_manage/image/icon/secret_<?=($data['secret'] == "Y") ? "r" : "n";?>.gif"  style="width:12px; height:12px; vertical-align:top;">
			<?=cutStr(stripslashes($data['title']), 60)?>
			<?=$data['atc']?>
			</td>
			<td><?=date("Y/m/d H:i",$data[rreg_date])?></td>
			<td><?=$data[answer_date]?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>

<?if($smode != 'main'){?>
<div class="box_bottom"><?=$pageRes?></div>
<?}?>
<script type="text/javascript">
	// 질문/답변 미리보기
	$('.qna_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var rno = $(this).attr('data-rno');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'qna'},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var rno = $(this).attr('data-rno');
		var window_width = $(this).attr('data-windowwidth');
		wisaOpen('./pop.php?body=member@product_qna_view.frm&no='+rno, '', 'yes', window_width+'px', '500');
	});
</script>