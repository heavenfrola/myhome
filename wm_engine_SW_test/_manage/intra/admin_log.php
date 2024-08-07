<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원접속통계
	' +----------------------------------------------------------------------------------------------+*/

	$sid = addslashes(trim($_GET['sid']));
	$sip = addslashes(trim($_GET['sip']));

	if ($admin['level'] > 1) {
		$w2=" and `member_id`!='wisa'";
	}
	if ($sid) {
		$w.=" and `member_id`='$sid'";
	}
	if ($sip) {
		$w.=" and `ip`='$sip'";
	}

    // 기간 검색
    $date_type = array(
        '오늘' => '-0 days',
        '1주일' => '-1 weeks',
        '15일' => '-15 days',
        '1개월' => '-1 months', '3개월' => '-3 months', '6개월' => '-6 months',
        '1년' => '-1 years', '2년' => '-2 years', '3년' => '-3 years'
    );
	if ($_GET['all_date']) $all_date = $_GET['all_date'];
	if ($_GET['start_date']) $start_date = $_GET['start_date'];
	if ($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if (!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-3 months'));
		$finish_date = date('Y-m-d', $now);
	}
	if ($all_date != 'Y') {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w .= " and log_date between '$_start_date' and '$_finish_date'";
	}
    $date_picker = array();
    foreach ($date_type as $key => $val) {
        $_btn_class = ($val && !$all_date && $finish_date == date('Y-m-d', $now) && $start_date == date('Y-m-d', strtotime($val))) ? 'on' : '';
        $_sdate = $_fdate = null;
        if ($val) {
            $_sdate = date('Y-m-d', strtotime($val));
            $_fdate = date('Y-m-d', $now);
        }
        $date_picker[$key] = array(
            's_date' => $_sdate,
            'f_date' => $_fdate,
            'btn_class' => $_btn_class
        );
    }

	$sql="select * from `$tbl[mng_log]` where 1 $w $w2 order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if ($page <= 1) $page = 1;
    if ($row <= 1) $row = 20;
	$block = 10;

	foreach($_GET as $key=>$val) {
		if($key!="page" && !is_array($val)) $add_QueryString="&".$key."=".$val;

		if($add_QueryString) {
			$QueryString.=$add_QueryString;
			if($key!="body") {
				$xls_query.=$add_QueryString;
			}
		}
	}

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[mng_log]` where 1 $w $w2");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

    $qs_without_row = makeQueryString(true, 'row');

?>
<form method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">사원접속통계</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">사원접속통계</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
        <tr>
            <th scope="row">기간</th>
            <td>
                <label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
                <input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
                <?php foreach($date_picker as $key => $val) { ?>
                <span class="box_btn_d <?=$val['btn_class']?> strong">
                    <input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$val['s_date']?>', '<?=$val['f_date']?>', '<?=$_GET['body']?>');">
                </span>
                <?php } ?>
                <script type="text/javascript">
                    searchDate(document.searchFrm);
                </script>
            </td>
        </tr>
		<tr>
			<th scope="row">관리자</th>
			<td>
                <div class="search_select">
                    <select name="sid">
                        <option value="">전체</option>
                        <?php
                            $sres = $pdo->iterator("select distinct(`member_id`) as member_id from `$tbl[mng_log]` where 1 $w2 order by `member_id`");
                            foreach ($sres as $sdata) {
                                echo nl2br(str_replace(" ", "&nbsp;&nbsp;", print_r($sdata, true)));
                        ?>
                        <option value="<?=$sdata['member_id']?>" <?=checked($sid,$sdata['member_id'],1)?>><?=$sdata['member_id']?></option>
                        <?php } ?>
                    </select>
                </div>
			</td>
		</tr>
		<tr>
			<th scope="row">아이피</th>
			<td>
                <div class="search_select">
                    <select name="sip">
                        <option value="">전체</option>
                        <?PHP
                            $sres = $pdo->iterator("select distinct(`ip`) as ip from `$tbl[mng_log]` where 1 $w2 order by `ip`");
                            foreach ($sres as $sdata) {
                        ?>
                        <option value="<?=$sdata['ip']?>" <?=checked($sip,$sdata['ip'],1)?>><?=$sdata['ip']?></option>
                        <?php } ?>
                    </select>
                </div>
			</td>
		</tr>
	</table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="검색"></span>
        <span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
    </div>
</form>

<div class="box_tab">
    <ul>
        <li><a href="#" class="active"> 전체 <span><?=number_format($NumTotalRec)?></span></a></li>
    </ul>
</div>
<!-- 정렬 -->
<div class="box_sort">
    <dl class="list">
        <dt class="hidden">정렬</dt>
        <dd>
            <select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
                <option value="20" <?=checked($row, 20, 1)?>>20</option>
                <option value="30" <?=checked($row, 30, 1)?>>30</option>
                <option value="50" <?=checked($row, 50, 1)?>>50</option>
                <option value="100" <?=checked($row, 100, 1)?>>100</option>
                <option value="500" <?=checked($row, 500, 1)?>>500</option>
            </select>
        </dd>
    </dl>
</div>
<table class="tbl_col">
	<caption class="hidden">사업접속통계 리스트</caption>
	<colgroup>
		<col style="width:80px">
		<col style="width:150px">
		<col style="width:150px">
		<col>
		<col style="width:150px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">수행일시</th>
			<th scope="col">관리자</th>
			<th scope="col">접속 결과</th>
			<th scope="col">접속아이피</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($res as $key => $data) {?>
		<tr>
			<td><?=$idx-$key?></td>
			<td><?=date('Y-m-d H:i', $data['log_date'])?></td>
			<td><?=$data['member_id']?></td>
			<td class="left"><?=$_login_result[$data['login_result']]?></td>
			<td><?=$data['ip']?></td>
		</tr>
		<?php } ?>
        <?php if ($NumTotalRec == 0) { ?>
        <tr>
            <td colspan="5"><p class="nodata">검색된 접속 내역이 없습니다.</p></td>
        </tr>
        <?php } ?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>

<script>
$(function() {
    $('.search_select>select').select2({'language':'ko'});
});
</script>