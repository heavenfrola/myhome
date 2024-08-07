<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	if($cfg['labelPrinter'] == '') $cfg['labelPrinter'] = 1;
	if($cfg['labelLineHeight'] == '') $cfg['labelLineHeight'] = 80;
	if($cfg['topmargin'] == '') $cfg['topmargin'] = 9;
	if($cfg['leftmargin'] == '') $cfg['leftmargin'] = 25;
	$float = $cfg['labelPrinter'] == 1 ? 'none' : 'left';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=_BASE_CHARSET_?>">
	<style type="text/css">
		body {margin:0; padding:0;}
		.label {
			float: <?=$float?>;
			width: 230px;
			height: 100px;
			overflow: hidden;
			text-align: center;
			margin: 0 47px <?=$cfg['labelLineHeight']?>px 0;
			padding: 0;
		}
		.label h3 {font-size:11px;font-weight:normal; margin: 2px 0; height: 16px; overflow: hidden; }
		.label img.barcode {width:220px; height:48px;}
	</style>
</head>
<body onload="printPage();">

<!-- MeadCo Security Manager -->
<object style="display:none"
  classid="clsid:5445be81-b796-11d2-b931-002018654e2e"
  codebase="<?=$engine_url?>/_manage/erp/barcode/smsx.cab#Version=6,5,439,50">
  <param name="GUID" value="{0ADB2135-6917-470B-B615-330DB4AE3701}">
  <param name="Revision" value="0">
</object>

<!-- MeadCo ScriptX -->
<object id=factory style="display:none" classid="clsid:1663ed61-23eb-11d2-b92f-008048fdd814"></object>
<script type="text/javascript">
function printPage() {
	factory.printing.header = "";
	factory.printing.footer = "";
	factory.printing.portrait = true;
	factory.printing.leftMargin = <?=$cfg[leftmargin]?>;
	factory.printing.topMargin = <?=$cfg[topmargin]?>;
	factory.printing.rightMargin = 0;
	factory.printing.bottomMargin = 0;
	factory.printing.print(false, window);
}
</script>
<?PHP

	$prdno = implode(',', numberOnly($_POST['check_ino']));
	$qty = array();
	foreach($_POST['ino'] as $key => $val) {
		$qty[$val] = numberOnly($_POST['print_qty'][$key]);
	}

	$res = $pdo->iterator("
		select a.name, b.barcode, b.complex_no, b.opts, a.storage_no
			from wm_product a, erp_complex_option b
			where a.stat in (2, 3, 4) and a.no = b.pno and b.del_yn = 'N' and b.complex_no in ($prdno)
	");

	$print_pos %= 30;
	$per = 0;
	$prd_cnt = 0;
	$total = 0;
    foreach ($res as $data) {
		$cnt = $qty[$data['complex_no']];
		$prd_cnt++;

        if($data['storage_no'] > 0) {
            $data['storage'] = stripslashes($pdo->row("select name from erp_storage where no='$data[storage_no]'"));
        }

		if($prd_cnt == 1) $cnt += $print_pos;
		for($i = 0; $i < $cnt; $i++) {
			$bname = cutstr(stripslashes($data['name']).'('.getComplexOptionName($data['opts']).')', 45);
			if(
				($cfg['labelPrinter'] == 3 && $per == 30) ||
				($cfg['labelPrinter'] == 1 && $total > 0 && $total%2 == 0 && $cfg['labelLineHeight'] > 0)  ||
				($cfg['labelPrinter'] == 2 && $total > 0)
			) {
				echo "
				<div style=\"page-break-before:always;clear:both;\">
				   <!--[if IE 7]><br style=\"height:0; line-height:0\"><![endif]-->
				</div>
				";
				$per = 0;
			}

			if($prd_cnt == 1 && $per < $print_pos){
				$per++;
				continue;
			}

			$nl = ($per % $cfg['labelPrinter'] == $cfg['labelPrinter']-1) ? "style='margin-right: 0;'" : '';
			?>
			<div class="label" <?=$nl?>>
				<h3><?=$bname?></h3>
                <h3><?=$data['storage']?></h3>
				<div><img class="barcode" src="?body=erp@barcode.exe&text=<?=$data['barcode']?>" /></div>
			</div>
			<?
			$per++;
			$total++;
		}
	}
?>
</body>
</html>