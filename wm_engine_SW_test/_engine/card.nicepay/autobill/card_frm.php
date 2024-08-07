<form name="nicepay_autobill" method="post" action="billkeyResult_utf.php" accept-charset="euc-kr">
	<input type="hidden" name="GoodsName" value="<?=$goodsName?>">
	<input type="hidden" name="BuyerName" value="<?=$buyerName?>">
	<input type="hidden" name="BuyerTel" value="<?=$buyerTel?>">
	<input type="hidden" name="BuyerEmail" value="<?=$buyerEmail?>">
	<input type="hidden" name="MID" value="<?=$mid?>"></td>
	<input type="hidden" name="Moid" value="<?=$moid?>">
	<input type="hidden" name="Amt" value="<?=$price?>">
	<input type="hidden" name="GoodsCl" value="<?=$goodsCl?>">
    <input type="hidden" name="ReturnUrl" value="<?=$returnUrl?>"/>
</form>