<?PHP

	printAjaxHeader();

	if($_REQUEST['ono']) $ono = $_REQUEST['ono'];
	if($_REQUEST['mid']) $mid = $_REQUEST['mid'];

	if($ono) $memo_w .= " and ono='$ono'";
	else if($mid) $memo_w .= " and ono='$mid'";
	if(!$memo_w) return;

	if($admin['partner_no'] > 0) {
		$memo_w .= " and partner_no in (0, $admin[partner_no])";
	}

	$memo_type = ($ono) ? '1' : '2';
	$sql = "select * from `$tbl[order_memo]` where type=$memo_type $memo_w order by no desc";

	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=5;
	$block=10;
	$QueryString = '';
	foreach($_GET as $key => $val) {
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['order_memo']} where type='$memo_type' $memo_w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$pg_res = preg_replace('/"?page=([0-9]+)[^"]+"/', '" onclick="reloadMemo(null, $1); return false;"', $pg_res);
	$mres = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	function parseMemo($res) {
        global $pdo, $tbl;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['content'] =  stripslashes($data['content']);
		$data['importance_str'] = ($data['importance'] == 2) ? 'check' : 'normal';
		$data['reg_date'] = date('Y-m-d H:i', $data['reg_date']);

        // 첨부파일 체크
        $data['files'] = $pdo->iterator("select * from {$tbl['neko']} where neko_gr=? and neko_id=? order by no asc", array(
            'memo'.$data['type'], 'memo_'.$data['type'].'_'.$data['no']
        ));

		return $data;
	}

    // 메모 열람 및 처리 권한
    $has_memo_auth = false;
    $memo_name = '';
    if ($memo_type == '1') {
        $has_memo_auth = authCheck('order', 'C0183');
        $memo_name = '주문';
    } elseif ($memo_type == '2') {
        $has_memo_auth = authCheck('member', 'C0245');
        $memo_name = '회원';
    }

?>
<?php if($from_ajax != 'true') { ?>
<script type="text/javascript">
	function deleteMemo(no, from_ajax) {
		if(!confirm('선택한 메모를 삭제하시겠습니까?')) {
			return false;
		}
		$.post('?body=member@member_memo.exe', {"exec":"delete", "no":no}, function(result) {
            if (result == 'OK') {
                if(from_ajax == true) reloadMemo();
                else location.reload();
            } else {
                window.alert(result);
            }
		});
	}

	function modifyMemo(no) {
		var txt = $('.input_memo_'+no);
        var view = $('.memo_view_'+no);
		var f = txt.parents('form').eq(0);

        view.hide();
        txt.show();

		txt.prop('readOnly', false);
		txt.css({'overflow':'hidden', 'max-height':'none', 'height':0});
		txt.css('height', txt.prop('scrollHeight'));
		txt.unbind('focus', blur);
		f.find('.btn_bottom').show();
		txt.focus();
        $('.input_attach_'+no).removeClass('hidden');
	}

	function toggleMemo(no) {
		$.post('?body=member@member_memo.exe', {"exec":"toggle", "no":no}, function(r) {
            if (r == 'OK') {
    			reloadMemo();
            } else {
                window.alert(r);
            }
		});
	}

	function reloadMemo(no, page) {
		if(no) {
			var txt = $('.input_memo_'+no);
            var view = $('.memo_view_'+no);
			var f = txt.parents('form').eq(0);

            view.show();
            txt.hide();

			txt.prop('readOnly', false);
			txt.css({'overflow':'auto', 'max-height':'180px'});
			txt.bind('focus', blur);
			txt.val($.trim(txt.val()));

			f.find('.btn_bottom').hide();
            $('.input_attach_'+no).addClass('hidden');

			txt.css('height', 0).css('height', txt.prop('scrollHeight'));
		} else {
			var page = (page) ? page : '<?=$page?>';
			$.get('./index.php?body=member@member_memo_list.exe&ono=<?=$ono?>&mid=<?=$mid?>&page='+page, function(r) {
				$('#mng_memo_area').html(r);
			});
		}
	}

	var blur = function() {
		this.blur();
	}

	$(function() {
		$('.input_memo').each(function() {
			$(this).css('overflow', 'hidden').css('height', 0).css('height', this.scrollHeight);
			if(this.id == 'memo_write') {
				$(this).css('max-height', 'none');
			} else {
				$(this).bind('focus', blur).css('overflow', 'auto');
			}
		}).bind('keyup change', function() {
			$(this).css('height', 0).css('height', this.scrollHeight);
		});
	});
</script>
<?php } ?>
<form method="post" enctype="multipart/form-data" action="./index.php" onsubmit="this.target=hid_frame">
	<input type="hidden" name="body" value="member@member_memo.exe">
	<input type="hidden" name="ono" value="<?=$ono?>">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="mno" value="<?=$mno?>">
	<input type="hidden" name="type" value="<?=$memo_type?>">
	<input type="hidden" name="meno" value="<?=$meno?>">
    <?php if ($has_memo_auth == true || $admin['level'] == '4') {?>
	<div class="frame_memo">
		<textarea name="content" id="memo_write" class="input_memo" placeholder="메모를 입력하세요."><?=$content?></textarea>
        <input type="file" name="upfile[]" class="input" class="input_attach" multiple>
		<div class="btn_bottom">
			<input type="submit" value="저장">
		</div>
	</div>
    <?php } ?>
