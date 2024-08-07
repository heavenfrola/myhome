<?PHP

	$data = $pdo->assoc("select * from `$tbl[partner_shop]` where `no`='$admin[partner_no]'");
	$data = array_map('stripslashes', $data);

	$res = $pdo->iterator("select name, admin_id, cell, email from $tbl[mng] where partner_no='$admin[partner_no]'");
	function parseMdata($res) {
		$mdata = $res->current();
        $res->next();
		if ($mdata == false) return false;

		$mdata = array_map('stripslashes', $mdata);
		return $mdata;
	}

?>
<table class="tbl_row">
	<caption>입점사 정보</caption>
	<colgroup>
		<col style="width:13%">
		<col style="width:87%">
	<colgroup>
	<tr>
		<th scope="row">입점사명</th>
		<td><?=$data['corporate_name']?></td>
	</tr>
	<tr>
		<th scope="row">사업자 등록번호</th>
		<td><?=$data['biz_num']?></td>
	</tr>
	<tr>
		<th scope="row">통신판매업신고번호</th>
		<td><?=$data['com_num']?></td>
	</tr>
	<tr>
		<th scope="row">업태/업종</th>
		<td>
			<?=$data['service_type1']?> /
			<?=$data['service_type2']?>
		</td>
	</tr>
	<tr>
		<th scope="row">대표자명</th>
		<td><?=$data['ceo']?></td>
	</tr>
	<tr>
		<th scope="row" rowspan="2">사업장 소재지</th>
		<td>
			<?=$data['addr1']?>
		</td>
	</tr>
	<tr>
		<td><?=$data['addr2']?></td>
	</tr>
	<tr>
		<th scope="row">이메일</th>
		<td><?=$data['email']?></td>
	</tr>
	<tr>
		<th scope="row">연락처</th>
		<td><?=$data['cell']?></td>
	</tr>
	<tr>
		<th scope="row">사이트 URL</th>
		<td><?=$data['siteurl']?></td>
	</tr>
</table>
<br>

<table class="tbl_row">
	<caption>담당자 정보</caption>
	<colgroup>
		<col style="width:13%">
		<col style="width:87%">
	<colgroup>
	<tr>
		<th scope="row">등록 담당자</th>
		<td>
			<ul class="list_msg pd">
				<?while($mdata = parseMdata($res)) {?>
				<li><?=$mdata['name']?> (<?=$mdata['admin_id']?> , <?=$mdata['cell']?> , <?=$mdata['email']?>)</li>
				<?}?>
			</ul>
		</td>
	</tr>
</table>
<br>

<table class="tbl_row">
	<caption>정산 및 계약정보</caption>
	<colgroup>
		<col style="width:13%">
		<col style="width:87%">
	<colgroup>
	<tr>
		<th scope="row">결제 계좌 명의</th>
		<td><?=$data['bank_name']?></td>
	</tr>
	<tr>
		<th scope="row">결제 은행</th>
		<td><?=$data['bank']?></td>
	</tr>
	<tr>
		<th scope="row">은행 계좌 번호</th>
		<td><?=$data['bank_account']?></td>
	</tr>
	<tr>
		<th scope="row">계약 수수료율</th>
		<td>
			<?=$data['partner_rate']?> %
		</td>
	</tr>
	<tr>
		<th scope="row">계약 상태</th>
		<td>
			<?=$_partner_stats[$data['stat']]?>
		</td>
	</tr>
	<tr>
		<th scope="row">계약 기간</th>
		<td>
			<?=date('Y-m-d',$data['dates'])?> ~ <?=date('Y-m-d',$data['datee'])?>
		</td>
	</tr>
	<tr>
		<th scope="row">정산 일자</th>
		<td>
			지난달 매출을
			매월 <strong><?=$data['account_dates']?></strong> 일에 정산
		</td>
	</tr>
</table>
<br>