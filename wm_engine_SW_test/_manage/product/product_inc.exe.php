<?PHP

	printAjaxHeader();

	include_once $engine_dir.'/_engine/include/shop_detail.lib.php';
	include $engine_dir."/_engine/include/paging.php";

	$type = $_GET['type'];
    $detaiil_search = $_GET['detaiil_search'];

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

    $w = " and (prd_type='1' or prd_type='')";
	$search_key = addslashes(trim($_GET['search_key']));
	$search_str = mb_convert_encoding(trim($_GET['search_str']), _BASE_CHARSET_, array('utf8', 'euckr'));
	$exclude = numberOnly($_GET['exclude']);
	if($exclude > 0) $w .= " and no != '$exclude'";
	if($search_key && $search_str) $w .= " and `$search_key` like '%".addslashes($search_str)."%'";
    foreach (array(1, 4, 5) as $ctype) {
        for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
            $col_name = $_cate_colname[$ctype][$i];
            $cate = numberOnly($_GET['p'.$col_name]);
            if($cate > 0) {
                $w .= " and $col_name='$cate'";
                break;
            }
        }
    }

    $sdate = $_GET['sdate'];
    $edate = $_GET['edate'];
	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	if (!$all_date) {
		$_sdate = strtotime($sdate);
        $_edate = strtotime($edate)+86399;
        if ($sdate) $w .= " and reg_date >= $_sdate";
        if ($edate) $w .= " and reg_date <= $_edate";
	}
	if (!$sdate || !$edate) {
		$sdate = $edate = date("Y-m-d", $now);
		$all_date = "Y";
	}

    $sprc = numberOnly($_GET['sprc']);
    $eprc = numberOnly($_GET['eprc']);
    if ($sprc || $eprc) {
        if ($sprc > 0) $w .= " and sell_prc >= $sprc";
        if ($eprc > 0) $w .= " and sell_prc <= $eprc";
    }

	$stat = $_GET['stat'];
	if(is_array($stat) == false) $stat = array(2,3,4);
	$w .= " and `stat` in (".implode(',', $stat).")";

	if($cfg['use_partner_shop'] == 'Y') {
		if($admin['partner_no'] > 0) {
			$w .= " and partner_no='$admin[partner_no]'";
		}
        $partner_no = numberOnly($_GET['partner_no']);
        if (strlen($partner_no) > 0) {
            if ($_GET['exec'] == 'refprd' && $_GET['exparam'] == '99') {
                if ($partner_no > 0) {
        			$w .= " and (partner_no='$partner_no' and dlv_type=0)";
                } else {
        			$w .= " and (partner_no='$partner_no' or dlv_type=1)";
                }
            } else {
    			$w .= " and partner_no='$partner_no'";
            }
        }

		$add_field = ", partner_no";
		$_partners = array();
		$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] order by corporate_name asc");
        foreach ($pres as $ptn) {
			$_partners[$ptn['no']] = stripslashes($ptn['corporate_name']);
		}
	}

    // 세트 상품에 개별 배송 등록 금지
    if ($scfg->comp('use_prd_dlvprc', 'Y') == true && $_GET['exparam'] == '99') {
        $w .= " and delivery_set=0";
    }

	if($_GET['exec'] == 'stock_list') {
		$w .= " and ea_type=1";
	}

	if($_GET['smartsotre'] == 'Y') {
		$w .= " and n_store_check='Y'";
		if($cfg['use_partner_shop'] == 'Y' && $admin['partner_no'] > 0) {
			$w .= " and partner_no='{$admin['partner_no']}'";
		}
	}

	if(isset($_GET['partner_mode'])) {
		$partner_mode = numberOnly($_GET['partner_mode']);
		if($_GET['partner_mode'] === '0') {
			$w .= " and (partner_no='$partner_mode' or dlv_type='1')";
		} else {
			$w .= " and partner_no='$partner_mode'";
		}
	}

    // 지정된 상품만 검색
    if ($_GET['search_no']) {
        $w .= ' and no in ('.addslashes($_GET['search_no']).')';
    }

	$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord, ea_type $add_field from $tbl[product] where wm_sc=0 and prd_type=1 $w $dw order by reg_date desc, no desc";
    $tstat = ($_GET['tstat']) ? numberOnly($_GET['tstat']) : '';
    ${'list_tab_active'.$tstat} = 'class="active"';
    $tres = $pdo->query("select stat, count(*) as cnt from {$tbl['product']} where wm_sc=0 $w $dw group by stat");
    foreach ($tres as $val) {
        $_tabcnt[$val['stat']] = $val['cnt'];
    }
    $_tabcnt['total'] = ($_tabcnt) ? array_sum($_tabcnt) : '0';
    if (empty($tstat) == false) $w .= " and stat='$tstat'";
    $_page_group = ($_GET['tstat'] > 0) ? $_GET['tstat'] : 'total';

    $_mng_sort = array(
        2 => '등록일순',
        0 => '수정일순',
        6 => '가격높은순',
        7 => '가격낮은순',
        24 => '후기작성수',
    );
    $sort = $_GET['sort'];
    if (empty($sort) == true || array_key_exists($sort, $_mng_sort) == false) {
        $sort = 0;
    }
    $_sort = $cfg['mng_sort'][$sort];

	$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord, ea_type $add_field from $tbl[product] where wm_sc = 0 $w $dw order by $_sort, no desc";

	$QueryString = makeQueryString('page');

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$PagingInstance = new Paging($_tabcnt[$_page_group], $page, 5, 10);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$_tabcnt[$_page_group]-($row*($page-1));

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="psearch.open(\'$1\', psearch.opt)"', $pg_res);

	include_once $engine_dir.'/_manage/manage.lib.php';

    // 파트너 목록
    if ($scfg->comp('use_partner_shop', 'Y') == true) {
        $_partners = array('0' => '본사');
        $ptns = $pdo->iterator("select no, corporate_name from {$tbl['partner_shop']} order by corporate_name asc");
        foreach ($ptns as $ptn_data) {
            $_partners[$ptn_data['no']] = stripslashes($ptn_data['corporate_name']);
        }

        $_partners_disabled = false;
        if (strlen($_GET['partner_no']) > 0 && $_GET['exparam'] == '99') {
            $_partners_disabled = true;
        }
        $_psel = (strlen($_GET['partner_no']) > 0) ? $_GET['partner_no'] : '';
    }

	if(defined('__PRODUCT_INC_CUSTOM__') == true) return;

