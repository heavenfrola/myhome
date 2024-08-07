<?PHP

	include_once $engine_dir."/_manage/promotion/attend_setup.php";

	$attd=get_info($tbl[attend], "no", $no);

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}
	$QueryString.="&listURL=".$listURL;

	$sql="select * from `$tbl[attend_member]` where 1 and `ano`='$no' $w order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['attend_member']} where 1 and ano='$no' $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	if($no){
		$data=get_info($tbl[attend], "no", $no);
	}

?>
<table class="tbl_col">
	<caption class="hidden">출석자명단</caption>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">이벤트명</th>
			<th scope="col">회원아이디</th>
			<th scope="col">회원이름</th>
			<th scope="col">출석일수</th>
			<th scope="col">마지막출석일</th>
			<th scope="col">취소</th>
		</tr>
	</thead>
		<?php
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				$del_link="./?body=promotion@attend.exe&exec=mem_delete&no=$data[no]";
				$att_mem = $pdo->assoc("select `no`,`member_id`,`name` from `$tbl[member]` where `no`='$data[member_no]'");
				$mem_link="<a href=\"javascript:\" onclick=\"viewMember('$att_mem[no]','$att_mem[member_id]')\">";
				if(!$att_mem[no]){
					$att_mem[member_id]="<s>".$att_mem[member_id]."</s>";
					$att_mem[name]="탈퇴회원";
					$mem_link="<a href=\"javascript:\" onclick=\"alert('탈퇴한 회원입니다')\">";
				}
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><?=$attd[title]?></td>
			<td><?=$mem_link?><?=$att_mem[member_id]?></a></td>
			<td><?=$att_mem[name]?></td>
			<td><?=$data[total]?></td>
			<td><?=$data[last_date]?></td>
			<td><a href="<?=$del_link?>" target="hidden<?=$now?>" onclick="return confirm('해당 출석자료를 취소하시겠습니까?');">취소</a></td>
		</tr>
		<?
				$idx--;
			}
		?>
</table>
<div class="box_middle">
	<?if($attd[milage] && $attd[charge] != "Y") {?>
	<span class="box_btn_s blue"><input type="submit" value="적립금지급" onclick="attendMilage()"></span>
	<?}else if($attd[charge] == "Y") {?>
	<span class=\"sred\">적립금 <?=date("Y-m-d H:i", $attd[charge_date])?> 지급완료</span>
	<?}?>
</div>
<div class="box_bottom">
	<?=$pg_res?>
	<?if($listURL){?>
	<span class="box_btn left_area"><input type="submit" value="전체목록" onclick="location.href='<?=$listURL?>'"></span>
	<?}?>
</div>

<script type="text/javascript">
	function attendCk(f){
		if(!checkBlank(f.title, "이벤트명을 입력해주세요.")) return false;
		if(!checkBlank(f.sdate, "시작일을 입력해주세요.")) return false;
		if(!checkBlank(f.fdate, "종료일을 입력해주세요.")) return false;
	}
	function attendMilage(){
		if(!confirm('해당 출석자들에게 적립금 <?=number_format($attd[milage])?>원을 지급하시겠습니까?')) return;
		window.frames[hid_frame].location.href='./?body=promotion@attend.exe&no=<?=$attd[no]?>&exec=charge&no=<?=$attd[no]?>';
	}
</script>