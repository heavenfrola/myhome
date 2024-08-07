<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  종합매출
	' +----------------------------------------------------------------------------------------------+*/

	extDateReady();

	if(!$start_date || !$finish_date) {
		$start_date=date("Y-m-01",$now);
		$finish_date=date("Y-m-d",$now);
	}

	if(numberOnly($finish_date)>date("Ymd",$now)) {
		$finish_date=date("Y-m-d",$now);
	}

	if($search_mode) {
		$_sdate=explode("-",$start_date);
		$sdate=mktime(0,0,0,$_sdate[1],$_sdate[2],$_sdate[0]);
		$_fdate=explode("-",$finish_date);
		$fdate=mktime(0,0,0,$_fdate[1],$_fdate[2],$_fdate[0]);

		if($fdate>$now) {
			$fdate=$now;
		}
		if($sdate>$fdate) {
			msg("기간 입력이 잘못되었습니다","back");
		}
		$osdate=$sdate;
		$s=true;
	}
	else {
		$mile=1;
	}

?>
<script language="JavaScript">
	var total=new Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	function getIncomeStatic(date) {
		req = newXMLHttpRequest();
		req.onreadystatechange = processReqChange;

		var q = "body=income@income_static.exe&sdate="+date;
		if (f.mile.checked==true) q+="&mile=1";
		if (f.dlv.checked==true) q+="&dlv=1";
		req.open("POST", "./index.php", true);
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.send(q);
	}

	function printData(txt) {
		var result=req.responseText;
		data=result.split("@");
		for (z=1; z<=13; z++)
		{
			var r=eval(data[z]);
			if (typeof r=='undefined' || !r) r=0;
			total[z]+=r;

			tmp=document.getElementById(data[0]+"_"+z);
			if (r<0)
			{
				tr=r*-1;
				r="-"+setComma(tr);
			}
			else r=setComma(r);


			if (tmp) tmp.innerHTML=r;
		}

		var ndate=eval(data[0])+86400;
		if (ndate<fdate)
		{
			getIncomeStatic(ndate);
		}
		else
		{
			for (j=1; j<=13; j++)
			{
				r=total[j];
				r=setComma(r);
				tmp=document.getElementById("total_"+j);
				if (tmp) tmp.innerHTML=r;
			}
		}
	}
	arrKor1 = new Array ('영','일','이','삼','사','오','육','칠','팔','구' );
	arrKor2 = new Array ('일', '만', '억', '조' );
	arrKor3 = new Array ('일','십', '백', '천' );

	function NumbToKorean(num) {
		num = num;

		delimiter = '';


		bPos = 0;
		sPos = 0;
		digit = 0;

		szDigit = '';
		is_start = false;
		appendFF = false;
		len = num.length;
		szHan = '';

		for (i=len-1;i>=0;i--) {
			szDigit=num.substring(i,i+1);
			digit=parseInt(szDigit);

			if (digit!=0) {
				if (bPos!=0 && sPos==0) {
					if (is_start==true) szHan += delimiter;
					szHan += arrKor2[bPos];
					appendFF=false;
				}
				if (bPos!=0 && appendFF==true) {
					if (is_start==true) szHan += delimiter;
					szHan += arrKor2[bPos];
					appendFF=false;
				}
				if (sPos!=0) szHan += arrKor3[sPos];
				szHan += arrKor1[digit];
				is_start=true;
			}
			else if (sPos==0 && bPos!=0) appendFF=true;
			sPos++;
			if (sPos%4==0) {
				sPos=0;
				bPos++;
				if (bPos>=4) return "(범위초과)";
			}
		}
		if (is_start==false) szHan += "영";

		rslt = '';
		for(i = szHan.length - 1; i >= 0; i--) {
			rslt += szHan.substring(i, i + 1);
		}

		return rslt //+ " 원";
	}
