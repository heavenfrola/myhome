<?PHP

	if($cfg[new_sort_system]=="Y") {
		msg("이미 업데이트 되었습니다","reload","parent");
	}

	$res = $pdo->iterator("select * from `$tbl[product]` order by `edt_date` asc");
	$total = $res->rowCount();

?>
<script language="JavaScript">
<!--
parent.document.getElementById('update_ord').style.display='block';
if(parent.document.getElementById('p1')) parent.document.getElementById('p1').innerHTML='<?=number_format($total)?>';
//-->
</script>
<?php

	$i=0;
	$old_per=0;
	flush();
	ob_flush();

    foreach ($res as $data) {
		$per=round(($i/$total)*100);
		if($per!=$old_per) {
?>
<script language="JavaScript">
<!--
if(parent.document.getElementById('gageTbl')) parent.document.getElementById('gageTbl').style.width='<?=$per?>%';
if(parent.document.getElementById('per')) parent.document.getElementById('per').innerHTML='<?=$per?>';
if(parent.document.getElementById('p2')) parent.document.getElementById('p2').innerHTML='<?=number_format($i)?>';
//-->
</script>
<?
			$old_per=$per;
		}

		$pdo->query("update `$tbl[product]` set `edt_date`='$i' where `no`='$data[no]'");

		if($i%50==0) {
			flush();
			ob_flush();
			ob_end_flush();
			usleep(3);
		}
		$i++;
	}
?>
<script language="JavaScript">
<!--
if(parent.document.getElementById('gageTbl')) parent.document.getElementById('gageTbl').style.width='100%';
if(parent.document.getElementById('per')) parent.document.getElementById('per').innerHTML='100';
if(parent.document.getElementById('p2')) parent.document.getElementById('p2').innerHTML='<?=number_format($total)?>';
//-->
</script>
<?PHP

	$cfg_msg="상품 업데이트가 완료되었습니다";
	include $engine_dir."/_manage/config/config.exe.php";

?>