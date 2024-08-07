<?PHP

	$ctype = numberOnly($_GET['ctype']);
	if(!$ctype) $ctype = 1;
	${'active_ctype'.$ctype} = 'active';

	$_big = numberOnly($_GET['big']);
	$_mid = numberOnly($_GET['mid']);
	$_small = numberOnly($_GET['small']);
	$_depth4 = numberOnly($_GET['depth4']);
	$nohidden = $_GET['nohidden'];
	$nosold = $_GET['nosold'];
	$noprivate = $_GET['noprivate'];

	$cate_split = ' &gt; ';
	$_cname = getCategoriesCache(1);

	switch($ctype) {
		case '1' :
			$ctype_name = '매장분류';
			$c_prefix = '';
			$order = 'desc';
			$correct_num = -1;
		break;
		case '4' :
			$ctype_name = $cfg['xbig_name'];
			$c_prefix = 'x';
			$order = 'asc';
			$correct_num = 1;
		break;
		case '5' :
			$ctype_name = $cfg['ybig_name'];
			$c_prefix = 'y';
			$order = 'asc';
			$correct_num = 1;
		break;
	}

	// 카테고리 검색 리스트 출력
	if($ctype == 1 || $_use[$c_prefix.'big'] == 'Y') {
		$cw = '';
		for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
			$cl = $_cate_colname[1][$i];
			$val = numberOnly($_GET[$cl]);
			if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
		}
		$sql = $pdo->iterator("select no, name, level from $tbl[category] where ctype='$ctype' and (level='1' $cw) order by level asc, sort asc");
        foreach ($sql as $cate) {
			$cl = ${'_'.$_cate_colname[1][$cate['level']]};
			$sel = ($cl == $cate['no']) ? 'selected' : '';
			${'item_'.$cate['level']} .= "\n<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
		}
	}

	// 상품 리스트
	if($_big > 0) {
		$w = '';
		if($_depth4 > 0) {
			$w .= " and {$c_prefix}depth4='$_depth4'";
			$_sort_fd_name = 'sort_depth4';
		} elseif($_small > 0) {
			$w .= " and {$c_prefix}small='$_small'";
			$_sort_fd_name = 'sort_small';
		} elseif($_mid > 0) {
			$w .= " and {$c_prefix}mid='$_mid'";
			$_sort_fd_name = 'sort_mid';
		} elseif($_big > 0) {
			$w .= " and {$c_prefix}big='$_big'";
			$_sort_fd_name = 'sort_big';
		}
		if($ctype == 1) {
            $_sort_fd_tbl = 'p';
			$_sort_fd_name = str_replace('_', '', $_sort_fd_name);
		} else {
            $_sort_fd_tbl = 'l';
			$join = " inner join $tbl[product_link] l on p.no=l.pno";
			$f = ", l.sort_big, l.sort_mid, l.sort_small";
			$w .= " and l.ctype='$ctype'";
		}

		if($_GET['nohidden'] == 'Y') {
			$w .= " and p.stat != 4";
			if($cfg['prd_sort_soldout'] == 'H') $w .= " and p.stat!=3";
		}
		if($_GET['nosold'] == 'Y') $w .= " and p.stat != 3";
		if($_GET['noprivate'] == 'Y') {
			$privates = $pdo->row("select group_concat(no) from $tbl[category] where ctype='1' and private='Y'");
			if($privates) {
				$w .= " and (big not in ($privates) and mid not in ($privates) and small not in ($privates))";
			}
		}

		$res = $pdo->iterator("
            select
                p.no, p.stat, p.wm_sc, p.updir, p.upfile3, p.w3, p.h3,
                p.name, p.reg_date, p.sell_prc, hit_order, $_sort_fd_tbl.$_sort_fd_name,
                p.hit_sales, p.hit_wish, p.hit_cart, p.hit_view $f
            from {$tbl['product']} p $join where p.stat in (2,3,4) $w
            order by $_sort_fd_tbl.$_sort_fd_name $order
        ");
        if (is_object($res) == true) $prd_ea = $res->rowCount();
        if($prd_ea == 0) {
            $message = '분류 내에 상품이 존재하지 않습니다.';
        }
	} else {
        $message = '상품을 정렬할 매장(분류)을 선택하세요.';
        $res = array();
    }

?>
<form id="search" name="scFrm" method="get" action="./">
	<input type="hidden" name="body" value="product@product_sort">
	<input type="hidden" name="ctype" value="<?=$ctype?>">
	<div class="box_title first">
		<h2 class="title">상품정렬순서변경</h2>
	</div>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class="<?=$active_ctype1?>"><a href="?body=<?=$_GET['body']?>&ctype=1">기본 매장분류</a></li>
			<?php if ($cfg['xbig_mng'] == 'Y') { ?>
			<li class="<?=$active_ctype4?>"><a href="?body=<?=$_GET['body']?>&ctype=4">매장분류(<?=$cfg['xbig_name']?>)</a></li>
			<?php } ?>
			<?php if ($cfg['ybig_mng'] == 'Y') { ?>
			<li class="<?=$active_ctype5?>"><a href="?body=<?=$_GET['body']?>&ctype=5">매장분류(<?=$cfg['ybig_name']?>)</a></li>
			<?php } ?>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품정렬순서변경</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><?=$ctype_name?></th>
			<td>
				<select name="big" class="cate_multis" onchange="chgCateInfinite(this, 2, '');">
					<option value="">::대분류::</option>
					<?=$item_1?>
				</select>
				<select name="mid" class="cate_multis" onchange="chgCateInfinite(this, 3, '');">
					<option value="">::중분류::</option>
					<?=$item_2?>
				</select>
				<select name="small" class="cate_multis" onchange="chgCateInfinite(this, 4, '');">
					<option value="">::소분류::</option>
					<?=$item_3?>
				</select>
				<?php if ($cfg['max_cate_depth'] >= 4) { ?>
				<select name="depth4" class="cate_multis" onchange="this.form.submit();">
					<option value="">::세분류::</option>
					<?=$item_4?>
				</select>
				<?php } ?>
				<div>
					<label class="p_cursor"><input type="checkbox" name="nohidden" value="Y" ;" <?=checked($nohidden, 'Y')?>> 숨김상품 제외</label>
					<label class="p_cursor"><input type="checkbox" name="nosold" value="Y" <?=checked($nosold, 'Y')?>> 품절상품 제외</label>
					<label class="p_cursor"><input type="checkbox" name="noprivate" value="Y" <?=checked($noprivate, 'Y')?>> 개인결제창 제외</label>
				</div>
			</td>
		</tr>
	</table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="reset" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
    </div>
</form>
<br>

<?php
	if($cfg['use_new_sortxy'] != 'Y' && ($ctype == 4 || $ctype == 5)) { // 새로운 이분류, 삼분류 정렬 사용을 위한 DB 마이그레이션
		include 'product_sort_setup.inc.php';
		return;
	}
?>

<div class="frame_sort">
	<form name="cateSortFrm" method="post" action="./" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="product@product_sort.exe">
		<input type="hidden" name="ctype" value="<?=$ctype?>">
		<input type="hidden" name="new_sort" value="">
		<input type="hidden" name="sort_fd_name" value="<?=$_sort_fd_name?>">

        <div class="box_tab first">
            <ul>
                <li><a class="active">전체<span><?=$prd_ea?></span></a></li>
            </ul>
        </div>
        <div class="box_middle left">
            <ul class="list_info">
                <li>상품이 많은 쇼핑몰의 경우 처리시간이 오래 걸리거나 사이트가 순간적으로 느려질 수 있으므로 가급적 접속자가 많은 시간을 피해 진행해 주시기 바랍니다.</li>
                <li>이동하실 상품을 클릭한 뒤 키보드 ↑ ↓ 키로 이동 가능하며, ctrl키와 shift키로 복수의 상품을 선택하여 이동시킬 수 있습니다.</li>
            </ul>
        </div>

		<div id="prdSort">
			<table id="table_goodsSorting" class="tbl_col tbl_col_bottom">
				<colgroup>
					<col style="width:70px">
					<col>
					<col style="width:100px">
					<col style="width:70px">
					<col style="width:70px">
					<col style="width:70px">
					<col style="width:70px">
					<col style="width:70px">
				</colgroup>
				<thead>
					<tr>
						<th scope="col">순서</th>
						<th scope="col">상품명</th>
						<th scope="col">가격</th>
						<th scope="col">주문</th>
						<th scope="col">판매</th>
						<th scope="col">관심</th>
						<th scope="col">담기</th>
						<th scope="col">조회</th>
					</tr>
				</thead>
				<tbody>
					<?PHP
						$ii=0;
						$_checkdata = array();
						$correct_no = 0;
                        foreach ($res as $data) {
							$stat="";
							if($data['stat']==3) $stat="<span class=\"p_color2\"> - 품절</span>";
							elseif($data['stat']==4) $stat="<span class=\"p_color2\"> - 숨김</span>";

							if($data['wm_sc'] > 0) {
								$_tmp_data = $pdo->assoc("select updir, upfile3 from `$tbl[product]` where no='$data[wm_sc]'");
								$data['updir'] = $_tmp_data['updir'];
								$data['upfile3'] = $_tmp_data['upfile3'];
								$data['name'] .= " <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>";
							}
							$thumb = getListImgURL($data['updir'], $data['upfile3']);
							if ($data['upfile3']) {
								$data['imgstr'] = "<img src='$thumb' style='max-width: 50px; max-height: 50px;'>";
							}

							$rewg_date = date('Y-m-d H:i:s', $data['reg_date']);
							$cstr = makeCategoryName($data, 1);

							$data['name']=stripslashes($data['name']);
							if(in_array($data[$_sort_fd_name], $_checkdata)) {
								$data[$_sort_fd_name] = $_prev_sort+$correct_num;
							}
							$_prev_sort = $data[$_sort_fd_name];
							$_checkdata[] = $data[$_sort_fd_name];

					?>
					<tr class="movable" id="<?=$data['no']?>">
						<td><span class="sortnum"><?=++$ii?></span></td>
						<td ondblclick="window.open('./?body=product@product_register&pno=<?=$data['no']?>')" class="left" style="word-break:break-all;">
							<input type="hidden" name="pno[]" value="<?=$data['no']?>">
							<input type="hidden" name="sort_val[]" value="<?=$data[$_sort_fd_name]?>">
							<div class="box_setup">
								<div class="thumb"><?=$data['imgstr']?></div>
								<dl>
									<dt class="title"><strong><?=$data['name']?></strong> <?=$stat?></dt>
									<dd class="cstr"><?=$cstr?></dd>
									<dd class="cstr"><?=$rewg_date?></dd>
								</dl>
							</div>
						</td>
						<td><?=number_format($data['sell_prc'])?>원</td>
						<td><?=number_format($data['hit_order'])?></td>
						<td><?=number_format($data['hit_sales'])?></td>
						<td><?=number_format($data['hit_wish'])?></td>
						<td><?=number_format($data['hit_cart'])?></td>
						<td><?=number_format($data['hit_view'])?></td>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>
		</div>
        <?php if (isset($message) == true) { ?>
        <div class="box_middle2">
            <?=$message?>
        </div>
        <?php } ?>
		<div id="quickmenu" style="position:absolute; right:50px;">
			<ul>
				<li><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" class="move_btn_up"></li>
				<li class="ea"><input type="text" id="step" name="step" value="1" class="input"> 칸 이동</li>
				<li><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" class="move_btn_dn"></li>
				<li>
					<span class="box_btn_s"><input type="button" value="최상" class="move_btn_top"></span>
					<span class="box_btn_s"><input type="button" value="최하" class="move_btn_bottom"></span>
				</li>
			</ul>
			<div class="box_btn gray"><input type="button" value="초기화" onclick="location.reload();"></div>
			<div class="box_btn blue"><input type="button" onclick="sorting_exe()" value="적용하기"></div>
		</div>
	</form>
</div>

<div class="box_bottom left" style="margin-top: 40px;">
	<ul class="list_msg">
		<?php if ($cfg['prd_sort_soldout']=="Y") { ?>
		<li>
			현재 품절 상품을 맨뒤로 정렬하는 기능을 사용중이므로, <strong>품절 상품</strong>을 앞쪽으로 정렬해도 뒤로 배열됩니다.
			<a href="?body=product@product_sort_set" class="p_color">설정변경</a>
		</li>
		<?php } ?>
		<li>정렬할 순서 대로 상품을 이동한뒤 적용하기버튼을 눌러주십시오.</li>
		<li>각 분류별(대/중/소분류) 검색결과에 따라 차이가 생길 수 있습니다 (가능하면 쇼핑몰에서 보여지는 분류별로 검색해 순서를 조정하세요)</li>
		<li>더블 클릭시 제품 상세 정보 / 수정 페이지로 이동합니다</li>
		<li>적용사항은 <a href="?body=product@product_sort_set" class="p_color">상품정렬 설정</a>에서 <strong>'추천순' 정렬</strong> 선택 시 적용됩니다.</li>
		<li>이동하실 상품을 클릭한뒤 키보드 ↑ ↓ 키로 이동가능하며, ctrl 키를 누른상태에서 상품을 클릭하면 복수의 상품을 선택하여 이동시킬수 있습니다.</li>
	</ul>
</div>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Slider.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/_js/productSort.js"></script>
<script type="text/javascript">
	var R2S = new R2Slider('quickmenu', 'R2S', 1, 10);
	R2S.limitTop = $('form[name=cateSortFrm]').offset().top+1;
	R2S.limitBottom = 50;
	R2S.slide();

	function sorting_exe(){
		var sortingArray = [];
		var f = document.cateSortFrm;

		$('#table_goodsSorting tr.movable').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			sortingArray.push(pno);
		});

		var sortVal = new Array();
		$("input[name='sort_val[]']").each(function() {
			sortVal.push(this.value);
		});

		printLoading();

		var url = '/_manage/';
		var data = 'body=product@product_sort.exe';
		data += '&sortingArray=' + sortingArray.join('|');
		data += '&sort_fd_name=' + f.sort_fd_name.value;
		data += '&sort_val='+sortVal.join('|');
		data += '&ctype='+f.ctype.value;

		$.ajax({
			url: url,
			type: 'post',
			data: data,
			success: function(data) {
				if( data == 'OK' ) {
					alert('정렬순서가 변경되었습니다.');
					//location.reload();
				}
				else if( data == 'ERROR1' ){
					alert("상품 업데이트부터 하십시오.");
				}
				else if( data == 'ERROR2' ){
					alert("정렬할 상품이 없습니다.");
				}
				else{
					alert(data);
				}
			},
			complete: function() {
                $('.sortnum').each(function(idx) {
                    this.innerHTML = (idx+1);
                });
				removeLoading();
			}
		});
	}
</script>