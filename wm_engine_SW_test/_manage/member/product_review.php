<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/shop_detail.lib.php";

	function getRevBuyDate($data) {
		global $tbl, $pdo;

		if($data['ono'] && $data['buy_date']) {
			$buy_date = $data['buy_date'];
			$ono = $data['ono'];
			if($data['npay'] == 'Y') $ono = $pdo->row("select ono from {$tbl['order_product']} where checkout_ono='$ono'");
		}

		$r = ($buy_date) ? "<a href=\"javascript:;\" onClick=\"viewOrder('$ono');return false;\"><span style=\"color:#FF33CC\">".date("Y/m/d", $buy_date)."</span></a>" : "미구매";

		return $r;
	}

	$where = $where2 = '';

	$rstat = numberOnly($_GET['rstat']);
	if($rstat) {
		$where2 .= " and r.`stat`='$rstat'";
	}

	$notice = addslashes($_GET['notice']);
	if($notice) {
		$where2 .= " and r.`notice`='$notice'";
	} else if($rstat > 0) {
		$where2 .= " and r.`notice`!='Y'";
	}

	$cate = addslashes(trim($_GET['cate']));
	if($cate) {
		$where .= " and r.`cate`='$cate'";
	}

	$search_method = addslashes(trim($_GET['search_method']));
	$a_search_str = addslashes(trim($_GET['search_str']));
	if($a_search_str) {
		$where .= " and r.`$search_method` like '%$a_search_str%'";
	}

	$_search_m = array("title" => "제목", "content" => "내용", "name" => "이름", "member_id" => "아이디");

	$prd_str = trim($_GET['prd_str']);
	if(strlen($prd_str) >= 4) {
		$search_pnos = array();
		$psql = $pdo->iterator("select `no` from `$tbl[product]` where `name` like '%".addslashes($prd_str)."%'");
        foreach ($psql as $pdata) {
			$search_pnos[] = $pdata['no'];
		}
		$pnos_str = implode(',', $search_pnos);
		if($pnos_str) $where .= " and `pno` in ($pnos_str)";
		else $where .= " and `pno` = 0";

	}

	// 날짜검색
	if($_GET['all_date']) $all_date = $_GET['all_date'];
	if($_GET['start_date']) $start_date = $_GET['start_date'];
	if($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-3 months'));
		$finish_date = date('Y-m-d', $now);
	}
	if($all_date != 'Y') {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$where .= " and r.reg_date between '$_start_date' and '$_finish_date'";
	}

	$rprd = trim($_GET['rprd']);
	if($rprd == 2) $where = " and r.`pno`=''";
	elseif($rprd == 1) $where = " and r.`pno`<>''";

	if($admin['level'] == 4) {
		$join = " inner join $tbl[product] p on r.pno=p.no";
		$where .= " and p.partner_no='$admin[partner_no]'";
	} else {
		$join = " left join $tbl[member] m on r.member_no=m.no";
		$fld = ", m.blacklist";
	}
	$sql = "select r.* $fld from `$tbl[review]` r $join where 1 $where $where2 order by r.`reg_date` desc";

	if($body == 'member@product_review_excel.exe') return;

// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 10;

	$list_tab_qry = makeQueryString(true, 'notice', 'rstat', 'page');
	$xls_query = makeQueryString('body', 'page');

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[review]` r $join where 1 $where $where2");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$qs_without_row = makeQueryString(true, 'row');

	// 상태별 통계
	$_tabcnt = array();
	$_tmpres = $pdo->iterator("select r.notice, r.stat, count(distinct r.no) as cnt from $tbl[review] r $join where 1 $where group by r.notice, r.stat");
    foreach ($_tmpres as $_tmp) {
		if($_tmp['notice'] == 'Y') $_tmp['stat'] = 0;
		$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
		$_tabcnt['total'] += $_tmp['cnt'];
	}
	if($notice == 'Y') $rstat = 0;
	${'list_tab_active'.$rstat} = 'class="active"';

?>
<form name="searchFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="notice" value="<?=$notice?>">
	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title">상품후기 관리</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_m, 'search_method', 2, null, $search_method)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?=searchBoxBtn("searchFrm", $_COOKIE['review_detail_search_on'])?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'review_detail_search_on');" <?=checked($_COOKIE['review_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상품Q&A 관리 검색</caption>
			<colgroup>
				<col style="width:12%;">
				<col style="width:38%;">
				<col style="width:12%;">
				<col style="width:38%;">
			</colgroup>
			<tr class="search_box_omit">
				<th scope="row">기간</th>
				<td colspan="3">
					<label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
					<?PHP
						$date_type = array(
							'오늘' => '-0 days',
							'1주일' => '-1 weeks',
							'15일' => '-15 days',
							'1개월' => '-1 months',
							'3개월' => '-3 months',
							'6개월' => '-6 months',
							'1년' => '-1 years',
							'2년' => '-2 years',
							'3년' => '-3 years'
						);
						foreach($date_type as $key => $val) {
							$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
							$_sdate=$_fdate = null;
							if($val) {
								$_sdate=date("Y-m-d", strtotime($val));
								$_fdate=date("Y-m-d", $now);
							}
							?> <span class="box_btn_d <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span><?
						}
					?>
					<script type="text/javascript">
						searchDate(document.searchFrm);
					</script>
				</td>
			</tr>
			<tr class="search_box_omit">
				<th scope="row">상태</th>
				<td>
					<?=selectArray($_review_stat, 'rstat', false, '전체', $rstat)?>
				</td>
				<th scope="row">분류</th>
				<td>
					<?
						$_cate = outPutCate("review", $cate);
						echo $_cate ? $_cate : "사용안함";
					?>
				</td>
			</tr>
			<tr class="search_box_omit">
				<th scope="row">관련상품 유무</th>
				<td>
					<select name="rprd">
						<option value="">전체</option>
						<option value="1" <?=checked($rprd, 1, 1)?>>있음</option>
						<option value="2" <?=checked($rprd, 2, 1)?>>없음</option>
					</select>
				</td>
				<th>관련상품명</th>
				<td>
					<input type="text" name="prd_str" value="<?=inputText($prd_str)?>" class="input" size="50" placeholder="한글 2자(영문 4자) 이상 입력해주시기 바랍니다.">
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
	<!-- //검색 폼 -->
	<!-- 검색 총합 -->
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&notice=Y" <?=$list_tab_active0?>>공지<span><?=number_format($_tabcnt[0])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=2" <?=$list_tab_active2?>><?=$_review_stat[2]?><span><?=number_format($_tabcnt[2])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=1" <?=$list_tab_active1?>><?=$_review_stat[1]?><span><?=number_format($_tabcnt[1])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=3" <?=$list_tab_active3?>><?=$_review_stat[3]?><span><?=number_format($_tabcnt[3])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=4" <?=$list_tab_active4?>><?=$_review_stat[4]?><span><?=number_format($_tabcnt[4])?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=member@product_review_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
	<!-- //검색 총합 -->
	<!-- 검색 테이블 -->
	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="20" <?=checked($row,20,1)?>>20</option>
					<option value="30" <?=checked($row,30,1)?>>30</option>
					<option value="50" <?=checked($row,50,1)?>>50</option>
					<option value="100" <?=checked($row,100,1)?>>100</option>
					<option value="500" <?=checked($row,500,1)?>>500</option>
				</select>
			</dd>
		</dl>
	</div>
</form>

<form name="prdFrm" method="get" action="./">
<input type="hidden" name="body" value="<?=$body?>">
<input type="hidden" name="exec" value="">
<input type="hidden" name="ext" value="">
	<!-- //정렬 -->
	<table class="tbl_col">
		<caption class="hidden">상품후기 관리 리스트</caption>
		<colgroup>
			<col style="width:45px">
			<col style="width:45px">
			<col style="width:45px">
			<col style="width:60px">
			<col style="width:150px">
			<col>
			<col style="width:30px">
			<col style="width:40px">
			<col style="width:120px">
			<col style="width:100px">
			<col style="width:60px">
			<col style="width:60px">
			<col style="width:90px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">상태</th>
				<th scope="col">분류</th>
				<th scope="col">상품명</th>
				<th scope="col">제목</th>
				<th scope="col">점수</th>
				<th scope="col">추천<br>비추천</th>
				<th scope="col">이름</th>
				<th scope="col">등록일시</th>
				<th scope="col">구매일</th>
				<th scope="col">적립일</th>
				<th scope="col">댓글</th>
			</tr>
		</thead>
		<tbody>
			<?php

                foreach ($res as $data) {
                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

					if(!$data['rev_pt']) $data['rev_pt'] = 0;
					$data['rname'] = $data['name']." ".blackIconPrint($data['blacklist']);
                    if($data['member_no'] && empty($admin['partner_no']) == true) {
						$data['rname'] = "<a onclick=\"viewMember('$data[member_no]','$data[member_id]')\" href=\"javascript:;\">$data[rname]<br>($data[member_id_v])</a>";
					}

					$buy_date = getRevBuyDate($data);

					$mile_date = ($data['milage_date'] && $data['milage']) ? "<span style=\"color:#3300CC\">".date("Y/m/d", $data['milage_date'])."</span>" : "미적립";
					$rclass = ($idx%2==0) ? "tcol2" : "tcol3";

					$prd = get_info($tbl['product'], "no", $data['pno']);
					$data['pname'] = $prd['name'];
					$data['hash']=$prd['hash'];
					$data['rreg_date'] = $data['reg_date'];
					$data['rno'] = $data['no'];
					$data['rstat'] = $data['stat'];
					$rstat = $_review_stat[$data['rstat']];

					if($data['notice'] == "Y") {
						$data['rname'] = "공지";
						$mile_date = $buy_date = $rstat = "";
					}

					$toolTitle = preg_replace("/\r|\n|\"/", "", nl2br($data['pname']));
					$toolContent = "<strong>- ".preg_replace("/\r|\n|\"/", "", nl2br($data['title']))."</strong><br>".preg_replace("/\r|\n|\"/", "", nl2br($data['content']));

					if($data['total_comment']>100) {
						$data['total_comment'] = "99+";
					}

					$window_width = ($data['notice'] == 'Y') ? 800 : 1600;

					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => 'board',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}
			?>
			<tr>
				<td>
					<input type="hidden" name="pno[]" value="<?=$data['pno']?>">
					<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['rno']?>">
				</td>
				<td><?=$idx?></td>
				<td><?=$rstat?></td>
				<td>
					<?if(strpos($data['external_id'], 'talkpay') === 0) {?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_talkpay.png" class="btt" tooltip="카카오 페이구매" style="width:12px">
					<?}?>
                    <?=$data['cate']?>
                </td>
				<td class="left" onmouseover="showToolTip(event, `<?=htmlspecialchars($data['pname'])?>`)" onmouseout="hideToolTip();">
					<?if($prd['no']){?>
					<a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=cutStr(stripslashes($data['pname']), 15)?></a>
					<a href="./?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:top;"></a>
					<?}?>
				</td>
				<td class="left review_title" title="" data-rno="<?=$data['rno']?>" data-windowwidth="<?=$window_width?>">
					<?if($data['npay'] == 'Y') {?>
					<img src="<?=$engine_url?>/_manage/image/icon/ic_conv_naver_cbox.gif" style="vertical-align: top;">
					<?}?>
					<?=cutStr(stripslashes($data['title']), 100)?>
					<?if($data['upfile1'] || $data['upfile2']){?><img src="<?=$engine_url?>/_manage/image/icon/atc.gif" alt="첨부파일" style="vertical-align:top;"><?}?>
				</td>
				<td><?=$data['rev_pt']?></td>
				<td>
					<?=number_format($data['recommend_y'])?> / <?=number_format($data['recommend_n'])?>
				</td>
				<td><?=$data['rname']?></td>
				<td ><?=date("Y/m/d H:i", $data['rreg_date'])?></td>
				<td><?=$buy_date?></td>
				<td><?=$mile_date?></td>
				<td>
					<?php if($data['notice'] != 'Y' && empty($data['external_id']) == true) { ?>
						<a class="left review_title" title="" data-rno="<?=$data['rno']?>" data-windowwidth="<?=$window_width?>"> <span class="box_btn_s"><input type="button" value="작성"></span></a>
                        <?php if ($admin['level'] > 3) { ?>
                        <?=$data['total_comment']?>
                        <?php } else { ?>
						<a href="#" onclick="goComment('<?=$data['no']?>');return false;" style="display:inline-block; width:26px; height:26px; border-radius:50%; background-color:#4a4e5a; color:#fff; text-align:center; line-height:26px;"><?=$data['total_comment']?></a>
                        <?php } ?>
					<?} else {?>
						-
					<?}?>
				</td>
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
		<?if($admin['level'] < 4) {?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="공지사항 등록" onclick="wisaOpen('./pop.php?body=member@product_review_view.frm&notice=Y');return false;"></span>
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="deleteRev(document.prdFrm, '<?=$cfg['use_trash_rev']?>');"></span>
		</div>
		<?}?>
		<?if(!$admin_main_include) {?>
			<p class="left" style="padding-top:20px;">
				<span class="desc1">* 상품이 삭제되었을 경우 미구매로 나타날수 있습니다</span>
				<a href="javascript:layTgl2('milage_info')">(적립금 지급 안내)</a>
			</p>
		<?}?>
		<div id="milage_info" style="display:none">
			<ul class="list_msg left">
				<li>적립금 지급을 클릭하시면 <a href="javascript:goM('config@milage')"><u>적립금 설정</u></a>에서 설정된 적립금 <span><?=number_format($cfg['milage_review'])?>원</span>을 적립합니다</li>
				<li>아래의 경우 적립금 지급을 하지 않습니다.<br>
				<span class="p_color2">
				1. 비회원의 상품평<br>
				2. 설정된 적립금이 0원일 경우<br>
				3. 이미 적립금이 지급된 상품평<br>
				4. 탈퇴한 회원의 상품평
				</span>
			</ul>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

<?if($admin['level'] < 4) {?>
<!-- 하단 탭 메뉴 -->
<div id="controlTab">
	<ul class="tabs">
		<li id="ctab_1" onclick="tabSH(1)" class="selected">일괄상태변경</li>
		<li id="ctab_2" onclick="tabSH(2)">적립금지급</li>
	</ul>
	<div class="context">
		<!-- 적립금 관리 -->
		<div id="edt_layer_1">
			<div class="box_bottom left">
				<div class="list_btn">
					<p class="title">선택한 상품후기의 상태를 일괄변경합니다.</p>
					<ul>
						<li>
							<span class="box_btn_s"><input type="button" value="<?=$_review_stat[1]?> 상태로 변경" onclick="updateRev(document.prdFrm,1);" style="width: 130px;"></span>
							상품후기게시판에 노출되지 않습니다.
						</li>
						<li>
							<span class="box_btn_s blue"><input type="button" value="<?=$_review_stat[2]?> 상태로 변경" onclick="updateRev(document.prdFrm,2);" style="width: 130px;"></span>
							상품후기게시판에 정상적으로 노출됩니다.
						</li>
						<li>
							<span class="box_btn_s blue"><input type="button" value="<?=$_review_stat[3]?> 상태로 변경" onclick="updateRev(document.prdFrm,3);" style="width: 130px;"></span>
							<?=$_review_stat[3]?> 상품후기로 관리합니다.
						</li>
						<li>
							<span class="box_btn_s blue"><input type="button" value="<?=$_review_stat[4]?> 상태로 변경" onclick="updateRev(document.prdFrm,4);" style="width: 130px;"></span>
							<?=$_review_stat[4]?> 상품후기로 관리합니다.
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div id="edt_layer_2" style="display:none">
			<div class="box_middle2 left">
				<ul class="list_msg">
					<li>선택한 상품후기의 작성자에게 일괄적으로 적립금을 지급합니다.</li>
					<li>현재 상품후기 등록시 <span class="desc3"><?=number_format($cfg['milage_review'],$cfg['currency_decimal'])?><?=$cfg['currency_type']?></span>의 적립금이 지급됩니다. <a href="?body=config@milage" target="_blank">설정변경</a></li>
				</ul>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><a href="javascript:" onclick="putMileRev(document.prdFrm);">확인</a></span>
			</div>
		</div>
	</div>
</div>
<!-- //하단 탭 메뉴 -->

<script type="text/javascript">
function putMileRev(f){
	if(!checkCB(f.check_pno,"적립금을 지급할 상품후기를 선택해주세요.")) return;
	if (!confirm('선택하신 상품후기를 작성한 회원에게 적립금을 지급하시겠습니까?')) return;
	f.body.value="member@product_review_update.exe";
	f.exec.value="milage";
	f.method='post';
	f.target=hid_frame;
	f.submit();
}


function updateRev(f,s){
	if(!checkCB(f.check_pno,'변경할 상품평을 선택해주세요.')) return;
<?if($cfg['milage_review_auto'] == "Y"){?>
	if(s == 2){
		if (!confirm('선택하신 상품평의 상태를 변경하시겠습니까?\n\n적립금은 자동으로 지급됩니다')) return;
	}else{
		if (!confirm('선택하신 상품평의 상태를 변경하시겠습니까?')) return;
	}
<?}else{?>
	if (!confirm('선택하신 상품평의 상태를 변경하시겠습니까?')) return;
<?}?>
	f.body.value='member@product_review_update.exe';
	f.exec.value='update';
	f.method='post';
	f.target=hid_frame;
	f.ext.value=s;
	f.submit();
}

function deleteRev(f, is_trash){
	if(!checkCB(f.check_pno,'삭제할 상품평을 선택해주세요.')) return;
	var msg = (is_trash == 'Y') ?
		'선택한 상품후기를 휴지통으로 이동시키겠습니까?\n휴지통에 이동된 상품후기글은 설정한 보관 기관이 경과되면 영구 삭제됩니다.' :
		'선택하신 상품후기를 삭제하시겠습니까?';
	if(!confirm(msg)) return;
	f.body.value = 'member@product_review_update.exe';
	f.exec.value = 'delete';
	f.method = 'post';
	f.target = hid_frame;
	f.ext.value = 'all';
	f.submit();
}
</script>
<?}?>


<script type="text/javascript">
function goComment(no) {
	location.href='./?body=member@product_review_comment&search_type=ref&search_str='+no;
}
</script>

<script type="text/javascript">
	// 질문/답변 미리보기
	$('.review_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var rno = $(this).attr('data-rno');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'review'},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var rno = $(this).attr('data-rno');
		var window_width = $(this).attr('data-windowwidth');
		wisaOpen('./pop.php?body=member@product_review_view.frm&no='+rno,'mng_review','yes', window_width+'px', '500');
	});
</script>
<style type="text/css">
.review_title {
	cursor: pointer;
}
</style>