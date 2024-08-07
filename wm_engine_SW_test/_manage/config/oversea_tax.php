<?
	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	asort($_nations);

	$delivery_com_array = getOverseaDeliveryComList();
	$nations_array = getDeliveryPossibleCountry(true);
?>
<style>
	.Lfloat{float:left;}
	.Rfloat{float:right;}
	.clear{clear:both;}
	.left{text-align:left;}
	.file_input_hidden {position:absolute; left:15px; top:5px; z-index:5; height:40px; opacity:0; filter: alpha(opacity=0); -ms-filter: "alpha(opacity=0)"; -khtml-opacity:0; -moz-opacity:0; cursor:pointer;border:1px solid red;width:120px;}
	.tbl_row th{text-align:left;}
</style>
<form name="deliveryFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@oversea_tax.exe">
	<input type="hidden" name="exec" value="delivery_com_tax">
	<div class="box_title first">
		<h2 class="title">배송사별 관세 사용</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배송사별 관세 사용</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<td scope="row">
			<?
				if(is_array($delivery_com_array['list'])) {
					foreach($delivery_com_array['list'] as $k=>$v) {
			?>
				<label><input type="checkbox" name="delivery_com[]" value="<?=$v['no']?>" <?=$v['tax_use']=='Y'?'checked':''?> /> <?=$v['name']?></label>&nbsp;&nbsp;&nbsp;
			<?
					}
				} else {
			?>
			등록된 해외 배송사가 없습니다. <span class="box_btn_s gray"><a href="/_manage/?body=config@delivery_prv">배송업체 추가하기</a></span>
			<?}?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br/>
<form name="deliveryFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="tax">
	<div class="box_title first">
		<h2 class="title">국가별 세금 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">국가별 세금 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<? if(count($nations_array) > 0) { ?>
			<? foreach($nations_array as $k=>$v) { ?>
			<tr>
				<th scope="row"><?=$v['name']?></th>
				<td>총 구매금액(배송비 포함) <input type="text" name="tax_add_limit[<?=$v['code']?>]" value="<?=$cfg["tax_add_limit".$v['code']]?>" class="input" size="5"/><?=$cfg['currency_type']?> 이상 <input type="text" name="tax_add_per[<?=$v['code']?>]" value="<?=$cfg["tax_add_per".$v['code']]?>" class="input" size="5"/>% 부가</td>
			</tr>
			<? } ?>
		<? }else{ ?>
			<tr>
				<td colspan="2" class="left">관세를 사용할 배송사를 먼저 선택하세요.</td>
			</tr>
		<? } ?>
		<tr>
			<td colspan="2" style="line-height:160%;">
				<span>
					ex) 태국 의류판매 : 세금 부가 기준 1500바트 이상, 관세 30%(CIF), 부가세 7%(CIF+관세)
					<br/>
					관&nbsp;&nbsp;세 : 1500x0.3 = <b>450바트</b><br/>
					부가세 : (1500+(1500x0.3))x0.07 = <b>136.5바트</b><br/>
					총금액 : <b>2086.5바트</b>
				</span>
				<br/><br/>
				<span>
					ex) 중국 화장품 판매 : 관세 32.9%(CIF), 소비세 : 30%(CIF+관세), 증치세 17%(CIF+관세+소비세)
					<br/>
					관&nbsp;&nbsp;세 : 100x0.329 = <b>32.9위안</b><br/>
					소비세 : ((100+32.9) / (1-0.3))*0.3 = <b>56.96위안</b><br/>
					증치세 : (100+32.9+56.96)x0.17 = <b>32.28위안</b><br/>
					총금액 : <b>222.14위안</b><br/><br/>

					ex) 중국 의류 판매 : 관세 32.9%(CIF), 증치세 17%(CIF+관세)
					<br/>
					관&nbsp;&nbsp;세 : 100x0.329 = <b>32.9위안</b><br/>
					증치세 : (100+32.9)x0.17 = <b>22.59위안</b><br/>
					총금액 : <b>155.49위안</b>
				</span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>