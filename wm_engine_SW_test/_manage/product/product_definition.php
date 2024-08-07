<?PHP

	$type = $_GET['type'];
	if(empty($type) == true) $type = 'self';
	${'active_'.$type} = 'class="active"';

	setListURL('product_definition');

?>
<div class="box_title first">
	<h2 class="title">상품정보제공고시 관리</h2>
</div>

<div class="box_tab first">
	<ul>
		<li><a href="?body=<?=$_GET['body']?>" <?=$active_self?>><?=$cfg['company_name']?></a></li>
		<?if($cfg['use_kakaoTalkStore'] == 'Y' || $cfg['use_talkpay'] == 'Y') {?>
		<li><a href="?body=<?=$_GET['body']?>&type=talkstore" <?=$active_talkstore?>>카카오</a></li>
		<?}?>
		<?if($cfg['n_smart_store'] =='Y') {?>
		<li><a href="?body=<?=$_GET['body']?>&type=smartstore" <?=$active_smartstore?>>스마트스토어</a></li>
		<?}?>
	</ul>
</div>

<?require 'product_definition_'.$type.'.inc.php'?>