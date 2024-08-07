<?PHP

	define('_wisa_manage_edit_', true);

	include_once $engine_dir."/_engine/include/common.lib.php";

	$contentId = preg_replace('/[^a-z0-9_-]/i', '', $_GET['contentId']);
	$editor_code =  preg_replace('/[^a-z0-9_-]/i', '', $_GET['editor_code']);
	$neko_gr = preg_replace('/[^a-z0-9_-]/i', '', $_GET['neko_gr']);

?>
<!DOCTYPE HTML PUBLIC "-W3CDTD HTML 4.01 TransitionalEN" "http:www.w3.org/TR/html4/loose.dtd">
<html lang="ko">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>네이버 :: Smart Editor 2 &#8482;</title>
<link href="<?=$engine_url?>/_engine/smartEditor/css/smart_editor2.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=$root_url?>/_data/swfupload/style.css?ver=<?=$ver?>">
<style type="text/css">
	body { margin: 10px; }
</style>
<script type="text/javascript">
var engine_url="<?=$engine_url?>";
var root_url="//<?=$_SERVER['HTTP_HOST']?>";
var contentId = '<?=$contentId?>';
var image_group = '<?=$neko_gr?>';
var editor_code = '<?=$editor_code?>';
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery/jquery-1.4.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/jindo.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/jindo_component.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/SE2B_Configuration_Service.js" charset="utf-8"></script>	<!-- 설정 파일 -->
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/SE2B_Configuration_General.js" charset="utf-8"></script>	<!-- 설정 파일 -->
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/SE2BasicCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/smarteditor2.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/upload/uploader.js"></script>
<script>
<?php if($editor_code) { ?>
jQuery(function() {
    HTML5Uploader();
});
<?php } ?>
</script>
</head>
<body>

<form name="tmpFrm">
<input type="hidden" name="contentId" value="<?=$contentId?>" />
<input type="hidden" name="editor_code" value="<?=$editor_code?>" />
<input type="hidden" name="tmp_code" value="" />
</form>

<span id="rev"></span>

