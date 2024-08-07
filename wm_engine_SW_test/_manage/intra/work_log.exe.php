<?PHP

/**
 * 데이터 수정 내역
 **/

use Wing\common\WorkLog;

include __ENGINE_DIR__.'/_engine/include/paging.php';
require_once __ENGINE_DIR__.'/_manage/intra/work_log.inc.php';

$WorkLog = new WorkLog();

$timestamp = numberOnly($_POST['timestamp']);

if (isset($_POST['page']) == false) $_POST['page'] = 1;
$page = (int) $_POST['page'];
if ($page <= 1) $page = 1;
$row = 10;
$block = 20;

$NumTotalRec = $pdo->row("select count(*) from {$tbl['work_log']} a inner join {$_POST['wpage']} b on a.pkey=b.no where a.timestamp='$timestamp' order by a.no asc");
$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, null, 'viewDetailPage');
$PagingInstance->addQueryString(makeQueryString('page'));
$PagingResult = $PagingInstance->result('ajax_admin');
$pg_res = $PagingResult['PageLink'];

$res = $pdo->iterator("
    select a.page, a.pkey, a.difference, a.title, b.*
        from {$tbl['work_log']} a left join {$_POST['wpage']} b on a.pkey=b.no
        where a.timestamp='$timestamp'
        order by null ".$PagingResult['LimitQuery']
);

?>
<form class="box_middle4" method="post" action="?">
	<table class="tbl_inner line full">
		<colgroup>
			<col style="width:50px">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" class="all_chkbox"></th>
				<th>제목</th>
                <th>변경 내역</th>
			</tr>
		</thead>
		<tbody>
			<?php while($data = $WorkLog->parse($res)) { ?>
			<tr>
				<td>
					<?php if (empty($data['class']) == true) { ?>
					<input type="checkbox" name="no[]" value="<?=$data['no']?>" class="sub_chkbox">
					<?php } ?>
				</td>
				<td class="left">
                    <a href="<?=$data['link']?>" target="_blank"><?=$data['imgstr']?></a>
                    <a href="<?=$data['link']?>" target="_blank" class="<?=$data['class']?>"><?=$data['title']?></a>
                </td>
                <td class="left" style="padding: 0">
                    <ul class="list_info" style="max-height: 115px; overflow-y: auto;'">
                        <?php foreach($data['diff'] as $diff) { ?>
                        <li><?=sprintf('<span>%s</span> : %s', $diff[0], cutstr($diff[1], 200));?></li>
                        <?php } ?>
                    </ul>
                </td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="right_area">
			<span class="box_btn"><input type="button" value="닫기" onclick="$('.details').remove();"></span>
		</div>
	</div>
</form>
<script type="text/javascript">
new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
</script>