<?php

/**
 * 배송 불가 지역 설정 목록
 **/

require_once __ENGINE_DIR__.'/_engine/include/paging.php';

if (isTable($tbl['delivery_range']) == false) {
    require __ENGINE_DIR__.'/_config/tbl_schema.php';
    $pdo->query($tbl_schema['delivery_range']);
}

if (isset($_POST['page']) == false) $_POST['page'] = 1;
$type = $_POST['type'];
$page = (int) $_POST['page'];
$row = 10;
$block = 10;
$type = (isset($_POST['type'])) ? $_POST['type'] : $scfg->get('dlv_possible_type');
if ($page <= 1) $page = 1;
if ($row <= 10) $row = 10;

$NumTotalRec = $pdo->row("select count(*) from {$tbl['delivery_range']} where partner_no=?", array(
    (int) $admin['partner_no']
));
$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, null, 'reloadDeliveryRange');
$PagingInstance->addQueryString(makeQueryString('page'));
$PagingResult = $PagingInstance->result('ajax_admin');

$scfg->def('dlv_possible_type', 'N');

$dlist = $pdo->iterator("select * from {$tbl['delivery_range']} where type=? and partner_no=? order by name asc ".$PagingResult['LimitQuery'], array(
    $type, (int) $admin['partner_no']
));

function parseDeliveryRange(&$dlist) {
    $data = $dlist->current();
    $dlist->next();

    if (is_null($data) == true) {
        return false;
    }

    $data['name'] = stripslashes($data['name']);

    $dong = explode(',', $data['dong']);
    $ri = explode(',', $data['ri']);
    $data['area'] = ($data['sido'].' '.$data['gugun'].' '.$dong[0].' '.$ri[0]);
    $data['reg_date'] = date('Y-m-d H:i:s', strtotime($data['reg_date']));
    $data['areas'] = str_replace(',', '<br />', $data['dong']);
    $data['areas_ri'] = str_replace(',', '<br />', $data['ri']);
    $cnt++;

    if(!$data['gugun']) $data['area'] .= '전체';
    elseif(!$data['dong']) $data['area'] .= '전체';

    $count = count($dong)-1;
    if($count > 0) {
        if($count > 0) $data['area'] .= " 외 {$count}";
        $data['area'] = "<a id='da_{$data['no']}' href='#' onclick='return false;' onmouseover=\"new R2Tip(this, '{$data['areas']}', null, event)\">{$data['area']}</a>";
    }

    $count = count($ri)-1;
    if($count > 0) {
        if($count > 0) $data['area'] .= " 외 {$count}";
        $data['area'] = "<a id='da_{$data['no']}' href='#' onclick='return false;' onmouseover=\"new R2Tip(this, '{$data['areas_ri']}', null, event)\">{$data['area']}</a>";
    }

    return $data;
}

?>
<script>
$(function() {
new chainCheckbox(
	$('.rangeall'),
	$('.rangeone')
)
});
</script>
<table class="tbl_col tbl_col_bottom">
    <colgroup>
        <col style="width:40px">
        <col style="width:220px">
        <col>
        <?php if ($type == 'D') { ?>
        <col>
        <?php } ?>
        <col style="width:100px">
        <col style="width:100px">
        <col style="width:130px">
    </colgroup>
    <thead>
        <tr>
            <th scope="col"><input type="checkbox" class="rangeall"></th>
            <th scope="col">배송지 별칭</th>
            <th scope="col">지역</th>
            <?php if ($type == 'D') { ?>
            <th scope="col">배송 제한 안내 문구</th>
            <?php } ?>
            <th scope="col">수정</th>
            <th scope="col">삭제</th>
            <th scope="col">등록일</th>
        </tr>
    </thead>
    <tbody id="d_list">
        <?php if ($dlist->rowCount() == 0) { ?>
        <tr>
            <td colspan="7"><p class="nodata">등록된 배송지가 없습니다.</p></td>
        </tr>
        <?php } ?>
        <?php while($data = parseDeliveryRange($dlist)) { ?>
        <tr>
            <td><input type="checkbox" class="rangeone" value="<?=$data['no']?>"></td>
            <td><?=$data['name']?></td>
            <td class="left"><?=$data['area']?></td>
            <?php if ($type == 'D') { ?>
            <td class="left"><?=$data['reason']?></td>
            <?php } ?>
            <td><span class="box_btn_s blue"><input type="button" onclick="modifyDeliveryRange(<?=$data['no']?>)" value="수정"></span></td>
            <td><span class="box_btn_s gray"><input type="button" onclick="removeDeliveryRange(<?=$data['no']?>)" value="삭제"></span></td>
            <td><?=$data['reg_date']?></td>
        </tr>
        <?PHP } ?>
    </tbody>
</table>
<div class="box_bottom">
    <div class="right_area">
        <span class="box_btn gray">
            <input
                type="button"
                value="선택 삭제"
                onclick="removeDeliveryRange(document.getElementById('delivery_range_list'));"
            >
        </span>
    </div>
    <?=$PagingResult['PageLink']?>
</div>