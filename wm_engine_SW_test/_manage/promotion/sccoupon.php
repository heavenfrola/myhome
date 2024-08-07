<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 관리
	' +----------------------------------------------------------------------------------------------+*/

	define('sccoupon', true);
	include_once $engine_dir."/_manage/promotion/sccoupon_install.exe.php";

?>
<form name="couponFrm" method="post" action="./" target="hidden<?=$now?>">
<input type="hidden" name="body" value="promotion@sccoupon.exe">
<input type="hidden" name="no" value="">
<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">소셜쿠폰 관리</h2>
	</div>
	<table class="tbl_col tbl_col_bottom">
		<caption class="hidden">소셜 쿠폰  관리</caption>
		<colgroup>
			<col style="width:40px">
			<col span="7">
			<col style="width:70px">
			<col style="width:70px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">쿠폰명</th>
				<th scope="col">교환적립금</th>
				<th scope="col">교환쿠폰</th>
				<th scope="col">교환기간</th>
				<th scope="col">메모</th>
				<th scope="col">등록일</th>
				<th scope="col">코드</th>
				<th scope="col">수정</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql="select *, (select `name` from `$tbl[coupon]` where `no`=a.`cno`) as `cname` from `$tbl[sccoupon]` a where 1 order by `no` desc";

				$res = $pdo->iterator($sql);
                foreach ($res as $data) {
					if($data['date_type'] == 1) $data['date_type']="무제한";
					else  $data['date_type']="$data[start_date] ~ $data[finish_date]";
					$data['reg_date']=date('Y-m-d', $data['reg_date']);
					$idx++;
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
			?>
			<tr>
				<td><?=$idx?></td>
				<td class="left"><a href="?body=promotion@sccoupon_register&no=<?=$data['no']?>"><?=cutStr(stripslashes($data['name']),30)?></a></td>
				<td><?=number_format($data['milage_prc'])?></td>
				<td class="left"><?=$data['cname']?></td>
				<td><?=$data['date_type']?></td>
				<td class="left"><a href="javascript:;" onmouseover="showToolTip(event,'<?=addslashes(str_replace("\r", "", str_replace("\t", "", str_replace("\n", "", strip_tags($data['memo'])))))?>')" onmouseout="hideToolTip();" ><?=cutStr($data['memo'], 40)?></a></td>
				<td><?=$data['reg_date']?></td>
				<td>
					<span class="box_btn_s"><a href="javascript:;" onclick="authCodeXls('<?=$data[no]?>');">엑셀출력</a></span><br>
					<span class="box_btn_s"><a href="./?body=promotion@sccoupon_code_list&no=<?=$data[no]?>">코드확인</a></span>
				</td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="location.href='?body=promotion@sccoupon_register&no=<?=$data['no']?>'"></span>
				</td>
				<td><span class="box_btn_s gray"><input type="button" value="삭제" onClick="deleteCoupon('<?=$data['no']?>')"></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
</form>

<script type="text/javascript">
	function deleteCoupon(no){
		if (!confirm('선택하신 쿠폰을 정말로 삭제하시겠습니까?         \n삭제시 복구할 수 없습니다.')) return;
		f=document.couponFrm;
		f.no.value=no;
		f.exec.value='delete';
		f.submit();
	}
	function authCodeXls(no){
		hidden<?=$now?>.window.location="./?body=promotion@sccoupon_code_xls.exe&no="+no;
	}
</script>