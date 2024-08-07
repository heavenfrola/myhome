<?php

/**
 * 상품 등록, 도서 정보
 **/

$book = $pdo->assoc("select * from {$tbl['product_book']} where no='$pno'");
if (!$book) {
    $book['is_used'] = 'N';
}

?>
<div>
    <div class="box_title_reg">
        <h2 class="title">도서정보</h2>
    </div>
    <table class="tbl_row_reg">
        <caption class="hidden">도서 정보</caption>
        <colgroup>
            <col style="width:134px">
        </colgroup>
        <tr>
            <th scope="row">도서 타입</th>
            <td>
                <?=selectArray($_is_book_type, 'is_book', false, '', $data['is_book'], 'isBook()')?>
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">중고 여부</th>
            <td>
                <label><input type="radio" name="book_is_used" value="N" <?=checked($book['is_used'], 'N')?>> 신품</label>
                <label><input type="radio" name="book_is_used" value="Y" <?=checked($book['is_used'], 'Y')?>> 중고</label>
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row"><strong>ISBN</strong></th>
            <td>
                <input type="text" name="book_isbn" value="<?=$book['isbn']?>" class="input" maxlength="13">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row"><strong>도서명</strong></th>
            <td>
                <input type="text" name="book_title" value="<?=$book['title']?>" class="input input_full">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">권호 정보</th>
            <td>
                <input type="text" name="book_number" value="<?=$book['number']?>" class="input">
                <ul class="list_msg">
                    <li>1권일 경우 1</li>
                </ul>
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">버전 정보</th>
            <td>
                <input type="text" name="book_version" value="<?=$book['version']?>" class="input">
                <ul class="list_msg">
                    <li>2021년 or 개정판</li>
                </ul>
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">부제</th>
            <td>
                <input type="text" name="book_subtitle" value="<?=$book['subtitle']?>" class="input input_full">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">원서명</th>
            <td>
                <input type="text" name="book_original_title" value="<?=$book['original_title']?>" class="input input_full">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">작가명</th>
            <td>
                <input type="text" name="book_author" value="<?=$book['author']?>" class="input">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">출판사</th>
            <td>
                <input type="text" name="book_publisher" value="<?=$book['publisher']?>" class="input">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row"><strong>출간일</strong></th>
            <td>
                <input type="text" name="book_publish_day" value="<?=$book['publish_day']?>" class="input">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">크기/판형</th>
            <td>
                <input type="text" name="book_size" value="<?=$book['size']?>" class="input">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">쪽수</th>
            <td>
                <input type="text" name="book_pages" value="<?=$book['pages']?>" class="input">
            </td>
        </tr>
        <tr class="book_info">
            <th scope="row">목차 또는 책 소개</th>
            <td>
                <textarea name="book_description" class="txta"><?=$book['description']?></textarea>
            </td>
        </tr>
    </table>
</div>