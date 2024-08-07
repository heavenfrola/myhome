<?php
/**
 * 영업 시간 설정 페이지
 */

$so = $pdo->assoc("select * from {$tbl['store_operate']} where sno=:sno", array(':sno'=>$no));
if(!$so) $so['otype'] = 'N';
?>
<div class="store_time">
    <div class="table">
        <div class="tr" id="operate_tr">
            <div class="th">영업주기</div>
            <div class="td">
                <div>
                    <input type="hidden" name="sono" value="<?php echo $so['no'];?>">
                    <label class="typeA"><input type="radio" name="otype" value="" <?php echo checked($so['otype'], 'N');?> onclick="operateForm(this, {'append':'operate_list'});">없음</label>
                    <label class="typeA"><input type="radio" name="otype" value="A" <?php echo checked($so['otype'], 'A');?> onclick="operateForm(this, {'append':'operate_list'});">매일 같음</label>
                    <label class="typeB"><input type="radio" name="otype" value="B" <?php echo checked($so['otype'], 'B');?> onclick="operateForm(this, {'append':'operate_list'});">평일/주말 다름</label>
                 <label class="typeC"><input type="radio" name="otype" value="C" <?php echo checked($so['otype'], 'C');?> onclick="operateForm(this, {'append':'operate_list'});">요일선택</label>
                </div>
            </div>
        </div>

        <div class="tr" id="operate_list">
            <?php include $engine_dir."/_manage/config/store_operate_register_frm.php"; ?>
        </div>
    </div>
</div>
