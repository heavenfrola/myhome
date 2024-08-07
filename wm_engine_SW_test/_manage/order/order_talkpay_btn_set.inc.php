<div>
    <div class="btn_npay">
		<div>
    		<?php if(in_array2(array(1, 2, 3), $_prd_stats)) { ?>
			<span class="box_btn_s"><input type="button" value="환불" onClick="jsPrdStat('15');"></span>
            <?php } ?>

    		<?php if(in_array2(array(12, 14), $_prd_stats)) { ?>
			<span class="box_btn_s"><input type="button" value="취소 승인" onClick="jsPrdStat('131');"></span>
            <?php } ?>

            <?php if(in_array2(array(4, 5), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="반품 요청" onClick="jsPrdStat('16');"></span>
            <?php } ?>

            <?php if(in_array2(array(16, 22, 23), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="반품 승인" onClick="jsPrdStat('17');"></span>
            <?php } ?>

            <?php if(in_array2(array(16, 22, 23), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="반품 보류" onClick="jsPrdStat('171');"></span>
            <?php } ?>

            <?php if(in_array2(array(16, 22, 23, 28), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="반품 거부" onClick="jsPrdStat('27');"></span>
            <?php } ?>

            <?php if(in_array2(array(12, 14), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="취소 불가 발송 처리" onClick="jsPrdStat('132');"></span>
            <?php } ?>

            <?php if(in_array2(array(4, 5), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="교환 요청" onClick="jsPrdStat('18');"></span>
            <?php } ?>

            <?php if(in_array2(array(18, 24, 25, 27), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="교환 재배송" onClick="jsPrdStat('26');"></span>
            <?php } ?>

            <?php if(in_array2(array(18, 24, 25), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="교환 보류" onClick="jsPrdStat('191');"></span>
            <?php } ?>

            <?php if(in_array2(array(18, 24, 25, 27), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="교환 거부" onClick="jsPrdStat('28');"></span>
            <?php } ?>
		</div>
        <div>
    		<?php if(in_array2(array(1, 2, 3), $_prd_stats)) { ?>
            <span class="box_btn_s"><input type="button" value="배송지연" onclick="jsPrdStat(401)"></span>
    		<?php } ?>
        </div>
    </div>

    <div style='margin: 5px 0;'>
        <p>
    		<?php if(in_array2(array(2, 3), $_prd_stats)) { ?>
            <span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[3]?>" onClick="jsPrdStat(3);"></span>
            <span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[4]?>" onClick="jsPrdStat(4);"></span>
            <?php } ?>
        </p>
    </div>
</div>