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
<?
	$res = array();

	$exec = $_POST['exec'];

	if($exec == 'in_barcode1' || $exec == 'in_barcode2') { // 재고조정 내역 조회
		foreach($_POST['no'] as $key => $val) {
			list($qty1, $qty2, $barcode) = explode('@', $val);
			$qty = $exec == 'in_barcode1' ? $qty1 : $qty2;
			if($qty == 0) continue;

			$data = $pdo->assoc("select name, opts from $tbl[product] a inner join erp_complex_option b on b.pno=a.no where b.barcode='$barcode'");
			$res[$key] = array(
							'qty' => $qty,
							'barcode' => $barcode,
							'name' => stripslashes($data['name']),
							'opts' => $data['opts']
						);
		}
	} else { // 입고내역 조회
		$sql = "select a.name, b.barcode, b.opts, c.qty, a.storage_no" .
			  "  from wm_product a, erp_complex_option b, erp_inout c" .
			  " where a.stat in (2, 3, 4) and a.no = b.pno and c.inout_kind = 'I'" .
			  "   and b.complex_no = c.complex_no" .
			  "   and b.del_yn = 'N'" .
			  "   and c.inout_no in ({$_GET['no']})";
		$sql = $pdo->iterator($sql);
        foreach ($sql as $data) {
            if($data['storage_no'] > 0) {
                $data['storage'] = stripslashes($pdo->row("select name from erp_storage where no='$data[storage_no]'"));
            }
			$res[] = $data;
		}
	}

	$per = $total = 0;
	foreach($res as $data) {
		for($i=0; $i < $data['qty']; $i++) {
			$bname = cutstr(stripslashes($data['name']).'('.getComplexOptionName($data['opts']).')', 50);
			if(
				($cfg['labelPrinter'] == 3 && $per == 30) ||
				($cfg['labelPrinter'] == 1 && $total > 0 && $total%2 == 0 && $cfg['labelLineHeight'] > 0)  ||
				($cfg['labelPrinter'] == 2 && $total > 0)
			) {
			?>
			<div style="page-break-before:always;clear:both;">
			   <!--[if IE 7]><br style="height:0; line-height:0"><![endif]-->
			</div>
			<?
			$per = 0;
			}
			$nl = ($per % $cfg['labelPrinter'] == $cfg['labelPrinter']-1) ? "style='margin-right: 0;'" : '';
			?>
			<div class="label" <?=$nl?>>
				<h3><?=$bname?></h3>
                <h3><?=$data['storage']?></h3>
				<div><img class="barcode" src="?body=erp@barcode.exe&text=<?=$data['barcode']?>" onerror="this.src='?body=erp@bartest.exe&barcode=<?=$data['barcode']?>'"/></div>
			</div>
			<?
			$per++;
			$total++;
		}
	}
?>
</body></html>