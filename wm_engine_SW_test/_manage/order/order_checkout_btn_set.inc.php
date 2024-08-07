<div>
	<!-- 미입금 -->
	<?if(in_array2(array(1), $_prd_stats)) {?>
	<div class="list_info">
		<p>미입금 상태에서는 구매자만 직접 취소할 수 있으며, +2 영업일 내 입금이 되지 않을 경우 자동 취소됩니다.</p>
	</div>
	<?}?>
	<!-- // 미입금 -->

	<div class="btn_npay">
		<!-- 취소 -->
		<?if(in_array2(array(2, 3, 12), $_prd_stats)) {?>
		<div>
			<span class="box_btn_s"><input type="button" value="취소/환불" onClick="jsPrdStat('13');"></span>
		</div>
		<?}?>
		<!-- //취소 -->

		<!-- 반품 -->
		<div>
			<?if(in_array2(array(4, 5), $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="반품접수" onClick="jsPrdStat('16');"></span>
			<?}?>
			<?if(in_array2(array(16, 22, 23), $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="반품승인" onClick="jsPrdStat('17');"></span>
			<?}?>
			<?if(in_array(16, $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="반품거부" onClick="jsPrdStat(27);"></span>
			<span class="box_btn_s"><input type="button" value="반품보류" onClick="jsPrdStat(171);"></span>
                <span class="box_btn_s"><input type="button" value="반품보류해제" onClick="jsPrdStat(172);"></span>
			<?}?>
		</div>
		<!-- //반품 -->

		<!-- 교환 -->
		<div>
			<?if(in_array2(array(18, 24), $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="교환수거완료" onClick="jsPrdStat(25);"></span>
			<?}?>
			<?if(in_array2(array(24, 25), $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="교환재배송" onClick="jsPrdStat(26);"></span>
			<?}?>
			<?if(in_array(18, $_prd_stats)) {?>
			<span class="box_btn_s"><input type="button" value="교환거부" onClick="jsPrdStat(28);"></span>
			<span class="box_btn_s"><input type="button" value="교환보류" onClick="jsPrdStat(191);"></span>
                <span class="box_btn_s"><input type="button" value="교환보류해제" onClick="jsPrdStat(192);"></span>
			<?}?>
		</div>
		<!-- //교환 -->

		<!-- 기타 -->
		<?if(in_array2(array(2, 3), $_prd_stats)) {?>
		<div>
			<span class="box_btn_s"><input type="button" value="발송지연" onclick="jsPrdStat(401)"></span>
		</div>
		<?}?>
		<!-- //기타 -->
	</div>

	<div style='margin: 5px 0;'>
		<!--입금완료-->
		<?if(in_array(20, $_prd_stats)) {?>
        <span class="box_btn_s blue"><input type="button" value="재고확인완료" onClick="jsPrdStat(-1);"></span>
		<?}?>
		<!-- 배송-->
		<?if(in_array2(array(2, 3, 12), $_prd_stats)) {?>
		<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[3]?>" onClick="jsPrdStat(3);"></span>
		<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[4]?>" onClick="jsPrdStat(4);"></span>
		<?}?>
	</div>
</div>