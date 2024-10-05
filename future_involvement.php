<?php
session_start();
require_once 'funcs.php';
require_once 'session_config.php';

loginCheck();

$pdo = db_conn();
$user_id = $_SESSION['user_id'];

// 最新のデータを取得する関数
function getLatestFutureInvolvement($pdo, $user_id) {
    $sql = "SELECT * FROM future_involvement 
            WHERE user_id = :user_id 
            ORDER BY updated_at DESC 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 初期データ取得（データベースから最新のデータ）
try {
    $user_involvement = getLatestFutureInvolvement($pdo, $user_id);
    if ($user_involvement) {
        saveToSession('future_data', $user_involvement);
    }
} catch (PDOException $e) {
    error_log("Database error in future_involvement.php: " . $e->getMessage());
    $_SESSION['error_message'] = "データの取得中にエラーが発生しました: " . $e->getMessage();
    redirect('holder.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateToken($_POST['csrf_token']);

    // POSTデータを取得し、サニタイズ
    $future_data = [
        'interest_furusato_tax' => filter_input(INPUT_POST, 'interest_furusato_tax', FILTER_SANITIZE_NUMBER_INT),
        'interest_local_events' => filter_input(INPUT_POST, 'interest_local_events', FILTER_SANITIZE_NUMBER_INT),
        'interest_volunteer' => filter_input(INPUT_POST, 'interest_volunteer', FILTER_SANITIZE_NUMBER_INT),
        'interest_local_products' => filter_input(INPUT_POST, 'interest_local_products', FILTER_SANITIZE_NUMBER_INT),
        'interest_relocation' => filter_input(INPUT_POST, 'interest_relocation', FILTER_SANITIZE_NUMBER_INT),
        'interest_business_support' => filter_input(INPUT_POST, 'interest_business_support', FILTER_SANITIZE_NUMBER_INT),
        'interest_startup' => filter_input(INPUT_POST, 'interest_startup', FILTER_SANITIZE_NUMBER_INT),
        'interest_employment' => filter_input(INPUT_POST, 'interest_employment', FILTER_SANITIZE_NUMBER_INT),
        'interest_other' => filter_input(INPUT_POST, 'interest_other', FILTER_SANITIZE_STRING)
    ];

    // セッションにデータを保存
    saveToSession('future_data', $future_data);

    // データベースに保存または更新
    try {
        $columns = implode(', ', array_keys($future_data));
        $placeholders = ':' . implode(', :', array_keys($future_data));
        $updates = [];
        foreach (array_keys($future_data) as $key) {
            $updates[] = "$key = VALUES($key)";
        }
        $updateString = implode(', ', $updates);

        $sql = "INSERT INTO future_involvement (user_id, $columns, created_at, updated_at) 
                VALUES (:user_id, $placeholders, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                $updateString, 
                updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        foreach ($future_data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            $_SESSION['success_message'] = "今後の関わりの情報が更新されました。";
            redirect('confirmation.php');
        } else {
            throw new Exception("データの保存に失敗しました。");
        }
    } catch (PDOException $e) {
        error_log("Database error in future_involvement.php: " . $e->getMessage());
        $_SESSION['error_message'] = "データベースエラー: " . $e->getMessage();
        redirect('holder.php');
    } catch (Exception $e) {
        error_log("Error in future_involvement.php: " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        redirect('holder.php');
    }
}

$csrf_token = generateToken();

// 以下、HTMLの部分
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>今後の地域との関わり方 - ふるさとID</title>
    <link rel="icon" type="image/png" href="https://zouuu.sakura.ne.jp/zouuu/img/IDfavicon.ico">
    <link rel="stylesheet" href="./css/styleholder.css">
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
        <h2>今後の地域との関わり方について、あなたの興味関心の度合いを教えてください</h2>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error'>" . h($_SESSION['error_message']) . "</p>";
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message'])) {
            echo "<p class='success'>" . h($_SESSION['success_message']) . "</p>";
            unset($_SESSION['success_message']);
        }
        ?>
        <form action="future_involvement.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

            <?php
            $interests = [
                'furusato_tax' => 'ふるさと納税',
                'local_events' => '地域イベントへの参加',
                'volunteer' => '地域でのボランティア活動',
                'local_products' => '地域物産品の購入',
                'relocation' => '移住や長期滞在',
                'business_support' => '地域ビジネスの支援',
                'startup' => '地域での起業',
                'employment' => '地域での就職・転職'
            ];

            foreach ($interests as $key => $label) {
                echo "<h3>" . h($label) . "</h3>";
                for ($i = 1; $i <= 5; $i++) {
                    $checked = ($user_involvement["interest_$key"] ?? '') == $i ? 'checked' : '';
                    echo "<label><input type='radio' name='interest_$key' value='$i' $checked required> $i - " . 
                         h(getInterestLabel($i)) . "</label><br>";
                }
                echo "<br>";
            }

            function getInterestLabel($value) {
                switch ($value) {
                    case 1: return "全く関心がない";
                    case 2: return "あまり関心がない";
                    case 3: return "普通";
                    case 4: return "関心がある";
                    case 5: return "非常に関心がある";
                    default: return "";
                }
            }
            ?>

            <h3>その他</h3>
            <label>自由記述:</label><br>
            <textarea name="interest_other" rows="4" cols="50" placeholder="具体的な内容を記入してください"><?php echo h($user_involvement['interest_other'] ?? ''); ?></textarea>
            <br><br>

            <input type="submit" value="次へ">
        </form>
    </main>

    <footer>
        <p>&copy; 2024 ふるさとID. All rights reserved.</p>
    </footer>
</body>
</html>