<?PHP

	printAjaxHeader();

	$orderby = numberOnly($_GET['orderby']);

    unset($sql, $res);

	if($_GET['prm_sort']=="Y") {
		$_GET['pno'] = explode(",", $_GET['pno']);
	}
	if($_GET['pno']) {
		if(is_array($_GET['pno'])) {
			$ws = " and no in (".implode(',', $_GET['pno']).")";
			if($orderby) {
				$os = " order by ".$_prd_by[$orderby];
			}else {
				$os = " order by field(no,".implode(',', $_GET['pno']).")";
			}
			$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord from $tbl[product] where wm_sc = 0 $ws $os";
			$res = $pdo->iterator($sql);
		}else {
			$pno = numberOnly($_GET['pno']);
			if($pno) {
				$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord from $tbl[product] where wm_sc = 0 and no='$pno'";
				$res = $pdo->iterator($sql);
			}
		}
	}

	$idx = numberOnly($_GET['idx']);
	if($res) {
		foreach ($res as $prd) {
			if($_GET['prm_sort']=="Y" || $orderby || is_array($_GET['pno'])) {
				$idx++;
			}

			$prd['name'] = strip_tags(stripslashes($prd['name']));
			$prd['sell_prc'] = parsePrice($prd['sell_prc'], true);
			$prd['milage'] = parsePrice($prd['milage'], true);

			if($prd['upfile3']) {
				$file_dir = getFileDir($prd['updir']);
				$prd['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='30' height='30'>";
			}
			$prd['name'] = cutStr($prd['name'], 35, '');

?>
			<tr id="<?=$prd[no]?>" data-idx="<?=$idx?>">
                <td><input type="checkbox" class="cb_prd_add" value="<?=$prd[no]?>>"></td>
                <!-- 상품등록리스트 개별 체크 체크박스 생성 -->
				<td><?=$idx?></td>
				<td class="left">
                    <div class="box_setup" style="padding: 0px;">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
						<dl style="height:30px;">
							<dt class="title"><?=$prd['name']?></dt>
							<dd><a href="./?body=product@product_register&pno=<?=$prd['no']?>" class="p_color" target="_blank">수정</a></dd>
						</dl>
					</div>
				</td>
				<td><?=$prd['sell_prc']?></td>
				<td><?=$prd['milage']?></td>
				<td><?=$_prd_stat[$prd['stat']]?></td>
				<td><span class="box_btn_s gray"><input type="button" value="제외" onclick="prdsearch.pcan(<?=$prd['no']?>, <?=$idx?>)"></span></td>
			</tr>
<?
		}
	}

?>