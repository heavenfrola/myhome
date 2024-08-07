<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	if(!isTable($tbl['often_comment'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['often_comment']);
	}

	$no = numberOnly($_GET['no']);
	$notice = ($_GET['notice'] == 'Y') ? 'Y' : 'N';

	if(!$notice) {
		checkBlank($no,'필수값(1)을 입력해주세요.');
	}
	if($no) {
		$data = get_info($tbl['review'], "no", $no);
		checkBlank($data['no'], "필수값(2)을 입력해주세요.");
		$notice = $data['notice'];
		if($data['member_no'] > 0) $blacklist = $pdo->row("select blacklist from `$tbl[member]` where `no` = '$data[member_no]'");

		include_once $engine_dir."/_engine/include/shop.lib.php";
		$w = 300;
		$h = 300;
		if($data['upfile1']) {
			$img = prdImg(1, $data, $w, $h);
			$data['img1'] = $img[0];
			$data['imgstr1'] = $img[1];
		}
		if($data['upfile2']) {
			$img = prdImg(2, $data, $w, $h);
			$data['img2'] = $img[0];
			$data['imgstr2'] = $img[1];
		}

        // 일반 첨부파일이 없을 경우 본문 내에 에디터 삽입 이미지가 있는지 체크
        if (!$data['img1'] && !$data['img2']) {
            $_prefix = getListImgURL($dir['upload'].'/editor_attach', '');

            $dom = new DomDocument('1.0', 'UTF-8');
            $dom->loadHTML($data['content']);
            $imgs = $dom->getElementsByTagName('img');
            foreach ($imgs as $img) {
                $src = $img->getAttribute('src');
                if ($src && preg_match('/^'.preg_quote($_prefix, '/').'/', $src) == true) {
                    $data['img_editor'] = $src;
                    break;
                }
            }
        }
	}

	if($data['pno']) {
		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$data[pno]'");
		$prd['name'] = cutStr(strip_tags(stripslashes($prd['name'])), 60);
		if($prd['no']) $prd_list = "<option value='$prd[no]' selected>$prd[name]</option>";
	}

	if($admin['level'] == 4) {
		if($prd['partner_no'] != $admin['partner_no']) msg('열람 권한이 없습니다.', 'close');
	}

	$_search_type['name'] = "상품명";
	$_search_type['content2'] = "내용";
	$_search_type['keyword'] = "검색 키워드";
	$_search_type['code'] = "상품 코드";
	$_search_type['hash'] = "시스템 코드";
	$_search_type['seller'] = "사입처";
	$_search_type['origin_name'] = "장기명";
	$_search_type['mng_memo'] = "관리자 메모";

	$file_url = getFileDir($data['updir']);
	$upfiles = array();
	if(is_array($data)) {
		foreach($data as $key => $val) {
			if(strpos($key, 'upfile') === 0 && $val) {
				$upfiles[$key] = array(
					'name' => $val,
					'path' => $file_url.'/'.$data['updir'].'/'.$val
				);
			}
		}
	}
	$_SESSION['adm_view'] = 'rev@'.$no;

	addPrivacyViewLog(array(
		'page_id' => 'board',
		'page_type' => 'view',
		'target_id' => $data['member_id'],
		'target_cnt' => 1
	));

	$sql = "select * from `$tbl[review_comment]` where ref='$data[no]' order by no desc";

	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=5;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['review_comment']} where ref='{$data['no']}'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$mres = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$target = "hidden".$now;

	$cres = $pdo->iterator("select * from `$tbl[often_comment]` where `cate`='review' order by `no`");

    // 게시판 관리자 아이디
    $board_admins = array();
    if ($admin['level'] < 4) {
        $ares = $pdo->iterator("select no, member_id, name from {$tbl['member']} where level=1 order by member_id asc");
        foreach ($ares as $adata) {
            $board_admins[$adata['no']] = stripslashes($adata['name'].' ('.$adata['member_id'].')');
        }
    }
    $keycode = trim($_site_key_file_info[2]);

    // 에디터 파일
    $editor_file = new EditorFile();
    $editor_file->setId('product_review', $no);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/resize.js"></script>
