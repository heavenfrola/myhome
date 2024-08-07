<?PHP
/**
 * [매장지도] 등록
 */

printAjaxHeader();

$no = numberOnly($_GET['no']);
if($no) {
	$subject = "매장 수정";
	$data = get_info($tbl['store_location'], "no", $no);
} else {
	$subject = "매장 추가";
	$data['hidden'] = 'N';
	$data['break_use'] = 'N';
	$data['otype'] = 'A';
}

//시설안내
$fsql = "select sort, name, no from {$tbl['store_facility_set']} where 1 order by sort asc";
$fres = $pdo->iterator($fsql);
$_store_facility_arr = array();
foreach($fres as $fd) {
	$_store_facility_arr[$fd['no']] = $fd['name'];
}
if($data['facility']) $data['facility'] = explode("@",$data['facility']);
?>

<form name="sLocationFrm" method="post" action="?" target="hidden<?php echo $now; ?>" onsubmit="return storeLocationCheck();" enctype="multipart/form-data">
    <input type="hidden" name="body" value="config@store_location.exe">
    <input type="hidden" name="no" value="<?php echo $data['no']; ?>">
    <input type="hidden" name="exec" value="register">

    <div class="box_title first">
        <h2 class="title"><?php echo $subject; ?></h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden"> <?php echo $subject; ?></caption>
        <colgroup>
            <col style="width:16%">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">상태
            </th>
            <td>
           <?php echo selectArray($_store_config_stat, 'stat', '2','선택', $data['stat']);?>
                <ul class="list_info tp">
                    <li>매장의 현재 운영 상태에 따라 <strong>정상</strong> / <strong>휴업</strong> / <strong>폐업</strong> 중 하나를 선택합니다.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th scope="row"><strong>상호명</strong></th>
            <td>
                <input type="text" name="title" value="<?php echo $data['title']; ?>" class="input input_full" placeholder="위사">
            </td>
        </tr>
        <tr>
            <th scope="row"><strong>대표자명</strong></th>
            <td>
                <input type="text" name="owner" value="<?php echo $data['owner']; ?>" class="input" placeholder="홍길동">
            </td>
        </tr>
        <tr>
            <th scope="row"><strong>전화번호</strong></th>
            <td>
                <input type="text" name="phone" value="<?php echo $data['phone']; ?>" class="input" placeholder="021234567">
                <ul class="list_info tp">
                    <li>전화번호를 <strong>'-'</strong> 제외하고 입력해 주세요.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th scope="row">휴대전화번호</th>
            <td>
                <input type="text" name="cell" value="<?php echo $data['cell']; ?>" class="input" placeholder="01012345678">
                <ul class="list_info tp">
                    <li>휴대전화번호를 <strong>'-'</strong> 제외하고 입력해 주세요.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th scope="row">이메일</th>
            <td>
                <input type="text" name="email" value="<?php echo $data['email']; ?>" class="input" size="40" placeholder="wisa@example.com">
            </td>
        </tr>
        <tr>
            <th><strong>주소</strong></th>
            <td>
                <input type="text" name="zipcode" value="<?php echo $data['zipcode'];?>" class="input"  size="10" readonly placeholder="우편번호">
                <span class="box_btn_s"><input type="button" value="우편번호검색" class="btn2" onClick="zipSearchM('sLocationFrm','zipcode','addr1','addr2')"></span><br>
                <input type="text" name="addr1" value="<?php echo $data['addr1'];?>" class="input" size="60" maxlength="50" style="margin:5px 0;" readonly placeholder="도로명 주소"><br>
                <input type="text" name="addr2" value="<?php echo inputText($data['addr2'])?>" class="input" size="60" maxlength="100" placeholder="상세 주소">
            </td>
        </tr>
        <tr>
            <th scope="row">숨김 여부</th>
            <td>
				<?php echo radioNewArray($_store_config_hidden, 'hidden', 2,'',$data['hidden'],''); ?>
                <ul class="list_info tp">
                    <li><strong>숨김</strong> 시, 해당 매장은 목록 및 지도에서 노출되지 않습니다.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th scope="row">시설안내 <a href="./?body=config@store_facility" target="_blank" class="sclink4" tooltip="설정"></a></th>
            <td>
				<?php echo checkNewArray($_store_facility_arr, 'facility[]', $data['facility']); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">매장 설명</th>
            <td>
                <textarea type="text" name="content" class="txta" placeholder="추가적인 매장 정보를 입력해 주세요."><?php echo $data['content'];?></textarea>
            </td>
        </tr>
		<?php
            $_img_arr = array(
                '1'=>'썸네일',
                '2'=>'커버'
               // '3'=>'매장'
            );

		for($i=1; $i<=2; $i++) {
			$_img_name = $_img_arr[$i].' 이미지';
			?>
            <tr>
                <th scope="row"><?php echo $_img_name;?></th>
                <td>
                    <input type="file" name="upfile<?php echo $i;?>" class="input">
					<?php echo delImgStr($data, $i);?>
                    <ul class="list_info tp">
						<?php if($i == 1) {?>
                           <li>지도에서 매장 마커 클릭 시 나타나는 매장 정보창에서 보여지는 이미지 입니다.</li>
                           <li>최대 <strong>1MB</strong> 크기 이하의 <strong>gif, jpg, png</strong> 파일만 가능 합니다.</li>
                           <li>권장 사이즈는 80 x 80 입니다.</li>
                        <?php } ?>
						<?php if($i == 2) {?>
                           <li>매장안내 상세 페이지 최상단에 가장 먼저 보여지는 이미지 입니다.</li>
                           <li>최대 <strong>5MB</strong> 크기 이하의 <strong>gif, jpg, png</strong> 파일만 가능 합니다.</li>
                           <li>권장 사이즈는 600 x 600 입니다.</li>
						<?php } ?>
                    </ul>
                </td>
            </tr>
		<?php } ?>
        </tbody>
    </table>

   <?php /* 아이콘 항목 */?>
    <div class="box_title_reg">
        <h2 class="title">아이콘</h2>
        <a href="./pop.php?body=product@product_icon_list&type=store" onclick="wisaOpen($(this).attr('href')); return false" class="setup btt" tooltip="아이콘 추가관리"></a>
    </div>
    <div class="box_middle2" id="area_prdicon">
		<?php
            $_store_icon_yn = true;
            include $engine_dir."/_manage/product/product_icon_list.exe.php";
        ?>
    </div>
	<?php /* 아이콘 항목 */?>

	<?php /* 영업 시간 */?>
    <div class="box_title_reg">
        <h2 class="title">영업 시간 설정</h2>
    </div>
    <?php include $engine_dir."/_manage/config/store_operate_register.php"; ?>

    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn gray"><input type="button" value="취소" onclick="location.href='?body=config@store_location'"></span>
    </div>
