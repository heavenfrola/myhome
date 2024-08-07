<?php

	$config_code = ($partner_order) ? "partner_order" : "order"; 

?>
<div id="popupContent" class="popupContent layerPop" style="width:720px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">주문 관리 설정</div>
	</div>
	<div id="popupContentArea">
		<p class="msg">주문 관리 설정을 통해 주문상태 색상설정 및 주문조회 항목설정 등을 편리하게 설정할 수 있습니다.</p>
		<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="this.target=hid_frame">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="<?=$config_code?>">
			<table class="tbl_row">
				<caption class="hidden">주문 관리 설정</caption>
				<colgroup>
					<col style="width:20%">
					<col>
				</colgroup>
				<?PHP
					include $engine_dir.'/_manage/config/order_config_order.inc.php';
				?>
			</table>
			<div class="box_bottom noline">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
				<span class="box_btn "><input type="button" value="닫기" onclick="oconfig.close()"></span>
			</div>
		</form>
	</div>
</div>