<form name="reviewFrm" method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onSubmit="return checkPrdReview(this)" class="pop_width fixbtn">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="edit">
	<input type="hidden" name="body" value="member@product_review_update.exe">
	<input type="hidden" name="notice" value="<?=$notice?>">
	<input type="hidden" name="mode" value="single">
	<input type="hidden" name="cno"  value="<?=$cno?>">
	<input type="hidden" name="ajax" value="N">
    <input type="hidden" name="editor_code" value="<?=$editor_file->getId()?>">

	<div class="box_qna" <?if($notice=="Y"){?>style="width:800px;"<?}?>>
	<div class="question" <?if($notice=="Y"){?>style="width:100%;"<?}?>>
	<h2>고객 상품후기</h2>
	<table class="tbl_row">
		<caption class="hidden">상품평</caption>
		<colgroup>
			<col style="width:15%;">
			<col style="width:85%;">
		<colgroup>
		<tr>
			<th scope="row">상품명</th>
			<td>
				<?if($prd['no']){?>
				<a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=cutStr(stripslashes($prd['name']), 60)?></a>
				<a href="./?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:top;"></a>
				<?}else{?>
				연동된 상품이 없습니다.
				<?}?>
			</td>
		</tr>
		<?if($notice == "N"){?>
		<tr>
			<th scope="row">상품연동하기</th>
			<td>
				<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
				<input type="text" name="prdname" class="input" size="20" onkeydown="return prdsearch(1, event)">
				<span class="box_btn_s"><input type="button" value="검색" onclick="prdsearch()"></span>
				<select name="pno">
					<option>이 상품평과 연동된 상품이 없습니다</option>
					<?=$prd_list?>
				</select>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">제목</th>
			<td><input type="text" name="title" value="<?=inputText($data['title'])?>" class="input input_full"></td>
		</tr>
		<?if($data['cate']){?>
		<tr>
			<th scope="row">분류</th>
			<td><?=outPutCate("review", $data['cate']);?></td>
		</tr>
		<?}?>
		<?if($notice == "N") {?>
		<tr>
			<th scope="row">이름</th>
			<td>
				<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input" size="6">
				<?php if ($admin['level'] < 4) { if($data['member_no']){?>
				<a href="javascript:viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')">(<?=$data['member_id']?>) <?=blackIconPrint($blacklist)?></a>
				<?}else{?>
				(비회원)
				<?}}?>
			</td>
		</tr>
		<tr>
			<th scope="row">등록일</th>
			<td><?=date('Y/m/d', $data['reg_date'])?></td>
		</tr>
		<tr>
			<th scope="row">평점</th>
			<td>
				<select name="rev_pt">
					<?for($ii=1; $ii<=5; $ii++) {?>
					<option value="<?=$ii?>" <?=checked($data['rev_pt'], $ii, 1)?>><?=$ii?></option>
					<?}?>
				</select>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">내용</th>
			<td><textarea id="content" name="content" class="txta" style="width:100%; height:350px;"><?=stripslashes($data['content'])?></textarea></td>
		</tr>
		<tr>
			<th scope="row">첨부파일</th>
			<td>
				<div class="upload_box">
					<ul class="tab">
						<li><a onclick="upload_view(0,this)" class="active">첨부파일</a></li>
						<li><a onclick="upload_view(1,this)">미리보기</a></li>
						<li><a onclick="upload_view(2,this)">업로드</a></li>
					</ul>
					<div class="upload_cnt upload_cnt0">
						<ul>
							<?foreach($upfiles as $key => $val) {?>
							<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><?=$val['name']?></a> <a href="#" onclick="removeAttach(<?=$no?>, '<?=$key?>'); return false;" class="close">삭제</a></li>
							<?}?>
						</ul>
					</div>
					<div class="upload_cnt upload_cnt1">
						<ul>
							<?foreach($upfiles as $key => $val) {?>
							<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><img src="<?=$val['path']?>" alt=""></a></li>
							<?}?>
						</ul>
					</div>
					<div class="upload_cnt upload_cnt2">
						<input type="file" name="upfile1" value="">
						<input type="file" name="upfile2" value="">
					</div>
				</div>
			</td>
		</tr>
		<? if($data['ip']){?>
		<tr>
			<th scope="row">아이피</th>
			<td><?=$data['ip']?></td>
		</tr>
		<?}?>
	</table>
		</div>
		<?if($notice == "N") {?>
			<div class="answer">
			<h2>관리자 상품후기 댓글</h2>
			<table class="tbl_row">
				<caption class="hidden">상품 문의</caption>
				<colgroup>
					<col style="width:15%;">
					<col style="width:85%;">
				<colgroup>
				<tr>
					<th scope="row">작성자</th>
					<td>
						<select name="mng_no">
                            <?php foreach ($board_admins as $mno => $mid) { ?>
                            <option value="<?=$mno?>"><?=$mid?></option>
                            <?php } ?>
						</select>
                        <?php if ($admin['level'] == '4') { ?>
                        <script type="text/javascript">
                        $(function() {
                            getLoginMember();
                        });
                        </script>
                        <?php } ?>
					</td>
				</tr>
				<tr>
					<th scope="row">자주쓰는 댓글</th>
					<td>
						<select name="often_comment" onchange = "getOftenComment(this.value);">
							<option value="">선택안함</option>
							<?php foreach ($cres as $adata) {?>
								<option value="<?=$adata['no']?>"><?=stripslashes($adata['title'])?></option>
							<?}?>
						</select>
					</td>
				</tr>
				<tr id="mng_memo_area">
					<th scope="row">내용</th>
					<td colspan="3"><textarea id='answer' name="answer" class="txta" style="height:363px;"><?=stripslashes($data['answer'])?></textarea></td>
				</tr>
				<tr id="product_memo_list_in">
					<th scope="row">댓글</th>
					<td>
						<table class="tbl_inner line full">
							<caption class="hidden">후기댓글</caption>
							<colgroup>
								<col style="width:70px;">
								<col>
								<col style="width:80px;">
								<col style="width:140px;">
								<col style="width:100px;">
							</colgroup>
							<thead>
								<tr>
									<th scope="col">번호</th>
									<th scope="col">댓글</th>
									<th scope="col">이름</th>
									<th scope="col">등록일</th>
									<th scope="col">수정/삭제</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($mres as $memodata) {?>
							<tr>
								<td><?=$idx?></td>
								<td class="left"><div style="overflow:hidden; height:55px;"><?=cutStr(nl2br(stripslashes($memodata['content'])), 153)?></div></td>
								<td><?=$memodata['name']?></td>
								<td><?=date('Y-m-d H:i', $memodata['reg_date'])?></td>
								<td>
                                    <?php if ($admin['level'] < 4) { ?>
									<a href="#" onclick="selectTarget('<?=$memodata['no']?>'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_modify.png" alt="수정"></a>
									<a href="#" onclick="removeComment('<?=$memodata['no']?>'); return false;"><img src="<?=$engine_url?>/_manage/image/crm/memo_delete.png" alt="삭제"></a>
                                    <?php } ?>
								</td>
							</tr>
							<?$idx--;}?>
							</tbody>
						</table>
						<?if($NumTotalRec != 0) {?>
							<div class="box_bottom"><?=$pg_res?></div>
						<?}?>
					</td>
				</tr>
			</table>
			</div>
			<?}?>
		</div>
		<div class="fb_btn">
		<?if($notice == "N" && $data['member_no']) {?>
		<div id="milage_input">
			<table cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<th scope="row">일반 텍스트</th>
						<td><input type="text" name="milage_review" value="<?=$cfg['milage_review']?>" class="input"> 원</td>
					</tr>
					<?if($data['img1'] || $data['img2'] || $data['img_editor']) {?>
					<tr>
						<th scope="row">이미지 첨부 시 추가</th>
						<td><input type="text" name="milage_review_image" value="<?=$cfg['milage_review_image']?>" class="input"> 원</td>
					</tr>
					<?}?>
				</tbody>
			</table>
			<div>
				<span class="box_btn_s blue"><input type="button" value="적립금 지급" onclick="putMileRev();"></span>
				<span class="box_btn_s gray"><input type="button" value="닫기" id="hidePutMileRev"></span>
			</div>
		</div>
		<?if ($cfg['milage_use']  == 1) {?>
			<span class="box_btn blue"><input type="button" value="적립금 지급" id="showPutMileRev"></span>
		<?}?>
		<?}?>
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="window.opener.location.reload(); wclose();"></span>
	</div>
</form>

<script type="text/javascript">
	this.focus();
	var f = document.reviewFrm;

	function removeComment(no) {
		if (!confirm('선택한 상품후기 댓글을 삭제하시겠습니까?')) return;
		$.post('./index.php', {'body':'member@product_review_update.exe', 'exec':'comment_delete', 'cno':no, 'ajax':'Y'}, function(r) {
			window.alert(r.message);
			document.location.reload();
		});
	}

	function putMileRev(){
		if (!confirm('상품후기를 작성한 회원에게 적립금을 지급하시겠습니까?')) return;
		f.exec.value = 'milage';
		f.submit();
	}

	$('#showPutMileRev').click(function(){
		$(this).hide();
		$('#milage_input').show();
		//$('html, body').scrollTop($('#milage_input').offset().top);
		$('#milage_input input:eq(0)').focus();
	});

	$('#hidePutMileRev').click(function(){
		$('#showPutMileRev').show();
		$('#milage_input').hide();
	});

	function checkPrdReview(){
		if (f.exec.value != 'edit') return true;
		if (!checkBlank(f.title,"제목을 입력해주세요.")) return false;

		submitContents('content');
		if (!checkBlank(f.content, "내용을 입력해주세요.")) return false;
	}

	function prdsearch(type, ev) {
		if(type == 1) {
			var ev = window.event ? window.event : ev;
			if(ev.keyCode != 13) return;
		}

		if(f.prdname.value.length < 2) {
			window.alert('검색어를 2자 이상으로 입력해 주십시오');
			return false;
		}

		$.post('./?body=member@product_review_search.exe', {'search_type':f.search_type.value, 'search':f.prdname.value}, function(r) {
			if(r.length > 0) {
				$(f.pno).find('option:gt(0)').remove();
				for(var key in r) {
					$(f.pno).append("<option value='"+r[key].no+"'>"+r[key].name+"</option>");
				}
			} else {
				window.alert('검색결과가 없습니다');
			}
		});

		return false;
	}

	<?if($cfg['product_review_use_editor'] == 'Y') {?>
	$(window).ready(function() {
		seCall('content', '<?=$editor_file->getId()?>', 'product_review');
	});
	<?}?>

	function upload_view(no,obj) {
		var tabs = $('.upload_box .tab').find('li');
		tabs.each(function(idx) {
			var detail = $('.upload_cnt'+idx);
			var active = $(this).find('a');
			if(no == idx) {
				active.addClass('active');
				detail.css('display', 'block');
			} else {
				active.removeClass('active');
				detail.css('display', 'none');
			}
		})
	}

	function removeAttach(no, key) {
		if(confirm('선택한 첨부파일을 삭제하시겠습니까?') == true) {
			$.post('./index.php', {'body':'member@product_review_update.exe', 'exec': 'remove_attach', 'no':no, 'key':key}, function(r) {
				if(r.result == 'success') {
					$('.list_'+key).remove();
				} else {
					window.alert(r.message);
				}
			});
		}
	}

	var targetSelector = new layerWindow();

	function selectTarget(val) {
		setDimmed();
		targetSelector.body  = 'member@product_review_cview.frm'
		targetSelector.body += '&no='+val+'&from_ajax='+'Y';
		targetSelector.open();
	}

    function getLoginMember() {
        var timestamp = Math.floor(new Date()/1000);
        $.ajax({
            url: root_url+"/main/exec.php?exec_file=member/getMemberId.exe.php&keycode=<?=$keycode?>&timestamp="+timestamp,
            dataType: 'jsonp',
            jsonpCallback: "callback"+timestamp,
            success: function(data) {
                if (data.member_id == null) {
                    window.alert('쇼핑몰에서 회원 아이디로 로그인 후 댓글 작성이 가능합니다.');
                }
                $('select[name=mng_no').append('<option value="'+data.member_no+'">'+data.member_id+'</option>');
            }
        });
    }
</script>