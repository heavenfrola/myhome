<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리 - 수정
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	$_tbl="mari_board";

	$mari_path=$engine_dir."/board";
	$mari_url=$root_url."/board";

	$no = numberOnly($_GET['no']);

	if($no > 0) {
		$data = $pdo->assoc("select * from `$_tbl` where `no`='$no' limit 1");
		$data = array_map('stripslashes', $data);
		$no = $data['no'];
		if(!$no) msg("존재하지 않는 글입니다", "back");
	} else {
		$_dbs = array('' => ':: 선택 ::');
		$res = $pdo->iterator("select db, title from mari_config order by title asc");
        foreach ($res as $data) {
			$_dbs[$data['db']] = stripslashes($data['title']);
		}

		$data['html'] = 1;
		$data['title'] = "";
	}
	$reg_date = explode(' ', date('Y-m-d H:i:s', $data['reg_date']));
	$reg_time = explode(':', $reg_date[1]);

	$config = $pdo->assoc("select * from `mari_config` where `db` = '$data[db]'");

	// 기간 설정
    if ($data['start_date'] == '0000-00-00 00:00:00') unset($data['start_date']);
    if ($data['end_date'] == '0000-00-00 00:00:00') unset($data['end_date']);
	if(strtotime(date($data['start_date'])) == false || strtotime(date($data['end_date'])) == false) {
		$no_date = 'Y';
	} else {
		$no_date = 'N';
		$start_date = preg_split('/(\s|:)/', $data['start_date']);
		$end_date = preg_split('/(\s|:)/', $data['end_date']);
	}

    // 에디터 파일
    $editor_file = new EditorFile();
    $editor_file->setId($data['db'], $no);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form id="wrtFrm" name="boardFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" onsubmit="return boardChk(this);" enctype="multipart/form-data">
	<input type="hidden" name="body" value="board@content.exe">
	<input type="hidden" name="exec" value="modify">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="pno" value="<?=$data['pno']?>">
    <input type="hidden" name="editor_code" value="<?=$editor_file->getId()?>">
	<div class="box_title first">
		<h2 class="title">게시물 관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시물 관리</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<?if(!$no) {?>
		<tr>
			<th scope="row">게시판</th>
			<td colspan="2">
				<?=selectArray($_dbs, 'db', false, null, null, 'getBoardConfig(this.value)')?>
			</td>
		</tr>
		<tr>
			<th scope="row">작성자명</th>
			<td colspan="2"><input type="text" name="name" value="<?=$data['name']?>" class="input"></td>
		</tr>
		<?}?>
		<tr class="tr_title">
			<th scope="row">제목</th>
			<td colspan="2"><input type="text" name="title" value="<?=$data[title]?>" class="input" style="width:500px;"></td>
		</tr>
		<?include 'content_write.cate.exe.php'?>
		<tr>
			<th scope="row">기간</th>
			<td>
				<p style="margin-bottom:10px">
					<label><input type="checkbox" name="no_date" value="Y" <?=checked($no_date, 'Y')?>> 무제한</label>
				</p>
				<p>
					<input type="text" name="start_date" class="input datepicker dates" size="8" value="<?=$start_date[0]?>">
					<select name="start_time" class="dates">
						<?for($i = 0; $i <= 23; $i++) {?>
						<option value="<?=$i?>" <?=checked($start_date[1], $i, true)?>><?=sprintf('%02d', $i)?></option>
						<?}?>
					</select> 시
					<select name="start_min" class="dates">
						<?for($i = 0; $i <= 59; $i++) {?>
						<option value="<?=$i?>" <?=checked($start_date[2], $i, true)?>><?=sprintf('%02d', $i)?></option>
						<?}?>
					</select> 분 ~

					<input type="text" name="end_date" class="input datepicker dates" size="8" value="<?=$end_date[0]?>">
					<select name="end_time" class="dates">
						<?for($i = 0; $i <= 23; $i++) {?>
						<option value="<?=$i?>" <?=checked($end_date[1], $i, true)?>><?=sprintf('%02d', $i)?></option>
						<?}?>
					</select> 시
					<select name="end_min" class="dates">
						<?for($i = 0; $i <= 59; $i++) {?>
						<option value="<?=$i?>" <?=checked($end_date[2], $i, true)?>><?=sprintf('%02d', $i)?></option>
						<?}?>
					</select> 분
				</p>
			</td>
			<td style="border-left: 1px solid #d6d6d6">
				<dl>
					<dt class="title"><strong>기간 종료 후 상태</strong></dt>
					<dd>
						<label><input type="radio" name="n_status" class="dates" value="" <?=checked($data['n_status'], '')?>> 변경 안함</label>
						<label><input type="radio" name="n_status" class="dates" value="Hidden" <?=checked($data['n_status'], 'Hidden')?>> 숨김</label>
						<label class="toCate" style="display:<?=($config['use_cate'] == 'Y') ? 'block' : 'none'?>">
							<input type="radio" name="n_status" class="dates" value="Category" <?=checked($data['n_status'], 'Category')?>> 분류 변경
							<select name="n_cate" class="dates">
								<?foreach($cate_list as $_cno => $_cname) {?>
								<option value="<?=$_cno?>" <?=checked($_cno, $data['n_cate'], true)?>><?=$_cname?></option>
								<?}?>
							</select>
						</label>
						<ul class="list_msg">
							<li>설정된 기간이 종료된 후 지정된 상태로 변경됩니다.</li>
						</ul>
					</dd>
				</dl>
			</td>
		</tr>
		<tr>
			<th scope="row">HTML</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" name="html" value="1" <?=checked($data[html],1)?>> TEXT</label>
				<label class="p_cursor"><input type="radio" name="html" value="3" <?=checked($data[html],3)?>> HTML</label>
				<label class="p_cursor"><input type="radio" name="html" value="2" <?=checked($data[html],2)?>> HTML + &lt;BR&gt;</label>
			</td>
		</tr>
		<tr>
			<th scope="row">속성</th>
			<td colspan="2">
				<label class="p_cursor"><input type="checkbox" name="notice" value="Y" <?=checked($data['notice'], 'Y')?>> 공지</label>
				<label class="p_cursor"><input type="checkbox" name="secret" value="Y" <?=checked($data['secret'], 'Y')?>> 비밀글</label>
				<label class="p_cursor">
					<input type="checkbox" name="hidden" value="Y" <?=checked($data['hidden'], 'Y')?>> 숨김
					<span class="explain">(게시판 관리자 접속 시 조회 가능)</span>
				</label>
			</td>
		</tr>
		<?if($config['use_sort'] == 'Y' && $data['no']) {?>
		<tr>
			<th scope="row">작성일시</th>
			<td colspan="2">
				<input type="text" name="reg_date[]" class="input datepicker" size="8" value="<?=$reg_date[0]?>"> 일&nbsp;
				<input type="text" name="reg_date[]" class="input right" size="2" value="<?=$reg_time[0]?>"> 시&nbsp;
				<input type="text" name="reg_date[]" class="input right" size="2" value="<?=$reg_time[1]?>"> 분&nbsp;
				<input type="text" name="reg_date[]" class="input right" size="2" value="<?=$reg_time[2]?>"> 초
			</td>
		</tr>
		<?}?>
		<tr class="tr_content">
			<th scope="row">내용</th>
			<td id="prdFrm" colspan="2">
				<ul class="tab_pr">
					<li class="on">
						<a onclick="tabover(0); return false;" class="box">PC 쇼핑몰</a>
					</li>
					<li>
						<a onclick="tabover(1); return false;" class="box">모바일 쇼핑몰</a>
						<label><input type="checkbox" name="use_m_content" value="Y" <?=checked($data['use_m_content'], 'Y')?>> 사용함</label>
					</li>
				</ul>
				<div style="padding: 10px; border: 1px solid #ccc; border-width: 0 1px 1px 1px;">
					<div class="board_content"><textarea id="content" name="content" class="txta" style="width:100%;height:300px;"><?=htmlspecialchars($data['content'])?></textarea></div>
					<div class="board_content" style="display:none;"><textarea id="m_content" name="m_content" class="txta" style="width:100%;height:300px;"><?=htmlspecialchars($data['m_content'])?></textarea></div>
				</div>
			</td>
		</tr>
		<?require 'content_write.addinfo.exe.php'?>
		<?
			for($ii=1; $ii<=4; $ii++){
				if($data["upfile".$ii]){
					$_link = getFileDir('board/'.$data['up_dir']).'/board/'.$data['up_dir'].'/'.$data['upfile'.$ii];
					${"file_link".$ii}="(<a href=\"$_link\" target=\"_blank\">".$data["ori_upfile".$ii]."</a> <input type=\"checkbox\" name=\"delfile".$ii."\" id=\"delfile".$ii."\" value=\"Y\"><label for=\"delfile".$ii."\">기존 파일 삭제</label>)<br>";
				}
		?>
		<tr>
			<th scope="row">첨부파일<?=$ii?></th>
			<td colspan="2"><?=${"file_link".$ii}?><input type="file" name="upfile<?=$ii?>" class="input" style="width:500px;"></td>
		</tr>
		<?
			}
		?>
		<tr>
			<th scope="row">관련상품추가</th>
			<td colspan="2">
				<div class="explain">
					스킨이 적용된 게시판에서만 표시됩니다.
					<span class="box_btn_s blue"><input type="button" value="추가" onclick="psearch.open();"></span>
					<div id="board_product_list_div">
						<?PHP
							$_POST['exec'] = 'getRefProduct';
							include 'content.exe.php';
						?>
					</div>
				</div>
			</td>
		</tr>
	</table>
	<div id="reg_footer" class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="history.back();"></span>
		<span class="box_btn gray"><input type="button" value="리스트" onclick="location.href='<?=$_SESSION['list_url']?>';"></span>
	</div>
	<?
		preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
		settype($agent[1], 'integer');
		if($agent[1] > 6 || $agent[1] == 0) {
	?>
	<div id="fastBtn">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="history.back();"></span>
		<span class="box_btn gray"><input type="button" value="리스트" onclick="location.href='<?=$_SESSION['list_url']?>';"></span>
	</div>
	<?}?>
