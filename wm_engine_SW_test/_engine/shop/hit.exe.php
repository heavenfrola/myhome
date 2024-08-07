<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	if(!$no || !$type) exit();

	if($cfg["product_".$type."_hitnum"] == "Y"){ // 2007-12-04 : Á¶È¸¼ö - Han
		if(!@strchr($_SESSION[$type."_hitted"],"_".$no."_")){
			$_SESSION[$type."_hitted"] .= "_".$no."_";
			$pdo->query("update `".$tbl[$type]."` set `hit`=`hit`+1 where `no`='$no' limit 1");
?>
<script language="JavaScript">
<!--
obj=parent.document.getElementById('<?=$type?>Hit_<?=$no?>');
if(obj){
	hit=eval(obj.innerText);
	obj.innerText=hit+1;
}
//-->
</script>
<?
		}
	}


 exit();

?>