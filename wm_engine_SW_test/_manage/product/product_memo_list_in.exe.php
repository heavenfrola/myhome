<?PHP

	if(headers_sent() == false) {
		header('Content-type:application/json; charset=utf-8;');
		ob_start();

		$is_json = true;
	}

	include_once $engine_dir."/_engine/include/paging.php";

	if(!$pno) $pno = addslashes(trim($_POST['pno']));
	$page = numberOnly($_POST['page']);
	if($page <= 1) $page = 1;
	if(!$memo_type) $memo_type = numberOnly($_POST['memo_type']);
	$row = 5;
	$block = 10;
	$QueryString = '';
	foreach($_POST as $key => $val) {
		$QueryString .= "&$key=".urlencode($val);
	}

	$sql = "select * from `$tbl[order_memo]` where type='$memo_type' and ono='$pno' order by no desc";

	$NumTotalRec = $pdo->row("select count(*) from $tbl[order_memo] where type='$memo_type' and ono='$pno'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$mres = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$pg_res = preg_replace('/\?page=([0-9]+)[^"]*/', "#\" onclick=\"reloadMemo_in($1); return false;", $pg_res);

	$mod_no = numberOnly($_POST['mod_no']);
	if($mod_no > 0) {
		$mod_data = $pdo->assoc("select content, importance, admin_id from $tbl[order_memo] where type='$memo_type' and no='$mod_no'");
		$mod_content = stripslashes($mod_data['content']);
		$mod_importance = ($mod_data['importance'] == 2) ? 'checked' : '';

        if ($admin['level'] > 2 && ($mod_data['admin_id'] != $admin['admin_id'])) {
            header('Content-type:application/json;');
            exit(json_encode(array(
                'error' => '수정권한이 없습니다.'
            )));
        }
	}

	$toggle_list_memo = ($_COOKIE['toggle_list_memo'] != 'Y') ? 'none' : 'block';
	if($memo_type != 3) $toggle_list_memo = 'block';

	function parseCommonMemo($res) {
        global $tbl, $pdo, $admin;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['imp'] = ($data['importance'] == 2) ? 'check' : 'normal';
        $data['permission'] = ($admin['level'] < 3 || $data['admin_no'] == $admin['no']) ? true : false;
        if ($admin['level'] == 4 && $data['admin_no'] != $admin['no']) {
            $data['content'] = '<span class="explain">주문메모 열람 및 작성 권한이 없습니다.</span>';
        }

        // 첨부파일 체크
        $data['files'] = $pdo->iterator("select no, updir, filename, ofilename from {$tbl['neko']} where neko_gr=? and neko_id=? order by no asc", array(
            'memo'.$data['type'], 'memo_'.$data['type'].'_'.$data['no']
        ));

		return $data;
	}

?>
<style type="text/css">
#dimmed img {
	width: 100px;
	height: 100px;
	position: absolute;
	left: 50%;
	top: 50%;
	margin: -50px 0 0 -50px;
}
</style>
<div id="list_memo" style="display:<?=$toggle_list_memo?>">
	<div class="box_middle2">
		<div class="write_memo">
			<p class="important"><label><input type="checkbox" id="memo_importance" value="2" <?=$mod_importance?>> 중요 <strong>!</strong></label></p>
			<input type="hidden" id="memo_no" value="<?=$mod_no?>">
			<textarea id="memo_content" class="txta"><?=$mod_content?></textarea>
			<div class="btn">
				<?php if ($mod_no > 0) { ?>
				<span class="box_btn blue sm"><input type="button" value="확인" onclick="saveMemo_in();"></span>
				<span class="box_btn sm"><input type="button" value="취소" onclick="reloadMemo_in();"></span>
				<?php } else { ?>
				<span class="box_btn"><input type="button" value="확인" onclick="saveMemo_in();"></span>
				<?php } ?>
			</div>
		</div>
        <div class="left">
            <input type="file" id="memo_upfile1" class="input input_full" multiple>
        </div>
	</div>
	<table class="tbl_col" style="border-top:0;">
		<caption class="hidden">상품메모</caption>
		<colgroup>
			<col style="width:50px;">
			<col>
			<col style="width:140px;">
			<col style="width:140px;">
			<col style="width:100px;">
		</colgroup>
		<thead style="display:none;">
			<tr>
				<th scope="col">순서</th>
				<th scope="col">내용</th>
				<th scope="col">작성자</th>
				<th scope="col">작성일</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
		<?php while ($memodata = parseCommonMemo($mres)) { ?>
		<tr>
			<td><?=$idx?></td>
			<td class="left">
                <?=nl2br(stripslashes($memodata['content']))?>
                <ul class="list_atc">
                    <?php foreach($memodata['files'] as $file) { ?>
                    <li class="xi-file-o">
                        <a href="<?=getListImgURL($file['updir'], $file['filename'])?>" target="_blank"><?=$file['ofilename']?></a>
                        <a href="#" onclick="removeMemoAttach(<?=$memodata['no']?>, <?=$file['no']?>)" class="xi-trash"></a>
                    </li>
                    <?php } ?>
                </ul>
            </td>
			<td><?=$memodata['admin_id']?></td>
			<td><?=date('Y-m-d H:i', $memodata['reg_date'])?></td>
			<td>
				<a href="#" onclick="toggleMemo_in('<?=$memodata['no']?>'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_<?=$memodata['imp']?>.png" alt="중요"></a>
				<a href="#" onclick="reloadMemo_in('<?=$page?>', '<?=$memodata['no']?>'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_modify.png" alt="수정"></a>
                <?php if ($memodata['permission'] == true) { ?>
				<a href="#" onclick="removeMemo_in('<?=$memodata['no']?>'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_delete.png" alt="삭제"></a>
                <?php } else { ?>
				<a href="#" onclick="window.alert('삭제권한이 없습니다.'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_delete.png" alt="삭제"></a>
                <?php } ?>
			</td>
		</tr>
		<?php $idx--;} ?>
		</tbody>
	</table>
	<?php if ($NumTotalRec != 0) { ?>
	<div class="box_bottom"><?=$pg_res?></div>
	<?php } ?>
	<script type="text/javascript">
	var memo_json_data = <?=json_encode(array('pno'=>$pno, 'page'=>$page, 'memo_type'=>$memo_type))?>
	</script>
