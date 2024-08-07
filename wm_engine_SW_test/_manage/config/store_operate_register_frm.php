<?php
/**
 * 영업 시간 설정
 */

$_time = otimeDefault();
if($_POST['execmode'] == true) {
	$so['otype'] = $_POST['otype'];
	$_no = $_POST['sono'];
} else {
	$_no = $so['no'];
}

$_otsql = "select * from {$tbl['store_operate_time']} where sono=:sono and otype=:otype ";
$_otarr = array(':sono' => $_no, ':otype'=>$so['otype']);
$otres = $pdo->iterator($_otsql, $_otarr);
$total_otres = $pdo->rowCount($_otsql, $_otarr);

if(!$total_otres) {
	$otres = array();
	$_otres = $_operate_otype_config[$so['otype']];
    foreach($_otres as $k => $v) {
		$otres[]['week'] = $k;
	}
}
$_time_display = (!$so['otype'] || $so['otype'] =='N') ? "none;":"block";
?>

<div class="th" id="option_items_week" style="display:<?php echo $_time_display; ?>">
     영업시간
		<?php if($so['otype'] == 'C') {?>
            <span class="box_btn_s"><a onclick="addWeekOpt({'is_add':true, 'append':'option_items_<?php echo $so['otype']?>', 'repet':'td_wrap'} )">+ 요일 추가</a></span>
		<?php } ?>
</div>
<div class="td" id="option_items_<?php echo $so['otype']?>" style="display:<?php echo $_time_display; ?>">
     <?php
        $seq=0;
        foreach($otres as $sd) {
            $seq++;
      ?>
        <?php include $engine_dir."/_manage/config/store_operate_register_sub_frm.php"; ?>
     <?php } ?>
</div>