</form>

<script type="text/javascript">
    const f = document.sLocationFrm;
    $(function() {
        weekDisabled();
    });
    /*
    * no : 해당 번호
    * is_add : 해당 개수
    * tappend : 추가 될 부모
    *
    * */
    function addOpt(no,option) {
        let param = {}
        if(option) param = option;

        let seq = (param['seq']) ? param['seq']:'';

        let divs = $('.'+param['append']).find("[id^='"+param['repet']+"']");

        if(param['is_add'] == true) {
            if(divs.length>0) {
                param['is_add'] = divs.last().attr('id').replace(param['repet'], '');
                divs.last().find('.blue').hide();
            } else {
                param['is_add'] = 0;
            }
        }

        param['execmode'] = 'ajax';
        param['break_addmode'] = true;
        param['so_no'] = no;
        param['body'] = 'config@store_operate_break_frm';

        $.post('./index.php', param, function(r) {
           $('.breaktime'+seq).append(r);
           $('#break_use'+seq).hide();
        });
    }

    function optDelete(seq, cellId, ino) {
        let otype = f.otype.value;
        let cellRow = $('#option_items_'+otype).find('.breaktime'+seq).find('#option_row_'+cellId);

        if(!cellRow) return;
        if(!ino) {
            cellRow.remove();
            let pre_cellRow = $('#option_items_'+otype).find('.breaktime'+seq).find('.option_item_row');
            let rowcount = pre_cellRow.length;
            pre_cellRow.eq(rowcount-1).find('.blue').show();

           if(rowcount == 0) {
               $("[name = 'buse["+seq+"]']").eq(0).prop('checked', true);
               $('#break_use'+seq).show();
           }
            return;
        }
    }

    /*
     * append : 추가 할 위치
     * id="wrap1" : 큰 틀
     *
     * */
    function addWeekOpt(option) {
        let otype = f.otype.value;

        let param = {}
        if(option) param = option;

        let divs = $('#'+param['append']).find("[class^='"+param['repet']+"']");
        let all_check = $('.check_wrap').find('input:checkbox:checked');
        console.log(all_check.length);
        if( all_check.length >=7) {
            window.alert("선택할 수 있는 요일이 없습니다.");
            return;
        }

        if(param['is_add'] == true) {

            if(divs.length>0) {
                param['is_add'] = parseInt(divs.last().attr('class').replace(param['repet'], ''))+1;
            } else {
                param['is_add'] = 0;
            }
        }

        param['is_add'] = param['is_add'];
        param['otype'] = otype;
        param['addmode'] = true;
        param['execmode'] = 'ajax';
        param['body'] = 'config@store_operate_register_sub_frm';

        $.post('./index.php', param, function(r) {
            $('#'+param['append']).append(r);
            weekDisabled();
        });
    }

    function delWeekOpt(no,cellId, ino) {
        let otype = f.otype.value;
        let cellRow = $('#option_items_'+otype).find('.td_wrap'+cellId);
        let pre_cellRow = $('#option_items_'+otype).find("[class^='td_wrap']")

        if(!cellRow || pre_cellRow.length ==1) return;

        if(!ino) {
            cellRow.remove();
            weekDisabled();
            return;
        }
    }


    //영업 주기에 따른 영역 선택
    function operateForm(o, option) {
        let type = o.value;
        let sono = f.sono.value;

        let param = {}
        if(option) param = option;

        $('#'+param['append']).empty();
        $.post('./index.php', {'body':'config@store_operate_register_frm', 'execmode':'ajax', 'otype':type, 'sono':sono }, function(r) {
            $('#'+param['append']).append(r);
        });
    }

    function weekDisabled() {
        let otype = f.otype.value;
        let test_arr = [];
        let seq = 0;

        if(otype != 'C') return;
        $('.check_wrap').find('input').each( function() {
            if($(this).is(':checked') == true) test_arr.push($(this).val());
        });

        $('.check_wrap').find('input').each( function() {
            if($.inArray($(this).val(),test_arr ) > -1) {
                if($(this).is(':checked') == true) {
                    $(this).attr('disabled', false);
                } else {
                    $(this).attr('disabled', true);
                }
            } else {
                $(this).attr('disabled', false);
            }
        });
    }
    function storeLocationCheck() {

        return;
    }
</script>