<?php
// PHPが正しく動作しているか確認
echo "PHP is working<br>";

// $jsonDataの内容を確認
$jsonData = json_encode([
    "interest_furusato_tax" => ["1" => 1, "2" => 0, "3" => 0, "4" => 0, "5" => 4],
    "interest_local_events" => ["1" => 1, "2" => 0, "3" => 0, "4" => 0, "5" => 4],
    "interest_volunteer" => ["1" => 1, "2" => 0, "3" => 1, "4" => 1, "5" => 2],
    "interest_local_products" => ["1" => 1, "2" => 1, "3" => 0, "4" => 1, "5" => 2],
    "interest_relocation" => ["1" => 1, "2" => 0, "3" => 1, "4" => 2, "5" => 1],
    "interest_business_support" => ["1" => 2, "2" => 0, "3" => 0, "4" => 1, "5" => 2],
    "interest_startup" => ["1" => 1, "2" => 0, "3" => 0, "4" => 1, "5" => 5],
    "interest_employment" => ["1" => 1, "2" => 0, "3" => 2, "4" => 0, "5" => 2]
]);
echo "JSON Data: " . $jsonData . "<br>";
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グラフデバッグ</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #chartContainer {
            width: 80%;
            height: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <h1>グラフデバッグ</h1>
    <div id="debugInfo" style="background-color: #f0f0f0; padding: 10px; margin-bottom: 20px;"></div>
    <div id="chartContainer">
        <canvas id="futureInvolvementChart"></canvas>
    </div>

    <script>
function displayDebugInfo(message) {
    const debugElement = document.getElementById('debugInfo');
    if (debugElement) {
        debugElement.innerHTML += message + '<br>';
    }
    console.log(message);
}

try {
    displayDebugInfo('デバッグ開始');

    const futureData = <?php echo $jsonData; ?>;
    displayDebugInfo('データ読み込み完了');
    displayDebugInfo('データ内容: ' + JSON.stringify(futureData));

    const labels = [
        'ふるさと納税', '地域イベント', 'ボランティア', '地域産品', 
        '移住', 'ビジネス支援', 'スタートアップ', '雇用'
    ];

    const keyMapping = {
        'ふるさと納税': 'interest_furusato_tax',
        '地域イベント': 'interest_local_events',
        'ボランティア': 'interest_volunteer',
        '地域産品': 'interest_local_products',
        '移住': 'interest_relocation',
        'ビジネス支援': 'interest_business_support',
        'スタートアップ': 'interest_startup',
        '雇用': 'interest_employment'
    };

    const interestLevels = ['全く関心がない', 'あまり関心がない', '普通', '関心がある', '非常に関心がある'];
    const colors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(255, 205, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(54, 162, 235, 0.7)'
    ];

    const datasets = interestLevels.map((level, index) => {
        const data = labels.map(label => {
            const key = keyMapping[label];
            const value = futureData[key] ? parseInt(futureData[key][(index + 1).toString()]) || 0 : 0;
            displayDebugInfo(`${label} - ${level}: ${value} (Key: ${key}, Index: ${index + 1})`);
            return value;
        });
        return {
            label: level,
            data: data,
            backgroundColor: colors[index],
        };
    });

    displayDebugInfo('データセット作成完了');
    displayDebugInfo('データセット内容: ' + JSON.stringify(datasets));

    const ctx = document.getElementById('futureInvolvementChart');
    if (!ctx) {
        throw new Error('Canvas element not found');
    }
    displayDebugInfo('Canvas要素を取得');

    if (typeof Chart === 'undefined') {
        throw new Error('Chart.js is not loaded');
    }
    displayDebugInfo('Chart.jsが読み込まれています');

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            scales: {
                x: { 
                    stacked: true,
                    title: {
                        display: true,
                        text: '関心分野'
                    }
                },
                y: { 
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'ユーザー数'
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                title: {
                    display: true,
                    text: '将来の地域との関わり方に対する関心度',
                    font: {
                        size: 18
                    },
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y}人`;
                        }
                    }
                }
            }
        }
    });

    displayDebugInfo('グラフ描画完了');
    displayDebugInfo('グラフオブジェクト: ' + (chart ? 'Created successfully' : 'Failed to create'));
} catch (error) {
    displayDebugInfo('エラーが発生しました: ' + error.message);
    console.error(error);
}
</script>

</body>
</html>