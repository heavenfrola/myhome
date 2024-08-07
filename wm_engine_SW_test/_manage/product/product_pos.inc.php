<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$page = numberOnly($_GET['page']);

	$opt_data = array();
	$set_name = array();
	$ores = $pdo->iterator("select no, name from ".$tbl['product_option_set']." where pno='$pno' and necessary in ('Y', 'C') and otype!='4B' order by sort desc");
    foreach ($ores as $oset) {
		$set_name[] = stripslashes($oset['name']);
		$_temp = $opt_data;
		$res2 = $pdo->iterator("select * from ".$tbl['product_option_item']." where pno='$pno' and opno='$oset[no]' order by sort asc");
        foreach ($res2 as $odata) {
			$iname = stripslashes($odata['iname']);
			if($odata['ori_no']) $odata['no'] = $odata['ori_no'];
			if(count($opt_data) == 0) {
				$_temp[$odata['no']] = $iname;
			} else {
				foreach($opt_data as $key => $val) {
					$_temp[$key.'_'.$odata['no']] = $iname.'<ss>'.$val;
					unset($_temp[$key]);
				}
			}
		}
		$opt_data = $_temp;
	}
	$total_complex = count($opt_data);
	if($total_complex == 0) {
		$set_name[] = '-';
		$opt_data[''] = '옵션없음';
	}
	krsort($set_name);

	if(!$page_row) $page_row = 50;
	if($total_complex > $page_row) {
		include $engine_dir.'/_engine/include/paging.php';

		if($page <= 1) $page = 1;
		$block = 20;
		$QueryString = '';
		foreach($_GET as $key => $val) {
			if($key == 'page') continue;
			$QueryString .= "&$key=".urlencode($val);
		}

		$PagingInstance = new Paging($total_complex, $page, $page_row, $block);
		$PagingInstance->addQueryString($QueryString);
		$PagingResult = $PagingInstance->result($pg_dsn);

		$complex_start = $PagingResult['LimitIndex'];
		$complex_end = $complex_start+$page_row-1;
	}

?>
<form id="erp_baseFrm" method="post" action="" target="hidden<?=$now?>" onsubmit="return initBarcode(this);">
	<input type="hidden" name="body" value="product@product_option.exe">
	<input type="hidden" name="exec" value="initial_complex">
	<input type="hidden" name="pno" value="<?=$pno?>">
	<input type="hidden" name="rurl" value="session_reload">
	<div class="box_title_reg">
		<h2 class="title">재고 리스트</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">재고 리스트</caption>
		<thead>
			<tr>
				<?php foreach ($set_name as $name) { ?>
				<th scope="col"><?=$name?></th>
				<?php } ?>
				<th scope="col" style="width:120px">품절방식</th>
				<th scope="col" style="width:220px">
					<?php if ($cfg['use_dooson'] == 'Y') { ?>
					SKU
					<?php } else { ?>
					바코드
					<?php } ?>
				</th>
				<th scope="col" style="width:130px">재고수량</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$erp_set = 0;
				$idx = -1;
				foreach($opt_data as $key => $val) {
					$idx++;
					if($total_complex > $page_row) {
						if($idx < $complex_start) continue;
						if($idx > $complex_end) break;
					}
					$key = makeComplexKey($key);
					$item_name = explode('<ss>', $val);
					$data = $pdo->assoc("select complex_no, barcode, force_soldout, curr_stock(complex_no) as curr from erp_complex_option where pno='$pno' and opts='$key' and del_yn='N'");
					if($data['complex_no'] > 0) $erp_set++;
					else $erp_notset++;
			?>
			<tr>
				<?php foreach ($item_name as $name) { ?>
				<td class="left"><?=$name?></td>
				<?php } ?>
				<td>
					<?php if ($data['curr'] !== null) { ?>
						<?=$_erp_force_stat[$data['force_soldout']]?>
					<?php } else { ?>
						<select name="force_soldout[<?=$idx?>]">
							<option value="L"><?=$_erp_force_stat['L']?></option>
							<option value="N"><?=$_erp_force_stat['N']?></option>
							<option value="Y"><?=$_erp_force_stat['Y']?></option>
						</select>
					<?php } ?>
				</td>
				<td>
					<?php if ($data['barcode']) { ?>
						<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><strong><?=$data['barcode']?></strong></a>
					<?php } else { ?>
						<input type="text" class="input" name="barcode[<?=$idx?>]" value="<?=$data['barcode']?>">
					<?php } ?>
				</td>
				<td>
					<?php if ($data['barcode']) { ?>
						<?=$data['curr']?>
					<?php } else { ?>
						<input type="text" class="input" name="bstock[<?=$idx?>]" size="10">
					<?php } ?>
					<input type="hidden" name="complex_no[<?=$idx?>]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="complex_optno[<?=$idx?>]" value="<?=$key?>">
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom right">
		<?php if ($erp_notset > 0) { ?>
		<span class="explain">
            <?=selectArray($_erp_force_stat, 'erp_force_stat', false, null, 'L')?>
			<input type="text" id="qty_all" class="input" size="5">
			<span class="box_btn_s blue"><input type="button" value="초기 재고수량 일괄 입력" onclick="qty_replace()"></span>
			<span class="box_btn_s blue"><input type="submit" value="바코드생성"></span>
		</span>
		<?php } ?>

		<?php if ($erp_set > 0) { ?>
		<span class="box_btn_s">
			<input type="button" value="윙POS 재고확인" onclick="window.open('/_manage/?body=erp@stock_adjust&exec=search&search_type=hash&search_str=<?=$prd['hash']?>')">
		</span>
		<?php } ?>

		<?php if ($total_complex > $page_row) { ?>
		<div class="paging center" style="width: 100%;">
			<?=$PagingResult['PageLink']?>
		</div>
		<?php } ?>
	</div>
</form>

<script type="text/javascript">
	function qty_replace() {
		var f = document.getElementById('erp_baseFrm');
		var qty = document.getElementById('qty_all');
		qty = parseInt(qty.value);

		if(qty < 0 || isNaN(qty) == true) {
			window.alert('일괄 입력 할 재고수량을 숫자로 입력 해 주십시오.');
			return false;
		}
		$('input[name^="bstock["]').val(qty);
		$('select[name^="force_soldout["]').val($('select[name=erp_force_stat]').val());
	}

	if(parent.checkInputData) {
		$('.input, .txta').each(function() {
			var o = $(this);
			if(this.value) parent.checkInputData(o);
			o.bind({
				'change' : function() {parent.checkInputData(o)},
				'keyup' : function() {parent.checkInputData(o)}
			});
		});
	}

    function initBarcode(f) {
        if (confirm('바코드를 생성하시겠습니까?') == true) {
            printLoading();
            return true;
        }
        return false;
    }
</script>