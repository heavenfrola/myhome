<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>jQuery 달력</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
</head>
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

<script>
    /*
    * 작업시 문제가 됬던 부분
    * - 전역 변수에 this는 사용 금지
    * - 달력의 시작 주, 마지막 주 구하는 방법
    *
    *
    *
    * */
    const startDate = new Date();

    let ChangeDate = startDate;
    let defaultStartYear = ChangeDate.getFullYear(); // 시작 년도
    let defaultStartMonth = ChangeDate.getMonth(); // 시작 월

    let checkInDate = "";
    let checkOutDate = "";

    //첫 시작 요일
    //마지막 시작 요일
    $(function() {
        $('.start-year-month').html(defaultStartYear+'-'+nf(defaultStartMonth+1));
        $('.last-year-month').html(defaultStartYear+'-'+nf(defaultStartMonth+2));

        //이전달
        $('.go-prev').click(function() {
            ChangeDate = new Date(defaultStartYear,defaultStartMonth-1,1);
            calendarSeting(ChangeDate);
        });

        //다음달
        $('.go-next').click(function() {
            ChangeDate = new Date(defaultStartYear,defaultStartMonth+1,1);
            calendarSeting(ChangeDate);
        });

       calendarSeting(ChangeDate);
    });

    function calendarSeting(thisSDate) {

        defaultStartYear = thisSDate.getFullYear();
        defaultStartMonth = thisSDate.getMonth();

        //이번달 표시
        const startDate = new Date();
        const thisStartYear = thisSDate.getFullYear(); // 시작 년도
        const thisStartMonth = thisSDate.getMonth(); // 시작 월

        //다음달 표시
        const thisLDate = new Date(thisStartYear,thisStartMonth+1,1);
        let nextYear = thisLDate.getFullYear(); // 시작 년도
        let nextMonth = thisLDate.getMonth(); // 시작 월

        const sView =  makeCalendar(thisStartYear, thisStartMonth);
        const eView =  makeCalendar(nextYear, nextMonth);

        $('.start-year-month').html(thisStartYear+'-'+nf(thisStartMonth+1));
        $('.last-year-month').html(nextYear+'-'+nf(nextMonth+1)); //0~11 까지 있으므로 +1

        $('.start-calendar').html(sView);
        $('.last-calendar').html(eView);

        //달력 이동 시 Class 유지
        if(checkInDate) {
            const cie = document.querySelectorAll('[data-day="'+checkInDate+'"]');
            $(cie).addClass('checkIn');
            $(cie).find('.check_in_out_p').html('체크인');
        }

        if(checkOutDate) {
            const oie = document.querySelectorAll('[data-day="'+checkOutDate+'"]');
            $(oie).addClass('checkOut');
            $(oie).find('.check_in_out_p').html('체크아웃');
        }
    }

    function makeCalendar(thisYear, thisMonth) {
        const startDay = new Date(thisYear, thisMonth, 0);
        const prevDate = startDay.getDate(); //이전 달 날짜
        const prevDay = startDay.getDay(); // 이전 달 요일

        // 이번 달의 마지막날 날짜와 요일 구하기
        const endDay = new Date(thisYear, thisMonth + 1, 0);
        const nextDate = endDay.getDate(); // 이번 달 날짜
        const nextDay = endDay.getDay(); //이번 달 요일

        let sText = '';
        for (let i = prevDate - prevDay; i <= prevDate; i++) sText += pervDisableDay(i); // 이전달 요일 [이전 마지막날 - 요일]
        for (let i = 1; i <= nextDate; i++) sText += MonthText(thisYear, thisMonth, i); // 해당 달력
        for (let i = 1; i <= (6-nextDay); i++) sText += pervDisableDay(i); // 다음달 요일 [다음 일주일-요일]

        return sText;
    }

    //선택 시
    function selectDay(o) {

        //체크인 시
        if(checkInDate === "") {
            $(o).addClass('checkIn');

            checkInDate = $(o).data('day');
           $(o).find('.check_in_out_p').html('체크인');
           $('#check_in_day').html($(o).data('day'));
        } else {

            //기존에 체크아웃을 선택했을 경우
            if(checkInDate == $(o).data('day')) {
                return;
            }

            //체크아웃 시
            if(checkOutDate === "") {
                $(o).addClass('checkIn');

                checkOutDate = $(o).data('day');
                $(o).find('.check_in_out_p').html('체크아웃');

                $('#check_out_day').html($(o).data('day'));
            } else {
                removeMonth();
            }
        }
    }

    function removeMonth() {

        $('.checkIn, .checkOut').find('.check_in_out_p').html('');
        $('#check_in_day, #check_out_day').html('');
        $('.day').removeClass('checkIn checkOut selectDay');

        checkInDate = checkOutDate = '';
    }

    // 지난달 미리 보기
    function pervDisableDay(day) {
        //이미 지난 요일은 선택 X / day 삭제
        return '<div class="day prev disable"></div>';
    }

    function MonthText(year, month, day) {

        const date = year+''+nf(month+1)+''+nf(day)
        let mText = '';
        mText = '<div class="day current" data-day="' + date + '" onclick="selectDay(this)"><span>' + day + '</span><p class="check_in_out_p"></p></div>';
        return mText;
    }

    function nf(num) {
        num = Number(num).toString();

        if (Number(num) < 10 && num.length == 1) {
            num = "0" + num;
        }

        return num;
    }

</script>
</html>