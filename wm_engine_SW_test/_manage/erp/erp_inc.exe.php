<?PHP

    /**
     * 바코드 선택 레이어
     **/

    printAjaxHeader();

    include_once __ENGINE_DIR__.'/_engine/include/shop_detail.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/wingPos.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/paging.php';

    $instance = $_GET['instance'];
    $search_key = addslashes(trim($_GET['search_key']));
    $search_str = trim($_GET['search_str']);
    $detaiil_search = $_GET['detaiil_search'];
	$force_soldout = $_GET['force_soldout'];

    // 검색 카테고리
    foreach (array(1, 4, 5) as $ctype) {
        $cw = '';
        for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
            $p_col_name = $_cate_colname[$ctype][($i-1)];
            $n_col_name = $_cate_colname[1][($i-1)];
            if ($p_col_name) {
                $parent = $_GET['p'.$p_col_name];
                $cw .= " and $n_col_name='$parent'";
            }
            if ($i != 1 && !$parent) continue;

            $sql = $pdo->iterator("select no, name from {$tbl['category']} where ctype='$ctype' and level='$i' $cw order by sort asc");
            foreach ($sql as $cate) {
                $sel = ($_GET['p'.$_cate_colname[$ctype][$i]] == $cate['no']) ? 'selected' : '';
                ${'pcate_sel'.$ctype.'_'.$i} .= "<option value='{$cate['no']}' $sel>".stripslashes($cate['name'])."</option>";
            }
        }
    }

    // 검색
    $w = "p.stat in (2, 3, 4) and p.wm_sc=0 and e.del_yn='N' ";
    if ($search_str) {
        $_search_str = addslashes($search_str);
        $w .= " and $search_key like '%{$_search_str}%'";
    }
	if (!$force_soldout) $force_soldout =array('N', 'Y', 'L');
	else {
		$_force = preg_replace('/([A-Z])/', "'$1'", implode(',', $force_soldout));
		$w .= " and force_soldout in ($_force)";
	}
    foreach (array(1, 4, 5) as $ctype) {
        for ($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
            $col_name = $_cate_colname[$ctype][$i];
            $cate = numberOnly($_GET['p'.$col_name]);
            if ($cate > 0) {
                $w .= " and $col_name='$cate'";
                break;
            }
        }
    }
    $add_field = '';
    if ($cfg['use_partner_shop'] == 'Y') {
        if($admin['partner_no'] > 0) {
            $w .= " and p.partner_no='{$admin['partner_no']}'";
        }

        $add_field .= ", p.partner_no";
        $_partners = array();
        $pres = $pdo->iterator("select no, corporate_name from {$tbl['partner_shop']} order by corporate_name asc");
        foreach ($pres as $ptn) {
            $_partners[$ptn['no']] = stripslashes($ptn['corporate_name']);
        }
    }

    // 탭
    $tstat = ($_GET['tstat']) ? (int) $_GET['tstat'] : '';
    ${'list_tab_active'.$tstat} = 'class="active"';
    $tres = $pdo->query("
        select p.stat, count(*) as cnt
        from {$tbl['product']} p inner join erp_complex_option e on p.no=e.pno
        where $w
        group by p.stat
    ");
    foreach ($tres as $val) {
        $_tabcnt[$val['stat']] = $val['cnt'];
    }
    $_tabcnt['total'] = array_sum($_tabcnt);
    if (empty($tstat) == false) $w .= " and stat='$tstat'";

    // 정렬
    $_mng_sort = array(
        2 => '등록일순',
        0 => '수정일순',
        6 => '가격높은순',
        7 => '가격낮은순',
    );
    $sort = $_GET['sort'];
    if (empty($sort) == true || array_key_exists($sort, $_mng_sort) == false) {
        $sort = 0;
    }
    $_sort = $cfg['mng_sort'][$sort];

    // 목록 조회
    $sql = "
        select
            p.no, p.hash, p.name, p.stat, p.updir, p.upfile3, p.w3, p.h3, p.sell_prc,
            e.complex_no, e.barcode, e.qty, e.opts, e.force_soldout $add_field
        from {$tbl['product']} p inner join erp_complex_option e on p.no=e.pno
        where $w and p.prd_type=1
        order by p.$_sort
    ";
    $page = (int) $_GET['page'];
    if ($page <= 1) $page = 1;
    $NumTotalRec = $pdo->row("select count(*) from {$tbl['product']} p inner join erp_complex_option e on p.no=e.pno where $w");
    $PagingInstance = new Paging($NumTotalRec, $page, 5, 10);
    $PagingInstance->addQueryString(makeQueryString('page'));
    $PagingResult = $PagingInstance->result($pg_dsn);
    $sql .= $PagingResult['LimitQuery'];

    $pg_res = $PagingResult['PageLink'];
    $res = $pdo->iterator($sql);
    $idx = $NumTotalRec-($row*($page-1));

    $pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="'.$instance.'.open(\'$1\')"', $pg_res);

?>
<div id="popupContent" class="popupContent layerPop" style="width:800px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">바코드검색</div>
	</div>
	<div id="popupContentArea">
		<form id="prdIncFrm" onsubmit="return <?=$instance?>.fsubmit(this);">
			<input type="hidden" name="body" value="<?=$_GET['body']?>">
			<input type="hidden" name="exparam" value="<?=$_GET['exparam']?>">
			<input type="hidden" name="tstat" value="<?=$_GET['tstat']?>">
			<input type="hidden" name="instance" value="<?=$instance?>">

            <div id="search">
                <div class="box_search">
                    <div class="box_input">
                        <div class="select_input shadow" style="margin-right: 110px;">
                            <div class="select">
                                <select name="search_key">
                                    <option value="name" <?=checked($search_key, 'name', 1)?>>상품명</option>
                                    <option value="keyword" <?=checked($search_key, 'keyword', 1)?>>검색키워드</option>
                                    <option value="code" <?=checked($search_key, 'code', 1)?>>상품코드</option>
                					<option value="barcode" <?=checked($search_key, 'barcode', 1)?>>바코드</option>
                                </select>
                            </div>
                            <div class="area_input">
                                <input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
                            </div>
                        </div>
                        <div class="view">
                            <div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn("prdIncFrm")?></div>
                        </div>
                    </div>
                </div>
                <table class="tbl_search search_box_omit">
                    <caption class="hidden">상품수정/관리</caption>
                    <colgroup>
                        <col style="width:20%">
                    </colgroup>
                    <?php foreach(array(1, 4, 5) as $ctype) { if ($ctype == 1 || $scfg->comp($_cate_colname[$ctype][1].'_mng', 'Y')) { ?>
                    <tr>
                        <th scope="row">
                            매장분류
                            <?php if ($ctype > 1) {?>
                            <p>(<?=$cfg[$_cate_colname[$ctype][1].'_name']?>)</p>
                            <?php } ?>
                        </th>
                        <td>
                            <select name="<?=$_cate_colname[$ctype][1]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="1">
                                <option value="">::대분류::</option>
                                <?=${'pcate_sel'.$ctype.'_1'}?>
                            </select>
                            <select name="<?=$_cate_colname[$ctype][2]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="2">
                                <option value="">::중분류::</option>
                                <?=${'pcate_sel'.$ctype.'_2'}?>
                            </select>
                            <select name="<?=$_cate_colname[$ctype][3]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="3">
                                <option value="">::소분류::</option>
                                <?=${'pcate_sel'.$ctype.'_3'}?>
                            </select>
                            <?if($cfg['max_cate_depth'] >= 4) {?>
                            <select name="<?=$_cate_colname[$ctype][4]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="1">
                                <option value="">::세분류::</option>
                                <?=${'pcate_sel'.$ctype.'_4'}?>
                            </select>
                            <?}?>
                        </td>
                    </tr>
                    <?}}?>
                    <tr>
                        <th scope="row">품절상태</th>
                        <td>
                            <?php foreach ($_erp_force_stat as $key => $val) { ?>
                            <label><input type="checkbox" name="force_soldout[]" value="<?=$key?>" <?=checked(in_array($key, $force_soldout), true)?>> <?=$val?></label>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <div class="box_bottom top_line">
                    <span class="box_btn blue"><input type="submit" value="검색"></span>
                    <span class="box_btn"><input type="button" value="초기화" onclick="psearch.open()"></span>
                </div>
            </div>
            <div class="box_tab" style="margin-top:0; border-top:0;">
                <ul>
                    <li><a href="#" onclick="tabSearch('tstat', ''); return false;"  <?=$list_tab_active?> >전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
                    <li><a href="#" onclick="tabSearch('tstat', '2'); return false;" <?=$list_tab_active2?>>정상<span><?=number_format($_tabcnt[2])?></span></a></li>
                    <li><a href="#" onclick="tabSearch('tstat', '3'); return false;" <?=$list_tab_active3?>>품절<span><?=number_format($_tabcnt[3])?></span></a></li>
                    <li><a href="#" onclick="tabSearch('tstat', '4'); return false;" <?=$list_tab_active4?>>숨김<span><?=number_format($_tabcnt[4])?></span></a></li>
                </ul>
            </div>
            <div class="box_sort">
                <dl class="list">
                    <dt class="hidden">정렬</dt>
                    <dd>
                        <?=selectArray($_mng_sort, 'sort', null, null, $sort, "psearch.fsubmit(this.form);")?>
                    </dd>
                </dl>
            </div>
		</form>
		<table class="tbl_col">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col>
				<col style="width:10%">
				<col>
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:9%">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">상품</th>
					<th scope="col">가격</th>
					<th scope="col">바코드</th>
					<th scope="col" colspan="2">재고수량</th>
					<th scope="col">상태</th>
					<th scope="col">선택</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $prd) {
						$prd['parent'] = $prd['no'];
						$prd['name'] = inputText(strip_tags($prd['name']));

						if($prd['upfile3']) {
							$file_dir = getFileDir($prd['updir']);
							$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='40' height='40'>";
						}

						switch($prd['stat']) {
							case '2' : $prd['stat'] = '정상'; break;
							case '3' : $prd['stat'] = '품절'; break;
							case '4' : $prd['stat'] = '숨김'; break;
						}
						$prd['opt_name'] = getComplexOptionName($prd['opts']);

						$json = json_encode($prd);

				?>
				<tr>
					<td class="left">
						<div class="box_setup" style="width:100%;">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
							<dl style="height:30px;">
								<dt class="title"><strong><?=$prd['name']?></strong></dt>
								<dd><?=$prd['opt_name']?></dd>
								<dd class="p_color4" style="margin: 2px 0;"><strong><?=$_partners[$prd['partner_no']]?></strong></dd>
							</dl>
						</div>
						<div style="width:100%;"><?=$data['option_str']?></div>
					</td>
					<td><?=parsePrice($prd['sell_prc'],true)?></td>
					<td><?=$prd['barcode']?></td>
					<td><?=$_erp_force_stat[$prd['force_soldout']]?></td>
					<td><?=number_format($prd['qty'])?></td>
					<td><?=$prd['stat']?></td>
					<td>
						<script type="text/javascript">
						var json_<?=$prd['complex_no']?> = <?=$json?>;
						</script>
						<span class="box_btn_s blue"><input type="button" value="선택" onclick="<?=$instance?>.psel(<?=$prd['complex_no']?>)"></span>
					</td>
				</tr>
				<?}?>
			</tbody>
		</table>
        <div class="box_bottom">
            <?=$pg_res?>
        </div>
        <div class="pop_bottom">
            <span class="box_btn_s gray"><input type="button" value="창닫기" onclick="<?=$instance?>.close()"></span>
        </div>
	</div>
</div>
<script>
function tabSearch(key, val) {
    var f = document.getElementById('prdIncFrm');
    f.elements[key].value = val;
    psearch.fsubmit(f);
}

$(function() {
    $('#popupContent').find('#popupContentArea').css({
        'max-height': $(window).height()-130,
        'overflow-y': 'auto',
    });

    $('.cate_multis').change(function() {
        var _t = $(this);
        var ctype = _t.data('ctype');
        var level = _t.data('level');
        var cprefix = '';

        switch(ctype) {
            case 4 : cprefix = 'x'; break;
            case 5 : cprefix = 'y'; break;
        }

        chgCateInfinite(this, level+1, 'p'+cprefix);
    });

    <?php if ($detaiil_search == '1') { ?>
    searchBoxSH(1, 'prdIncFrm');
    <?php } ?>
});
</script>