</div>
<?php
	if($is_json == true) {
		$content = ob_get_clean();
		exit(json_encode(array('content'=>$content, 'rows'=>$NumTotalRec)));
	}
?>
<script type="text/javascript">
	// 등록폼 삽입 상품메모
	function reloadMemo_in(page, mod_no) {
		if(!mod_no) mod_no = 0;
		if(!page) page = memo_json_data.page;
		memo_json_data.mod_no = mod_no;
		memo_json_data.page = page;

		$('#product_memo_list_in').append("<div id='dimmed'></div>");
		$('#dimmed').append('<img src="<?=$engine_url?>/_manage/image/common//ajax-loader.gif">');

		$.post('./?body=product@product_memo_list_in.exe', memo_json_data, function(r) {
            if (r.error) {
                $('#dimmed').remove();
                window.alert(r.error);
                return;
            }
			$('#memo_cnt').html(r.rows);
			$('#product_memo_list_in').html(r.content);

			if(typeof reloadMemo == 'function') {
				reloadMemo();
			}
		});
	}

	function removeMemo_in(no) {
		if(!confirm('선택한 메모를 삭제하시겠습니까?')) {
			return false;
		}
		memo_json_data.exec = 'delete';
		memo_json_data.no = no;
		$.post('?body=member@member_memo.exe', memo_json_data, function(result) {
            if (result == 'OK') {
    			reloadMemo_in(memo_json_data.page);
            } else {
                window.alert(result);
            }
		});
	}

	function saveMemo_in() {
		if(!$.trim($('#memo_content').val())) {
			window.alert('메모 내용을 입력해주세요.');
			return false;
		}

        var fd = new FormData();
        fd.append('pno', '<?=$pno?>');
        fd.append('page', '<?=$page?>');
        fd.append('memo_type', '<?=$memo_type?>');
        fd.append('meno', $('#memo_no').val());
        fd.append('content', $.trim($('#memo_content').val()));
        fd.append('importance', ($('#memo_importance').prop('checked') == true) ? 2 : 1);

        // 첨부파일
        var input = document.querySelector('#memo_upfile1');
        for(var i = 0; i < input.files.length; i++) {
            fd.append("upfile"+i, input.files[i]);
        }

        $.ajax({
            'url': './?body=member@member_memo.exe',
            'type':'post',
            'contentType': false,
            'processData': false,
            'async': false,
            'data': fd,
            'success': function(r) {
                reloadMemo_in(memo_json_data.page);
            },
        });
	}

	function toggleMemo_in(no) {
		memo_json_data.exec = 'toggle';
		memo_json_data.no = no;
		$.post('?body=member@member_memo.exe', memo_json_data, function(result) {
            if (result == 'OK') {
    			reloadMemo_in(memo_json_data.page);
            } else {
                window.alert(result);
            }
		});
	}
</script>