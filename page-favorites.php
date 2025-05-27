<?php
/**
 * Template Name: お気に入りリスト
 *
 * @package WordPress
 */

get_header(); ?>
<div class="pankuzu">
	<?php breadcrumb(); ?>
    </div>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php if ( is_user_logged_in() ) : ?>
            
            <div class="favorites-page-header">
                <h1>お気に入り求人リスト</h1>
                
                <?php
                // JavaScriptの読み込み
                wp_enqueue_script('favorites-script', get_template_directory_uri() . '/js/favorites.js', array('jquery'), '1.0.0', true);
                wp_localize_script('favorites-script', 'favoritesAjax', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('favorites_nonce')
                ));
                ?>
            </div>

            <div id="favorites-list-container">
                <?php
                $current_user_id = get_current_user_id();
                $favorite_job_ids = get_user_meta( $current_user_id, 'user_favorites', true );

                if ( ! empty( $favorite_job_ids ) && is_array( $favorite_job_ids ) ) :
                    $args = array(
                        'post_type'      => 'job',
                        'post__in'       => $favorite_job_ids,
                        'posts_per_page' => -1,
                        'orderby'        => 'post__in',
                        'post_status'    => 'publish'
                    );
                    $favorite_jobs_query = new WP_Query( $args );

                    if ( $favorite_jobs_query->have_posts() ) :
                ?>
                        <div class="favorites-notice">
                            <p>現在<strong><?php echo count($favorite_job_ids); ?>件</strong>の求人をお気に入りしています。</p>
                        </div>
                        
                        <div class="job-cards-container">
                            <?php while ( $favorite_jobs_query->have_posts() ) : $favorite_jobs_query->the_post(); 
                                // 求人の詳細情報を取得
                                $job_id = get_the_ID();
                                $facility_name = get_post_meta($job_id, 'facility_name', true);
                                $facility_company = get_post_meta($job_id, 'facility_company', true);
                                $job_content_title = get_post_meta($job_id, 'job_content_title', true);
                                $salary_range = get_post_meta($job_id, 'salary_range', true);
                                $salary_type = get_post_meta($job_id, 'salary_type', true);
                                $facility_address = get_post_meta($job_id, 'facility_address', true);
                                
                                // タクソノミーの取得
                                $facility_types = get_the_terms($job_id, 'facility_type');
                                $job_features = get_the_terms($job_id, 'job_feature');
                                $job_types = get_the_terms($job_id, 'job_type');
                                $job_positions = get_the_terms($job_id, 'job_position');
                                
                                // 施設形態のチェック
                                $has_jidou = false;    // 児童発達支援フラグ
                                $has_houkago = false;  // 放課後等デイサービスフラグ

                                if ($facility_types && !is_wp_error($facility_types)) {
                                    foreach ($facility_types as $type) {
                                        // 組み合わせタイプのチェック
                                        if ($type->slug === 'jidou-houkago') {
                                            // 児童発達支援・放課後等デイの場合は両方表示
                                            $has_jidou = true;
                                            $has_houkago = true;
                                        } 
                                        // 児童発達支援のみのチェック
                                        else if ($type->slug === 'jidou') {
                                            $has_jidou = true;
                                        } 
                                        // 放課後等デイサービスのみのチェック
                                        else if ($type->slug === 'houkago') {
                                            $has_houkago = true;
                                        }
                                        
                                        // 従来の拡張スラッグもサポート
                                        else if (in_array($type->slug, ['jidou-hattatsu', 'jidou-hattatsu-shien', 'child-development-support'])) {
                                            $has_jidou = true;
                                        }
                                        else if (in_array($type->slug, ['houkago-day', 'houkago-dayservice', 'after-school-day-service'])) {
                                            $has_houkago = true;
                                        }
                                    }
                                }
                                
                                // 雇用形態に基づくカラークラスを設定
