<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑몰 상품 리스트 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	if(defined("_wisa_shop_lib_included")) return;
    define("_wisa_shop_lib_included",true);

	include $engine_dir."/_engine/include/shop2.lib.php";

    function mwhere($t = '') {
        global $member;

        if ($member['no']) {
            return " and {$t}member_no='{$member['no']}'";
        } else {
            return " and {$t}guest_no='{$_SESSION['guest_no']}'";
        }
    }

	// 분류 정보
	function getCateInfo($cno) {
		$cno = numberOnly($cno);
		$_cate = get_info($GLOBALS['tbl']['category'], 'no', $cno);
		if(!$_cate['no']) msg(__lang_shop_error_nocate__, $GLOBALS['root_url']);

		if($_cate['level'] == 1) {
			$_cate[101]=$_cate['no'];
		}
		elseif($_cate['level'] == 2) {
			$_cate[102]=$_cate['no'];
			$_cate[101]=$_cate['big'];
		}
		elseif($_cate['level'] == 3) {
			$_cate[103]=$_cate['no'];
			$_cate[102]=$_cate['mid'];
			$_cate[101]=$_cate['big'];
		}
		elseif($_cate['level'] == 4) {
			$_cate[104]=$_cate['no'];
			$_cate[103]=$_cate['small'];
			$_cate[102]=$_cate['mid'];
			$_cate[101]=$_cate['big'];
		}

		$_cate['name'] = stripslashes($_cate['name']);
		$_cate['add_cont1'] = stripslashes($_cate['add_cont1']);
		$_cate['no_access_msg'] = stripslashes($_cate['no_access_msg']);
		$_cate['code'] = $_cate['code'];
		if(!$_cate['width']) $_cate['width'] = 100;
		if(!$_cate['height']) $_cate['height'] = 100;

		return $_cate;
	}

	// 상품 목록 초기화
	function prdListRESET() {
		$GLOBALS['nidx'] = 0;
		$GLOBALS['prdRes'] = 0;
	}

	// 이미지가로,세로,조건절,제목줄임,총데이터,이미지번호,정렬,TR사이꾸밈
	function prdListSpecial($w,$h,$where,$title_cut=0,$row=20,$col_cut=0,$imgn=3,$order_by="edt desc",$tr="",$just_res="",$link_cate="",$prd_maxcnt="") {
		global $NumTotalRec, $tbl, $cfg, $pdo, $prdRes, $PagingInstance, $ori_cno1;

		if(!$prdRes) {
			getTsPrd();

			if(!$order_by) $order_by = $GLOBALS['prdOrder'];
			$ctype = numberOnly($GLOBALS['_ctype']);
			if(
				($ctype == 2 || $ctype == 6 || ($cfg['use_new_sortxy'] == 'Y' && in_array($ctype, array(4, 5)))) ||
				($ori_cno1['ctype'] == 2 || $ori_cno1['ctype'] == 6 || ($cfg['use_new_sortxy'] == 'Y' && in_array($ori_cno1['ctype'], array(4, 5))))
			) {
				$join_sql = " inner join $tbl[product_link] l on p.no=l.pno ";
				$where .= " and l.ctype='$ctype'";
			}

            // 리스트에서 상세 설명 필드 가져오지 않도록 변경
            $fieldset = '';
            $fields = $pdo->iterator("SHOW COLUMNS FROM {$tbl['product']}");
            foreach ($fields as $field) {
                if (preg_match('/content[2-5]/', $field['Field'])) continue;
                if ($fieldset) $fieldset .= ' ,';
                $fieldset .= 'p.'.$field['Field'];
            }

			$sql="select $fieldset from $tbl[product] p $join_sql where 1 $where GROUP BY p.no order by ".$order_by;

			if($GLOBALS['_paging_code'] > 0){
                if ($prd_maxcnt) {
                    $NumTotalRec = $prd_maxcnt;
                } else {
                    $NumTotalRec = $pdo->rowCount($sql);
                }

				if(!defined('_page_inc')) include_once $GLOBALS['engine_dir']."/_engine/include/paging.php";
				$page_code="page".$GLOBALS['_paging_code'];
				$GLOBALS[$page_code] = ($GLOBALS[$page_code]) ? numberOnly($GLOBALS[$page_code]) : 1;
				foreach($_GET as $key=>$val) {
					$QueryString .= ($key != $page_code && !is_array($val)) ? '&'.$key.'='.$val : '';
				}
				$PagingInstance = new Paging($NumTotalRec, $GLOBALS[$page_code], $row, 10);
				$PagingInstance->addQueryString($QueryString);
				$PagingResult=$PagingInstance->result($GLOBALS['pg_dsn']); //
                if ($prd_maxcnt && ($PagingResult["CurrentPage"] * $row > $prd_maxcnt)) {
                    $limitChk = (($PagingResult["CurrentPage"]-1)*$row);
                    if ($prd_maxcnt - $limitChk < 0 || $limitChk > $prd_maxcnt) {
                        return;
                    }
                    $sql.= " limit ".$limitChk.", ".($prd_maxcnt - $limitChk);
                } else {
                    $sql.=$PagingResult['LimitQuery'];
                }
				$GLOBALS[$page_code.'_result'] = preg_replace("/\?page=/", "?".$page_code.'=', $PagingResult['PageLink']);
			}else{
                    if ($prd_maxcnt && ($prd_maxcnt <= $row)) {
                        $sql .= " limit 0,$prd_maxcnt";
                    } else {
                        $sql .= " limit 0,$row";
                    }
			}

			$prdRes = $pdo->iterator($sql);
            if ($prdRes == false) {
                return false;
            }
			$NumTotalRec = $prdRes->rowCount();
			if($just_res) return;
		}
		$data = $prdRes->current();
        $prdRes->next();
		if(!$data['no']) {
			prdListRest($col_cut);
			prdListRESET();
			return;
		}
		$data = prdOneData($data, $w, $h, $imgn, $title_cut, $col_cut, $tr, $cate_info, $link_cate);

		$GLOBALS['nidx']++;
		return $data;
	}

	// 상품 목록
	// ($w,$h,$col_cut,$where,$title_cut=0,$row=20,$block=10,$imgn=3,$order_by="")
	// 이미지가로,세로,한줄수,조건절,제목줄임,총행,페이지블럭(안쓰면 X),이미지번호(1~3),정렬(디비쿼리),분류정보원할경우 분리값,분류정보제외값,레코드가져오는선작업
	function prdList($w,$h,$col_cut=0,$where='',$title_cut=0,$row=20,$block=10,$imgn=3,$order_by="edt_date desc",$cate_info="",$cate_level=0,$just_res="") {
		global $NumTotalRec,$admin, $tbl, $cfg, $_cno1, $_cno2, $pdo, $prdRes, $PagingInstance;
		if(!$prdRes) {
			getTsPrd();

			for($i = 1; $i <= 2; $i++) {
				$ctype = numberOnly(${'_cno'.$i}['ctype']);
				if($ctype == 2 || $ctype == 6 || ($cfg['use_new_sortxy'] == 'Y' && ($ctype == 4 || $ctype == 5))) {
					$prd_join = " inner join $tbl[product_link] l on p.no=l.pno ";
					$where .= " and l.ctype='$ctype'";
					break;
				}
			}

			if(!$row) {
				if($col_cut) $row=$col_cut*3;
				else $row=20;
			}
			if(!$order_by) $order_by=$GLOBALS['prdOrder'];
			if(!$_GET['cno1']) $where.=" and `wm_sc`=0";
			$sql = "select p.* from {$tbl['product']} p $prd_join where 1 $where order by ".$order_by;
			if($GLOBALS['distinct_sc'] == 'Y') {
				$sql="select p.*, if(wm_sc>0, wm_sc, no) as prno from {$tbl['product']} p $prd_join where 1 $where group by prno order by ".$order_by;
			}

			if($block!="X") {
				$page = numberOnly($_GET['page']);
				if($page<=1) $page=1;
				$QueryString.="";

				if($_SESSION['browser_type'] == 'mobile' && !$row) $row=12;
				if($GLOBALS['distinct_sc'] == 'Y') {
					$NumTotalRec = $pdo->row("select count(distinct(if(wm_sc>0, wm_sc, no))) from {$tbl['product']} p $prd_join where 1 $where");
				} else {
					$NumTotalRec = $pdo->row("select count(*) from {$tbl['product']} p $prd_join where 1 $where");
				}
				$PagingInstance=new Paging($NumTotalRec, $page, $row, $block);
				$PagingInstance->addQueryString($GLOBALS['QueryString']);
				$PagingResult=$PagingInstance->result($GLOBALS['pg_dsn']); //
				$sql.=$PagingResult['LimitQuery'];
				//$GLOBALS['pageRes']=preg_replace('/\?page=[^"]+/','javascript:onclick=test();',$PagingResult['PageLink']);
				$GLOBALS['pageRes']=$PagingResult['PageLink'];
			}

			$prdRes = $pdo->iterator($sql);
            if (!$prdRes instanceof Iterator) {
                return false;
            }
			if($just_res) return;
		}

		$data = $prdRes->current();
        $prdRes->next();

		if((!$data['no'])) {
			if($GLOBALS['prdlist_disable_tr'] != true) {
				prdListRest($col_cut);
			}
			prdListRESET();
			return;
		}

		$data=prdOneData($data,$w,$h,$imgn,$title_cut,$col_cut,$tr,$cate_info);

		$GLOBALS['nidx']++;
		return $data;
	}

	function prdOneData($data, $w, $h, $imgn, $title_cut="", $col_cut=0, $tr="", $cate_info="", $link_cate="") {
		global $_cno1, $_rollover, $cfg, $tbl, $member, $mgroup_info, $now, $pdo;

		$data = shortCut($data);

		if(function_exists('addPrdOneData')) {
			$data = addPrdOneData($data);
		}

        // 성인 인증
        if ($data['adult'] == 'Y') {
            require_once __ENGINE_DIR__ . '/_engine/member/kcb/lib.php';
            $is_adult = is_adult();
        }

		// 상품 실 판매가
		$prdCart = new OrderCart(array(
			'is_detail' => true
		));
		if($data['buy_ea'] < 1) $data['buy_ea'] = 1;
		$prdCart->skip_dlv = 'Y';
		$prdCart->addCart($data);
		$prdCart->complete();
		$objCart = $prdCart->loopCart();

		$data['event_prc'] = parsePrice($prdCart->sale2, true);
		$data['timesale_prc'] = parsePrice($prdCart->sale3, true);
		$data['member_prc'] = parsePrice($prdCart->sale4, true);
		$data['pay_prc'] = parsePrice($prdCart->pay_prc, true);
		$data['is_sale'] = ($data['sell_prc'] > $prdCart->pay_prc) ? 'Y' : '';

		$data['name'] = stripslashes($data['name']);
        $data['name2'] = inputText(trim(str_replace("'", '', strip_tags($data['name']))));
		$data['content1'] = stripslashes(preg_replace("/\n/", "", nl2br($data['content1'])));
		if($title_cut>0) $data['name'] = cutStr($data['name'], $title_cut);
		$data['normal_prc'] = $data['normal_prc']>0?parsePrice($data['normal_prc'], true):'';
		$data['sell_prc'] = parsePrice($data['sell_prc'], true);
		$data['member_prc'] = $data['member_prc']>0?parsePrice($data['member_prc'], true):'';
		$data['milage'] = $data['milage']>0?parsePrice($data['milage'], true):'';
		// 참조 가격
		$data['normal_r_prc'] = showExchangeFee($data['normal_prc']);
		$data['sell_r_prc'] = showExchangeFee($data['sell_prc']);
		$data['member_r_prc'] = showExchangeFee($data['member_prc']);
		$data['r_milage'] = showExchangeFee($data['milage']);
		$data['pay_r_prc'] = showExchangeFee($data['pay_prc']);
		$data['event_r_prc'] = showExchangeFee($data['event_prc']);
		$data['timesale_r_prc'] = showExchangeFee($data['timesale_prc']);
		// 할인율
		$data['total_sale_per1'] = $prdCart->getData('total_sale_per1');
		$data['total_sale_per2'] = $prdCart->getData('total_sale_per2');
		$data['total_sale_per3'] = $prdCart->getData('total_sale_per3');

		if(!$_cno1['name']) {
			$_cno1['ctype'] = 1;
			if($link_cate) $_cno1['no'] = $data[$link_cate];
		}

		if($col_cut>0 && $GLOBALS['nidx']%$col_cut==0 && $GLOBALS['nidx']>0 && $GLOBALS['prdlist_disable_tr'] != true) {
			echo "</tr>$tr<tr>";
		}

		if(!$data['link']) {
			$data['link'] = $GLOBALS['root_url']."/shop/detail.php?pno=$data[hash]&rURL=".urlencode($GLOBALS['this_url'])."&ctype=".$_cno1['ctype']."&cno1=".$_cno1['no'];
		}

        // 상품 이미지 목록
        for($i = 1; $i <= $cfg['mng_add_prd_img']+3; $i++) {
            if (!$data['upfile'.$i] && $i != $imgn) continue;

            // 성인 상품 대체 섬네일
            if ($data['adult'] == 'Y' && !$is_adult && $cfg['thumb_adult']) {
                $data['upfile' . $i] = $cfg['thumb_adult'];
                $thumb = setImagesize($cfg['thumb_adult_w'], $cfg['thumb_adult_h'], $w, $h);
                $img[0] = getListImgUrl('_data/_default/prd', $cfg['thumb_adult']);
                $img[1] = $thumb[2];
            } else {
                $img = prdImg($i, $data, $w, $h);
            }
            $data['upfile'.$i.'_tag'] = "<img src=\"$img[0]\" {$img[1]}>";
            $data['upfile'.$i.'_link'] = "<a href=\"{$data['link']}\">".$data['upfile'.$i.'_tag']."</a>";
            $data['upfile'.$i.'_url'] = $img[0];
			$data['upfile'.$i.'_str'] = $img[1];
        }
		$data['imgstr'] = $data['upfile'.$imgn.'_str'];
        $data['img'] = $data['upfile'.$imgn.'_url'];
		$data['imgr'] = $data['upfile'.$imgn.'_tag'];

        // 상품 롤오버 이미지 설정
		if ($_rollover > 0 && $_rollover != $imgn) {
            if ($data['upfile'.$_rollover]) {
                $data['imgr'] = sprintf(
                    "<img src=\"%s\" class='rover' ".$data['upfile'.$imgn.'_str']." onmouseover=\"this.src='%s';\" onmouseout=\"this.src='%s';\">",
                    $data['upfile'.$imgn.'_url'],
                    $data['upfile'.$_rollover.'_url'],
                    $data['upfile'.$imgn.'_url']
                );
            }
		}

		if($data['stat'] == 2) $data['stack_ok'] = 1;
		elseif($data['stat'] == 3) $data['sold_out'] = "out";

		$data['icons'] = prdIcons($data);

		$data['name_link'] = "<a href=\"".$data['link']."\">".$data['name']."</a>";
		$data['imgr_link'] = "<a href=\"".$data['link']."\">".$data['imgr']."</a>";
		$data['icons_link'] = "<a href=\"".$data['link']."\">".$data['icons']."</a>";
		$data['sell_prc_link'] = "<a href=\"".$data['link']."\">".$data['sell_prc']."</a>";
		$data['wish_link'] = "<a href='#' onclick='wishPartCartAjax(\"$data[hash]\", this); return false;' ".(($data['is_wish'] == 'on') ? 'class="wish_on"' : '').">";
        $data['cart_link'] = "<a href='#' onclick='cartPartCartAjax(\"$data[hash]\", \"".htmlspecialchars($data['name2'])."\", \"{$data['sell_prc']}\"); return false;'>";
		$data['dlv_alone'] = ($data['dlv_alone'] == 'Y') ? 'Y' : '';
		$_sns_title = urlencode(iconv(_BASE_CHARSET_, "UTF-8", $data['name']." → ".$data['sell_prc'].__currency__));

        // 성인 인증
        if ($data['adult'] == 'Y' && !$is_adult) {
            $is_null = (is_null($is_adult)) ? 'false' : 'true';
            $data['name_link'] = "<a href='#' onclick=\"memberCert('{$data['link']}', $is_null)\">" . $data['name'] . "</a>";
            $data['imgr_link'] = "<a href='#' onclick=\"memberCert('{$data['link']}', $is_null)\">" . $data['imgr'] . "</a>";
            $data['icons_link'] = "<a href='#' onclick=\"memberCert('{$data['link']}', $is_null)\">" . $data['icons'] . "</a>";
            $data['sell_prc_link'] = "<a href='#' onclick=\"memberCert('{$data['link']}', $is_null)\">" . $data['sell_prc'] . "</a>";
        }

		$_sns_link = urlencode($data['link']);
		$_sns_links[1] = "http://twitter.com/intent/tweet?text=".$_sns_title."&url=".$_sns_link;
		$_sns_links[2] = "http://www.facebook.com/sharer/sharer.php?u=".urlencode($GLOBALS['root_url']."/shop/detail.php?pno=".$data['hash']);
		$data['sns_twitter_url'] = $_sns_links[1];
		$data['sns_facebook_url'] = $_sns_links[2];

		$data['link_pop'] = "javascript:quickDetailPopup(this, $data[no], '$_cno1[no]');";
		$data['link_frame'] = $data['link']."#$data[no]_frame";

		if ($cfg['use_prc_consult'] == 'Y') {
			// 가격협의
			if($data['sell_prc_consultation']!='' ) $data['sell_prc'] =  stripslashes($data['sell_prc_consultation']);
			if($data['sell_prc_consultation']!='' ) $data['sell_prc_link'] = "<a href=\"".$data['link']."\">".stripslashes($data['sell_prc_consultation'])."</a>";
		}

		// 타임세일
		$ts = $objCart->getData('ts');
		$data['ts_use'] = $ts->use;
		if($ts->use == 'Y' && $ts->datee > 0) {
			$data['ts_timer'] = "<span class='_timesale_timer _timesale_{$data['parent']}' data-timestamp='$ts->datee'><script>printTimeSale($('._timesale_{$data['parent']}'));</script></span>";
		}

		// 오늘출발
		$data['naver_today_start'] = '';
		if($cfg['compare_today_start_use'] == 'Y') {
			$data['naver_today_start'] = ($data['compare_today_start']=='Y') ? 'Y':'';
			$data['naver_today_time'] = $cfg['compare_today_time'].":00";
		}

		if($cfg['use_bs_list_addimg'] == 'Y') {
			$_line = getModuleContent('product_all_image_list');
			$_tmp = lineValues("product_all_image_list", $_line, array(
				'url' => $data['img'],
				'link' => $data['link'],
			), 'common_module');
			$ires = $pdo->iterator("select updir, filename from {$tbl['product_image']} where pno='{$data['parent']}' and filetype in (2, 8) order by sort asc, no desc");
			foreach ($ires as $idata) {
				$idata['url'] = getListImgURL($idata['updir'], $idata['filename']);
				$idata['link'] = $data['link'];
				$_tmp .= lineValues("product_all_image_list", $_line, $idata, 'common_module');
			}
			$data['add_imgs'] = listContentSetting($_tmp, $_line);
		}

		// 컬러칩 출력
		if(isset($cfg['use_colorchip_cache']) == true && $cfg['use_colorchip_cache'] == 'Y') {
			global $_colorchips_data;
			if($data['colorchip_cache']) {
				// 캐시 생성
				if(isset($_colorchips_data) == false) {
					$_res = $pdo->iterator("select * from {$tbl['product_option_colorchip']}");
                    foreach ($_res as $_data) {
						$_colorchips_data[$_data['no']] = array(
							'type' => $_data['type'],
							'name' => stripslashes($_data['name']),
							'code' => $_data['code'],
							'url' => ($_data['type'] == 'file') ? getListImgURL($_data['updir'], $_data['upfile1']) : ''
						);
					}
				}

				// 스킨 출력
				$_colorchip_idx = explode(',', $data['colorchip_cache']);
				$_tmp = '';
				$_line = getModuleContent('product_colorchip_list');
				foreach($_colorchip_idx as $_idx) {
					$_data = $_colorchips_data[$_idx];
					$_tmp .= lineValues('product_colorchip_list', ($_data['type'] == 'file') ? $_line[2] : $_line[5], $_data);
				}
				$data['product_colorchip_list'] = listContentSetting($_tmp, $_line);
			}
		}

		$data['hit_wish'] =  number_format($data['hit_wish']);
		$data['rev_avg_round'] = floor($data['rev_avg']);
		$data['sell_prc_consultation_use'] = ($data['sell_prc_consultation']) ? '' : 'Y';

        // 상품평평균평점
        $_rev_avg = $pdo->row("select AVG(rev_pt) as avg_rev_pt from {$tbl['review']} where pno='{$data['parent']}'");
        $data['detail_review_avg'] = round($_rev_avg, 1);

		return $data;
	}

	// 상품 목록 table 마지막 td 채우기
	function prdListRest($col_cut="",$blank="&nbsp;") {
		if(!$col_cut) return;
		if($GLOBALS['prdRes']) $GLOBALS['prdRes']="";

		if($GLOBALS['prdlist_disable_tr'] == true) return;
		$per=round(100/$col_cut);
		while($GLOBALS['nidx']%$col_cut!=0) {
			echo "<td width=\"$per%\" class=\"empty_cell\">$blank</td>";
			$GLOBALS['nidx']++;
		}
	}

	// 상품 - 카테고리에 따른 조건절
	function prdWhereByCate($cate) {
		global $_cate_colname;
		$col_name=$_cate_colname[$cate['ctype']][$cate['level']];
		$cate['no'] = numberOnly($cate['no']);
		if(!$cate['no']) $r="";
		else {
			if($col_name=="ebig"||$col_name=="mbig") {
				$r=" and l.nbig='$cate[no]'";
			}
			else {
				$r=" and `".$col_name."`=$cate[no]";
			}
		}
		return $r;
	}

	// 상품 이미지
	function prdImg($n,$data,$w,$h,$noimg=""){
		global $root_url,$root_dir,$cfg,$dw,$dh,$_use,$file_server,$file_server_num;

		if($n>3 && !$data["upfile".$n]) $n=3;

		if($data["upfile".$n]) {
			$img="/".$data['updir']."/".$data["upfile".$n];
		}
		else {
			if($n>0) {
				if($noimg) $img=$noimg;
				else $img=$cfg["noimg".$n];
			}
			else {
				$img=$cfg["noimg".$n];
				$data["w".$n]=$w;
				$data["h".$n]=$h;
			}
		}

		if($img) {
			if(!$data["w".$n] || !$data["h".$n]) {
				list($data["w".$n],$data["h".$n])=@GetImageSize($root_dir.$img);
			}

			if($data["w".$n] && $data["h".$n]) {
				$is=setImageSize($data["w".$n],$data["h".$n],$w,$h);
				$imgstr=$is[2];
			}

			$file_dir = ($cfg['use_icb_storage'] == 'Y' && $data['upurl']) ? $data['upurl'] : getFileDir($data['updir']);
			$img=$file_dir.$img;
		}
		return array($img,$imgstr);
	}

	// 하위 분류 정보
	function getSCate($level,$sub) {
		global $_cate, $db;
		$data=$pdo->assoc("select * from `".$GLOBALS[tbl][category]."` where `level`='$level' and `$sub`='$_cate[$sub]'");
		return $data;
	}

	// 분류별 상품수
	function totalCatItem($where) {
        global $tbl, $pdo;

		$sql="select count(*) from {$tbl['product']} p where 1 and `stat` in (2,3) $where";
		$data = $pdo->row($sql);
		if($_GET[vsql]) {
			echo $sql."<hr>";
		}
		return $data;
	}

	function _getOrdStat($data,$ostat="") {
		global $_order_stat,$_order_color;
		if(!$ostat) {
			$ostat=$data['stat'];
		}

        $r = (defined('__lang_order_stat'.$ostat.'__') == true) ?
            constant('__lang_order_stat'.$ostat.'__') :
            $_order_stat[$ostat];

		if($_order_color[$ostat]) {
			$r="<font color=\"".$_order_color[$ostat]."\">".$r."</font>";
		}

		$dlv=getDlvUrl($data);

		if($data[dlv_code] && $ostat==4) {
			$r="<a href=\"$dlv[url]\" target=\"_blank\">".$r."</a>";
		}

		return $r;
	}

	// 주문 내역
	function orderList($addOrdWhere="") {
		global $member,$_order_stat, $dlv,$_pay_type, $tbl, $cfg, $root_url, $tbl, $orderRes, $pdo;

		if(!$orderRes) {
			$orderRes = $pdo->iterator("select * from {$tbl['order']} where `stat`  not in (11, 31, 32) and `member_no`='$member[no]' and `member_id`='$member[member_id]' ".$GLOBALS[orderWhere]." $addOrdWhere order by `date1` desc"); // 2007-02-15 member_id 추가
			$GLOBALS['oidx'] = $pdo->row("select count(*) from {$tbl['order']} where `stat`  not in (11, 31, 32) and `member_no`='$member[no]' and `member_id`='$member[member_id]' ".$GLOBALS[orderWhere]." $addOrdWhere")+1;
		}
		if (is_object($orderRes) == false) {
            return false;
        }
        $data = $orderRes->current();
        $orderRes->next();
        if($data == false) {
            unset($orderRes);
            return false;
		}

		// 이니에스크로 수취확인
		if($data['pay_type'] != 2 && $data['stat'] == 4) {

			$card_tbl=($data['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
			$card=get_info($card_tbl, 'wm_ono', $data['ono']);

			if($card['pg'] == 'inicis' && $card['pg_version']== 'INILite') {
				$data['escrow_id']=$card['tno'];
				$data['escrow_type']='ini_escrow';
			}
		}

		// 배송중일때 수취확인, 배송조회
		if($data['stat'] == 4 && !preg_match('/@(12|14|16|18)@/', $data['stat2'])) {
			$data['receive']="<a href=\"javascript:receiveProduct('$data[ono]','$data[escrow_type]','$data[escrow_id]');\">";
			$dlv = getDlvUrl($data);
			$data['delivery_link'] = "<a href='$dlv[url]' target='_blank'>";
		} else {
			$data['receive']="<wisamall2006 ";
			$data['delivery_link'] = '';
		}

		// 부분 배송
		$stat2 = preg_replace('/^@|@$/', '', $data['stat2']);
		if($cfg[dlv_part]=="Y" && count($stat2) > 1) {
			$stats = explode('@', $stat2);
			$stats = array_unique($stats);
			foreach($stats as $key => $val) {
				$stats[$key] = _getOrdStat($data, $val);
			}
			$data['stat'] = implode('<br />', $stats);
		}
		else {
			$data['stat']=_getOrdStat($data);
		}

		$data['date1']=date("Y/m/d",$data['date1']);
		$data['o_total_prc']=$data['total_prc'];
		$data['total_prc']=parsePrice($data['total_prc'], true);
		$data['total_r_prc']=showExchangeFee($data['total_prc']);
		$data['link']=$root_url."/mypage/order_detail.php?ono=".$data['ono'];
		$data['pay_type_str']=$_pay_type[$data['pay_type']];

		$dlv=getDlvUrl($data);
		$GLOBALS['oidx']--;
		return $data;
	}

	// 주문 상세 장바구니 내역
	function orderCartList($split_big="/",$split_small=":",$opt_deco1="",$opt_deco2="",$w=0, $h=0) {
		global $ord, $orderCartRes, $pdo, $cfg;
		if(!$orderCartRes) {
			if ($cfg['use_partner_delivery'] == 'Y') {
				$orderby = 'partner_no asc, no asc';
			} else {
				$orderby = 'no asc';
			}
            if ($cfg['use_set_product'] == 'Y') { // 세트별 정렬
                $orderby = 'set_idx asc, '.$orderby;
            }
			$orderCartRes = $pdo->iterator("select * from {$GLOBALS['tbl']['order_product']} where `ono`='{$ord['ono']}' order by $orderby");
		}
		if (is_object($orderCartRes) == false) {
            return false;
        }
        $data = $orderCartRes->current();
        $orderCartRes->next();
        if($data == false) {
            unset($orderCartRes);
            return false;
        }

		$data['name']=stripslashes($data['name']);
		$prd=get_info($GLOBALS['tbl']['product'],"no",$data['pno']);
		//if($prd[no] && ($prd['stat']=="2" || $prd['stat']!="3")) {
		if($prd['no'] &&  ($prd['stat']=="2" || $prd['stat']=="3")) {
			$data['plink']=$GLOBALS['root_url']."/shop/detail.php?pno=".$prd['hash'];

			$img=prdImg(3,$prd,$w,$h);
			$data['img']=$img[0];
			$data['imgstr']=$img[1];
			$data['delivery_set'] = $prd['delivery_set'];
		}
		else {
			$data['plink']="javascript:noPrd();";
			$data['imgstr']="width=\"0\" height=\"0\"";
		}

		$dlv = getDlvUrl($data);
	 	$data['dlv_url'] = $dlv['url'];
		$data['dlv_name'] = $dlv['name'];
		$data['option_str'] = ''; //초기화
		if($data['option']) {
			$data['option_str']=str_replace("<split_big>",$split_big,$data['option']);
			$data['option_str']=str_replace("<split_small>",$split_small,$data['option_str']);
			$data['option_str']=$opt_deco1.$data['option_str'].$opt_deco2;
		}

		// 기타메세지 추가
		$data['etc'] = ($data['etc']?stripslashes($data['etc']):'');

		$data['milage'] = parsePrice($data['milage'], true);
		$data['member_milage'] = parsePrice($data['member_milage'], true);
		$data['total_milage'] = parsePrice($data['total_milage'], true);

		$data['sell_prc']=parsePrice($data['sell_prc'], true);
		$data['milage_c']=$data['milage'];
		$data['total_prc']=parsePrice($data['total_prc'], true);
		$data['r_milage']=showExchangeFee($data['milage']);
		$data['sell_r_prc']=showExchangeFee($data['sell_prc']);
		$data['total_r_prc']=showExchangeFee($data['total_prc']);
		$data['prd_dlv_prc'] = parsePrice($data['prd_dlv_prc'], true);

		return $data;
	}

	function prdSortParse($deco1="",$deco2="") {
        global $pdo, $sortRes;

		if(!$sortRes) {

			if($_SESSION['browser_type'] == 'mobile') $notIn=" and `name` not in ('판매량높은순', '판매량낮은순', '조회수높은순', '조회수낮은순', '고객평가순') ";

			$sortRes = $pdo->iterator("select * from {$GLOBALS['tbl']['product_sort']} where `use`='Y' and `real_use`='Y' order by `sort`");
		}
		$data = $sortRes->current();
        $sortRes->next();
		if(!$data['no']) return;
		if($GLOBALS['sort'] == $data['no']) {
			$data['name'] = $deco1.$data['name'].$deco2;
			$data['checked'] = 'checked';
		}
		$data['link'] = $_SERVER['PHP_SELF']."?sort=".$data['no'].$GLOBALS['sort_list_query'];

		return $data;
	}

	function ctrlPrdHit($pno,$hit_col,$hit) {
		global $cfg, $tbl, $pdo;

		if(empty($pno) == true) return false;

		$log_qry = "update $tbl[product] set $hit_col=$hit_col $hit where no='$pno' or wm_sc='$pno'";
		if($cfg['use_log_scheduler'] == 'Y') {
			$log_qry = addslashes($log_qry);
			$pdo->query("insert into {$tbl['log_schedule']} (query, reg_date) values ('$log_qry', now())");
		} else {
			$pdo->query($log_qry);
		}
	}

	function prdIcons($prd2="") {
		global $root_url,$root_dir,$tbl,$dir,$cfg,$setPrdCk, $_preload_prdicons, $pdo;
		if(!$prd2) $prd2 = $GLOBALS['prd'];

		if(is_array($_preload_prdicons) == false) {
			$_preload_prdicons = array();

			// 아이콘파일 경로
			$conck = fsConFolder($dir['upload'].'/'.$dir['icon']);
			$file_dir = getFileDir($dir['upload'].'/'.$dir['icon']);
			$_preload_prdicons['url'] = $file_dir.'/'.$dir['upload'].'/'.$dir['icon'];

			$iasql = "";
			if($cfg['product_icon_sort']=='Y') {
				$iasql = "order by `sort`";
			}

			// 아이콘파일
			$res = $pdo->iterator("select no, upfile, itype from `$tbl[product_icon]` $iasql");
			foreach ($res as $data) {
				$_preload_prdicons[$data['no']] = $data['upfile'];
				if($data['itype']) $_preload_prdicons['itype'.$data['itype']] = $data['upfile'];
			}
		}

		$icons = explode('@', $prd2['icons']);
		if($prd2['event_sale'] == 'Y') $icons[] = 'itype2';
		if($prd2['stat'] == 3) $icons[] = 'itype4';
		if($prd2['free_delivery'] == 'Y' && $cfg['delivery_type'] == 3 && $cfg['delivery_prd_free'] == 'Y') $icons[] = 'itype5';
		if($prd2['compare_today_start'] == 'Y' && $cfg['compare_today_start_use'] == 'Y') $icons[] = 'itype6';
		if($prd2['dlv_alone'] == 'Y') $icons[] = 'itype7';

		if(count($icons) < 1) return '';

		$str = '';
		foreach($_preload_prdicons as $key=>$val) {
			if(!in_array($key, $icons)) continue;
			$str .= "<img src='".$_preload_prdicons['url']."/".$val."' align='absmiddle'>";
		}
		return $str;
	}

	function getDlvUrl($data) {
		global $tbl, $dlvcache, $cfg;
		if($data['dlv_no']) {
			if ( $dlvcache[$data['dlv_no']] ) $dlv = $dlvcache[$data['dlv_no']];
			else 	{
				$dlv=get_info($tbl['delivery_url'],"no",$data['dlv_no']);
				$dlvcache[$data['dlv_no']] = $dlv;
			}
			$data['dlv_code'] = str_replace("-", "", $data['dlv_code']);
			$dlv['url']=str_replace("{송장번호}",$data['dlv_code'],$dlv['url']);
			if(empty($cfg['invoice_prv']) == false) $dlv['url'] .= '&invoice_prv='.$cfg['invoice_prv'];

			return $dlv;
		}
	}

	function myCouponList($tp=1,$data="", $options=array()) {
		global $mcouponRes,$tbl,$now,$root_url,$member,$_ord_cpn,$total_order_price,$cpn_array,$cpn_cart,$cidx, $free_delivery,$cfg,$delivery_fee_type, $pdo;

		if($member['attr_no_sale'] == 'Y' || $member['attr_no_coupon'] == 'Y') return; // 특별회원그룹속성
		if(!$data) {
			if(!$mcouponRes) {
				$nowYmd = date("Y-m-d",$now);
				if($tp == 1) { // 사용가능 쿠폰만
					$cw = "";
					if(is_array($cpn_array)) {
						$cpn_array = array_unique($cpn_array);
						foreach($cpn_array as $key=>$val) {
							if(!$val) continue;
							$cw .= " or a.cno='$val'";
						}
						if($cw != "") {
							$cw = substr($cw, 4);
							$cw = "or (a.stype='2' and ($cw))";
						}
					}
					if($free_delivery != "") $stypeWhere = "1,5";
					else{
						$stypeWhere = "1,3,5";
						if($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O') $stypeWhere = "1,4,5";
					}

					$where = "and ((a.udate_type='2' and a.ustart_date<='$nowYmd' and a.ufinish_date>='$nowYmd') or a.udate_type='1' or (a.udate_type='3' and a.ufinish_date >= '$nowYmd'))  and (a.stype in ({$stypeWhere})  $cw) and a.ono=''";
				}
				if($_SESSION['is_wisaapp'] == true) $where .= " and a.device in ('', 'app', 'mobile_all')";
				else if($_SESSION['browser_type'] == 'mobile') $where .= " and a.device in ('', 'mobile', 'mobile_all')";
				else $where .= " and a.device in ('', 'pc')";

				// 개별상품쿠폰
				$where .= ($options['is_prdcpn'] == true) ? ' and a.stype=5' : ' and a.stype!=5';

                // 온라인 쿠폰만 사용 가능
                if ($cfg['use_erp_interface'] == 'Y' && $cfg['erp_interface_name'] = 'dooson') {
                    $where .= " and a.place in ('', 'online')";
                }

				$sql = "select a.*, b.attachtype, b.attach_items from {$tbl['coupon_download']} a inner join {$tbl['coupon']} b on a.cno=b.no where a.member_no='{$member['no']}' and `member_id`='{$member['member_id']}' and b.place!='offline' $where order by a.no desc";
				$mcouponRes = $pdo->iterator($sql);
				$cidx = 0;
			}
			$data = $mcouponRes->current();
            $mcouponRes->next();
		}

		if(!$data['no']) return;

		if($data['udate_type'] == 1) $data['udate_type'] = __lang_shop_info_cpnunlimited__;
		else $data['udate_type'] = "$data[ustart_date] ~ $data[ufinish_date]";

		if($total_order_price >= $data['sale_limit']) {
			$_ord_cpn[] = $data['code'];
		}

		if($data['sale_type'] == "m") $data['sale_limit_k'] = "";
		else $data['sale_limit_k'] = "<br>최대할인금액 ".number_format($data['sale_limit'])." ".$cfg['currency_type'];

		switch($data['sale_type']) {
			case 'm' : $data['sale_type_k'] = __lang_shop_info_cpnUnit1__; break;
			case 'e' : $data['sale_type_k'] = __lang_shop_info_cpnUnit2__; break;
			case 'p' : $data['sale_type_k'] = __lang_shop_info_cpnUnit3__; break;
		}
		$data['onclick'] = "useCpn($cidx,'$data[sale_type]','$data[sale_prc]','$data[prc_limit]','$data[sale_limit]','$data[stype]','$data[no]','$data[use_limit]')";

		$disabled = ($data['pay_type'] == 2) ? "disabled" : "";
		$data['radio'] = "<input type=\"hidden\" id=\"coupon_stype_$data[no]\" value=\"$data[stype]\"><input type=\"hidden\" name=\"coupon_pay_type\" value=\"$data[pay_type]\"><input type=\"radio\" name=\"coupon\" id=\"coupon\" value=\"$data[no]\" onClick=\"$data[onclick]\" $disabled>";
        $data['dataopt'] = "data-cidx=\"{$cidx}\" data-sale_type=\"{$data['sale_type']}\" data-sale_prc=\"{$data['sale_prc']}\" data-prc_limit=\"{$data['prc_limit']}\" data-sale_limit=\"{$data['sale_limit']}\" data-stype=\"{$data['stype']}\" data-no=\"{$data['no']}\" data-use_limit=\"{$data['use_limit']}\" data-pay_type=\"{$data['pay_type']}\" data-cpn_name=\"{$data['name']}\" ";

		if($data['stype'] == 1) {
			$data['select_prd'] = "모든 상품 (총결제액 할인)";
		}
		else {
			if(is_array($cpn_cart)) {
				$data['select_prd'] = "<select name=\"select_prd".$data['no']."\" onChange=\"$data[onclick]\">\n";
				foreach($cpn_cart as $key=>$val) {
					if(!preg_match("/@".$data['cno']."@/",$val['coupon'])) continue;
					$data['select_prd'] .= "<option value=\"".$val['no'].":".$val['sell_prc']*$val['buy_ea']."\">".cutStr(inputText($val['name']),60)."</option>";
				}
				$data['select_prd'] .= "</select>\n";
			}
		}
		$cidx++;
		return $data;
	}

	function offCouponAuth($auth_code=""){
		global $tbl, $cfg, $engine_dir, $member, $pdo;
		$auth_code = trim($auth_code);
		if(!$member[no]) msg(__lang_cpn_error_memberOnly__);
		if(!$auth_code) msg(__lang_cpn_input_authcode__);

		if(!isTable($tbl['coupon_auth_code'])) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['coupon_auth_code']);
		}
		$no = $pdo->row("select cno from $tbl[coupon_auth_code] where auth_code='$auth_code'");
		if(!$no) return false;

		$cpn=get_info($tbl[coupon],"no",$no);
		if(!$cpn[no]) msg(__lang_cpn_error_nocpn__);

		$today=date("Y-m-d");
		if($cpn[udate_type] == 2 && !($cpn[ustart_date] <= $today && $cpn[ufinish_date] >= $today)) msg(__lang_cpn_error_expirecpn__);

		$code=trim($auth_code);

		// 이미 사용된 코드인지 확인
		if($cpn['down_type'] == 'B') {
			if($member['no'] == 0) msg(__lang_cpn_error_memberOnly__);
			$asql = " and `member_no` = '$member[no]'";
		}

		$ck = $pdo->row("select count(*) from `$tbl[coupon_download]` where `ono` != '' and `is_type`='B' and `auth_code`='$code' $asql");
		if($ck) msg(__lang_cpn_error_used__);

		if($cpn['release_limit'] != 1) { // 단일코드쿠폰 한정체크
			$used = $pdo->row("select count(*) from $tbl[coupon_download] where cno='$cpn[no]'");
			if($used >= $cpn['release_limit_ea']) msg(__lang_cpn_error_used__);
		}

		return $cpn;
	}

	function ordGift() {
		global $gift_res,$root_url,$cfg,$member, $file_server;
		if(!$gift_res) return;
		$data = $gift_res->current();
        $gift_res->next();
		if($data == false) {
			return false;
		}

		$file_dir = getFileDir($data['updir']);

		$data['img']=$file_dir."/$data[updir]/$data[upfile]";

		if($cfg['order_gift_multi'] != 'Y') $cfg['order_gift_multi_ea'] = 1;
		$onclick = 'onclick="return checkSelectedGift(this, $cfg[order_gift_multi_ea]);"';

		$selected_gift = explode(',', trim($_POST['selected_gift'], ','));
		$checked = (in_array($data['no'], $selected_gift) == true) ? 'checked' : '';
		if($GLOBALS['total_gift_res'] == 1) $checked = 'checked'; // 검색된 사은품이 한개일때는 강제 체크박스

		if($cfg['order_gift_multi'] == "Y"){
			$data['select']="<input type=\"checkbox\" name=\"gift[]\" class=\"selected_gift\" value=\"$data[no]\" $checked $onclick>";
		}else{
			$data['select']="<input type=\"radio\" name=\"gift[]\" class=\"selected_gift\" value=\"$data[no]\" $checked $onclick>";
		}
		$data['select'] .= "<input type=\"hidden\" name=\"gift_point".$data['no']."\" value=\"".$data['point_limit']."\">";

		return $data;
	}

	// 상품 회원권한별 접근
	function getPrdMyLevel($block="") {
		global $tbl,$member,$admin,$prd, $pdo;

		if($admin['no'] || $member['no'] == 1 || $member['level'] == 1) return;
		if($member['level'] == 10) $q=" and `access_member` != '' and access_member not like 'buy%'";
		else $q=" and `access_member` != '' and (`access_member` not like '%@".$member['level']."@%' and access_member not like 'buy%')";

		$_sql = $pdo->iterator("select `no`,`no_access_msg`, `no_access_page` from {$tbl['category']} where 1".$q);
		$_total = $pdo->row("select count(*) from {$tbl['category']} where 1".$q);

		if($_total < 1) return;
		$_fd=array("big", "mid", "small", "obig", "omid", "ebig", "xbig", "ybig");
		if($block && $prd){ // detail 페이지에서 막기
			$_prdck="";
			for($ii=0; $ii<sizeof($_fd); $ii++){
				if($ii == 0) $_prdck .= "@";
				if($prd[$_fd[$ii]]) $_prdck .= $prd[$_fd[$ii]]."@";
			}
		}
		$r=" not in (";
		$c=0;
        foreach ($_sql as $_cate) {
			if($c > 0) $r .= ", ";
			$r .= "'".$_cate[no]."'";
			if($_prdck != ""){
				$deny_msg = $_cate['no_access_msg'] ? $_cate['no_access_msg'] : __lang_shop_info_denyPrd__;
				if(strchr($_prdck,"@".$_cate[no]."@")) {
					if($_GET['type'] == 'popup' && $_GET['striplayout']) {
						alert($deny_msg);
						javac("parent.removequickDetailPopup();");
					} else {
                        $repage = ($_cate['no_access_page']) ? $_cate['no_access_page'] : 'back';
						msg($deny_msg,$repage);
					}
				}
			}
			$c++;
		}
		$r .= ")";
		$_r="";
		for($ii=0; $ii<sizeof($_fd); $ii++){
			if($ii == 0) $_r .= " and (`big`".$r;
			else $_r .= " and `".$_fd[$ii]."`".$r;
		}
		$_r .= ")";
		return $_r;
	}

	function getPrdBuyLevel($prd) {
		global $tbl, $_cate_colname, $member, $admin, $pdo;

		if($admin['no'] || $member['no'] == 1 || $member['level'] == 1) return;

		$prd2 = prdOneData($prd, null, null, 3);

		$prd2['pno'] = $prd['no'];
		$all_cno = getPrdAllCates($prd2);
		$all_cno = implode(',', $all_cno);
		if($all_cno) {
			$cres = $pdo->iterator("select access_member, no_buy_msg from {$tbl['category']} where no in ($all_cno)"); // 접근 불가 고객은 구매도 불가하므로 전부 체크
            foreach ($cres as $cdata) {
				if(empty($cdata['access_member']) == true) continue;
				$msg = stripslashes($cdata['no_buy_msg']) ? stripslashes($cdata['no_buy_msg']) : __lang_shop_info_denyMember__;

				$acc = explode('@', preg_replace('/^(buy)?@|@$/', '', $cdata['access_member']));
				if(in_array($member['level'], $acc) == false) return $msg;
			}
		}
	}

	function cateNavigator($parent = 0, $ctype = 1, $cutstr = 0, $multi = null) {
		global $naviSql, $tbl, $root_url, $_cate_colname;

		if (!$naviSql) {
			if ($parent) {
				$c = getcateinfo($parent);
				if ($c[level] > 1) {
					$cname = $_cate_colname[$c[ctype]][$c[level]-1];
					$cpw = " and `$cname` = '$parent'";
				}
			}
			$naviSql = $pdo->iterator("select * from `$tbl[category]` where `ctype` = '$ctype' $cpw order by `sort` asc");
		}

		$cdata = $naviSql->current();
        $naviSql->next();

		if (!$cdata) return;

		$querystring = (!$multi) ? "cno1=$cdata[no]" : "cno1=$multi&cno2=$cdata[no]";
		$cdata[link] = $root_url."/shop/big_section.php?".$querystring;
		$cdata[name] = stripslashes($cdata[name]);
		if ($cutstr) $cdata[name] = cutstr($cdata[name], $cutstr);

		return $cdata;
	}

	// 데이콤 현금영수증 자동 발급
	function cashReceiptAuto($ord, $stat, $ext=''){
		global $tbl, $cfg, $engine_dir, $now, $admin, $root_url, $root_dir, $dir, $pdo;

        require_once __ENGINE_DIR__.'/_engine/include/migration/cfg_cash_receipt_taxfree.inc.php';

		if(is_array($ord['cash_no']) == false && $cfg['cash_receipt_auto'] != 'Y') return;

		// 사용여부 / 데이콤
		if($cfg['cash_receipt_use'] == "N" || $cfg['cash_r_pg'] != "dacom" || !$cfg['cash_dacom_key']) {
			if(is_array($ord['cash_no']) == false) {
				return;
			}else {
				msg("현금영수증 발급 필수정보 부족1");
			}
		}
		if(is_array($ord['cash_no']) == false) {
			if(!is_array($ord)) {
				$ono = $ord;
				unset($ord);
				$ord['ono'] = $ono;
				$ord['pay_type'] = $pdo->row("select pay_type from {$tbl['order']} where `ono` = '$ono'");
			}
			if(!$ord['ono'] || $ord['pay_type'] != 2) return;
			if(!$_SESSION['approval_no']) {
				addField($tbl['cash_receipt'],"chk_approval_no","VARCHAR(30) NOT NULL default ''");
				$_SESSION['approval_no'] = "Y";
			}
			$data = $pdo->assoc("select * from {$tbl['cash_receipt']} where `ono`='{$ord['ono']}' order by no desc limit 1"); // 현금영수증 신청서 조회
			if(!$data['no']) return;

			// 발급
			if(!$cfg['cash_receipt_stat']) $cfg['cash_receipt_stat'] = 2;
			if($stat >= $cfg['cash_receipt_stat'] && $stat < 6 && $data['stat'] != 2) $ext = 1;
			if($stat == 13 || $stat == 15 || $stat == 17 || ($stat < $cfg['cash_receipt_stat'])) {
                if ($data['stat'] == 2 && $data['mcht_name'] == "auto") $ext = 2;
                if ($data['stat'] == 1) return; // 설정한 발급 시점보다 이전 stat이고 & 현금영수증 '신청' 단계이면 return
            }

			if(!$ext) return;

			$auto = "Y";
			$_cash_no = array($data['no']);
		} else {//관리자
			if(!$ext) msg("현금영수증 발급 필수정보 부족2");
			$auto = "";
			foreach($ord['cash_no'] as $key=>$val) {
				$_cash_no[] = numberOnly($val);
			}
		}

		//exe 페이지
		$_issue_stat = $cfg['cash_receipt_stat'] ? $cfg['cash_receipt_stat'] : 2;

		$cash_count = 0;
		foreach($_cash_no as $key=>$val) {
			$val = numberOnly($val);
			$data = $pdo->assoc("select * from {$tbl['cash_receipt']} where no='$val'");

            if (preg_match('/^SS/', $data['ono']) == true) {
                $pdo->query("update {$tbl['cash_receipt']} set stat=? where no=?", array(
                    $ext, $data['no']
                ));
                return 1;
            }

			if($ext == 1 && $data['stat'] == 2) continue;
			$_ostat = '';
			$_ostat = ($data['mcht_name'] == "indv") ? 5 : $pdo->row("select stat from {$tbl['order']} where `ono`='{$data['ono']}'");

			if($ext == 1 && !($_ostat >= $_issue_stat && $_ostat < 6)) continue;
			if($ext == 2 && $data['stat'] == 1) {
				$pdo->query("update {$tbl['cash_receipt']} set stat='3' where `no`='{$data['no']}'");
                cashReceiptLog(array(
                    'cno' => $data['no'],
                    'ono' => $data['ono'],
                    'stat' => 3,
                    'ori_stat' => 1,
                ));
				$cash_count++;
				continue;
			}

			$biz_num = numberOnly($cfg['company_biz_num']);
			$biz_num = ($data['b_num']) ? $data['b_num'] : $biz_num;

			$_dacom_mid = $cfg['cash_dacom_id'];
			$_dacom_mert_key = $cfg['cash_dacom_key'];

			// 로그 및 mall.conf 설치
			include_once $engine_dir."/_manage/config/cash_receipt_dacom.php";

			// 타 상점과의 주문번호 충돌 방지
			if(defined('use_cash_receipt_prefix') == true) {
				$ono = trim(addslashes($data[ono]));
				$ord = $pdo->assoc("select date1 from {$tbl['order']} where ono='$ono'");
				$ono_prefix = $ord['date1'].'_';
			}

			$data['prod_name'] = iconv(_BASE_CHARSET_, 'euc-kr', $data['prod_name']);
			$company_name = iconv(_BASE_CHARSET_, 'euc-kr', $cfg['company_name']);

            if (isset($data['taxfree_amt']) == false) $data['taxfree_amt'] = 0; // 비과세
			if ($ext == 3) $ext = 1;
			ob_start();
			$CST_PLATFORM               = "service";       								//LG텔레콤 결제 서비스 선택(test:테스트, service:서비스)
			$CST_MID                    = addslashes($cfg['cash_dacom_id']);            //상점아이디(LG텔레콤으로 부터 발급받으신 상점아이디를 입력하세요)
																						//테스트 아이디는 't'를 반드시 제외하고 입력하세요.
			$LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;  //상점아이디(자동생성)
			$LGD_TID                	= ($ext == 2) ? $data['mtrsno'] : "";			//LG텔레콤으로 부터 내려받은 거래번호(LGD_TID)

			$LGD_METHOD   		    	= ($ext == 1) ? "AUTH" : "CANCEL";              //메소드('AUTH':승인, 'CANCEL' 취소)
			$LGD_OID                	= $data['ono'];									//주문번호(상점정의 유니크한 주문번호를 입력하세요)
			$LGD_PAYTYPE                = "SC0100";										//결제수단 코드 (SC0030:계좌이체, SC0040:가상계좌, SC0100:무통장입금 단독)
			$LGD_AMOUNT     		    = $data['amt1'];            					//금액("," 를 제외한 금액을 입력하세요)
			$LGD_CASHCARDNUM        	= $data['cash_reg_num'];						//발급번호(주민등록번호,현금영수증카드번호,휴대폰번호 등등)
			$LGD_CUSTOM_MERTNAME 		= addslashes($company_name);    				//상점명
			$LGD_CUSTOM_BUSINESSNUM 	= $biz_num;    //사업자등록번호
			$LGD_CUSTOM_MERTPHONE 		= numberOnly($cfg['company_phone']);    		//상점 전화번호
			$LGD_CASHRECEIPTUSE     	= "1";											//현금영수증발급용도('1':소득공제, '2':지출증빙)
			$LGD_PRODUCTINFO        	= addslashes($data['prod_name']);				//상품명(무통장입금 단독 발행건만 입력)
			$LGD_TID        			= ($ext == 2) ? $data['mtrsno'] : "";			//텔레콤 거래번호

			$configPath 				= $engine_dir."/_engine/cash.dacom";			//LG텔레콤에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

			require_once($configPath."/XPayClient.php");
			$xpay = new XPayClient($configPath, $CST_PLATFORM);
			$xpay->Init_TX($LGD_MID);
			$xpay->Set("LGD_TXNAME", "CashReceipt");
			$xpay->Set("LGD_METHOD", $LGD_METHOD);
			$xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

			if($LGD_METHOD == "AUTH") {					// 현금영수증 발급 요청
				$xpay->Set("LGD_OID", $ono_prefix.$LGD_OID);
				$xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
				$xpay->Set("LGD_CASHCARDNUM", $LGD_CASHCARDNUM);
				$xpay->Set("LGD_CUSTOM_MERTNAME", $LGD_CUSTOM_MERTNAME);
				$xpay->Set("LGD_CUSTOM_BUSINESSNUM", $LGD_CUSTOM_BUSINESSNUM);
				$xpay->Set("LGD_CUSTOM_MERTPHONE", $LGD_CUSTOM_MERTPHONE);
				$xpay->Set("LGD_CASHRECEIPTUSE", $LGD_CASHRECEIPTUSE);
                $xpay->Set('LGD_TAXFREEAMOUNT', $data['taxfree_amt']);

				if($LGD_PAYTYPE == "SC0030") {				//기결제된 계좌이체건 현금영수증 발급요청시 필수
					$xpay->Set("LGD_TID", $LGD_TID);
				}else if($LGD_PAYTYPE == "SC0040") {			//기결제된 가상계좌건 현금영수증 발급요청시 필수
					$xpay->Set("LGD_TID", $LGD_TID);
					$xpay->Set("LGD_SEQNO", "001");
				}else {										//무통장입금 단독건 발급요청
					$xpay->Set("LGD_PRODUCTINFO", $LGD_PRODUCTINFO);
				}
			}else {											// 현금영수증 취소 요청
				$xpay->Set("LGD_TID", $LGD_TID);
				if($LGD_PAYTYPE == "SC0040") {				//가상계좌건 현금영수증 발급취소시 필수
					$xpay->Set("LGD_SEQNO", "001");
				}
			}

			$result['stat'] = "";
			$result['msg'] = "";
			if($xpay->TX()) {
				if(!fieldExist($tbl['cash_receipt'], 'msg')) {
					addField($tbl['cash_receipt'],"msg","VARCHAR(100) NOT NULL");
				}
				$LGD_RESPCODE = $xpay->Response("LGD_RESPCODE", 0);
				if($LGD_RESPCODE == "0000") {
					$result['stat'] = "ok";
					$stat = ($LGD_METHOD == "AUTH") ? 2 : 3;
					$authno = $xpay->Response("LGD_RESPCODE", 0);
					$mtrsno = $xpay->Response("LGD_TID", 0);
					if($ext == 1){
						$chk_approval_no = $xpay->Response("LGD_CASHRECEIPTNUM", 0);
					} else {
						$chk_approval_no = '취소';
					}
					// 2010-07-22 : 실행 날짜 저장 - Han
					$tsdtime = $xpay->Response("LGD_RESPDATE", 0);
					$tsdtime = $tsdtime ? strtotime(preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1-$2-$3 $4:$5:$6", $tsdtime)) : "";
					$mtrs_q = ($mtrsno) ? ", `mtrsno`='".$mtrsno."'" : "";
					$mtrs_q .= ($tsdtime) ? ", `tsdtime`='".$tsdtime."'" : "";
					$mtrs_q .= ($auto == "Y") ? ", `mcht_name`='auto'" : "";
					$mtrs_q .= ($biz_num) ? ", `b_num`='".$biz_num."'" : "";
					$mtrs_q .= ($chk_approval_no) ? ", `chk_approval_no`='".$chk_approval_no."'" : "";
					if($data['msg']) {
						$mtrs_q .= ", `msg`=''";
					}

					$pdo->query("update `$tbl[cash_receipt]` set `stat`='$stat', `authno`='".$authno."' ".$mtrs_q." where `no`='$data[no]'");
					$cash_count++;
				}else {
					$result['msg'] = addslashes(iconv('euc-kr', _BASE_CHARSET_, $xpay->Response_Msg()));
					$pdo->query("update `$tbl[cash_receipt]` set `msg`='$result[msg]' where `no`='$data[no]'");
				}

                cashReceiptLog(array(
                    'cno' => $data['no'],
                    'ono' => $data['ono'],
                    'stat' => $stat,
                    'ori_stat' => $data['stat'],
                ));
			}
			ob_end_clean();
		}

		return $cash_count; // 신청서 정보 리턴
	}

	// 데이콤 현금영수증 영수증 출력
	function cashReceiptView($ono, $link_only = false){
		global $cfg;

		if($cfg['cash_r_pg'] != 'dacom') return false;

		$event = "wisaOpen('http://pg.dacom.net/transfer/cashreceipt.jsp?orderid=$ono&mid={$cfg['cash_dacom_id']}&servicetype=SC0100');";
		$r = "<span class=\"box_btn_s\"><a href=\"javascript:;\" onclick=\"$event\">영수증</a></span>";

		return ($link_only == true) ? $event : $r;
	}

    /**
     * U+ 현금 영수증 로그 생성
     *
     * @no        string 현금 영수증 번호
     * @stat      int 상태
     * @member_id string 처리한 회원 아이디
     * @mng_id    string 관리자 아이디
     **/
    function cashReceiptLog($data) {
        global $tbl, $pdo, $admin;

        require_once __ENGINE_DIR__.'/_engine/include/migration/cfg_cash_receipt_taxfree.inc.php';

        if (isset($admin['admin_id']) == false) $admin['admin_id'] = '';
        $admin_id = $admin['admin_id'];
        $data['system'] = ($data['system'] == 'Y' || empty($admin_id) == true) ? 'Y' : 'N';

        $receipt = $pdo->assoc("select * from {$tbl['cash_receipt']} where no=?", array($data['cno']));

        $pdo->query("
            insert into {$tbl['cash_receipt_log']}
            (cno, ono, stat, ori_stat, price, cash_reg_num, mtrsno, b_num, admin_id, system, remote_addr, reg_date)
            values
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, unix_timestamp(now()))
        ", array(
            $data['cno'], $data['ono'], $data['stat'], $data['ori_stat'], $receipt['amt1'],
            $receipt['cash_reg_num'], $receipt['mtrsno'], $receipt['b_num'],
            $admin_id, $data['system'], $_SERVER['REMOTE_ADDR']
        ));
    }

	// 한정시간 판매 OPEN/CLOSE 체크
	function getTsPrd() {
		global $tbl, $cfg, $now, $pdo;

		if($cfg['ts_use'] != 'Y') return;

		if(defined('__getTsPrd_checked__')) return;
		define('__getTsPrd_checked__', true);

		$res = $pdo->iterator("
            select no, stat, ts_ing, ts_dates, ts_datee, ts_names, ts_namee, ts_state
            from {$tbl['product']}
            where
                stat in (2, 3, 4)
                and wm_sc=0
                and ts_use='Y'
                and (
                    (ts_ing='N' and ts_dates <= unix_timestamp() and (ts_datee=0 or ts_datee > unix_timestamp())) /* 켜짐 */
                    or (ts_ing='Y' and (ts_dates > unix_timestamp() or (ts_datee < unix_timestamp() and ts_datee > 0)))) /* 꺼짐 */
        ");
		foreach ($res as $data) {
			$asql = '';
			$nstat = 0;
            if ($data['ts_state'] == '2') $data['ts_state'] = null;

			if($data['ts_ing'] == 'Y') {
				$ts_ing = 'N';
				//if($data['ts_datee'] < $now) {
					if($data['ts_namee']) $asql .= ", name='$data[ts_namee]'";
					if(empty($data['ts_state']) == false && $data['ts_state'] != $data['stat']) {
						$nstat = $data['ts_state'];
					}
				//}
			} else {
				$ts_ing = 'Y';
				if($data['ts_dates'] <= $now) {
					$nstat = 2;
					if($data['ts_names']) $asql .= ", name='$data[ts_names]'";
				}
			}

			if($nstat > 0) $asql .= ", stat='$nstat'";
			$pdo->query("update $tbl[product] set ts_ing='$ts_ing' $asql where no='$data[no]'");
			if($nstat > 0 && $nstat != $data['stat']) {
				$pdo->query("update $tbl[product] set stat='$nstat' where wm_sc='$data[no]' and stat != 5");
				prdStatLogw($data['no'], $nstat, $data['stat'], array(
					'no' => 0,
					'admin_id' => 'timesaleScheduler'
				));
			}
		}
	}

	// 해외배송 가능 업체 목록
	function getOverseaDeliveryComList($nations=""){
		global $tbl, $cfg, $pdo;

        $sql = "select * from ${tbl['delivery_url']} where overseas_delivery='O' order by no asc";
        if ($nations) $sql = "select a.* from ${tbl['delivery_url']} a join ${tbl['os_delivery_country']} b on a.`no` = b.delivery_com where a.overseas_delivery = 'O' and b.country_code = '{$nations}' order by no asc";
		$res = $pdo->iterator($sql);
		$_return = array();
		$_return['cnt'] = $res->rowCount();

		if ($_return['cnt'] > 0) {
            foreach ($res as $i => $data) {
				$_return['list'][$i]['no'] = $data['no'];
				$_return['list'][$i]['name'] = $data['name'];
				$_return['list'][$i]['tax_use'] = $data['tax_use'];
			}
		}

		return $_return;

	}

	// 배송 가능국가
	function getDeliveryPossibleCountry($tax_use=false){
		global $tbl, $engine_dir, $_nations, $_nations_phone, $pdo;

		if($tax_use && fieldExist($tbl['delivery_url'],'tax_use')) $res = $pdo->iterator("select c.country_code, c.delivery_com from ${tbl['os_delivery_country']} as c inner join ${tbl['delivery_url']} as u on c.delivery_com=u.no where u.tax_use='Y' group by c.country_code order by c.country_code asc");
		else $res = $pdo->iterator("select country_code, delivery_com from ${tbl['os_delivery_country']} group by country_code order by country_code asc");
		$_return = array();

		if($res){
            foreach ($res as $i => $data) {
				$_return[$i]['code'] = $data['country_code'];
				$_return[$i]['name'] = $_nations[$data['country_code']];
				$_return[$i]['phone'] = $_nations_phone[$data['country_code']];
				$_return[$i]['delivery_com'] = $data['delivery_com'];
			}
		}

		return $_return;

	}

	// 국가코드->국가명 반환
	function getCountryNameFromCode($nation_code){
		global $engine_dir,$tbl, $pdo;

		include $engine_dir.'/_config/set.country.php';

		$nation_name="";
		if($nation_code) $nation_name = $_nations[$nation_code];

		return $nation_name;
	}

	// 업체no->업체명명 반환
	function getDeliveryNameFromNo($delivery_com){
		global $cfg, $tbl, $pdo;

		$delivery_com_name="";
		if($delivery_com) $delivery_com_name = $pdo->row("select name from ${tbl['delivery_url']} where no='${delivery_com}'");

		return $delivery_com_name;
	}

	function parseUserCart($cart, $is_quickcart = 0) {
		if(!$is_quickcart) $is_quickcart = 0;

		$cart['cart_no'] = $cart_no;
		$cart['link'] = $root_url."/shop/detail.php?pno=".$cart['hash'];
		$cut_name = strip_tags($cart['name']);
		$cart['fix_name_link'] = $cart['name_link'] = "<a href=\"".$cart['link']."\">".$cut_name."</a>";
		$cart['imgr'] = "<img src=\"$cart[img]\" $cart[imgstr] barder=\"0\">";
		$cart['imgr_link'] = "<a href=\"$cart[link]\">$cart[imgr]</a>";
		$cart['del_link'] = "<a href='#' onclick='deletePartCartAjax($cart[cno], $is_quickcart); return false;'>";
		$cart['fix_del_link'] = "<a href=\"#\" onclick=\"deletePartCartAjax($cart[cno], $is_quickcart); return false;\">";
		$cart['wish_link'] = "<a href='#' onclick='wishPartCartAjax(\"$cart[hash]\", this); return false;' class='wish_{$cart['is_wish']}'>";
        if (!$cart['set_idx'] || $cart['option']) {
    		$cart['chgopt_link'] = "<a href='#' onclick='cartChgOption($cart[cno]); return false;' class='changeCartOption'>";
        } else {
            $cart['chgopt_link'] = "<a style='display:none;'>";
        }
		$cart['is_dlv_alone'] = ($cart['dlv_alone'] == 'Y') ? 'singleorder' : '';
		$cart['prd_dlv_prc'] = parsePrice($cart['prd_dlv_prc'], true);

		return $cart;
	}

	// 텍스트 옵션 가격 출력
	function getTextOptionPrc($opno, $value) {
		global $tbl, $pdo;

		if(!trim($value)) {
			return array(0, array());
		}

		$opno = numberOnly($opno);
		$topt = $pdo->assoc("select a.deco1, a.deco2, b.max_val, b.min_val, b.add_price, b.add_price_option from $tbl[product_option_set] a inner join $tbl[product_option_item] b on a.no=b.opno where a.no='$opno'");

		$o_prc = $topt['add_price'];
		if($topt['add_price_option'] > 0) {
			$o_prc += ($topt['add_price_option']*mb_strlen($value, _BASE_CHARSET_)); // 한글도 1자로 처리
		}

		return array(
			'price' => parsePrice($o_prc),
			'data' => $topt,
		);
	}

    /**
     * 주문서 상품명 요약(title) 생성
     **/
    function makeOrderTitle($ono, $merge = true, $where = '')
    {
        global $tbl, $cfg, $pdo;

        $afield = ($cfg['use_set_product'] == 'Y') ? ', set_idx, set_pno' : '';
        if ($merge == true) {
            $where .= " and (stat<10 or stat=11)";
        }

        $title = ''; // 상품명 요약
        $cart_rows = 0; // 상품 종류
        $set_id = 0; // 세트 순서
        $set_checked = array(); // 중복 세트 체크
        $res = $pdo->iterator("select name, buy_ea $afield from {$tbl['order_product']} where ono=? $where order by no asc", array($ono));
        foreach ($res as $data) {
            if ($data['set_idx'] && in_array($data['set_idx'], $set_checked) == true) {
                continue;
            }
            if ($data['set_pno']) {
                $data['name'] = $pdo->row("select name from {$tbl['product']} where no='{$data['set_pno']}'");
            }

            if (!$title || $merge == false) {
                if ($title) $title .= ' / ';
                if ($data['set_idx']) { // 세트라벨
                    $set_id++;
                    $title .= "<span class='set_label'>SET{$set_id}</span> ";
                }
                $title .= stripslashes($data['name']);
                if ($data['buy_ea'] > 1) $title .= "({$data['buy_ea']})";
            }
            $set_checked[] = $data['set_idx'];
            $cart_rows++;
        }

    	if($cart_rows > 1 && $merge == true) $title .= " 外 ".($cart_rows-1);
        return $title;
    }

	// 세트상품의 기본 가격 정보를 구합니다.
	function getSetPrice($pno, $update = false)
	{
		global $tbl, $pdo;

        $prd_type = $pdo->row("SELECT prd_type FROM {$tbl['product']} WHERE no='$pno'");
        if ($prd_type != '4') return;

		$data = $pdo->assoc("
			SELECT sum(p.normal_prc) as normal_prc, sum(p.sell_prc) as sell_prc
			FROM {$tbl['product_refprd']} r INNER JOIN {$tbl['product']} p ON r.refpno=p.no
			WHERE r.pno='$pno'
		");

		if ($update == true) {
			$pdo->query("UPDATE {$tbl['product']} SET normal_prc='{$data['normal_prc']}', sell_prc='{$data['sell_prc']}' WHERE no='$pno'");
		}

		return array(
			'normal_prc' => parsePrice($data['normal_prc']),
			'sell_prc' => parsePrice($data['sell_prc'])
		);
	}

?>