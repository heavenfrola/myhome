<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 상품 수량 및 옵션 변경 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($dlv_part) {
		$pdo->query("ALTER TABLE `$tbl[order_product]` CHANGE `stat` `stat` VARCHAR( 2 ) CHARACTER SET euckr COLLATE euckr_korean_ci NOT NULL DEFAULT '1'");

		$res = $pdo->iterator("select * from `$tbl[order]`");
		$total = $res->rowCount();

?>
<script language="JavaScript">
<!--
parent.document.getElementById('update_ord').style.display='block';
if(parent.document.getElementById('p1')) parent.document.getElementById('p1').innerHTML='<?=number_format($total)?>';
//-->
</script>
<?


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

			$prd_stats="";
			$sql="update `$tbl[order_product]` set `stat`='$data[stat]', `dlv_no`='$data[dlv_no]', `dlv_code`='$data[dlv_code]' where `ono`='$data[ono]'";
			$pdo->query($sql);
			$sql="update `$tbl[order]` set `stat2`='@$data[stat]@' where `no`='$data[no]'";
			$pdo->query($sql);

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
<?
		$cfg_msg="부분 배송 설정이 완료되었습니다";
		include $engine_dir."/_manage/config/config.exe.php";
	}elseif($repay_part){
		if($cfg[repay_part]=="Y") {
			msg("이미 부분 취소/환불 기능을 사용중입니다");
		}

		$pdo->query("alter table `$tbl[order_product]` change `stat` `stat` varchar(2) not null default '1'");

		flush();
		ob_flush();
		$cfg_msg="부분 취소/환불 기능이 설정되었습니다";
		include $engine_dir."/_manage/config/config.exe.php";

	}elseif($order_prd_change){
		if($cfg[product_annex_use] == "Y") msg("부속상품기능이 사용중이기 때문에 설정이 불가능합니다");
		if($cfg[order_prd_change]=="Y") {
			msg("이미 수량 및 옵션 변경기능을 사용중입니다");
		}

		$cfg_msg="수량 및 옵션 변경기능이 설정되었습니다";
		include $engine_dir."/_manage/config/config.exe.php";
	}
	else {
		msg("설정에 변화가 없습니다","reload","parent");
	}

?>