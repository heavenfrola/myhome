<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업 관리
	' +----------------------------------------------------------------------------------------------+*/

	if($device == 'pc') $w .= " and device=''";
	elseif($device == 'mobile') $w .= " and device='mobile'";

	$use = $_GET['use'];
	if($use) {
		if($use == 'Y') $w .= " and `use`='Y'";
		else $w .= " and `use`!='Y'";
	}

	$frame=array();
	$sql="select * from {$tbl['popup_frame']} order by `no` desc";
	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$frame[$data['no']]=$data['title'];
	}

	$sql="select * from {$tbl['popup']} where 1 $w order by `no` desc";
	$sql_t="select count(*) from {$tbl['popup']} where 1 $w";
	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	if(!$row) $row=30;
	$block=20;

	$QueryString = '';
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row($sql_t);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$preview=true;


	$cnt = array('total' => 0, 'Y' => 0, 'N' => 0);
	$cnt_qry = "select `use`, count(*) as cnt from {$tbl['popup']} group by `use`";
	$cntres = $pdo->iterator($cnt_qry);
    foreach ($cntres as $tmp) {
		if($tmp['use'] != 'Y') $tmp['use'] = 'N';
		$cnt[$tmp['use']] += $tmp['cnt'];
		$cnt['total'] += $tmp['cnt'];
	}
	${'list_tab_active'.$use} = " class='active'";
	$list_tab_qry = preg_replace('/(\?|&)use=.?/', '', getURL());
	$qs_without_row = preg_replace('/(\?|&)row=[0-9]+/', '', getURL());

?>
<input type="hidden" name="body" value="<?=$_GET['body']?>">
<input type="hidden" name="row" value="<?=$row?>">
<div class="box_title first">
	<h2 class="title">팝업 관리</h2>
</div>
<div class="box_tab first">
	<ul>
		<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($cnt['total'])?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&use=Y" <?=$list_tab_activeY?>>사용<span class="cnt_use_Y"><?=number_format($cnt['Y'])?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&use=N" <?=$list_tab_activeN?>>미사용<span class="cnt_use_N"><?=number_format($cnt['N'])?></span></a></li>
	</ul>
</div>
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row,10,1)?>>10</option>
				<option value="20" <?=checked($row,20,1)?>>20</option>
				<option value="30" <?=checked($row,30,1)?>>30</option>
				<option value="50" <?=checked($row,50,1)?>>50</option>
				<option value="70" <?=checked($row,70,1)?>>70</option>
				<option value="100" <?=checked($row,100,1)?>>100</option>
			</select>
		</dd>
	</dl>
</div>
<table class="tbl_col">
	<caption class="hidden">팝업 리스트</caption>
	<colgroup>
		<col style="width:50px">
		<col>
		<col style="width:180px">
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">제목</th>
			<th scope="col">팝업스킨</th>
			<th scope="col">시작일</th>
			<th scope="col">종료일</th>
			<th scope="col">사용여부</th>
			<th scope="col">등록일</th>
			<th scope="col">미리보기</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?PHP
            foreach ($res as $data) {
				$data = array_map('stripslashes', $data);

				$use_on = ($data['use'] == 'Y') ? 'on' : '';
				$expired = ($data['finish_date'] != '0000-00-00 00:00:00' && strtotime($data['finish_date']) < $now) ? 'Y' : '';
				$finish_date = ($data['finish_date'] == '0000-00-00 00:00:00') ? '무제한' : subStr($data['finish_date'],0,10);
				$data['name'] = ($data['name']) ? $data['name'] : '팝업 ('.$frame[$data['frame']].')';
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><a href="?body=design@design_popup_register&no=<?=$data['no']?>"><?=$data['name']?></a></td>
			<td class="left"><?=$frame[$data['frame']]?></td>
			<td><?=subStr($data['start_date'],0,10)?></td>
			<td><?=$finish_date?></td>
			<td>
				<div class="switch <?=$use_on?>" onclick="toggleUsePopup(<?=$data['no']?>, $(this))" data-expired="<?=$expired?>"></div>
			</td>
			<td><?=date("Y-m-d",$data['reg_date'])?></a></td>
			<td><span class="box_btn_s"><input type="button" value="미리보기" onClick="openPopup(<?=$data['no']?>, <?=$data['w']?>, <?=$data['h']?>)"></span></td>
			<td><span class="box_btn_s"><input type="button" value="삭제" onClick="deletePopup(<?=$data['no']?>);"></span></td>
		</tr>
		<?php
				$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<div class="left_area">
		<span class="box_btn blue"><input type="button" value="추가" onclick="location.href='./?body=design@design_popup_register';"></span>
	</div>
	<?=$pg_res?>
</div>
<form name="popFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="design@design_popup_register.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
</form>

<script type="text/javascript">
	function deletePopup(no){
		f=document.popFrm;
		f.no.value=no;
		if(confirm('삭제하시겠습니까?')){
			f.exec.value='delete';
			f.submit();
		}
	}

	function openPopup(no, width, height) {
		window.open('?body=design@design_popup_preview.frm&no='+no+'&preview=Y', 'poppreview', 'status=no, scrollbars=no, height='+height+'px, width='+width+'px');
	}

	function toggleUsePopup(no, o) {
		$.post('?body=design@design_popup_register.exe', {'exec':'toggle', 'no':no, 'cnt_qry':"<?=base64_encode($cnt_qry)?>"}, function(r) {
			if(r.changed == 'Y') {
				o.addClass('on');
				if(o.attr('data-expired') == 'Y') {
					window.alert('종료일이 지난 팝업을 사용함으로 설정하셨습니다.');
				}
			} else {
				o.removeClass('on');
			}
			$('.cnt_use_Y').html(r.Y);
			$('.cnt_use_N').html(r.N);
		});
	}
</script>