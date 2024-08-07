<?php
if($_POST['addmode'] == true) {
	$seq = $_POST['is_add'];
	$so['otype'] = $_POST['otype'];
	$sd['no'] = numberOnly($_POST['break_no']);

	$_time = otimeDefault();
}
if(!$_POST['execmode']) {
	$_week_list = array();
	if($sd['week']) $_week_list = @explode(',',$sd['week']);
}

if(!$sd['buse']) $sd['buse'] = 'N';
$buse_display = ($sd['buse'] == 'N') ? "":"display:none";
if($so['otype'] == 'B') $td_title = $_operate_otype_config[$so['otype']][$seq];
else $td_title = "영업시간";
?>
<div class="td_wrap<?php echo $seq;?>">
	<input type="hidden" name="ono[<?php echo $seq;?>]" value="<?php echo $sd['no'];?>">
	<?php if($so['otype'] == 'C') {?>
		<!-- 요일체크 추가 -->
		<div>
			<span class="s_title">요일</span>
			<div class="check_wrap">
				<?php echo checkNewArray($_schedul_week_config, 'week'.$seq.'[]', $_week_list, 'weekDisabled();'); ?>
			</div>
		</div>
	<!-- //요일체크 추가 -->
	<?php } else {?>
		<input type="hidden" name="week<?php echo $seq;?>[]" value="<?php echo $_operate_otype_week_config[$so['otype']][$seq];?>">
	<?php } ?>
	<div>
		<span class="s_title"><?php echo $td_title; ?></span>
		<div class="before">
			<?php echo selectNewArray($_time, 'shour['.$seq.']', 2, ':: 시작시간 ::', $sd['shour'], '',$_all_time_readonly ); ?>
		</div>
		&nbsp;~ &nbsp;
		<div class="after">
			<?php echo selectNewArray($_time, 'ehour['.$seq.']', 2, ':: 종료시간 ::', $sd['ehour'], '',$_all_time_readonly); ?>
		</div>
		<label><input type="checkbox" name="all_time[<?php echo $seq;?>]" value="Y"<?php echo checked($sd['all_time'], 'Y'); ?> onclick="hourCtrl(1)"> 24시간</label>
	</div>
	<div id="break_use<?php echo $seq;?>" style="<?php echo $buse_display; ?>">
		브레이크 타임이 있으신가요?
		<label class="typeA"><input type="radio" name="buse[<?php echo $seq;?>]" value="N" <?php echo checked($sd['buse'], 'N');?> onclick="">없음</label>
		<label class="typeB"><input type="radio" name="buse[<?php echo $seq;?>]" value="Y" <?php echo checked($sd['buse'], 'Y');?> onclick="addOpt( '<?php echo $sd['no'];?>', {'append':'td_wrap<?php echo $seq;?>', 'repet':'option_row_', 'is_add':true, 'seq':<?php echo $seq;?>});">있음</label>
	</div>
	<div class="breaktime<?php echo $seq;?>">
		<?php include $engine_dir."/_manage/config/store_operate_break_frm.php"; ?>
    </div>
	<?php if($so['otype'] == 'C') {?>
		<div class="box_btn gray"><a onclick="delWeekOpt('<?php echo $sd['no']; ?>', <?php echo $seq; ?>);">- 요일 삭제</a></div>
	<?php } ?>
</div>
