<?PHP

    /**
     * 상품 정보 일괄 변경
     **/

	set_time_limit(0);
	ini_set('memory_limit', -1);

	use Wing\API\Kakao\KakaoTalkPay;
    use Wing\common\WorkLog;

	checkBasic();
	$nsql = $asql = '';

	$nums = trim($_POST['nums']);
	$check_pno = numberOnly($_POST['check_pno']);
	$pno = numberOnly($_POST['pno']);
	$exec = $_POST['exec'];
	$w = $_POST['w'];
	$imgcopy = $_POST['imgcopy'];
	$detailcopy = $_POST['detailcopy'];
	$income_use = $_POST['income_use'];

    $prd_join = '';

    $log = new WorkLog();

	if($nums) {
		if($exec != 'fullcopy' && $admin['level'] == 4 && $cfg['partner_prd_accept'] == 'Y') {
			if(!trim($_POST['partner_cmt'])) msg('상품 변경 내용 및 사유를 입력해 주세요.');
		}
		$check_pno=explode("@",$nums);
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$nno=$check_pno[$ii];
			if(!$nno) {
				continue;
			}
			if($exec=="event" || $exec=="shortcut") {
				$op=get_info($tbl[product],"no",$nno);
				if($op[content2]=="wm_sc") $nno=$op[content1];
				if($op['wm_sc'] > 0) $nno = $op['wm_sc'];
			}
			// 바로가기 두번 복사 방지
			if($exec=="shortcut" || $exec == 'move' || $exec == 'fullcopy') {
				$nsql.=" or `no`='$nno'";
			} else {
				if($income_use) {
					$nsql.=" or p.`no`='$nno' or p.`wm_sc`='$nno'";
				}else {
					$nsql.=" or `no`='$nno' or `wm_sc`='$nno'";
				}
			}
			if($admin['level'] > 3 && $cfg['partner_prd_accept'] == 'Y' && $exec != 'fullcopy') {
				include_once $engine_dir.'/_partner/lib/partner_product.class.php';
				$pp = new PartnerProduct();
				$name = $pdo->row("select `name` from `wm_product` where `no` = '$nno'");
				$pp->setLog(array(
					'pno' => $nno,
					'req_stat' => 1,
					'name' => $_POST['name'],
					'content' => $_POST['partner_cmt'],
				));
			}
		}
		$nsql=substr($nsql,4);
		$wsql=" and ( $nsql )";
	}

	elseif($w) {
		$w = stripslashes($w);
		$wsql=$w;

        if (preg_match('/pr\.pgrp_no/', str_replace('`', '', $w)) == true) {
            $prd_join .= " inner join {$tbl['promotion_pgrp_link']} pr on p.no=pr.pno";
        }
	}

	if($admin['level'] > 3) {
		$wsql .= "and `partner_no` = '$admin[partner_no]'";
	}

	// 가격, 상태 수정
	if(!$exec) {
		foreach($pno as $ii => $_pno) {
			$_pno = numberOnly($_pno);
			$data = $pdo->assoc("select * from $tbl[product] where no='$_pno'");
			if(!$data['no']) continue;

			$_sell_prc = numberOnly($_POST['sell_prc'][$ii], true);
			$_normal_prc = numberOnly($_POST['normal_prc'][$ii], true);
			$_milage = numberOnly($_POST['milage'][$ii], true);
			$_stat = numberOnly($_POST['stat'][$ii]);

			$opno = ($data['wm_sc'] > 0) ? $data['wm_sc'] : $_pno;

			$add_q = '';
			$hits = $pdo->assoc("select sum(buy_ea) as orders, sum(if(stat=5,buy_ea,0)) as sales from $tbl[order_product] where pno='$opno' and stat between 1 and 5");
			if($cfg['prd_normal_prc'] == 'Y') {
				$add_q .= ", normal_prc='$_normal_prc'";
			}
            if ($data['prd_type'] != '4') {
                $add_q .= ", sell_prc='$_sell_prc', milage='$_milage'";
            }

			if($data['stat'] != $_stat) {
				$pdo->query("update `{$tbl['product']}` set `stat`='$_stat' where `no`='$opno' or (wm_sc='$opno' and `stat` != 5)");
			}

			if($pdo->query("update `{$tbl['product']}` set hit_sales='$hits[sales]', hit_order='$hits[orders]' $add_q where `no`='$opno' or `wm_sc`='$opno'")) {
				if($data['wm_sc'] == 0 && $data['stat'] != $_stat) prdStatLogw($opno, $_stat, $data['stat']);
			}

            $log->createLog(
                $tbl['product'],
                (int) $opno,
                'name',
                $data,
                $pdo->assoc("select * from {$tbl['product']} where no=?", array($opno)),
                array('hit_sales', 'hit_order')
            );
		}
	}
	// 이동,복사,바로가기생성
	elseif($exec=="move" || $exec=="shortcut") {
		$nctype = numberOnly($_POST['nctype']);
		$nbig = numberOnly($_POST['nbig']);
		$nmid = numberOnly($_POST['nmid']);
		$nsmall = numberOnly($_POST['nsmall']);
		$ndepth4 = numberOnly($_POST['ndepth4']);

		if(!$nbig) {
			msg('이동/복사할 분류를 선택하세요.');
		}

		if($nmid) checkBlank($nbig, "대분류를 입력해주세요.");
		if($nsmall) checkBlank($nmid, "중분류를 입력해주세요.");
		if($nctype == 4) $nctype_txt = "x";
		elseif($nctype == 5) $nctype_txt = "y";
		else $nctype_txt = "";
		switch($exec) {
			case 'move':
				if($cfg['max_cate_depth'] >= 4) {
					$add_qry .= ", {$nctype_txt}depth4='$ndepth4'";
				}
				$sql = "update `".$tbl['product']."` p $prd_join set `{$nctype_txt}big`='$nbig',`{$nctype_txt}mid`='$nmid',`{$nctype_txt}small`='$nsmall' $add_qry where 1 $wsql";
				$pdo->query($sql);

				if($nctype == 4 || $nctype == 5) {
					$pres = $pdo->iterator("select p.no from $tbl[product] p $prd_join where 1 $wsql");
                    foreach ($pres as $pdata) {
						createProductLink($pdata['no'], $nctype, $nbig, $nmid, $nsmall, $ndepth4);
					}
				}
				break;
			case 'shortcut':
				$_sort = array("big", "mid", "small");
				if($cfg['max_cate_depth'] >= 4) {
					$_sort[] = 'depth4';
				}

				$sql = "select p.* from `".$tbl['product']."` p $prd_join where 1 $wsql";
				$res = $pdo->iterator($sql);
				$pno = $pdo->row("select max(no) from {$tbl['product']}");
                foreach ($res as $data) {
                    if ($data['wm_sc'] > 0) { // 검색된 상품 수정 바로가기
                        $data = $pdo->assoc("select * from {$tbl['product']} where no='{$data['wm_sc']}'");
                    }
					$add_qry1 = $add_qry2 = '';
					if($cfg['max_cate_depth'] >= 4) {
						$add_qry1 .= ", depth4, xdepth4, ydepth4, sortdepth4";
						$add_qry2 .= ", '$ndepth4', '$data[xdepth4]', '$data[ydepth4]', '$data[sortdepth4]'";
					}
					foreach($_sort as $sl) {
						$ncate = ${'n'.$sl};
						$data['sort'.$sl] = ($ncate) ? $pdo->row("select max(`sort$sl`)+1 from `$tbl[product]` where `$sl`='$ncate'") : 0;
					}
					if($cfg['use_partner_shop'] == 'Y') {
						$add_qry1 .= ", partner_no";
						$add_qry2 .= ", '{$data['partner_no']}'";
					}
					$add_qry1 .= ", seller_idx, seller";
					$add_qry2 .= ", '{$data['seller_idx']}', '{$data['seller']}'";

					$pno++;
					$hash = strtoupper(md5($pno));
					$data['name'] = addslashes($data['name']);
					$data['keyword'] = addslashes($data['keyword']);
					$sql = "INSERT INTO `".$tbl['product']."` (`no`,`hash`,`stat`,`reg_date`,`edt_date`,`prd_type`,`big`,`mid`,`small`,`xbig`,`xmid`,`xsmall`,`ybig`,`ymid`,`ysmall`,`content1`,`content2`,`name`,`keyword`,`code`,`sell_prc`,`event_sale`,`dlv_alone`,`member_sale`,`free_delivery`,`wm_sc`,`sortbig`,`sortmid`,`sortsmall` $add_qry1) VALUES ('$pno', '$hash', '$data[stat]', '$now', '$now', '$data[prd_type]', '$nbig', '$nmid', '$nsmall','$data[xbig]','$data[xmid]','$data[xsmall]','$data[ybig]','$data[ymid]','$data[ysmall]','$data[no]', 'wm_sc','$data[name]','$data[keyword]','$data[code]','$data[sell_prc]','$data[event_sale]','$data[dlv_alone]','$data[member_sale]','$data[free_delivery]','$data[no]','$data[sortbig]','$data[sortmid]','$data[sortsmall]' $add_qry2)";
					$pdo->query($sql);
				}
				break;
		}
	}
	elseif($exec == 'fullcopy') {
		$nbig = numberOnly($_POST['nbig']);
		$nmid = numberOnly($_POST['nmid']);
		$nsmall = numberOnly($_POST['nsmall']);
		$ndepth4 = numberOnly($_POST['ndepth4']);

		if($nbig < 0) msg("이동/복사할 분류를 선택하세요");
		$res = $pdo->iterator("select p.* from $tbl[product] p $prd_join where 1 $wsql");
        foreach ($res as $data) {
			$sql1 = $sql2 = $asql = '';

			$ori = $data['no'];
			$no = $pdo->row("select max(no) from $tbl[product]")+1;

			unset($data['use_talkstore'], $data['n_store_check'], $data['nstoreId']);

			$data['no'] = $no;
			$data['hash'] = strtoupper(md5($no));
			$data['big'] = ($detailcopy == 'Y') ? $data['big'] : $nbig;
			$data['mid'] = ($detailcopy == 'Y') ? $data['mid'] : $nmid;
			$data['small'] = ($detailcopy == 'Y') ? $data['small'] : $nsmall;
            if ($cfg['max_cate_depth'] >= 4) {
    			$data['depth4'] = ($detailcopy == 'Y') ? $data['depth4'] : $ndepth4;
            }
			$data['name'] = ($detailcopy == 'Y') ? '(복사) '.$data['name'] : $data['name'];
			$data['stat'] = 4;
			$data['ea'] = 0;
			$data['updir'] = ($imgcopy == 'Y') ? $data['updir'] : "";
			$data['sortbig'] = $pdo->row("select max(sortbig)+1 from $tbl[product]");
			$data['sortmid'] = $nmid > 0 ? $pdo->row("select max(sortmid)+1 from $tbl[product] where mid='$nmid'") : 0;
			$data['sortsmall'] = $nsmall > 0 ? $pdo->row("select max(sortmid)+1 from $tbl[product] where small='$nsmall'"): 0;
            if ($cfg['max_cate_depth'] >= 4) {
    			$data['sortdepth4'] = $depth4 > 0 ? $pdo->row("select max(sortdepth4)+1 from $tbl[product] where depth4='$ndepth4'") : 0;
            }
			$data['ebig'] = '';
			$data['reg_date'] = $now;
			$data['edt_date'] = 0;
			$data['hit_view'] = $data['hit_order'] = $data['hit_sales'] = $data['hit_search'] = $data['hit_search'] = $data['hit_cart'] = $data['hit_wish'] = 0;
			$data['qna_cnt'] = $data['rev_cnt'] = $data['rev_avg'] = $data['rev_total'] = 0;

			foreach($data as $key => $val) {
				if(preg_match('/^(upfile[0-9]+)$/', $key)) $val = '';
				if(preg_match('/^(w[0-9]+)|(h[0-9]+)|(sort[0-9a-z]+)|(hit_[a-z]+)$/', $key)) $val = '';
				$val = addslashes($val);

				if($sql1) $sql1 .= ",";
				if($sql2) $sql2 .= ",";
				$sql1 .= "`$key`";
				$sql2 .= "'$val'";
			}
			$pdo->query("insert into $tbl[product] ($sql1) values ($sql2)");

			// 상품 옵션 복사
            $itemNoMatch = array();
			$xres = $pdo->iterator("select * from `$tbl[product_option_set]` where pno='$ori'");
            foreach ($xres as $xdata) {
				$xdata['pno'] = $no;
				$xdata['updir'] = $xdata['upfile1'] = '';
				$xdata['items_ea'] = '';
				$xdata['reg_date'] = $now;

				$sql1 = $sql2 = '';
				foreach($xdata as $xkey => $xval) {
					if($xkey == 'no') continue;
					$sql1 .= $sql1 ? ",`$xkey`" : "`$xkey`";
					$sql2 .= $sql2 ? ",'$xval'" : "'$xval'";
				}
				$pdo->query("insert into $tbl[product_option_set] ($sql1) values ($sql2)");
				$new_opno = $pdo->lastInsertId();

				$yres = $pdo->iterator("select * from $tbl[product_option_item] where opno='$xdata[no]'");
                foreach ($yres as $ydata) {
					$ydata['pno'] = $no;
					$ydata['opno'] = $new_opno;
					$ydata['ea'] = 0;
					$ydata['reg_date'] = $now;

					$sql1 = $sql2 = '';
					foreach($ydata as $ykey => $yval) {
						if($ykey == 'no') continue;
						$sql1 .= $sql1 ? ",`$ykey`" : "`$ykey`";
						$sql2 .= $sql2 ? ",'$yval'" : "'$yval'";
					}
					$pdo->query("insert into $tbl[product_option_item] ($sql1) values ($sql2)");
                    $itemNoMatch[$ydata['no']] = $pdo->lastInsertId();
				}
			}

			// 상품 필드 복사
			$zres = $pdo->iterator("select * from `$tbl[product_filed]` where pno='$ori'");
            foreach ($zres as $zdata) {
				$zdata['pno'] = $no;
				$pdo->query("insert into $tbl[product_field] (pno, fno, value) values ('$no', '$zdata[fno]', '$zdata[value]')");
			}

            // 관련 상품 복사
            $rres = $pdo->iterator("select * from {$tbl['product_refprd']} where pno='$ori'");
            foreach ($rres as $rdata) {
                unset($rdata['no']);
                $rdata['pno'] = $no;

                $__fields = $__values = '';
                foreach ($rdata as $f => $v) {
                    $__fields .= ", `$f`";
                    $__values .= ", '$v'";
                }
                $__fields = substr($__fields, 1);
                $__values = substr($__values, 1);

                $pdo->query("
                    insert into {$tbl['product_refprd']}
                        ($__fields) values ($__values)
                ");
                unset($__fields, $__values);

                fwriteTo('_data/ref.txt', $pdo->getqry()."\n");
            }

			if($imgcopy) {
				$img_count = ($detailcopy == "Y") ? $cfg['mng_add_prd_img']+3 : 3;
				for($ii=1; $ii<=$img_count; $ii++) {
					if($data['upfile'.$ii]) {
						$ext = getExt($data['upfile'.$ii]);
						if(!preg_match("/jpg|jpeg|gif|png/i", $ext)) msg("썸네일을 만들수 없는 이미지 형식입니다");
						$up_filename = md5($ii*time()+$no).".".$ext;

						if($_use['file_server'] == "Y" && fsConFolder($data['updir'])){
							$updir = $root_dir."/".$dir['upload']."/auto_thumb";
							fsFileDown($data['updir'], $data['upfile'.$ii], $updir);
							if(is_file($updir."/".$data['upfile'.$ii])){
								fsUploadFile($data['updir'], $updir."/".$data['upfile'.$ii], $up_filename);
								@unlink($updir."/".$data['upfile'.$ii]);
							}
						} else {
							$updir = $root_dir."/".$data['updir'];
						    copy($updir."/".$data['upfile'.$ii], $updir."/".$up_filename);
						}

						$asql.=",`upfile".$ii."`='".$up_filename."' , `w".$ii."`='".$data['w'.$ii]."' , `h".$ii."`='".$data['h'.$ii]."'";
					}
				}
				if($detailcopy == "Y") {
                    $isOptionItemImg = (fieldExist($tbl['product_image'], 'option_item_no')) ? 'Y' : 'N';
					$zres = $pdo->iterator("select * from `$tbl[product_image]` where pno='$ori'");
                    foreach ($zres as $zdata) {
						$ext = getExt($zdata['filename']);
						$up_filename=md5($ii*time()+$zdata['no']).".".$ext;

						if($_use['file_server'] == "Y" && fsConFolder($zdata['updir'])){
							$updir = $root_dir."/".$dir['upload']."/auto_thumb";
							fsFileDown($zdata['updir'], $zdata['filename'], $updir);;
							if(is_file($updir."/".$zdata['filename'])){
								fsUploadFile($zdata['updir'], $updir."/".$zdata['filename'], $up_filename);
								@unlink($updir."/".$zdata['filename']);
							}
						} else {
							$updir = $root_dir."/".$zdata['updir'];
						    copy($updir."/".$zdata['filename'], $updir."/".$up_filename);
						}

                        if ($isOptionItemImg == 'Y') {
                            $opItemNo = $itemNoMatch[$zdata['option_item_no']];
                            $asql_pi = ", option_item_no";
                            $asql_pi2 = ", '$opItemNo'";
                        } else {
                            $asql_pi = '';
                            $asql_pi2 = '';
                        }

						$pdo->query("insert into $tbl[product_image] (pno, filetype, updir, filename, ofilename, stat, reg_date, width, height, filesize, sort $asql_pi) values ('$no', '$zdata[filetype]', '$zdata[updir]', '$up_filename', '$zdata[ofilename]', '$zdata[stat]', '$now', '$zdata[width]', '$zdata[height]', '$zdata[filesize]', '$zdata[sort]' $asql_pi2)");
					}
				}

				$sql="update `".$tbl['product']."` set `updir`='".$data['updir']."' $asql where `no`='$no'";
				$pdo->query($sql);
			}

            // 입점사 등록 신청내역 생성
            if($admin['level'] == 4 && $cfg['partner_prd_accept'] == 'Y') {
                include_once $engine_dir.'/_partner/lib/partner_product.class.php';
                $pp = new PartnerProduct();
                $pp->setLog(array(
                    'pno' => $no,
                    'req_stat' => 5,
                    'name' => $data['name'],
                    'content' => '상품복사',
                ));
            }
		}
		if($detailcopy) {
			echo $no;
			exit;
		} else {
			msg('상품 복사가 완료되었습니다.', 'reload', 'parent');
		}
	}
	// 이벤트
	elseif($exec=="event") {
		$event = addslashes(trim($_POST['event']));
		$dlv_alone = addslashes(trim($_POST['dlv_alone']));
		$member_sale = addslashes(trim($_POST['member_sale']));
		$free_delivery = addslashes(trim($_POST['free_delivery']));
		$oversea_free_delivery = addslashes(trim($_POST['oversea_free_delivery']));
		$checkout = addslashes(trim($_POST['checkout']));
		$talkpay = addslashes(trim($_POST['talkpay']));
		$import_flag = addslashes(trim($_POST['import_flag']));
		$compare_today_start = addslashes(trim($_POST['compare_today_start']));
		$no_milage = addslashes(trim($_POST['no_milage']));
		$no_cpn = addslashes(trim($_POST['no_cpn']));
        $no_ep = addslashes(trim($_POST['no_ep']));

        $asql = $asql_all = '';
		if($event) {
			$asql.="`event_sale`='$event'";
		}
		if($dlv_alone) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`dlv_alone`='$dlv_alone'";
		}
		if($member_sale) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`member_sale`='$member_sale'";
		}
		if($free_delivery) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`free_delivery`='$free_delivery'";
			if($cfg['use_prd_dlvprc'] == 'Y') {
				$asql .= ", delivery_set=0";
			}
		}
		if($oversea_free_delivery) {
			if(!fieldExist($tbl['product'], 'oversea_free_delivery')) addField($tbl['product'], 'oversea_free_delivery', "enum('N','Y') not null default 'N' comment '해외무료배송여부' after free_delivery");
			if($asql) {
				$asql.=",";
			}
			$asql.="`oversea_free_delivery`='$oversea_free_delivery'";
		}
		if($checkout) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`checkout`='$checkout'";
		}
		if($talkpay) {
			if($asql) {
				$asql.=",";
			}
			$asql.="use_talkpay='$talkpay'";
		}
		if($import_flag) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`import_flag`='$import_flag'";
		}
		if($compare_today_start) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`compare_today_start`='$compare_today_start'";
		}
		if($no_milage) {
			if($asql_all) {
				$asql_all.=",";
			}
			$asql_all.="`no_milage`='$no_milage'";
		}
		if($no_cpn) {
			if($asql_all) {
				$asql_all.=",";
			}
			$asql_all.="`no_cpn`='$no_cpn'";
		}
		if($no_ep) {
			if($asql) {
				$asql.=",";
			}
			$asql.="`no_ep`='$no_ep'";
		}
		if(!$asql && !$asql_all) {
			msg("상품 설정의 변화가 없습니다");
		}

        $res = $pdo->iterator("select p.* from {$tbl['product']} p $prd_join where 1 $wsql");
        foreach ($res as $data) {
            if ($data['prd_type'] == '1' && $asql) {
                $pdo->query("update {$tbl['product']} set $asql where no='{$data['no']}'");
            }
            if ($asql_all) {
                $pdo->query("update {$tbl['product']} set $asql_all where no='{$data['no']}'");
            }

            if ($data['wm_sc'] == '0') {
                $log->createLog(
                    $tbl['product'],
                    (int) $data['no'],
                    'name',
                    $data,
                    $pdo->assoc("select * from {$tbl['product']} where no=?", array($data['no']))
                );
            }
        }

        if ($talkpay == 'Y') {
            // 특정 조건에 카카오페이 구매 사용 불가능 처리
            $pdo->query("update {$tbl['product']} p set use_talkpay='N' where ea_type!='1' $wsql");

            // 카카오페이구매 정보고시 추가
            $kres = $pdo->iterator("select no, wm_sc from {$tbl['product']} where 1 $wsql");
            $kko = new KakaoTalkPay($scfg);
            foreach ($kres as $kdata) {
                $kko->setAnnoucement(
                    ($kdata['wm_sc'] > 0) ? $kdata['wm_sc'] : $kdata['no'],
                    $_POST['kakao_annoucement_idx']
                );
            }
        }

		msg("모두 변경하였습니다","reload","parent");
	}
	// 주문/판매수 일치
	elseif($exec=="saleOrdNum") {
		$pdo->query("update `wm_product` p $prd_join set `hit_sales` = (select sum(`buy_ea`) from `wm_order_product` where `pno` = p.`no` and stat = '5') where 1 $wsql");
		$pdo->query("update `wm_product` p $prd_join set `hit_order` = (select sum(`buy_ea`) from `wm_order_product` where `pno` = p.`no` and `stat` in (1,2,3,4,5)) where 1 $wsql");
	}
	elseif($exec == 'seller') {
		$seller_idx = (int) $_POST['seller_idx'];
		$seller = $pdo->row("select provider from $tbl[provider] where no='$seller_idx'");
        if (empty($seller) == true) {
            msg('존재하지 않는 사입처입니다.');
        }

        $res = $pdo->iterator("select p.* from {$tbl['product']} p $prd_join where 1 $wsql");
        foreach ($res as $data) {
            $pdo->query("update {$tbl['product']} p $prd_join set seller_idx=?, seller=? where p.no=?", array(
                $seller_idx, $seller, $data['no']
            ));

            $log->createLog(
                $tbl['product'],
                (int) $data['no'],
                'name',
                $data,
                $pdo->assoc("select * from {$tbl['product']} where no=?", array($data['no']))
            );
        }
	}
	elseif($exec == 'field') {
		$fno = numberOnly($_POST['fno']);
		$fvalue = addslashes(trim($_POST['fvalue']));
		checkBlank($fvalue, '변경할 항목을 입력해주세요.');

		$cnt = 0;
		$field = $pdo->assoc("select * from $tbl[product_field_set] where no='$fno'");
		$res = $pdo->iterator("select p.no from $tbl[product] p $prd_join where 1 $wsql");
        foreach ($res as $data) {
			if($pdo->row("select count(*) from $tbl[product_field] where pno='$data[no]' and fno='$fno'") > 0) {
				$r = $pdo->query("update $tbl[product_field] set value='$fvalue' where pno='$data[no]' and fno='$fno'");
			} else {
				$r = $pdo->query("insert into $tbl[product_field] (pno, fno, value) values ('$data[no]', '$fno', '$fvalue')");
			}
			if($r) $cnt++;
		}

		msg("$cnt 개의 항목이 수정되었습니다.", "reload", "parent");
	}
	elseif($exec == 'toEbig') {
		$ebig_mode = $_POST['ebig_mode'];
		$ebig_first = $_POST['ebig_first'];
		$ebig = numberOnly($_POST['ebig']);
		$mbig = numberOnly($_POST['mbig']);
		$income_use = $_POST['income_use'];
		$cq = $_POST['cq'];
		$g = $_POST['g'];
		$ord = $_POST['ord'];
		$row = $_POST['row'];
		$prd_field = $_POST['prd_field'];

		if(count($ebig) < 1 && count($mbig) < 1) {
			msg('추가/제외할 기획전을 선택해 주세요.');
		}

		if($income_use) {
			$old_pno = array();
			$sql_text = "select p.*, sum(`buy_ea`) as `amount`, sum(o.`total_prc` $cq) as `price`, o.`option` from $prd_field p inner join `$tbl[order_product]` o on p.`no` = o.`pno` inner join `$tbl[order]` x on o.`ono` = x.`ono` $prd_join where o.`stat` not in (11, 31, 32) and (x.x_order_id='' or x.x_order_id in ('checkout', 'talkstore') or x.x_order_id is null) $w $wsql group by o.`pno` $g order by $ord limit $row";
		}else {
			$sql_text = "select p.* from $tbl[product] p $prd_join where 1 $wsql order by reg_date desc";
		}

		if ($_POST['ebig_first'] == 'D') {
			for($i = 0; $i <= 1; $i++) {
				$etype = $i == 0 ? 'ebig' : 'mbig';
					//상품별매출에서 선택항목
				if (is_array($_POST[$etype])) {
					foreach($_POST[$etype] as $cno) {
						$pdo->query("delete from $tbl[product_link] where nbig='$cno'");
                        $res = $pdo->iterator("select no, name, ebig, mbig from {$tbl['product']} where {$etype} like '%@$cno@%'");
                        foreach ($res as $data) {
    						$pdo->query("update {$tbl['product']} set `$etype`=replace(`$etype`,'@$cno','') where no='{$data['no']}'");

                            $log->createLog(
                                $tbl['product'],
                                (int) $data['no'],
                                'name',
                                $data,
                                $pdo->assoc("select no, name, ebig, mbig from {$tbl['product']} where no=?", array($data['no']))
                            );
                        }
					}
				}
			}
		}

		$res = $pdo->iterator($sql_text);
		$sort_date = array();
        foreach ($res as $data) {
			$sort_date[] = $data;
		}
		if($_POST['ebig_first'] == 'Y') {
			$sort_date = array_reverse($sort_date);
		}
		foreach($sort_date as $key=>$val) {
			$asql = '';
			$pno = $val['wm_sc'] == 0 ? $val['no'] : $val['wm_sc'];
			for($i = 0; $i <= 1; $i++) {
				$etype = $i == 0 ? 'ebig' : 'mbig';
				$ctype = $i == 0 ? '2' : '6';

				$new_tmp = explode('@', preg_replace('/^@|@$/', '', $val[$etype]));
				if(is_array($_POST[$etype])) {
					foreach($_POST[$etype] as $cno) {
						$check = in_array($cno, $new_tmp);

						if($ebig_mode == 'add' && $check === false) { // 추가
							$new_tmp[] = $cno;

							if(!$pdo->row("select count(*) from $tbl[product_link] where pno='$pno' and nbig='$cno'")) {
								if($_POST['ebig_first'] != 'Y') {
									$sort1 = $pdo->row("select max(sort_big) from $tbl[product_link] where nbig='$cno'")+1;
								} else {
									$pdo->query("update $tbl[product_link] set sort_big=sort_big+1 where nbig='$cno'");
								}
								$pdo->query("
									insert into $tbl[product_link] (pno, ctype, nbig, nmid, nsmall, sort_big, sort_mid, sort_small)
									values ('$pno', '$ctype', '$cno', '0', '0', '$sort1', '0', '0')
								");
							}
						}
						if($ebig_mode == 'remove' && $check === true) {
							$pdo->query("delete from $tbl[product_link] where pno='$pno' and nbig='$cno'");
							$idx = array_search($cno, $new_tmp);
							unset($new_tmp[$idx]);
						}
					}
				}
				$new_tmp = implode('@', $new_tmp);
				if($new_tmp) $new_tmp = '@'.preg_replace('/^@/', '', $new_tmp).'@';
				${'new_'.$etype} = $new_tmp;
			}

			if($cfg['mobile_use'] == 'Y') $asql .= ", mbig='$new_mbig'";

			$pdo->query("update $tbl[product] set ebig='$new_ebig' $asql where no='$val[no]'");

            $log->createLog(
                $tbl['product'],
                (int) $val['no'],
                'name',
                $val,
                $pdo->assoc("select no, name, ebig, mbig from {$tbl['product']} where no=?", array($val['no']))
            );
		}
        javac("parent.removeLoading();");
        exit;
	}else if($exec == 'exchangeRate'){
		$where = $_POST['where'];
		$o1 = $_POST['o1'];
		$w = '';

		// 상품 조건
		if($where==1) {
			$_nums = explode("@",$nums);
			foreach($_nums as $key=>$val) {
				if(!$val) continue;
				$w .= " or `no`='$val'";
			}
			$w = substr($w,4);
			$w = " and ($w)";
		} else {
			$w = stripslashes($_POST['w']);
		}

		$lw = array();

		$manage_price = numberOnly($cfg['cur_manage_price'],$cfg['m_currency_decimal']);
		$sell_price = numberOnly($cfg['cur_sell_price'],$cfg['currency_decimal']);

		$sql="select p.* from `$tbl[product]` as p where 1 $w";
		$res = $pdo->iterator($sql);
        foreach ($res as $data) {
			if($data[content2]=='wm_sc') {
				$data=get_info($tbl[product],"no",$data[content1]);
			}

			unset($lw);

			if($o1 == 'all'){
				if($data['m_sell_prc']) $lw[] = "sell_prc='".($data['m_sell_prc']/$manage_price) * $sell_price."'";
				if($data['m_normal_prc']) $lw[] = "normal_prc='".($data['m_normal_prc']/$manage_price) * $sell_price."'";
			}else{
				if($data["m_${o1}"]) $lw[] = $o1."='".($data["m_${o1}"]/$manage_price) * $sell_price."'";
			}

			if(count($lw) > 0) {
				$query = "update `$tbl[product]` set ".implode(',',$lw)." where no='${data['no']}'";
				$pdo->query($query);
			}
		}
	}else if($exec == 'timesale'){
		$ts_use = ($_POST['ts_use'] == 'Y') ? 'Y' : 'N';
		$ts_dates = strtotime($_POST['ts_dates'].' '.$_POST['ts_times'].':'.$_POST['ts_mins'].':00');
		$ts_datee = strtotime($_POST['ts_datee'].' '.$_POST['ts_timee'].':'.$_POST['ts_mine'].':59');
		$ts_saleprc = numberOnly($_POST['ts_saleprc']);
		$ts_saletype = ($_POST['ts_saletype'] == 'price') ? 'price' : 'percent';
		$ts_state = numberOnly($_POST['ts_state']);
		$use_ts_set = $_POST['use_ts_set'];
		$ts_set = numberOnly($_POST['ts_set']);

		if($ts_use == 'Y') {
			if($use_ts_set == 'Y') {
				checkBlank($ts_set, '타임세일 정책을 선택해주세요.');

				$ts = $pdo->assoc("select ts_use, ts_dates, ts_datee, ts_set from {$tbl['product_timesale_set']} where no='$ts_set'");
				$ts_dates = strtotime($ts['ts_dates']);
				$ts_datee = strtotime($ts['ts_datee']);
				$ts_state = $ts['ts_state'];
				$ts_names = $ts_namee = '';

			} else {
				checkBlank($_POST['ts_dates'], '한정판매 시작일을 입력해주세요.');
                if (isset($_POST['ts_unlimited']) == false) {
    				checkBlank($_POST['ts_datee'], '한정판매 종료일을 입력해주세요.');
                } else {
                    $ts_datee = 0;
                }
			}
		}

		$ts_ing = ($ts_dates <= $now && ($ts_datee == 0 || $ts_datee >= $now)) ? 'Y' : 'N';
        $res = $pdo->iterator("select p.* from {$tbl['product']} p $prd_join where prd_type=1 $wsql");
        foreach ($res as $prd) {
            $pdo->query("update {$tbl['product']} p $prd_join set ts_use='$ts_use', ts_dates='$ts_dates', ts_datee='$ts_datee', ts_names='$ts_names', ts_namee='$ts_namee', ts_saleprc='$ts_saleprc', ts_saletype='$ts_saletype', ts_state='$ts_state', ts_ing='$ts_ing', ts_set='$ts_set' $asql where p.no='{$prd['no']}'");
            $pdo->query("update {$tbl['product']} p $prd_join set ts_use='$ts_use', ts_dates='$ts_dates', ts_datee='$ts_datee', ts_names='$ts_names', ts_namee='$ts_namee', ts_state='$ts_state', ts_ing='$ts_ing', ts_set='$ts_set' $asql where prd_type!=1 and p.no='{$prd['no']}'"); // 세트상품

            if ($prd['wm_sc'] == 0) {
                $log->createLog(
                    $tbl['product'],
                    (int) $prd['no'],
                    'name',
                    $prd,
                    $pdo->assoc("select * from {$tbl['product']} where no=?", array($prd['no']))
                );
            }
        }
	} else if($exec == 'createTsField') {
		addField($tbl['product'], 'ts_set', 'int(5) not null default "0" after ts_use');
		addField($tbl['product'], 'ts_event_type', 'enum("1","2") not null default "1" after ts_namee');
		addField($tbl['product'], 'ts_cut', 'int(4) not null default "1" after ts_saletype');

		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['product_timesale_set']);
	}
    // 상품정보고시
    else if ($exec == 'annoucement') {
        $annoucements = (int) $_POST['annoucements'];
        $kakao_annoucement_idx = (int) $_POST['kakao_annoucement_idx'];

        $res = $pdo->iterator("select p.no, p.fieldset from {$tbl['product']} p $prd_join where wm_sc=0 $wsql");
        foreach ($res as $data) {
            if ($annoucements > 0 && $data['fieldset'] != $annoucements) {
                $pdo->query("update {$tbl['product']} set fieldset='$annoucements' where no='{$data['no']}'");
                if ($data['fieldset'] > 0) {
                    $pdo->query("
                        delete FROM {$tbl['product_field']}
                        WHERE pno=$pno and fno IN (select no from {$tbl['product_field_set']} WHERE category='{$data['fieldset']}'
                    ");
                }
            }
            if ($kakao_annoucement_idx > 0) {
                if (is_object($kko) == false) {
                    $kko = new KakaoTalkPay($scfg);
                }
                $kko->setAnnoucement(
                    $data['no'], $kakao_annoucement_idx
                );
            }
        }
    }
	// 그외
	else {
		$edate=$now;
		if($exec=="delete") {
			include_once $engine_dir.'/_manage/product/product_wdisk.inc.php';
		}
		if($exec == 'truncate') {
			$check_pno = array();
			$tmpres = $pdo->iterator("select no from $tbl[product] where stat=5");
            foreach ($tmpres as $tmpdata) {
				$check_pno[] = $tmpdata['no'];
			}
		}
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			// 삭제
			if($exec=="delete" || $exec == 'truncate') {
				$data=$pdo->assoc("select no, name, hash, stat, del_stat, del_date from `$tbl[product]` where `no`='$check_pno[$ii]' limit 1");
				delPrd($check_pno[$ii]);
				productLogw($check_pno[$ii],$data['name'],3); // 2008-11-07 : 상품로그 - Han

                $log->createLog(
                    $tbl['product'],
                    (int) $data['no'],
                    'name',
                    $data,
                    $pdo->assoc("select no, name, stat, del_stat, del_date from {$tbl['product']} where no=?", array($data['no']))
                );
			}
			// 복구
			if($exec=="restore") {
				$data = $pdo->assoc("select no, name, stat, del_stat from $tbl[product] where no='$check_pno[$ii]'");
				$pdo->query("update $tbl[product] set stat='$data[del_stat]', del_date=0, del_admin='', del_stat=1 where no='$check_pno[$ii]'");
				prdStatLogw($check_pno[$ii], $data['del_stat'], $data['stat']);

                $log->createLog(
                    $tbl['product'],
                    (int) $data['no'],
                    'name',
                    $data,
                    $pdo->assoc("select no, name, stat from {$tbl['product']} where no=?", array($data['no']))
                );
			}
			// 수정일
			elseif($exec=="update") {
				$sql="update `".$tbl['product']."` set `edt_date`='$edate' where `no`='$check_pno[$ii]'";
				$pdo->query($sql);
				$edate--;
			}
			elseif($exec=="sp_sort") {
				$_sort_idx = numberOnly($_POST['sortidx'][$ii]);
				$_sort = numberOnly($_POST['sort'][$ii]);
				$pdo->query("update $tbl[product_link] set sort_big='$_sort' where idx='$_sort_idx'");

			}
			elseif($exec=="sp_out") {
				$cno = numberOnly($_POST['cno']);
				$mno = numberOnly($_POST['mno']);
				if($cno > 0) {
					$fname = 'ebig';
				} elseif($mno > 0) {
					$fname = 'mbig';
					$cno = $mno;
				} else {
					msg('기획전 코드가 존재하지 않습니다.');
				}

				$pdo->query("update `".$tbl['product']."` set `$fname`=replace(`$fname`,'@$cno','') where `no`='$check_pno[$ii]'");
				$pdo->query("delete from $tbl[product_link] where pno='$check_pno[$ii]' and nbig='$cno'");
			}
			elseif($exec=="sp_in" && $cno) {
				$sql="update `".$tbl['product']."` set `ebig`=concat(`ebig`,'@','$cno') where `no`='$check_pno[$ii]' and `ebig` not like '%@$cno%'";
				$pdo->query($sql);
			}
			elseif($exec=="thumb") {
				$updir=$asql=$up_filename="";
				$data=get_info($tbl['product'],"no",$check_pno[$ii]);

				$updir=$root_dir."/".$data[updir];
				if(!$data[upfile1] || !is_file($updir."/".$data[upfile1])) continue;

				$ext=getExt($data[upfile1]);
				if(!preg_match('/jpg|jpeg|gif|png/', $ext)) continue;

				for($j=2; $j<=3; $j++) {
					$up_filename = md5($check_pno[$ii]*$j*time()).".".$ext;
					$result = makeThumb($updir.'/'.$data['upfile1'], $updir.'/'.$up_filename, $cfg['thumb'.$j.'_w'],$cfg['thumb'.$j.'_h']);
					$width = $result['width'];
					$height = $result['height'];

					if($j==3) $asql.=",";
					$asql.="`upfile".$j."`='".$up_filename."' , `w".$j."`='".$width."' , `h".$j."`='".$height."'";
					@unlink($updir."/".$data['upfile'.$j]);
					unset($GD);
				}

				$sql="update `".$tbl['product']."` set $asql where `no`='".$check_pno[$ii]."'";
				$pdo->query($sql);

				unset($data);
			}
		}
	}


	msg("","reload","parent");

?>