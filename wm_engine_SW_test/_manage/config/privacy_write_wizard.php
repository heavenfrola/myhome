<?php
$rURL = getListURL('privacyList');
if(!$rURL) $rURL = './?body=config@privacy';
?>

<script type="text/javascript" src='<?= $engine_url ?>/_engine/common/jquery.serializeObject.js'></script>
<script type="text/javascript" src='<?= $engine_url ?>/_manage/privacy.js?t=<?=time()?>'></script>
<form name="privacy_writeF" id="privacy_writeF" action="./?body=config@privacy_write" method="POST">
    <input type="hidden" name="company_name" value="<?= $cfg['company_name'] ?>">
    <input type="hidden" name="pageType" value="wizard">
    <textarea name="contents" style="display:none;"></textarea>
    <div class="box_title first">
        <h2>기본정보</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">기본정보</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>시행일자</th>
            <td><input type="text" class="datepicker input" name="effective_date" value="<?= date('Y-m-d', $now) ?>">
            </td>
        </tr>
        <tr>
            <th>속성</th>
            <td><label><input type="checkbox" name="hidden" value="N">사용</label></td>
        </tr>
        </tbody>
    </table>
    <div class="box_title">
        <h2>수집하는 개인 정보 항목</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">수집하는 개인 정보 항목</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>개인 정보 수집 방법</th>
            <td>
                <label><input type="checkbox" name="collect_path" value="회원가입" checked>회원가입</label>
                <label><input type="checkbox" name="collect_path" value="주문" checked>주문</label>
                <label><input type="checkbox" name="collect_path" value="기타">기타</label>
                <input type="text" class="input" name="collect_path_text"> (,로 구분)
            </td>
        </tr>
        <tr>
            <th>일반 정보</th>
            <td>
                <label><input type="checkbox" name="basic_info" value="이름" checked>이름</label>
                <label><input type="checkbox" name="basic_info" value="생년월일" checked>생년월일</label>
                <label><input type="checkbox" name="basic_info" value="성별" checked>성별</label>
                <label><input type="checkbox" name="basic_info" value="로그인ID" checked>로그인ID</label>
                <label><input type="checkbox" name="basic_info" value="비밀번호" checked>비밀번호</label>
                <label><input type="checkbox" name="basic_info" value="비밀번호 질문과 답변" checked>비밀번호 질문과 답변</label>
                <label><input type="checkbox" name="basic_info" value="자택 전화번호" checked>자택 전화번호</label>
                <label><input type="checkbox" name="basic_info" value="자택 주소">자택 주소</label>
                <label><input type="checkbox" name="basic_info" value="휴대전화번호">휴대전화번호</label>
                <label><input type="checkbox" name="basic_info" value="이메일">이메일</label>
            </td>
        </tr>
        <tr>
            <th>직장 정보</th>
            <td>
                <label><input type="checkbox" name="job_info" value="직업" checked>직업</label>
                <label><input type="checkbox" name="job_info" value="회사명" checked>회사명</label>
                <label><input type="checkbox" name="job_info" value="부서">부서</label>
                <label><input type="checkbox" name="job_info" value="직책">직책</label>
                <label><input type="checkbox" name="job_info" value="회사전화번호">회사전화번호</label>
            </td>
        </tr>
        <tr>
            <th>관심사, 기념일 정보</th>
            <td>
                <label><input type="checkbox" name="interests" value="취미" checked>취미</label>
                <label><input type="checkbox" name="interests" value="결혼여부">결혼여부</label>
                <label><input type="checkbox" name="interests" value="기념일">기념일</label>
            </td>
        </tr>
        <tr>
            <th>법정대리인 정보</th>
            <td>
                <label><input type="checkbox" name="legal_agent" value="법정대리인정보" checked>법정대리인정보</label>
            </td>
        </tr>
        <tr>
            <th>활동 정보</th>
            <td>
                <label><input type="checkbox" name="additional" value="종교" checked>종교</label>
                <label><input type="checkbox" name="additional" value="학력" checked>학력</label>
                <label><input type="checkbox" name="additional" value="신체정보">신체정보</label>
            </td>
        </tr>
        <tr>
            <th>금융 정보</th>
            <td>
                <label><input type="checkbox" name="financial" value="신용카드 정보" checked>신용카드 정보</label>
                <label><input type="checkbox" name="financial" value="은행계좌 정보">은행계좌 정보</label>
            </td>
        </tr>
        <tr>
            <th>자동 생성 정보</th>
            <td>
                <label><input type="checkbox" name="log_info" value="서비스 이용기록" checked>서비스 이용기록</label>
                <label><input type="checkbox" name="log_info" value="접속 로그">접속 로그</label>
                <label><input type="checkbox" name="log_info" value="쿠키">쿠키</label>
                <label><input type="checkbox" name="log_info" value="접속 IP정보">접속 IP정보</label>
                <label><input type="checkbox" name="log_info" value="결제기록">결제기록</label>
                <label><input type="checkbox" name="log_info" value="기타정보">기타정보</label>
            </td>
        </tr>
        <tr>
            <th>기타정보</th>
            <td>
                <label><input type="checkbox" name="etc_info" value="기타" checked>기타</label>
                <input type="text" name="etc_info_text" class="input">(,로 구분)
            </td>
        </tr>
        </tbody>
    </table>
    <div class="box_title">
        <h2>개인정보 수집 및 이용목적</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">개인정보 수집 및 이용목적</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>서비스</th>
            <td>
                <ul>
                    <li><label><input type="checkbox" name="service" value="콘텐츠 제공" checked>콘텐츠 제공</label></li>
                    <li><label><input type="checkbox" name="service" value="구매 및 요금 결제" checked>구매 및 요금 결제</label></li>
                    <li><label><input type="checkbox" name="service" value="물품배송 또는 청구서 등 발송">물품배송 또는 청구서 등 발송</label>
                    </li>
                    <li><label><input type="checkbox" name="service" value="금융거래 본인 인증 및 금융서비스">금융거래 본인 인증 및
                            금융서비스</label></li>
                    <li><label><input type="checkbox" name="service" value="요금추심">요금추심</label></li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>회원관리</th>
            <td>
                <ul>
                    <li><label><input type="checkbox" name="member_mng" value="회원제 서비스 이용에 따른 본인확인" checked>회원제 서비스 이용에
                            따른 본인확인</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="개인 식별" checked>개인 식별</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="불량회원의 부정 이용 방지와 비인가 사용 방지">불량회원의 부정 이용
                            방지와 비인가 사용 방지</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="가입 의사 확인">가입 의사 확인</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="연령확인">연령확인</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="만14세 미만 아동 개인정보 수집 시 법정 대리인 동의여부 확인">만14세
                            미만 아동 개인정보 수집 시 법정 대리인 동의여부 확인</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="불만처리 등 민원처리">불만처리 등 민원처리</label></li>
                    <li><label><input type="checkbox" name="member_mng" value="고지사항 전달">고지사항 전달</label></li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>마케팅</th>
            <td>
                <ul>
                    <li><label><input type="checkbox" name="marketing" value="신규 서비스(제품) 개발 및 특화" checked>신규 서비스(제품) 개발
                            및 특화</label></li>
                    <li><label><input type="checkbox" name="marketing" value="이벤트 등 광고성 정보 전달">이벤트 등 광고성 정보 전달</label>
                    </li>
                    <li><label><input type="checkbox" name="marketing" value="인구통계학적 특성에 따른 서비스 제공 및 광고 게재">인구통계학적 특성에
                            따른 서비스 제공 및 광고 게재</label></li>
                    <li><label><input type="checkbox" name="marketing" value="접속 빈도 파악 또는 회원의 서비스 이용에 대한 통계">접속 빈도 파악 또는
                            회원의 서비스 이용에 대한 통계</label></li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>기타</th>
            <td>
                <label><input type="checkbox" name="etc2_info" value="기타" checked>기타</label>
                <input type="text" name="etc2_info_text" class="input" value="기타,기타,기타">(,로 구분)
            </td>
        </tr>
        </tbody>
    </table>
    <div class="box_title">
        <h2>고객서비스 담당 부서</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">고객서비스 담당 부서</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>부서명</th>
            <td>
                <input type="text" name="customer_service_name" class="input"
                       value="<?= $cfg['company_privacy1_part'] ?>">
            </td>
        </tr>
        <tr>
            <th>이메일</th>
            <td>
                <input type="text" name="customer_service_email" class="input"
                       value="<?= $cfg['company_privacy1_email'] ?>">
            </td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td>
                <input type="text" name="customer_service_phone" class="input"
                       value="<?= $cfg['company_privacy1_phone'] ?>">
            </td>
        </tr>
        </tbody>
    </table>
    <div class="box_title">
        <h2>개인 정보보호 책임자</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">개인 정보보호 책임자</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>성명</th>
            <td>
                <input type="text" name="security_service_name" class="input"
                       value="<?= $cfg['company_privacy2_name'] ?>">
            </td>
        </tr>
        <tr>
            <th>이메일</th>
            <td>
                <input type="text" name="security_service_email" class="input"
                       value="<?= $cfg['company_privacy2_email'] ?>">
            </td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td>
                <input type="text" name="security_service_phone" class="input"
                       value="<?= $cfg['company_privacy2_phone'] ?>">
            </td>
        </tr>
        </tbody>
    </table>
    <div class="box_title">
        <h2>개인정보 파기 절차 및 방법</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">개인정보 파기 절차 및 방법</caption>
        <colgroup>
            <col style="width:15%">
            <col style="width:85%">
        </colgroup>
        <tbody>
        <tr>
            <th>파기 절차 및 방법</th>
            <td>
                <ul>
                    <li><label><input type="checkbox" name="remove_method" value="파일 재사용이 불가능한 방법으로 삭제" checked>파일 재사용이
                            불가능한 방법으로 삭제</label></li>
                    <li><label><input type="checkbox" name="remove_method" value="물리적으로 분쇄 또는 소각">물리적으로 분쇄 또는 소각</label>
                    </li>
                    <li><label><input type="checkbox" name="remove_method" value="기타">기타</label> <input type="text" name="remove_method_text" class="input">(,로 구분)
                    </li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>개인정보 자동 수집</th>
            <td>
                <ul>
                    <li>쿠키와 같은 개인정보 자동수집 장치를 설치 운영하고 있는지 여부와, 설치/운영 시 그 거부 방법에 대해 입력합니다.</li>
                    <li><input type="radio" name="auto_collect" value="Y" checked>예 <input type="radio" name="auto_collect" value="N">아니오
                    </li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><button type="button" onClick="privacy_cls.wizardNext();">확인</button></span>
        <span class="box_btn gray"><button type="button" onClick="location.href='<?=$rURL?>';">취소</button></span>
        <span class="box_btn gray"><button type="button" onClick="privacy_cls.preview('wizard');">미리보기</button></span>
    </div>
</form>

<style>
    .privacy_preview {
        position: fixed;
        display: none;
        z-index: 100;
        top: 200px;
        left: 50%;
        margin-left: -249px;
        border: 1px solid #dedede;
        background: #fff;
        overflow:hidden;
    }
    .privacy_preview, .privacy_preview iframe {
        height: 500px;
        width: 900px;
    }
    .privacy_preview .preview_title {
        background:#000;
        padding:3px;
        text-align:right;
    }
    .privacy_preview .preview_title a {
        color:#fff;
        font-weight:bold;
    }
</style>
<div id="privacy_preview" class="privacy_preview">
    <div class="preview_title">
        <a href="javascript:;" onClick="privacy_cls.previewToggle(false);">[닫기]</a>
    </div>
    <iframe ></iframe>
</div>


<script>
    const privacy_cls = new Privacy();
</script>