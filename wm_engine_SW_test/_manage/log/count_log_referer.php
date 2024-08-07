<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |
	' +----------------------------------------------------------------------------------------------+*/

	$_engine_arr=array(
	"http://search.naver.com"=>"네이버",
	"http://search.daum.net"=>"다음",
	"http://kr.search.yahoo.com"=>"야후 코리아",
	"http://www.google.co.kr"=>"구글",
	"http://search.empas.com"=>"엠파스",
	"http://search.paran.com"=>"파란",
	"http://search.d.paran.com"=>"드림위즈",
	"http://nate.search.empas.com"=>"네이트",
	"http://www.altavista.com"=>"알타비스타",
	"http://www.simmani.com"=>"심마니",
	"http://search.lycos.com"=>"라이코스 코리아",
	"http://search.korea.com"=>"코리아닷컴"
	);

	$total_referer=0;

	$_list=array();
	foreach($_engine_arr as $key=>$val){
		$total=$pdo->row("select sum(`hit`) from `$tbl[log_referer]` where `log` like '".$key."%'");
		if($total > 0){
			$re=1;
			$total_referer += $total;
		}
		$_list[$val]['total'] = $total;
		$_list[$val]['width'] = (500 / 100) * @ceil(($data['hit']/ $max) * 100);
	}

	if($re) arsort($_list);

?>
<div class="box_title first">
	<h2 class="title">검색엔진별 접속통계</h2>
</div>
<div class="graphFrm width">
	<table>
		<caption class="hidden">검색엔진별 접속통계</caption>
		<tr>
		<?foreach($_list as $key => $val) {?>
			<th><?=$key?></th>
			<td>
				<dl class="grp">
					<dt><span><?=$key?></span></dt>
					<dd style="width:<?=$val['width']?>px;"><span><?=$val['total']?></span></dd>
				</dl>
			</td>
		</tr>
		<?}?>
	</table>
</div>
<div class="box_bottom top_line"><?=$pg_res?></div>