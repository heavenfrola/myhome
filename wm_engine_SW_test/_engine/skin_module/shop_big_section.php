<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카테고리 상품 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_fbn = ($_file_name != "shop_big_section.php") ? str_replace("shop_", "", str_replace(".php", "", $_file_name))."_" : "";
	$_replace_code[$_file_name]['cate_name'] = $_cno1['name'];
	$_replace_code[$_file_name]['cate_cno1'] = $_cno1['no'];
	$_replace_code[$_file_name]['cate_ctype'] = $_cno1['ctype'];
	$_replace_code[$_file_name]['cate_level'] = $_cno1['level'];

	$_SESSION['rURL'] = $root_url."/shop/big_section.php?cno1=".$_cno1['no'];

	$_tmp = "";
	if($cfg['prd_sort_type'] == 3){
		$_line = getModuleContent($_fbn."sort_list");
		while($prd_sort = prdSortParse("<strong class='selected'>","</strong>")){
			if($sort == $prd_sort['no']) {
				$prd_sort['chk1'] = "selected";
				$prd_sort['chk2'] = "checked";
				$prd_sort['chk3'] = "active";
			}
			$_tmp .= lineValues($_fbn."sort_list", $_line, $prd_sort);
		}
	}elseif($cfg['prd_sort_type'] == 2){
		while($prd_sort = prdSortParse("<strong>","</strong>")){
			$_tmp .="<input type=\"radio\" id=\"sort".$prd_sort['no']."\" name=\"sort\" ".$prd_sort['checked']." onClick=\"if(this.value){location.href=this.value}\" value=\"".$prd_sort['link']."\"><label for=\"sort".$prd_sort['no']."\">".$prd_sort['name']."</label>";
		}
	}else{
		$_tmp = "<select onChange=\"if(this.value){location.href=this.value}\">";
		$_tmp .= '<option value="">:: '.__lang_shop_info_sortlabel__.' ::</option>';
		while($prd_sort = prdSortParse('', '')){
			$_tmp .= "<option value=\"".$prd_sort['link']."\" ".checked($sort, $prd_sort['no'], 1).">".$prd_sort['name']."</option>";
		}
		$_tmp .= "</select>";
	}
	if($cfg['prd_sort_type'] == 3){
		$_replace_code[$_file_name][$_fbn."sort_list"] = $_tmp;
	}else{
		$_replace_code[$_file_name][$_fbn.'product_sort'] = $_tmp;
	}

	if($_file_name == "shop_search_result.php"){
		$_replace_code[$_file_name]['form_start'] = "<form method=\"get\" action=\"".$root_url."/shop/search_result.php\" onSubmit=\"return checkSearchFrm(this)\" style=\"margin:0px\">";
		$_replace_code[$_file_name]['form_end'] = "</form>";
		$_replace_code[$_file_name]['search1'] = $search1;
		$_replace_code[$_file_name]['search2'] = strip_tags($search2);

		$_tmp = "";
		if(isset($_GET['search_str']) == true) {
			$cate_total = array();
			$_line = getModuleContent("search_cate_list");
			while($cs = cateSearchLoop('big', '', '')) {
				if (!$cs['no']) continue;
				$cs['selected'] = $_GET['cate'] == $cs['no'] ? "class='selected'" : '';
				$cs['name'] = "<a href=\"".$cs['link']."\" $cs[selected]>".$cs['name']."</a>";
				$_tmp .= lineValues("search_cate_list", $_line, $cs);
				$cate_total['big'] += $cs['total'];
			}
			$cs['name'] = trim($_skin['cate_total_name']);
			if($cs['name']) {
				$cs['selected'] = $_GET['cate'] < 1 ? "class='selected'" : '';
				$cs['total'] = number_format($cate_total['big']);
				$cs['link'] = preg_replace('/&?cate=[0-9]+&ctype=big/', '', getURL());
				$cs['name'] = "<a href='".$cs['link']."' $cs[selected]>$cs[name]</a>";;
				$_tmp = lineValues('search_cate_list', $_line, $cs).$_tmp;
			}
			$_replace_code[$_file_name]['search_cate_list'] = listContentSetting($_tmp, $_line);

			for($level = 4; $level <= 5; $level++) { // 2분류, 3분류 검색
				$_lvcode = $_cate_colname[$level][1];
				$_lvonly = "and `$_lvcode` > 0";
				if($cfg[$_lvcode.'_mng'] != 'Y') continue;

				$_tmp = '';
				$_line = getModuleContent('search_'.$_lvcode.'_list');
				$cres = $pdo->iterator("select $_lvcode, count(*) as total from $tbl[product] p where 1 $prdWhere1 $_lvonly group by $_lvcode");
                foreach ($cres as $cs) {
					$cs['selected'] = $_GET[$_lvcode] == $cs[$_lvcode] ? "class='selected'" : '';
					$cs['link'] = preg_replace('/&?'.$_lvcode.'=[0-9]+/', '', getURL()).'&'.$_lvcode.'='.$cs[$_lvcode];
					$cs['name'] = getCateName($cs[$_lvcode]);
					$cs['name'] = "<a href='".$cs['link']."' $cs[selected]>".$cs['name']."</a>";
					$cs['total'] = number_format($cs['total']);
					$_tmp .= lineValues('search_'.$_lvcode.'_list', $_line, $cs);
					$cate_total[$_lvcode] += $cs['total'];
				}
				$cs['name'] = trim($_skin[$_lvcode.'_total_name']);
				if($cs['name']) {
					$cs['selected'] = $_GET[$_lvcode] < 1 ? "class='selected'" : '';
					$cs['total'] = number_format($cate_total[$_lvcode]);
					$cs['link'] = preg_replace('/&?'.$_lvcode.'=[0-9]+/', '', getURL());
					$cs['name'] = "<a href='".$cs['link']."' $cs[selected]>$cs[name]</a>";;
					$_tmp = lineValues('search_'.$_lvcode.'_list', $_line, $cs).$_tmp;
				}
				$_replace_code[$_file_name]['search_'.$_lvcode.'_list'] = listContentSetting($_tmp, $_line);
			}

			$_tmp = "";
			$_line = getModuleContent("search_rank_list");
			$_para1 = ($_skin['search_rank_total']) ? $_skin['search_rank_total'] : 10;
			while($rank = searchRank($_para1)) {
				$rank['ridx'] = $ridx;
				$rank['keyword'] = "<a href=\"".$root_url."/shop/search_result.php?search_str=".$rank['keyword2']."\"><font class=\"search_name\">".$rank['keyword']."</font></a>";
				$_tmp .= lineValues("search_rank_list", $_line, $rank);
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]['search_rank_list'] = $_tmp;
		}
		prdListRESET();
	}

	$_tmp = "";
	$_line = getModuleContent($_fbn."product_list");
	if($_GET['single_module'] == 'prd_list') {
		$page = $_GET['page'] = numberOnly($_GET['module_page']);
	}

	ob_start();
	$_para1 = (!$_skin[$_fbn."product_list_imgw"]) ? $cfg['thumb3_w'] : $_skin[$_fbn."product_list_imgw"];
	$_para2 = (!$_skin[$_fbn."product_list_imgh"]) ? $cfg['thumb3_h'] : $_skin[$_fbn."product_list_imgh"];
	$_para3 = (!$_skin[$_fbn."product_list_cols"]) ? $_cno1['cols'] : $_skin[$_fbn."product_list_cols"];
	$_para3_2 = (!$_skin[$_fbn."product_list_rows"]) ? $_cno1['rows'] : $_skin[$_fbn."product_list_rows"];
	if($_skin_module['cols']) $_para3 = $_skin_module['cols'];
	if($_skin_module['rows']) $_para3_2 = $_skin_module['rows'];
	$rows = $_list_tmp_row ? $_list_tmp_row : $_para3*$_para3_2;
	if($_more_page > 0) {
		${'page'.$_paging_code} = $_GET['page'.$_paging_code] = 1;
		$rows = ($rows*$_more_page);
	}
	if($_GET['rows']) $rows = numberOnly($_GET['rows']);
	$_para4 = (!$_skin[$_fbn."product_list_namecut"]) ? $_cno1['cut_title'] : $_skin[$_fbn."product_list_namecut"];
	$_img_fd = (!$_skin[$_fbn."product_img_fd"]) ? 3 : $_skin[$_fbn."product_img_fd"];
	$_img_fd = ($_skin[$_fbn."product_img_fd"] && $cfg['add_prd_img'] >= $_skin[$_fbn."product_img_fd"]) ? $_skin[$_fbn."product_img_fd"] : $_img_fd;
	$_over_img_fd = (!$_skin[$_fbn."over_product_img_fd"]) ? "N" : $_skin[$_fbn."over_product_img_fd"];
	$_over_img_fd = ($_skin[$_fbn."over_product_img_fd"] != "N") ? $_skin[$_fbn."over_product_img_fd"] : "N";
	$_rollover = ($_over_img_fd != "N") ? $_over_img_fd : "";
	$distinct_sc = $_skin['distinct_sc'];
	$prdlist_disable_tr = ($_skin[$_fbn.'product_disable_tr'] == 'Y') ? true : false;
	//상품목록(이미지가로,세로,한줄수,조건절,제목줄임,총행,페이지블럭(안쓰면 X),이미지번호(1~3),정렬(디비쿼리),분류정보원할경우 분리값)
	while($prd = prdList($_para1,$_para2,$_para3,$prdWhere,$_para4,$rows,10,$_img_fd,$prdOrder,"&gt;")){
		$prd['nidx'] = $nidx;
		$prd['content1'] = stripslashes($prd['content1']);

		echo lineValues($_fbn."product_list", $_line, $prd, "", 1);

		if(isset($productIds) == false || is_array($productIds) == false) {
			$productIds = array(); // 크리테오에서 사용. 차후 다른 서비스에서도 이용 할수 있기에 조건문 없이 처리.
		}
		if(count($productIds) < 3) {
			$productIds[] = $prd['hash'];
		}
	}
	$_tmp = ob_get_contents();
	ob_end_clean();

	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][$_fbn."product_list"] = $_tmp;

	$_replace_code[$_file_name][$_fbn."total_product"] = $NumTotalRec;

    // 다음 페이지 주소
    $_page = (int) $_GET['page'];
    if (!$_page) $_page = 1;
    $_replace_code[$_file_name]['nextpage_link'] = ($PagingInstance->end > $_page)
                    ? $_SERVER['SCRIPT_NAME'].makeQueryString(true, 'page').'&page='.($_page+1) : '';

	prdListRESET();

	$prdlist_disable_tr = false;

	if($_cno1['upfile1']) {
		$_replace_code[$_file_name]['cate_upfile1'] = getFileDir($_cno1['updir']).'/'.$_cno1['updir'].'/'.$_cno1['upfile1'];
	}

?>