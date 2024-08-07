<?
	printAjaxHeader();
	checkBasic();

	//if($cfg['repay_part'] != 'Y') msg('주문 부분상태 변경 설정이 되어있지 않습니다.');

	$stat = numberOnly($_POST['stat']);
	$exec = $_POST['exec'];

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['member_no'] > 0) {
		$amember = $pdo->assoc("select * from $tbl[member] where no='$ord[member_no]'");
	}

	$stat = numberOnly($_POST['stat']);

	$_repay_part_stat = array(13 => '주문취소',15 => '환불');
	$title = $_repay_part_stat[$stat];

	ob_start();
?>
<input type="hidden" name="cno" value="<?=$cno?>">
<table class="tbl_row">
	<caption><?=$title?> 처리</caption>
	<colgroup>
		<col style="width:15%;">
		<col>
	</colgroup>
	<tbody>
	<?
		for($ii = 0; $ii < count($pno); $ii++) {
			$prd = $pdo->assoc("select * from $tbl[order_product] where no='$pno[$ii]'");
			$prd['name'] = addslashes($prd['name']);
			$_prdname = strip_tags($prd['name']);
			$_prdoption = addslashes(str_replace('<split_small>', ' : ', str_replace('<split_big>', ' / ', $prd['option'])));

			if(($prd['stat']==4 || $prd['stat'] == 5|| $prd['stat'] == 6) && ($stat==12 || $stat==13 || $stat==14 || $stat==15)) {
				msg("배송된 주문은 취소 및 환불 처리하실수 없습니다.\\n반품/교환 처리만 가능합니다.");
			}

			$cancel_prc = $prd['total_prc']-$prd['sale2']-$prd['sale3']-$prd['sale4']-$prd['sale5']-$prd['sale6'];
			if($prd['repay_prc'] > 0) $cancel_prc = $prd['repay_prc'];
		?>
		<tr style="display:<?=$stat=='13'?'none':''?>;">
				<th scope='row' style='width:auto;'><input type=hidden name='repay_no[]' value='<?=$prd[no]?>'>&nbsp;<?=$_prdname?>&nbsp;<div><?=$_prdoption?></div></th>
				<td>환불금액 : <span id="repay_prc_txt<?=$prd['no']?>"><?=parsePrice($cancel_prc)?></span> <?=$cfg['currency_type']?> <input type='hidden' name='repay_prc[]' id="repay_prc<?=$prd['no']?>" value='<?=$cancel_prc?>' /></td>
				<td>환불갯수 :
					<select name="repay_buy_ea<?=$prd[no]?>" -onchange="repay_prc_calc(this,'<?=$prd['no']?>','<?=$prd['buy_ea']?>')">
					<? for($i=$prd['buy_ea'];$i>=1;$i--) { ?>
					<option value="<?=$i?>"><?=$i?></option>
					<? } ?>
					</select>
				</td>
				<? if($amember){ ?>
					<td>
						반환상품적립금: <?=($prd['total_milage']-$prd['member_milage'])?> <input type="hidden" name='repay_milage[]' value='<?=($prd['total_milage']-$prd['member_milage'])?>'> <?=$cfg['currency_type']?>,
						반환회원적립금: <?=$prd[member_milage]?> <input type="hidden" name='repay_member_milage[]' value='<?=$prd[member_milage]?>'> <?=$cfg['currency_type']?>
					</td>
				<? } ?>
		</tr>
	<?
		}
	?>

	<? if($stat == '15') { ?>
	<tr>
		<th scope="row">환불타입</th>
		<td colspan="3">
			<select name="refund_type">
				<option value="AMOUNT_AND_ITEM">주문과 금액 취소</option>
			</select>
		</td>
	</tr>
	<?
	}
		$cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$ono'");
		if($cpn_no > 0) {
			$cpn = $pdo->assoc("select name, cno from $tbl[coupon_download] where no='$cpn_no'");
			$cpn['name'] = stripslashes($cpn['name']);

	?>

	<tr>
		<th scope='row'>쿠폰반환</th>
		<td colspan="3">
			<label><input type='checkbox' name='cpn_no' value='<?=$cpn_no?>'> 사용한 쿠폰을 고객에게 반환합니다. (적용된 쿠폰할인가는 자동 반영되지 않습니다.)</label>
			<ul class='list_msg'>
				<li><?=$cpn[name]?> <a href='#' onclick='cpndetail.open(\"no=<?=$cpn[cno]?>?>&readOnly=true\"); return false;' class='sclink'>상세정보</a></li>
			</ul>
		</td>
	</tr>
	<?
		}

		$reasons = '';
		$rres = $pdo->iterator("select reason from $tbl[claim_reasons] order by sort asc");
        foreach ($rres as $rdata) {
			$rdata['reason'] = stripslashes($rdata['reason']);
			$reasons .= "<option>$rdata[reason]</option>";
		}
	?>
	<tr>
		<th scope="row"><?=$title?> 사유</th>
		<td colspan="3">
			<select name="reason">
				<option value=''>:: 사유를 선택해주세요 ::</option>
				<?=$reasons?>
			</select>
			<label><input type='checkbox' name='copytomemo' value='Y'> 입력한 상세 사유를 메모에도 등록</label>
			<p style='margin-top: 5px;'><textarea class='txta' name='comment' cols='80' rows='5'></textarea></p>
		</td>
	</tr>
	</tbody>
</table>


<div class="box_bottom">
	<span class="box_btn blue"><input type="button" value="확인" onclick="jsPrdStatSet(this.form);"></span>
	<span class="box_btn gray"><input type="button" value="닫기" onclick="layTgl(repayDetail);"></span>
</div>
<?
	$script = php2java(ob_get_clean());

?>
<script type="text/javascript">
parent.prd_stat_refresh = 0;
parent.prdStat = <?=$stat?>;
parent.$('#repayDetail', parent.document).html("<?=$script?>").show();
</script>