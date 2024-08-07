<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	function naverFileInfo($fn, $feedtype = null) {
		global $root_dir,$root_url,$dir, $cfg;

		$fn = str_replace('.txt', '.php', $fn);

		$path = $root_dir."/".$dir['upload']."/".$dir['compare']."/naver/".$fn;
		$url=$root_url."/".$dir['upload']."/".$dir['compare']."/naver/".$fn;
		$url = str_replace('https://', 'http://', $url);

        if(!file_exists($file)) {
            makeFullDir('_data/compare/naver');
            $feed  = "<?PHP\n";
            $feed .= "	include '../../../_config/set.php';\n";
            $feed .= "	\$feedtype = $feedtype;\n";
            $feed .= "	include \$engine_dir.'/_engine/promotion/navershop_feed.tsv.php';\n";
            $feed .= "?>";
            $fp = @fopen($path, 'w');
            if($fp) {
                fwrite($fp, $feed);
                fclose($fp);
            }
        }
        $r = "<a href=\"{$url}\" target=\"_blank\" class=\"p_color\">{$url}</a>";

		return array($url, $r);
	}

	$status = 1;

	$site_key = md5($wec->config['wm_key_code']);
	$tracking_url = $root_url.'/main/exec.php?exec_file=compare/naver_tracking.php&site_key='.$site_key;
	$sell_url = $root_url.'/main/exec.php?exec_file=compare/naver_sell.php&site_key='.$site_key;

	$all_url = naverFileInfo("all_prd.txt", 1);
	$summ_url = naverFileInfo("summary_prd.txt", 2);
    $book_url = naverFileInfo('book_prd.txt', 3);
    $book_summ_url = naverFileInfo('book_summary_prd.txt', 4);

?>
	<div class="box_title">
		<h2 class="title">네이버쇼핑 엔진파일 생성</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버쇼핑 엔진파일 생성</caption>
		<colgroup>
			<col style="width:15%">
			<col>
			<col style="width:15%">
		</colgroup>
		<?if($status) {?>
		<tr>
			<th>전체 EP(DB) URL</th>
			<td><?=$all_url[1]?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$all_url[0]?>"></span>
			</td>
		</tr>
		<tr>
			<th>요약 EP(DB) URL</th>
			<td><?=$summ_url[1]?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$summ_url[0]?>"></span>
			</td>
		</tr>
        <?php if ($scfg->comp('use_navershopping_book', 'Y') == true) { ?>
		<tr>
			<th>도서 EP</th>
			<td><?=$book_url[1]?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$book_url[0]?>"></span>
			</td>
		</tr>
		<tr>
			<th>도서 요약 EP</th>
			<td><?=$book_summ_url[1]?></td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=$book_summ_url[0]?>"></span>
			</td>
		</tr>
        <?php } ?>
		<tr>
			<th>판매지수 EP(DB) URL</th>
			<td>
				<a href="<?=$sell_url?>" target="_blank" class="p_color"><?=$sell_url?></a>
			</td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" onclick="tagCopy('<?=$sell_url?>');"></span>
			</td>
		</tr>
		<?} else {?>
		<tr>
			<td colspan="3">
				서비스가 시작되지 않았습니다<br>1:1 게시판을 통해 eAD 사업부로 별도 문의 해 주시기 바랍니다.
			</td>
		</tr>
		<?}?>
	</table>

<script type="text/javascript">
new Clipboard('.clipboard');
</script>