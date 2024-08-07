<?PHP

	printAjaxHeader();

	$pgrp_no = numberOnly($_GET['pgrp_no']);
	if($pgrp_no) {
		$pgdata = $pdo->assoc("select * from $tbl[promotion_pgrp_list] where no='$pgrp_no'");
	}

?>
<div id="popupContent" class="popupContent layerPop pop1" style="width:900px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">프로모션 상품그룹 등록</div>
	</div>
	<form name="prmgFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" enctype="multipart/form-data">
		<input type="hidden" id="list_yn" name="list_yn" value="<?=$_GET['list_yn']?>">
		<div id="popupContentArea">
			<input type="hidden" id="gno" name="gno" value="<?=$pgrp_no?>">
			<div>
				<div class="box_title first hidden">
					<h3 class="title">프로모션 상품그룹 등록</h3>
				</div>
				<table class="tbl_row">
					<caption class="hidden">프로모션 상품그룹 등록</caption>
					<colgroup>
						<col style="width:18%">
						<col>
						<col style="width:18%">
						<col>
					</colgroup>
					<tr>
						<th scope="row"><strong>프로모션 상품그룹명</strong></th>
						<td><input type="text" class="input" id="pgrp_nm" name="pgrp_nm" value="<?=stripslashes($pgdata['pgrp_nm'])?>"></td>
						<th scope="row">띠배너 명(PC/Mobile)</th>
						<td><input type="text" class="input" name="banner_text" value="<?=stripslashes($pgdata['banner_text'])?>"></td>
					</tr>
					<tr>
						<th scope="row">띠배너 이미지(PC)</th>
						<td class="pgrp_banner">
                             <input type="file" id="upfile1" name="upfile1" value="<?=$pgdata['upfile1']?>" style="width: 100%">
							<?=delImgStr($pgdata,1)?>
						</td>
						<th scope="row">띠배너 이미지(Mobile)</th>
						<td class="pgrp_banner">
                            <input type="file" id="upfile2" name="upfile2" value="<?=$pgdata['upfile2']?>" style="width: 100%">
							<?=delImgStr($pgdata,2)?>
						</td>
					</tr>
                    <tr>
                        <th scope="row">링크</th>
                        <td>
                            <select name="link_type">
                                <option value="1" <?=checked($pgdata['link_type'], '1', true)?>>URL</option>
                                <option value="2" <?=checked($pgdata['link_type'], '2', true)?>>상품</option>
                                <option value="3" <?=checked($pgdata['link_type'], '3', true)?>>분류</option>
                            </select>
                            <input type="text" name="link" value="<?=htmlspecialchars($pgdata['link']);?>" class="input" size="15">
                            <span id="prdpop" style="display:<?=($pgdata['link_type'] == '2') ? "inline" : "none";?>">
                                <div class="box_btn_s" style="display:inline;"><input type="button" value="상품선택" onClick="psearch.open(null, {'name': 'prdbx', 'topmargin': +30, 'leftmargin': -230});"></div>
                            </span>
                        </td>
                        <th scope="row">링크타겟</th>
                        <td>
                             <select name="target">
                                <option value="" <?=checked($pgdata['target'], '',  true)?>>같은 창</option>
                                <option value="_blank" <?=checked($pgdata['target'], '_blank', true)?>>새창</option>
                                <option value="_parent" <?=checked($pgdata['target'], '_parent', true)?>>부모</option>
                                <option value="_top" <?=checked($pgdata['target'], '_top', true)?>>최상</option>
                            </select>
                        </td>
                    </tr>
				</table>
			</div>
			<div class="box_title first" style="margin-top:20px">
				<h3 class="title">프로모션 상품그룹 진열 설정</h3>
				<select id="orderby" name="orderby" onchange="prm_sort_submit('', '', this.value);" style="position:absolute; right:10px; top:10px;">
					<option value="" <?=checked($orderby,'')?>>::정렬선택::</option>
					<option value="1" <?=checked($orderby,1)?>>높은가격순</option>
					<option value="2" <?=checked($orderby,2)?>>낮은가격순</option>
					<option value="3" <?=checked($orderby,3)?>>판매량높은순</option>
					<option value="4" <?=checked($orderby,4)?>>판매량낮은순</option>
					<option value="5" <?=checked($orderby,5)?>>조회수높은순</option>
					<option value="6" <?=checked($orderby,6)?>>조회수낮은순</option>
				</select>
			</div>
			<div id="sort_list" style="max-height: 270px; overflow-y: auto;">
				<?include_once $engine_dir."/_manage/product/promotion_group.exe.php";?>
			</div>
		</div>
		<div class="box_middle2 left">
			<div>
				<select id="set_prd" name="set_prd">
					<option value="1" <?=checked($set_prd,'1')?>>선택한 상품의</option>
					<option value="2" <?=checked($set_prd,'2')?>>전체 상품의</option>
				</select> 상태를
				<select id="prd_stat" name="prd_stat">
					<option value="">::상태::</option>
					<option value="4" <?=checked($up_prd_stat,4)?>>숨김</option>
					<option value="2" <?=checked($up_prd_stat,2)?>>정상</option>
					<option value="3" <?=checked($up_prd_stat,3)?>>품절</option>
				</select>
				로
				<span class="box_btn_s blue"><input type="button" value="변경" onclick="prd_stat_update(this.value);"></span>
			</div>
		</div>
		<div class="box_middle2 left">
			<span class="box_btn"><input type="button" value="선택삭제" onclick="cancelprmSort()"></span>
			<span class="box_btn"><input type="button" value="전체삭제" onclick="cancelprmSort('all')"></span>
			<div class="right_area">
				<span class="box_btn blue"><input type="button" value="상품 선택" onclick="prmprdselect(this)"></span>
			</div>
		</div>
		<div class="pop_bottom">
			<span class="box_btn blue"><input type="button" value="등록" onclick="prmg_submit();"></span>
			<span class="box_btn gray"><input type="button" value="취소" onclick="pgsearch.close({'name':'pop1'})"></span>
		</div>
	</form>

    <style>
    .pgrp_banner label {display: inline;}
    .pgrp_banner br {display: none;}
    .pgrp_banner .box_btn_s {margin-bottom: 0 !important}
    </style>

    <script type="text/javascript">
        var prdsearch = new layerWindow('product@promotion_product.frm');
        var srt5 = null;
        prdsearch.psel = function(pno, type) {
            var idx = 1;
            var return_chk = false;
            var remain_cnt = 0;	// 중복된 상품을 제외한 나머지 상품의 개수
            if ($('#confirm_prd tr').size() > 0) {
                var idx = $('#confirm_prd tr:last').data('idx');
                if (type != "Y") {
                    idx++;
                }
                $('#confirm_prd tr').each(function () { // 중복 상품 찾기
                    var trid = $(this).attr('id');
                    if (type != "Y") { //한개씩
                        if (trid == pno) {
                            alert("이미 추가 된 상품입니다.");
                            return_chk = true;
                            return false;
                        }
                    } else {
                        if ($.inArray(trid, pno) != '-1') {
                            pno = pno.filter(value => value != trid);
                            remain_cnt++;
                        }
                    }
                });
            } else {
                if (type == "Y") {
                    idx = 0;
                }
            }
            if (remain_cnt) { // 중복된 상품을 제외한 상품이 존재한다면
                if (!pno.length) {
                    //추가할 수 있는 상품이 없다면 얼럿
                    alert("이미 추가 된 상품을 제외 후 다시 시도해주세요.");
                    return false;
                } else {
                    alert("이미 추가된 상품을 제외한 " + pno.length + " 개의 상품이 추가되었습니다.");
                }
            }
            if (return_chk == true) {
                return false;
            }
            $.get('?body=product@promotion_product_add.exe', {"pno": pno, "idx": idx}, function (data) {
                $('#confirm_prd').append(data);
                srt5 = new Sorttbl('prm_product_list');
            })
        }
        prdsearch.pcan = function(pno) {
            $('#prm_product_list #'+pno).remove();
            unCheckcb();
        }
        function unCheckcb() {
            // 상품등록리스트에서 제외 시 체크박스 해제
            const checkbox = document.getElementById('cball_prd_add');
            checkbox.checked = false;
        }
        function prmprdselect(obj) {
            var pno = $('#prm_sort').find('#pno').val();

            if($('#prm_sort tbody tr').size()>0) {
                var sortingArray = [];
                _pno = pno.split("|");
                _pno.forEach(function(val) {
                    sortingArray.push(val);
                })
            }
            if(sortingArray) {
                sparam = '?pno='+sortingArray+'&prm_sort=Y';
            }else {
                sparam = "";
            }

            prdsearch.input = obj;
            prdsearch.open(sparam, {"name":"pop2", "topmargin":0, "leftmargin":-650});
        }
        function prm_sort_submit(row, page, ordby) {
            var f = document.prmgFrm;

            pno = f.pno.value;
            if(pno) {
                _pno = pno.split('|');
            }

            if(!ordby) {
                ordby = $('#orderby').val();
            }
            $.get('?body=product@promotion_group.exe', {"pno":_pno, "page":page, "orderby":ordby}, function(data) {
                $('#sort_list').html(data);
            })
        }
        var srt2 = null;
        function prmg_submit() {
            var fdata =  $("form[name=prmgFrm]").serialize();
            var formData = new FormData();
            var list_yn = $('#list_yn').val();
            var pgrp_nm = $('#pgrp_nm').val();

            if(!pgrp_nm) {
                alert("상품그룹명을 입력해주세요.");
                return false;
            }

            formData.append("upfile1",$('#upfile1')[0].files[0]);
            formData.append("upfile2",$('#upfile2')[0].files[0]);

            $.ajax({
                url: './?body=product@promotion_product.exe&'+fdata,
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(gno) {
                    var pno_this = '';
                    if(list_yn=="Y") {
                        pgsearch.close({"name":"pop1"});
                        window.location.reload();
                    }else {
                        if($('#prd_add_list tr').size()>0) {
                            $('#prd_add_list tr').each(function() {
                                var pno = $(this).attr('id');
                                if(pno==gno) {
                                    pno_this = $(this);
                                }
                            })
                        }
                        $.get('?body=product@promotion_register_add.exe', {"pgrp_no":gno}, function(data) {
                            if(pno_this) {
                                pno_this.after($(data));
                                pno_this.remove();
                            }else {
                                if($('#prd_add_list').size()>0) {
                                    $('#prd_add_list').append(data);
                                }
                            }
                            srt2 = new Sorttbl('groupFrm');
                            pgsearch.close({"name":"pop1"});
                            $("#ui-datepicker-div").remove();
                            setDatepicker();
                        })
                    }
                }
            });
        }
        function prd_stat_update() {
            var set_prd = $('#set_prd').val();
            var stat = $('#prd_stat').val();
            var f = document.prmgFrm;
            var _pno = [];

            if(!stat) return;

            pno = f.pno.value;
            if(pno) {
                _pno = pno.split('|');
                _spno = pno.split('|');
            }
            if(set_prd==1) {
                if($('#prm_sort tbody tr.checked').size()>0) {
                    var _spno = [];
                    $('#prm_sort tbody tr.checked').each(function() {
                        var pno = $(this).attr('id');
                        if( !pno ) return;
                        _spno.push(pno);
                    });
                }else {
                    alert("수정할 상품을 선택해주세요.");
                    return false;
                }
            }
            $.post('?body=product@promotion_register.exe', {"exec":"up_stat", "pno":_spno, "stat":stat}, function(data) {
                $.get('?body=product@promotion_group.exe', {"pno":_pno}, function(data) {
                    $('#sort_list').html(data);
                })
            })
        }

        function cancelprmSort(type) {
            var prdArray = [];
            var cancelArray = [];
            var f = document.prmgFrm;
            var pno = f.pno.value;

            if(type=='all') {
                f.pno.value = "";
                $('#sort_list').html("");
            }else {
                $('#prm_sort tr.checked').each(function() {
                    var pno2 = $(this).attr('id');
                    if(!pno2) return;
                    cancelArray.push(pno2);
                });

                if(pno) {
                    _pno = pno.split("|");
                    _pno.forEach(function(val) {
                        if($.inArray(val, cancelArray) == -1) {
                            prdArray.push(val);
                        }else {
                            $('#prm_sort #'+val).remove();
                        }
                    })
                    f.pno.value = prdArray.join("|");
                    $.get('?body=product@promotion_group.exe', {"pno":prdArray}, function(data) {
                        $('#sort_list').html(data);
                    })
                }
            }
        }

        var psearch = new layerWindow('product@product_inc.exe');
        psearch.psel = function(pno)
        {
            $('input[name="link"]').val(pno);
            this.pop.fadeOut(function() {
                this.remove();
            });
        }
        psearch.close = function() {
            $('.'+this.opt.name).fadeOut('fast', function() {
        		$(this).remove();
        		$('body').off('keyup');
        	});
        }

        $('select[name=link_type]').on('change', function() {
            if (this.value == '2') $('#prdpop').show();
            else $('#prdpop').hide();
        });
    </script>
</div>