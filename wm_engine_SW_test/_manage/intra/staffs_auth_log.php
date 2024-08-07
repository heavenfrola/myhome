<?php

/**
 * 쇼핑몰 관리권한 설정 내역
 **/

if (isTable($tbl['mng_auth_log']) == false) {
    include __ENGINE_DIR__.'/_config/tbl_schema.php';
    $pdo->query($tbl_schema['mng_auth_log']);
}

// 검색
$w = '';
$_search_type = array(
    'admin_id' => '관리자아이디',
    'target_id' => '대상 관리자 아이디',
);
if (isset($_GET['search_str']) == true) {
    if (isset($_search_type[$_GET['search_type']]) == true) {
        $search_str = addslashes($_GET['search_str']);
        $w .= " and {$_GET['search_type']} like '%$search_str%'";
    }
}

// 기간 검색
if (isset($_GET['all_date']) == true) $all_date = $_GET['all_date'];
if (isset($_GET['start_date']) == true) $start_date = $_GET['start_date'];
else $start_date = date('Y-m-d', strtotime('-3 months'));
if (isset($_GET['finish_date']) == true) $finish_date = $_GET['finish_date'];
else $finish_date = date('Y-m-d', $now);
if ($all_date != 'Y') {
    $w .= " and reg_date between '$start_date' and '$finish_date 23:59:59'";
}

// 기간 선택툴
$date_type = array(
    '오늘' => '-0 days',
    '1주일' => '-1 weeks',
    '15일' => '-15 days',
    '1개월' => '-1 months', '3개월' => '-3 months', '6개월' => '-6 months',
    '1년' => '-1 years', '2년' => '-2 years', '3년' => '-3 years'
);
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

// 리스트 쿼리
$sql = "select * from {$tbl['mng_auth_log']} where 1 $w order by reg_date desc";

require __ENGINE_DIR__.'/_engine/include/paging.php';
$page = (isset($_GET['page']) == true) ? (int) $_GET['page'] : 1;
$row = (isset($_GET['row']) == true) ? (int) $_GET['row'] : 20;
if ($page <= 1) $page = 1;
if ($row < 20 || $row > 500) $row = 20;
$block = 10;

$NumTotalRec = $pdo->row("select count(*) from {$tbl['mng_auth_log']} where 1 $w");
$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
$PagingInstance->addQueryString(makeQueryString('page'));
$PagingResult = $PagingInstance->result($pg_dsn);
$sql .= $PagingResult['LimitQuery'];

$res = $pdo->iterator($sql);

// 관리자명 캐시
$mng_name = array();
$mngs = $pdo->iterator("select no, name from {$tbl['mng']}");
foreach ($mngs as $mng) {
    $mng_name[$mng['no']] = stripslashes($mng['name']);
}

// 대메뉴명 캐시
$big_name = array('main' => '관리자홈');
foreach ($menudata->big as $key => $val) {
    $big_name[$val->attr('category')] = $val->attr('name');
}

// parser
function parseData(&$res)
{
    global $NumTotalRec, $mng_name, $big_name, $_mng_levels;

    $data = $res->current();
    if (is_null($data) == true) return false;

    $data['idx'] = ($NumTotalRec-$res->key());
    $data['admin_name'] = $mng_name[$data['admin_no']];
    $data['target_name'] = $mng_name[$data['target_no']];

    $data['desc'] = '';

    // 제거된 대메뉴 권한
    $auth1 = explode('@', $data['auth1']);
    $_auth1 = array();
    foreach ($auth1 as $category) {
        $_auth1[] = "$big_name[$category]";
    }
    $_auth1 = implode(', ', $_auth1);
    if ($_auth1) {
        $data['desc'] .= "<li class=\"release\">$_auth1</li>";
    }

    // 추가된 대메뉴 권한
    $auth2 = explode('@', $data['auth2']);
    $_auth2 = array();
    foreach ($auth2 as $category) {
        $_auth2[] = "$big_name[$category]";
    }
    $_auth2 = implode(', ', $_auth2);
    if ($_auth2) {
        $data['desc'] .= "<li>$_auth2</li>";
    }

    // 제거된 소메뉴 권한
    if ($data['auth_d1']) {
        $auth_d1 = explode('@', trim($data['auth_d1'], '@'));
        $count = count($auth_d1);
        $category = $big_name[$data['category']];
        $data['desc'] .= "<li class=\"release\">{$category}메뉴의 세부권한 {$count}개</li>";
    }

    // 추가된 소메뉴 권한
    if ($data['auth_d2']) {
        $auth_d2 = explode('@', trim($data['auth_d2'], '@'));
        $count = count($auth_d2);
        $category = $big_name[$data['category']];
        $data['desc'] .= "<li>{$category} 메뉴의 세부권한 {$count}개</li>";
    }

    if ($data['desc']) {
        $data['desc'] = "<ul class=\"perm_items\">{$data['desc']}</ul>";
    }

    // 관리자 등급 변경
    if ($data['category'] == 'level') {
        $data['desc'] = sprintf(
            '<strong class="p_color">%s</strong>에서 <strong class="p_color">%s</strong>로 변경',
            $_mng_levels[$data['auth1']], $_mng_levels[$data['auth2']]
        );
    }

    $res->next();

    return $data;
}

?>
<style>
.box_search .box_input .select_input .select select {width: 160px !important;}
.box_search .box_input .select_input .area_input {margin-left: 160px;}
.perm_items li {line-height: 160%;}
.perm_items li.release {text-decoration: line-through; color: #999;}
</style>
<form name="searchFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">쇼핑몰 관리권한 설정 내역</h2>
	</div>
	<div id="search">
		<div class="box_search box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type, 'search_type', 2, '::선택::', $search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
    </div>
	<table class="tbl_row">
		<caption class="hidden">쇼핑몰 관리권한 설정 내역</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
        <tr>
            <th scope="row">기간</th>
            <td>
                <label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
                <input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~
                <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">

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
            <select name="row" onchange="location.href='<?=makeQueryString(true, 'row')?>&row='+this.value">
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
		<col style="width:200px">
		<col style="width:200px">
		<col>
		<col style="width:150px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">수행일시</th>
			<th scope="col">관리자</th>
			<th scope="col">대상 관리자</th>
			<th scope="col">권한 부여 페이지</th>
			<th scope="col">접속아이피</th>
		</tr>
	</thead>
	<tbody>
		<?php while($data = parseData($res)) {?>
        <tr>
            <td><?=$data['idx']?></td>
            <td><?=$data['reg_date']?></td>
            <td>
                <?=$mng_name[$data['admin_no']]?>
                <p>(<?=$data['admin_id']?>)</p>
            </td>
            <td>
                <?=$mng_name[$data['target_no']]?>
                <p>(<?=$data['target_id']?>)</p>
            </td>
            <td class="left"><?=$data['desc']?></td>
            <td><?=$data['remote_addr']?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="box_bottom">
	<?=$PagingResult['PageLink']?>
</div>