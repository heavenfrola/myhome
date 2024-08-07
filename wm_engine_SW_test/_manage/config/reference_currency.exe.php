<?PHP
	include_once $engine_dir."/_config/set.country.php";

	printAjaxHeader();

	asort($_nations_currency_code);
?>
<style>
.currency_table{width:100%;}
.currency_table th{border:1px solid #cccccc;border-right:3px double #000000;}
.currency_table th:last-child{border:1px solid #cccccc;}
.currency_table td{padding:3px 5px !important;border:1px solid #cccccc;}

</style>
<div id="popupContent" class="layerPop pop_width">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">국가별 화폐코드</div>
	</div>
	<div id="popupContentArea">
		<p class="explain">* 아래 국가 화폐코드 이외를 사용하면 참조화폐 금액 노출이 정상적이지 않을 수 있습니다.</p>
		<table class="currency_table">
			<col width="28%"></col>
			<col width="5%"></col>
			<col width="29%"></col>
			<col width="5%"></col>
			<col width="28%"></col>
			<col width="5%"></col>
			<tr>
				<? $i=1;foreach($_nations_currency_code as $k=>$v) { ?>
					<td class="p_cursor" onclick="select_r_currency('<?=$k?>')"><?=$v?></td><th class="p_cursor" onclick="select_r_currency('<?=$k?>')"><b><?=$k?></b></th>
					<? if($i % 3 == 0) echo "</tr><tr>";?>	
				<? $i++;} ?>
			</tr>
		</table>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick='r_currency_list.close()'></span>
	</div>
</div>
<script>
	function select_r_currency(currency){
		$('input[name="r_currency_type_custom"]').val(currency);
		$('input[name="r_currency_decimal"]').val('2');
		$('input[name="r_currency"]').val(currency);
		
		r_currency_list.close();
	}

	$('.currency_table td, .currency_table th').mouseover(function(){
		$(this).attr('style','background:#f1f1f1');
	}).mouseout(function(){
		$(this).attr('style','background:#ffffff');	
	});
</script>