</script>
<div id="serverMsg"></div>
<script language="JavaScript" src="<?=$engine_url?>/_engine/common/calendar.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/calendar.css">
<form name="prdFrm" method="test" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="search_mode" value="Y">
	<div class="box_title first">
		<h2 class="title">종합매출</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">종합매출</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">기간</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				<input type="text" name="start_date" value="<?=$start_date?>" class="input" onfocus="new Calendar(this);" style="cursor:pointer" size="10"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" class="input" onfocus="new Calendar(this);" style="cursor:pointer" size="10">
			</td>
		</tr>
		<tr>
			<th scope="row">옵션</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="dlv" value="1" <?=checked($dlv,true)?>> 배송비 포함</label>
				<label class="p_cursor"><input type="checkbox" name="mile" value="1" <?=checked($mile,true)?>> 적립금결제액 포함</label>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<p>삭제된 주문은 통계에 나타나지 않습니다</p>
	</div>
	<div class="box_bottom">
		<span class="box_btn gray"><input type="button" value="검색" onclick="searchSubmit(this.form,'<?=$_GET[body]?>');"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
	</div>
</form>
<?if($sdate && $fdate) {?>
	<table class="tbl_col">
		<caption class="hidden">종합매출 결과</caption>
		<thead>
			<tr>
				<th scope="col">매출일</th>
				<th scope="col">총주문</th>
				<th scope="col">입금전취소</th>
				<th scope="col">실결제주문</th>
				<th scope="col">배송전취소</th>
				<th scope="col">배송완료</th>
				<th scope="col">취소/반품</th>
				<th scope="col">순매출</th>
			</tr>
		</thead>
		<tbody>
			<?
				$idx=0;
				while($sdate<=$fdate) {
					$idx++;
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					$yoils=getYoil($sdate);
					$sdate_str=date("Y-m-d",$sdate)." (".$yoils.")";
					if($yoils=="토") {
						$sdate_str="<span style=\"color:#45a9cd\">".$sdate_str."</span>";
					}
					elseif($yoils=="일") {
						$sdate_str="<span style=\"color:#cd4b2c\">".$sdate_str."</span>";
					}
			?>
			<tr onMouseOver="this.className='ontr'" onMouseOut="this.className='<?=$rclass?>'" class="<?=$rclass?>">
				<td><?=$sdate_str?></td>
				<td><span id="<?=$sdate?>_1"><img src="<?=$engine_url?>/_manage/image/icon/snake_transparent.gif" alt=""></span> (<span id="<?=$sdate?>_2" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_3">0</span> (<span id="<?=$sdate?>_4" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_5">0</span> (<span id="<?=$sdate?>_6" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_7">0</span> (<span id="<?=$sdate?>_8" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_9">0</span> (<span id="<?=$sdate?>_10" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_11">0</span> (<span id="<?=$sdate?>_12" style="width:27px">0</span>)</td>
				<td><span id="<?=$sdate?>_13">0</span></td>
			</tr>
			<?
				$sdate=strtotime("+1 day",$sdate);
			}
			?>
		</tbody>
		<script type="text/javascript">
			var fdate=<?=$sdate+0?>;
			var f=document.prdFrm;
			<?if($s){?>
			getIncomeStatic('<?=$osdate?>');
			<?}?>
		</script>
		<?
			if($s){
		?>
		<tr style="background:#d8feef">
			<td><span style="color:#ff00ff"><b>합계</b></span></td>
			<td><span id="total_1"><img src="<?=$engine_url?>/_manage/image/icon/snake_transparent.gif" alt=""></span> (<span id="total_2" style="width:27px">0</span>)</td>
			<td><span id="total_3">0</span> (<span id="total_4" style="width:27px">0</span>)</td>
			<td><span id="total_5">0</span> (<span id="total_6" style="width:27px">0</span>)</td>
			<td><span id="total_7">0</span> (<span id="total_8" style="width:27px">0</span>)</td>
			<td><span id="total_9">0</span> (<span id="total_10" style="width:27px">0</span>)</td>
			<td><span id="total_11">0</span> (<span id="total_12" style="width:27px">0</span>)</td>
			<td><span id="total_13">0</span></td>
		</tr>
		<?
			$o="`pay_prc`";
			if($mile) {
				$o.="+`milage_prc`";
			}
			if(!$dlv) {
				$o.="-`dlv_prc`";
			}

			$sdate=$sdate-1;
			$statq=" and `stat` not in (11,31)";

			$term_where=" between '$osdate' and '$sdate'";
			$qry = "
			select
				sum(if(`date1` $term_where, $o, 0)) as r1_sum, sum(if(`date1` $term_where, 1, 0)) as r1_cnt,
				sum(if(`stat` = 13 and `date2` = 0 and `ext_date` $term_where, $o, 0)) as r2_sum, sum(if(`stat`=13 and `date2` = 0 and `date1` $term_where, 1, 0)) as r2_cnt,
				sum(if(`stat` < 10 and `date2` $term_where, $o, 0)) as r3_sum, sum(if(`stat`<10 and `date2` $term_where, 1, 0)) as r3_cnt,
				sum(if(`stat` = 13 and `date2` > 0 and `ext_date` $term_where, $o, 0)) as r4_sum, sum(if(`stat`=13 and `date2` > 0 and `ext_date` $term_where, 1, 0)) as r4_cnt,
				sum(if(`stat` = 5 and `date5` $term_where, $o, 0)) as r5_sum, sum(if(`stat`=5 and `date5` $term_where, 1, 0)) as r5_cnt,
				sum(if(`stat` in (13,15,17) and `date5` > 0 and `ext_date` $term_where, $o, 0)) as r6_sum, sum(if(`stat` in (13,15,17) and `date5` > 0 and `ext_date` $term_where, 1, 0)) as r6_cnt,
				`pay_type`
			from `$tbl[order]`
			where `stat` not in (11,31) and (x_order_id='' or x_order_id='checkout')
			group by `pay_type`
			";
			$res = $pdo->iterator($qry);

            foreach ($res as $data) {
				$idx++;
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
				$inc=$data['r5_sum']-$data['r6_sum'];
		?>
		<tr class="<?=$rclass?>">
			<td align="center"><?=$_pay_type[$data['pay_type']]?></td>
			<td align="right"><?=number_format($data[r1_sum])?>  (<span style="width:27px"><?=number_format($data[r1_cnt])?></span>)</td>
			<td align="right"><?=number_format($data[r2_sum])?>  (<span style="width:27px"><?=number_format($data[r2_cnt])?></span>)</td>
			<td align="right"><?=number_format($data[r3_sum])?>  (<span style="width:27px"><?=number_format($data[r3_cnt])?></span>)</td>
			<td align="right"><?=number_format($data[r4_sum])?>  (<span style="width:27px"><?=number_format($data[r4_cnt])?></span>)</td>
			<td align="right"><?=number_format($data[r5_sum])?>  (<span style="width:27px"><?=number_format($data[r5_cnt])?></span>)</td>
			<td align="right"><?=number_format($data[r6_sum])?>  (<span style="width:27px"><?=number_format($data[r6_cnt])?></span>)</td>
			<td align="right"><?=number_format($inc)?></td>
		</tr>
		<?
			}
			}
		?>
	</table>
	<table class="tbl_col">
		<caption class="hidden">용어 설명</caption>
		<colgroup>
			<col style="width:20%">
			<col>
			<col style="width:20%">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">용어</th>
				<th scope="col">설명</th>
				<th scope="col">기준일</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>총주문</th>
				<td class="left">해당일에 주문된 주문의 합</td>
				<td>주문일</td>
			</tr>
			<tr>
				<th>입금전취소</th>
				<td class="left">무통장 주문이 입금이 되기 전에 해당일에 취소된 주문의 합</td>
				<td>취소일</td>
			</tr>
			<tr>
				<th>실결제주문</th>
				<td class="left">입금확인된 날을 기준으로 결제된 주문의 합</td>
				<td>입금확인일</td>
			</tr>
			<tr>
				<th>배송전취소</th>
				<td class="left">배송완료전 해당일에 취소된 주문</td>
				<td>취소일</td>
			</tr>
			<tr>
				<th>취소/반품</th>
				<td class="left">배송완료후 해당일에 취소/반품/환불된 주문</td>
				<td>취소/반품일</td>
			</tr>
			<tr>
				<th>순매출</th>
				<td class="left">배송완료에서 취소/반품/환불을 차감한 순수 매출합</td>
				<td>배송완료일</td>
			</tr>
		</tbody>
	</table>
<?}?>