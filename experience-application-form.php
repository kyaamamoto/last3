<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'session_config.php';
require_once 'security_headers.php';
require_once 'funcs.php';

// ログインチェック
loginCheck();

// ユーザー情報の取得
$user = getUserInfo($_SESSION['user_id']);

// データベース接続
$pdo = db_conn();

// フロンティアIDを取得（URLパラメータから）
$frontier_id = isset($_GET['frontier_id']) ? intval($_GET['frontier_id']) : 0;

if ($frontier_id === 0) {
    die('フロンティアIDが指定されていません。');
}

try {
    // フロンティア情報を取得
    $stmt = $pdo->prepare("SELECT * FROM gs_chiiki_frontier WHERE id = :id");
    $stmt->execute([':id' => $frontier_id]);
    $frontier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$frontier) {
        die('指定されたフロンティアが見つかりません。');
    }

    // 利用不可能な時間枠を取得
    $stmt = $pdo->prepare("SELECT * FROM unavailable_slots WHERE frontier_id = :frontier_id");
    $stmt->execute([':frontier_id' => $frontier_id]);
    $unavailable_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // エラーログを記録
    error_log('データベースエラー: ' . $e->getMessage());
    die('システムエラーが発生しました。管理者にお問い合わせください。');
}

// 利用不可能な時間枠を JSON 形式で JavaScript に渡す
$unavailable_slots_json = json_encode($unavailable_slots);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>体験申込 - ZOUUU</title>
    <style>
        :root {
            --primary-color: #0c344e;
            --secondary-color: #1a73e8;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #dadce0;
            --text-color: #3c4043;
            --hover-color: #e8f0fe;
        }
        body {
            font-family: 'Roboto', 'Noto Sans JP', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: var(--card-background);
            color: var(--primary-color);
            padding: 15px 0;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin-left: 20px;
        }
        nav ul li a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            transition: background-color 0.3s;
            border-radius: 4px;
        }
        nav ul li a:hover {
            background-color: var(--hover-color);
        }
        main {
            flex: 1;
        }
        .card {
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            padding: 20px;
            margin-bottom: 20px;
        }
        h1, h2, h3 {
            color: var(--primary-color);
        }
        h1 {
            text-align: center;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
        }
        .btn:hover {
            background-color: #1765cc;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            text-decoration: none;
            margin-right: 10px; /* 申し込むボタンとのスペース */
            border: none;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            text-decoration: none;
        }
        footer {
            background-color: var(--card-background);
            color: var(--primary-color);
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
        }
        footer .container {
            text-align: center;
        }
        .footer-logo {
            font-size: 1.5em;
            font-weight: bold;
        }
        .frontier-info {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .frontier-image {
            width: 150px;
            height: 150px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            margin-right: 20px;
        }
        .frontier-details {
            flex: 1;
        }
        .tag {
            display: inline-block;
            background-color: var(--hover-color);
            color: var(--secondary-color);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .calendar {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: var(--secondary-color);
            color: white;
        }
        .calendar-body {
            display: flex;
            flex-wrap: wrap;
        }
        .calendar-day {
            width: calc(100% / 7);
            aspect-ratio: 1 / 1;
            border: 1px solid var(--border-color);
            padding: 5px;
            box-sizing: border-box;
        }
        .calendar-day-header {
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
            padding-bottom: 5px;
        }
        .calendar-slots {
            display: flex;
            flex-direction: column;
            height: calc(100% - 25px);
            overflow-y: auto;
        }
        .calendar-slot {
            padding: 2px 4px;
            margin-bottom: 2px;
            font-size: 0.75rem;
            cursor: pointer;
            border-radius: 4px;
        }
        .calendar-slot:hover {
            background-color: var(--hover-color);
        }
        .calendar-slot.selected {
            background-color: var(--secondary-color);
            color: white;
        }
        .calendar-slot.unavailable {
            background-color: #f0f0f0;
            color: #999;
            cursor: not-allowed;
        }
        #selected-slots {
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .calendar-day {
                width: calc(100% / 3);
            }
        }
        @media (max-width: 480px) {
            .calendar-day {
                width: 100%;
            }
            .frontier-info {
                flex-direction: column;
            }
            .frontier-image {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
            #message-container {
                margin-top: 20px;
            }

            #user-message {
                width: 90%;
                padding: 10px;
                border: 1px solid var(--border-color);
                border-radius: 4px;
                resize: vertical;
            }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">ZOUUU</div>
            <nav>
                <ul>
                    <li><a href="chiiki_kasseika.php"><b>フロンティア一覧</b></a></li>
                    <li><a href="mypage.php"><b>マイページ</b></a></li>
                    <li><a href="logoutmypage.php"><b>ログアウト</b></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="card">
            <h1>体験申込</h1>
            <div class="frontier-info">
                <div class="frontier-image" style="background-image: url('<?php echo h($frontier['image_url']); ?>');"></div>
                <div class="frontier-details">
                    <h2><?php echo h($frontier['name']); ?></h2>
                    <p>カテゴリー: <span class="tag"><?php echo h($frontier['category']); ?></span></p>
                </div>
            </div>

            <div class="card" id="message-container">
                <h3>管理者へのメッセージ</h3>
                <textarea id="user-message" name="user-message" rows="4" placeholder="管理者へのメッセージを入力してください（必須）" required></textarea>
            </div>

            <h3>希望日時を選択してください（最大3つまで）</h3>
            <div class="calendar">
                <div class="calendar-header">
                    <button id="prev-month">&lt;</button>
                    <span id="current-month"></span>
                    <button id="next-month">&gt;</button>
                </div>
                <div class="calendar-body">
                    <!-- 日付と時間枠はJavaScriptで生成 -->
                </div>
            </div>
            <div id="selected-slots">
                <h3>選択された日時:</h3>
                <ul id="selected-slots-list"></ul>
            </div>
            
            <div class="btn-container">
                <!-- 戻るボタン -->
                <button type="button" class="btn btn-secondary" onclick="history.back();">戻る</button>

                <!-- 申し込むボタン -->
                <button type="submit" class="btn btn-primary" id="submit-booking">申し込む</button>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p class="footer-logo">ZOUUU</p>
            <small>&copy; 2024 ZOUUU. All rights reserved.</small>
        </div>
    </footer>

    <script>
    // 利用不可能な時間枠のデータを PHP から受け取る
const unavailableSlots = <?php echo $unavailable_slots_json; ?>;
const frontierID = <?php echo $frontier_id; ?>; // PHP から frontier_id を受け取る

document.addEventListener('DOMContentLoaded', function() {
    const calendarBody = document.querySelector('.calendar-body');
    const selectedSlotsList = document.getElementById('selected-slots-list');
    const currentMonthElement = document.getElementById('current-month');
    const prevMonthButton = document.getElementById('prev-month');
    const nextMonthButton = document.getElementById('next-month');
    const maxSelections = 3;
    let selectedSlots = [];
    let currentDate = new Date();

    function generateCalendar(date) {
        calendarBody.innerHTML = '';
        currentMonthElement.textContent = date.toLocaleString('ja-JP', { year: 'numeric', month: 'long' });

        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

        for (let i = 0; i < firstDay.getDay(); i++) {
            calendarBody.appendChild(createEmptyDay());
        }

        for (let i = 1; i <= lastDay.getDate(); i++) {
            calendarBody.appendChild(createDay(new Date(date.getFullYear(), date.getMonth(), i)));
        }
    }

    function createEmptyDay() {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        return dayElement;
    }

    function createDay(date) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.innerHTML = `
            <div class="calendar-day-header">${date.getDate()}</div>
            <div class="calendar-slots">
                ${generateTimeSlots(date)}
            </div>
        `;
        return dayElement;
    }

    function generateTimeSlots(date) {
        let slots = '';
        for (let hour = 9; hour < 18; hour++) {
            const time = `${hour.toString().padStart(2, '0')}:00`;
            const isUnavailable = isSlotUnavailable(date, time);
            const unavailableClass = isUnavailable ? 'unavailable' : '';
            // ローカルのタイムゾーンで日付を生成
            const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
            slots += `<div class="calendar-slot ${unavailableClass}" data-date="${localDate}" data-time="${time}">${time}</div>`;
        }
        return slots;
    }

    function isSlotUnavailable(date, time) {
        // ローカルのタイムゾーンで日付を生成
        const dateString = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        return unavailableSlots.some(slot => 
            slot.date === dateString && 
            slot.start_time <= time && 
            time < slot.end_time
        );
    }

    function updateSelectedSlotsList() {
        selectedSlotsList.innerHTML = selectedSlots.map(slot => `<li>${slot.date} ${slot.time}</li>`).join('');
    }

    calendarBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('calendar-slot') && !e.target.classList.contains('unavailable')) {
            const slotElement = e.target;
            const date = slotElement.dataset.date;
            const time = slotElement.dataset.time;
            const dateTime = { date, time };

            if (slotElement.classList.contains('selected')) {
                slotElement.classList.remove('selected');
                selectedSlots = selectedSlots.filter(slot => !(slot.date === date && slot.time === time));
            } else if (selectedSlots.length < maxSelections) {
                slotElement.classList.add('selected');
                selectedSlots.push(dateTime);
            }

            updateSelectedSlotsList();
        }
    });

    prevMonthButton.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        generateCalendar(currentDate);
    });

    nextMonthButton.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        generateCalendar(currentDate);
    });

    // 申し込みボタンのイベントリスナー
    document.getElementById('submit-booking').addEventListener('click', function() {
        if (selectedSlots.length === 0) {
            alert('希望日時を少なくとも1つ選択してください。');
            return;
        }
    
        const userMessage = document.getElementById('user-message').value.trim();
        if (userMessage === '') {
            alert('管理者へのメッセージを入力してください。');
            return;
        }

        // AJAX でサーバーに予約リクエストを送信
        fetch('process_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                frontier_id: frontierID,
                slots: selectedSlots,
                user_message: userMessage
            }),
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('予約リクエストが送信されました。');
                window.location.href = 'mypage.php';
            } else {
                alert('予約リクエストの送信に失敗しました: ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('予約リクエストの送信中にエラーが発生しました: ' + error.message);
        });
    });

    // 初期カレンダーの生成
    generateCalendar(currentDate);
});
    </script>
</body>
</html>