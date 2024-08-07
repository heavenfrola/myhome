<?PHP

	printAjaxHeader();

	if(!isTable($tbl['bank_customer'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['bank_customer']);
	}

	$total_count = $pdo->row("select count(*) from $tbl[bank_customer]");

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;z-index:1001;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">입금은행 추가</div>
	</div>
	<div id="popupContentArea">
		<div class='add_account'>
			<div class='list_info'>
				<p>은행코드는 금융결제원 금융회사 공동코드 자료를 통해 확인할 수 있으며, 반드시 기관명에 맞는 은행코드를 입력해주시길 바랍니다. <a href='http://www.kftc.or.kr/kftc/data/EgovBankListMove.do' target='_blank'>바로가기</a></p>
			</div>
			<div class='info'>
				<input type='text' id='bank' name='bank' value='' class='input' placeholder='기관명'><input type='text' id='code' name='code' value='' class='input' placeholder='은행코드'>
			</div>
			<div class='btn'>
				<span class='box_btn blue'><input type='button' value='확인' onclick="bankAdd();"></span>
				<span class='box_btn gray'><input type='button' value='닫기' onclick="ordbank.close();removeDimmed();"></span>
			</div>
			<div class='list' id='banklistdiv'>
				<ul id="banklist">
					<?php
					$res = $pdo->iterator("select * from $tbl[bank_customer] order by no");
                    foreach ($res as $data) {
						$bank = $data['bank'];
						$code = $data['code'];
						$bcno = $data['no'];
						include $engine_dir."/_manage/order/order_bank.exe.php";
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
<?if($total_count==0) {?>
$('#banklistdiv').hide();
<?}?>
	function bankAdd() {
		var bank = $("#bank").val();
		var code = $("#code").val();

		if(!bank) {
			alert("기관명을 입력해주시기 바랍니다.");
			return false;
		}
		if(!code) {
			alert("은행코드를 입력해주시기 바랍니다.");
			return false;
		}

		if($("#banklistdiv").css("display") == "none"){
			$('#banklistdiv').show();
		}

		$.ajax({
			type : 'POST',
			url : './?body=order@order_bank.exe',
			data: '&exec=insert&bank='+bank+'&code='+code,
			dataType : 'html',
			success : function(result) {
				if(result=='pass') {
					alert("동일한 코드가 있습니다.");
					return false;
				}
				$("#banklist").append(result);
                var option = $("<option value='"+bank+"'>"+bank+"</option>");
                $("select[name=bank]").append(option);
				$('#bank').val('');
				$('#code').val('');
			}
		});
	}

	function bankDelete(no, bank) {
		if(!confirm("삭제하시겠습니까?")) return false;
		$.post('?body=order@order_bank.exe', {"no":no, "exec":"delete"}, function(data) {
			$(".repay_method option").filter(function() {
				if(this.text==bank) {
					this.remove();
				}
			})
		})
		$('#banklist #'+no).remove();
		if($('#banklist li').size()==0) {
			$('#banklistdiv').hide();
		}
	}
</script>