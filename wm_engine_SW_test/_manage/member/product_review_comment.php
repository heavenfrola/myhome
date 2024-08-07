<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/shop_detail.lib.php";

	// 검색
	$_search_type = array("content" => "내용", "name" => "이름", "member_id" => "아이디", "ip" => "아이피", "ref" => "후기 글번호");
	$search_type = addslashes(trim($_GET['search_type']));
	$search_str = addslashes(trim($_GET['search_str']));
	if ($search_type && $search_str) {
		if($search_type == "content") $w .= "and a.`$search_type` like '%$search_str%'";
		else $w .= " and a.`$search_type` = '$search_str'";
	}

	$rtitle = addslashes(trim($_GET['rtitle']));
	if($rtitle) $w .= " and b.`title` like '%$rtitle%'";

	// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[review_comment]` a inner join `$tbl[review]` b on a.`ref` = b.`no` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql = "select a.*, b.`no` as `rno`, b.`title` as `rtitle`, b.`pno` from `$tbl[review_comment]` a inner join `$tbl[review]` b on a.`ref` = b.`no` where 1 $w order by a.`reg_date` desc $PagingResult[LimitQuery]";

	$pageRes=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title">상품후기 댓글</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn("prdFrm", $_COOKIE[review_detail_search_on])?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'review_detail_search_on');" <?=checked($_COOKIE['review_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품후기 관리 검색</caption>
			<colgroup>
				<col style="width:12%;">
				<col style="width:38%;">
				<col style="width:12%;">
				<col style="width:38%;">
			</colgroup>
			<tr class="search_box_omit">
				<th scope="row">후기글 상태</th>
				<td>
					<select name="rstat">
						<option value="">전체</option>
						<option value="1" <?=checked($rstat,1,1)?>>대기</option>
						<option value="2" <?=checked($rstat,2,1)?>>등록</option>
						<option value="3" <?=checked($rstat,3,1)?>>베스트</option>
					</select>
				</td>
				<th scope="row">후기글 제목</th>
				<td>
					<input type="text" name="rtitle" value="<?=inputText($rtitle)?>" class="input" size="50">
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
	<!-- 검색 폼 -->
	<!-- 검색 총합 -->
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 게시물이 검색되었습니다.
		<p class="total explain">상품후기의 댓글은 일반회원들만 입력할 수 있습니다.</p>
	</div>
	<!-- //검색 총합 -->
	<!-- 검색 테이블 -->
	<table class="tbl_col">
		<caption class="hidden">상품후기 관리 리스트</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:50px">
			<col style="width:150px">
			<col style="width:220px">
			<col>
			<col style="width:150px">
			<col style="width:100px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">상품명</th>
				<th scope="col">상품후기</th>
				<th scope="col">댓글</th>
				<th scope="col">이름</th>
				<th scope="col">등록일</th>
				<th scope="col">아이피</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					if(!$data['rev_pt']) $data['rev_pt'] = 0;
					$data['rname'] = $data['name'];
					if($data['member_no']) {
						$data['rname'] = "<a onclick=\"viewMember('$data[member_no]','$data[member_id]')\" href=\"javascript:;\">$data[rname]<br>($data[member_id])</a>";
					}

					$mile_date = ($data['milage_date'] && $data['milage']) ? "<span style=\"color:#3300CC\">".date("Y/m/d",$data[milage_date])."</span>" : "미적립";
					$rclass = ($idx % 2 == 0) ? "tcol2" : "tcol3";

					$prd = get_info($tbl['product'], "no", $data['pno']);
					$data['pname'] = $prd['name'];
					$data['hash'] = $prd['hash'];
					$data['rreg_date'] = $data['reg_date'];
					$data['rstat'] = $data['stat'];
					$rstat = $_review_stat[$data['rstat']];

					if($data['notice'] == "Y") {
						$data['rname'] = "공지";
						$mile_date = $buy_date = $rstat="";
					}
			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>"></td>
				<td><?=$idx?></td>
				<td class="left" onmouseover="showToolTip(event, `<?=htmlspecialchars($data['pname'])?>`)" onmouseout="hideToolTip();">
					<?if($data['pno']){?>
					<a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=cutStr(stripslashes($data['pname']), 15)?></a>
					<a href="./?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:top;"></a>
					<?}?>
				</td>
				<td class="left">
					<a href="javascript:;" onClick="wisaOpen('./pop.php?body=member@product_review_view.frm&no=<?=$data['rno']?>','mng_review')">
						<?=cutStr(stripslashes($data['rtitle']), 30)?>
					</a>
				</td>
				<td class="left comment_title" title="" data-rno="<?=$data['no']?>">
						<?=cutStr(stripslashes($data['content']), 75)?>
				</td>
				<td><?=$data['rname']?></td>
				<td><?=date("Y/m/d", $data['rreg_date'])?></td>
				<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data['ip']?>" target="_blank" title="IP 정보"><?=$data['ip']?></a></td>
			</tr>
			<?
					$idx--;
				}
			?>
		</tbody>
	</table>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pageRes?>
		<?if($NumTotalRec){?>
		<div class="left_area">
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="deleteRev(document.prdFrm);"></span>
		</div>
		<?}?>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

<script type="text/javascript">
	function deleteRev(f){
		if(!checkCB(f.check_pno,"삭제할 댓글을")) return;
		if (!confirm('선택하신 상품평 댓글을 삭제하시겠습니까?')) return;
		f.body.value="member@product_review_update.exe";
		f.exec.value="comment_delete";
		f.method='post';
		f.target=hid_frame;
		f.ext.value="all";
		f.submit();
	}
</script>

<script type="text/javascript">
	// 질문/답변 미리보기
	$('.comment_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var rno = $(this).attr('data-rno');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'comment'},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var rno = $(this).attr('data-rno');
		wisaOpen('./pop.php?body=member@product_review_cview.frm&no='+rno,'mng_review');
	});
</script>
<style type="text/css">
.comment_title {
	cursor: pointer;
}
</style>