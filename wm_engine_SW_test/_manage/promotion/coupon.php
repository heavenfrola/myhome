<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 관리
	' +----------------------------------------------------------------------------------------------+*/

	$is_type = addslashes($_GET['is_type']);
	if(!$is_type) $is_type="A";
	$add_q=" and `is_type`='$is_type'";
	$is_type_title=($is_type == "A") ? "온라인" : "시리얼";

?>
<form name="couponFrm" method="post" action="./" target="hidden<?=$now?>">
<input type="hidden" name="body" value="promotion@coupon.exe">
<input type="hidden" name="no" value="">
<input type="hidden" name="exec" value="">
<input type="hidden" name="del_type" value="">
	<div class="box_title first">
		<h2 class="title"><?=$is_type_title?>쿠폰  관리</h2>
	</div>
	<table class="tbl_col tbl_col_bottom">
		<caption class="hidden"><?=$is_type_title?> 쿠폰  관리</caption>
		<colgroup>
			<col style="width:40px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">쿠폰명</th>
				<?if($is_type <> "B"){?>
				<th scope="col">발급방식</th>
				<?}?>
				<th scope="col">할인금액(율)</th>
				<th scope="col">사용제한</th>
				<th scope="col">최대 할인금액</th>
				<th scope="col">발급<? if($is_type == "B") echo "수량"; else echo "기간"; ?></th>
				<th scope="col">사용기간</th>
				<?if($is_type == "B"){?>
				<th scope="col">인증코드</th>
				<?}?>
				<th scope="col">수정</th>
				<th scope="col">삭제</th>
				<?if($is_type == "A"){?>
				<th scope="col">회수</th>
				<th scope="col">CSV 일괄지급</th>
				<th scope="col">적용범위</th>
				<?}?>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql="select * from `$tbl[coupon]` where 1 $add_q order by `no` desc";
				$res = $pdo->iterator($sql);

                foreach ($res as $data) {
					switch($data['sale_type']) {
						case 'm' : $sale_type = $cfg['currency_type']; break;
						case 'p' : $sale_type = '%'; break;
						case 'e' : $sale_type = '개'; break;
					}

					if($data[rdate_type]==1) {
						$data[rdate_type]="무제한";
					}
					else {
						$data[rdate_type]="$data[rstart_date] <br>~ $data[rfinish_date]";
					}

					if($data[udate_type]==1) {
						$data[udate_type]="무제한";
					}
					else if($data['udate_type'] == 2) {
						$data[udate_type]="$data[ustart_date] <br>~ $data[ufinish_date]";
					} else { // 발급일로부터 사용제한 추가 2013-04-26
						$data[udate_type]="발급일로부터 $data[udate_limit]일 까지";
					}

					if($data[sale_type]=="m") {
						$data[sale_limit]="";
					}
					else {
						$data[sale_limit]=number_format($data[sale_limit]).' '.$cfg['currency_type'];
					}

					if(!$data['device']) {
						$data['device'] = "<img src=\"".$engine_url."/_manage/image/icon/pc.gif\" alt=\"PC\">&nbsp;&nbsp;<img src=\"".$engine_url."/_manage/image/icon/mobile.gif\" alt=\"모바일\">&nbsp;&nbsp;<img src=\"".$engine_url."/_manage/image/icon/app.gif\" alt=\"앱모바일\">";
					} else {
						if($data['device'] == "mobile_all") {
							$data['device'] = "<img src=\"".$engine_url."/_manage/image/icon/mobile.gif\" alt=\"모바일\">&nbsp;&nbsp;<img src=\"".$engine_url."/_manage/image/icon/app.gif\" alt=\"앱모바일\">";
						} else {
							$data['device'] = "<img src=\"".$engine_url."/_manage/image/icon/".$data['device'].".gif\">";
						}
					}

					$idx++;
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
			?>
			<tr>
				<td><?=$idx?></td>
				<td class="left"><a href='?body=promotion@coupon_register&is_type=<?=$is_type?>&no=<?=$data['no']?>' class="btt" tooltip="<?=$data['name']?>"><?=cutStr(stripslashes($data[name]),30)?></a></td>
				<?if($is_type <> "B"){?>
				<td>
					<?
					echo couponTpName($data['down_type'], $data);
					if(in_array($data['down_type'], array('B', 'L', 'L2'))) {
						if($data['down_grade'] > 0) {
							echo "<br><span style=\"color:#9F2D86;\">(".getGroupName($data['down_grade']);
							if($data[down_gradeonly] == "Y") echo "만"; else echo "이상";
							echo ")</span>";
						}
					}
					?>
				</td>
				<?}?>
				<td><?=number_format($data[sale_prc]).$sale_type?></td>
				<td><?=number_format($data[prc_limit])?> <?=$cfg['currency_type']?></td>
				<td><?=$data[sale_limit]?></td>
				<td>
					<?if($is_type == "B") {?>
						<?=($data['release_limit'] == 1) ? '무제한' : number_format($data['release_limit_ea'])?>
					<?} else {?>
						<?=$data['rdate_type']?>
					<?}?>
				</td>
				<td><?=$data[udate_type]?></td>
				<?if($is_type == "B"){?>
				<td>
					<span class="box_btn_s"><a href="javascript:;" onclick="authCodeXls('<?=$data[no]?>');">엑셀출력</a></span><br>
					<span class="box_btn_s"><a href="./?body=promotion@coupon_code_list&no=<?=$data[no]?>">코드확인</a></span>
				</td>
				<?}?>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="location.href='?body=promotion@coupon_register&is_type=<?=$is_type?>&no=<?=$data['no']?>'"></span>
				</td>
				<td><span class="box_btn_s gray"><input type="button" value="삭제" onClick="deleteCoupon(<?=$data['no']?>)"></span></td>
				<?if($is_type == "A"){?>
				<td><span class="box_btn_s gray"><input type="button" value="회수" onClick="cpnRecall(<?=$data['no']?>)"></span></td>
				<td>
					<span class='box_btn_s'><input type='button' value='선택' onclick="putCoupons(<?=$data['no']?>);"></span>
				</td>
				<td><?=$data['device']?></td>
				<?}?>
			</tr>
			<?}?>
		</tbody>
	</table>
</form>

<script type="text/javascript">
function deleteCoupon(n){
	if (!confirm('선택하신 쿠폰을 삭제하시겠습니까?\n고객에게 지급된 미사용 쿠폰들이 같이 삭제됩니다.')) return;

	f=document.couponFrm;
	f.no.value=n;
	f.exec.value='delete';
	f.submit();
}

function authCodeXls(no){
	hidden<?=$now?>.window.location="./?body=promotion@coupon_code_xls.exe&no="+no;
}

function putCoupons(no) {
	wisaOpen('./pop.php?body=promotion@coupon_csv.frm&no='+no, 'putCoupons', false, 500, 300);
}

function cpnRecall(no) {
	if(confirm('발급된 쿠폰을 모두 회수(발급취소) 처리 합니다.\n이미 사용한 쿠폰은 회수되지 않습니다.\n\n진행하시겠습니까?')) {
		$.post('./index.php', {'body':'promotion@coupon.exe', 'exec':'recall', 'no':no}, function(r) {
			window.alert(r);
			document.location.replace();
		});
	}
}
</script>