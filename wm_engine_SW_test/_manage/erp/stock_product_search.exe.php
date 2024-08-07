<?PHP

	if($_POST['exec'] == 'stock_list' || $body == 'erp@stock_list') {
		printAjaxHeader();

		$pno = numberOnly($_REQUEST['pno']);
		$option = $_REQUEST['option'];
		$tempwhere = '';
		$optionStr = '';
		if(!$pno) return;
		if($option) {
			$tempwhere = array();
			$tempoption = explode (",", $option);
			if(is_array($tempoption)) {
				foreach($tempoption as $key=>$val) {
					$tempwhere[] = " e.opts like '%_".addslashes($val)."_%' ";
				}
				$tempwhere = implode(' and ',$tempwhere);
				$tempwhere = ' and (' . $tempwhere . ')' ;
			}
		}
		$sql = "select p.* from `$tbl[product]`  as p inner join `erp_complex_option` as e on (p.no=e.pno) where p.`no`='$pno' $tempwhere";
		$prd = $pdo->assoc($sql);
        if (!$prd) {
            exit('error');
        }
		$prd = shortCut($prd);
		$prd['name'] = stripslashes($prd['name']);
		$prd['thumb'] = getFileDir($prd['updir'])."/$prd[updir]/$prd[upfile3]";

		//옵션
		if(is_array($tempoption)) {
			$sql = "select poi.iname from `".$tbl['product_option_item']."` as poi where poi.`pno`='".$prd['no']."' and poi.no in (".implode(',',$tempoption).") order by poi.`no` ";
			$optionres = $pdo->iterator($sql);
			if($optionres) {
                foreach ($optionres as $temp) {
					$optionStr .= '[' . $temp['iname'] . ']';
				}
			}
		}
		?>
		<input type="hidden" name="pno" value="<?=$prd['no']?>">
		<input type="hidden" name="option" value="<?=$option?>">
		<div class="box_setup">
			<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><img src="<?=$prd['thumb']?>" width="50px;"></a></div>
			<dl>
				<dt class="title"><a href="?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><?=$prd['name']?></a></dt>
				<dd class="cstr"><?=$prd['origin_name']?></dd>
				<dd><?=number_format($prd['sell_prc'])?> 원</dd>
				<dd><?=$optionStr?></dd>
			</dl>
		</div>
		<?
		if($body == 'order@stock_list') return;
	}
?>