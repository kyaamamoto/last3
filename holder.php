<?php
require_once 'session_config.php';
require_once 'security_headers.php';
require_once 'funcs.php';

// ログインチェック
loginCheck();

// ユーザー情報の取得
$user = getUserInfo($_SESSION['user_id']);

// データベース接続
$pdo = db_conn();

// 最新のデータを取得する関数
function fetchLatestData($pdo, $table, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = :user_id ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 値を日本語に変換する関数
function translateValue($key, $value) {
    $translations = [
        'place_of_residence' => [
            'never' => '居住経験なし',
            'past' => '過去に居住',
            'current' => '現在居住中'
        ],
        'visit_frequency' => [
            'never' => '訪れたことがない',
            'rarely' => 'ほとんど訪れない',
            'yearly' => '年に1回程度',
            'several_times_a_year' => '年に数回',
            'monthly' => '月に1回程度',
            'weekly' => '週に1回程度',
            'daily' => 'ほぼ毎日'
        ],
        'stay_duration' => [
            'day_trip' => '日帰り',
            'short_stay' => '1〜3日程度の短期滞在',
            'medium_stay' => '1週間〜1ヶ月程度の中期滞在',
            'long_stay' => '1ヶ月以上の長期滞在'
        ],
        'donation_count' => [
            'once' => '1回',
            'twice' => '2回',
            'three_times' => '3回',
            'four_or_more_times' => '4回以上'
        ]
    ];

    return $translations[$key][$value] ?? $value;
}

// 最新のデータを取得
$past_data = fetchLatestData($pdo, 'past_involvement', $_SESSION['user_id']);

// ランクの計算
function calculateRank($past_data) {
    $points = 0;
    if ($past_data['travel_experience'] === 'yes') $points += 1;
    if ($past_data['volunteer_experience'] === 'yes') $points += 2;
    if ($past_data['donation_experience'] === 'yes') $points += 2;
    if ($past_data['product_purchase'] === 'yes') $points += 1;
    if ($past_data['work_experience'] === 'yes') $points += 3;

    if ($points >= 8) return '大将軍';
    if ($points >= 6) return '将軍';
    if ($points >= 4) return '千人将';
    if ($points >= 2) return '百人隊長';
    return '一兵卒';
}

$rank = calculateRank($past_data);

// ランクに応じたメッセージ
$rank_messages = [
    '一兵卒' => '地域との関わりを始めたばかりの初心者。これから地域を深く知っていきましょう！あなたの関わりをお待ちしています。',
    '百人隊長' => '地域の活動に主体的に取り組めます。地域の魅力をどんどん発信していってください。期待しています！',
    '千人将' => '多くの仲間とともに地域の魅力を発信いただきありがとうございます。地域の良さをもっともっと知っていただき、地域の人との関わりを積極的に取ってください。あなたの行動が地域を変えます！',
    '将軍' => '地域の未来を一緒に考えていただける存在です。ともに考え、ともに成長していきましょう。もうあなたは地域のリーダーですよ！',
    '大将軍' => '地域の発展に貢献していただいています。あなたの活動が地域の未来に、そして子どもたちの未来につながります。より良い地域を目指し、ともに次世代に繋げていきましょう。'
];

// 申請状況の取得
$applicationStatus = getApplicationStatus($user['id']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ふるさとID マイページ ダッシュボード</title>
    <link rel="icon" type="image/png" href="https://zouuu.sakura.ne.jp/zouuu/img/IDfavicon.ico">
    <link rel="stylesheet" href="./css/styleholder.css">
    <style>
        .rank-badge {
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .一兵卒 { background-color: #C0C0C0; }
        .百人隊長 { background-color: #ffd900; }
        .千人将 { background-color: #e60012; }
        .将軍 { background-color: #2ca9e1; }
        .大将軍 { background-color: #cc00ff; }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="holder.php"><img src="https://zouuu.sakura.ne.jp/zouuu/img/fIDLogo.png" alt="ふるさとID ロゴ"></a>
        </div>
        <nav>
            <ul>
                <li><a href="skill_check.php">ふるさとID申請</a></li>
                <li><a href="#">ふるさとID活動記録</a></li>
                <li><a href="#">ふるさと×ウェルビーイング</a></li>
                <li><a href="logoutmypage.php">ログアウト</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="dashboard">
            <div class="welcome">
                <h1>ようこそ、<?= h($user['name']) ?>さん</h1>
            </div>
            <div class="status">
                <h2>あなたのふるさとIDランク</h2>
                <div class="rank-badge <?= h($rank) ?>">
                    <?= h($rank) ?>
                </div>
                <p><?= h($rank_messages[$rank]) ?></p>
            </div>
            <div class="status">
                <h2>ふるさとID 活動状況</h2>
                <div class="id_block">
                    <h3>過去の地域との関わり</h3>
                    <?php if ($past_data): ?>
                        <p>出身地である: <?= h($past_data['birthplace'] === 'yes' ? 'はい' : 'いいえ') ?></p>
                        <p>居住地である: <?= h(translateValue('place_of_residence', $past_data['place_of_residence'])) ?></p>
                        <p>旅行経験: <?= h($past_data['travel_experience'] === 'yes' ? 'あり' : 'なし') ?></p>
                        <p>訪問頻度: <?= h(translateValue('visit_frequency', $past_data['visit_frequency'])) ?></p>
                        <p>滞在期間: <?= h(translateValue('stay_duration', $past_data['stay_duration'])) ?></p>
                        <p>ボランティア経験: <?= h($past_data['volunteer_experience'] === 'yes' ? 'あり' : 'なし') ?></p>
                        <?php if ($past_data['volunteer_experience'] === 'yes'): ?>
                            <p>ボランティア活動内容: <?= h(implode(', ', json_decode($past_data['volunteer_activity'], true))) ?></p>
                            <p>ボランティア頻度: <?= h(translateValue('visit_frequency', $past_data['volunteer_frequency'])) ?></p>
                        <?php endif; ?>
                        <p>ふるさと納税経験: <?= h($past_data['donation_experience'] === 'yes' ? 'あり' : 'なし') ?></p>
                        <?php if ($past_data['donation_experience'] === 'yes'): ?>
                            <p>寄付回数: <?= h(translateValue('donation_count', $past_data['donation_count'])) ?></p>
                            <p>寄付理由: <?= h($past_data['donation_reason']) ?></p>
                        <?php endif; ?>
                        <p>物産品購入経験: <?= h($past_data['product_purchase'] === 'yes' ? 'あり' : 'なし') ?></p>
                        <?php if ($past_data['product_purchase'] === 'yes'): ?>
                            <p>購入頻度: <?= h(translateValue('visit_frequency', $past_data['purchase_frequency'])) ?></p>
                            <p>購入理由: <?= h($past_data['purchase_reason']) ?></p>
                        <?php endif; ?>
                        <p>仕事での関わり: <?= h($past_data['work_experience'] === 'yes' ? 'あり' : 'なし') ?></p>
                        <?php if ($past_data['work_experience'] === 'yes'): ?>
                            <p>仕事の種類: <?= h(implode(', ', json_decode($past_data['work_type'], true))) ?></p>
                            <p>仕事での関わり頻度: <?= h(translateValue('visit_frequency', $past_data['work_frequency'])) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>過去の関わりデータが未登録です。</p>
                    <?php endif; ?>
                    <a href="confirmation.php" class="btn">活動状況の確認・修正</a>
                </div>
            </div>
            <div class="status">
                <h2>ふるさと×ウェルビーイング</h2>
                <div class="id_block">
                    <!-- ここにグラフを入れる -->
                    <p>準備中</p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 ふるさとID. All rights reserved.</p>
    </footer>
</body>
</html>