$employment_color_class = 'other'; // デフォルトはその他
if ($job_types && !is_wp_error($job_types)) {
    // スラッグによる判定
    $job_type_slug = $job_types[0]->slug;
    $job_type_name = $job_types[0]->name;
    
    // スラッグベースでの判定
    switch($job_type_slug) {
        case 'full-time':
        case 'seishain': // 正社員
            $employment_color_class = 'full-time';
            break;
        case 'part-time':
        case 'part':
        case 'arubaito': // パート・アルバイト
            $employment_color_class = 'part-time';
            break;
        default:
            // スラッグで判定できない場合は名前で判定
            if ($job_type_name === '正社員') {
                $employment_color_class = 'full-time';
            } else if ($job_type_name === 'パート・アルバイト' || 
                      strpos($job_type_name, 'パート') !== false || 
                      strpos($job_type_name, 'アルバイト') !== false) {
                $employment_color_class = 'part-time';
            } else {
                $employment_color_class = 'other';
            }
            break;
    }
}
                            ?>
                                <div id="favorite-item-<?php echo $job_id; ?>" class="job-card">
                                    <!-- 上部コンテンツ：左右に分割 -->
                                    <div class="job-content">
                                        <!-- 左側：サムネイル画像、施設形態アイコン、特徴タグ -->
                                        <div class="left-content">
                                            <!-- サムネイル画像 -->
                                            <div class="job-image">
                                                <?php if (has_post_thumbnail($job_id)): ?>
                                                    <?php echo get_the_post_thumbnail($job_id, 'medium'); ?>
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/300x200" alt="<?php echo esc_attr($facility_name); ?>">
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- 施設形態を画像アイコン -->
                                            <div class="facility-icons">
                                                <?php if ($has_houkago): ?>
                                                <!-- 放デイアイコン -->
                                                <div class="facility-icon">
                                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/day.webp" alt="放デイ">
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($has_jidou): ?>
                                                <!-- 児発支援アイコン -->
                                                <div class="facility-icon red-icon">
                                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/support.webp" alt="児発支援">
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- 特徴タクソノミータグ - 10つまで表示 -->
                                            <?php if ($job_features && !is_wp_error($job_features)): ?>
                                            <div class="tags-container">
                                                <?php 
                                                $features_count = 0;
                                                foreach ($job_features as $feature):
                                                    if ($features_count < 10):
                                                        // プレミアム特徴の判定
                                                        $premium_class = (in_array($feature->slug, ['high-salary', 'bonus-available'])) ? 'premium' : '';
                                                ?>
                                                    <span class="tag <?php echo $premium_class; ?>"><?php echo esc_html($feature->name); ?></span>
                                                <?php
                                                        $features_count++;
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- 右側：運営会社名、施設名、本文詳細 -->
                                        <div class="right-content">
                                            <!-- 会社名と雇用形態を横に並べる -->
                                            <div class="company-section">
                                                <span class="company-name"><?php echo esc_html($facility_company); ?></span>
                                                <?php if ($job_types && !is_wp_error($job_types)): ?>
                                                <div class="employment-type <?php echo $employment_color_class; ?>">
                                                    <?php echo esc_html($job_types[0]->name); ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- 施設名を会社名の下に配置 -->
                                            <h1 class="job-title">
                                                    <?php echo esc_html($facility_name); ?>
                                            </h1>
                                            
                                            <h2 class="job-subtitle"><?php echo esc_html($job_content_title); ?></h2>
                                            
                                            <p class="job-description">
                                                <?php echo wp_trim_words(get_the_content(), 40, '...'); ?>
                                            </p>
                                            
                                            <!-- 本文の下に区切り線を追加 -->
                                            <div class="divider"></div>
                                            
                                            <!-- 職種、給料、住所情報 -->
                                            <div class="job-info">
                                                <?php if ($job_positions && !is_wp_error($job_positions)): ?>
                                                <div class="info-item">
                                                    <span class="info-icon"><i class="fa-solid fa-user"></i></span>
                                                    <span><?php echo esc_html($job_positions[0]->name); ?></span>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="info-item">
                                                    <span class="info-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
                                                    <span>
                                                        <?php 
                                                            // 賃金形態の表示（月給/時給）
                                                            if ($salary_type === 'MONTH') {
                                                                echo '月給 ';
                                                            } elseif ($salary_type === 'HOUR') {
                                                                echo '時給 ';
                                                            }
                                                            
                                                            echo esc_html($salary_range);
                                                            
                                                            // 円表示がなければ追加
                                                            if ($salary_range && mb_strpos($salary_range, '円') === false) {
                                                                echo '円';
                                                            }
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="info-item">
    <span class="info-icon"><i class="fa-solid fa-location-dot"></i></span>
    <span>
        <?php 
            $facility_address = get_post_meta(get_the_ID(), 'facility_address', true);
            
            // 郵便番号部分を削除する（〒123-4567などのパターンを検出）
            $address_without_postal = preg_replace('/〒?\s*\d{3}-\d{4}\s*/u', '', $facility_address);
            
            // 郵便番号を除いた住所を表示
            echo esc_html($address_without_postal);
        ?>
    </span>
