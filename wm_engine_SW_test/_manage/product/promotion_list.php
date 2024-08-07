<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  프로모션 기획전
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name = editSkinName();

	if(!isTable($tbl['promotion_link'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['promotion_link']);
		$pdo->query($tbl_schema['promotion_list']);
		$pdo->query($tbl_schema['promotion_pgrp_link']);
		$pdo->query($tbl_schema['promotion_pgrp_list']);
	}

	// 텍스트 검색
	$_search_type = array();
	$_search_type['promotion_nm'] = '프로모션 기획전명';
	$_search_type['admin_id'] = '작성자';

	$w = '';
	$list_tab_qry = '';
	$list_tab_qry = makeQueryString(true, 'stat', 'page');

	$sch_prd_stat = numberOnly($_GET['stat']);
	if(!$sch_prd_stat) $sch_prd_stat = '1';
	${'list_tab_active'.$sch_prd_stat} = 'class="active"';

	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	$start_date = preg_replace('/[^0-9-]/', '', $_GET['start_date']);
	$finish_date = preg_replace('/[^0-9-]/', '', $_GET['finish_date']);
	if(!$start_date || !$finish_date) $all_date = "Y";
	if(!$all_date) {
		$_start_date = $start_date.' 00:00:00';
		$_end_date = $finish_date.' 23:59:59';
		$w .= " and `reg_date` >= '$_start_date'";
		$w .= " and `reg_date` <= '$_end_date'";
	}
	if(!$start_date || !$finish_date) $start_date = $finish_date = date("Y-m-d", $now);

	$ing_all_date = ($_GET['ing_all_date'] == 'Y') ? 'Y' : '';
	$date_start = preg_replace('/[^0-9-]/', '', $_GET['date_start']);
	$date_end = preg_replace('/[^0-9-]/', '', $_GET['date_end']);
	if(!$date_start || !$date_end) $ing_all_date = "Y";
	if(!$ing_all_date) {
		$_date_start = $date_start.' '.$_GET['ts_times'].':00:00';
		$_date_end = $date_end.' '.$_GET['ts_timee'].':59:59';
		$w .= " and ((period_type = 'N') OR (period_type = 'Y' and (`date_start` <= '$_date_start' and `date_end` >= '$_date_end') or (`date_start` <= '$_date_start' and `date_end` >= '$_date_start') or (`date_start` <= '$_date_end' and `date_end` >= '$_date_end')))";
	}

	if(!$search_type) $search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		if($search_type=='admin_id') {
			$admin_id = $pdo->row("select admin_id from $tbl[mng] where `name` like '%$search_str%'");
			$w .= " and `admin_id`='$admin_id'";
		}else {
			$w .= " and `$search_type` like '%$search_str%'";
		}
	}

	if($_GET['use_yn']) {
		$use_yn = ($_GET['use_yn'] == 'Y') ? 'Y' : 'N';
		$w .= " and `use_yn` = '$use_yn'";
	}

	$sw = "";
	$_nowdate = date("Y-m-d H:i:s", $now);
	switch($sch_prd_stat) {
		case '2' : // 대기
			$sw .= " and period_type='Y' and date_start>'$_nowdate' $w";
		break;
		case '3' : // 진행중
			$sw .= " and ((period_type='Y' and date_start<='$_nowdate' and date_end>='$_nowdate') or period_type='N') $w";
		break;
		case '4' : // 종료
			$sw .= " and period_type='Y' and date_end<'$_nowdate' $w";
		break;
	}

    // 정렬 순서
    $_sort = array(
        1 => '정렬설정순',
        2 => '기획전명순',
        3 => '등록일순',
        4 => '등록일역순',
        5 => '시작일순',
        6 => '시작일역순',
        7 => '종료일순',
        8 => '종료일역순',
    );
    if (isset($_GET['sort']) == false) $_GET['sort'] = 1;
    settype($_GET['sort'], 'integer');
    switch($_GET['sort']) {
        case '1' : $sort = 'sort asc'; break;
        case '2' : $sort = 'promotion_nm asc'; break;
        case '3' : $sort = 'reg_date asc'; break;
        case '4' : $sort = 'reg_date desc'; break;
        case '5' : $sort = 'date_start asc, date_end asc'; break;
        case '6' : $sort = 'date_start desc, date_end desc'; break;
        case '7' : $sort = 'date_end asc, date_start asc'; break;
        case '8' : $sort = 'date_end desc, date_start desc'; break;
    }

	$sql = "select * from `{$tbl['promotion_list']}` where 1 $w $sw order by $sort";
	$NumTotalRec = $pdo->row("select count(distinct no) from $tbl[promotion_list] where 1 $w $sw");
	$res = $pdo->iterator($sql);

	// 상태별 통계
	$_tabcnt = array('total' => 0, 2 => 0, 3 => 0, 4 => 0);
	$_tabcnt[2] = $pdo->row("select count(*) as cnt from $tbl[promotion_list] where period_type='Y' and date_start>'$_nowdate' $w");
	$_tabcnt[3] = $pdo->row("select count(*) as cnt from $tbl[promotion_list] where ((period_type='Y' and date_start<='$_nowdate' and date_end>='$_nowdate') or period_type='N') $w");
	$_tabcnt[4] = $pdo->row("select count(*) as cnt from $tbl[promotion_list] where period_type='Y' and date_end<'$_nowdate' $w");
	$_tabcnt['total'] = $_tabcnt[2]+$_tabcnt[3]+$_tabcnt[4];

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<?php if (!is_file($root_dir."/_skin/".$_skin_name."/CORE/shop_promotion.wsr")) { ?>
<div class="msg_topbar sub quad warning">
	사용 중인 스킨 내 프로모션 기획전 페이지 작업유무를 확인해주세요.<br><br>
	프로모션 기획전 및 프로모션 상품그룹 관리를 사용하기 위해서는<br>
	반드시 사용중인 스킨 내 프로모션 기획전 페이지가 존재해야 합니다.<br><br>
	<strong>[PC 쇼핑몰]</strong> <a href="?body=design@editor&type=&edit_pg=4%2F12" target="_blank" class="list_move">바로가기</a>
	<strong>[모바일 쇼핑몰]</strong> <a href="?body=wmb@editor&type=mobile&edit_pg=4%2F12" target="_blank" class="list_move">바로가기</a>
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>
<!-- 검색 폼 -->
<form name="prmSearchFrm" id="prmSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">프로모션 기획전</h2>
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
					<div id="searchCtl" onclick="toggle_shadow()"><?php searchBoxBtn('prmSearchFrm', $_COOKIE['promotion_search_on']); ?></div>
					<label class="always p_cursor"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'promotion_search_on');" <?=checked($_COOKIE['promotion_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">프로모션 기획전 검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<label class="p_cursor"><input type="radio" name="use_yn" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="use_yn" value="Y" <?=checked($use_yn, 'Y')?>> 사용</label>
					<label class="p_cursor"><input type="radio" name="use_yn" value="N" <?=checked($use_yn, 'N')?>> 미사용</label>
				</td>
			</tr>
			<tr>
				<th scope="row">진행기간 검색</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="ing_all_date" value="Y" <?=checked($ing_all_date,"Y")?> onClick="searchDate2(this.form)"> 전체 기간</label>
					<input type="text" name="date_start" value="<?=$date_start?>" size="10" class="input datepicker">
					<select name="ts_times">
					<?php for ($i = 0; $i <= 23; $i++) { $i = sprintf('%02d', $i) ?>
						<option value="<?=$i?>" <?=checked($_GET['ts_times'], $i, 1)?>><?=$i?> 시</option>
					<?php } ?>
					</select> ~
					<input type="text" name="date_end" value="<?=$date_end?>" size="10" class="input datepicker">
					<select name="ts_timee">
					<?php for ($i = 0; $i <= 23; $i++) { $i = sprintf('%02d', $i); ?>
						<option value="<?=$i?>" <?=checked($_GET['ts_timee'], $i, 1)?>><?=$i?> 시</option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">등록일 검색</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_tab">
	<ul>
		<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active1?>>전체<span class="stat_total"><?=$_tabcnt['total']?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&stat=2" <?=$list_tab_active2?>>대기<span class="stat_2"><?=$_tabcnt[2]?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&stat=3" <?=$list_tab_active3?>>진행중<span class="stat_3"><?=$_tabcnt[3]?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&stat=4" <?=$list_tab_active4?>>종료<span class="stat_4"><?=$_tabcnt[4]?></span></a></li>
	</ul>
</div>
<div class="box_sort">
    <?=selectArray($_sort, 'sort', false, null, $_GET['sort'], 'setSort()')?>
</div>
<!-- //검색 총합 -->
<!-- 검색 테이블 -->
<form id="prmlFrm" name="prmlFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col tbl_col2">
		<caption class="hidden">상품수정/관리 리스트</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:50px">
			<col>
			<col style="width:250px">
			<col style="width:80px">
			<col style="width:130px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prmlFrm.check_prno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">프로모션 기획전명</th>
				<th scope="col">진행기간</th>
				<th scope="col">상태</th>
				<th scope="col">등록일시</th>
				<th scope="col">작성자</th>
				<th scope="col">사용</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody id="prm_list">
		<?php
			$idx = 1;
            foreach ($res as $data) {
				if($data['period_type']=="N") {
					$date = "무제한";
				}else {
					$datetime = new DateTime($data['date_start']);
					$date_start = $datetime->format('Y-m-d H:i');
					$datetime = new DateTime($data['date_end']);
					$date_end = $datetime->format('Y-m-d H:i');
					$date = $date_start." ~ ".$date_end;
				}
				$stat_text = "";
				if($data['period_type']=='Y' && $data['date_start']>$_nowdate) {
					$stat_text = "대기";
				}else if(($data['period_type']=='Y' && $data['date_start']<=$_nowdate && $data['date_end']>=$_nowdate) || $data['period_type']=='N') {
					$stat_text = "진행중";
				}else if($data['period_type']=='Y' && $data['date_end']<$_nowdate) {
					$stat_text = "종료";
				}

				$admin_name = stripslashes($pdo->row("select name from $tbl[mng] where no='$data[admin_no]'"));

				$use_on = ($data['use_yn'] == 'Y') ? 'on' : '';

				$datetime = new DateTime($data['reg_date']);
				$reg_date = $datetime->format('Y-m-d H:i');
		?>
				<tr id=<?=$data['no']?>>
					<td><input type="checkbox" name="check_prno[]" id="check_prno" value="<?=$data['no']?>"></td>
					<td><?=$idx?></td>
					<td class="left"><a href="./?body=product@promotion_register&prno=<?=$data['no']?>"><?=stripslashes($data['promotion_nm'])?></a> <a href="/shop/promotion.php?pno=<?=$data['no']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt=""></a></td>
					<td><a href="./?body=product@promotion_register&prno=<?=$data['no']?>"><?=$date?></a></td>
					<td><a href="./?body=product@promotion_register&prno=<?=$data['no']?>"><?=$stat_text?></a></td>
					<td><?=$reg_date?></td>
					<td><?=$admin_name?></td>
					<td><div class="switch <?=$use_on?>" onclick="toggleUsePromotion(<?=$data['no']?>, $(this))"></div></td>
					<td><span class="box_btn_s"><input type="button" value="수정" onclick="location.href='./?body=product@promotion_register&prno=<?=$data['no']?>'"></a></span></td>
				</tr>
		<?PHP

			$idx++;
			}
		?>
		</tbody>
	</table>
</form>
<!-- //검색 테이블 -->
<!-- 페이징 & 버튼 -->
<div class="box_middle2 left">
	<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="prDelete(document.prmlFrm)"></span>
	<span class="btn_move last_h"><input type="button" name="" value="마지막" onclick="srt.toBottom()"></span>
	<span class="btn_move next_h"><input type="button" name="" value="아래" onclick="listsort('plus');"></span>
	<span class="btn_move prev_h"><input type="button" name="" value="위" onclick="listsort('minus');"></span>
	<span class="btn_move first_h"><input type="button" name="" value="처음" onclick="srt.toTop()"></span>
	<span class="ea"><input type="text" id="step" name="step" value="1" class="input"> 칸 이동</span>
	<span class="box_btn"><input type="button" value="원래대로" onclick="location.reload();"></span>
	<span class="box_btn blue"><input type="button" onclick="prSort()" value="적용하기"></span>
	<div class="right_area">
		<span class="box_btn blue"><input type="button" value="프로모션 기획전 등록" onclick="location.href='./?body=product@promotion_register'"></span>
	</div>
</div>
<!-- //페이징 & 버튼 -->
<div class="box_bottom left" style="margin-top: 40px;">
	<ul class="list_info">
		<li>진행기간에 따른 상태가 종료임에도 사용여부가 사용으로 되어있다면 쇼핑몰 내 노출 되어 집니다.</li>
	</ul>
</div>

<script type="text/javascript">
	searchDate(document.prmSearchFrm);
	searchDate2(document.prmSearchFrm);
	var srt = null;
	$(function() {
		srt = new Sorttbl('prmlFrm');
	});
	function listsort(type) {
		var step = $('#step').val();
		if(type=='plus') {
			srt.move(+(step));
		}else {
			srt.move(-(step));
		}
	}
	function searchDate2(f){
		if(f.ing_all_date.checked==true)
		{
			textDisable(f.date_start,1);
			textDisable(f.date_end,1);
			textDisable(f.ts_times,1);
			textDisable(f.ts_timee,1);
		}
		else
		{
			textDisable(f.date_start,'');
			textDisable(f.date_end,'');
			textDisable(f.ts_times,'');
			textDisable(f.ts_timee,'');
		}
	}
	function toggleUsePromotion(no, o) {
		$.post('?body=product@promotion_register.exe', {'exec':'toggle', 'prno':no}, function(r) {
			if(r.changed == 'Y') {
				o.addClass('on');
			} else {
				o.removeClass('on');
			}
		});
	}

	function prDelete(f) {
		if(!confirm("프로모션 기획전을 삭제하시겠습니까?")) return false;
		if(!checkCB(f.check_prno,"삭제할 프로모션 기획전을 선택해주세요.")) return false;

		f.body.value="product@promotion_register.exe";
		f.exec.value = 'pr_delete';
		f.method='post';
		f.target=hid_frame;
		f.submit();
	}
	function prSort() {
		var sortingArray = [];
		$('#prm_list tr').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			sortingArray.push(pno);
		});
		$.post('?body=product@promotion_register.exe', {'no':sortingArray, "exec":"pr_sort"}, function(r) {
			if(r=='OK') {
				location.reload();
			}
		});
	}

    function setSort()
    {
        location.href = '?body=product@promotion_list&sort='+$('select[name=sort]').val();
    }
</script>