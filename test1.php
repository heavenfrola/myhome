<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>달력 만들기</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

</head>
<style>
    @import url('https://cdn.jsdelivr.net/npm/xeicon@2.3.3/xeicon.min.css');

    /* section calendar */
    @font-face {
        font-family: 'NanumSquareR';
        font-style: normal;
        font-weight: 400;
        src: local('Nanum Square Regular'),
        local('NanumSquareR'),
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html {
        --blue: #3f99ff;
    }

    body {
        font-family: NanumSquareR;
    }

    a {
        text-decoration-line: none;
        text-decoration: none;
        color: #000;
    }

    ol,
    ul {
        list-style: none;
    }


    /* Calendar ---------------------------------------------------- */
    .calendar-wrap {
        max-width: 1175px;
        padding-top: 50px;
        margin: 0 auto;
        font-family: "NanumSquareR";
        display: flex;
        gap: 2%;
        margin-bottom: 20px;
    }

    .calendar-wrap>div {}

    .calendar-middle-wrap {
        background: #fafbfa;
        width: 34%;
        padding: 40px 32px;
        padding-bottom: 100px;
    }

    .checkInOutInfo {
        width: 30%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #fafbfa;
        position: relative;
    }

    .calendar-wrap .cal_nav {
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 700;
        font-size: 48px;
        line-height: 78px;
    }

    .calendar-wrap .cal_nav .year-month {
        width: 300px;
        text-align: center;
        line-height: 1;
        font-size: 20px;
    }

    .calendar-wrap .cal_nav .nav {
        display: flex;
        border: 1px solid #333333;
        border-radius: 5px;
    }

    .calendar-wrap .cal_nav .go-prev,
    .calendar-wrap .cal_nav .go-next {
        display: block;
        width: 50px;
        font-size: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .calendar-wrap .cal_nav .go-prev::before,
    .calendar-wrap .cal_nav .go-next::before {
        content: "";
        display: block;
        width: 10px;
        height: 10px;
        border: 1px solid #000;
        border-width: 3px 3px 0 0;
        transition: border 0.1s;
    }

    .calendar-wrap .cal_nav .go-prev::before {
        transform: rotate(-135deg);
    }

    .calendar-wrap .cal_nav .go-next::before {
        transform: rotate(45deg);
    }

    .calendar-wrap .cal_wrap {
        padding-top: 40px;
        position: relative;
        margin: 0 auto;
    }

    .calendar-wrap .cal_wrap::after {
        top: 368px;
    }

    .calendar-wrap .cal_wrap .dates {
        display: flex;
        flex-flow: wrap;
        height: 290px;
    }

    .calendar-wrap .cal_wrap .days {
        display: flex;
        margin-bottom: 20px;
    }

    .calendar-wrap .cal_wrap .day {
        display: flex;
        justify-content: center;
        align-items: center;
        width: calc(100% / 7);
        text-align: left;
        color: #2d1d0b;
        font-size: 13px;
        font-weight: bold;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        position: relative;
    }

    .calendar-wrap .cal_wrap .day span {
        z-index: 5;
    }

    .calendar-wrap .cal_wrap .day .check_in_out_p {
        position: absolute;
        left: 50%;
        top: 47px;
        transform: translateX(-50%);
        font-size: 12px;
        width: 100%;
        color: var(--blue);
    }

    .calendar-wrap .cal_wrap .checkIn span {
        color: #fff;
    }

    .calendar-wrap .cal_wrap .checkOut span {
        color: #fff;
    }

    .calendar-wrap .cal_wrap .selectDay {
        position: relative;
    }

    .calendar-wrap .cal_wrap .selectDay::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        height: 30px;
        background: #c3defc;
        opacity: 0.5;
        z-index: 1;
    }

    .calendar-wrap .cal_wrap .checkIn.selectDay::before {
        left: 50%;
        width: 50%;
    }

    .calendar-wrap .cal_wrap .checkOut.selectDay::before {
        width: 50%;
    }

    .calendar-wrap .cal_wrap .checkIn::after,
    .calendar-wrap .cal_wrap .checkOut::after,
    .calendar-wrap .cal_wrap .checkIn::after {
        content: '';
        background: var(--blue);
        position: absolute;
        left: 50%;
        top: 50%;
        width: 30px;
        height: 30px;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        z-index: 2;
    }

    .calendar-wrap .cal_wrap .day:nth-child(7n -6) {
        color: #ed2a61;
    }

    .calendar-wrap .cal_wrap .day:nth-child(7n) {
        color: #3c6ffa;
    }

    .calendar-wrap .cal_wrap .day.disable {
        color: #ddd;
    }

    .current.today {
        background: rgb(242 242 242);
    }

    .checkInOutInfo>div {
        text-align: center;
        display: flex;
        flex-direction: column;
    }

    .checkInOutInfo p {
        font-size: 24px;
        color: #494949;
        line-height: 1.7;
        text-align: center;
    }

    .checkInOutInfo p.space {
        margin-bottom: 10px;
    }

    .checkInOutInfo p:nth-child(1) {
        width: 100%;
    }

    .checkInOutInfo p:nth-child(3) {
        width: 100%;
    }

    .checkInOutInfo p span {
        display: block;
        font-size: 16px;
        color: #a1a1a1;
    }


    @media screen and (max-width :1200px) {
        .calendar-wrap {
            flex-wrap: wrap;
            padding: 2%;
        }

        .calendar-middle-wrap {
            width: 49%;
            padding: 40px 7%;
        }

        .checkInOutInfo {
            width: 100%;
            margin-top: 2%;
            padding: 50px;
        }

        .checkInOutInfo>div {
            flex-direction: row;
            width: 100%;
        }

        .checkInOutInfo p:nth-child(1) {
            width: 50%;
        }

        .checkInOutInfo p:nth-child(2) {
            width: 50px;
        }

        .checkInOutInfo p:nth-child(3) {
            width: 50%;
        }
    }

    @media screen and (max-width:768px) {
        .calendar-middle-wrap {
            width: 100%;
            padding: 40px 10%;
        }

        .calendar-middle-wrap:first-of-type {
            margin-bottom: 2%;
        }

        .checkInOutInfo {
            padding: 20px;
        }

        .checkInOutInfo>div {
            flex-direction: column;
        }

        .checkInOutInfo p:nth-child(1) {
            width: 100%;
        }

        .checkInOutInfo p:nth-child(2) {
            width: 100%;
        }

        .checkInOutInfo p:nth-child(3) {
            width: 100%;
        }

        .checkInOutInfo p.space {
            margin-bottom: 0;
        }

        .checkInOutInfo p span {
            display: inline-block;
            margin-right: 10px;
        }

        .checkInOutInfo p label {
            font-size: 20px;
        }
    }
</style>
<script>
    // 날짜 포맷 정규식 (yyyy-mm-dd)
    const regexDate = RegExp(/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/);
    // 오늘 날짜 (yyyy-mm-dd 00:00:00)
    const today = new Date();
    // 달력이도 최대 개월 수
    const limitMonth = 4;
    // 달력에서 표기하는 날짜 객체
    let thisMonth = today;
    // 달력에서 표기하는 년
    let currentYear = thisMonth.getFullYear();
    // 달력에서 표기하는 월
    let currentMonth = thisMonth.getMonth();
    // 체크인 날짜
    let checkInDate = "";
    // 체크아웃 날짜
    let checkOutDate = "";

    $(document).ready(function () {
        // 달력 만들기
        calendarInit(thisMonth);

        // 이전달로 이동
        $('.go-prev').on('click', function () {
            const startDate = $('.start-year-month').html().split('.');

            // 달력이 현재 년 월 보다 같거나 작을경우 뒤로가기 막기
            if (getLimitMonthCheck(parseInt(startDate[0]), parseInt(startDate[1])) <= 0) {
                return;
            }

            thisMonth = new Date(currentYear, currentMonth - 1, 1);
            calendarInit(thisMonth);
        });

        // 다음달로 이동
        $('.go-next').on('click', function () {
            const lastDate = $('.last-year-month').html().split('.');

            // 예약 가능 최대 개월수와 같거나 크다면 다음달 이동 막기
            // if (getLimitMonthCheck(parseInt(lastDate[0]), parseInt(lastDate[1])) >= limitMonth) {
            //     alert('최대예약 기간은 ' + limitMonth + '개월 입니다.');
            //     return;
            // }

            let limitYear = today.getFullYear();
            if (currentMonth + limitMonth >= 12) {
                limitYear = limitYear + 1
            }

            thisMonth = new Date(currentYear, currentMonth + 1, 1);
            calendarInit(thisMonth);
        });
    });

    // 달력 그리기
    function calendarInit(thisMonth) {

        // 렌더링을 위한 데이터 정리
        currentYear = thisMonth.getFullYear();
        currentMonth = thisMonth.getMonth();

        // 렌더링 html 요소 생성
        let start_calendar = '';
        let last_calendar = '';

        makeStartCalendar();
        makeLastCalendar();

        // start_calendar
        function makeStartCalendar() {
            // 이전 달의 마지막 날 날짜와 요일 구하기
            const startDay = new Date(currentYear, currentMonth, 0);
            const prevDate = startDay.getDate();
            const prevDay = startDay.getDay();

            // 이번 달의 마지막날 날짜와 요일 구하기
            const endDay = new Date(currentYear, currentMonth + 1, 0);
            const nextDate = endDay.getDate();
            const nextDay = endDay.getDay();

            // 지난달
            for (let i = prevDate - prevDay; i <= prevDate; i++) {
                start_calendar += pervDisableDay(i);
            }

            // 이번달
            for (let i = 1; i <= nextDate; i++) {
                // 이번달이 현재 년도와 월이 같을경우
                if (currentYear === today.getFullYear() && currentMonth === today.getMonth()) {
                    // 지난 날짜는 disable 처리
                    if (i < today.getDate()) {
                        start_calendar += pervDisableDay(i)
                    } else {
                        start_calendar += dailyDay(currentYear, currentMonth, i);
                    }
                } else {
                    start_calendar += dailyDay(currentYear, currentMonth, i);
                }
            }

            // 다음달 7 일 표시
            for (let i = 1; i <= (6 - nextDay); i++) {
                start_calendar += nextDisableDay(i);
            }

            $('.start-calendar').html(start_calendar);
            // 월 표기
            $('.start-year-month').text(currentYear + '.' + zf((currentMonth + 1)));
        }

        // last_calendar
        function makeLastCalendar() {
            let tempCurrentYear = currentYear;
            let tempCurrentMonth = currentMonth + 1;

            if (tempCurrentMonth >= 12) {
                tempCurrentYear = parseInt(tempCurrentYear) + 1;
                tempCurrentMonth = 0;
            }

            // 이전 달의 마지막 날 날짜와 요일 구하기
            const startDay = new Date(tempCurrentYear, tempCurrentMonth, 0);
            const prevDate = startDay.getDate();
            const prevDay = startDay.getDay();

            // 이번 달의 마지막날 날짜와 요일 구하기
            const endDay = new Date(tempCurrentYear, tempCurrentMonth + 1, 0);
            const nextDate = endDay.getDate();
            const nextDay = endDay.getDay();

            // 지난달
            for (let i = prevDate - prevDay; i <= prevDate; i++) {
                last_calendar += pervDisableDay(i);
            }

            // 이번달
            for (let i = 1; i <= nextDate; i++) {
                // 이번달이 현재 년도와 월이 같을경우
                if (tempCurrentYear === today.getFullYear() && tempCurrentMonth === today.getMonth()) {
                    // 지난 날짜는 disable 처리
                    if (i < today.getDate()) {
                        last_calendar += pervDisableDay(i)
                    } else {
                        last_calendar += dailyDay(tempCurrentYear, tempCurrentMonth, i);
                    }
                } else {
                    last_calendar += dailyDay(tempCurrentYear, tempCurrentMonth, i);
                }

            }

            // 다음달 7 일 표시
            for (let i = 1; i <= (6 - nextDay); i++) {
                last_calendar += nextDisableDay(i);
            }

            $('.last-calendar').html(last_calendar);
            // 월 표기
            $('.last-year-month').text(tempCurrentYear + '.' + zf((tempCurrentMonth + 1)));
        }


        // 지난달 미리 보기
        function pervDisableDay(day) {
            return '<div class="day prev disable">' + day + '</div>';
        }

        // 이번달
        function dailyDay(currentYear, currentMonth, day) {
            const date = currentYear + '' + zf((currentMonth + 1)) + '' + zf(day);

            if (checkInDate === date) {
                return '<div class="day current checkIn" data-day="' + date + '" onclick="selectDay(this)"><span>' + day + '</span><p class="check_in_out_p"></p><p>' + '</div>';
            } else if (checkOutDate === date) {
                return '<div class="day current checkOut" data-day="' + date + '" onclick="selectDay(this)"><span>' + day + '</span><p class="check_in_out_p"></p><p>' + '</div>';
            } else {
                return '<div class="day current" data-day="' + date + '" onclick="selectDay(this)"><span>' + day + '</span><p class="check_in_out_p"></p><p>' + '</div>';
            }
        }

        // 다음달 미리 보기
        function nextDisableDay(day) {
            return '<div class="day next disable">' + day + '</div>';
        }

        addClassSelectDay();
    }

    // 체크인 체크아웃 기간 안에 날짜 선택 처리
    function addClassSelectDay() {
        if (checkInDate !== "" && checkOutDate != "") {
            $('.day').each(function () {
                const data_day = $(this).data('day');

                if (data_day !== undefined && data_day >= checkInDate && data_day <= checkOutDate) {
                    $(this).addClass('selectDay');
                }
            });

            $('.checkIn').find('.check_in_out_p').html('체크인');
            $('.checkOut').find('.check_in_out_p').html('체크아웃');
        }
    }

    // 달력 날짜 클릭
    function selectDay(obj) {
        if (checkInDate === "") {
            $(obj).addClass('checkIn');
            $('.checkIn').find('.check_in_out_p').html('체크인');

            checkInDate = $(obj).data('day');

            $('#check_in_day').html(getCheckIndateHtml());

            lastCheckInDate();
        } else {
            // 체크인 날짜를 한번더 클릭했을때 아무 동작 하지 않기
            if (parseInt(checkInDate) === $(obj).data('day')) {
                return;
            }

            // 체크인 날짜보다 체크아웃 날짜를 더 앞으로 찍었을경우 체크인 날짜와 체크아웃 날짜를 바꿔준다
            if (checkOutDate === "" && parseInt(checkInDate) > $(obj).data('day')) {
                $('.checkIn').find('.check_in_out_p').html('');
                $('.day').removeClass('checkIn');
                $('#check_in_day').html("");

                checkOutDate = checkInDate
                checkInDate = $(obj).data('day');

                $(obj).addClass('checkIn');
                $('.checkIn').find('.check_in_out_p').html('체크인');

                $('.day[data-day="' + checkOutDate + '"]').addClass('checkOut');
                $('.checkOut').find('.check_in_out_p').html('체크아웃');

                $('#check_in_day').html(getCheckIndateHtml());
                $('#check_out_day').html(getCheckOutdateHtml());

                addClassSelectDay();

                return;
            }

            // 체크아웃
            if (checkOutDate === "") {
                $(obj).addClass('checkOut');
                $('.checkOut').find('.check_in_out_p').html('체크아웃');

                checkOutDate = $(obj).data('day');

                $('#check_out_day').html(getCheckOutdateHtml());

                addClassSelectDay();
            } else {
                // 체크아웃을 날짜 까지 지정했지만 체크인 날짜를 변경할 경우
               // if (confirm('체크인 날짜를 변경 하시겠습니까?')) {
                    $('.checkIn').find('.check_in_out_p').html('');
                    $('.checkOut').find('.check_in_out_p').html('');

                    $('.day').removeClass('checkIn');
                    $('.day').removeClass('checkOut');
                    $('.day').removeClass('selectDay');

                    $(obj).addClass('checkIn');
                    $('.checkIn').find('.check_in_out_p').html('체크인');

                    checkInDate = $(obj).data('day');
                    checkOutDate = "";

                    $('#check_in_day').html(getCheckIndateHtml());
                    $('#check_out_day').html("");

                    lastCheckInDate();
               // }
            }
        }
    }

    // 체크인 날짜 표기
    function getCheckIndateHtml() {
        checkInDate = checkInDate.toString();
        return checkInDate.substring('0', '4') + "-" + checkInDate.substring('4', '6') + "-" + checkInDate.substring('6', '8') + " ( " + strWeekDay(weekday(checkInDate)) + " )";
    }

    // 체크아웃 날짜 표기
    function getCheckOutdateHtml() {
        checkOutDate = checkOutDate.toString();
        return checkOutDate.substring('0', '4') + "-" + checkOutDate.substring('4', '6') + "-" + checkOutDate.substring('6', '8') + " ( " + strWeekDay(weekday(checkOutDate)) + " )";
    }

    // 체크인 날짜 클릭시 예약 가능한 마지막 날인지 체크 마지막날 일경우 체크아웃 날짜 자동 선택
    function lastCheckInDate() {
        // 날짜 비교를 위해 시간값을 초기화 하기위해 체크인 날짜 다시 셋팅
        let thisCheckDate = new Date(conversion_date(checkInDate, 1));
        thisCheckDate = new Date(thisCheckDate.getFullYear(), thisCheckDate.getMonth(), thisCheckDate.getDate());

        // 예약 가능한 마지막달의 마지막 날짜 셋팅
        let thisLastDate = new Date(today.getFullYear(), ((today.getMonth() + 1) + limitMonth), 0);

        // 체크인 날짜 클릭시 해당일이 예약 가능한 달에 마지막 날짜 일때 체크아웃 강제 표기
        if (thisCheckDate.getTime() === thisLastDate.getTime()) {
            // 체크인 날짜에 하루 더하기
            let thisCheckOutDate = new Date(thisCheckDate.getFullYear(), thisCheckDate.getMonth(), thisCheckDate.getDate());
            thisCheckOutDate.setDate(thisCheckOutDate.getDate() + 1);
            // YYYYMMDD 형태로 변환
            thisCheckOutDate = thisCheckOutDate.getFullYear() + "" + zf((thisCheckOutDate.getMonth() + 1)) + "" + zf(thisCheckOutDate.getDate());

            checkOutDate = thisCheckOutDate;

            $($(".day div[data-day='" + checkOutDate + "']")).addClass('checkOut');

            if ($('.checkOut').find('p').hasClass('holi_day_p')) {
                $('.checkOut').find('.holi_day_p').hide();
            }

            $('.checkOut').find('.check_in_out_p').html('체크아웃');

            $('#check_out_day').html(getCheckOutdateHtml());

            addClassSelectDay();
        }
    }

    // 최대 개월수 체크
    function getLimitMonthCheck(year, month) {
        let months = ((today.getFullYear() - year) * 12);
        months -= (today.getMonth() + 1);
        months += month;

        return months;
    }

    // 날짜형태 변환
    function conversion_date(YYMMDD, choice) {
        const yyyy = YYMMDD.substring(0, 4);
        const mm = YYMMDD.substring(4, 6);
        const dd = YYMMDD.substring(6, 8);

        return (choice === 1)
            ? yyyy + "-" + zf(mm) + "-" + zf(dd)
            : yyyy + "." + zf(mm) + "." + zf(dd);
    }

    // 몇요일인지 알려주는 함수 (숫자 형태)
    function weekday(YYYYMMDD) {
        const weekday_year = YYYYMMDD.substring(0, 4);
        const weekday_menth = YYYYMMDD.substring(4, 6);
        const weekday_day = YYYYMMDD.substring(6, 9);

        return new Date(weekday_year + "-" + weekday_menth + "-" + weekday_day).getDay();
    }

    // 요일 리턴
    function strWeekDay(weekday) {
        switch (weekday) {
            case 0: return "일"
                break;
            case 1: return "월"
                break;
            case 2: return "화"
                break;
            case 3: return "수"
                break;
            case 4: return "목"
                break;
            case 5: return "금"
                break;
            case 6: return "토"
                break;
        }
    }

    // 숫자 두자리로 만들기
    function zf(num) {
        num = Number(num).toString();

        if (Number(num) < 10 && num.length == 1) {
            num = "0" + num;
        }

        return num;
    }
</script>
<body>
<div class="calendar-wrap">
    <div class="calendar-middle-wrap">
        <div class="cal_nav">
            <a href="javascript:;" class="nav-btn go-prev"></a>
            <span class="year-month start-year-month"></span>
            <a href="javascript:;" class="nav-btn go-next"></a>
        </div>
        <div class="cal_wrap">
            <div class="days">
                <div class="day">일</div>
                <div class="day">월</div>
                <div class="day">화</div>
                <div class="day">수</div>
                <div class="day">목</div>
                <div class="day">금</div>
                <div class="day">토</div>
            </div>
            <div class="dates start-calendar"></div>
        </div>
    </div>

    <div class="calendar-middle-wrap">
        <div class="cal_nav">
            <a href="javascript:;" class="nav-btn go-prev"></a>
            <span class="year-month last-year-month"></span>
            <a href="javascript:;" class="nav-btn go-next"></a>
        </div>
        <div class="cal_wrap">
            <div class="days">
                <div class="day">일</div>
                <div class="day">월</div>
                <div class="day">화</div>
                <div class="day">수</div>
                <div class="day">목</div>
                <div class="day">금</div>
                <div class="day">토</div>
            </div>
            <div class="dates last-calendar"></div>
        </div>
    </div>

    <div class="checkInOutInfo">
        <div>
            <p>
                <span>체크인</span>
                <label id="check_in_day"></label>
            </p>
            <p class="space">~</p>
            <p>
                <span>체크아웃</span>
                <label id="check_out_day"></label>
            </p>
        </div>
    </div>
</div>
</body>

</html>