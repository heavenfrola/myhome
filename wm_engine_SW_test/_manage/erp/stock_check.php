<?PHP

	// 카테고리 검색
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
	}
	$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($sql as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ( $_GET[$cl] == $cate['no'] ) ? 'selected' : '';
		${'item_1_'.$cate['level']} .= "\n\t<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
	}

	// 발송대기상태
	$stat = $_GET['stat'];
	for($key = 1; $key <= 5; $key++) {
		if(is_array($stat)) $checked = in_array($key, $stat) ? 'checked' : '';
		$stats .= "<label class=\"p_cursor\"><input type=\"checkbox\" name=\"stat[]\" value=\"$key\" $checked> $_order_stat[$key]\n</label>";
	}

	// 품절방식
	$force = $_GET['force'];
	if(count($force) < 1) $force = array('N', 'Y', 'L');
	$_forces = $_erp_force_stat;
	foreach($_forces as $key => $val) {
		$checked = in_array($key, $force) ? 'checked' : '';
		$forces .= "<label class=\"p_cursor\"><input type=\"checkbox\" name=\"force[]\" value=\"$key\" $checked> $val\n</label>";
	}
	$force = preg_replace('/([A-Z])/', "'$1'", implode(',', $force));

	$big = numberOnly($_GET['big']);
	$mid = numberOnly($_GET['mid']);
	$small = numberOnly($_GET['small']);

	$w = '';
	if($mid) $w .= " and p.mid='$mid'";
	if($small) $w .= " and p.small='$small'";
	$w .= " and c.force_soldout in ($force)";

	if(is_array($stat) && count($stat) > 0) {
		$stat = implode(',', $stat);
		$join .= " left join (select no, complex_no from $tbl[order_product] where stat in ($stat)) o on o.complex_no = c.complex_no ";
		$w .= " group by c.complex_no";
		$f .= " , count(o.no) as stock_ord ";
	}

