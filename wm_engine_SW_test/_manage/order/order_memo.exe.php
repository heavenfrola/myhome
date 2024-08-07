<?PHP

	$exec = $_REQUEST['exec'];
	$ono = trim(addslashes($_REQUEST['ono']));

	if($exec == 'delete') {
		if($admin['level'] > 2) $w = " and `admin_id` = '$admin[admin_id]'";

		$mno = implode(',', $_REQUEST['mno']);
		$pdo->query("delete from `$tbl[order_memo]` where `no` in ($mno) $w");
		msg("", "reload", "parent");
		exit;
	}

    // 메모 열람 및 처리 권한
    $has_memo_auth = authCheck('order', 'C0183');

	function parseMemo($mm) {
		global $memoidx, $admin, $has_memo_auth;

		$mdata = $mm->current();
        $mm->next();
		if($mdata == false) return;
		$mdata['date'] = date("y/m/d H:i", $mdata['reg_date']);
		$mdata['content'] = nl2br(stripslashes(trim($mdata['content'])));
        if ($has_memo_auth == false && $admin['admin_id'] != $mdata['admin_id']) {
            $mdata['content'] = '<span class="explain">메모 열람권한이 없습니다.</span>';
        }

		$memoidx++;

		return $mdata;
	}

	printAjaxHeader();

	$mm = $pdo->iterator("select * from `$tbl[order_memo]` where `ono` = '$ono' order by `no` desc");
	if(!$mm) {
		if(!isTable($tbl['order_memo'])) {
			include_once $engine_dir."/_config/tbl_schema.php";
			$pdo->query($tbl_schema['order_memo']);
		}
	}

	$meno = numberOnly($_GET['meno']);
	if($meno) {
		$sql = $pdo->iterator("select * from `$tbl[order_memo]` where `no` = '$meno'");
		$memodata = $sql->current();
        $sql->next();
	}
	$btn_mode = ($meno) ? "메모 수정" : "메모 입력";
	if(!$memo_type) $memo_type = 1;

	if($exec == 'viewList') {?>
<div id="popupContent" class="popupContent layerPop" style="width:600px; z-index:1001;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">주문메모 - <?=$ono?></div>
	</div>
	<div id="popupContentArea" style="max-height:400px; overflow: auto;">
		<table class="tbl_col">
			<colgroup>
				<col style="width: 100px;">
				<col>
				<col style="width: 100px;">
			</colgroup>
			<thead>
				<tr>
					<th>작성자</th>
					<th>메모내용</th>
					<th>작성일시</th>
				</tr>
			</thead>
			<tbody>
                <?php if ($admin['level'] == '3' && authCheck('order', 'C0183') == false) { ?>
                <tr>
                    <td colspan="3"><p class="nodata">주문메모 열람 및 작성 권한이 없습니다.</p></td>
                </tr>
                <?php } else { ?>
				<?php while ($mdata = parseMemo($mm)) { ?>
				<tr>
					<td><?=$mdata['admin_id']?></td>
					<td class="left"><?=$mdata['content']?></td>
					<td><?=date('Y.m.d h:i', $mdata['reg_date'])?></td>
				</tr>
				<?php }} ?>
			</tbody>
		</table>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="window.memoLayer.close(); removeDimmed();"></span>
	</div>
</div>
	<?php } else { ?>
<input type="hidden" name="meno" value="<?=$meno?>">
<input type="hidden" name="type" value="<?=$memo_type?>">
<div class="frame_memo write">
	<textarea name="mng_memo" class="input_memo" placeholder="메모를 입력하세요."><?=stripslashes($memodata['content'])?></textarea>
	<div class="btn_bottom">
		<input type="button" value="<?=$btn_mode?>" onClick="updeteOrder('mng_memo_new','')">
		<?php if ($meno){ ?>
		<input type="button" value="취소" onClick="reloadMemo('<?=$ono?>','')">
		<?php } ?>
	</div>
</div>
<ul>
	<?php while ($mdata = parseMemo($mm)) { ?>
	<li>
		<div class="frame_memo">
			<div class="writer">
				<?=$mdata['admin_id']?>
				<div class="btn">
					<span class="p_cursor" onclick="reloadMemo('<?=$ono?>','<?=$mdata['no']?>');">M</span>
					<span class="p_cursor" onclick="deleteMemo('<?=$mdata['no']?>');">X</span>
				</div>
			</div>
			<div class="content"><?=nl2br(stripslashes($mdata['content']))?></div>
			<p class="date"><?=date("Y.m.d h:i",$mdata['reg_date'])?></p>
		</div>
	</li>
	<?php } ?>
</ul>
<?php } ?>