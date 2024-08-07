<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  일정 등록/관리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	$wdate=$wdate ? $wdate : date("Y-m-d", $now);

	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);

?>
<form name="" method="get" action="?" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">기간별검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">기간별검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">기간별검색</td>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				<span class="box_btn_s blue"><input type="submit" value="조회"></span>
			</td>
		</tr>
	</table>
</form>
<form name="schFrm" method="post" action="?" target="hidden<?=$now?>" onsubmit="return schCk(this);">
	<input type="hidden" name="body" value="intra@schedule.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="no" value="">
	<input type="hidden" name="font_color" value="">
	<div class="box_title">
		<h2 class="title">일정 등록하기</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">일정 등록하기</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">일정등록</th>
			<td>
				<input type="text" name="wdate" value="<?=$wdate?>" size="10" class="input datepicker">
				<input type="checkbox" name="alarm" value="Y" id="alarm_1">
				<label for="alarm_1" class="p_cursor">알람</label>&nbsp;
				<input type="text" name="content" id="content_id" class="input" size="50" maxlength="50">
				<span class="box_btn_s blue"><input type="submit" name="schBtn" value="등록하기"></span>
				<div class="explain">알람을 설정하시면 해당 날짜에 인트라넷 접속시 작동됩니다</div>
			</td>
		</tr>
	</table>
</form>
<div class="box_title">
	<h2 class="title">등록된 일정</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">등록된 일정</caption>
	<colgroup>
		<col style="width:110px">
		<col>
		<col style="width:80px">
		<col style="width:110px">
		<col style="width:70px">
		<col style="width:70px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">날짜</th>
			<th scope="col">일정내용</th>
			<th scope="col">알람</th>
			<th scope="col">등록일</th>
			<th scope="col">수정</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($_GET as $key=>$val) {
				if($key!="page") $QueryString.="&".$key."=".$val;
			}
			if($start_date && $finish_date){
				$w=" and `date`>='$start_date' and `date`<='$finish_date'";
			}
			$sql="select * from `$tbl[intra_schedule]` where 1 $w order by `date` desc";
			include $engine_dir."/_engine/include/paging.php";

            $page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec = $pdo->row(str_replace("select * from", "select count(*) from", $sql));
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString($QueryString);
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult[LimitQuery];

			$pg_res=$PagingResult[PageLink];
			$res = $pdo->iterator($sql);
			$idx=$NumTotalRec-($row*($page-1));
            foreach ($res as $data) {
			$_style=$data[font_color] ? " style=\"color:$data[font_color];\"" : "";
		?>
		<tr class="tcol2" onMouseOver="this.style.backgroundColor='#ffffcc'" onMouseOut="this.style.backgroundColor=''">
			<td><div id="schDate<?=$data[no]?>"><?=$data['date']?></div></td>
			<td class="left"><div id="schCon<?=$data[no]?>"<?=$_style?>><?=$data[content]?></div></td>
			<td><?=($data[alarm] == "Y") ? "○" : "-";?></td>
			<td><?=date("Y-m-d", $data[reg_date])?></td>
			<td><span class="box_btn_s"><input type="button" value="수정" onclick="modSchd(<?=$data[no]?>,'<?=$data[alarm]?>','<?=$data[font_color]?>');"></span></td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="delSchd(<?=$data[no]?>);"></span></td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom"><?=$pg_res?></div>

<script type="text/javascript">
	function schCk(f){
		if(!checkBlank(f.wdate, '일정 날짜를 입력해주세요.')) return false;
		if(!checkBlank(f.content, '일정 내용을 입력해주세요.')) return false;
	}
	f=document.schFrm;
	function modSchd(no, alarm, fcolor){
		f.no.value=no;
		f.schBtn.value='수정하기';
		w1=document.getElementById('schDate'+no);
		w2=document.getElementById('schCon'+no);
		f.wdate.value=w1.innerHTML;
		f.content.value=w2.innerHTML;
		if(alarm == 'Y'){
			f.alarm.checked=true;
		}else{
			f.alarm.checked=false;
		}
		if(fcolor){
			f.font_color.value=fcolor;
			document.getElementById('content_id').style.color=fcolor;
			document.getElementById('color_id').style.backgroundColor=fcolor;
		}else{
			f.font_color.value='';
			document.getElementById('content_id').style.color='';
			document.getElementById('color_id').style.backgroundColor='#FFFFFF';
		}
		f.content.focus();
	}
	function delSchd(no){
		if(!confirm('삭제하시겠습니까?')) return;
		f.no.value=no;
		f.exec.value='delete';
		f.submit();
	}
</script>