?>
<style type="text/css">
#ui-datepicker-div {z-index: 1000 !important;}
.option {margin-bottom: 3px;}
.option > select {width: 95%;}
.option > input {width:95%;}
</style>
<div id="popupContent" class="popupContent layerPop prdbx" style="width:800px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA." style="width:63px; height:18px;"></h1>
		<div id="mngTab_pop">상품검색</div>
	</div>
	<div id="popupContentArea">
		<form id="prdIncFrm" onsubmit="return psearch.fsubmit(this);">
			<input type="hidden" name="body" value="<?=$_GET['body']?>">
			<input type="hidden" name="exparam" value="<?=$_GET['exparam']?>">
			<input type="hidden" name="tstat" value="<?=$_GET['tstat']?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
            <div id="search">
                <div class="box_search">
                    <div class="box_input">
                        <div class="select_input shadow" style="margin-right: 110px;">
                            <div class="select">
                                <select name="search_key">
                                    <option value="name" <?=checked($search_key, 'name', 1)?>>상품명</option>
                                    <option value="keyword" <?=checked($search_key, 'keyword', 1)?>>검색키워드</option>
                                    <option value="code" <?=checked($search_key, 'code', 1)?>>상품코드</option>
                                    <option value="origin_name" <?=checked($search_key, 'origin_name', 1)?>>장기명</option>
                                    <option value="seller" <?=checked($search_key, 'seller', 1)?>>사입처명</option>
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
                            <select name="p<?=$_cate_colname[$ctype][1]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="1">
                                <option value="">::대분류::</option>
                                <?=${'pcate_sel'.$ctype.'_1'}?>
                            </select>
                            <select name="p<?=$_cate_colname[$ctype][2]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="2">
                                <option value="">::중분류::</option>
                                <?=${'pcate_sel'.$ctype.'_2'}?>
                            </select>
                            <select name="p<?=$_cate_colname[$ctype][3]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="3">
                                <option value="">::소분류::</option>
                                <?=${'pcate_sel'.$ctype.'_3'}?>
                            </select>
                            <?php if($cfg['max_cate_depth'] >= 4) { ?>
                            <select name="p<?=$_cate_colname[$ctype][4]?>" style="width:24%;" class="cate_multis" data-ctype="<?=$ctype?>" data-level="1">
                                <option value="">::세분류::</option>
                                <?=${'pcate_sel'.$ctype.'_4'}?>
                            </select>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php }} ?>
                    <tr>
                        <th scope="row">등록일</th>
                        <td>
                            <input type="input" name="sdate" class="input datepicker" size="10" value="<?=$sdate?>"> ~
                            <input type="input" name="edate" class="input datepicker" size="10" value="<?=$edate?>">
                            <label><input type="checkbox" class="p_all_date" name="all_date" value="Y" <?=checked($all_date, 'Y')?>> 전체기간</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">판매가</th>
                        <td>
                            <input type="input" name="sprc" class="input" size="10" value="<?=$sprc?>"> ~
                            <input type="input" name="eprc" class="input" size="10" value="<?=$eprc?>">
                        </td>
                    </tr>
	                <?php if (is_array($_partners) == true) { ?>
	                <tr>
	                    <th scope="row">입점사</th>
	                    <td>
	                        <?=selectArray($_partners, 'partner_no', false, ':: 입점사 ::', $_psel)?>
	                        <script>
                            $('.ref_sortable_99').each(function() {
                                $('select[name=partner_no]').prop('disabled', true);
                            });
	                        </script>
	                    </td>
	                </tr>
	                <?php } ?>
                </table>
                <div class="box_bottom top_line">
                    <span class="box_btn blue"><input type="submit" value="검색"></span>
                    <span class="box_btn"><input type="button" value="초기화" onclick="psearch.reset()"></span>
                </div>
            </div>
		</form>
        <div class="box_tab" style="margin-top:0; border-top:0;">
            <ul>
                <li><a href="#" onclick="tabSearch('tstat', ''); return false;" <?=$list_tab_active?> >전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
                <?php if (in_array('2', $stat) == true) {?>
                <li><a href="#" onclick="tabSearch('tstat', '2'); return false;" <?=$list_tab_active2?>>정상<span><?=number_format($_tabcnt[2])?></span></a></li>
                <?php } ?>
                <?php if (in_array('3', $stat) == true) {?>
                <li><a href="#" onclick="tabSearch('tstat', '3'); return false;" <?=$list_tab_active3?>>품절<span><?=number_format($_tabcnt[3])?></span></a></li>
                <?php } ?>
                <?php if (in_array('4', $stat) == true) {?>
                <li><a href="#" onclick="tabSearch('tstat', '4'); return false;" <?=$list_tab_active4?>>숨김<span><?=number_format($_tabcnt[4])?></span></a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="box_sort">
            <dl class="list">
                <dt class="hidden">정렬</dt>
                <dd>
                    <?=selectArray($_mng_sort, 'sort', null, null, $sort, "tabSearch('sort', this.value);")?>
                </dd>
            </dl>
        </div>
		<table class="tbl_col">
			<caption class="hidden">상품검색</caption>
			<colgroup>
				<col>
				<?php if($type == 'add') { ?>
				<col style="width:20%">
				<col style="width:10%">
				<?php } ?>
				<col style="width:15%">
				<?php if($type != 'add') { ?>
				<col style="width:10%">
				<?php } ?>
				<col style="width:10%">
				<col style="width:100px">
			</colgroup>
			<thead>
				<tr>
					<th scope="col">상품</th>
					<?php if($type == 'add') { ?>
					<th scope="col">옵션</th>
					<th scope="col">수량</th>
					<?php } ?>
					<th scope="col">가격</th>
					<?php if($type != 'add') { ?>
					<th scope="col">적립금</th>
					<?php } ?>
					<th scope="col">상태</th>
					<th scope="col">선택</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $prd) {
						$prd['parent'] = $prd['no'];
						$prd['name'] = inputText(strip_tags($prd['name']));
						if($prd['min_ord'] < 1) $prd['min_ord'] = 1;

						if($prd['upfile3']) {
							$file_dir = getFileDir($prd['updir']);
							$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='40' height='40'>";
						}

						switch($prd['stat']) {
							case '2' : $prd['stat'] = '정상'; break;
							case '3' : $prd['stat'] = '품절'; break;
							case '4' : $prd['stat'] = '숨김'; break;
						}

						// erp/stock_list 넘어 올경우
						$option = '';
						$data['option_str'] = '';
						if($_GET['exec'] == 'stock_list') {
							$sql="select * from `".$tbl['product_option_set']."` where `pno`='".$prd['no']."'  and otype!='4B' order by `sort`";
							$optionres = $pdo->iterator($sql);
							if($optionres) {
                                foreach ($optionres as $option) {
									if($option) $data['option_str'] .='<br>'.printOption($option,'','','','',$prd['no']);
								}
							}
						}
				?>
				<tr>
					<td class="left">
						<div class="box_setup btn_none">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
							<dl style="height:30px;">
								<dt class="title"><a href="javascript:;" onclick="psearch.psel(<?=$prd['no']?>)"><?=$prd['name']?></a></dt>
								<dd class="p_color4" style="margin: 2px 0;"><strong><?=$_partners[$prd['partner_no']]?></strong></dd>
								<dd><a href="./?body=product@product_register&pno=<?=$prd['no']?>" class="p_color" target="_blank">수정</a></dd>
							</dl>
						</div>
						<div style="width:100%;"><?=$data['option_str']?></div>
					</td>
					<?php if($type == 'add') { ?>
					<td class="product_select_options_<?=$prd['no']?>">
						<?PHP
							if(isset($multi) == false) $multi = 0;
							$multi++;
							$prdOptionNoTR = true;
							$option_list_asql = " and necessary in ('Y', 'N')";
							while($opt = prdOptionList("", 1, "", "", $multi)) {
								echo '<div class="option">'.str_replace('form_input', '', $opt['option_str']).'</div>';
							}
							unset($prdOptionRes, $opt_no);
						?>
					</td>
					<td>
						<input type="text" name="buy_ea" value="<?=$prd['min_ord']?>" class="input buy_ea_pno_<?=$prd['no']?>" size="2" onfocus="this.select();">
					</td>
					<?php } ?>
					<td>
						<?=parsePrice($prd['sell_prc'],true)?>
						<div class="add_price<?=$multi?>"></div>
					</td>
					<?php if($type != 'add') { ?>
					<td><?=parsePrice($prd['milage'], true)?></td>
					<?php } ?>
					<td><?=$prd['stat']?></td>
					<td>
						<?php if ($_GET['exec'] == 'refprd' && $_GET['exparam'] == '99') { ?>
                        <span class="box_btn_s blue"><input type="button" value="추가" onclick="psearch.addRefPrd('99', 1, <?=$prd['no']?>)"></span>
						<?php } else if($_GET['exec'] == 'refprd') { ?>
						    <?php if ($admin['partner_no'] > 0 && $cfg['partner_prd_accept'] == 'Y') { ?>
							<span class="box_btn_s blue"><input type="button" value="일반등록" onclick="psearch.addHeadRef('<?=$_GET['exparam']?>', 1, <?=$prd['no']?>)"></span>
							<?php } else if ($admin['partner_no']==0 || ($admin['partner_no']>0 && $cfg['partner_prd_accept']=='N')) { ?>
							<p><span class="box_btn_s2 blue"><input type="button" value="일반등록" onclick="psearch.addRefPrd('<?=$_GET['exparam']?>', 1, <?=$prd['no']?>)"></span></p>
							<p style="margin-top: 3px"><span class="box_btn_s2 blue"><input type="button" value="상호등록" onclick="psearch.addRefPrd('<?=$_GET['exparam']?>', 2, <?=$prd['no']?>)"></span></p>
							<?php } ?>
						<?php } else { ?>
                        <span class="box_btn_s blue"><input type="button" value="선택" onclick="psearch.psel(<?=$prd['no']?>)"></div>
                        <?php } ?>
					</td>
				</tr>
				<?php } ?>
                <?php if ($res->rowCount() == 0) {?>
                <tr class="none">
                    <td colspan="7"><p class="nodata">검색된 상품이 없습니다.</p></td>
                </tr>
                <?php } ?>
			</tbody>
		</table>

        <div class="box_bottom">
            <?=$pg_res?>
        </div>
	</div>
    <div class="pop_bottom">
        <span class="box_btn_s gray"><input type="button" value="창닫기" onclick="psearch.close()"></span>
    </div>

	<script type="text/javascript">
	function optionCal(f, dummy1, val, o, multi) {
		var opt_no = 0;
		var add_price = 0;
		while(1) {
			opt_no++;
			var o = $('select[name="option'+opt_no+'['+multi+']"]');
			if(o.length == 0) break;
			if(o.val() ==  '') continue;

			var val = o.val().split('::');
			add_price += parseInt(val[1]);
		}
		$('.add_price'+multi).html((add_price > 0) ? '(+'+setComma(add_price)+')' : '');
	}

    function tabSearch(key, val) {
        var f = document.getElementById('prdIncFrm');
        f.elements[key].value = val;
        psearch.fsubmit(f);
    }

    (allCheck = function() {
        var all_checked = $('.p_all_date').prop('checked');
        var dates = $('#popupContentArea').find('[name=sdate], [name=edate]');
        if (all_checked == true) {
            dates.prop('disabled', true).css('background-color', '#f2f2f2');
        } else {
            dates.prop('disabled', false).css('background-color', '');
        }
    })();
    $('.p_all_date').change(allCheck);

    $(function() {
        $('#popupContent').find('#popupContentArea').css({
            'max-height': $(window).height()-190,
            'overflow-y': 'auto',
        });

        setDatepicker();

		setTimeout(function() {
			//$('.btt').btt('tooltip_square');
		}, 1);

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
</div>