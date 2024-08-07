<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  메인페이지
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir."/_manage/main/main_box.php";
	require_once 'att.inc.php';

?>
<div id="intra">
	<?
		if($cfg[intra_day_check] == "Y") {
			$today=date("Y-m-d", $now);

			// 다른 멤버 현황
			$_attend_mem=$pdo->row("select count(*) from `$tbl[intra_day_check]` where `date`='$today'");
			$_leave_mem=$pdo->row("select count(*) from `$tbl[intra_day_check]` where `date`='$today' and `etime` != 0");
			$_attend_mem=$_attend_mem ? $_attend_mem : 0;
			$_leave_mem=$_leave_mem ? $_leave_mem : 0;
	?>
	<div class="box_title first">
		<h2 class="title">근태현황 [<?=date("Y/m/d H:i:s", $now)?>]</h2>
	</div>
	<div class="box_bottom top_line check left">
		<div class="work">
			<a href="javascript:day_check(1);" class="in">출근</a>
			<a href="javascript:day_check(2);" class="out">퇴근</a>
		</div>

		<div class="list">
			<ul>
				<li>출근시간 : <b><?=$_stime?></b></li>
				<li>퇴근시간 : <b><?=$_etime?></b></li>
				<li>
					현황 :
					출근 <a href="javascript:;" onclick="$('#attenMemdiv').show();"><strong class="p_color"><u><?=$_attend_mem?></u></strong></a>명 &nbsp;
					퇴근 <a href="javascript:;" onclick="$('#attenMemdiv').show();"><strong class="p_color2"><u><?=$_leave_mem?></u></strong></a>명
				</li>
			</ul>
			<div id="attenMemdiv">
				<span class="box_btn gray"><input type="button" value="닫기" onclick="$('#attenMemdiv').hide();"></span>
				<table class="tbl_mini full">
					<thead>
						<tr>
							<th scope="col">No</th>
							<th scope="col">이름</th>
							<th scope="col">출근</th>
							<th scope="col">퇴근</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$idx=1;
							$sql = $pdo->iterator("select * from `$tbl[intra_day_check]` where `date`='$today' order by `stime`");
                            foreach ($sql as $data) {
								$mname=$pdo->row("select `name` from `$tbl[mng]` where `no`='$data[member_no]' limit 1");
						?>
						<tr>
							<td><?=$idx?></td>
							<td><?=$mname?></td>
							<td><?=date("H:i", $data[stime]);?></td>
							<td><?=$data[etime] ? date("H:i", $data[etime]) : "근무중";?></td>
						</tr>
						<?
								$idx++;
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?}?>
	<div class="board">
		<?php
			$cfg[intra_main_board1]=$cfg[intra_main_board1] ? $cfg[intra_main_board1] : "notice";
			$cfg[intra_main_board2]=$cfg[intra_main_board2] ? $cfg[intra_main_board2] : "community";
			$cfg[intra_main_board3]=$cfg[intra_main_board3] ? $cfg[intra_main_board3] : "_intra_recent_comment_";
			for($ii=1; $ii<=3; $ii++){
				if($cfg["intra_main_board".$ii]){
					$bname=$pdo->row("select `title` from `$tbl[intra_board_config]` where `db`='".$cfg["intra_main_board".$ii]."' limit 1");
					$_more_btn="<a href=\"./?body=intra@board&db=".$cfg["intra_main_board".$ii]."\">더보기</a>";
					if($ii == 3){
						if($cfg[intra_main_board3] == "_intra_recent_comment_"){
							$bname="최근 댓글";
						}
					}
					$_more_btn="&nbsp;";
			?>
			<div class="box box<?=$ii?>">
				<div class="box_title">
					<h2 class="title"><?=$bname?></h2>
					<span class="btns"><?=$_more_btn?></span>
				</div>
				<div class="box_bottom top_line left">
					<ul class="list_msg left">
						<?
							if($ii == 3 && $cfg[intra_main_board3] == "_intra_recent_comment_"){
								$bsql = $pdo->iterator("select `db`, `ref` as `no`, `content` as `title` from `$tbl[intra_comment]` order by `no` desc limit 10");
							}else{
								$bsql = $pdo->iterator("select `no`, `title`, `db` from `$tbl[intra_board]` where `db`='".$cfg["intra_main_board".$ii]."' order by `no` desc limit 5");
							}
                            foreach ($bsql as $data) {
								$title=cutStr($data[title], 50);
						?>
						<li><a href="./?body=intra@board&db=<?=$data[db]?>&mode=view&no=<?=$data[no]?>"><?=$title?></a></li>
						<?}?>
					</ul>
				</div>

			</div>
			<?
				}else echo "<div class=\"box\"></div>;";
			?>
		<?}?>
	</div>
</div>
<div id="intra_calendar"></div>

<script language="JavaScript">
	function getCalContent(addq) {
		if(!addq) addq='';
		$.get('./?body=intra@calendar_inc.exe&db=main_schedule'+addq, function(r) {
			$('#intra_calendar').html(r);
		});
	}
	getCalContent();
</script>