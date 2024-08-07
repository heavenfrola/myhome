<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  1:1고객상담 내역
	' +----------------------------------------------------------------------------------------------+*/

	addField($tbl['cs'], "reply_id", "varchar(30) not null");
	addField($tbl['cs'], "mng_memo", "text not null");

	$_msort=array('c.`reg_date` desc');
	$sort = numberOnly($_GET['sort']);
	if(!$sort || !$_msort['sort']) $sort=0;

	$_search_type[title]='제목';
	$_search_type[content]='질문';
	$_search_type[reply]='답변';
	$_search_type[reply_id]='답변자';
	$_search_type[name]='이름';
	$_search_type[member_id]='아이디';
	$_search_type[ono]='주문번호';

	$cate = addslashes(trim($_GET['cate']));
	if($cate) {
		$w.=" and concat(c.`cate1`,':',c.`cate2`)='$cate'";
	}
	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		$w.=" and c.`$search_type` like '%$search_str%'";
	}

	$pdo->query("update $tbl[cs] set reply_ok='Y' where (reply_ok='N' or isnull(reply_ok)) and reply_date>0");
	$stat = numberOnly($_GET['stat']);
	if($stat==1) {
		$w2 = ' and c.`reply_date`=0';
	} else if($stat == 2) {
		$w2 = ' and c.`reply_date`>0';
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
		$w .= " and c.reg_date between '$_start_date' and '$_finish_date'";
	}

	$list_tab_qry = makeQueryString(true, 'page', 'stat');
	$qs_without_row = makeQueryString(true, 'row');
	$xls_query = makeQueryString('body', 'page');

	$sql="select c.*, m.blacklist from `$tbl[cs]` c left join $tbl[member] m on c.member_no=m.no where 1 $w $w2 order by $_msort[$sort]";

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[cs]` c where 1 $w $w2");
	if($body == 'member@1to1_excel.exe') return;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=10;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$group=getGroupName();

	// 상태별 통계
	$_tabcnt = array();

	$_tmpres = $pdo->iterator("select c.reply_ok, count(no) as cnt from $tbl[cs] c where 1 $w group by c.reply_ok");
    foreach ($_tmpres as $_tmp) {
		$_rstat = ($_tmp['reply_ok'] == 'Y') ? '2' : '1';
		$_tabcnt[$_rstat] = $_tmp['cnt'];
		$_tabcnt['total'] += $_tmp['cnt'];
	}
	${'list_tab_active'.$stat} = 'class="active"';

?>
<form name="prdFrm" method="get" action="./">
<input type="hidden" name="body" value="<?=$body?>">
<input type="hidden" name="exec" value="">
<input type="hidden" name="ext" value="">
	<!-- 검색 폼 -->
	<div class="box_title first">
		<h2 class="title">1:1상담 관리</h2>
	</div>
	<div id="search">
		<div class="box_search ">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">상담 접수 내역 검색</caption>
			<colgroup>
				<col style="width:12%;">
				<col style="width:38%;">
				<col style="width:12%;">
				<col style="width:38%;">
			</colgroup>
			<tr>
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
			<tr>
				<th scope="row">답변유무</th>
				<td>
					<select name="stat">
						<option value="">전체</option>
						<option value="2" <?=checked($stat, 2, 1)?>>답변완료</option>
						<option value="1" <?=checked($stat, 1, 1)?>>미답변</option>
					</select>
				</td>
				<th scope="row">분류</th>
				<td>
					<select name="cate">
						<option value="">전체</option>
						<?
							foreach($_cust_cate as $key=>$val) {
								foreach($val as $key2=>$val2) {
						?>
						<option value="<?=$key.":".$key2?>" <?=checked($cate,$key.":".$key2,1)?>><?=$val2?></option>
						<?
								}
							}
						?>
					</select>
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
			<li><a href="<?=$list_tab_qry?>&stat=2" <?=$list_tab_active2?>>답변완료<span><?=number_format($_tabcnt[2])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&stat=1" <?=$list_tab_active1?>>미답변<span><?=number_format($_tabcnt[1])?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=member@1to1_excel.exe<?=$xls_query?>'"></span>
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
	<!-- //정렬 -->
	<table class="tbl_col">
		<caption class="hidden">상담 접수 내역 리스트</caption>
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
				<th scope="col">주문번호</th>
				<th scope="col">제목</th>
				<th scope="col">메모</th>
				<th scope="col">이름</th>
				<th scope="col">등록일시</th>
				<th scope="col">답변일시</th>
				<th scope="col">답변자</th>
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

					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					if(!$data[member_id]) $data[member_id]="비회원";
					$tr_class = ($data[reply_date]) ? "" : "noanswer";
					if($data[reply_date]) {
						$data[reply_date]=date("Y/m/d H:i",$data[reply_date]);
					}
					else {
						$data[reply_date]="-";
					}

					if($data['upfile1'] || $data['upfile2']) {
						$data['atc'] = "<img src=\"$engine_url/_manage/image/icon/atc.gif\" alt=\"첨부파일\" style=\"vertical-align:top;\">";
					}

					$mng = ($data['mng_memo']) ? "<img src=\"$engine_url/_manage/image/common/icon_memo.png\">" : "";

					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => '1to1',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}
			?>
			<tr class="<?=$tr_class?>">
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
				<td><?=$idx?></td>
				<td><?=$_cust_cate[$data[cate1]][$data[cate2]]?></td>
				<td><a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><?=$data[ono]?></a></td>
				<td class="left cs_title" title="" data-rno="<?=$data[no]?>">
					<?=cutStr(stripslashes($data[title]),50)?>
					<?=$data['atc']?>
				</td>
				<td class="memo_title" title="" data-rno="<?=$data[no]?>"><?=$mng?></td>
				<td><a href="javascript:;" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')"><?=$data[name]?> <?=blackIconPrint($data['blacklist'])?><br>(<?=$data['member_id_v']?>)</a></td>
				<td><?=date("Y/m/d H:i",$data[reg_date])?></td>
				<td><?=$data[reply_date]?></td>
				<td><?=$data[reply_id]?></td>
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
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="deleteCS();"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
</form>

<script type="text/javascript">
	// 질문/답변 미리보기
	$('.cs_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var rno = $(this).attr('data-rno');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'cs'},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var rno = $(this).attr('data-rno');
		wisaOpen('pop.php?body=member@1to1_view.frm&no='+rno,'','yes');
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
				'data': {'body':'member@member_preview.exe', 'rno':rno, 'type':'cs', 'field':'memo'},
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
.cs_title {
	cursor: pointer;
}
.memo_title {
	cursor: pointer;
}
</style>