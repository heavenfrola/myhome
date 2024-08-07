<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  토스계좌결제 parent iframe 으로 타겟 넘겨서 처리
	' +----------------------------------------------------------------------------------------------+*/
?>
<form name="orderReturn" method="GET" action="/main/exec.php">
	<input type="hidden" name="exec_file" value="card.tosspayment/card_pay.exe.php" />
	<? foreach($_GET as $key=>$val){ if($key != 'exec_file'){ ?>
	<input type="hidden" name="<?=$key?>" value="<?=$val?>">
	<? }}?>
</form>
<script type='text/javascript'>
	try{
		document.orderReturn.target = opener.window.name;
	}catch(e){
		document.orderReturn.target = "_top";
	}
	document.orderReturn.submit();
	if(document.orderReturn.target != "_top"){
		self.close();
	}
</script>