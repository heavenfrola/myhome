<?PHP

	$type = $_GET['type'];
	if(empty($type) == true) $type = 'qna';
	$active[$type] = 'active';

?>
<div id="sns_login">
	<div class="box_title first">
		<h2 class="title">상품문의 설정</h2>
	</div>
	<div class="box_tab first tablist">
		<ul>
			<li><a href="?body=member@product_inquery_config&type=qna" class="<?=$active['qna']?>">상품Q&A 설정</a></li>
			<li><a href="?body=member@product_inquery_config&type=counsel" class="<?=$active['counsel']?>">1:1상담 설정</a></li>
		</ul>
	</div>
	<div class="box_sort"></div>
</div>

<?include 'product_'.$type.'_config.php'?>