</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 区切り線 -->
                                    <div class="divider"></div>
                                    
                                    <!-- ボタンエリア -->
<div class="buttons-container">
    <button class="keep-button kept" data-job-id="<?php echo $job_id; ?>">
        <span class="star"></span>
        お気に入り済み
    </button>
    
    <a href="<?php the_permalink(); ?>" class="detail-view-button">詳細をみる</a>
</div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                <?php
                        wp_reset_postdata();
                    else :
                        echo '<div class="no-favorites">';
                        echo '<h2>お気に入りの求人がありません</h2>';
                        echo '<p>気になる求人をお気に入りに追加してリストを作成しましょう。</p>';
                        echo '<a href="' . home_url('/jobs/') . '" class="search-jobs-btn">求人を検索する</a>';
                        echo '</div>';
                    endif;
                else :
                    echo '<div class="no-favorites">';
                    echo '<h2>お気に入りの求人がありません</h2>';
                    echo '<p>気になる求人をお気に入りに追加してリストを作成しましょう。</p>';
                    echo '<a href="' . home_url('/jobs/') . '" class="search-jobs-btn">求人を検索する</a>';
                    echo '</div>';
                endif;
                ?>
            </div>

            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // キープボタンのクリック処理
                $('.keep-button').on('click', function() {
                    var button = $(this);
                    var jobId = button.data('job-id');
                    
                    if (!confirm('この求人をお気に入りから削除しますか？')) {
                        return;
                    }
                    
                    $.ajax({
                        url: favoritesAjax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'toggle_job_favorite',
                            job_id: jobId,
                            nonce: favoritesAjax.nonce
                        },
                        beforeSend: function() {
                            button.prop('disabled', true).css('opacity', '0.5');
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#favorite-item-' + jobId).fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // リストが空になった場合のメッセージ表示
                                    if ($('.job-card').length === 0) {
                                        $('#favorites-list-container').html(
                                            '<div class="no-favorites">' +
                                            '<h2>お気に入りの求人がありません</h2>' +
                                            '<p>気になる求人をお気に入りに追加してリストを作成しましょう。</p>' +
                                            '<a href="<?php echo home_url('/jobs/'); ?>" class="search-jobs-btn">求人を検索する</a>' +
                                            '</div>'
                                        );
                                    }
                                    
                                    // 件数を更新
                                    var currentCount = $('.favorites-notice strong').text();
                                    var newCount = parseInt(currentCount) - 1;
                                    if (newCount > 0) {
                                        $('.favorites-notice strong').text(newCount);
                                    } else {
                                        $('.favorites-notice').hide();
                                    }
                                });
                            } else {
                                alert('エラー: ' + response.data.message);
                                button.prop('disabled', false).css('opacity', '1');
                            }
                        },
                        error: function() {
                            alert('通信エラーが発生しました。');
                            button.prop('disabled', false).css('opacity', '1');
                        }
                    });
                });
            });
            </script>

        <?php else : ?>
            <div class="login-required">
                <h2>お気に入り求人リストを見るにはログインが必要です</h2>
                <p>お気に入りの求人を保存してマイページで管理できます。</p>
                <div class="login-buttons">
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="login-btn">ログイン</a>
                    <a href="<?php echo home_url('/register/'); ?>" class="register-btn">新規会員登録</a>
                </div>
            </div>
    
            <style>
            .login-required {
                text-align: center;
                padding: 60px 20px;
                background: #f9f9f9;
                border-radius: 8px;
                max-width: 1000px;
                margin: 0 auto;
            }
            
            .login-required h2 {
                font-size: 24px;
                margin-bottom: 15px;
                color: #333;
            }
            
            .login-required p {
                font-size: 16px;
                margin-bottom: 25px;
                color: #666;
            }
            
            .login-buttons {
                display: flex;
                justify-content: center;
                gap: 20px;
            }
            
            .login-btn, .register-btn {
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: bold;
                display: inline-block;
                transition: background 0.2s;
            }
            
            .login-btn {
                background: #2196F3;
                color: white;
            }
            
            .register-btn {
                background: #fff;
                color: #2196F3;
                border: 1px solid #2196F3;
            }
            
            .login-btn:hover {
                background: #1976d2;
            }
            
            .register-btn:hover {
                background: #f0f0f0;
            }
            </style>
        <?php endif; ?>

    </main>
</div>

<?php get_footer(); ?>