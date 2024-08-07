<?PHP

	function addRef($refkey, $pno, $prd_no) {
		global $tbl, $now, $pdo;
		if($pdo->row("select count(*) from `$tbl[product_refprd]` where `pno`='$pno' and `group`='$refkey' and `refpno`='$prd_no'") > 0) {
			return false;
		}

		$sort = $pdo->row("select max(`sort`) from `$tbl[product_refprd]` where `pno`='$pno' and `group`='$refkey'");
		$sort++;
		$pdo->query("insert into $tbl[product_refprd] (`pno`, `group`, `refpno`, `sort`, `reg_date`) values ('$pno', '$refkey', '$prd_no', '$sort', '$now')");

		if($refkey == '99') {
			getSetPrice($pno, true);
		}

		return true;
	}

	$pno = numberOnly($_POST['pno']);
	$del_no = numberOnly($_POST['del_no']);
	$prd_no = numberOnly($_POST['prd_no']);
	$opno = numberOnly($_POST['opno']);
	$updown = addslashes($_POST['updown']);
	$exec = addslashes($_POST['exec']);
	$refkey = numberOnly($_POST['refkey']);
	$save_type = numberOnly($_POST['save_type']);

	if($opno > 0 && $pno > 0 && $updown) $exec = 'sort';
	elseif($del_no > 0) $exec = 'remove';
	elseif($prd_no > 0) $exec = 'add';

	switch($exec) {
		case 'sort';
			$data = $pdo->assoc("select `no`, `sort`, `pno`, `group` from `$tbl[product_refprd]` where `no`='$opno'");
			if($updown == 'up') $w = "and sort < '$data[sort]' order by sort desc limit 1";
			else $w = "and sort > '$data[sort]' order by sort asc limit 1";

			$target = $pdo->assoc("select `no`, `sort` from `$tbl[product_refprd]` where `pno`='$data[pno]' and `group`='$data[group]' ".$w);
			if($target) {
				$pdo->query("update `$tbl[product_refprd]` set `sort`='$target[sort]' where `no`='$data[no]'");
				$pdo->query("update `$tbl[product_refprd]` set `sort`='$data[sort]' where `no`='$target[no]'");
			}
		break;
		case 'sort_all' :
			$data = numberOnly($_POST['data']);
			foreach($data as $key => $refno) {
				$sort = ($key+1);
				$pdo->query("update {$tbl['product_refprd']} set sort='$sort' where no='$refno'");
			}
			exit;
		break;
		case 'remove' :
			$data = $pdo->assoc("select `pno`, `group` from `{$tbl['product_refprd']}` where `no`='$del_no'");
			$pdo->query("delete from `$tbl[product_refprd]` where `no`='$del_no'");
			if($data['group'] == '99') {
				getSetPrice($data['pno'], true);
			}
		break;
		case 'add' :
            header('Content-type:application/json; charset='._BASE_CHARSET_);
            if ($refkey == '99') {
                $exists = $pdo->row("
                    select count(*) from {$tbl['product_refprd']} where pno=? and refpno=? and `group`=99",
                    array($pno, $prd_no)
                );
                if ($exists > 0) {
                    exit(json_encode(array(
                        'result' => 'faild',
                        'message' => '이미 등록된 상품입니다.',
                    )));
                }

                $prd = $pdo->assoc("select dlv_type, dlv_alone, partner_no from {$tbl['product']} where no=?", array($prd_no));
                if ($prd['dlv_type'] == '1') $prd['partner_no'] = '0';
                $set_partner_no = $pdo->row("
                    select if(a.dlv_type=1, '0', partner_no) from {$tbl['product']} a inner join {$tbl['product_refprd']} b on a.no=b.refpno
                    where pno='{$pno}' and `group`=99
                ");
                if (gettype($set_partner_no) == 'string' && $set_partner_no != $prd['partner_no']) {
                    exit(json_encode(array(
                        'result' => 'faild',
                        'message' => '서로 다른 입점사 상품은 세트 상품으로 구성할 수 없습니다.',
                    )));
                }
                if ($prd['dlv_alone'] == 'Y') {
                    exit(json_encode(array(
                        'result' => 'faild',
                        'message' => '단독배송 상품은 세트에 추가할수 없습니다.',
                    )));
                }
            }
			addRef($refkey, $pno, $prd_no);
			if($save_type == 2) { // 상호등록
				addRef($refkey, $prd_no, $pno);
			}
		break;
        case 'checkPerm' :
            header('Content-type: application/json');

            $count = $pdo->row("select count(*) from {$tbl['product_refprd']} where `group`=99 and pno!=? and refpno=?", array(
                $_POST['pno'], $_POST['refpno']
            ));
            exit(json_encode(array(
                'result' => 'success',
                'count' => $count,
                'pno' => $_POST['pno'],
                'refpno' => $_POST['refpno'],
            )));
            break;
        case 'setPerm' :
            if ($admin['level'] == '4' && $scfg->comp('partner_prd_accept', 'N') == false) {
                exit(json_encode(array('affected' => 0)));
            }

            $checked = ($_POST['checked'] == 'true') ? 'N' : 'Y';
            $pdo->query("
                update {$tbl['product']}
                    set perm_lst='$checked', perm_dtl='$checked', perm_sch='$checked'
                    where no='$pno'
            ");
            exit(json_encode(array('affected' => $pdo->lastRowCount())));
            return;
        break;
	}

	include $engine_dir.'/_manage/product/product_ref_frm.exe.php';

?>