</form>
<ul class="memo_list"">
	<?php while ($data = parseMemo($mres)) { ?>
	<li class="frame_memo <?=$data['importance_str']?>">
		<form method="post" enctype="multipart/form-data"  action="./index.php" onsubmit="this.target=hid_frame">
			<input type="hidden" name="body" value="member@member_memo.exe">
        	<input type="hidden" name="type" value="<?=$memo_type?>">
			<input type="hidden" name="meno" value="<?=$data['no']?>">
			<input type="hidden" name="importance" value="<?=$data['importance']?>">
			<div class="writer">
				<?=$data['admin_id']?>
                <?php if ($has_memo_auth == true || $admin['admin_id'] == $data['admin_id']) {?>
				<div class="btn">
					<span class="p_cursor" onclick="toggleMemo(<?=$data['no']?>);"><img src="<?=$engine_url?>/_manage/image/crm/memo_<?=$data['importance_str']?>.png" alt="중요"></span>
					<?php if ($data['admin_id'] != 'system') { ?>
					<span class="p_cursor" onclick="modifyMemo(<?=$data['no']?>);"><img src="<?=$engine_url?>/_manage/image/crm/memo_modify.png" alt="수정"></span>
					<span class="p_cursor" onclick="deleteMemo('<?=$data['no']?>', true)"><img src="<?=$engine_url?>/_manage/image/crm/memo_delete.png" alt="삭제"></span>
					<?php } ?>
				</div>
                <?php } ?>
			</div>
            <?php if ($has_memo_auth == true || $admin['admin_id'] == $data['admin_id']) {?>
			<textarea name="content" class="input_memo input_memo_<?=$data['no']?>" readonly><?=$data['content']?></textarea>
            <input type="file" name="upfile[]" class="input input_attach_<?=$data['no']?> hidden" multiple>
            <div class="memo_view_<?=$data['no']?>">
                <?=nl2br($data['content'])?>
                <ul class="files">
                <?php foreach ($data['files'] as $file) { ?>
                    <li>
                        <?php if ($file['width'] > 0 && $file['height'] > 0) { ?>
                        <span>
                            <a href="<?=getListImgURL($file['updir'], $file['filename'])?>" target="_blank">
                                <img src="<?=getListImgURL($file['updir'], $file['filename'])?>">
                            </a>
                            <a href="#" onclick="removeMemoAttach(<?=$data['no']?>, <?=$file['no']?>)" class="delete xi-close xi-2x"></a>
                        </span>
                        <?php } else { ?>
                        <p style="padding: 3px 0">
                            <a href="<?=getListImgURL($file['updir'], $file['filename'])?>" target="_blank" class="delete xi-file-o">
                               <?=$file['ofilename']?>
                            </a>
                            <a href="#" onclick="removeMemoAttach(<?=$data['no']?>, <?=$file['no']?>)" class="xi-trash"></a>
                        </p>
                        <?php } ?>
                    </li>
                <?php } ?>
                </ul>
            </div>
            <?php } else { ?>
            <div class="explain"><?=$memo_name?>메모 열람 및 작성 권한이 없습니다.</div>
            <?php } ?>
			<p class="date"><?=$data['reg_date']?></p>
            <?php if ($has_memo_auth == true || $admin['admin_id'] == $data['admin_id']) {?>
			<div class="btn_bottom" style="display:none;">
				<input type="submit" value="저장">
				<input type="button" value="취소" onClick="this.form.reset(); reloadMemo(<?=$data['no']?>)">
			</div>
            <?php } ?>
		</form>
	</li>
	<?php
		$idx--;
		}
	?>
</ul>
<?php if ($NumTotalRec != 0) { ?>
<div class="pop_bottom"><?=$pg_res?></div>
<?php } ?>