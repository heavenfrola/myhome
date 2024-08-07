<?php

/**
 * 데이터 수정 내역
 **/

use Wing\common\WorkLog;

require_once __ENGINE_DIR__.'/_engine/include/paging.php';
require_once __ENGINE_DIR__.'/_manage/intra/work_log.inc.php';

$w = '';
$bind = array();

// 날짜 검색
$all_date = (isset($_GET['all_date']) == true && $_GET['all_date'] == 'Y') ? 'Y' : '';
if (isset($_GET['start_date']) == false) $_GET['start_date'] = date('Y-m-d', strtotime('-1 months'));
if (isset($_GET['finish_date']) == false) $_GET['finish_date'] = date('Y-m-d');
$start_date = $_GET['start_date'];
$finish_date = $_GET['finish_date'];
if ($all_date != 'Y') {
    $w .= " and reg_date between ? and ?";
    $bind[] = $start_date.' 00:00:00';
    $bind[] = $finish_date.' 23:59:59';
}

// 페이지 검색
if (isset($_GET['wpage']) == false) $_GET['wpage'] = '';
$wpage = addslashes($_GET['wpage']);
if ($wpage) {
    $w .= " and page=?";
    $bind[] = $wpage;
}

// 검색어
if (isset($_GET['search_str']) == false) $_GET['search_str'] = '';
$search_str = addslashes($_GET['search_str']);
if ($search_str) {
    $w .= " and title like ?";
    $bind[] = "%$search_str%";
}

// 페이징
if (isset($_GET['page']) == false) $_GET['page'] = 1;
$page = (int) $_GET['page'];
if ($page <= 1) $page = 1;
if (!$row) $row = 20;
if ($row > 100) $row = 100;
$block = 10;
$NumTotalRec = $pdo->row("select count(distinct page, timestamp) from {$tbl['work_log']} where 1 $w", $bind);
$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
$PagingInstance->addQueryString(makeQueryString('page'));
$PagingResult = $PagingInstance->result($pg_dsn);

$sql  = "select *, count(*) as cnt, min(pkey) as pkey from {$tbl['work_log']} where 1 $w group by page, timestamp order by no desc";
$sql .= $PagingResult['LimitQuery'];

$pageRes = $PagingResult['PageLink'];
$res = $pdo->iterator($sql, $bind);
$idx = $NumTotalRec-($row*($page-1));

$log = new WorkLog();

?>
<style>
.deleted {text-decoration: line-through;}
.tbl_col, .tbl_inner {table-layout: fixed;}
.list_info {max-height: 125px; padding: 15px; overflow-y: auto;}
.list_info::-webkit-scrollbar {width: 5px;}
.list_info::-webkit-scrollbar-track {background: #f1f1f1;}
.list_info::-webkit-scrollbar-button:start {height:0}
.list_info::-webkit-scrollbar-button:end {height:0}
.list_info::-webkit-scrollbar-thumb {background-color: #999;}
.list_info li {overflow: hidden; white-space: nowrap; text-overflow:ellipsis;}
</style>
<!-- 검색폼 -->
<div class="box_title first">
	<h2 class="title">데이터 수정 내역</h2>
</div>

<form id="searchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
        <table class="tbl_search">
			<caption class="hidden">데이터 수정 내역 검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">페이지</th>
				<td>
                    <?=selectArray($_page, 'wpage', false, ':: 전체 ::', $wpage)?>
				</td>
				<th scope="row">수정일자</th>
				<td>
                    <label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date, 'Y')?> onClick="searchDate(this.form)"> 전체기간</label>
                    <input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~
                    <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
                    <script type="text/javascript">
                        searchDate(document.querySelector('#searchFrm'));
                    </script>
				</td>
			</tr>
        </table>
    </div>
    <div class="box_bottom top_line">
        <span class="box_btn blue"><input type="submit" value="검색"></span>
        <span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
    </div>
</form>

<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 수정내역이 검색되었습니다.
</div>
<!-- //검색 총합 -->

<table class="tbl_col">
	<colgroup>
		<col style="width:80px">
		<col style="width:120px">
		<col>
		<col>
		<col style="width:110px">
		<col style="width:110px">
		<col style="width:110px">
		<col style="width:70px">
	</colgroup>
	<thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">데이터 종류</th>
            <th scope="col">대상 데이터</th>
            <th scope="col">변경 내역</th>
            <th scope="col">처리 일시</th>
            <th scope="col">처리자</th>
            <th scope="col">아이피</th>
            <th scope="col">상세보기</th>
        </tr>
    </thead>
    <tbody>
        <?php while($data = $log->parse($res)) { ?>
        <tr>
            <td><?=$idx--?></td>
            <td><?=$_page[$data['page']]?></td>
            <td class="left"><?=$data['title2']?></td>
            <td class="left" style="padding: 0">
                <ul class="list_info">
                    <?php foreach($data['diff'] as $diff) { ?>
                    <li><?=sprintf('<span>%s</span> : %s', $diff[0], cutstr($diff[1], 200));?></li>
                    <?php } ?>
                </ul>
            </td>
            <td><?=date('Y-m-d H:i', strtotime($data['reg_date']))?></td>
            <td><?=$data['admin_id']?></td>
            <td><?=$data['remote_addr']?></td>
            <td><span class="box_btn_s blue">
                <?php if ($data['page'] == $tbl['product'] && $data['cnt'] > 1) { ?>
                <input type="button" value="상세" onclick="viewDetail(<?=$data['timestamp']?>, '<?=$data['page']?>', this);"></span>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="box_bottom">
	<?=$pageRes?>
</div>

<script>
var _timestamp;
var _wpage;
function viewDetail(timestamp, wpage, obj, page) {
	var detail = $('.detail_'+timestamp);
	if (!page) page = 1;

	if (detail.length == 0 || page > 1) {
        printLoading();

		$('.details').remove();
		$.post('?body=intra@work_log.exe', {'exec': 'viewDetail', 'timestamp': timestamp, 'wpage': wpage, 'page': page}, function(r) {
			$(obj).parents('tr').after(
                "<tr class='details detail_"+timestamp+"' style='background:#f2f2f2;'>\
                 <td colspan='"+$(obj).parents('tr').find('td').length+"' class='right'>"+r+"</td>\
                 </tr>"
            );
			_timestamp = timestamp;
            _wpage = wpage;
            removeLoading();
		});
	} else {
		detail.remove();
	}
}

function viewDetailPage(rows, page) {
	$.post('?body=intra@work_log.exe', {'exec':'viewDetail', 'timestamp':_timestamp, 'wpage': _wpage, 'page':page}, function(r) {
		$('.detail_'+_timestamp+'>td').html(r);
	});
}
</script>