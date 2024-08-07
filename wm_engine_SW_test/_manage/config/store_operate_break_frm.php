<?PHP
/**
 * 영업 시간 브레이크 타임 설정
 */

$_time = otimeDefault();

if($_POST['break_addmode'] == true) {
	$no = numberOnly($_POST['so_no']);
	$ires = $pdo->iterator("select '0' as no");
	$opt_item_idx = numberOnly($_POST['is_add']);
	$o_append = $_POST['append'];
	$o_seq = $_POST['seq'];
	$total_res = $opt_item_idx+1;
} else {
	$o_seq = $seq;
	$o_append = 'td_wrap'.$seq;

    $_bsql = "select * from {$tbl['store_operate_break']} where stno=:stno";
    $_barr = array(':stno'=>$sd['no']);

	$ires = $pdo->iterator($_bsql, $_barr);
	$total_res = $pdo->rowCount($_bsql, $_barr);
	$opt_item_idx = 0;
}
?>
<?php
    foreach($ires as $item) {
    	$opt_item_idx++;
        $btn_display = ($total_res == $opt_item_idx) ? "":"display:none";
?>
    <div id="option_row_<?php echo $opt_item_idx; ?>" class="option_item_row">
        <input type="hidden" name="ob_no<?php echo $o_seq; ?>[]" value="<?php echo $item['no']; ?>">
        <div>
            <span class="s_title"><?php if($opt_item_idx == 1) {?>브레이크 타임<?php } ?></span>
            <div class="before">
				<?php echo selectArray($_time, 'break_shour'.$o_seq.'[]', 2, ':: 시작시간 ::', $item['shour']); ?>
            </div>
            &nbsp;~ &nbsp;
            <div class="after">
				<?php echo selectArray($_time, 'break_ehour'.$o_seq.'[]', 2, ':: 마감시간 ::', $item['ehour']); ?>
            </div>
			<div class="box_btn_s gray"><span onclick="optDelete('<?php echo $o_seq;?>', '<?php echo $opt_item_idx;?>');">삭제</span></div>
             <div class="box_btn_s blue" style="<?php echo $btn_display;?>"><span onclick="addOpt( '<?php echo $sd['no'];?>', {'append':'<?php echo $o_append;?>', 'repet':'option_row', 'is_add':true, 'seq':'<?php echo $o_seq;?>'});">시간 추가</span></div>

        </div>
    </div>
<?php } ?>