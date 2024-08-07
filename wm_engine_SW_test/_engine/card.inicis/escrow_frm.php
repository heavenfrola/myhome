<form name=ini<?=$ord[no]?> method=post action="<?=$root_url?>/main/exec.php">
	<input type=hidden name=exec_file value="card.inicis/escrow.exe.php">
	<input type=hidden name=hanatid value="<?=$tno?>">
	<input type=hidden name=mid value="<?=$cfg[card_mall_id]?>">
	<input type=hidden name=EscrowType value="dr">
	<input type=hidden name=invno value="<?=$dlv_code?>">
	<input type=hidden name=adminID value="<?=$admin[admin_id]?>">
	<input type=hidden name=adminName value="<?=$admin[admin_id]?>">
	<input type=hidden name=compName value="<?=$dlv_name?>">
	<input type=hidden name=compID value="<?=$_dlvnum?>">
	<input type=hidden name=transtype value="S0">
	<input type=hidden name=transport value="T0">
	<input type=hidden name=transfee value="<?=$cfg[delivery_fee]?>">
	<input type=hidden name=paymeth value="F0">
	<input type=hidden name=notice value="C4">
	<input type=hidden name=transdate1 value="<?=date("Ymd");?>"></td>
	<input type=hidden name=transdate2 value=""></td>
</form>

<script type="text/javascript">
document.ini<?=$ord[no]?>.submit();
</script>