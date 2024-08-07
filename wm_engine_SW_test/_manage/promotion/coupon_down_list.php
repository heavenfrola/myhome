<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 발급내역
	' +----------------------------------------------------------------------------------------------+*/

	$down_start = addslashes($_GET['down_start']);
	$down_finish = addslashes($_GET['down_finish']);
	$use_start = addslashes($_GET['use_start']);
	$use_finish = addslashes($_GET['use_finish']);
	$is_type = addslashes($_GET['is_type']);
	$search_str = addslashes($_GET['search_str']);
	$sFrom = addslashes($_GET['sFrom']);
	$cuse = addslashes($_GET['cuse']);
	$dFrom = addslashes($_GET['dFrom']);
	$all_use = addslashes($_GET['all_use']);
	$all_down = addslashes($_GET['all_down']);

	$all_use = ($smode == "cp_list") ? "Y" : $all_use;
	$all_down = ($smode == "cp_list") ? "Y" : $all_down;
	if(!$down_start || !$down_finish) {
		$down_start = date('Y-m-d', strtotime('-2 week'));
		$down_finish = date('Y-m-d');
	}
	if(!$use_start || !$use_finish) {
		$all_use = "Y";
		$use_start = date('Y-m-d', strtotime('-2 week'));
		$use_finish = date('Y-m-d');
	}

	$_use_start = strtotime($use_start);
	$_use_finish = strtotime($use_finish)+86399;
	$_down_start = strtotime($down_start);
	$_down_finish = strtotime($down_finish)+86399;

	if(!$is_type) $is_type="A";

	if(!$all_use) {
		$add_q.=" and a.`use_date` >= '$_use_start'";
		$add_q.=" and a.`use_date` <= '$_use_finish'";
	}

	if (!$all_down && $is_type != 'B') {
		$add_q.=" and a.`down_date` >= '$_down_start'";
		$add_q.=" and a.`down_date` <= '$_down_finish'";
	}

	$add_q.= " and a.`is_type`='$is_type'";
	$is_type_title=($is_type == "A") ? "온라인쿠폰 발급내역" : "시리얼쿠폰 사용내역";

	if($sFrom && $search_str) $add_q .= " and a.`$sFrom` like '%{$search_str}%'";
	if($dFrom && $dFrom != 'Z' && $dFrom != 'H' && $dFrom != 'I') {
		$add_q .= " and b.`down_type` = '$dFrom' and b.`is_birth` != 'Y' ";
	} else if ($dFrom == 'H') {
		$add_q .= " and b.`is_birth` = 'Y' and b.`down_type` != 'B'";
	} else if ($dFrom == 'I') {
		$add_q .= " and b.`is_birth` = 'Y' and b.`down_type` = 'B'";
	}
	if($smode && $mno) $add_q .= " and a.`member_no`='$mno' and a.`member_id`='$mid'";

	$dmsen = $pdo->iterator("select if(ono='', 'N', 'Y') as is_use, count(*) as cnt from  wm_coupon_download a inner join wm_coupon b  on a.cno = b.no where 1 $add_q group by is_use order by null");
    foreach ($dmsen as $cnt) {
		if($cnt['is_use'] == 'N') $cuse_n = $cnt['cnt'];
		if($cnt['is_use'] == 'Y') $cuse_y = $cnt['cnt'];
		$total = $cuse_n+$cuse_y;
	}
	$disabled = '';
	if($is_type == "B") $cuse = "Y";
	if($cuse){
		if($cuse == "Y") $add_q .= " and a.`ono` != ''";
		if ($cuse == "N") {
			$add_q .= " and a.`ono`=''";
			$disabled = "disabled";
		}
	}

	$sql="select a.*, b.udate_limit, c.cell, c.sms from `$tbl[coupon_download]` a inner join `$tbl[coupon]` b  on a.cno = b.no left join `$tbl[member]` c on a.member_no=c.no where 1 $add_q order by a.`no` desc";

	$QueryString = '';
	foreach($_GET as $key=>$val) {
		if($key != 'page' && $key != 'cuse') {
			$list_tab_qry .= ($list_tab_qry) ? '&' : '?';
			$list_tab_qry .= $key.'='.urlencode($val);
		}
	}
	$QueryString = makeQueryString('page');
	$xls_query = makeQueryString('page', 'body');
	${'list_tab_active'.$cuse} = 'class="active"';

	if($body == 'promotion@coupon_excel.exe') return;

	include $engine_dir."/_engine/include/paging.php";
	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;

	$NumTotalRec = $pdo->row("
        select count(*)
            from {$tbl['coupon_download']} a
            inner join {$tbl['coupon']} b on a.cno=b.no
            left join {$tbl['member']} c on a.member_no=c.no
        where 1 $add_q
    ");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$rSql = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$box_class = ($smode != 'cp_list') ? "box_tab" : "box_tab first";
	if(!$smode || !$mno) {

?>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="is_type" value="<?=$is_type?>">
	<div class="box_title first">
		<h2 class="title"><?=$is_type_title?></h2>
	</div>
		<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<select name="sFrom">
							<option value="name" <?=checked($sFrom,"name",1)?>>쿠폰명</option>
							<option value="cno" <?=checked($sFrom,"cno",1)?>>쿠폰번호</option>
							<? if($is_type == "B"){ ?>
							<option value="auth_code" <?=checked($sFrom,"auth_code",1)?>>인증코드</option>
							<? } ?>
							<option value="member_name" <?=checked($sFrom,"member_name",1)?>>회원이름</option>
							<option value="member_id" <?=checked($sFrom,"member_id",1)?>>회원아이디</option>
							<option value="ono" <?=checked($sFrom,"ono",1)?>>주문번호</option>
						</select>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
	<table class="tbl_search">
		<caption class="hidden">온라인쿠폰 발급내역</caption>
		<colgroup>
			<col style="width:15%">
			<col>
			<?php if ($is_type == "A"){ ?>
			<col style="width:15%">
			<col>
			<?php }?>
		</colgroup>
		<? if($is_type == "A"){ ?>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" class="disable" onClick="useDateCheck(this.form)" name="cuse" value="" <?=checked($_GET['cuse'],"")?>> 전체</label>
				<label class="p_cursor"><input type="radio" class="disable" onClick="useDateCheck(this.form)" name="cuse" value="Y" <?=checked($_GET['cuse'],"Y")?>> 사용</label>
				<label class="p_cursor"><input type="radio" class="disable" onClick="useDateCheck(this.form)" name="cuse" value="N" <?=checked($_GET['cuse'],"N")?>> 미사용</label>
			</td>
			<th scope="row">쿠폰발급/형태</th>
			<td>
				<select name="dFrom">
					<option value="" <?=checked($dFrom,"",1)?>>전체</option>
					<option value="A" <?=checked($dFrom,"A",1)?>>다운로드 - 전체회원</option>
					<option value="B" <?=checked($dFrom,"B",1)?>>다운로드 - 회원등급별</option>
					<option value="C" <?=checked($dFrom,"C",1)?>>회원가입 시 자동발급</option>
					<option value="E" <?=checked($dFrom,"E",1)?>>회원가입 시 자동발급(앱 전용)</option>
					<option value="G" <?=checked($dFrom,"G",1)?>>첫구매 완료 시 자동발급</option>
					<option value="F" <?=checked($dFrom,"F",1)?>>구매 완료 시 자동발급</option>
					<option value="D" <?=checked($dFrom,"D",1)?>>수동발급</option>
					<option value="H" <?=checked($dFrom,"H",1)?>>생일쿠폰 - 전체회원</option>
					<option value="I" <?=checked($dFrom,"I",1)?>>생일쿠폰 - 회원등급별</option>
				</select>
			</td>
		</tr>
		<? } ?>
		<tr>
		<?php if ($is_type == "A"){ ?>
			<th scope="row">발급일자</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="all_down" value="Y" <?=checked($all_down,"Y")?> onClick="searchDate2(this.form)"> 전체 기간</label>
				<input type="text" name="down_start" value="<?=$down_start?>" class="input datepicker" size="10"> ~ <input type="text" name="down_finish" value="<?=$down_finish?>" class="input datepicker" size="10">
			</td>
		<?php }?>
			<th scope="row">사용일자</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="all_use" <?=$disabled?> class="disable" value="Y" <?=checked($all_use,"Y")?> onClick="searchDate2(this.form)"> 전체 기간</label>
				<input type="text" name="use_start" value="<?=$use_start?>" <?=$disabled?> class="input datepicker" size="10"> ~ <input type="text" name="use_finish" value="<?=$use_finish?>" <?=$disabled?> class="input datepicker" size="10">
			</td>
		</tr>
	</table>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&is_type=<?=$is_type?>'"></span>
	</div>
</form>
<?}?>
<form name="cdFrm" action="./" method="post" target="hidden<?=$now?>" onSubmit="return checkFrm()">
<?if($is_type == "A") {?>
	<div class='<?=$box_class?>'>
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($total)?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&cuse=Y" <?=$list_tab_activeY?>>사용<span><?=number_format($cuse_y)?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&cuse=N" <?=$list_tab_activeN?>>미사용<span><?=number_format($cuse_n)?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=promotion@coupon_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
<?} else {?>
  	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 내역이 검색되었습니다.
		<span class="box_btn_s btns icon excel"><a href="./?body=promotion@coupon_excel.exe<?=$xls_query?>">엑셀다운</a></span>
	</div>
<?}?>
	<div class="box_sort">
		<dl class="list">
			<a class="left p_color">
				사용날짜를 클릭하시면 주문내역을 확인하실 수 있습니다.
			</a>
		</dl>
	</div>
	<table class="tbl_col">
		<caption class="hidden">온라인쿠폰 발급내역 리스트</caption>
		<colgroup>
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">회원</th>
				<th scope="col">쿠폰명</th>
				<th scope="col">할인금액(율)</th>
				<th scope="col">사용제한</th>
				<th scope="col">최대할인</th>
				<th scope="col"><? if($is_type == "B") echo "인증코드"; else echo "발급일"; ?></th>
				<th scope="col">만료일</th>
				<th scope="col">사용일</th>
				<th scope="col">발급취소</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($rSql as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					$u_date=($data[ono]) ? "<span class=\"box_btn_s\"><a href=\"javascript:viewOrder('$data[ono]');\">".date("Y-m-d",$data[use_date])."</a></span>" : "<span class=\"p_color2\">미사용</span>";
					if ($data['udate_type'] == 1) {
						$e_date = "무제한";
					} else if ($data['udate_type'] == 2) {
					    $e_date = $data['ufinish_date'];
					} else {
					  $e_date =  date("Y-m-d", ($data['down_date'] + ($data['udate_limit']*86400)));
					}

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['member_name'] = strMask($data['member_name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }
			?>
			<tr>
				<td><?=$idx?></td>
				<td><?=$data[member_name]?>(<a href="javascript:;" onclick="viewMember('<?=$data[member_no]?>', '<?=$data[member_id]?>');"><?=$data['member_id_v']?></a>)</td>
				<td><?=cutStr(stripslashes($data[name]),30)?></td>
				<td><?=number_format($data[sale_prc]).$sale_type?></td>
				<td><?=number_format($data[prc_limit])?> <?=$cfg['currency_type']?></td>
				<td><?=number_format($data[sale_limit])?></td>
				<td><? if($is_type == "B") echo $data[auth_code]; else echo date("Y-m-d",$data[down_date]); ?></td>
				<td><?=$e_date?></td>
				<td><?=$u_date?></td>
				<td>
					<?if($data['ono']){?>
					<span class="box_btn_s"><a href="javascript:;" onClick="restoreCdown(<?=$data['no']?>)">복구</a></span>
					<?} else{?>
					<span class="box_btn_s"><a href="javascript:;" onClick="deleteCdown(<?=$data['no']?>)">취소</a></span>
					<?}?>
				</td>
			</tr>
			<?
					$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom"><?=$pageRes?></div>
	<input type="hidden" name="body" value="promotion@coupon.exe">
	<input type="hidden" name="no">
	<input type="hidden" name="exec" value="down_delete">
</form>

<script language="JavaScript">
	$(document).ready(function() {
		searchDate2(document.prdFrm);
	});

	function deleteCdown(no){
		if(!confirm('선택하신 쿠폰의 발급을 취소하시겠습니까?')) return;
		f=document.cdFrm;
		f.no.value=no;
		f.submit();
	}

	function restoreCdown(no) {
		if(confirm('선택하신 쿠폰을 재사용 가능하도록 복구하시겠습니까?')) {
			$.post('./index.php', {'body':'promotion@coupon.exe', 'exec':'restore', 'no':no}, function(r) {
				location.reload();
			});
		}
	}

	function searchDate2(f) {
		<?if($_GET[is_type]=='A'){?>
			var fields = new Array('down', 'use');
		<?}else{?>
			var fields = new Array('use');
		<?}?>
		for(var idx in fields) {
			var chk = f.elements['all_'+fields[idx]];
			var date1 = f.elements[fields[idx]+'_start'];
			var date2 = f.elements[fields[idx]+'_finish'];

			if(chk.checked == true) {
				date1.disabled = true;
				date2.disabled = true;
				date1.style.backgroundColor='#eee';
				date2.style.backgroundColor='#eee';
			} else {
				date1.disabled = false;
				date2.disabled = false;
				date1.style.backgroundColor='';
				date2.style.backgroundColor='';
			}
		}
	}

	function useDateCheck(f) {
		var date1 = f.elements['use_start'];
		var date2 = f.elements['use_finish'];
		if ($(':checked[name=cuse]').val() == 'N') {
			f.all_use.disabled = true;
			f.use_start.disabled = true;
			f.use_finish.disabled = true;
		} else {
			f.all_use.disabled = false;
			f.use_start.disabled = false;
			f.use_finish.disabled = false;
		}

	}

	$('.disable', document.getElementById('prdFrm')).click(function() {
		useDateCheck(this.form);
	});

</script>