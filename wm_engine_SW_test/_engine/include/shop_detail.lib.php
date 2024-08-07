<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑몰 상세 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	// 대표 이미지
	function mainImg($w=50,$h=50,$ii=2,$iname="",$addtag="") {
		global $prd,$root_url,$root_dir,$cfg,$mw,$mh,$_use,$file_server,$file_server_num, $matched_server;
		$mw=$w;
		$mh=$h;
		if($prd["upfile".$ii] && (!$_use['file_server'] && is_file($root_dir."/".$prd['updir']."/".$prd["upfile".$ii]) || $_use['file_server'] == "Y")) {
			$is=setImageSize($prd["w".$ii],$prd["h".$ii],$w,$h);

			$file_dir = ($cfg['use_icb_storage'] == 'Y' && $prd['upurl']) ? $prd['upurl'] : getFileDir($prd['updir']);
			if($prd['upfile1']) $upfile1 = $file_dir."/$prd[updir]/$prd[upfile1]";

			$res="<img id=\"mainImg$iname\" src=\"$file_dir/$prd[updir]/".$prd["upfile".$ii]."\" upfile1='$upfile1' $is[2] $addtag>";
		}
		else {
			if($_SESSION['browser_type'] == 'mobile' && $ii=3) $ii=4;
			$res="<img id=\"mainImg$iname\" src=\"".$cfg["noimg".$ii]."\">";
		}

		return $res;
	}

	// 추가 이미지 출력 함수
	function attatchPrdImg($w=50,$h=50,$ii=2) {
		global $attatchPrdRes,$mw,$mh,$aii,$prd, $cache_account, $cfg, $tbl, $pdo;
		if(!$attatchPrdRes) {
			if($cfg['up_aimg_sort'] == "Y" && fieldExist($tbl['product_image'], "sort")) $orderby = "order by `sort` asc, `no` desc";
			else $orderby = "order by `no` desc";

			$attatchPrdRes = $pdo->iterator("select * from {$GLOBALS['tbl']['product_image']} where `filetype` in ('2','8') and `pno`='$prd[parent]' $orderby");
		}
		if($aii) {
            $data = $attatchPrdRes->current();
            $attatchPrdRes->next();
		}
		else {
			// 상품 메인 이미지
			$data['width']=$prd["w".$ii];
			$data['height']=$prd["h".$ii];
			$data['updir']=$prd['updir'];
			$data['filename']=$prd["upfile".$ii];
			$aii=1;
		}

        $upfile = getListImgURL($data['updir'], $data['filename']);
		if(!$data['filename']) {
			$res="&nbsp;";
		}
		else {
			$is1=setImageSize($data['width'],$data['height'],$mw,$mh);
			$is2=setImageSize($data['width'],$data['height'],$w,$h);
			$res="<img src=\"$upfile\" $is2[2] onMouseOver=\"toggleAttatchImage(this.src,$is1[0],$is1[1])\" style=\"cursor:pointer\">";
		}
		return $res;
	}

	// 상세설명
	function prdContent($n, $replace = null) {
		global $prd,$prd_content,$admin, $templete_used_key;
		$text=$prd['content'.$n];
		if($n>2 && $n<6 && $text=="wisamall_default") {
			$text=$prd_content["content".$n];
		}
		$text=stripslashes($text);

		if($n>2) {
			$text=nl2br($text);
		}

        if (is_array($replace) == true && count($replace) > 0) {
            foreach ($replace as $key => $val) {
                $text = str_replace('{'.$key.'}', $val, $text);
            }
        }
        if ($text) {
			preg_match_all('/(\{\{\$([^}{]+)\}\})|(\{\{if\(([^}]+)\)\}\})/', $text, $matches);
            if (count($matches[2]) > 0 || count($matches[4]) > 0) {
                if (is_array($templete_used_key) == false) $templete_used_key = array();
		    	$templete_used_key = array_unique(array_merge($templete_used_key, $matches[2], $matches[4]));
            }
           $text = contentReset($text, $_file_name);
        }

		return $text;
	}

	// 상품 상세 - 추가 항목
	function prdFiledList($pno, $category=0) {
		global $tbl,$preFieldRes,$prd, $pdo;

        if ($prd['parent']) {
    		$pno = $prd['parent'];
        }
		if(!$GLOBALS['preFieldRes']) {
			$add_qry = (fieldExist($tbl['product_field_set'], 'default_value')) ? " or a.default_value!=''" : '';
			$GLOBALS['preFieldRes'] = $pdo->iterator("
				select a.*, b.pno, b.fno, b.value
					from
						{$tbl['product_filed_set']} a
						left join {$tbl['product_filed']} b on a.no=b.fno
					where
						a.category='$category'
						and (b.pno=$pno or b.pno is null)
						and (b.value!='' $add_qry) order by `sort` asc
			");
		}
		$data = $GLOBALS['preFieldRes']->current();
        $GLOBALS['preFieldRes']->next();
		if($data == false) {
			unset($GLOBALS['preFieldRes']);
			return;
		}
		$data['name'] = stripslashes($data['name']);
		$data['value'] = stripslashes($data['value']);
		if(empty($data['value']) == true && empty($data['default_value']) == false) {
			$data['value'] = stripslashes($data['default_value']);
		}

		return $data;
	}

	// 상품평 상품 질답 쓰기 권한
	function reviewAuth($au, $edit_no = null) {
		global $cfg,$rbuy_date,$member,$tbl, $prd, $pdo;
		$ra=$cfg[$au];

		switch($ra) {
			case '1': // 비회원
				$ra="";
				break;
			case '2': // 회원 전용
				if($member['no']) $ra="";
				break;
			case '3': // 구매자만
				$prdall = $pdo->row("select group_concat(`no`) from `$tbl[product]` where `no` = '{$prd['parent']}'");
				$prdall = preg_replace('/^,|,$/', '', $prdall);
				if(!$prdall) $prdall = 0;
				if($member['no'] && !$rbuy_date) $rbuy_date = $pdo->row("select `date1` from `$tbl[order]` o inner join `$tbl[order_product]` p using (`ono`) where `member_no`='$member[no]' and `member_id`='$member[member_id]' and p.`stat`='5' and p.`pno` in ($prdall)");

				if(!$member['no']) $ra='2';
				elseif($rbuy_date) $ra="";
				break;
			case '4' :
				if($member['no'] > 0) {
					$w = ($edit_no > 0) ?  " and no!='$edit_no'" : "";
					$order_cnt = $pdo->row("select count(*) from $tbl[order] a inner join $tbl[order_product] b using(ono) where a.member_no='$member[no]' and pno='{$prd['parent']}' and b.stat=5");
					$review_cnt = $pdo->row("select count(*) from $tbl[review] where member_no='$member[no]' and pno='{$prd['parent']}' $w");

                    if($_REQUEST['ono']) {
                        $ono = addslashes($_REQUEST['ono']);
                        $prd_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and pno='{$prd['parent']}' and stat=5");
						$rev_cnt = $pdo->row("
							select count(*) from {$tbl['review']}
							where member_no='{$member['no']}' and pno='{$prd['parent']}' and ono='$ono'
						");
						if ($prd_cnt > 0 && $rev_cnt > 0 && $rev_cnt >= $prd_cnt) {
							return '4'; // 이미 리뷰 작성 완료
						}
                    }

					if($order_cnt > $review_cnt) $ra = '';
					else $ra = '3';
				} else {
					$ra = '2';
				}
				break;
		}

		return $ra;
	}

	// 상품평 이름 표시 (후기, QNA)
	function reviewName($member,$type)
    {
		global $cfg,$admin,$tbl,$board_admin_list, $pdo;

        $review_name = getBoardName('bbs_'.$type, $member);

		return $review_name;
	}

    /**
     * 게시판 이름 출력
     **/
	function getBoardName($db, $data)
    {
        global $scfg, $tbl, $pdo, $_writer_names, $_board_config;

        if (isset($_writer_names) == false) {
            $_writer_names = explode('@', $scfg->get('writer_name_bbs'));
        }

        if (in_array($db, $_writer_names) == true) { // global
            $writer_name = $scfg->get('writer_name');
            if (empty($writer_name) == true) $writer_name = 'name';
            $config = array(
                'writer_name' => $writer_name,
                'protect_name' => $scfg->get('protect_name'),
                'protect_name_strlen' => $scfg->get('protect_name_strlen'),
                'protect_name_suffix' => $scfg->get('protect_name_suffix'),
                'protect_id' => $scfg->get('protect_id'),
                'protect_id_strlen' => $scfg->get('protect_id_strlen'),
                'protect_id_suffix' => $scfg->get('protect_id_suffix')
            );
        } else { // local
            if (preg_match('/^bbs_(review|qna)$/', $db, $tmp) == true) {
                $db = $tmp[1];
                $config = array(
                    'writer_name' => $scfg->get('product_'.$db.'_name'),
                    'protect_name' => $scfg->get('product_'.$db.'_protect_name'),
                    'protect_name_strlen' => $scfg->get('product_'.$db.'_protect_name_strlen'),
                    'protect_name_suffix' => $scfg->get('product_'.$db.'_protect_name_suffix'),
                    'protect_id' =>  $scfg->get('product_'.$db.'_protect_id'),
                    'protect_id_strlen' => $scfg->get('product_'.$db.'_protect_id_strlen'),
                    'protect_id_suffix' => $scfg->get('product_'.$db.'_protect_id_suffix')
                );
            } else {
                if (is_array($_board_config[$db]) == false) {
                    $_board_config[$db] = $pdo->assoc("select * from mari_config where db='$db'");
                }
                $config = $_board_config[$db];
            }

            // 미설정시 기본 값 (global 설정 기준으로)
            if (empty($config['writer_name']) == true) $config['writer_name'] = $scfg->get('writer_name');
            if (empty($config['writer_name']) == true) $config['writer_name'] = 'name';
            if (empty($config['protect_name']) == true) $config['protect_name'] = $scfg->get('protect_name');
            if (empty($config['protect_id']) == true) $config['protect_id'] = $scfg->get('protect_id');
            if (empty($config['protect_name_strlen']) == true) $config['protect_name_strlen'] = $scfg->get('protect_name_strlen');
            if (empty($config['protect_name_suffix']) == true) $config['protect_name_suffix'] = $scfg->get('protect_name_suffix');
            if (empty($config['protect_id_strlen']) == true) $config['protect_id_strlen'] = $scfg->get('protect_id_strlen');
            if (empty($config['protect_id_suffix']) == true) $config['protect_id_suffix'] = $scfg->get('protect_id_suffix');
        }

        switch($config['writer_name']) {
            case 'name' :
                return protectName($config, 'name', $data['name']);
            case 'member_id' :
                if (empty($data['member_id']) == true) {
                    return protectName($config, 'name', $data['name']);
                }
                return protectName($config, 'id', $data['member_id']);
            case 'name_id' :
                $name = protectName($config, 'name', $data['name']);
                if (empty($data['member_id']) == false) {
                    $name .= "(".protectName($config, 'id', $data['member_id']).")";
                }
                return $name;
            case 'nickname' :
                if ($data['member_id'] && $scfg->comp('member_join_nickname', 'Y') == true) {
                    $nickname = $pdo->row("select nick from {$tbl['member']} where member_id='{$data['member_id']}'");
                    if ($nickname) return $nickname;
                    else return protectName($config, 'name', $data['name']);
                }
                return protectName($config, 'name', $data['name']);
            case 'icon' :
                if ($data['member_id']) {
                    $icon = getMemberIcon($data['member_no'], $data['member_id']);
                    if ($icon) return $icon.protectName($config, 'name', $data['name']);
                }
                return protectName($config, 'name', $data['name']);
        }
	}

    function protectName($config, $fn, $str) {
        if (isset($config['protect_'.$fn]) == false) {
            return $str;
        }
		if ($config['protect_'.$fn] == 'Y') {
            $strlen = $config['protect_'.$fn.'_strlen'];
            $orglen = mb_strlen($str, _BASE_CHARSET_);
            if ($orglen == $strlen) {
                $strlen-=1;
            }

            if (preg_match('/^[^@]+@/', $str) == true) { // 이메일 형태
                $tmp = explode('@', $str);
                $tmp[0] = protectName($config, $fn, $tmp[0]);
                return $tmp[0].'@'.$tmp[1];
            }

            $tmp_str = mb_substr($str, 0, $strlen, _BASE_CHARSET_);
            return $tmp_str.$config['protect_'.$fn.'_suffix'];
		}
        return $str;
    }

	function qnaList($re_title="",$date_form="Y/m/d",$qna_row = 10, $qna_block=10) {
		global $qnaRes,$tbl,$prd,$qna_idx,$all_qna, $root_url,$_use, $pdo;

		$rno = numberOnly($_GET['rno']);

		if(!$qnaRes) {
			if($_use['fast_sql'] == "Y") {
				if(!$prd['no']) {
					$w=" and `no`='$rno'";
				}
				$pno = ($prd['parent']) ? $prd['parent'] : $prd['no'];
				$sql="select * from `$tbl[qna]` where `pno`='$pno' $w order by `reg_date` desc";
				$qna_idx = $pdo->row("select count(*) from {$tbl['qna']} where `pno`='$pno' $w");
			}
			else {
				if($rno){
					if($prd[no]) $w=" and q.`no`='$rno'";
					else $w=" and `no`='$rno'";
				}

				// 바로가기 재수정 2007-02-15 - Jin (임시책)
				$parent_no=($prd['parent'])? $prd['parent']:$prd['no'];
				if($prd['no']){
					$pstat = '';
					if($_SESSION['admin_no'] >0 && $GLOBALS['admin']['no'] == $_SESSION['admin_no']) $pstat = ",4";
					$sql="select p.`no`,p.`stat`,p.`content1`,p.`content2`,q.* from `$tbl[product]` p inner join `$tbl[qna]` q on p.no=q.pno where p.`no`=q.`pno` and p.`stat` in (2,3,5$pstat) and p.`no`='$parent_no' $w order by `reg_date` desc";
					$qna_idx = $pdo->row("select count(*) from `$tbl[product]` p inner join `$tbl[qna]` q on p.no=q.pno where p.`stat` in (2,3$pstat) and p.`no`='$parent_no' $w");
				}else{
					$sql="select * from `$tbl[qna]` where 1 $w order by `reg_date` desc";
					$qna_idx = $pdo->row("select count(*) from `$tbl[qna]` where 1 $w");
				}
			}

			// 페이징
			include_once __ENGINE_DIR__."/_engine/include/paging.php";
			foreach($_GET as $key=>$val) {
				if ($val && $key != "qna_page") $QueryString.="&".$key."=".$val;
			}

			$QueryString .= "#qna";

			if (!$GLOBALS['qna_page']) $GLOBALS['qna_page'] = 1;

			$qna_paging = new Paging($qna_idx, $GLOBALS['qna_page'], $qna_row, $qna_block);
			$qna_paging->setParamName("qna_page");
			$qna_paging->addQueryString($QueryString);
			$qna_paging_result = $qna_paging->result($pg_dsn);

			if(!$all_qna) $sql .= $qna_paging_result['LimitQuery'];
			$qna_idx=$qna_idx-($qna_row*($GLOBALS['qna_page']-1))+1;
			$qna_paging_result['PageLink'] = preg_replace("/(\?qna_page=[0-9]+[^\"']+)/", "#\" onclick=\"reloadProductBoard('qna', '$1'); return false;\"", $qna_paging_result['PageLink']);
			$GLOBALS['qna_pageRes']=$qna_paging_result['PageLink'];
			$qnaRes = $pdo->iterator($sql);
		}

		$data = $qnaRes->current();
        $qnaRes->next();
		if($data == false) return;

		$data=qnaOneData($data,"","");
		$qna_idx--;
		return $data;
	}

	function qnaOneData($data,$title_cut=0,$prd_cut=0) {
		global $member,$root_dir,$now,$cfg,$engine_dir,$cate,$rno;
		$data['title']=stripslashes($data['title']);
		if($title_cut>0) $data['title']=cutStr($data['title'],$title_cut);

		if($data['upfile1'] || $data['upfile2']) $data['atc']=true;
		else $data['atc']=false;
		if(strip_tags($data['content']) != strip_tags($data['content'], '<img>')) {
			$data['atc'] = true;
		}

		$data['content']=nl2br(stripslashes($data['content']));
		if($data['notice'] == "N") $data['content']=$data['answer_str']="";
		$data['del_link']=$data['edit_link']="<a style='display:none;'>";

		if($cfg['product_qna_hitnum'] == "Y"){
			if(!@strchr($_SESSION['qna_hitted'],"_".$data['no']."_")){
				$data['title']="<font onclick=\"rvQnaHit('qna', '".$data['no']."');\">".$data['title']."</font>";
			}
			$data['hit']="<font id=\"qnaHit_".$data['no']."\">".$data['hit']."</font>";
			if($rno && $data['no'] == $rno){
				$data['hit'] .= "<script type=\"text/javascript\">rvQnaHit('qna', '".$data['no']."');</script>";
			}
		}else $data['hit']=0;


		$prd=prdCache($data['pno']);
		if($prd['no']) {
			$data['prd_name']=stripslashes($prd['name']);
			if($prd_cut>0) $data['prd_name']=cutStr($data['prd_name'],$prd_cut);
		}

		$data['link1']=$GLOBALS['root_url'].'/shop/detail.php?pno='.$prd['hash'];
		$data['link2']=$GLOBALS['root_url']."/shop/product_qna.php?rno={$data['no']}&pno={$prd['hash']}&cate=$cate#qnaTitle{$data['no']}";

		$data['cate']=$data['cate'] ? "<a href=\"".$data['link2']."\">".$data['cate']."</a>" : "";

		if($cfg['product_qna_new_time']>0) {
			if(!$cfg['product_qna_new_time_now']) {
				$cfg['product_qna_new_time_now']=$now-$cfg['product_qna_new_time']*60*60;
			}

			if($data['reg_date']>=$cfg['product_qna_new_time_now']) {
				$data['new_check']=true;
			}
		}

		$data['name']=reviewName($data,'qna');
		$data['reg_date2']=date('m/d',$data['reg_date']);
		$data['reg_date']=parseDateType($cfg['date_type_qna'], $data['reg_date']);

		return $data;
	}

	function reviewList($w="",$h="",$rev_row=10,$rev_block=10) {
		global $reviewRes,$tbl,$prd,$rev_idx,$all_rev, $reviewCommentRes,$root_url, $rev_order, $_use, $cfg, $member, $pdo, $setsubs, $_skin;

		$rno = numberOnly($_GET['rno']);

		if(!$reviewRes) {
			$rev_sort = $_GET['rev_sort'];
			if(!$rev_sort && $_COOKIE['b_review_sort']) $rev_sort = $_COOKIE['b_review_sort'];

			if(!$rev_order) $rev_order = "r.reg_date desc ";
			switch($rev_sort) {
				case '2' : $rev_order = 'r.rev_pt desc, r.reg_date desc'; break;
				case '3' : $rev_order = 'r.rev_pt asc, r.reg_date desc'; break;
				case '4' :
					if(isTable($tbl['review_recommend']) == true) {
						$rev_order = 'recommend_Y desc, recommend_N asc, r.reg_date desc';
					}
				break;
			}

            if ($_skin['review_list_best_use'] == 'Y') {
                $rev_order = ' r.stat DESC, '.$rev_order;
            }

			$stat_qry = ($cfg['product_review_atype_detail'] == 'Y' && $member['no'] > 0) ? "(r.`stat` in (2,3,4) or r.member_no='$member[no]')":"r.`stat` in (2,3,4)";
			$parent_no = ($prd['parent']) ? $prd['parent'] : $prd['no'];
			if($prd['no']) {
                $where = '';
                if ($setsubs) {
                        $where = " and p.no in ($setsubs)";
                } else {
                    $where = " and p.`no`='$parent_no'";
                }
				$pstat = '';
				if($_SESSION['admin_no'] >0 && $GLOBALS['admin']['no'] == $_SESSION['admin_no']) $pstat = ",4";
				$sql = "select p.`no`,p.`stat`,p.`content1`,p.`content2`, r.* from `$tbl[product]` p inner join `$tbl[review]` r on p.`no`=r.`pno` where $stat_qry and p.`stat` in (2,3$pstat) $where order by $rev_order";
				$rev_idx = $pdo->row("select count(*) from `$tbl[product]` p inner join `$tbl[review]` r on p.`no`=r.`pno` where $stat_qry and p.`stat` in (2,3$pstat) $where");
			} else {
				$sql = "select * from `$tbl[review]` r where $stat_qry and `no`='$rno' order by $rev_order";
				$rev_idx = $pdo->row("select count(*) from `$tbl[review]` r where $stat_qry and `no`='$rno'");
			}
			$_SESSION['rev_qry'] = $sql;

            $prd['rev_cnt'] = $rev_idx;

			// 페이징
			include_once $GLOBALS['engine_dir']."/_engine/include/paging.php";
			foreach($_GET as $key=>$val) {
				if ($val && $key != "rev_page") $QueryString.="&".$key."=".$val;
			}

			$QueryString .= "#review";

			$rev_page = numberOnly($_GET['rev_page']);
			if (!$rev_page) $rev_page = 1;

            /**
             * 페이징 미사용 설정시(all_rev) 데이터가 300개 이상이라면 강제로 페이징 처리
             * 스킨내 페이징관련 디자인코드 삽입 필요
             */
            if (
                $all_rev
                && $rev_idx >= 300
            ) {
                $all_rev = false;
            }

            $rev_paging = new Paging($rev_idx, $rev_page, $rev_row, $rev_block);
			$rev_paging->setParamName("rev_page");
			$rev_paging->addQueryString($QueryString);
			$rev_paging_result = $rev_paging->result($pg_dsn);

			if(!$all_rev) $sql .= $rev_paging_result['LimitQuery'];
			$rev_idx = $rev_idx-($rev_row*($rev_page-1))+1;
			$rev_paging_result['PageLink'] = preg_replace("/(\?rev_page=[0-9]+[^\"']+)/", "#\" onclick=\"reloadProductBoard('review', '$1'); return false;\"", $rev_paging_result['PageLink']);
			$GLOBALS['rev_pageRes'] = $rev_paging_result['PageLink'];
			$reviewRes = $pdo->iterator($sql);
		}
		$data = $reviewRes->current();
        $reviewRes->next();
		if(!$data['no']) return;

		$data['rev_idx'] = ($rev_idx-1);
		$data = reviewOneData($data,$title_cut,$w,$h,"/_image/_default/etc/spacer.gif");

		unset($reviewCommentRes);
		$rev_idx--;
		return $data;
	}

	function reviewOneData($data,$title_cut="",$w="",$h="",$def_img="/_image/shop/bari_noimg_review.gif",$prd_cut="",$link_type="",$img_no=1) {
		global $cfg,$now,$engine_dir,$root_url,$root_dir,$rno;

		$data['title'] = stripslashes($data['title']);
		if($title_cut > 0) $data['title'] = cutStr($data['title'], $title_cut);

		$data['ocontent'] = $data['content'];
		$data['content'] = nl2br(stripslashes($data['content']));
		$data['content_plain'] = strip_tags($data['content']);
		$data['content_short'] = mb_strimwidth($data['content_plain'], 0, 100, '...', _BASE_CHARSET_);

		$data['name'] = reviewName($data, "review");

		if($cfg['product_review_new_time'] > 0) {
			if(!$cfg['product_review_new_time_now']) {
				$cfg['product_review_new_time_now'] = $now-$cfg['product_review_new_time']*60*60;
			}

			if($data['reg_date'] >= $cfg['product_review_new_time_now']) {
				$data['new_check'] = true;
			}
		}

		$data['reg_date2'] = date("m/d", $data['reg_date']);
		$data['reg_date'] = parseDateType($cfg['date_type_review'], $data['reg_date']);

		$prd = prdCache($data['pno']);
		if($prd['no']) {
			$data['prd_name'] = stripslashes($prd['name']);
			if($prd_cut > 0) $data['prd_name'] = cutStr($data['prd_name'], $prd_cut);
            $data['prd_link'] = $root_url.'/shop/detail.php?pno='.$prd['hash'];
		}

		$data['link1'] = $GLOBALS['root_url']."/shop/detail.php?pno=".$prd['hash'];
		$data['link2'] = $GLOBALS['root_url']."/shop/product_review.php?rno=".$data['no']."&pno=".$prd['hash'];
		$data['title_layer_link'] = "openReviewDetail($data[no], '$data[rev_idx]');";
		if($link_type == "") {
			$data['link2'] .= "#revTitle".$data['no'];
		}

		$data['recommend_link'] = "return recommendReview($data[no], true);";
		$data['disrecommend_link'] = "return recommendReview($data[no], false);";

		$data['recommend_y'] = number_format($data['recommend_y']);
		$data['recommend_n'] = number_format($data['recommend_n']);

		$data['del_link'] = $data['edit_link'] = "<a href=\"#\" style=\"display:none;\">";
		$auth = getDataAuth2($data);
		if($auth) {
			if($auth == 1 || $cfg['product_review_del'] == "Y") {
				if($auth == 3) $data['del_link'] = "<a href=\"javascript:conDelRev($data[no], '{$_REQUEST['rev_idx']}')\">";
				else $data['del_link'] = "<a href=\"javascript:delRev($data[no])\">";
			}
			if($auth==1 || $cfg['product_review_edit'] == "Y") {
				$data['edit_link'] = "<a href=\"javascript:editRev($data[no])\">";
				$data['edit_link_layer'] = "<a href=\"javascript:writeReviewWithoutRa({$data['pno']}, {$data['no']}, '{$_GET['rev_idx']}')\">";
			}
		}

		if($w && $h) {
			if($img_no > 1) {
				$img = prdImg($img_no,$prd,$w,$h,$def_img);
				$data['img'] = $img[0];
				$data['imgstr'] = $img[1];
			}
		}

		// 본문 삽입 이미지 구하기
		if(strip_tags($data['content']) != strip_tags($data['content'], '<img>')) {
			$dom = new DomDocument();
			$dom->preserveWhiteSpace = false;
			@$dom->loadHTML(
				'<meta http-equiv="Content-Type" content="text/html; charset='._BASE_CHARSET_.'">'.$data['content']
			);
			$imgs = $dom->getElementsByTagName('img');
			foreach($imgs as $key => $val) {
				$data['upfile'.(3+$key)] = $val->getAttribute('src');
			}
		}

		// 전체 후기 이미지 정리
		$i = 1;
		$img = array();
		while(isset($data['upfile'.$i])) {
			if($data['upfile'.$i]) {
				if(strpos($data['upfile'.$i], '/') == false) {
					$img[] = getFileDir($data['updir']).'/'.$data['updir'].'/'.$data['upfile'.$i];
				} else {
					$img[] = $data['upfile'.$i];
				}
			}
			$i++;
		}

		$img = array_unique($img);
		$data['atc'] = (count($img) > 0);
		$data['img_cnt'] = count($img);
		if($_POST['exec'] != 'view') {
			foreach($img as $key => $val) $data['img'.($key+1)] = $val;
		}

		$data['file_exist'] = $data['atc'] ? "yes" : "no";
		$data['best'] = ($data['stat'] == 3) ? "best" : "";
		$data['hot']=($data['stat'] == 4) ? 'hot' : '';

		$GLOBALS['rev_idx2']++;
		$data['rev_idx'] = $GLOBALS['rev_idx']-1;
		$data['rev_idx2'] = $GLOBALS['rev_idx2'];

		$prd['rev_avg'] = $data['rev_pt'];

		if($data['total_comment'] > 0) {
			$data['total_comment_str'] = "[".$data['total_comment']."]";
		}

		if($data['img']) {
			$data['prd_img'] = "<a href=\"".$data['link1']."\"><img src=\"".$data['img']."\" ".$data['imgstr']."></a>";
		}

		if($cfg['product_review_hitnum'] == "Y"){
			if(!@strchr($_SESSION['review_hitted'],"_".$data['no']."_")){
				$data['title'] = "<font onclick=\"rvQnaHit('review', '".$data['no']."');\">".$data['title']."</font>";
			}
			$data['hit'] = "<font id=\"reviewHit_".$data[no]."\">".$data[hit]."</font>";
			if($rno && $data['no'] == $rno){
				$data['hit'] .= "<script type=\"text/javascript\">rvQnaHit('review', '".$data['no']."');</script>";
			}
		}else $data['hit'] = 0;

		return $data;
	}

	function reviewStar($star, $rev = null) {
		global $prd, $review;

		if(is_null($rev) && is_array($review)) $rev = $review;
		if($rev['no']) $point = $rev['rev_pt'];
		else $point = round($prd['rev_avg']);

		for($ii = 0; $ii < $point; $ii++) {
			$r .= $star;
		}
		return $r;
	}

    /**
     * 상품 후기에 포함된 이미지 개수 업데이트
     **/
    function reviewImageCount($data)
    {
        global $tbl, $pdo, $scfg;

        $count = 0;
        if ($data['upfile1']) $count++;
        if ($data['upfile2']) $count++;

        $dom = new \DomDocument();
        $dom->loadHTML(
            '<meta http-equiv="Content-Type" content="text/html; charset='._BASE_CHARSET_.'">'.$data['content']
        );
        $imgs = $dom->getElementsByTagName('img');
        $count += $imgs->length;

        $pdo->query("update {$tbl['review']} set image_cnt=$count where no='{$data['no']}'");

        return $count;
    }

	function prdOptionList($css="",$allow_color_chip = false,$no_js="",$detxt="",$multi="") {
        global $tbl, $pdo, $prd, $prdOptionRes;

		if(isset($prdOptionRes) == false) {
			$asql = $GLOBALS['option_list_asql'];
			if ($multi > 0) {
				$asql .= " and necessary!='P'";
			}
			$prdOptionRes=$pdo->iterator("select * from {$GLOBALS['tbl']['product_option_set']} where `stat`='2' and `pno`='".$GLOBALS['prd']['parent']."' $asql order by necessary='P' asc, sort asc");
		}
		$data = $prdOptionRes->current();
        $prdOptionRes->next();
		if($data == false) {
            unset($prdOptionRes);
            return false;
        }
		$GLOBALS['opt_no']++;

		if($allow_color_chip != true && ($data['otype'] == '5A' || $data['otype'] == '5B')) {
			$data['otype'] = '2A';
		}
		$data['option_str']=printOption($data,$GLOBALS['opt_no'],$css,$no_js,$detxt,$multi);

		$data['name']=$ori_name=stripslashes($data['name']);
		$data['option_desc'] = stripslashes($data['desc']);
		if(!$data['updir']) $data['updir'] = '_data/prd_common';
		if($data['upfile1']){
			$imgsrc = getListImgURL($data['updir'], $data['upfile1']);
			$data['name']="<img src='$imgsrc'>";
		}

		// 상품 수량 정보가 무제한일 경우 스크립트체크 막음
		$data['ea_ck'] = ($prd['ea_type'] == '2') ? 'N' : $data['ea_ck'];

		$otype=substr($data['otype'],0,1);
		if($multi && $otype != '4') $otype='2'; // 멀티옵션일 경우 셀렉트로 고정

		$multi_suffix = ($multi > 0) ? '['.$multi.']' : '';
		$data['hidden_str']="<input type=\"hidden\" name=\"option_necessary".$GLOBALS['opt_no'].$multi_suffix."\" value=\"".$data['necessary']."\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_type".$GLOBALS['opt_no'].$multi_suffix."\" value=\"".$otype."\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_name".$GLOBALS['opt_no'].$multi_suffix."\" value=\"".inputText($ori_name)."\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_prc".$GLOBALS['opt_no'].$multi_suffix."\" value=\"0\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_how_cal".$GLOBALS['opt_no'].$multi_suffix."\" value=\"".$data['how_cal']."\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_sel_item".$GLOBALS['opt_no'].$multi_suffix."\" value=\"\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_ea_ck".$GLOBALS['opt_no'].$multi_suffix."\" value=\"".$data['ea_ck']."\">\n";
		$data['hidden_str'].="<input type=\"hidden\" name=\"option_ea_num".$GLOBALS['opt_no'].$multi_suffix."\" value=\"\">\n";

		/*if((($GLOBALS[opt_no]+1)%$row)==0 && !$multi && !$GLOBALS['prdOptionNoTR']) {
			echo "</tr><tr>";
		}*/
		return $data;
	}

	function printOption($data,$opt_no="",$css="",$no_js="",$detxt="",$multi="") {
		global $tbl, $cfg, $pdo;

		if(defined('_wisa_manage_edit_') == true && $data['otype'] != '4B') $data['otype'] = '2A';

		$data['name'] = inputText(stripslashes($data['name']));
		$otype=substr($data['otype'],0,1);
		$br=substr($data['otype'],1);
		$objName="option".$opt_no;
		if($multi){ // 멀티옵션일 경우 셀렉트로 고정
			$objName .= "[".$multi."]";
		}

		if($data['otype'] == '5A') {
			$_line = $GLOBALS['_line_imgc'];
			$skin_module_name = 'detail_opt_img_list';
			if(!$_line) $otype = 2;
		}
		if($data['otype'] == '5B') {
			$_line = $GLOBALS['_line_txtc'];
			$skin_module_name = 'detail_opt_txt_list';
			if(!$_line) $otype = 2;
		}

		$script="\"\"";
		if(!$GLOBALS['pop'] && $no_js=="") {
			$script="\"optionCal(this.form,'$opt_no',this.value, this, '$multi')\"";
			if($multi) {
				//$script="\"\"";
				if($_REQUEST['exec'] == 'prd') $script = "\"optionCal($multi, this)\""; // 관리자모드
			}
		}
		if($otype=="2") {
			$class = ($no_ns == true) ? '' : "class=\"wing_multi_option pno{$data['pno']} necessary_$data[necessary]\" data-pno=\"{$data['pno']}\"";
			$str="<select name=\"$objName\" onChange=$script data-necessary='$data[necessary]' data-type='{$data['otype']}' data-name=\"{$data['name']}\" $class>";
			if($detxt) $str.="<option value=\"\">".$detxt."</option>";
			else $str.="<option value=\"\">::".$data['name']."::</option>";
		}
		elseif($otype=="1") {
			$ot="checkbox";
		}
		elseif($otype == '4' || $otype == '5') {
			$str = "";
		}
		else {
			$ot="radio";
		}

		$iidx = 0;
		$isql = $pdo->iterator("select * from `wm_product_option_item` where `pno`='{$data['pno']}' and `opno`='{$data['no']}' and hidden!='Y' order by `sort` asc");
        foreach ($isql as $item) {
			$iidx++;
			if (!$item['no']) continue;
			if($data['necessary'] != 'P') $item['complex_no'] = 0;
			if($data['necessary'] == 'P' && $item['complex_no'] > 0) {
				$comp = $pdo->assoc("select complex_no, pno, opts from erp_complex_option where complex_no='$item[complex_no]' and del_yn='N'");
				if(!$comp['pno']) continue;
				$comp['opts'] = str_replace('_', ',', trim($comp['opts'], '_'));
				$_prd = $pdo->assoc("select sell_prc, min_ord from $tbl[product] where no='$comp[pno]'");
				$item['add_price'] = $_prd['sell_prc'];
				$item['min_ord'] = $_prd['min_ord'];
				if($comp['opts']) {
					$item['add_price'] += $pdo->row("select sum(add_price) from $tbl[product_option_item] where no in ($comp[opts])");
				}
			}

			$item['iname'] = str_replace('"', '&quot', stripslashes($item['iname']));
			$item['iname'] = str_replace("'", '&#039', stripslashes($item['iname']));
			if($item['add_price'] != 0) {
				if ($item['add_price'] < 0 && $data['deco1'] == "+") $data['deco1'] = '';
				$prc_str = parsePrice($item['add_price'], true);
				if ($data['deco_use']=="Y") {
					$deco1 = ($item['add_price'] < 0) ? str_replace('+', '' ,$data['deco1']) : $data['deco1'];
					$prc_str=$deco1.$prc_str.$data['deco2'];
				}
				else $prc_str="";
			}
			else {
				$prc_str="";
			}

			if($data['out_hide'] == "Y" && $item['ea'] < 1){
				$str.= "";
			}else{
				if($data['ea_ck'] == "Y" && $GLOBALS['prd']['ea_type'] == 3){
					if($item['ea'] < 1){
						$ea_str = ' - '.__lang_common_info_soldoutname__;
						if($otype=="2"){
							$ea_deco=" style='color:#A1A1A1;'";
						}else{ $ea_deco=" disabled"; }
					}else{ $ea_str=""; $ea_deco=""; }
				}
				if($item['ea'] < 1) $item['ea']=0;

				if($data['necessary'] != 'N' && ($GLOBALS['prd']['ea_type'] == 1 && $opt_no == 1) || $comp['complex_no'] > 0) {
					$stock_sql = ($comp['complex_no'] > 0) ?
						" and c.complex_no='$comp[complex_no]'" :
						" and c.pno='$data[pno]' and c.opts like '%#_{$item['no']}#_%' ESCAPE '#'";
					if($cfg['erp_force_limit'] == 'Y') {
						$stock_sql2 = " and (c.limit_qty>-1 || c.qty>c.limit_qty)";
					}
					$stock_ck = $pdo->row("select count(*) from erp_complex_option c inner join {$tbl['product']} p on c.pno=p.no where c.del_yn='N' and ((c.force_soldout='N' $stock_sql2) or (c.force_soldout='L' and c.qty > 0)) and p.stat=2 $stock_sql");

					if($stock_ck < 1) {
						$item['soldout'] = ' ('.__lang_common_info_soldoutname__.')';
						$item['disabled'] = 'disabled';
					}
				}

				if($otype=="2") {
					$str.="<option $item[disabled] value=\"$item[iname]$data[unit]::$item[add_price]::$item[ea]::$item[no]::cpx$item[complex_no]::$item[min_ord]\"$ea_deco>$item[iname]$data[unit]$item[soldout]$prc_str$ea_str</option>";

				}
				elseif($data['otype'] == '4A') {
					if(!$data['deco1']) $data['deco1'] = 'div';
					$str .= "<$data[deco1] class='area_item_$iidx'>";
					$str .= "<label>$item[iname]</label> ";
					$str .= "<input type='text' class='otype4 input form_input' multi='{$multi}' iidx='$iidx' name='{$objName}[]' data-type='{$data['otype']}' data-necessary='{$data['necessary']}' size='$data[deco2]' value='0' title='$item[iname]' onkeyup=$script /> $data[unit]";
					$str .= "<input type='hidden' multi='{$multi}' iidx='$iidx' name='option_area{$opt_no}[]' value='$item[no]' />";
					$str .= "</$data[deco1]>";
				}
				elseif($data['otype'] == '4B') {
					$idata = $pdo->assoc("select add_price, add_price_option, max_val, min_val from $tbl[product_option_item] where opno='$data[no]'");
					$max_length = ($idata['max_val'] > 0) ? " maxlength='$idata[max_val]'" : '';
					if($GLOBALS['_make_detail_option'] != true) {
						$placeholder = 'placeholder="'.inputText($data['name']).'"';
					}
					$multi_suffix = ($multi > 0) ? '['.$multi.']' : '';
					$str .= "
					<input
						type='text' name='{$objName}' multi='$multi'
						data-otype='4B' data-add-price='$idata[add_price]' data-add-price-option='$idata[add_price_option]' data-min-length='$idata[min_val]' data-type='{$data['otype']}' data-name=\"{$data['name']}\"
						data-necessary='{$data['necessary']}'
                        data-pno=\"{$data['pno']}\"
						onchange=$script
						class='input form_input text_option_basic wing_multi_option pno{$data['pno']} necessary_$data[necessary]'
						$placeholder $max_length
					>
					<input type='hidden' name='txt_option_set_no{$opt_no}$multi_suffix' value='$data[no]'>
					";
				}
				elseif($otype == 5) {
					$str = "<input type='hidden' name='$objName' data-type='{$data['otype']}' data-necessary='{$data['necessary']}' class='wing_multi_option pno{$data['pno']} necessary_$data[necessary]' data-pno=\"{$data['pno']}\">\n";
					$item['idx'] = $opt_no;
					$item['script'] = "selectOptionChip($opt_no, '$item[iname]$data[unit]::$item[add_price]::$item[ea]::$item[no]::cpx$item[complex_no]::{$item['min_ord']}', this);return false;";
					if($data['otype'] == '5A') {
						$chip = $pdo->assoc("select * from $tbl[product_option_colorchip] where no='$item[chip_idx]'");
						$item['upfile1'] = getFileDir($chip['updir']).'/'.$chip['updir'].'/'.$chip['upfile1'];
						$item['color_code'] = $chip['code'];
                        $_lineValues_content = ($chip['type'] == 'file') ? $_line[2] : $_line[5];
					} else {
                        $_lineValues_content = $_line;
                    }
                    $tmp .= lineValues($skin_module_name, $_lineValues_content, $item);
				}
				else {
					if(!$GLOBALS['pop'] && $no_js == '') {
						$script="\"optionCal(this.form,'$opt_no','$item[iname]$data[unit]::$item[add_price]::$item[ea]::$item[no]::cpx$item[complex_no]')\"";
					}
					$str.="
					<label>
						<input
							type=\"$ot\"
							name=\"$objName\"
							value=\"$item[iname]$data[unit]::$item[add_price]::$item[ea]::$item[no]::cpx$item[complex_no]::{$item['min_ord']}\"
							$ea_deco
							onClick=$script
							data-type='{$data['otype']}'
							data-necessary='$data[necessary]'
                            data-pno=\"{$data['pno']}\"
							class=\"wing_multi_option pno{$data['pno']} necessary_$data[necessary]\" $item[disabled]
						> $item[iname]$prc_str$ea_str
					</label>";

					if($br=="B") {
						$str.="<br />";
					}
				}
				$str.="\n";
			}
		}
		if($otype=="2") {
			$str.="</select>";
		}

		if($skin_module_name) $str .= listContentSetting($tmp, $_line);

		return $str;
	}

	function prdOptionImgList($pno){
        global $tbl, $pdo, $prdOptionImgRes;

		if(isset($prdOptionImgRes) == false) {
			$prdOptionImgRes = $pdo->iterator("select a.* from {$tbl['product_option_img']} a inner join {$tbl['product_option_set']} b on a.opno = b.no where a.`pno`='$pno' order by b.sort, a.no");
		}
		$data = $prdOptionImgRes->current();
        $prdOptionImgRes->next();
		if(!$data['no']) {
			unset($prdOptionImgRes);
			return false;
		}

		$file_dir=getFileDir($data['updir']);

		$data['img1']=$file_dir."/".$data['updir']."/".$data['upfile1'];
		$data['img2']=$file_dir."/".$data['updir']."/".$data['upfile2'];

		return $data;
	}

	function getWMDefault($_code) {
		global $tbl, $pdo;

		foreach($_code as $key=>$val) {
			$asql.=" or `code`='$val'";
		}
		$asql=substr($asql,3);
		$tmp=array();
		$res = $pdo->query("select * from `".$tbl['default']."` where $asql");
		foreach ($res as $data) {
			$tmp[$data['code']]=$data['value'];
		}
		return $tmp;
	}

	function refPrdList($pno,$w,$h,$title_cut=0,$col_cut=0,$imgn=3, $refkey = 1) {
		global $refPrdRes,$tbl,$prd,$refIdx, $pdo;
		if(isset($refPrdRes) == false) {
			$refPrdRes = $pdo->iterator("select b.* from {$tbl['product_refprd']} a inner join {$tbl['product']} b on a.refpno=b.no where `pno`=:pno and `group`=:refkey and b.stat in (2,3) order by `sort` asc", array(
                    ':pno' => $pno,
                    ':refkey' => $refkey,
               ));
		}
        $data = $refPrdRes->current();
        $refPrdRes->next();
		if($data == false) {
			unset($refPrdRes);
			if(!$col_cut) return;
			$per=round(100/$col_cut);
			while($refIdx%$col_cut!=0) {
				echo "<td width=\"$per%\">$blank</td>";
				$refIdx++;
			}
			return;
		}
		$data=prdOneData($data,$w,$h,$imgn,$title_cut,$col_cut);

		if($col_cut>0 && $refIdx > 0 && $refIdx%$col_cut==0 && $GLOBALS['prdlist_disable_tr'] != true) {
			echo "<tr class='$col_cut'></tr>";
		}
		$refIdx++;
		return $data;
	}

	// 세트 상품의 데이터 정리 및 옵션 출력
	function parseSetPrd($prd, $multi_idx)
	{
		$prd = prdOneData($prd, null, null, 3);

        $prd['link'] .= '&ref=set';

		// 상품 옵션
		unset($GLOBALS['prdOptionRes']);
		$GLOBALS['opt_no'] = 0;
		$GLOBALS['prd']['parent'] = $prd['parent'];
		while ($ref_opt = prdOptionList(null, null, true, null, $multi_idx)) {
			$prd['option'] .= $ref_opt['hidden_str'].$ref_opt['option_str'];
		}
		$prd['btn_script'] = "return addMultiSet($multi_idx);";
        $prd['soldout'] = ($prd['stat'] == '2') ? '' : 'Y';
        $prd['preview_link'] = ($prd['perm_dtl'] == 'N') ? null : "quickDetailPopup(this, '{$prd['no']}', 'ref=set');";

		return $prd;
	}

	function reviewCommentList() {
		global $review,$reviewCommentRes,$tbl,$member, $cfg, $pdo;
		if($review['total_comment']==0) {
			return;
		}
		if(isset($reviewCommentRes) == false) {
			$orderby=($cfg['product_review_com_sort'] == 2) ? "order by `no`" : "order by `no` desc";
			$reviewCommentRes = $pdo->iterator("select * from `$tbl[review_comment]` where `ref`='$review[no]' $orderby");
		}
        $data = $reviewCommentRes->current();
        $reviewCommentRes->next();
		if($data == false) {
            unset($reviewCommentRes);
			return false;
		}
		$data['content']=nl2br(stripslashes($data['content']));
		$data['content']="<div id=\"reviewCom".$data['no']."\">".$data['content']."</div>";
		$data['reg_date']=date("Y/m/d",$data['reg_date']);
		$data['mod_link']="<wisamall2006 ";
		if(getDataAuth2($data)) {
			$data['del_link'] = "<a href=\"javascript:delRevCmt({$data['no']})\">";
			$data['del_link_ajax'] = "<a href='#' onclick='return delRevCmtAjax({$data['no']})'>";
			if($member['no'] && $data['member_no'] == $member['no']) $data['mod_link']="<a href=\"javascript:modRevCmt($data[no])\">";
		}
		else {
			$data['del_link'] = $data['del_link_ajax'] = "<wisamall2006 ";
		}
		$data['name']=reviewName($data,'review');
		return $data;
	}

	function outPutCate($type ,$val=""){
		global $cfg;
		if(!$cfg['product_'.$type.'_cate'] || !$type) return;
		$arr=explode(",",$cfg['product_'.$type.'_cate']);
		$data=selectArray($arr,"cate",1, __lang_board_select_cartegory__,$val);
		return $data;
	}

	function prdCache($no) {
		global $_prd_cache, $pdo;

		if(!$no) return;
		if($_prd_cache[$no]) {
			$prd = $_prd_cache[$no];
		} else {
            $prd = $pdo->assoc("select * from {$GLOBALS['tbl']['product']} where no='$no'");
			$_prd_cache[$prd['no']]=$prd;
		}
		return $prd;
	}

?>