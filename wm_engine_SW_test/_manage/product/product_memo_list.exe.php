<?PHP

	printAjaxHeader();

	if(!$pno) $pno = numberOnly($_GET['pno']);
	$sql = "select * from {$tbl['order_memo']} where type=3 and ono=:pno order by no desc";
    $bind = array(':pno' => $pno);

	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=4;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['order_memo']} where type=3 and ono=:pno", $bind);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$mres = $pdo->iterator($sql, $bind);
	$idx=$NumTotalRec-($row*($page-1));

	$pg_res = preg_replace('/\?page=([0-9])[^"]+/', "#\" onclick=\"reloadMemo(null, $1); return false;", $pg_res);

?>
<?php if ($from_ajax != 'true') { ?>
<script type="text/javascript">
	function deleteMemo(no, from_ajax) {
		if(!confirm('선택한 메모를 삭제하시겠습니까?')) {
			return false;
		}
		$.post('?body=member@member_memo.exe', {"exec":"delete", "no":no}, function(result) {
			if(result != 'OK') {
				window.alert(result);
				return;
			}

			if(from_ajax == true) reloadMemo();
			else location.reload();
		});
	}
	function modifyMemo(no) {
		var txt = $('.input_memo_'+no).focus();
		var f = txt.parents('form').eq(0);

		txt[0].readOnly = false;
		txt[0].onfocus = null;
		f.find('.btn_bottom').show();

	}
	function reloadMemo(no, page) {
		if(no) {
			var txt = $('.input_memo_'+no);
			var f = txt.parents('form').eq(0);

			txt[0].readOnly = false;
			txt[0].onfocus = function() { this.blur(); };
			f.find('.btn_bottom').hide();
		} else {
			if(!page) page = '<?=$page?>';
			$.get('./index.php?body=product@product_memo_list.exe&pno=<?=$pno?>&page='+page, function(r) {
				$('#mng_memo_area').html(r);
			});
		}
	}
</script>
<?php } ?>
<p class="total"><strong class="p_color"><?=number_format($NumTotalRec)?></strong> 개의 상품 메모가 있습니다.</p>
<ul>
	<li class="list">
		<form method="post" action="./index.php" onsubmit="this.target=hid_frame;" class="frame_memo write">
			<input type="hidden" name="body" value="member@member_memo.exe">
			<input type="hidden" name="pno" value="<?=$pno?>">
			<input type="hidden" name="type" value="3">
			<input type="hidden" name="meno" value="">
			<input type="hidden" name="from_ajax" value="true">

			<textarea name="content" rows="5" class="input_memo" placeholder="메모를 입력하세요."></textarea>
			<div class="btn_bottom">
				<input type="submit" value="저장">
			</div>
		</form>
	</li>
	<?php foreach ($mres as $data) {?>
	<li class="list">
		<div class="frame_memo">
			<form method="post" action="./index.php" onsubmit="this.target=hid_frame">
				<input type="hidden" name="body" value="member@member_memo.exe">
				<input type="hidden" name="meno" value="<?=$data['no']?>">
				<input type="hidden" name="from_ajax" value="true">
				<div class="writer">
					<?=$data['admin_id']?>
					<div class="btn">
						<span class="p_cursor" onclick="modifyMemo(<?=$data['no']?>);"><img src="<?=$engine_url?>/_manage/image/crm/memo_modify.png" alt="수정"></span>
						<span class="p_cursor" onclick="deleteMemo('<?=$data['no']?>', true)"><img src="<?=$engine_url?>/_manage/image/crm/memo_delete.png" alt="삭제"></span>
					</div>
				</div>
				<textarea name="content" rows="5" class="input_memo input_memo_<?=$data['no']?>" onfocus="this.blur();" readonly><?=stripslashes($data['content'])?></textarea>
				<p class="date"><?=date("Y.m.d H:i",$data['reg_date'])?></p>
				<div class="btn_bottom" style="display:none;">
					<input type="submit" value="저장">
					<input type="button" value="취소" onClick="this.form.reset(); reloadMemo(<?=$data['no']?>)">
				</div>
			</form>
		</div>
	</li>
	<?php
		$idx--;
		}
	?>
</ul>
<?php if ($NumTotalRec != 0) { ?>
<div class="bottom"><?=$pg_res?></div>
<?php } ?>