<!-- SE2 Markup Start -->
<div id="smart_editor2">
	<div id="smart_editor2_content"><a href="#se2_iframe" class="blind">글쓰기영역으로 바로가기</a>
		<div class="se2_tool" id="se2_tool">

			<div class="se2_text_tool husky_seditor_text_tool">
			<ul class="se2_font_type">
				<li class="husky_seditor_ui_fontName"><button type="button" class="se2_font_family" title="글꼴"><span class="husky_se2m_current_fontName">글꼴</span></button>
					<!-- 글꼴 레이어 -->
					<div class="se2_layer husky_se_fontName_layer">
						<div class="se2_in_layer">
							<ul class="se2_l_font_fam" style="display:">
							<li style="display:none"><button type="button"><span>@DisplayName@<span>(</span><em style="font-family:FontFamily;">@SampleText@</em><span>)</span></span></button></li>
							<!--
							<li><button type="button"><span>돋움<span>(</span><em style="font-family:'돋움',Dotum,Sans-serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>돋움체<span>(</span><em style="font-family:'돋움체',DotumChe,Sans-serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>굴림<span>(</span><em style="font-family:'굴림',Gulim,Sans-serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>굴림체<span>(</span><em style="font-family:'굴림체',GulimChe,Sans-serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>바탕<span>(</span><em style="font-family:'바탕',Batang,serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>바탕체<span>(</span><em style="font-family:'바탕체',BatangChe,serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>궁서<span>(</span><em style="font-family:'궁서',Gungsuh,serif;">가나다라</em><span>)</span></span></button></li>
							<li><button type="button"><span>Arial<span>(</span><em style="font-family:arial,Sans-serif;">abcd</em><span>)</span></span></button></li>
							<li><button type="button"><span>Tahoma<span>(</span><em style="font-family:tahoma,Sans-serif;">abcd</em><span>)</span></span></button></li>
							<li><button type="button"><span>Times New Roman<span>(</span><em style="font-family:'Times New Roman',Times,serif;">abcd</em><span>)</span></span></button></li>
							<li><button type="button"><span>Verdana<span>(</span><em style="font-family:verdana,Sans-serif;">abcd</em><span>)</span></span></button></li>
							-->
							<li><button type="button"><span>Georgia<span>(</span><em style="font-family:Georgia;">abcd</em><span>)</span></span></button></li>
							<li class="se2_division husky_seditor_font_separator"></li>
							<li class="husky_seditor_font_nanumgothic"><button type="button"><span>나눔고딕<span>(</span><em style="font-family:'나눔고딕',NanumGothic,Sans-serif;">가나다라</em><span>)</span></span></button></li>
							<li class="husky_seditor_font_nanummyeongjo"><button type="button"><span>나눔명조<span>(</span><em style="font-family:'나눔명조',NanumMyeongjo,serif;">가나다라</em><span>)</span></span></button></li>
							</ul>
						</div>
					</div>
					<!--글꼴 레이어 -->
				</li>

				<li class="husky_seditor_ui_fontSize"><button type="button" class="se2_font_size" title="글자크기"><span class="husky_se2m_current_fontSize">크기</span></button>
					<!-- 폰트 사이즈 레이어 -->
					<div class="se2_layer husky_se_fontSize_layer">
						<div class="se2_in_layer">
							<ul class="se2_l_font_size">
							<li><button type="button" style="height:19px;"><span style="margin-top:4px; margin-bottom:3px; margin-left:5px; font-size:7pt;">가나다라마바사<span style=" font-size:7pt;">(7pt)</span></span></button></li>
							<li><button type="button" style="height:20px;"><span style="margin-bottom:2px; font-size:8pt;">가나다라마바사<span style="font-size:8pt;">(8pt)</span></span></button></li>
							<li><button type="button" style="height:20px;"><span style="margin-bottom:1px; font-size:9pt;">가나다라마바사<span style="font-size:9pt;">(9pt)</span></span></button></li>
							<li><button type="button" style="height:21px;"><span style="margin-bottom:1px; font-size:10pt;">가나다라마바사<span style="font-size:10pt;">(10pt)</span></span></button></li>
							<li><button type="button" style="height:23px;"><span style="margin-bottom:2px; font-size:11pt;">가나다라마바사<span style="font-size:11pt;">(11pt)</span></span></button></li>
							<li><button type="button" style="height:25px;"><span style="margin-bottom:1px; font-size:12pt;">가나다라마바사<span style="font-size:12pt;">(12pt)</span></span></button></li>
							<li><button type="button" style="height:27px;"><span style="margin-bottom:2px; font-size:14pt;">가나다라마바사<span style="margin-left:6px;font-size:14pt;">(14pt)</span></span></button></li>
							<li><button type="button" style="height:33px;"><span style="margin-bottom:1px; font-size:18pt;">가나다라마바사<span style="margin-left:8px;font-size:18pt;">(18pt)</span></span></button></li>
							<li><button type="button" style="height:39px;"><span style="margin-left:3px; font-size:24pt;">가나다라마<span style="margin-left:11px;font-size:24pt;">(24pt)</span></span></button></li>
							<li><button type="button" style="height:53px;"><span style="margin-top:-1px; margin-left:3px; font-size:36pt;">가나다<span style="font-size:36pt;">(36pt)</span></span></button></li>
							</ul>
						</div>
					</div>
					<!--폰트 사이즈 레이어 -->
				</li>
</ul><ul>
				<li class="husky_seditor_ui_bold"><button type="button" title="굵게[Ctrl+B]" class="se2_bold"><span class="_buttonRound">굵게[Ctrl+B]</span></button></li>

				<li class="husky_seditor_ui_underline"><button type="button" title="밑줄[Ctrl+U]" class="se2_underline"><span class="_buttonRound">밑줄[Ctrl+U]</span></button></li>

				<li class="husky_seditor_ui_italic"><button type="button" title="기울임꼴[Ctrl+I]" class="se2_italic"><span class="_buttonRound">기울임꼴[Ctrl+I]</span></button></li>

				<li class="husky_seditor_ui_lineThrough"><button type="button" title="취소선[Ctrl+D]" class="se2_tdel"><span class="_buttonRound">취소선[Ctrl+D]</span></button></li>

				<li class="se2_pair husky_seditor_ui_fontColor"><span class="selected_color husky_se2m_fontColor_lastUsed" style="background-color:#4477f9"></span><span class="husky_seditor_ui_fontColorA"><button type="button" title="글자색" class="se2_fcolor"><span>글자색</span></button></span><span class="husky_seditor_ui_fontColorB"><button type="button" title="더보기" class="se2_fcolor_more"><span class="_buttonRound">더보기</span></button></span>
					<!-- 글자색 -->
					<div class="se2_layer husky_se2m_fontcolor_layer" style="display:none">
						<div class="se2_in_layer husky_se2m_fontcolor_paletteHolder">
							<div class="se2_palette husky_se2m_color_palette">
								<ul class="se2_pick_color">
								<li><button type="button" title="#ff0000" style="background:#ff0000"><span><span>#ff0000</span></span></button></li>
								<li><button type="button" title="#ff6c00" style="background:#ff6c00"><span><span>#ff6c00</span></span></button></li>
								<li><button type="button" title="#ffaa00" style="background:#ffaa00"><span><span>#ffaa00</span></span></button></li>
								<li><button type="button" title="#ffef00" style="background:#ffef00"><span><span>#ffef00</span></span></button></li>
								<li><button type="button" title="#a6cf00" style="background:#a6cf00"><span><span>#a6cf00</span></span></button></li>
								<li><button type="button" title="#009e25" style="background:#009e25"><span><span>#009e25</span></span></button></li>
								<li><button type="button" title="#00b0a2" style="background:#00b0a2"><span><span>#00b0a2</span></span></button></li>
								<li><button type="button" title="#0075c8" style="background:#0075c8"><span><span>#0075c8</span></span></button></li>
								<li><button type="button" title="#3a32c3" style="background:#3a32c3"><span><span>#3a32c3</span></span></button></li>
								<li><button type="button" title="#7820b9" style="background:#7820b9"><span><span>#7820b9</span></span></button></li>
								<li><button type="button" title="#ef007c" style="background:#ef007c"><span><span>#ef007c</span></span></button></li>
								<li><button type="button" title="#000000" style="background:#000000"><span><span>#000000</span></span></button></li>
								<li><button type="button" title="#252525" style="background:#252525"><span><span>#252525</span></span></button></li>
								<li><button type="button" title="#464646" style="background:#464646"><span><span>#464646</span></span></button></li>
								<li><button type="button" title="#636363" style="background:#636363"><span><span>#636363</span></span></button></li>
								<li><button type="button" title="#7d7d7d" style="background:#7d7d7d"><span><span>#7d7d7d</span></span></button></li>
								<li><button type="button" title="#9a9a9a" style="background:#9a9a9a"><span><span>#9a9a9a</span></span></button></li>
								<li><button type="button" title="#ffe8e8" style="background:#ffe8e8"><span><span>#9a9a9a</span></span></button></li>
								<li><button type="button" title="#f7e2d2" style="background:#f7e2d2"><span><span>#f7e2d2</span></span></button></li>
								<li><button type="button" title="#f5eddc" style="background:#f5eddc"><span><span>#f5eddc</span></span></button></li>
								<li><button type="button" title="#f5f4e0" style="background:#f5f4e0"><span><span>#f5f4e0</span></span></button></li>
								<li><button type="button" title="#edf2c2" style="background:#edf2c2"><span><span>#edf2c2</span></span></button></li>
								<li><button type="button" title="#def7e5" style="background:#def7e5"><span><span>#def7e5</span></span></button></li>
								<li><button type="button" title="#d9eeec" style="background:#d9eeec"><span><span>#d9eeec</span></span></button></li>
								<li><button type="button" title="#c9e0f0" style="background:#c9e0f0"><span><span>#c9e0f0</span></span></button></li>
								<li><button type="button" title="#d6d4eb" style="background:#d6d4eb"><span><span>#d6d4eb</span></span></button></li>
								<li><button type="button" title="#e7dbed" style="background:#e7dbed"><span><span>#e7dbed</span></span></button></li>
								<li><button type="button" title="#f1e2ea" style="background:#f1e2ea"><span><span>#f1e2ea</span></span></button></li>
								<li><button type="button" title="#acacac" style="background:#acacac"><span><span>#acacac</span></span></button></li>
								<li><button type="button" title="#c2c2c2" style="background:#c2c2c2"><span><span>#c2c2c2</span></span></button></li>
								<li><button type="button" title="#cccccc" style="background:#cccccc"><span><span>#cccccc</span></span></button></li>
								<li><button type="button" title="#e1e1e1" style="background:#e1e1e1"><span><span>#e1e1e1</span></span></button></li>
								<li><button type="button" title="#ebebeb" style="background:#ebebeb"><span><span>#ebebeb</span></span></button></li>
								<li><button type="button" title="#ffffff" style="background:#ffffff"><span><span>#ffffff</span></span></button></li>
								</ul>
								<ul class="se2_pick_color" style="width:156px;">
								<li><button type="button" title="#e97d81" style="background:#e97d81"><span><span>#e97d81</span></span></button></li>
								<li><button type="button" title="#e19b73" style="background:#e19b73"><span><span>#e19b73</span></span></button></li>
								<li><button type="button" title="#d1b274" style="background:#d1b274"><span><span>#d1b274</span></span></button></li>
								<li><button type="button" title="#cfcca2" style="background:#cfcca2"><span><span>#cfcca2</span></span></button></li>
								<li><button type="button" title="#cfcca2" style="background:#cfcca2"><span><span>#cfcca2</span></span></button></li>
								<li><button type="button" title="#61b977" style="background:#61b977"><span><span>#61b977</span></span></button></li>
								<li><button type="button" title="#53aea8" style="background:#53aea8"><span><span>#53aea8</span></span></button></li>
								<li><button type="button" title="#518fbb" style="background:#518fbb"><span><span>#518fbb</span></span></button></li>
								<li><button type="button" title="#6a65bb" style="background:#6a65bb"><span><span>#6a65bb</span></span></button></li>
								<li><button type="button" title="#9a54ce" style="background:#9a54ce"><span><span>#9a54ce</span></span></button></li>
								<li><button type="button" title="#e573ae" style="background:#e573ae"><span><span>#e573ae</span></span></button></li>
								<li><button type="button" title="#5a504b" style="background:#5a504b"><span><span>#5a504b</span></span></button></li>
								<li><button type="button" title="#767b86" style="background:#767b86"><span><span>#767b86</span></span></button></li>
								<li><button type="button" title="#951015" style="background:#951015"><span><span>#951015</span></span></button></li>
								<li><button type="button" title="#6e391a" style="background:#6e391a"><span><span>#6e391a</span></span></button></li>
								<li><button type="button" title="#785c25" style="background:#785c25"><span><span>#785c25</span></span></button></li>
								<li><button type="button" title="#5f5b25" style="background:#5f5b25"><span><span>#5f5b25</span></span></button></li>
								<li><button type="button" title="#4c511f" style="background:#4c511f"><span><span>#4c511f</span></span></button></li>
								<li><button type="button" title="#1c4827" style="background:#1c4827"><span><span>#1c4827</span></span></button></li>
								<li><button type="button" title="#0d514c" style="background:#0d514c"><span><span>#0d514c</span></span></button></li>
								<li><button type="button" title="#1b496a" style="background:#1b496a"><span><span>#1b496a</span></span></button></li>
								<li><button type="button" title="#2b285f" style="background:#2b285f"><span><span>#2b285f</span></span></button></li>
								<li><button type="button" title="#45245b" style="background:#45245b"><span><span>#45245b</span></span></button></li>
								<li><button type="button" title="#721947" style="background:#721947"><span><span>#721947</span></span></button></li>
								<li><button type="button" title="#352e2c" style="background:#352e2c"><span><span>#352e2c</span></span></button></li>
								<li><button type="button" title="#3c3f45" style="background:#3c3f45"><span><span>#3c3f45</span></span></button></li>
								</ul>
								<button type="button" title="더보기" class="se2_view_more husky_se2m_color_palette_more_btn"><span>더보기</span></button>
								<div class="husky_se2m_color_palette_recent" style="display:none">
									<h4>최근 사용한 색</h4>
									<ul class="se2_pick_color">
									<li></li>
									<!-- 최근 사용한 색 템플릿 -->
									<!-- <li><button type="button" title="#e97d81" style="background:#e97d81"><span><span>#e97d81</span></span></button></li> -->
									<!--최근 사용한 색 템플릿 -->
									</ul>
								</div>
								<div class="se2_palette2 husky_se2m_color_palette_colorpicker">
									<!--form action="http:test.emoticon.naver.com/colortable/TextAdd.nhn" method="post"-->
										<div class="se2_color_set">
											<span class="se2_selected_color"><span class="husky_se2m_cp_preview" style="background:#e97d81"></span></span><input type="text" name="" class="input_ty1 husky_se2m_cp_colorcode" value="#e97d81"><button type="button" class="se2_btn_insert husky_se2m_color_palette_ok_btn" title="입력"><span>입력</span></button></div>
										<!--input type="hidden" name="callback" value="http:test.emoticon.naver.com/colortable/result.jsp" />
										<input type="hidden" name="callback_func" value="1" />
										<input type="hidden" name="text_key" value="" />
										<input type="hidden" name="text_data" value="" />
									</form-->
									<div class="se2_gradation1 husky_se2m_cp_colpanel"></div>
									<div class="se2_gradation2 husky_se2m_cp_huepanel"></div>
								</div>
							</div>
                        </div>
					</div>
                    <!--글자색 -->
				</li>

				<li class="se2_pair husky_seditor_ui_BGColor"><span class="selected_color husky_se2m_BGColor_lastUsed" style="background-color:#4477f9"></span><span class="husky_seditor_ui_BGColorA"><button type="button" title="배경색" class="se2_bgcolor"><span>배경색</span></button></span><span class="husky_seditor_ui_BGColorB"><button type="button" title="더보기" class="se2_bgcolor_more"><span class="_buttonRound">더보기</span></button></span>
					<!-- 배경색 -->
					<div class="se2_layer se2_layer husky_se2m_BGColor_layer" style="display:none">
						<div class="se2_in_layer">
							<div class="se2_palette_bgcolor">
								<ul class="se2_background husky_se2m_bgcolor_list">
								<li><button type="button" title="#ff0000" style="background:#ff0000; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#6d30cf" style="background:#6d30cf; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#000000" style="background:#000000; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#ff6600" style="background:#ff6600; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#3333cc" style="background:#3333cc; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#333333" style="background:#333333; color:#ffff00"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#ffa700" style="background:#ffa700; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#009999" style="background:#009999; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#8e8e8e" style="background:#8e8e8e; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#cc9900" style="background:#cc9900; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#77b02b" style="background:#77b02b; color:#ffffff"><span><span>가나다</span></span></button></li>
								<li><button type="button" title="#ffffff" style="background:#ffffff; color:#000000"><span><span>가나다</span></span></button></li>
								</ul>
							</div>
							<div class="husky_se2m_BGColor_paletteHolder"></div>
                        </div>
					</div>
                    <!--배경색 -->
				</li>

				<li class="husky_seditor_ui_superscript"><button type="button" title="윗첨자" class="se2_sup"><span class="_buttonRound">윗첨자</span></button></li>

				<li class="husky_seditor_ui_subscript"><button type="button" title="아래첨자" class="se2_sub"><span class="_buttonRound">아래첨자</span></button></li>
</ul><ul>
				<li class="husky_seditor_ui_justifyleft"><button type="button" title="왼쪽정렬" class="se2_left"><span class="_buttonRound">왼쪽정렬</span></button></li>

				<li class="husky_seditor_ui_justifycenter"><button type="button" title="가운데정렬" class="se2_center"><span class="_buttonRound">가운데정렬</span></button></li>

				<li class="husky_seditor_ui_justifyright"><button type="button" title="오른쪽정렬" class="se2_right"><span class="_buttonRound">오른쪽정렬</span></button></li>

				<li class="husky_seditor_ui_justifyfull"><button type="button" title="양쪽정렬" class="se2_justify"><span class="_buttonRound">양쪽정렬</span></button></li>

				<li class="husky_seditor_ui_lineHeight"><button type="button" title="줄간격" class="se2_lineheight" ><span class="_buttonRound">줄간격</span></button>
					<!-- 줄간격 레이어 -->
					<div class="se2_layer husky_se2m_lineHeight_layer">
						<div class="se2_in_layer">
							<ul class="se2_l_line_height">
							<li><button type="button"><span>50%</span></button></li>
							<li><button type="button"><span>80%</span></button></li>
							<li><button type="button"><span>100%</span></button></li>
							<li><button type="button"><span>120%</span></button></li>
							<li><button type="button"><span>150%</span></button></li>
							<li><button type="button"><span>180%</span></button></li>
							<li><button type="button"><span>200%</span></button></li>
							</ul>
							<div class="se2_l_line_height_user husky_se2m_lineHeight_direct_input">
								<h3>직접 입력</h3>
								<span class="bx_input">
								<input type="text" class="input_ty1" maxlength="3" style="width:75px">
								<button type="button" title="1% 더하기" class="btn_up"><span>1% 더하기</span></button>
								<button type="button" title="1% 빼기" class="btn_down"><span>1% 빼기</span></button>
								</span>
								<div class="btn_area">
									<button type="button" class="se2_btn_apply3"><span>적용</span></button><button type="button" class="se2_btn_cancel3"><span>취소</span></button>
								</div>
							</div>
						</div>
					</div>
					<!--줄간격 레이어 -->
				</li>
</ul><ul>
				<li class="husky_seditor_ui_text_more" id="se2_text_more"><button title="더보기" type="button" class="se2_text_tool_more"><span>더보기</span></button>
					<div class="se2_sub_text_tool se2_sub_step1">
						<ul>
						<li class="husky_seditor_ui_orderedlist"><button type="button" title="번호매기기" class="se2_ol"><span class="_buttonRound">번호매기기</span></button></li>
						<li class="husky_seditor_ui_unorderedlist"><button type="button" title="글머리기호" class="se2_ul"><span class="_buttonRound">글머리기호</span></button></li>
						<li class="husky_seditor_ui_outdent"><button type="button" title="내어쓰기[Shift+Tab]" class="se2_outdent"><span class="_buttonRound">내어쓰기[Shift+Tab]</span></button></li>
						<li class="husky_seditor_ui_indent"><button type="button" title="들여쓰기[Tab]" class="se2_indent"><span class="_buttonRound">들여쓰기[Tab]</span></button></li>
						</ul>
					</div>
				</li>
</ul><ul>
				<li class="husky_seditor_ui_quote"><button type="button" title="인용구" class="se2_blockquote"><span class="_buttonRound">인용구</span></button>
					<!-- 인용구 -->
					<div class="se2_layer husky_seditor_blockquote_layer" style="margin-left:-407px; display:none;">
						<div class="se2_in_layer">
							<div class="se2_quote">
								<ul>
								<li class="q1"><button type="button" class="se2_quote1"><span><span>인용구 스타일1</span></span></button></li>
								<li class="q2"><button type="button" class="se2_quote2"><span><span>인용구 스타일2</span></span></button></li>
								<li class="q3"><button type="button" class="se2_quote3"><span><span>인용구 스타일3</span></span></button></li>
								<li class="q4"><button type="button" class="se2_quote4"><span><span>인용구 스타일4</span></span></button></li>
								<li class="q5"><button type="button" class="se2_quote5"><span><span>인용구 스타일5</span></span></button></li>
								<li class="q6"><button type="button" class="se2_quote6"><span><span>인용구 스타일6</span></span></button></li>
								<li class="q7"><button type="button" class="se2_quote7"><span><span>인용구 스타일7</span></span></button></li>
								<li class="q8"><button type="button" class="se2_quote8"><span><span>인용구 스타일8</span></span></button></li>
								<li class="q9"><button type="button" class="se2_quote9"><span><span>인용구 스타일9</span></span></button></li>
								<li class="q10"><button type="button" class="se2_quote10"><span><span>인용구 스타일10</span></span></button></li>
								</ul>
								<button type="button" class="se2_cancel2"><span>적용취소</span></button>
							</div>
						</div>
					</div>
					<!--인용구 -->
				</li>
</ul><ul>
				<li class="husky_seditor_ui_hyperlink"><button type="button" title="링크" class="se2_url"><span class="_buttonRound">링크</span></button>
					<!-- 링크 -->
					<div class="se2_layer" style="margin-left:-285px">
						<div class="se2_in_layer">
							<div class="se2_url2">
								<input type="text" class="input_ty1" value="http:">
								<button type="button" class="se2_apply"><span>적용</span></button><button type="button" class="se2_cancel"><span>취소</span></button>
							</div>
						</div>
					</div>
					<!--링크 -->
				</li>

				<li class="husky_seditor_ui_sCharacter"><button type="button" title="특수기호" class="se2_character"><span class="_buttonRound">특수기호</span></button>
					<!-- 특수기호 -->
					<div class="se2_layer husky_seditor_sCharacter_layer" style="margin-left:-448px;">
						<div class="se2_in_layer">
							<div class="se2_bx_character">
								<ul class="se2_char_tab">
								<li class="active"><button type="button" title="일반기호" class="se2_char1"><span>일반기호</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 일반기호 목록 -->
											<!-- <li class="hover"><button type="button"><span>｛</span></button></li><li class="active"><button type="button"><span>｝</span></button></li><li><button type="button"><span>〔</span></button></li><li><button type="button"><span>〕</span></button></li><li><button type="button"><span>〈</span></button></li><li><button type="button"><span>〉</span></button></li><li><button type="button"><span>《</span></button></li><li><button type="button"><span>》</span></button></li><li><button type="button"><span>「</span></button></li><li><button type="button"><span>」</span></button></li><li><button type="button"><span>『</span></button></li><li><button type="button"><span>』</span></button></li><li><button type="button"><span>【</span></button></li><li><button type="button"><span>】</span></button></li><li><button type="button"><span>‘</span></button></li><li><button type="button"><span>’</span></button></li><li><button type="button"><span>“</span></button></li><li><button type="button"><span>”</span></button></li><li><button type="button"><span>、</span></button></li><li><button type="button"><span>。</span></button></li><li><button type="button"><span>·</span></button></li><li><button type="button"><span>‥</span></button></li><li><button type="button"><span>…</span></button></li><li><button type="button"><span>§</span></button></li><li><button type="button"><span>※</span></button></li><li><button type="button"><span>☆</span></button></li><li><button type="button"><span>★</span></button></li><li><button type="button"><span>○</span></button></li><li><button type="button"><span>●</span></button></li><li><button type="button"><span>◎</span></button></li><li><button type="button"><span>◇</span></button></li><li><button type="button"><span>◆</span></button></li><li><button type="button"><span>□</span></button></li><li><button type="button"><span>■</span></button></li><li><button type="button"><span>△</span></button></li><li><button type="button"><span>▲</span></button></li><li><button type="button"><span>▽</span></button></li><li><button type="button"><span>▼</span></button></li><li><button type="button"><span>◁</span></button></li><li><button type="button"><span>◀</span></button></li><li><button type="button"><span>▷</span></button></li><li><button type="button"><span>▶</span></button></li><li><button type="button"><span>♤</span></button></li><li><button type="button"><span>♠</span></button></li><li><button type="button"><span>♡</span></button></li><li><button type="button"><span>♥</span></button></li><li><button type="button"><span>♧</span></button></li><li><button type="button"><span>♣</span></button></li><li><button type="button"><span>⊙</span></button></li><li><button type="button"><span>◈</span></button></li><li><button type="button"><span>▣</span></button></li><li><button type="button"><span>◐</span></button></li><li><button type="button"><span>◑</span></button></li><li><button type="button"><span>▒</span></button></li><li><button type="button"><span>▤</span></button></li><li><button type="button"><span>▥</span></button></li><li><button type="button"><span>▨</span></button></li><li><button type="button"><span>▧</span></button></li><li><button type="button"><span>▦</span></button></li><li><button type="button"><span>▩</span></button></li><li><button type="button"><span>±</span></button></li><li><button type="button"><span>×</span></button></li><li><button type="button"><span>÷</span></button></li><li><button type="button"><span>≠</span></button></li><li><button type="button"><span>≤</span></button></li><li><button type="button"><span>≥</span></button></li><li><button type="button"><span>∞</span></button></li><li><button type="button"><span>∴</span></button></li><li><button type="button"><span>°</span></button></li><li><button type="button"><span>′</span></button></li><li><button type="button"><span>″</span></button></li><li><button type="button"><span>∠</span></button></li><li><button type="button"><span>⊥</span></button></li><li><button type="button"><span>⌒</span></button></li><li><button type="button"><span>∂</span></button></li><li><button type="button"><span>≡</span></button></li><li><button type="button"><span>≒</span></button></li><li><button type="button"><span>≪</span></button></li><li><button type="button"><span>≫</span></button></li><li><button type="button"><span>√</span></button></li><li><button type="button"><span>∽</span></button></li><li><button type="button"><span>∝</span></button></li><li><button type="button"><span>∵</span></button></li><li><button type="button"><span>∫</span></button></li><li><button type="button"><span>∬</span></button></li><li><button type="button"><span>∈</span></button></li><li><button type="button"><span>∋</span></button></li><li><button type="button"><span>⊆</span></button></li><li><button type="button"><span>⊇</span></button></li><li><button type="button"><span>⊂</span></button></li><li><button type="button"><span>⊃</span></button></li><li><button type="button"><span>∪</span></button></li><li><button type="button"><span>∩</span></button></li><li><button type="button"><span>∧</span></button></li><li><button type="button"><span>∨</span></button></li><li><button type="button"><span>￢</span></button></li><li><button type="button"><span>⇒</span></button></li><li><button type="button"><span>⇔</span></button></li><li><button type="button"><span>∀</span></button></li><li><button type="button"><span>∃</span></button></li><li><button type="button"><span>´</span></button></li><li><button type="button"><span>～</span></button></li><li><button type="button"><span>ˇ</span></button></li><li><button type="button"><span>˘</span></button></li><li><button type="button"><span>˝</span></button></li><li><button type="button"><span>˚</span></button></li><li><button type="button"><span>˙</span></button></li><li><button type="button"><span>¸</span></button></li><li><button type="button"><span>˛</span></button></li><li><button type="button"><span>¡</span></button></li><li><button type="button"><span>¿</span></button></li><li><button type="button"><span>ː</span></button></li><li><button type="button"><span>∮</span></button></li><li><button type="button"><span>∑</span></button></li><li><button type="button"><span>∏</span></button></li><li><button type="button"><span>♭</span></button></li><li><button type="button"><span>♩</span></button></li><li><button type="button"><span>♪</span></button></li><li><button type="button"><span>♬</span></button></li><li><button type="button"><span>㉿</span></button></li><li><button type="button"><span>→</span></button></li><li><button type="button"><span>←</span></button></li><li><button type="button"><span>↑</span></button></li><li><button type="button"><span>↓</span></button></li><li><button type="button"><span>↔</span></button></li><li><button type="button"><span>↕</span></button></li><li><button type="button"><span>↗</span></button></li><li><button type="button"><span>↙</span></button></li><li><button type="button"><span>↖</span></button></li><li><button type="button"><span>↘</span></button></li><li><button type="button"><span>㈜</span></button></li><li><button type="button"><span>№</span></button></li><li><button type="button"><span>㏇</span></button></li><li><button type="button"><span>™</span></button></li><li><button type="button"><span>㏂</span></button></li><li><button type="button"><span>㏘</span></button></li><li><button type="button"><span>℡</span></button></li><li><button type="button"><span>♨</span></button></li><li><button type="button"><span>☏</span></button></li><li><button type="button"><span>☎</span></button></li><li><button type="button"><span>☜</span></button></li><li><button type="button"><span>☞</span></button></li><li><button type="button"><span>¶</span></button></li><li><button type="button"><span>†</span></button></li><li><button type="button"><span>‡</span></button></li><li><button type="button"><span>®</span></button></li><li><button type="button"><span>ª</span></button></li><li><button type="button"><span>º</span></button></li><li><button type="button"><span>♂</span></button></li><li><button type="button"><span>♀</span></button></li> -->
										</ul>
									</div>
								</li>
								<li><button type="button" title="숫자와 단위" class="se2_char2"><span>숫자와 단위</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 숫자와 단위 목록 -->
											<!-- <li class="hover"><button type="button"><span>½</span></button></li><li><button type="button"><span>⅓</span></button></li><li><button type="button"><span>⅔</span></button></li><li><button type="button"><span>¼</span></button></li><li><button type="button"><span>¾</span></button></li><li><button type="button"><span>⅛</span></button></li><li><button type="button"><span>⅜</span></button></li><li><button type="button"><span>⅝</span></button></li><li><button type="button"><span>⅞</span></button></li><li><button type="button"><span>¹</span></button></li><li><button type="button"><span>²</span></button></li><li><button type="button"><span>³</span></button></li><li><button type="button"><span>⁴</span></button></li><li><button type="button"><span>ⁿ</span></button></li><li><button type="button"><span>₁</span></button></li><li><button type="button"><span>₂</span></button></li><li><button type="button"><span>₃</span></button></li><li><button type="button"><span>₄</span></button></li><li><button type="button"><span>Ⅰ</span></button></li><li><button type="button"><span>Ⅱ</span></button></li><li><button type="button"><span>Ⅲ</span></button></li><li><button type="button"><span>Ⅳ</span></button></li><li><button type="button"><span>Ⅴ</span></button></li><li><button type="button"><span>Ⅵ</span></button></li><li><button type="button"><span>Ⅶ</span></button></li><li><button type="button"><span>Ⅷ</span></button></li><li><button type="button"><span>Ⅸ</span></button></li><li><button type="button"><span>Ⅹ</span></button></li><li><button type="button"><span>ⅰ</span></button></li><li><button type="button"><span>ⅱ</span></button></li><li><button type="button"><span>ⅲ</span></button></li><li><button type="button"><span>ⅳ</span></button></li><li><button type="button"><span>ⅴ</span></button></li><li><button type="button"><span>ⅵ</span></button></li><li><button type="button"><span>ⅶ</span></button></li><li><button type="button"><span>ⅷ</span></button></li><li><button type="button"><span>ⅸ</span></button></li><li><button type="button"><span>ⅹ</span></button></li><li><button type="button"><span>￦</span></button></li><li><button type="button"><span>$</span></button></li><li><button type="button"><span>￥</span></button></li><li><button type="button"><span>￡</span></button></li><li><button type="button"><span>€</span></button></li><li><button type="button"><span>℃</span></button></li><li><button type="button"><span>A</span></button></li><li><button type="button"><span>℉</span></button></li><li><button type="button"><span>￠</span></button></li><li><button type="button"><span>¤</span></button></li><li><button type="button"><span>‰</span></button></li><li><button type="button"><span>㎕</span></button></li><li><button type="button"><span>㎖</span></button></li><li><button type="button"><span>㎗</span></button></li><li><button type="button"><span>ℓ</span></button></li><li><button type="button"><span>㎘</span></button></li><li><button type="button"><span>㏄</span></button></li><li><button type="button"><span>㎣</span></button></li><li><button type="button"><span>㎤</span></button></li><li><button type="button"><span>㎥</span></button></li><li><button type="button"><span>㎦</span></button></li><li><button type="button"><span>㎙</span></button></li><li><button type="button"><span>㎚</span></button></li><li><button type="button"><span>㎛</span></button></li><li><button type="button"><span>㎜</span></button></li><li><button type="button"><span>㎝</span></button></li><li><button type="button"><span>㎞</span></button></li><li><button type="button"><span>㎟</span></button></li><li><button type="button"><span>㎠</span></button></li><li><button type="button"><span>㎡</span></button></li><li><button type="button"><span>㎢</span></button></li><li><button type="button"><span>㏊</span></button></li><li><button type="button"><span>㎍</span></button></li><li><button type="button"><span>㎎</span></button></li><li><button type="button"><span>㎏</span></button></li><li><button type="button"><span>㏏</span></button></li><li><button type="button"><span>㎈</span></button></li><li><button type="button"><span>㎉</span></button></li><li><button type="button"><span>㏈</span></button></li><li><button type="button"><span>㎧</span></button></li><li><button type="button"><span>㎨</span></button></li><li><button type="button"><span>㎰</span></button></li><li><button type="button"><span>㎱</span></button></li><li><button type="button"><span>㎲</span></button></li><li><button type="button"><span>㎳</span></button></li><li><button type="button"><span>㎴</span></button></li><li><button type="button"><span>㎵</span></button></li><li><button type="button"><span>㎶</span></button></li><li><button type="button"><span>㎷</span></button></li><li><button type="button"><span>㎸</span></button></li><li><button type="button"><span>㎹</span></button></li><li><button type="button"><span>㎀</span></button></li><li><button type="button"><span>㎁</span></button></li><li><button type="button"><span>㎂</span></button></li><li><button type="button"><span>㎃</span></button></li><li><button type="button"><span>㎄</span></button></li><li><button type="button"><span>㎺</span></button></li><li><button type="button"><span>㎻</span></button></li><li><button type="button"><span>㎼</span></button></li><li><button type="button"><span>㎽</span></button></li><li><button type="button"><span>㎾</span></button></li><li><button type="button"><span>㎿</span></button></li><li><button type="button"><span>㎐</span></button></li><li><button type="button"><span>㎑</span></button></li><li><button type="button"><span>㎒</span></button></li><li><button type="button"><span>㎓</span></button></li><li><button type="button"><span>㎔</span></button></li><li><button type="button"><span>Ω</span></button></li><li><button type="button"><span>㏀</span></button></li><li><button type="button"><span>㏁</span></button></li><li><button type="button"><span>㎊</span></button></li><li><button type="button"><span>㎋</span></button></li><li><button type="button"><span>㎌</span></button></li><li><button type="button"><span>㏖</span></button></li><li><button type="button"><span>㏅</span></button></li><li><button type="button"><span>㎭</span></button></li><li><button type="button"><span>㎮</span></button></li><li><button type="button"><span>㎯</span></button></li><li><button type="button"><span>㏛</span></button></li><li><button type="button"><span>㎩</span></button></li><li><button type="button"><span>㎪</span></button></li><li><button type="button"><span>㎫</span></button></li><li><button type="button"><span>㎬</span></button></li><li><button type="button"><span>㏝</span></button></li><li><button type="button"><span>㏐</span></button></li><li><button type="button"><span>㏓</span></button></li><li><button type="button"><span>㏃</span></button></li><li><button type="button"><span>㏉</span></button></li><li><button type="button"><span>㏜</span></button></li><li><button type="button"><span>㏆</span></button></li> -->
										</ul>
									</div>
								</li>
								<li><button type="button" title="원,괄호" class="se2_char3"><span>원,괄호</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 원,괄호 목록 -->
											<!-- <li><button type="button"><span>㉠</span></button></li><li><button type="button"><span>㉡</span></button></li><li><button type="button"><span>㉢</span></button></li><li><button type="button"><span>㉣</span></button></li><li><button type="button"><span>㉤</span></button></li><li><button type="button"><span>㉥</span></button></li><li><button type="button"><span>㉦</span></button></li><li><button type="button"><span>㉧</span></button></li><li><button type="button"><span>㉨</span></button></li><li><button type="button"><span>㉩</span></button></li><li><button type="button"><span>㉪</span></button></li><li><button type="button"><span>㉫</span></button></li><li><button type="button"><span>㉬</span></button></li><li><button type="button"><span>㉭</span></button></li><li><button type="button"><span>㉮</span></button></li><li><button type="button"><span>㉯</span></button></li><li><button type="button"><span>㉰</span></button></li><li><button type="button"><span>㉱</span></button></li><li><button type="button"><span>㉲</span></button></li><li><button type="button"><span>㉳</span></button></li><li><button type="button"><span>㉴</span></button></li><li><button type="button"><span>㉵</span></button></li><li><button type="button"><span>㉶</span></button></li><li><button type="button"><span>㉷</span></button></li><li><button type="button"><span>㉸</span></button></li><li><button type="button"><span>㉹</span></button></li><li><button type="button"><span>㉺</span></button></li><li><button type="button"><span>㉻</span></button></li><li><button type="button"><span>ⓐ</span></button></li><li><button type="button"><span>ⓑ</span></button></li><li><button type="button"><span>ⓒ</span></button></li><li><button type="button"><span>ⓓ</span></button></li><li><button type="button"><span>ⓔ</span></button></li><li><button type="button"><span>ⓕ</span></button></li><li><button type="button"><span>ⓖ</span></button></li><li><button type="button"><span>ⓗ</span></button></li><li><button type="button"><span>ⓘ</span></button></li><li><button type="button"><span>ⓙ</span></button></li><li><button type="button"><span>ⓚ</span></button></li><li><button type="button"><span>ⓛ</span></button></li><li><button type="button"><span>ⓜ</span></button></li><li><button type="button"><span>ⓝ</span></button></li><li><button type="button"><span>ⓞ</span></button></li><li><button type="button"><span>ⓟ</span></button></li><li><button type="button"><span>ⓠ</span></button></li><li><button type="button"><span>ⓡ</span></button></li><li><button type="button"><span>ⓢ</span></button></li><li><button type="button"><span>ⓣ</span></button></li><li><button type="button"><span>ⓤ</span></button></li><li><button type="button"><span>ⓥ</span></button></li><li><button type="button"><span>ⓦ</span></button></li><li><button type="button"><span>ⓧ</span></button></li><li><button type="button"><span>ⓨ</span></button></li><li><button type="button"><span>ⓩ</span></button></li><li><button type="button"><span>①</span></button></li><li><button type="button"><span>②</span></button></li><li><button type="button"><span>③</span></button></li><li><button type="button"><span>④</span></button></li><li><button type="button"><span>⑤</span></button></li><li><button type="button"><span>⑥</span></button></li><li><button type="button"><span>⑦</span></button></li><li><button type="button"><span>⑧</span></button></li><li><button type="button"><span>⑨</span></button></li><li><button type="button"><span>⑩</span></button></li><li><button type="button"><span>⑪</span></button></li><li><button type="button"><span>⑫</span></button></li><li><button type="button"><span>⑬</span></button></li><li><button type="button"><span>⑭</span></button></li><li><button type="button"><span>⑮</span></button></li><li><button type="button"><span>㈀</span></button></li><li><button type="button"><span>㈁</span></button></li><li class="hover"><button type="button"><span>㈂</span></button></li><li><button type="button"><span>㈃</span></button></li><li><button type="button"><span>㈄</span></button></li><li><button type="button"><span>㈅</span></button></li><li><button type="button"><span>㈆</span></button></li><li><button type="button"><span>㈇</span></button></li><li><button type="button"><span>㈈</span></button></li><li><button type="button"><span>㈉</span></button></li><li><button type="button"><span>㈊</span></button></li><li><button type="button"><span>㈋</span></button></li><li><button type="button"><span>㈌</span></button></li><li><button type="button"><span>㈍</span></button></li><li><button type="button"><span>㈎</span></button></li><li><button type="button"><span>㈏</span></button></li><li><button type="button"><span>㈐</span></button></li><li><button type="button"><span>㈑</span></button></li><li><button type="button"><span>㈒</span></button></li><li><button type="button"><span>㈓</span></button></li><li><button type="button"><span>㈔</span></button></li><li><button type="button"><span>㈕</span></button></li><li><button type="button"><span>㈖</span></button></li><li><button type="button"><span>㈗</span></button></li><li><button type="button"><span>㈘</span></button></li><li><button type="button"><span>㈙</span></button></li><li><button type="button"><span>㈚</span></button></li><li><button type="button"><span>㈛</span></button></li><li><button type="button"><span>⒜</span></button></li><li><button type="button"><span>⒝</span></button></li><li><button type="button"><span>⒞</span></button></li><li><button type="button"><span>⒟</span></button></li><li><button type="button"><span>⒠</span></button></li><li><button type="button"><span>⒡</span></button></li><li><button type="button"><span>⒢</span></button></li><li><button type="button"><span>⒣</span></button></li><li><button type="button"><span>⒤</span></button></li><li><button type="button"><span>⒥</span></button></li><li><button type="button"><span>⒦</span></button></li><li><button type="button"><span>⒧</span></button></li><li><button type="button"><span>⒨</span></button></li><li><button type="button"><span>⒩</span></button></li><li><button type="button"><span>⒪</span></button></li><li><button type="button"><span>⒫</span></button></li><li><button type="button"><span>⒬</span></button></li><li><button type="button"><span>⒭</span></button></li><li><button type="button"><span>⒮</span></button></li><li><button type="button"><span>⒯</span></button></li><li><button type="button"><span>⒰</span></button></li><li><button type="button"><span>⒱</span></button></li><li><button type="button"><span>⒲</span></button></li><li><button type="button"><span>⒳</span></button></li><li><button type="button"><span>⒴</span></button></li><li><button type="button"><span>⒵</span></button></li><li><button type="button"><span>⑴</span></button></li><li><button type="button"><span>⑵</span></button></li><li><button type="button"><span>⑶</span></button></li><li><button type="button"><span>⑷</span></button></li><li><button type="button"><span>⑸</span></button></li><li><button type="button"><span>⑹</span></button></li><li><button type="button"><span>⑺</span></button></li><li><button type="button"><span>⑻</span></button></li><li><button type="button"><span>⑼</span></button></li><li><button type="button"><span>⑽</span></button></li><li><button type="button"><span>⑾</span></button></li><li><button type="button"><span>⑿</span></button></li><li><button type="button"><span>⒀</span></button></li><li><button type="button"><span>⒁</span></button></li><li><button type="button"><span>⒂</span></button></li> -->
										</ul>
									</div>
								</li>
								<li><button type="button" title="한글" class="se2_char4"><span>한글</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 한글 목록 -->
											<!-- <li><button type="button"><span>ㄱ</span></button></li><li><button type="button"><span>ㄲ</span></button></li><li><button type="button"><span>ㄳ</span></button></li><li><button type="button"><span>ㄴ</span></button></li><li><button type="button"><span>ㄵ</span></button></li><li><button type="button"><span>ㄶ</span></button></li><li><button type="button"><span>ㄷ</span></button></li><li><button type="button"><span>ㄸ</span></button></li><li><button type="button"><span>ㄹ</span></button></li><li><button type="button"><span>ㄺ</span></button></li><li><button type="button"><span>ㄻ</span></button></li><li><button type="button"><span>ㄼ</span></button></li><li><button type="button"><span>ㄽ</span></button></li><li><button type="button"><span>ㄾ</span></button></li><li><button type="button"><span>ㄿ</span></button></li><li><button type="button"><span>ㅀ</span></button></li><li><button type="button"><span>ㅁ</span></button></li><li><button type="button"><span>ㅂ</span></button></li><li><button type="button"><span>ㅃ</span></button></li><li><button type="button"><span>ㅄ</span></button></li><li><button type="button"><span>ㅅ</span></button></li><li><button type="button"><span>ㅆ</span></button></li><li><button type="button"><span>ㅇ</span></button></li><li><button type="button"><span>ㅈ</span></button></li><li><button type="button"><span>ㅉ</span></button></li><li><button type="button"><span>ㅊ</span></button></li><li><button type="button"><span>ㅋ</span></button></li><li><button type="button"><span>ㅌ</span></button></li><li><button type="button"><span>ㅍ</span></button></li><li><button type="button"><span>ㅎ</span></button></li><li><button type="button"><span>ㅏ</span></button></li><li><button type="button"><span>ㅐ</span></button></li><li><button type="button"><span>ㅑ</span></button></li><li><button type="button"><span>ㅒ</span></button></li><li><button type="button"><span>ㅓ</span></button></li><li><button type="button"><span>ㅔ</span></button></li><li><button type="button"><span>ㅕ</span></button></li><li><button type="button"><span>ㅖ</span></button></li><li><button type="button"><span>ㅗ</span></button></li><li><button type="button"><span>ㅘ</span></button></li><li><button type="button"><span>ㅙ</span></button></li><li><button type="button"><span>ㅚ</span></button></li><li><button type="button"><span>ㅛ</span></button></li><li><button type="button"><span>ㅜ</span></button></li><li><button type="button"><span>ㅝ</span></button></li><li><button type="button"><span>ㅞ</span></button></li><li><button type="button"><span>ㅟ</span></button></li><li><button type="button"><span>ㅠ</span></button></li><li><button type="button"><span>ㅡ</span></button></li><li><button type="button"><span>ㅢ</span></button></li><li><button type="button"><span>ㅣ</span></button></li><li><button type="button"><span>ㅥ</span></button></li><li><button type="button"><span>ㅦ</span></button></li><li><button type="button"><span>ㅧ</span></button></li><li><button type="button"><span>ㅨ</span></button></li><li><button type="button"><span>ㅩ</span></button></li><li><button type="button"><span>ㅪ</span></button></li><li><button type="button"><span>ㅫ</span></button></li><li><button type="button"><span>ㅬ</span></button></li><li><button type="button"><span>ㅭ</span></button></li><li><button type="button"><span>ㅮ</span></button></li><li><button type="button"><span>ㅯ</span></button></li><li><button type="button"><span>ㅰ</span></button></li><li><button type="button"><span>ㅱ</span></button></li><li><button type="button"><span>ㅲ</span></button></li><li><button type="button"><span>ㅳ</span></button></li><li><button type="button"><span>ㅴ</span></button></li><li><button type="button"><span>ㅵ</span></button></li><li><button type="button"><span>ㅶ</span></button></li><li><button type="button"><span>ㅷ</span></button></li><li><button type="button"><span>ㅸ</span></button></li><li><button type="button"><span>ㅹ</span></button></li><li><button type="button"><span>ㅺ</span></button></li><li><button type="button"><span>ㅻ</span></button></li><li><button type="button"><span>ㅼ</span></button></li><li><button type="button"><span>ㅽ</span></button></li><li><button type="button"><span>ㅾ</span></button></li><li><button type="button"><span>ㅿ</span></button></li><li><button type="button"><span>ㆀ</span></button></li><li><button type="button"><span>ㆁ</span></button></li><li><button type="button"><span>ㆂ</span></button></li><li><button type="button"><span>ㆃ</span></button></li><li><button type="button"><span>ㆄ</span></button></li><li><button type="button"><span>ㆅ</span></button></li><li><button type="button"><span>ㆆ</span></button></li><li><button type="button"><span>ㆇ</span></button></li><li><button type="button"><span>ㆈ</span></button></li><li><button type="button"><span>ㆉ</span></button></li><li><button type="button"><span>ㆊ</span></button></li><li><button type="button"><span>ㆋ</span></button></li><li><button type="button"><span>ㆌ</span></button></li><li><button type="button"><span>ㆍ</span></button></li><li><button type="button"><span>ㆎ</span></button></li> -->
										</ul>
									</div>
								</li>
								<li><button type="button" title="그리스,라틴어" class="se2_char5"><span>그리스,라틴어</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 그리스,라틴어 목록 -->
											<!-- <li><button type="button"><span>Α</span></button></li><li><button type="button"><span>Β</span></button></li><li><button type="button"><span>Γ</span></button></li><li><button type="button"><span>Δ</span></button></li><li><button type="button"><span>Ε</span></button></li><li><button type="button"><span>Ζ</span></button></li><li><button type="button"><span>Η</span></button></li><li><button type="button"><span>Θ</span></button></li><li><button type="button"><span>Ι</span></button></li><li><button type="button"><span>Κ</span></button></li><li><button type="button"><span>Λ</span></button></li><li><button type="button"><span>Μ</span></button></li><li><button type="button"><span>Ν</span></button></li><li><button type="button"><span>Ξ</span></button></li><li class="hover"><button type="button"><span>Ο</span></button></li><li><button type="button"><span>Π</span></button></li><li><button type="button"><span>Ρ</span></button></li><li><button type="button"><span>Σ</span></button></li><li><button type="button"><span>Τ</span></button></li><li><button type="button"><span>Υ</span></button></li><li><button type="button"><span>Φ</span></button></li><li><button type="button"><span>Χ</span></button></li><li><button type="button"><span>Ψ</span></button></li><li><button type="button"><span>Ω</span></button></li><li><button type="button"><span>α</span></button></li><li><button type="button"><span>β</span></button></li><li><button type="button"><span>γ</span></button></li><li><button type="button"><span>δ</span></button></li><li><button type="button"><span>ε</span></button></li><li><button type="button"><span>ζ</span></button></li><li><button type="button"><span>η</span></button></li><li><button type="button"><span>θ</span></button></li><li><button type="button"><span>ι</span></button></li><li><button type="button"><span>κ</span></button></li><li><button type="button"><span>λ</span></button></li><li><button type="button"><span>μ</span></button></li><li><button type="button"><span>ν</span></button></li><li><button type="button"><span>ξ</span></button></li><li><button type="button"><span>ο</span></button></li><li><button type="button"><span>π</span></button></li><li><button type="button"><span>ρ</span></button></li><li><button type="button"><span>σ</span></button></li><li><button type="button"><span>τ</span></button></li><li><button type="button"><span>υ</span></button></li><li><button type="button"><span>φ</span></button></li><li><button type="button"><span>χ</span></button></li><li><button type="button"><span>ψ</span></button></li><li><button type="button"><span>ω</span></button></li><li><button type="button"><span>Æ</span></button></li><li><button type="button"><span>Ð</span></button></li><li><button type="button"><span>Ħ</span></button></li><li><button type="button"><span>Ĳ</span></button></li><li><button type="button"><span>Ŀ</span></button></li><li><button type="button"><span>Ł</span></button></li><li><button type="button"><span>Ø</span></button></li><li><button type="button"><span>Œ</span></button></li><li><button type="button"><span>Þ</span></button></li><li><button type="button"><span>Ŧ</span></button></li><li><button type="button"><span>Ŋ</span></button></li><li><button type="button"><span>æ</span></button></li><li><button type="button"><span>đ</span></button></li><li><button type="button"><span>ð</span></button></li><li><button type="button"><span>ħ</span></button></li><li><button type="button"><span>I</span></button></li><li><button type="button"><span>ĳ</span></button></li><li><button type="button"><span>ĸ</span></button></li><li><button type="button"><span>ŀ</span></button></li><li><button type="button"><span>ł</span></button></li><li><button type="button"><span>ł</span></button></li><li><button type="button"><span>œ</span></button></li><li><button type="button"><span>ß</span></button></li><li><button type="button"><span>þ</span></button></li><li><button type="button"><span>ŧ</span></button></li><li><button type="button"><span>ŋ</span></button></li><li><button type="button"><span>ŉ</span></button></li><li><button type="button"><span>Б</span></button></li><li><button type="button"><span>Г</span></button></li><li><button type="button"><span>Д</span></button></li><li><button type="button"><span>Ё</span></button></li><li><button type="button"><span>Ж</span></button></li><li><button type="button"><span>З</span></button></li><li><button type="button"><span>И</span></button></li><li><button type="button"><span>Й</span></button></li><li><button type="button"><span>Л</span></button></li><li><button type="button"><span>П</span></button></li><li><button type="button"><span>Ц</span></button></li><li><button type="button"><span>Ч</span></button></li><li><button type="button"><span>Ш</span></button></li><li><button type="button"><span>Щ</span></button></li><li><button type="button"><span>Ъ</span></button></li><li><button type="button"><span>Ы</span></button></li><li><button type="button"><span>Ь</span></button></li><li><button type="button"><span>Э</span></button></li><li><button type="button"><span>Ю</span></button></li><li><button type="button"><span>Я</span></button></li><li><button type="button"><span>б</span></button></li><li><button type="button"><span>в</span></button></li><li><button type="button"><span>г</span></button></li><li><button type="button"><span>д</span></button></li><li><button type="button"><span>ё</span></button></li><li><button type="button"><span>ж</span></button></li><li><button type="button"><span>з</span></button></li><li><button type="button"><span>и</span></button></li><li><button type="button"><span>й</span></button></li><li><button type="button"><span>л</span></button></li><li><button type="button"><span>п</span></button></li><li><button type="button"><span>ф</span></button></li><li><button type="button"><span>ц</span></button></li><li><button type="button"><span>ч</span></button></li><li><button type="button"><span>ш</span></button></li><li><button type="button"><span>щ</span></button></li><li><button type="button"><span>ъ</span></button></li><li><button type="button"><span>ы</span></button></li><li><button type="button"><span>ь</span></button></li><li><button type="button"><span>э</span></button></li><li><button type="button"><span>ю</span></button></li><li><button type="button"><span>я</span></button></li> -->
										</ul>
									</div>
								</li>
								<li><button type="button" title="일본어" class="se2_char6"><span>일본어</span></button>
									<div class="se2_s_character">
										<ul class="husky_se2m_sCharacter_list">
											<li></li>
											<!-- 일본어 목록 -->
											<!-- <li><button type="button"><span>ぁ</span></button></li><li class="hover"><button type="button"><span>あ</span></button></li><li><button type="button"><span>ぃ</span></button></li><li><button type="button"><span>い</span></button></li><li><button type="button"><span>ぅ</span></button></li><li><button type="button"><span>う</span></button></li><li><button type="button"><span>ぇ</span></button></li><li><button type="button"><span>え</span></button></li><li><button type="button"><span>ぉ</span></button></li><li><button type="button"><span>お</span></button></li><li><button type="button"><span>か</span></button></li><li><button type="button"><span>が</span></button></li><li><button type="button"><span>き</span></button></li><li><button type="button"><span>ぎ</span></button></li><li><button type="button"><span>く</span></button></li><li><button type="button"><span>ぐ</span></button></li><li><button type="button"><span>け</span></button></li><li><button type="button"><span>げ</span></button></li><li><button type="button"><span>こ</span></button></li><li><button type="button"><span>ご</span></button></li><li><button type="button"><span>さ</span></button></li><li><button type="button"><span>ざ</span></button></li><li><button type="button"><span>し</span></button></li><li><button type="button"><span>じ</span></button></li><li><button type="button"><span>す</span></button></li><li><button type="button"><span>ず</span></button></li><li><button type="button"><span>せ</span></button></li><li><button type="button"><span>ぜ</span></button></li><li><button type="button"><span>そ</span></button></li><li><button type="button"><span>ぞ</span></button></li><li><button type="button"><span>た</span></button></li><li><button type="button"><span>だ</span></button></li><li><button type="button"><span>ち</span></button></li><li><button type="button"><span>ぢ</span></button></li><li><button type="button"><span>っ</span></button></li><li><button type="button"><span>つ</span></button></li><li><button type="button"><span>づ</span></button></li><li><button type="button"><span>て</span></button></li><li><button type="button"><span>で</span></button></li><li><button type="button"><span>と</span></button></li><li><button type="button"><span>ど</span></button></li><li><button type="button"><span>な</span></button></li><li><button type="button"><span>に</span></button></li><li><button type="button"><span>ぬ</span></button></li><li><button type="button"><span>ね</span></button></li><li><button type="button"><span>の</span></button></li><li><button type="button"><span>は</span></button></li><li><button type="button"><span>ば</span></button></li><li><button type="button"><span>ぱ</span></button></li><li><button type="button"><span>ひ</span></button></li><li><button type="button"><span>び</span></button></li><li><button type="button"><span>ぴ</span></button></li><li><button type="button"><span>ふ</span></button></li><li><button type="button"><span>ぶ</span></button></li><li><button type="button"><span>ぷ</span></button></li><li><button type="button"><span>へ</span></button></li><li><button type="button"><span>べ</span></button></li><li><button type="button"><span>ぺ</span></button></li><li><button type="button"><span>ほ</span></button></li><li><button type="button"><span>ぼ</span></button></li><li><button type="button"><span>ぽ</span></button></li><li><button type="button"><span>ま</span></button></li><li><button type="button"><span>み</span></button></li><li><button type="button"><span>む</span></button></li><li><button type="button"><span>め</span></button></li><li><button type="button"><span>も</span></button></li><li><button type="button"><span>ゃ</span></button></li><li><button type="button"><span>や</span></button></li><li><button type="button"><span>ゅ</span></button></li><li><button type="button"><span>ゆ</span></button></li><li><button type="button"><span>ょ</span></button></li><li><button type="button"><span>よ</span></button></li><li><button type="button"><span>ら</span></button></li><li><button type="button"><span>り</span></button></li><li><button type="button"><span>る</span></button></li><li><button type="button"><span>れ</span></button></li><li><button type="button"><span>ろ</span></button></li><li><button type="button"><span>ゎ</span></button></li><li><button type="button"><span>わ</span></button></li><li><button type="button"><span>ゐ</span></button></li><li><button type="button"><span>ゑ</span></button></li><li><button type="button"><span>を</span></button></li><li><button type="button"><span>ん</span></button></li><li><button type="button"><span>ァ</span></button></li><li><button type="button"><span>ア</span></button></li><li><button type="button"><span>ィ</span></button></li><li><button type="button"><span>イ</span></button></li><li><button type="button"><span>ゥ</span></button></li><li><button type="button"><span>ウ</span></button></li><li><button type="button"><span>ェ</span></button></li><li><button type="button"><span>エ</span></button></li><li><button type="button"><span>ォ</span></button></li><li><button type="button"><span>オ</span></button></li><li><button type="button"><span>カ</span></button></li><li><button type="button"><span>ガ</span></button></li><li><button type="button"><span>キ</span></button></li><li><button type="button"><span>ギ</span></button></li><li><button type="button"><span>ク</span></button></li><li><button type="button"><span>グ</span></button></li><li><button type="button"><span>ケ</span></button></li><li><button type="button"><span>ゲ</span></button></li><li><button type="button"><span>コ</span></button></li><li><button type="button"><span>ゴ</span></button></li><li><button type="button"><span>サ</span></button></li><li><button type="button"><span>ザ</span></button></li><li><button type="button"><span>シ</span></button></li><li><button type="button"><span>ジ</span></button></li><li><button type="button"><span>ス</span></button></li><li><button type="button"><span>ズ</span></button></li><li><button type="button"><span>セ</span></button></li><li><button type="button"><span>ゼ</span></button></li><li><button type="button"><span>ソ</span></button></li><li><button type="button"><span>ゾ</span></button></li><li><button type="button"><span>タ</span></button></li><li><button type="button"><span>ダ</span></button></li><li><button type="button"><span>チ</span></button></li><li><button type="button"><span>ヂ</span></button></li><li><button type="button"><span>ッ</span></button></li><li><button type="button"><span>ツ</span></button></li><li><button type="button"><span>ヅ</span></button></li><li><button type="button"><span>テ</span></button></li><li><button type="button"><span>デ</span></button></li><li><button type="button"><span>ト</span></button></li><li><button type="button"><span>ド</span></button></li><li><button type="button"><span>ナ</span></button></li><li><button type="button"><span>ニ</span></button></li><li><button type="button"><span>ヌ</span></button></li><li><button type="button"><span>ネ</span></button></li><li><button type="button"><span>ノ</span></button></li><li><button type="button"><span>ハ</span></button></li><li><button type="button"><span>バ</span></button></li><li><button type="button"><span>パ</span></button></li><li><button type="button"><span>ヒ</span></button></li><li><button type="button"><span>ビ</span></button></li><li><button type="button"><span>ピ</span></button></li><li><button type="button"><span>フ</span></button></li><li><button type="button"><span>ブ</span></button></li><li><button type="button"><span>プ</span></button></li><li><button type="button"><span>ヘ</span></button></li><li><button type="button"><span>ベ</span></button></li><li><button type="button"><span>ペ</span></button></li><li><button type="button"><span>ホ</span></button></li><li><button type="button"><span>ボ</span></button></li><li><button type="button"><span>ポ</span></button></li><li><button type="button"><span>マ</span></button></li><li><button type="button"><span>ミ</span></button></li><li><button type="button"><span>ム</span></button></li><li><button type="button"><span>メ</span></button></li><li><button type="button"><span>モ</span></button></li><li><button type="button"><span>ャ</span></button></li><li><button type="button"><span>ヤ</span></button></li><li><button type="button"><span>ュ</span></button></li><li><button type="button"><span>ユ</span></button></li><li><button type="button"><span>ョ</span></button></li><li><button type="button"><span>ヨ</span></button></li><li><button type="button"><span>ラ</span></button></li><li><button type="button"><span>リ</span></button></li><li><button type="button"><span>ル</span></button></li><li><button type="button"><span>レ</span></button></li><li><button type="button"><span>ロ</span></button></li><li><button type="button"><span>ヮ</span></button></li><li><button type="button"><span>ワ</span></button></li><li><button type="button"><span>ヰ</span></button></li><li><button type="button"><span>ヱ</span></button></li><li><button type="button"><span>ヲ</span></button></li><li><button type="button"><span>ン</span></button></li><li><button type="button"><span>ヴ</span></button></li><li><button type="button"><span>ヵ</span></button></li><li><button type="button"><span>ヶ</span></button></li> -->
										</ul>
									</div>
								</li>
								</ul>
								<p class="se2_apply_character">
									<label for="char_preview">선택한 기호</label> <input type="text" name="char_preview" id="char_preview" value="®º⊆●○" class="input_ty1"><button type="button" class="se2_confirm"><span>적용</span></button><button type="button" class="se2_cancel husky_se2m_sCharacter_close"><span>취소</span></button>
								</p>
							</div>
						</div>
					</div>
					<!--특수기호 -->
				</li>

				<li class="husky_seditor_ui_table"><button type="button" title="표" class="se2_table"><span class="_buttonRound">표</span></button>
					<!--@lazyload_html create_table-->
					<!-- 표 -->
					<div class="se2_layer husky_se2m_table_layer" style="margin-left:-171px">
						<div class="se2_in_layer">
							<div class="se2_table_set">
								<fieldset>
								<legend>칸수 지정</legend>
									<dl class="se2_cell_num">
									<dt><label for="row">행</label></dt>
									<dd><input id="row" name="" type="text" maxlength="2" value="4" class="input_ty2">
										<button type="button" class="se2_add"><span>1행추가</span></button>
										<button type="button" class="se2_del"><span>1행삭제</span></button>
									</dd>
									<dt><label for="col">열</label></dt>
									<dd><input id="col" name="" type="text" maxlength="2" value="4" class="input_ty2">
										<button type="button" class="se2_add"><span>1열추가</span></button>
										<button type="button" class="se2_del"><span>1열삭제</span></button>
									</dd>
									</dl>
									<table border="0" cellspacing="1" class="se2_pre_table husky_se2m_table_preview">
									<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									</tr>
									<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									</tr>
									<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									</tr>
									</table>
								</fieldset>
								<fieldset>
									<legend>속성직접입력</legend>
									<dl class="se2_t_proper1">
									<dt><input type="radio" id="se2_tbp1" name="se2_tbp" checked><label for="se2_tbp1">속성직접입력</label></dt>
									<dd>
										<dl class="se2_t_proper1_1">
										<dt><label>표스타일</label></dt>
										<dd><div class="se2_select_ty1"><span class="se2_b_style3 husky_se2m_table_border_style_preview"></span><button type="button" title="더보기" class="se2_view_more"><span>더보기</span></button></div>
											<!-- 레이어 : 테두리스타일 -->
											<div class="se2_layer_b_style husky_se2m_table_border_style_layer" style="display:none">
												<ul>
												<li><button type="button" class="se2_b_style1"><span class="se2m_no_border">테두리없음</span></button></li>
												<li><button type="button" class="se2_b_style2"><span><span>테두리스타일2</span></span></button></li>
												<li><button type="button" class="se2_b_style3"><span><span>테두리스타일3</span></span></button></li>
												<li><button type="button" class="se2_b_style4"><span><span>테두리스타일4</span></span></button></li>
												<li><button type="button" class="se2_b_style5"><span><span>테두리스타일5</span></span></button></li>
												<li><button type="button" class="se2_b_style6"><span><span>테두리스타일6</span></span></button></li>
												<li><button type="button" class="se2_b_style7"><span><span>테두리스타일7</span></span></button></li>
												</ul>
											</div>
											<!--레이어 : 테두리스타일 -->
										</dd>
										</dl>
										<dl class="se2_t_proper1_1 se2_t_proper1_2">
										<dt><label for="se2_b_width">테두리두께</label></dt>
										<dd><input id="se2_b_width" name="" type="text" maxlength="2" value="1" class="input_ty1">
											<button type="button" title="1px 더하기" class="se2_add se2m_incBorder"><span>1px 더하기</span></button>
											<button type="button" title="1px 빼기" class="se2_del se2m_decBorder"><span>1px 빼기</span></button>
										</dd>
										</dl>
										<dl class="se2_t_proper1_1 se2_t_proper1_3">
										<dt><label for="se2_b_color">테두리색</label></dt>
										<dd><input id="se2_b_color" name="" type="text" maxlength="7" value="#cccccc" class="input_ty3"><span class="se2_pre_color"><button type="button" style="background:#cccccc;"><span>색찾기</span></button></span>
										<!-- 레이어 : 테두리색 -->
											<div class="se2_layer se2_b_t_b1" style="clear:both;display:none;position:absolute;top:20px;left:-147px;">
												<div class="se2_in_layer husky_se2m_table_border_color_pallet">
												</div>
											</div>
										<!--레이어 : 테두리색-->
										</dd>
										</dl>
										<div class="se2_t_dim0"></div><!-- 테두리 없음일때 딤드레이어 -->
										<dl class="se2_t_proper1_1 se2_t_proper1_4">
										<dt><label for="se2_cellbg">셀 배경색</label></dt>
										<dd><input id="se2_cellbg" name="" type="text" maxlength="7" value="#ffffff" class="input_ty3"><span class="se2_pre_color"><button type="button" style="background:#ffffff;"><span>색찾기</span></button></span>
										<!-- 레이어 : 셀배경색 -->
										<div class="se2_layer se2_b_t_b1" style="clear:both;display:none;position:absolute;top:20px;left:-147px;">
											<div class="se2_in_layer husky_se2m_table_bgcolor_pallet">
											</div>
										</div>
										<!--레이어 : 셀배경색-->
										</dd>
										</dl>
									</dd>
									</dl>
								</fieldset>
								<fieldset>
									<legend>표스타일</legend>
									<dl class="se2_t_proper2">
									<dt><input type="radio" id="se2_tbp2" name="se2_tbp"><label for="se2_tbp2">스타일 선택</label></dt>
									<dd><div class="se2_select_ty2"><span class="se2_t_style1 husky_se2m_table_style_preview"></span><button type="button" title="더보기" class="se2_view_more"><span>더보기</span></button></div>
										<!-- 레이어 : 표템플릿선택 -->
										<div class="se2_layer_t_style husky_se2m_table_style_layer" style="display:none">
											<ul class="se2_scroll">
											<li><button type="button" class="se2_t_style1"><span>표스타일1</span></button></li>
											<li><button type="button" class="se2_t_style2"><span>표스타일2</span></button></li>
											<li><button type="button" class="se2_t_style3"><span>표스타일3</span></button></li>
											<li><button type="button" class="se2_t_style4"><span>표스타일4</span></button></li>
											<li><button type="button" class="se2_t_style5"><span>표스타일5</span></button></li>
											<li><button type="button" class="se2_t_style6"><span>표스타일6</span></button></li>
											<li><button type="button" class="se2_t_style7"><span>표스타일7</span></button></li>
											<li><button type="button" class="se2_t_style8"><span>표스타일8</span></button></li>
											<li><button type="button" class="se2_t_style9"><span>표스타일9</span></button></li>
											<li><button type="button" class="se2_t_style10"><span>표스타일10</span></button></li>
											<li><button type="button" class="se2_t_style11"><span>표스타일11</span></button></li>
											<li><button type="button" class="se2_t_style12"><span>표스타일12</span></button></li>
											<li><button type="button" class="se2_t_style13"><span>표스타일13</span></button></li>
											<li><button type="button" class="se2_t_style14"><span>표스타일14</span></button></li>
											<li><button type="button" class="se2_t_style15"><span>표스타일15</span></button></li>
											<li><button type="button" class="se2_t_style16"><span>표스타일16</span></button></li>
											</ul>
										</div>
										<!--레이어 : 표템플릿선택 -->
									</dd>
									</dl>
								</fieldset>
								<p class="se2_btn_area">
									<button type="button" class="se2_apply"><span>적용</span></button><button type="button" class="se2_cancel"><span>취소</span></button>
								</p>
								<!-- 딤드레이어 -->
								<div class="se2_t_dim3"></div>
								<!--딤드레이어 -->
							</div>
						</div>
					</div>
					<!--표 -->
					<!--@lazyload_html-->
				</li>

				<li class="husky_seditor_ui_findAndReplace"><button type="button" title="찾기/바꾸기" class="se2_find"><span class="_buttonRound">찾기/바꾸기</span></button>
					<!--@lazyload_html find_and_replace-->
					<!-- 찾기/바꾸기 -->
					<div class="se2_layer husky_se2m_findAndReplace_layer" style="margin-left:-238px;">
						<div class="se2_in_layer">
							<div class="se2_bx_find_revise">
								<button type="button" title="닫기" class="se2_close husky_se2m_cancel"><span>닫기</span></button>
								<h3>찾기/바꾸기</h3>
								<ul>
								<li class="active"><button type="button" class="se2_tabfind"><span>찾기</span></button></li>
								<li><button type="button" class="se2_tabrevise"><span>바꾸기</span></button></li>
								</ul>
								<div class="se2_in_bx_find husky_se2m_find_ui" style="display:block">
									<dl>
									<dt><label for="find_word">찾을단어</label></dt><dd><input type="text" id="find_word" value="스마트에디터" class="input_ty1"></dd>
									</dl>
									<p class="se2_find_btns">
										<button type="button" class="se2_find_next husky_se2m_find_next"><span>다음 찾기</span></button><button type="button" class="se2_cancel husky_se2m_cancel"><span>취소</span></button>
									</p>
								</div>
								<div class="se2_in_bx_revise husky_se2m_replace_ui" style="display:none">
									<dl>
									<dt><label for="find_word2">찾을단어</label></dt><dd><input type="text" id="find_word2" value="스마트에디터" class="input_ty1"></dd>
									<dt><label for="revise_word">바꿀단어</label></dt><dd><input type="text" id="revise_word" value="스마트에디터" class="input_ty1"></dd>
									</dl>
									<p class="se2_find_btns">
										<button type="button" class="se2_find_next2 husky_se2m_replace_find_next"><span>다음찾기</span></button><button type="button" class="se2_revise1 husky_se2m_replace"><span>바꾸기</span></button><button type="button" class="se2_revise2 husky_se2m_replace_all"><span>모두 바꾸기</span></button><button type="button" class="se2_cancel husky_se2m_cancel"><span>취소</span></button>
									</p>
								</div>
								<button type="button" title="닫기" class="se2_close husky_se2m_cancel"><span>닫기</span></button>
							</div>
						</div>
					</div>
					<!--찾기/바꾸기 -->
					<!--@lazyload_html-->
				</li>
</ul>

				<?php if ($editor_code) { ?>
				<ul class="se2_multy">
					<li>
						<div id="se2_uploader" style="width:48px; height:29px;"></div>
					</li>
				</ul>
				<?php } ?>

			<!--
				<ul class="se2_font_type"></ul>
				<ul></ul>
			-->
			</div>
			<!--704이상 -->
		</div>

		<hr>
		<!-- 입력 -->
		<div class="se2_input_area husky_seditor_editing_area_container">


			<iframe src="about:blank" id="se2_iframe" name="se2_iframe" class="se2_input_wysiwyg" width="400" height="300" title="글쓰기 영역 : 글쓰기 영역에서 빠져 나오시려면 Shift+ESC키를 누르세요" frameborder="0" style="display:block;"></iframe>
			<textarea name="" rows="10" cols="100" title="HTML 편집 모드" class="se2_input_syntax se2_input_htmlsrc" style="display:none;outline-style:none;resize:none"> </textarea>
			<textarea name="" rows="10" cols="100" title="TEXT 편집 모드" class="se2_input_syntax se2_input_text" style="display:none;outline-style:none;resize:none;"> </textarea>

			<!-- 입력창 조절 안내 레이어 -->
			<div class="ly_controller husky_seditor_resize_notice" style="z-index:20;display:none;">
				<p>아래 영역을 드래그하여 입력창 크기를 조절할 수 있습니다.</p>
				<button type="button" title="닫기" class="bt_clse"><span>닫기</span></button>
				<span class="ic_arr"></span>
			</div>
			<!--입력창 조절 안내 레이어 -->
						<div class="quick_wrap">
				<!-- 표/글양식 간단편집기 -->
				<!--@lazyload_html qe_table-->
				<div class="q_table_wrap" style="z-index: 150;">
				<button class="_fold se2_qmax q_open_table_full" style="position:absolute; display:none;top:340px;left:210px;z-index:30;" title="최대화" type="button"><span>퀵에디터최대화</span></button>
				<div class="_full se2_qeditor se2_table_set" style="position:absolute;display:none;top:135px;left:661px;z-index:30;">
					<div class="se2_qbar q_dragable"><span class="se2_qmini"><button title="최소화" class="q_open_table_fold"><span>퀵에디터최소화</span></button></span></div>
					<div class="se2_qbody0">
						<div class="se2_qbody">
							<dl class="se2_qe1">
							<dt>삽입</dt><dd><button class="se2_addrow" title="행삽입" type="button"><span>행삽입</span></button><button class="se2_addcol" title="열삽입" type="button"><span>열삽입</span></button></dd>
							<dt>분할</dt><dd><button class="se2_seprow" title="행분할" type="button"><span>행분할</span></button><button class="se2_sepcol" title="열분할" type="button"><span>열분할</span></button></dd>

							<dt>삭제</dt><dd><button class="se2_delrow" title="행삭제" type="button"><span>행삭제</span></button><button class="se2_delcol" title="열삭제" type="button"><span>열삭제</span></button></dd>
							<dt>병합</dt><dd><button class="se2_merrow" title="행병합" type="button"><span>행병합</span></button></dd>
							</dl>
							<div class="se2_qe2 se2_qe2_3"> <!-- 테이블 퀵에디터의 경우만,  se2_qe2_3제거 -->
								<!-- 샐배경색 -->
								<dl class="se2_qe2_1">

								<dt><input type="radio" checked="checked" name="se2_tbp3" id="se2_cellbg2" class="husky_se2m_radio_bgc"><label for="se2_cellbg2">셀 배경색</label></dt>
								<dd><span class="se2_pre_color"><button style="background: none repeat scroll 0% 0% rgb(255, 255, 255);" type="button" class="husky_se2m_table_qe_bgcolor_btn"><span>색찾기</span></button></span>
									<!-- layer:셀배경색 -->
									<div style="display:none;position:absolute;top:20px;left:0px;" class="se2_layer se2_b_t_b1">
										<div class="se2_in_layer husky_se2m_tbl_qe_bg_paletteHolder">
										</div>
									</div>
									<!--layer:셀배경색-->

								</dd>
								</dl>
								<!--샐배경색 -->
								<!-- 배경이미지선택 -->
								<dl style="display: none;" class="se2_qe2_2 husky_se2m_tbl_qe_review_bg">
								<dt><input type="radio" name="se2_tbp3" id="se2_cellbg3" class="husky_se2m_radio_bgimg"><label for="se2_cellbg3">이미지</label></dt>
								<dd><span class="se2_pre_bgimg"><button class="husky_se2m_table_qe_bgimage_btn se2_cellimg0" type="button"><span>배경이미지선택</span></button></span>
									<!-- layer:배경이미지선택 -->
									<div style="display:none;position:absolute;top:20px;left:-155px;" class="se2_layer se2_b_t_b1">
										<div class="se2_in_layer husky_se2m_tbl_qe_bg_img_paletteHolder">
											<ul class="se2_cellimg_set">
											<li><button class="se2_cellimg0" type="button"><span>배경없음</span></button></li>
											<li><button class="se2_cellimg1" type="button"><span>배경1</span></button></li>
											<li><button class="se2_cellimg2" type="button"><span>배경2</span></button></li>
											<li><button class="se2_cellimg3" type="button"><span>배경3</span></button></li>
											<li><button class="se2_cellimg4" type="button"><span>배경4</span></button></li>
											<li><button class="se2_cellimg5" type="button"><span>배경5</span></button></li>
											<li><button class="se2_cellimg6" type="button"><span>배경6</span></button></li>
											<li><button class="se2_cellimg7" type="button"><span>배경7</span></button></li>
											<li><button class="se2_cellimg8" type="button"><span>배경8</span></button></li>
											<li><button class="se2_cellimg9" type="button"><span>배경9</span></button></li>
											<li><button class="se2_cellimg10" type="button"><span>배경10</span></button></li>
											<li><button class="se2_cellimg11" type="button"><span>배경11</span></button></li>
											<li><button class="se2_cellimg12" type="button"><span>배경12</span></button></li>
											<li><button class="se2_cellimg13" type="button"><span>배경13</span></button></li>
											<li><button class="se2_cellimg14" type="button"><span>배경14</span></button></li>
											<li><button class="se2_cellimg15" type="button"><span>배경15</span></button></li>
											<li><button class="se2_cellimg16" type="button"><span>배경16</span></button></li>
											<li><button class="se2_cellimg17" type="button"><span>배경17</span></button></li>
											<li><button class="se2_cellimg18" type="button"><span>배경18</span></button></li>
											<li><button class="se2_cellimg19" type="button"><span>배경19</span></button></li>
											<li><button class="se2_cellimg20" type="button"><span>배경20</span></button></li>
											<li><button class="se2_cellimg21" type="button"><span>배경21</span></button></li>
											<li><button class="se2_cellimg22" type="button"><span>배경22</span></button></li>
											<li><button class="se2_cellimg23" type="button"><span>배경23</span></button></li>
											<li><button class="se2_cellimg24" type="button"><span>배경24</span></button></li>
											<li><button class="se2_cellimg25" type="button"><span>배경25</span></button></li>
											<li><button class="se2_cellimg26" type="button"><span>배경26</span></button></li>
											<li><button class="se2_cellimg27" type="button"><span>배경27</span></button></li>
											<li><button class="se2_cellimg28" type="button"><span>배경28</span></button></li>
											<li><button class="se2_cellimg29" type="button"><span>배경29</span></button></li>
											<li><button class="se2_cellimg30" type="button"><span>배경30</span></button></li>
											<li><button class="se2_cellimg31" type="button"><span>배경31</span></button></li>
											</ul>
										</div>
									</div>
									<!--layer:배경이미지선택-->
								</dd>
								</dl>
								<!--배경이미지선택 -->
							</div>
							<dl style="display: block;" class="se2_qe3 se2_t_proper2">
							<dt><input type="radio" name="se2_tbp3" id="se2_tbp4" class="husky_se2m_radio_template"><label for="se2_tbp4">표 스타일</label></dt>
							<dd>
								<div class="se2_qe3_table">
								<div class="se2_select_ty2"><span class="se2_t_style1"></span><button class="se2_view_more husky_se2m_template_more" title="더보기" type="button"><span>더보기</span></button></div>
								<!-- layer:표스타일 -->
								<div style="display:none;top:33px;left:0;margin:0;" class="se2_layer_t_style">
									<ul>
									<li><button class="se2_t_style1" type="button"><span>표 스타일1</span></button></li>
									<li><button class="se2_t_style2" type="button"><span>표 스타일2</span></button></li>
									<li><button class="se2_t_style3" type="button"><span>표 스타일3</span></button></li>
									<li><button class="se2_t_style4" type="button"><span>표 스타일4</span></button></li>
									<li><button class="se2_t_style5" type="button"><span>표 스타일5</span></button></li>
									<li><button class="se2_t_style6" type="button"><span>표 스타일6</span></button></li>
									<li><button class="se2_t_style7" type="button"><span>표 스타일7</span></button></li>
									<li><button class="se2_t_style8" type="button"><span>표 스타일8</span></button></li>
									<li><button class="se2_t_style9" type="button"><span>표 스타일9</span></button></li>
									<li><button class="se2_t_style10" type="button"><span>표 스타일10</span></button></li>
									<li><button class="se2_t_style11" type="button"><span>표 스타일11</span></button></li>
									<li><button class="se2_t_style12" type="button"><span>표 스타일12</span></button></li>
									<li><button class="se2_t_style13" type="button"><span>표 스타일13</span></button></li>
									<li><button class="se2_t_style14" type="button"><span>표 스타일14</span></button></li>
									<li><button class="se2_t_style15" type="button"><span>표 스타일15</span></button></li>
									<li><button class="se2_t_style16" type="button"><span>표 스타일16</span></button></li>
									</ul>
								</div>
								<!--layer:표스타일 -->
								</div>
							</dd>
							</dl>
							<div style="display:none" class="se2_btn_area">
								<button class="se2_btn_save" type="button"><span>My 리뷰저장</span></button>
							</div>
							<div class="se2_qdim0 husky_se2m_tbl_qe_dim1"></div>
							<div class="se2_qdim4 husky_se2m_tbl_qe_dim2"></div>
							<div class="se2_qdim6c husky_se2m_tbl_qe_dim_del_col"></div>
							<div class="se2_qdim6r husky_se2m_tbl_qe_dim_del_row"></div>
						</div>
					</div>
				</div>
				</div>
				<!--@lazyload_html-->
				<!--표/글양식 간단편집기 -->
				<!-- 이미지 간단편집기 -->
				<!--@lazyload_html qe_image-->
				<div class="q_img_wrap">
					<button class="_fold se2_qmax q_open_img_full" style="position:absolute;display:none;top:240px;left:210px;z-index:30;" title="최대화" type="button"><span>퀵에디터최대화</span></button>
					<div class="_full se2_qeditor se2_table_set" style="position:absolute;display:none;top:140px;left:450px;z-index:30;">
						<div class="se2_qbar  q_dragable"><span class="se2_qmini"><button title="최소화" class="q_open_img_fold"><span>퀵에디터최소화</span></button></span></div>
						<div class="se2_qbody0">
							<div class="se2_qbody">
								<div class="se2_qe10">
									<label for="se2_swidth">가로</label><input type="text" class="input_ty1 widthimg" name="" id="se2_swidth" value="1024"><label class="se2_sheight" for="se2_sheight">세로</label><input type="text" class="input_ty1 heightimg" name="" id="se2_sheight" value="768"><button class="se2_sreset" type="button"><span>초기화</span></button>
									<div class="se2_qe10_1"><input type="checkbox" name="" class="se2_srate" id="se2_srate"><label for="se2_srate">가로 세로 비율 유지</label></div>
								</div>
								<div class="se2_qe11">
									<dl class="se2_qe11_1">
									<dt><label for="se2_b_width2">테두리두께</label></dt>
										<dd class="se2_numberStepper"><input type="text" class="input_ty1 input bordersize" value="1" maxlength="2" name="" id="se2_b_width2" readonly="readonly">
										<button class="se2_add plus" type="button"><span>1px 더하기</span></button>
										<button class="se2_del minus" type="button"><span>1px 빼기</span></button>
									</dd>
									</dl>

									<dl class="se2_qe11_2">
									<dt>테두리 색</dt>
									<dd><span class="se2_pre_color"><button style="background:#000000;" type="button" class="husky_se2m_img_qe_bgcolor_btn"><span>색찾기</span></button></span>
										<!-- layer:테두리 색 -->
										<div style="display:none;position:absolute;top:20px;left:-209px;" class="se2_layer se2_b_t_b1">
											<div class="se2_in_layer husky_se2m_img_qe_bg_paletteHolder">
											</div>
										</div>
										<!--layer:테두리 색 -->
									</dd>
									</dl>
								</div>
								<dl class="se2_qe12">
								<dt>정렬</dt>
								<dd><button title="정렬없음" class="se2_align0" type="button"><span>정렬없음</span></button><button title="좌측정렬" class="se2_align1 left" type="button"><span>좌측정렬</span></button><button title="우측정렬" class="se2_align2 right" type="button"><span>우측정렬</span></button>
								</dd>
								</dl>
								<button class="se2_highedit" type="button"><span>고급편집</span></button>
								<div class="se2_qdim0"></div>
							</div>
						</div>
					</div>
				</div>
				<!--@lazyload_html-->
				<!-- 이미지 간단편집기 -->
			</div>
		</div>
		<!--입력 -->
		<!-- 입력창조절/ 모드전환 -->
		<div class="se2_conversion_mode">
			<button type="button" class="se2_inputarea_controller husky_seditor_editingArea_verticalResizer" title="입력창 크기 조절" <?php if ($contentId == 'contentField') { ?>style="display:none"<?php } ?>><span>입력창 크기 조절</span></button>
			<ul class="se2_converter">
			<li class="active"><button type="button" class="se2_to_editor"><span>Editor</span></button></li>
			<li><button type="button" class="se2_to_html"><span>HTML</span></button></li>
			<li style="display:none"><button type="button" class="se2_to_text"><span>TEXT</span></button></li>
			</ul>
		</div>
		<!--입력창조절/ 모드전환 -->
		<hr>

	</div>
	<?php if ($editor_code) { ?>
	<div id="fileProgressBar" style="position:absolute;height:20px;"></div>
	<div id="se2_img_preview"><iframe name="se2_img_preview_frm" id="se2_img_preview_frm" src="exec.php?exec_file=smartEditor/upload/preview.php&editor_code=<?=$editor_code?>&contentId=<?=$contentId?>" border="no" frameborder="no" noresize width="100%" height="95px" /></iframe></div>
	<?php } ?>
</div>
<!-- SE2 Markup End -->
</body>
</html>