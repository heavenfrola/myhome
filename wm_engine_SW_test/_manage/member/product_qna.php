<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품Q&A
	' +----------------------------------------------------------------------------------------------+*/

	if(!fieldExist($tbl['qna'], 'answer_id')) {
		addField($tbl['qna'], 'answer_id', "varchar(50) not null comment '답변자 아이디' after answer_ok");
	}

	if($cfg['use_npay_qna'] == 'Y' && isset($_GET['search_key']) == false) {
		include $engine_dir.'/_engine/shop/qna_get_checkout.exe.php';
	}

	if(isset($_GET['search_key']) == false && getSmartStoreState() == true){
		$is_qna_page = true;
		include $engine_dir.'/_engine/shop/qna_get_smartstore.exe.php';
	}

	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$rstat = numberOnly($_GET['rstat']);

	$where1= $where2 = '';

	$pdo->query("update $tbl[qna] set answer_ok='Y' where (answer_ok='N' or isnull(answer_ok)) and notice='N' and answer_date>0");
	if($rstat == 2) $where1.=" and q.answer_ok='Y' and notice='N'";
	else if($rstat == 1) $where1.=" and q.answer_ok='N' and notice='N'";

	$notice = ($_GET['notice'] == 'Y') ? 'Y' : 'N';
	if($notice == 'Y') {
		$where1 .= " and q.notice='$notice'";
		$rstat = 3;
	}

	$cate = addslashes(trim($_GET['cate']));
	if($cate) {
		$where2 .= " and q.`cate`='$cate'";
	}

	$npay = $_GET['npay'];
	if($npay == 'Y') {
		$where2 .= " and q.checkout_no > 0";
	}
	$n_smart = $_GET['n_smart'];
	if($n_smart == 'Y') {
		$where2 .= " and q.smartstore_no > 0";
	}
	$talkstore = $_GET['talkstore'];
	if($talkstore == 'Y') {
		$where2 .= " and q.talkstore_qnaId!=''";
	}
	$talkpay = $_GET['talkpay'];
	if($talkpay == 'Y') {
		$where2 .= " and q.external_id like 'talkpay%'";
	}

	if(isset($_GET['sstore']) == true) {
		$where2 .= " and q.smartstore_no > 0";
	}

	$_search_key = array("title" => "제목","content" => "질문","answer" => "답변", "answer_id" => "답변자", "name" => "이름", "member_id" => "아이디", "mng_memo" => "관리자메모");

	$search_key = addslashes(trim($_GET['search_key']));
	$a_search_str = addslashes(trim($_GET['search_str']));
	if($a_search_str) {
		$where2 .= " and q.`$search_key` like '%$a_search_str%'";
	}

	$prd_str = trim($_GET['prd_str']);
	if(strlen($prd_str) >= 4) {
		$search_pnos = array();
		$psql = $pdo->iterator("select `no` from `$tbl[product]` where `name` like '%".addslashes($prd_str)."%'");
        foreach ($psql as $pdata) {
			$search_pnos[] = $pdata['no'];
		}
		$pnos_str = implode(',', $search_pnos);
		if($pnos_str) $where2 .= " and `pno` in ($pnos_str)";
		else $where2 .= " and `pno` = 0";

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
		$where2 .= " and q.reg_date between '$_start_date' and '$_finish_date'";
	}

	$rprd = numberOnly($_GET['rprd']);
	if($rprd==2) $where2 .= " and q.pno=0";
	elseif($rprd==1) $where2 .= " and q.pno>0";

	if($admin['level'] == 4) {
		$join = " inner join $tbl[product] p on q.pno=p.no";
		$where2 .= " and p.partner_no='$admin[partner_no]'";
	} else {
		$join = " left join $tbl[member] m on q.member_no=m.no";
		$fld = ", m.blacklist";
	}

	$sql="select q.* $fld from `$tbl[qna]` q $join where 1 $where1 $where2 order by q.`reg_date` desc";

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[qna]` q $join where 1 $where1 $where2");
	if($body == 'member@product_qna_excel.exe') return;

	include_once $engine_dir."/_engine/include/paging.php";

	$list_tab_qry = makeQueryString(true, 'page', 'notice', 'rstat');
	$qs_without_row = makeQueryString(true, 'row');
	$xls_query = makeQueryString('body', 'page');

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=10;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	// 상태별 통계
	$_tabcnt = array();
	$_tmpres = $pdo->iterator("select notice, answer_ok, count(distinct q.no) as cnt from $tbl[qna] q $join where 1 $where2 group by notice, answer_ok");
    foreach ($_tmpres as $_tmp) {
		$_rstat = ($_tmp['answer_ok'] == 'Y') ? '2' : '1';
		if($_tmp['notice'] == 'Y') $_rstat = 3;
		$_tabcnt[$_rstat] = $_tmp['cnt'];
		$_tabcnt['total'] += $_tmp['cnt'];
	}
	${'list_tab_active'.$rstat} = 'class="active"';

?>
<script language="JavaScript">
	helptext=new Array();
</script>

<form name="searchFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="notice" value="<?=$notice?>">
	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title">상품Q&A 관리</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_key,"search_key",2,"",$search_key)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn("searchFrm", $_COOKIE['qna_detail_search_on'])?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'qna_detail_search_on');" <?=checked($_COOKIE['qna_detail_search_on'], "Y")?>> 항상 상세검색</label>
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
				<th scope="row">답변유무</th>
				<td>
					<select name="rstat">
						<option value="">전체</option>
						<option value="2" <?=checked($rstat, 2, 1)?>>답변완료</option>
						<option value="1" <?=checked($rstat, 1, 1)?>>미처리</option>
					</select>
				</td>
				<th scope="row">분류</th>
					<td><?$_cate=outPutCate("qna",$cate); echo $_cate?$_cate:"사용안함";?></td>
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
					<input type="text" name="prd_str" value="<?=inputText($prd_str)?>" class="input" size ="50" placeholder="한글 2자(영문 4자) 이상 입력해주시기 바랍니다.">
				</td>
			</tr>
			<tr class="search_box_omit">
				<th scope="row">검색옵션</th>
				<td colspan="3">
					<?if($cfg['use_npay_qna']) {?>
					<label class="p_cursor"><input type="checkbox" name="npay" value="Y" <?=checked($npay, "Y")?>> 네이버페이 문의</label>
					<?}?>
					<?if($scfg->comp('use_talkpay', 'Y') == true) {?>
					<label class="p_cursor"><input type="checkbox" name="talkpay" value="Y" <?=checked($talkpay, "Y")?>> 카카오 페이구매 문의</label>
					<?}?>
					<?if($cfg['n_smart_store'] == 'Y' && $cfg['use_n_smart_qna'] == 'Y') {?>
					<label class="p_cursor"><input type="checkbox" name="n_smart" value="Y" <?=checked($n_smart, "Y")?>> 스마트스토어 문의</label>
					<?}?>
					<?if($cfg['use_talkstore_qna'] == 'Y') {?>
					<label class="p_cursor"><input type="checkbox" name="talkstore" value="Y" <?=checked($talkstore, "Y")?>> 카카오톡 스토어 문의</label>
					<?}?>
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
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&notice=Y" <?=$list_tab_active3?>>공지<span><?=number_format($_tabcnt[3])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=2" <?=$list_tab_active2?>>답변완료<span><?=number_format($_tabcnt[2])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&rstat=1" <?=$list_tab_active1?>>미답변<span><?=number_format($_tabcnt[1])?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=member@product_qna_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
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
	<table class="tbl_col">
		<caption class="hidden">상품Q&A 관리 리스트</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:50px">
			<col style="width:100px">
			<col style="width:150px">
			<col>
			<col style="width:50px">
			<col style="width:150px">
			<col style="width:120px">
			<col style="width:120px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">분류</th>
				<th scope="col">상품명</th>
				<th scope="col">제목</th>
				<th scope="col">메모</th>
				<th scope="col">이름</th>
				<th scope="col">등록일시</th>
				<th scope="col">답변일시</th>
				<th scope="col">답변자</th>
			</tr>
		</thead>
		<tbody>
			<?PHP

                foreach ($res as $data) {
                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

					$answer_check = (strip_tags($data['answer'], '<img>') || $data['upfile3'] || $data['upfile4']) ? "Y" : "";
					$data['rname'] = $data[name]." ".blackIconPrint($data['blacklist']);
					$answer = ($data['answer']) ? "<img src=\"$engine_url/_manage/image/common/icon_check.png\">" : "";
					$mng = ($data['mng_memo']) ? "<img src=\"$engine_url/_manage/image/common/icon_memo.png\">" : "";
					if($data['member_no'] && empty($admin['partner_no']) == true) {
						$data['rname'] = "<a onclick=\"viewMember('$data[member_no]','$data[member_id]')\" href=\"javascript:;\">$data[rname]<br>($data[member_id_v])</a>";
					}
					elseif($data['notice'] == "Y") {
						$data['rname'] = "공지";
						$answer = "";
					}

					$rclass = ($idx%2 == 0) ? "tcol2" : "tcol3";
					$data['answer_date_s'] = $data['answer_date'] > 0 ? date('Y/m/d H:i', $data['answer_date']) : '-';

					$prd = array();
					if($data['pno']) {
						$prd = get_info($tbl['product'], "no", $data['pno']);
					}
					$data['pname'] = $prd['name'];
					$data['hash'] = $prd['hash'];
					$data['rreg_date'] = $data['reg_date'];
					$data['rno'] = $data['no'];

					$content = cnvTip($data['content'], 500);
					$mng_memo = cnvTip($data['mng_memo'], 500);

					$tr_class = ($answer_check || $data['notice'] == 'Y') ? "" : "noanswer";
					if($data['upfile1'] || $data['upfile2']) {
						$data['atc'] = "<img src=\"$engine_url/_manage/image/icon/atc.gif\" alt=\"첨부파일\" style=\"vertical-align:top;\">";
					}

					$window_width = ($data['notice'] == 'Y') ? 800 : 1600;

					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => 'qna',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}

			?>
			<tr class="<?=$tr_class?>">
				<td>
					<input type="hidden" name="pno[]" value="<?=$data['no']?>">
					<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['rno']?>">
				</td>
				<td><?=$idx?></td>
				<td>
					<?if($data['checkout_no'] > 0) {?>
					<img src="<?=$engine_url?>/_manage/image/icon/ic_conv_naver_cbox.gif" class="btt" tooltip="네이버페이">
					<?}?>
					<?if($data['smartstore_no'] > 0) {?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_smartstore.png" class="btt" tooltip="네이버 스마트스토어">
					<?}?>
					<?if($data['talkstore_qnaId'] > 0) {?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_talkstore.png" class="btt" tooltip="카카오톡 스토어">
					<?}?>
					<?if(strpos($data['external_id'], 'talkpay') === 0) {?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_talkpay.png" class="btt" tooltip="카카오 페이구매" style="width:12px">
					<?}?>
					<?=$data['cate']?>
				</td>
				<td class="left" onmouseover="showToolTip(event, `<?=htmlspecialchars($data['pname'])?>`)" onmouseout="hideToolTip();">
					<?if($prd['no']){?>
					<a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=cutStr(stripslashes($data['pname']), 15)?></a>
					<a href="./?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:middle;"></a>
					<?}?>
				</td>
				<td class="left qna_title" title="" data-rno="<?=$data['rno']?>" data-windowwidth="<?=$window_width?>">
					<img src="<?=$engine_url?>/_manage/image/icon/secret_<?=($data['secret'] == "Y") ? "r" : "n";?>.gif"  style="width:12px; height:12px; vertical-align:top;">
					<?=cutStr(stripslashes($data['title']), 60)?>
					<?=$data['atc']?>
				</td>
				<td class="memo_title" title="" data-rno="<?=$data['rno']?>"><?=$mng?></td>
				<td><?=$data['rname']?></td>
				<td><?=date("Y/m/d H:i", $data['rreg_date'])?></td>
				<td><?=$data['answer_date_s']?></td>
				<td><?=$data['answer_id']?></td>
			</tr>
			<script type="text/javascript">
			helptext[<?=$idx?>]='<?=$content?>';
			helptext['m<?=$idx?>']='<?=$mng_memo?>';
			</script>
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
		<div class="left_area">
			<?if($admin['level'] < 4) {?>
			<span class="box_btn"><input type="button" value="공지사항 등록" onclick="wisaOpen('./pop.php?body=member@product_qna_view.frm&notice=Y');return false;"></span>
			<?}?>
			<?if($NumTotalRec){?>
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="deleteQna(document.prdFrm, '<?=$cfg['use_trash_qna']?>');"></span>
			<?}?>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

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

	// 관리자메모 미리보기
	$('.memo_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var rno = $(this).attr('data-rno');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'qna', 'field':'memo'},
				'type': "GET",
				'success': function(r) {
					if(r) {
						callback(r);
					}
				}
			});
		}
	});
</script>
<style type="text/css">
.qna_title {
	cursor: pointer;
}
.memo_title {
	cursor: pointer;
}
</style>