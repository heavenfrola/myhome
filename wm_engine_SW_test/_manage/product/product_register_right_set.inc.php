<?php

/**
 * 세트 상품 등록. 우측 상품 선택 섹션
 **/

?>
<div class="box_title_reg first" style="border-bottom:0">
    <h2 class="title">
        세트 구성
        <span class="box_btn_s icon copy btns">
            <input type="button" value="상품선택" onclick="psearch.opennew('exparam=99&partner_no='+pf.partner_no.value);">
        </span>
    </h2>
</div>
<table class="tbl_col">
    <caption class="hidden">세트 구성</caption>
    <colgroup>
        <col>
        <col style="width:80px">
    </colgroup>
    <thead>
        <th scope="col">상품</th>
        <th scope="col">별도판매<br>불가</th>
        <th scope="col">삭제</th>
    </thead>
    <tbody id="refArea99">
        <?PHP
            $refkey = '99';
            include $engine_dir."/_manage/product/product_ref_frm.exe.php";
        ?>
        <?php if (!$html) { ?>
        <tr>
            <td colspan="3">구성된 상품이 없습니다.</td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<br>

<script type="text/javascript">
$("#refArea99").sortable({
    'placeholder': 'placeholder',
    'cursor':'all-scroll',
    'scroll': false,
    'update': function() {
        var sort = [];
        $('.ref_sortable_99').each(function(idx) {
            sort[idx] = $(this).data('refno');
        })
        $.post('./index.php', {'body':'product@product_ref.exe', 'exec':'sort_all', 'data':sort});
    }
});
</script>