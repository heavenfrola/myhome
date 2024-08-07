<?PHP

	$pno = numberOnly($_GET['pno']);
	$stat = numberOnly($_GET['stat']);

?>
<form name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" class="pop_width">
	<input type="hidden" name="body" value="product@product_option.exe">
	<input type="hidden" name="exec" value="copy">
	<input type="hidden" name="pno" value="<?=$pno?>">
	<input type="hidden" name="stat" value="<?=$stat?>">
	<table class="tbl_col">
		<caption class="hidden">옵션세트 불러오기</caption>
		<colgroup>
			<col style="width:8%">
			<col style="width:30%">
			<col>
			<col style="width:20%">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">이름</th>
				<th scope="col">속성</th>
				<th scope="col">미리보기</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
				if($cfg['use_partner_shop'] == 'Y') $where .= " and partner_no='$admin[partner_no]'";

				$sql="select * from `".$tbl['product_option_set']."` where `stat`='5' $where order by `sort`";
				$res = $pdo->iterator($sql);
				$total = $res->rowCount();
				$idx=0;
                foreach ($res as $data) {
					if($data['necessary'] == 'C') $data['necessary'] = 'Y';
					switch($data['necessary']) {
						case 'Y' : $necessary = '필수'; break;
						case 'N' : $necessary = '선택'; break;
					}
					$rclass=($idx%2==0) ? "tcol3" : "tcol2";
					$idx++;
					if($data['sort']!=$idx) {
						$pdo->query("update `$tbl[product_option_set]` set `sort`='$idx' where `no`='$data[no]'");
					}
			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>"></td>
				<td class="left"><?=stripslashes($data['name'])?></td>
				<td class="left"><?=$_otype[$data['otype']]?> (<?=$necessary?>)</td>
				<td align="center">
					<span class="box_btn_s"><a href="javascript:;" onClick="wisaOpen('./pop.php?body=product@product_option_preview&opno=<?=$data['no']?>','pfldopt1')">미리보기</a></span>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_middle2 right">
		<span class="box_btn_s gray"><input type="button" value="삭제" onclick="deletePrdOption('', document.prdFrm);"></span>
		<span class="box_btn_s blue"><input type="button" value="불러오기" onclick="loadOption(document.prdFrm);"></span>
	</div>
	<div class="pop_bottom"><?=$close_btn?></div>
</form>

<script type="text/javascript">
	setPoptitle('옵션세트 불러오기');
</script>