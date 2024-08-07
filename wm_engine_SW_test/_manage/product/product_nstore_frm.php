<?PHP

    use Wing\API\Naver\CommerceAPI;

    require_once __ENGINE_DIR__.'/_config/set.nstore_delivery_codes.php';

    $commerceAPI = new CommerceAPI();

	if($_POST['pno']) { // 세트 읽기
		$pno = numberOnly($_POST['pno']);
		$data = $pdo->assoc("select * from {$tbl['product']} where no='$pno'");
	}

	$_store_tax_arr = array('TAX' => '과세상품', 'DUTYFREE' => '면세상품', 'SMALL' => '영세상품');
	$_store_origin_arr = array( "00"=>"국산", "02"=>"수입산" , '03' => '상세설명에표시');

	$s_data = $pdo->assoc("select * from {$tbl['product_nstore']} where pno='$pno'");
	if(is_array($s_data) == true) {
        // 스마트스토어 사용 중 상품일 경우 상품 정상 등록여부 체크 후 데이터 보정
        if ($data['n_store_check'] && $s_data['product_id']) {
            $channelProduct = $commerceAPI->channelProducts($s_data['product_id']);
            if (!is_object($channelProduct->originProduct)) { // 삭제된 상품
                $data['n_store_check'] = 'N';
                $pdo->query("update {$tbl['product']} set n_store_check='N', nstoreId=0 where no='$pno'");
                $pdo->query("update {$tbl['product_nstore']} set product_id='' where pno='$pno'");
            }
        }

		unset($s_data['no'], $s_data['product_id']);
		$data = array_merge($data, $s_data);
	}
	$n_extra = json_decode($data['extra_datas']);

    $_cate_select = $commerceAPI->makeCategoryData($data);
    if ($_cate_select) {
        // 원산지
        $_origin_select = $commerceAPI->makeOriginData($data);

        // 모델명
        if ($n_extra->n_model) {
            $model = $commerceAPI->productModelsId($n_extra->n_model);
            $selectedModel = '<option value="' . $model->id . '" selected>' . $model->name . '</option>';
        }

        // 인증
        $certification = $n_extra->certificationKindType . '@' . $n_extra->certificationInfoId;

        // 배송지
        $delivery_parcel = array();
        $deliveryInfo = $commerceAPI->sellerAddressbooksForPage();
        foreach ($deliveryInfo->addressBooks as $addr) {
            if ($addr->addressType == 'REFUND_OR_EXCHANGE') { // 반품 배송지
                $delivery_parcel[$addr->addressBookNo] = $addr->name .
                    ' ('. $addr->baseAddress . ' ' . $addr->detailAddress . ')';
            }
        }

        // 정보고시 목록
        $s_res = $pdo->iterator("select * from {$tbl['store_summary']} order by no asc");
        foreach ($s_res as $s_data) $summary_arr[$s_data['no']] = $s_data['title'];

        // 기본 값
        if (!$data['n_store_check']) $data['n_store_check'] = 'N';
        if (!$data['n_infant']) $data['n_infant'] = 'Y';
        if (!$data['n_custom_made']) $data['n_custom_made'] = 'N';
        $data['n_category_depth4'] = $data['n_category_depth'];

        // 배송 택배사 기본 값
        if (isset($data['n_delivery_company']) == false || empty($data['n_delivery_company']) == true) {
            $tmp = $pdo->row("select name from {$tbl['delivery_url']} where partner_no=0 order by sort asc limit 1");
            $data['n_delivery_company'] = array_search(str_replace(' ', '', $tmp), $n_store_delivery_codes);
        }

        // 설정 복사 가능 여부
        $nstore_registed = $pdo->row("select count(*) from {$tbl['product_nstore']} where pno!='$pno'");

?>
<div class="box_title_reg smartstore">
	<h2 class="title">
		네이버 스마트스토어
		<label class="p_cursor"><input type="checkbox" name="n_store_check" value="Y" <?=checked($data['n_store_check'], 'Y')?>> 사용함</label>
	</h2>
	<a href="./?body=config@n_smart_store" target="_blank" class="setup btt" tooltip="설정"></a>
</div>

<table class="tbl_row_reg n_store_table" id="n_store_table" <?php if($data['n_store_check'] != "Y"){?> style="display:none;"<?php } ?>>
	<caption class="hidden">스마트스토어</caption>
	<colgroup>
		<col style="width:134px">
		<col>
	</colgroup>
	<tbody>
		<?php if(empty($data['product_id']) == true && count($nstore_registed) > 0) { ?>
		<tr>
			<th scope="row">설정 복사</th>
			<td>
				<span class="box_btn_s icon copy2"><input type="button" value="가져오기" onclick="nPreset.open();"></span>
			</td>
		</tr>
		<?php } ?>
		<?php if($data['product_id']) { ?>
		<tr>
			<th scope="row">스마트스토어 상품ID</th>
			<td>
				<?=$data['product_id']?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row"><strong>카테고리</strong></th>
			<td>
                <?php
                foreach ($_cate_colname[1] as $k => $v) {
                    echo selectArray(
                        $_cate_select[$v],
                        'n_category_' . $v,
                        false,
                        ':: ' . $k . '차분류 ::',
                        $data['n_category_' . $v],
                        "smartCateInfinite(this, $k)"
                    );
                }
                ?>

				<?php if ($data['n_category_big']) { ?>
				<div class="list_info">
					<p class="warning">수정 시 대분류는 변경할 수 없습니다.</p>
				</div>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>부가세</strong></th>
			<td>
				<select name="n_taxtype">
					<?php foreach($_store_tax_arr as $k => $v) { ?>
					<option value="<?=$k?>" <?=checked($data['n_taxtype'], $k, 1)?>><?=$v?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>원산지</strong></th>
			<td>
                <?php
                foreach ($_cate_colname[1] as $k => $v) { if ($k == 4) break;
                    echo selectArray(
                        $_origin_select[$v],
                        'n_origin_' . $v,
                        false,
                        ':: ' . $k . '차분류 ::',
                        $data['n_origin_' . $v],
                        "smartOriginInfinite(this, $k)"
                    );
                }
                ?>
				<input
                    type="text"
                    name="n_importer"
                    value="<?=inputText($data['n_importer'])?>"
                    placeholder="수입사 입력"
                    class="input"
                    size="30"
                    style="display:none; margin-top: 5px;"
                >
                <div class="origin_content" style="display: none; margin-top: 5px;">
                    <input
                        type="text"
                        name="n_origin_content"
                        class="input"
                        size="30"
                        value="<?=$n_extra->origin_content?>"
                        placeholder="원산지 직접입력"
                    >
                </div>
			</td>
		</tr>
        <tr>
            <th>스토어 전용 상품명</th>
            <td>
                <input type="text" name="n_channelProductName" value="<?=$n_extra->channelProductName?>" class="input input_full">
                <div class="list_info tp">
                    <p class="warring">미입력 시 기존 상품명을 이용합니다.</p>
                </div>
            </td>
        </tr>
        <tr class="sm_model" style="display: none;">
            <th scope="row">모델</th>
            <td>
                <input type="text" class="input" placeholder="검색어를 입력해주세요." onkeyup="getModel(this)">
                <select name="n_model" style="width: 300px">
                    <option value="">:: 모델명 ::</option>
                    <?=$selectedModel?>
                </select>

                <div class="list_info tp">
                    <p class="warring">찾으시는 모델이 없을 경우 더 자세한 검색어를 입력해주세요.</p>
                </div>
            </td>
        </tr>
        <tr>
            <th>태그</th>
            <td>
                <input type="text" name="n_tags" value="<?=$n_extra->tags?>" class="input input_full">
                <div class="list_info tp">
                    <p class="warring">쉼표로 구분해주세요.</p>
                </div>
            </td>
        </tr>
        <tr id="n_attr">
            <th scope="row">
                속성
            </th>
            <td class="n_attr">
                <div v-if="optional > 0" style="margin-bottom: 10px;">
                    <span class="box_btn_s2">
                        <input v-if="primary_only" type="button" value="모든 속성 보기" @click="primary_only = false">
                        <input v-else type="button" value="중요 속성만 보기" @click="primary_only = true">
                    </span>
                </div>

                <div v-if="attributes.length > 0" v-for="attr in attributes">
                    <div v-show="!primary_only || attr.attributeType == 'PRIMARY'">
                        <div :class="{title: true, [attr.attributeType]: true}"><span>{{ attr.attributeName }}</span></div>
                        <ul style="max-height: 150px; overflow-y: auto;">
                            <li v-for="value in attr.values">
                                <label v-else>
                                    <input
                                        :type="getAttrType(attr)"
                                        :name="`n_attr[${attr.attributeSeq}][]`"
                                        :value="value.attributeValueSeq"
                                        :checked="value.checked"
                                        @click="checkMaxMatchingCount(attr, $event)"
                                    >
                                    <span v-if="value.minAttributeValue">
                                        {{ value.minAttributeValue }}
                                        <span v-if="attr.unit && value.attributeValueSeq">{{ attr.unit.unitCodeName }}</span>
                                    </span>
                                    <span v-if="value.maxAttributeValue">
                                         ~
                                        {{ value.maxAttributeValue }}
                                        <span v-if="attr.unit">{{ attr.unit.unitCodeName }}</span>
                                    </span>
                                </label>
                            </li>
                        </ul>
                        <!-- 속성 실제값 입력 -->
                        <div v-if="attr.attributeClassificationType == 'RANGE'" style="margin-bottom: 10px">
                            <input
                                type="text"
                                class="input"
                                :name="`n_attr_v[${attr.attributeSeq}]`"
                                :value="attr.attributeRealValue"
                                placeholder="속성실제값"
                            >
                            <input
                                type="hidden"
                                :name="`n_attr_u[${attr.attributeSeq}]`"
                                :value="attr.unit.id"
                            >
                            {{ attr.unit.unitCodeName }}
                        </div>
                    </div>
                </div>
                <div v-else class="list_info">
                    <p class="warring">카테고리를 선택해주세요.</p>
                </div>
            </td>
        </tr>
        <tr class="sm_certification" style="display: none;">
            <th scope="row">인증</th>
            <td>
                <select name="n_certificationTargetExclude">
                    <option value="N">인증 필요</option>
                    <option value="Y">인증 대상 예외</option>
                </select>
                <select name="n_certificationInfoId" style="width: 300px">
                    <option value="">:: 인증방식 ::</option>
                </select>
                <div style="margin-top: 5px;">
                    <input
                        type="text"
                        name="n_certificationInfoName"
                        value="<?=$n_extra->certificationInfoName?>"
                        class="input"
                        placeholder="인증 기관명"
                    >
                    <input
                        type="text"
                        name="n_certificationNumber"
                        value="<?=$n_extra->certificationNumber?>"
                        class="input"
                        placeholder="인증 번호"
                    >
                </div>
            </td>
        </tr>
		<tr>
			<th scope="row">맞춤제작</th>
			<td>
				<label><input type="radio" name="n_custom_made" value="Y" <?=checked($data['n_custom_made'], 'Y')?>> 가능</label>
				<label><input type="radio" name="n_custom_made" value="N" <?=checked($data['n_custom_made'], 'N')?>> 불가능</label>
			</td>
		</tr>
		<tr>
			<th scope="row">미성년자 구매</th>
			<td>
				<label><input type="radio" name="n_infant" value="Y" <?=checked($data['n_infant'], 'Y')?>> 가능</label>
				<label><input type="radio" name="n_infant" value="N" <?=checked($data['n_infant'], 'N')?>> 불가능</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>상품정보고시</strong> <a href="./?body=product@product_definition&type=smartstore" target="_blank" class="setup btt" tooltip="설정"></th>
			<td>
				<?=selectArray($summary_arr, 'n_summary_no', null, ':: 상품정보고시 선택 ::', $data['n_summary_no'], '')?>
				<div class="list_info tp">
					<p class="warring">정보고시 내용을 입력해야 등록이 가능합니다.</p>
				</div>
			</td>
		</tr>
        <tr>
            <th scope="row"><strong>택배사</strong></th>
            <td>
                <?=selectArray($n_store_delivery_codes, 'n_delivery_company', false, ':: 택배사 선택 ::', $data['n_delivery_company'])?>
            </td>
        </tr>
		<tr class="class_delivery">
			<th scope="row"><strong>반품/교환지 주소</strong></th>
			<td>
				<?=selectArray($delivery_parcel, 'n_delivery_parcel', null, ':: 반품/교환지 주소 ::', $data['n_delivery_parcel'], '')?>
				<ul class="list_info tp">
					<li>
                        출고지 및 반품/교환지 주소는 네이버 스마트스토어센터 > 판매자정보 > 판매자 정보 내 배송정보를 통해 미리 등록해야 합니다.
                        <a href="https://sell.smartstore.naver.com/#/seller/info" target="_blank">바로가기</a>
                    </li>
					<li>
                        반품택배사 설정은 '국내 사업자 판매자'에 한해 변경 가능하며, 그 외에는 '우체국택배'만 이용 가능합니다.
                        <a href="https://help.sell.smartstore.naver.com/faq/content.help?faqId=3889" target="_blank">변경 안내</a>
                    </li>
				</ul>
			</td>
		</tr>
		<tr class="class_delivery">
			<th scope="row"><strong>반품배송비(편도)</strong></th>
			<td><input type="text" name="n_delivery_return_prc" value="<?=inputText($data['n_delivery_return_prc'])?>" class="input" placeholder=" 반품배송비(편도)"> 원</td>
		</tr>
		<tr class="class_delivery">
			<th scope="row"><strong>교환배송비(왕복)</strong></th>
			<td><input type="text" name="n_delivery_change_prc" value="<?=inputText($data['n_delivery_change_prc'])?>" class="input" placeholder=" 교환배송비(왕복)"> 원</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2"><strong>A/S</strong></th>
			<td><input type="text" name="n_as_tel" value="<?=inputText($data['n_as_tel'])?>" class="input" placeholder=" A/S전화번호"></td>
		</tr>
		<tr>
			<td>
				<p><input type="text" name="n_as_comment" value="<?=inputText($data['n_as_comment'])?>" class="input input_full" placeholder=" A/S안내"></p>
				<div class="list_info tp">
					<p>A/S전화번호의 경우 하이픈(-)을 포함하여 전화번호를 정확히 기입해주세요.</p>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<?php if ($cfg['n_smart_content2'] == 'Y') { ?>
<table class="tbl_row_reg n_store_table" <?php if($data['n_store_check'] != "Y"){?> style="display:none;"<? } ?>>
	<caption class="hidden">스마트스토어</caption>
	<tbody>
		<tr>
			<td class="right">
				<span class="box_btn_s icon copy"><input type="button" value="PC 상품상세설명 가져오기" onclick="getNContent(<?=$pno?>)"></span>
			</td>
		</tr>
		<tr>
			<td>
				<textarea id="n_content" name="n_content" style="width: 100%"><?=$data['n_content']?></textarea>
			</td>
		</tr>
	</tbody>
</table>
<?php } ?>

<?PHP
} else { ?>
        <div class="box_title_reg smartstore">
            <input type="hidden" name="n_store_check" value="<?=$data['n_store_check']?>">
            <h2 class="title">
                현재 네이버 스마트스토어와의 통신이 불안정합니다. <a href="#" class="tooltip_trigger" data-child="tooltip_smartstore_error"></a>
                <div class="info_tooltip tooltip_smartstore_error">
                    <div class="p_color">쇼핑몰에는 정상적으로 상품 정보가 저장됩니다.</div>
                        일시적인 현상이며 나중에 다시 수정 하시면 스마트스토어에 정상적으로 상품 정보가 반영됩니다.
                </div>
            </h2>
            <a href="./?body=config@n_smart_store" target="_blank" class="setup btt" tooltip="설정"></a>
        </div>
<?php } ?>
<style>
.n_attr .title {
    padding: 10px;
    background: #f8f8f8;
    font-weight: bold;
}
.n_attr .title.PRIMARY span {
    padding-right: 15px;
    background-image: url('<?=$engine_url?>/_manage/image/product/register/check.png');
    background-repeat: no-repeat;
    background-position: right 2px;
}
.n_attr ul {
    margin-bottom: 10px;
}
.n_attr li {
    width: 16.6%;
    display: inline-block;
    padding: 2px 20px 2px 0;
    white-space: nowrap;
}
</style>

<script src="<?=$engine_url?>/_engine/common/vue3/vue.js"></script>
<script type="text/javascript">
    // 인증 방식 변경 체크
    let smCertificationInfos = [];
    let smChild_certification = false;
    let smKc_certification = false;
    let smGreen_product = false;
    let smWholeCategory = 0;
    let smCategoryLast = false;
    function smCertifyChange() {
        // 카테고리 선택 완료 후 모델 검색
        const model = document.querySelector('.sm_model');
        if (smCategoryLast == true) {
            model.style.display = '';
        } else {
            model.style.display = 'hidden';
        }

        // 카테고리 변경 후 인증 선택
        const certification = document.querySelector('.sm_certification');
        const sel = document.querySelector('select[name=n_certificationInfoId]');
        const current_cert_type = '<?=($certification) ? $certification : ''?>';
        if (smChild_certification || smKc_certification || smGreen_product) {
            certification.style.display = '';
            while (sel.options.length > 1) {
                sel.remove(sel.options.length - 1);
            }
            for (let i = 0; i < smCertificationInfos.length; i++) {
                let cert = smCertificationInfos[i];

                let cert_type = '';
                if (smChild_certification && cert.kindTypes.includes('CHILD_CERTIFICATION')) cert_type = 'CHILD_CERTIFICATION';
                if (smGreen_product && cert.kindTypes.includes('GREEN_PRODUCTS')) cert_type = 'GREEN_PRODUCTS';
                if (smKc_certification && cert.kindTypes.includes('KC_CERTIFICATION')) cert_type = 'KC_CERTIFICATION';

                if (cert_type) {
                    var option = document.createElement('option');
                    option.text = cert.name;
                    option.value = cert_type + '@' + cert.id;
                    if (current_cert_type == cert_type + '@' + cert.id) {
                        option.selected = true;
                    }

                    sel.appendChild(option);
                }
            };
        } else {
            certification.style.display = 'none';
            sel.value = '';
        }

        // 인증 필요 여부
        const certificationTargetExclude = '<?=$n_extra->certificationTargetExclude?>';
        if (certificationTargetExclude) {
            document.querySelector('select[name=n_certificationTargetExclude]').value = certificationTargetExclude;
        }

        // 카테고리 변경 후 상품 속성란
        fetch('?body=product@product_store_attributes.exe&pno=<?=$pno?>&categoryId=' + smWholeCategory)
            .then(ret => ret.json())
            .then(ret => {
                nAttr.getAttributes(ret);
            });
    }
    
    // 카테고리 선택
    function smartCateInfinite(obj, level) {
        if (!obj) {
            document.querySelectorAll('[name^=n_category_]').forEach((o, key) => {
                if (o.value) {
                    obj = o;
                    level = key + 1
                }
            });
        }
        if (!obj) return false;

        smWholeCategory = obj.value;
        fetch('?body=product@product_store_cate.exe&level=' + level + '&cno=' + obj.value)
            .then(ret => ret.json())
            .then(ret => {
                const sel = document.querySelector('select[name=n_category_' + ret.next +']');
                if (sel) {
                    while (sel.options.length > 1) {
                        sel.remove(sel.options.length - 1);
                    }
                    ret.data.forEach(o => {
                        var option = document.createElement('option');
                        option.text = o.name;
                        option.value = o.id;

                        sel.appendChild(option);
                    });
                }
                smCategoryLast = ret.info.last;

                // 하위 select 초기화
                document.querySelectorAll('select[name^=n_category]').forEach((s, key) => {
                    if (level + 2 > key) return false;
                    while (s.options.length > 1) {
                        s.remove(s.options.length - 1);
                    }
                });

                // 최종 선택 카테고리로부터 인증 정보 체크
                smChild_certification = false;
                smKc_certification = false;
                smGreen_product = false;
                if (ret.info) {
                    smCertificationInfos = ret.info.certificationInfos;
                    if (ret.info.exceptionalCategories && ret.info.exceptionalCategories.length) {
                        ret.info.exceptionalCategories.forEach(policy => {
                            if (policy == 'CHILD_CERTIFICATION') {
                                smChild_certification = true;
                            }
                            if (policy == 'KC_CERTIFICATION') {
                                smKc_certification = true;
                            }
                            if (policy == 'GREEN_PRODUCTS') {
                                smGreen_product = true;
                            }
                        });
                    }
                }
                smCertifyChange();
            });
	}
    smartCateInfinite();

    // 원산지 선택
    function smartOriginInfinite(obj, level) {
        if (!obj) {
            document.querySelectorAll('[name^=n_origin_]').forEach((o, key) => {
                if (o.value) {
                    obj = o;
                    level = key + 1
                }
            });
        }
        if (!obj) return false;

        fetch('?body=product@product_store_origin.exe&level=' + level + '&cno=' + obj.value)
            .then(ret => ret.json())
            .then(ret => {
                const sel = document.querySelector('select[name=n_origin_' + ret.next +']');
                if (sel) {
                    while (sel.options.length > 1) {
                        sel.remove(sel.options.length - 1);
                    }
                    ret.data.forEach(o => {
                        var option = document.createElement('option');
                        option.text = o.name;
                        option.value = o.id;

                        sel.appendChild(option);
                    });
                }

                // 하위 select 초기화
                document.querySelectorAll('select[name^=n_origin_]').forEach((s, key) => {
                    if (level + 1 > key) return false;
                    while (s.options.length > 1) {
                        s.remove(s.options.length - 1);
                    }
                });
            });

        // 수입일 경우 수입사 명
        const n_importer = document.querySelector('[name=n_importer]');
        if (/^02/.test(obj.value)) {
            n_importer.style.display = 'block';
            n_importer.disabled = false;
        } else {
            n_importer.style.display = 'none';
            n_importer.disabled = true;
        }

        // 직접 입력
        const origin_content = document.querySelector('.origin_content');
        if (obj.form.n_origin_big.value == '04') {
            origin_content.style.display = '';
        } else {
            origin_content.style.display = 'none';
            origin_content.value = '';
        }
    }
    smartOriginInfinite();

    // 모델 검색
    function getModel(o) {
        const sel = document.querySelector('select[name=n_model]');
        while (sel.options.length > 1) {
            sel.remove(sel.options.length - 1);
        }

        if (o.value.length < 2) {
            return;
        }
        if (window.sm_interval) {
            clearTimeout(window.sm_interval);
        }
        window.sm_interval = setTimeout(() => {
            sel.options[0].text = '로딩중...';
            fetch('?body=product@product_store_model.exe&name=' + o.value + '&categoryId=' + smWholeCategory)
                .then(ret => ret.json())
                .then(ret => {
                    sel.options[0].text = ':: 모델명 ::';

                    while (sel.options.length > 1) {
                        sel.remove(sel.options.length - 1);
                    }
                    ret.data.forEach(o => {
                        var option = document.createElement('option');
                        option.text = o.name;
                        option.value = o.id;

                        sel.appendChild(option);
                    });
                });
        }, 300);
    }

	// 스마트스토어 상세설명의 에디터 열기
	function openNstoreEditor() {
		if( (oEditors.length == 0 || !oEditors.getById['n_content']) && $('textarea#n_content').length) {
			var editor_code = 'product_nstore_<?=$pno?>';
			var editor_gr = 'product_nstore';
			var editor = new R2Na('n_content', {'editor_code':editor_code, 'editor_gr':editor_gr});
			editor.initNeko(editor_code, editor_gr, 'img');

			$('#prdFrm').submit(function() {
				oEditors.getById['n_content'].exec("UPDATE_CONTENTS_FIELD", []);
				$('#n_content').val($('#n_content').val().replace(unescape("%uFEFF"), ""));
			});

		}
	}

	// 스마트스토어 상세설명에 기본 상세설명 가져오기
	function getNContent(pno) {
		$.post('?body=product@product_content2.exe', {'pno':pno}, function(r) {
			oEditors.getById['n_content'].setContents($(r).find('#content2').val());
		});
	}

    // 스마트스토어 사용함 처리
	var nStoreUseYn;
	(nStoreUseYn = function() {
        if($(':checkbox[name=n_store_check]').prop('checked') == true) {
			<?php if($data['nstoreId'] == '0') { ?>
			if($('#prdFrm').find(':checked[name=stat]').val() != '2') {
				this.checked = false;
				window.alert('스마트스토어에 최초 등록 시에는 \'정상\'상태의 상품만 등록하실 수 있습니다.');
				return false;
			}
			<?php } ?>
			$('.n_store_table').show();
			openNstoreEditor();
		} else {
			$('.n_store_table').hide();
		}
	})();
	$(':checkbox[name=n_store_check]').change(nStoreUseYn);

	<?php if($data['n_store_check'] == 'Y') { ?>
	openNstoreEditor();
	<?php } ?>

    // 설정 복사하기 프리셋 검색 레이어
	var nPreset = new layerWindow('product@product_nstore_preset.exe');
	nPreset.sel = function(pno) {
		$.post('?body=product@product_nstore_frm', {'pno':pno}, function(r) {
			$('#n_store_table').html($(r).find('#n_store_table').html());
			nPreset.close();
		});
	}

    // 속성
    const nAttr = Vue.createApp({
        data: function() {
            return {
                primary_only: true, // 중요항목만 보기 toggle
                optional: 0, // 비 중요항목 수
                attributes: []
            }
        },
        methods: {
            getAttributes: function(json) {
                this.optional = json.optional;
                this.attributes = json.attributes;
            },
            getAttrType: function(attr) {
                switch(attr.attributeClassificationType) {
                    case 'RANGE' :
                    case 'SINGLE_SELECT' :
                        return 'radio'
                    case 'MULTI_SELECT' :
                        return 'checkbox'
                }
            },
            checkMaxMatchingCount: function(attr, e) {
                if (attr.attributeValueMaxMatchingCount > 0) {
                    const checked = document.querySelectorAll('input:checked[name="n_attr[' + attr.attributeSeq + '][]"]');
                    if (checked.length > attr.attributeValueMaxMatchingCount) {
                        window.alert('최대 ' + attr.attributeValueMaxMatchingCount + '개 까지만 선택할 수 있습니다.');
                        e.target.checked = false;
                        return false;
                    }
                }
                return true;
            }
        },
        mounted: function() {

        }
    }).mount('#n_attr');
</script>