</form>
<script language="JavaScript">
	var f = document.getElementById('wrtFrm');
	function boardChk(f){
		if(!checkBlank(f.title, '제목을 입력해주세요.')) return false;

		if(seCalled != false) {
			submitContents('content', '');
			submitContents('m_content', '');
		}
	}

	var psearch = new layerWindow('product@product_inc.exe&stat[]=2&stat[]=4');
	psearch.psel = function(n) {
		var f = document.getElementById('wrtFrm');

		if(n) {
			var tmp = f.pno.value.split(',');
			if(tmp.length == 30) {
				window.alert('최대 30개 까지만 등록할수 있습니다.');
				return false;
			}
			for(var key in tmp) {
				if(tmp[key] == n) {
					window.alert('이미 선택된 상품입니다.');
					return false;
				}
			}
			f.pno.value += ','+n;
			f.pno.value = f.pno.value.replace(/^,/, '');
		}

		$.post('?body=board@content.exe', {'exec':'getRefProduct', 'pno':f.pno.value}, function(r) {
			$('#board_product_list_div').html(r);
		});

		if(window.event.ctrlKey != true) {
			this.close();
		}
	}

	$(window).bind({
		'keydown': function(e) {
			if(e.ctrlKey == true) $('.popupContent').find(':button[value=선택]').val('계속선택');
		},
		'keyup': function(e) {
			if(e.ctrlKey == false) $('.popupContent').find(':button[value=계속선택]').val('선택');
		}
	});

	function refProductRemove(no) {
		var f = document.getElementById('wrtFrm');
		var tmp = f.pno.value.split(',');
		var n = '';
		for(var key in tmp) {
			if(tmp[key] == no) continue;
			n += ','+tmp[key];
		}
		f.pno.value = n.replace(/^,/, '');
		psearch.psel();
	}

	function tabover(no) {
		$('.tab_pr').find('li').each(function(idx) {
			if(idx == no) $(this).addClass('on');
			else $(this).removeClass('on');

			$('.board_content').hide();
			$('.board_content').eq(no).show();
		});
	}

	function getBoardConfig(db) {
		$('.tr_cate, .tr_addinfo').remove();

		$.post('?body=board@content.exe', {'exec':'getConfig', 'db':db}, function(json) {
			if(json.use_editor == 3) {
				$(':radio[name=html]').filter('[value=3]').prop('checked', true);
				$(':radio[name=html]').not('[value=3]').prop('disabled', true);
				setBoardEditor(db);
			} else {
				$(':radio[name=html]').filter('[value=1]').prop('checked', true);
				$(':radio[name=html]').prop('disabled', false);
				seCalled = false;
				oEditors = [];
				$('.editorFrm').remove();
				$('#content, #m_content').show();
				$('.board_content').eq(1).hide();
			}
		});

		$('select[name=n_cate]').html('');
		$.get('?body=board@content_write.cate.exe', {'db':db, 'from_ajax':true}, function(data) {
			if(typeof data == 'object' && data.count != '0') {
				$('.tr_title').after(data.html);
				$('.toCate').show();
			} else {
				$('.toCate').hide();
			}
			$('select[name=n_cate]').html($(data.html).find('select').html());
		});

		$.get('?body=board@content_write.addinfo.exe', {'db':db}, function(data) {
			$('.tr_content').after(data);
		});
	}

	function setBoardEditor(db, no) {
		if(seCalled == false) {
			$('.board_content').show();

			var editor_code = '<?=$editor_file->getId()?>';
			var editor1 = new R2Na('content', {
				'editor_gr': 'board',
				'editor_code': editor_code
			});
			var editor2 = new R2Na('m_content', {
				'editor_gr': 'board',
				'editor_code': editor_code
			});
			editor1.initNeko(editor_code, 'content', 'img');
			editor2.initNeko(editor_code, 'm_content', 'img');
			window.editorCheck = setInterval(function() {
				if(oEditors && oEditors.getById && oEditors.getById['m_content']) {
					if(oEditors.getById['m_content'].getEditingAreaHeight) {
						$('.board_content').eq(1).hide();
						clearInterval(window.editorCheck);
					}
				}
			}, 200);
		}
	}

	// FIXED 슬라이드 저장버튼
	function refineFastBtn() {
		$('#fastBtn').css('left', $('#contentArea').css('margin-left')).width($('#contentTop').innerWidth()+100);
	}
	function toggleFastBtn() {
		var doc = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement : document.body;
		var fastBtn = $('#fastBtn');

		if(fastBtn.css('opacity') == 1) fastBtn.css('opacity', '.8');

		if(doc.scrollTop > $('#reg_footer').offset().top-$(window).height()) {
			if(fastBtn.css('opacity') > 0) {
				fastBtn.animate({"opacity":"0"}, {"queue":false}).css('display','none');
			}
		} else {
			if(fastBtn.css('opacity') == 0) {
				fastBtn.animate({"opacity":".8"}, {"queue":false}).css('display','');
			}
		}
	}
	if($('#fastBtn').length > 0) {
		$(document).ready(function() {
			toggleFastBtn();
			refineFastBtn();

			$('#contentArea').change(refineFastBtn);
		});

		$(window).bind({
			"resize": refineFastBtn,
			"scroll": toggleFastBtn
		});
	}

	function checkNoDate() {
		if($(':checked[name=no_date]').length == 1) {
			$('.dates').prop('disabled', true).css('background', '#f2f2f2');
		} else {
			$('.dates').prop('disabled', false).css('background', '');
		}
	}
	$(':checkbox[name=no_date]').change(function() {
		checkNoDate();
	});

	$(function() {
		checkNoDate();

		<?if($config['use_editor'] == '3') {?>
		setBoardEditor("<?=$data['db']?>", "<?=$data['no']?>");
		$(':radio[name=html]').filter('[value=3]').prop('checked', true);
		$(':radio[name=html]').not('[value=3]').prop('disabled', true);
		<?}?>
	});
</script>