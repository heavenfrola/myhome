<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |
	' +----------------------------------------------------------------------------------------------+*/
	$stype = addslashes($_GET['stype']);
	$_stype=($stype) ? 1 : 0;

	$otitle[0]="브라우저";
	$otitle[1]="OS";

	$tmp = $pdo->assoc("select max(`hit`),sum(`hit`) from `$tbl[log_agent]` where `os`='$_stype'");
	$max=$tmp[0];
	$total=$tmp[1];

	$r = array();
	$sql="select * from `$tbl[log_agent]` where `os`='$_stype' order by `hit` desc";
	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$name=$data[name];
		if($max>0 && $r[$_stype]==$max) $name="<font color=\"#FF3300\">$name</font>";

		if($total>0) $per1=round((($data[hit]/$total)*100),2);
		if($max>0) $per2=round((($data[hit]/$max)*100),2);

		$data['width'] =  (500 / 100) *  @ceil(($data['hit']/ $max) * 100)-1;
		$r[] = $data;
	}

?>
<div class="box_title first">
	<h2 class="title">agent</h2>
</div>
<div class="graphFrm width">
	<table>
		<caption class="hidden">접속통계</caption>
		<tr>
		<?foreach($r as $key => $val) {?>
			<th><?=$val['name']?></th>
			<td>
				<dl class="grp">
					<dt style="width:<?=$val['width']?>px;"><span><?=$val['log']?></span></dt>
					<dd><?=$val['hit']?></dd>
				</dl>
			</td>
		</tr>
		<?}?>
	</table>
</div>
<div class="box_bottom top_line"><?=$pg_res?></div>