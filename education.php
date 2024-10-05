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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地域企業との連携で探究学習を推進する | ZOUUU</title>
    <meta name="description" content="ZOUUUの地域課題解決型探究学習コース一覧。防災・防犯対策、子育て支援、環境対策など、多様な地域課題に取り組むコースをご紹介します。">
    <link rel="icon" type="image/png" href="./img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/education2.css">

    <style>
        #about {
    background-color: #f8f9fa;
    padding: 80px 0;
    color: #333;
        }

        #about .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        #about .section-title {
            font-size: 42px;
            color: #0c344e;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 900;
        }

        #about .section-description {
            text-align: center;
            font-size: 20px;
            line-height: 1.6;
            margin-bottom: 60px;
            color: #555;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 24px;
            color: #0c344e;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .feature-card p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }

        .about-content {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 60px;
        }

        .content-block {
            flex: 1;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .content-block h3 {
            font-size: 24px;
            color: #0c344e;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .content-block ul {
            padding-left: 20px;
        }

        .content-block li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .learning-timeline {
    margin-top: 60px;
    padding: 20px 0;
}

.learning-timeline h3 {
    font-size: 28px;
    color: #0c344e;
    text-align: center;
    margin-bottom: 40px;
    font-weight: 700;
}

.timeline {
    position: relative;
    max-width: 1000px;
    margin: 0 auto;
}

.timeline::after {
    content: '';
    position: absolute;
    width: 6px;
    background-color: #0c344e;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -3px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
    box-sizing: border-box;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 25px;
    height: 25px;
    background-color: #fff;
    border: 4px solid #0c344e;
    border-radius: 50%;
    z-index: 1;
    top: 15px;
}

.timeline-item:nth-child(odd) {
    left: 0;
    padding-right: 50px;
}

.timeline-item:nth-child(even) {
    left: 50%;
    padding-left: 50px;
}

.timeline-item:nth-child(odd)::after {
    right: -16px;
}

.timeline-item:nth-child(even)::after {
    left: -16px;
}

.timeline-content {
    padding: 20px 30px;
    background-color: white;
    position: relative;
    border-radius: 6px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.timeline-content::before {
    content: '';
    position: absolute;
    top: 15px;
    width: 0;
    height: 0;
    border: medium solid #fff;
}

.timeline-item:nth-child(odd) .timeline-content::before {
    right: -15px;
    border-width: 10px 0 10px 15px;
    border-color: transparent transparent transparent #fff;
}

.timeline-item:nth-child(even) .timeline-content::before {
    left: -15px;
    border-width: 10px 15px 10px 0;
    border-color: transparent #fff transparent transparent;
}

.timeline-content h4 {
    font-size: 20px;
    color: #0c344e;
    margin-bottom: 10px;
    font-weight: 700;
}

.timeline-content p {
    font-size: 16px;
    line-height: 1.6;
    color: #555;
    margin: 0;
}

@media screen and (max-width: 768px) {
    .timeline::after {
        left: 31px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .timeline-item::after {
        left: 15px;
    }
    
    .timeline-item:nth-child(even) {
        left: 0%;
    }
    
    .timeline-item:nth-child(odd)::after,
    .timeline-item:nth-child(even)::after {
        left: 15px;
    }
    
    .timeline-item:nth-child(odd) .timeline-content::before,
    .timeline-item:nth-child(even) .timeline-content::before {
        left: -15px;
        border-width: 10px 15px 10px 0;
        border-color: transparent #fff transparent transparent;
    }
}
    </style>

</head>

<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">ZOUUU</div>
                <div class="welcome">
                    <h1>ようこそ、<?= h($user['name']) ?>さん</h1>
                </div>
               
             <ul>
                <li><a href="education.php">コース一覧</a></li>
                <li><a href="#about">ZOUUUとは</a></li>
                <li><a href="mypage.php">マイページ</a></li>
                <li><a href="logoutmypage.php">ログアウト</a></li>
            </ul>
            </nav>

        </div>
    </header>

    <section id="home" class="hero">
        <div class="container">
        <h1>地域企業との連携で探究学習を推進する<br>教育サポートプラットフォーム<br>- ZOUUU -</h1>
        <p>Inspire Learning, Cultivate Experience（学びを刺激し、体験を育む）</p>    
        <a href="#courses" class="btn">コースを探す</a>
        </div>
    </section>

    <section id="courses" class="section">
    <div class="container">
        <h2>コース一覧</h2>
        <div class="card-container">
            <a href="bousai_bohan.php" class="card-link">
                <div class="card">
                    <img src="./img/bousai_s.jpg" alt="防災・防犯対策">
                    <div class="card-content">
                        <h3>防災・防犯対策</h3>
                        <p>防災対策、防犯対策、安全対策、日常生活上の怪我防止等</p>
                    </div>
                </div>
            </a>
            
            <a href="kosodate_shien.php" class="card-link">
                <div class="card">
                    <img src="./img/kosodate_s.jpg" alt="子育て支援">
                    <div class="card-content">
                        <h3>子育て支援</h3>
                        <p>待機児童解消対策、医療・予防接種、発達支援、児童虐待防止、母子支援策（ひとり親対策）等</p>
                    </div>
                </div>
            </a>
            
            <a href="fukushi_hoken.php" class="card-link">
                <div class="card">
                    <img src="./img/fukushi_s.jpg" alt="福祉・保健衛生">
                    <div class="card-content">
                        <h3>福祉・保健衛生</h3>
                        <p>保健衛生、高齢者福祉、障害者福祉、生活福祉（低所得者等向け）等</p>
                    </div>
                </div>
            </a>

            <a href="kankyo_taisaku.php" class="card-link">
                <div class="card">
                    <img src="./img/kankyo_s.jpg" alt="環境対策">
                    <div class="card-content">
                        <h3>環境対策</h3>
                        <p>地球温暖化対策、エネルギー対策、自然環境保全（生活圏外）、環境保全対策（生活圏内）、廃棄物（ゴミ対策）等</p>
                    </div>
                </div>
            </a>
            
            <a href="chiiki_kasseika.php" class="card-link">
                <div class="card">
                    <img src="./img/chiiki_s.jpg" alt="地域活性化">
                    <div class="card-content">
                        <h3>地域活性化</h3>
                        <p>産業（商工業）振興、農林水産業振興、雇用対策、観光振興等</p>
                    </div>
                </div>
            </a>

            <a href="jinko_taisaku.php" class="card-link">
                <div class="card">
                    <img src="./img/jinko_s.jpg" alt="人口対策">
                    <div class="card-content">
                        <h3>人口対策</h3>
                        <p>人口減少対策、関係人口・交流人口対策、空き家対策等</p>
                    </div>
                </div>
            </a>
            
            <a href="bunka_shinko.php" class="card-link">
                <div class="card">
                    <img src="./img/bunka_s.jpg" alt="文化振興">
                    <div class="card-content">
                        <h3>文化振興</h3>
                        <p>文化芸術振興、文化財保護、伝統産業の活性化等</p>
                    </div>
                </div>
            </a>

            <a href="toshi_kiban.php" class="card-link">
                <div class="card">
                    <img src="./img/toshi_s.jpg" alt="都市基盤整備">
                    <div class="card-content">
                        <h3>都市基盤整備</h3>
                        <p>地区整備・再開発、道路・交通対策、公共御施設対策、都市景観整備等</p>
                    </div>
                </div>
            </a>

            <a href="kyoiku.php" class="card-link">
                <div class="card">
                    <img src="./img/kyoiku_s.jpg" alt="教育">
                    <div class="card-content">
                        <h3>教育</h3>
                        <p>施設整備、学力向上、放課後対策（見守り）、不登校対策、要支援対策等</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<section id="about" class="section">
    <div class="container">
        <h2 class="section-title">ZOUUUとは</h2>
        <p class="section-description">
            高校教員を支援し、地域企業と連携して探究学習を推進する<br>革新的なオンラインプラットフォーム
        </p>

        <div class="feature-grid">
            <div class="feature-card">
                <!-- <div class="feature-icon">📚</div> -->
                <h3>オンラインコンテンツの充実</h3>
                <p>全国の地域リーダーや企業の実際の課題を動画で学び、探究学習のテーマ設定に活用</p>
            </div>
            <div class="feature-card">
                <!-- <div class="feature-icon">🤝</div> -->
                <h3>地域企業との連携</h3>
                <p>地域企業と協力して現実の課題に取り組む体験学習の機会を提供</p>
            </div>
            <div class="feature-card">
                <!-- <div class="feature-icon">👨‍🏫</div> -->
                <h3>教員向けサポート</h3>
                <p>探究学習のカリキュラムや指導方法に関する情報を提供し、効果的な生徒サポートを実現</p>
            </div>
        </div>

        <div class="about-content">
            <div class="content-block">
                <h3>目的</h3>
                <ul>
                    <li><strong>実践的な学びの提供</strong>: 地域課題への取り組みを通じて、学びを深化</li>
                    <li><strong>多様な価値観との出会い</strong>: さまざまな価値観に触れ、社会的視野を拡大</li>
                </ul>
            </div>
            
            <div class="content-block">
                <h3>高校教員の探究学習をサポート</h3>
                <ul>
                    <li>探究学習のテーマ策定と授業方針策定のサポート</li>
                    <li>体験学習受け入れ先の選定、打診、調整のサポート</li>
                    <li>地域企業の視点を取り入れた生徒の探究学習の振り返り指導サポート</li>
                </ul>
            </div>
        </div>

        <div class="learning-timeline">
            <h3>ZOUUUを活用した学習イメージ</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>4月〜7月頃</h4>
                        <p>探究学習のテーマの検討、調査、決定、ディスカッション</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>8月〜12月頃</h4>
                        <p>課題設計、具体的施策の検討、現地見学、レポート作成</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>1月〜3月頃</h4>
                        <p>実地研修、プロジェクト実施、振り返り、成果発表</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <footer id="footer">
        <div class="container">
            <p class="footer-logo">ZOUUU</p>
            <small>&copy; 2024 ZOUUU. All rights reserved.</small>
            <div id="page-top"><a href="#" title="トップへ戻る">▲</a></div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const cards = document.querySelectorAll('.card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 1s ease forwards';
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>