?>
<form id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">대상 상품 검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">대상 상품 검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">분류</th>
			<td>
				<select name="big" onchange="chgCateInfinite(this, 2, '')">
					<option value="">::대분류::</option>
					<?=$item_1_1?>
				</select>
				<select name="mid" onchange="chgCateInfinite(this, 3, '')">
					<option value="">::중분류::</option>
					<?=$item_1_2?>
				</select>
				<select name="small"  onchange="chgCateInfinite(this, 4, '')">
					<option value="">::소분류::</option>
					<?=$item_1_3?>
				</select>
				<?if($cfg['max_cate_depth'] >= 4) {?>
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">품절방식</th>
			<td>
				<?=$forces?>
			</td>
		</tr>
		<tr>
			<th scope="row">발송대기 제외</th>
			<td>
				<?=$stats?>
				<ul class="list_msg">
					<li>상품 발송전에 재고가 차감되도록 설정 된 경우, 전산재고와 창고재고에 차이가 있을 수 있으므로 이를 보정합니다.</li>
					<li>
						<?=$_order_stat[2]?> 시 재고가 차감되도록 설정된 경우 <?=$_order_stat[2]?>/<?=$_order_stat[3]?> 상태에서는 전산재고는 차감되어있으나 창고에는 해당 주문에 대한 재고가 존재하게 됩니다.<br>선택 시 해당 주문 수량만큼 기본재고에 반영되어, 정확한 재고조사가 가능합니다.
					</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<form method="post" target="hidden<?=$now?>" onsubmit="return stockPrdSearch(this)">
	<input type="hidden" name="body" value="erp@stock_check.exe">
	<input type="hidden" name="exec" value="search">
	<div class="box_title">
		<h2 class="title">바코드 처리</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>본 기능은 반드시 <span class="p_color2">카테고리 내 전체상품의 재고를 다시 파악할 때만</span> 사용해 주십시오.</li>
			<li>일부 상품만 변경하실 때에는 <a href="?body=erp@stock_adjust">재고 조정</a> 기능을 이용하시면 됩니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">바코드 처리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">바코드 입력</th>
			<td>
				<input id="barcode" type="input" name="barcode" class="input" size="20" onfocus="this.select();">
				<span class="box_btn_s"><input type="submit" value="재고입력"></span>
				<span class="explain">가장 최근에 확인한 상품이 최상단에 보입니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">현재 상품</th>
			<td id="searchPrd"></td>
		</tr>
	</table>
</form>
<?PHP

	if(!$big) {
		echo "<div class=\"box_full center\">재고 파악을 진행할 분류를 선택해 주십시오.</div>";
		return;
	}

	$res = $pdo->iterator("select p.name, c.barcode, c.complex_no, c.force_soldout, c.opts, curr_stock(c.complex_no) as stock $f from $tbl[product] p inner join erp_complex_option c on p.no = c.pno $join where p.big='$big' and p.stat in (2,3) and c.del_yn='N' $w order by p.name asc");

?>
<form method="post" target="hidden<?=$now?>" onsubmit="return stockSubmit(this)">
	<input type="hidden" name="body" value="erp@stock_check.exe">
	<input type="hidden" name="exec" value="complete">
	<div class="box_middle2">
		<span class="box_btn blue"><input type="submit" value="재고파악 완료"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="allZero()"></span>
	</div>
	<table class="tbl_col">
		<caption class="hidden">재고 현황</caption>
		<colgroup>
			<col span="2">
			<col style="width:150px">
			<col style="width:80px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">상품명</th>
				<th scope="col">옵션</th>
				<th scope="col">바코드</th>
				<th scope="col">품절방식</th>
				<th scope="col">현재고</th>
				<th scope="col">주문</th>
				<th scope="col">변경재고</th>
			</tr>
		</thead>
		<tbody id="prd_list">
			<?php
				$idx = 0;
                foreach ($res as $data) {
					$default_stock = 0 - $data['stock_ord'];
					switch($data['force_soldout']) {
						case 'Y' : $out_status = "<span class='p_color2'>$_erp_force_stat[Y]</span>"; break;
						case 'L' : $out_status = "<span style='color:#00cc00'>$_erp_force_stat[L]</span>"; break;
						case 'N' : $out_status = $_erp_force_stat['N']; break;
					}
					$idx++;
			?>
			<tr id="complex_<?=$data['complex_no']?>">
				<td class="left">
					<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$data['name']?>
					<input type="hidden" name="complex_no[<?=$idx?>]" value="<?=$data['complex_no']?>">
				</td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td style="font-family: tahoma;"><?=$data['barcode']?></td>
				<td><?=$out_status?></td>
				<td><?=number_format($data['stock'])?></td>
				<td><?=number_format($data['stock_ord'])?></td>
				<td style="background:#ffeef7;">
					<input type="text" name="stock[<?=$idx?>]" class="input stockInput" ord="<?=$data['stock_ord']?>" value="<?=$default_stock?>" size="5">
					<input type="hidden" name="org[<?=$idx?>]" value="<?=$data['stock']?>">
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="재고파악 완료"></span>
	</div>
</form>
<object id="mplayer" CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" style="display:none;">
	<param name="autoStart" value="false">
	<param name="URL" value="">
</object>

<script type="text/javascript">
	$(document).ready(function() {
		$('#barcode').select();
	});

	function stockPrdSearch(f) {
		if(!checkBlank(f.barcode, '상품 바코드를 입력해주세요.')) return false;
		$.post('?body='+f.body.value, {'exec':f.exec.value, 'barcode':f.barcode.value}, function(data) {
			var json = jQuery.parseJSON(data);
			if(json.result != 200) {
				playwav('notExists');
				window.alert('잘못된 바코드입니다.');
			} else {
				var tr = $('#complex_'+json.complex_no);
				if(tr.length > 0) {
					tr.css('backgroundColor', '#FEF1B4');
					$("#prd_list").prepend(tr);
					setTimeout(function(){
						tr.css('backgroundColor', '');
					}, 1500);

					playwav('confirm');
					var stock = tr.find('input[name^=stock]');
					stock.val(stock.val().toNumber() + 1);
					$('#searchPrd').html(json.html);
					$('#barcode').val('').select();
				} else {
					playwav('notExists');
					window.alert('검색된 내용에 포함되지 않은 상품입니다.');
				}
			}
		});

		return false;
	}

	function playwav(msg) {
		try {
			var mplayer = document.getElementById('mplayer');
			mplayer.URL = engine_url+'/_manage/erp/wav/'+msg+'.wma';
			mplayer.controls.play();
		} catch(audioException) {}
	}

	function allZero() {
		if(confirm('변경재고가 모두 0으로 변경됩니다.\n작업하신 내용이 있다면 모두 삭제됩니다.\n발송대기 주문이 있는 경우 주문 수량만큼 추가로 마이너스 처리됩니다.\n\n계속 진행하시겠습니까?')) {
			$('.stockInput').each(function() {
				this.value = 0 - $(this).attr('ord').toNumber();
			});
		}
	}

	function stockSubmit(f) {
		if(confirm('설정하신 내용과 같이 재고를 변경하시겠습니까?')) {

		}
	}
</script>