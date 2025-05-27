<?php
/*
Template Name: 求人応募ページ
*/

// 求人IDを取得
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// 求人情報を取得
$facility_name = '';
$job_position = '';
$job_type = '';

if ($job_id) {
    $job_post = get_post($job_id);
    if ($job_post && $job_post->post_type === 'job') {
        $facility_name = get_post_meta($job_id, 'facility_name', true);
        $job_position_terms = wp_get_object_terms($job_id, 'job_position', array('fields' => 'names'));
        $job_type_terms = wp_get_object_terms($job_id, 'job_type', array('fields' => 'names'));
        $job_position = !empty($job_position_terms) ? $job_position_terms[0] : '';
        $job_type = !empty($job_type_terms) ? $job_type_terms[0] : '';
    }
}

// ユーザー情報を取得（ログインしている場合）
$user_data = array();
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $user_data = array(
        'oname' => get_user_meta($current_user->ID, 'oname', true),
        'tel' => get_user_meta($current_user->ID, 'tel', true),
        'seibetu' => get_user_meta($current_user->ID, 'seibetu', true),
        'age' => get_user_meta($current_user->ID, 'age', true),
        'postcode' => get_user_meta($current_user->ID, 'postcode', true),
        'prefectures' => get_user_meta($current_user->ID, 'prefectures', true),
        'municipalities' => get_user_meta($current_user->ID, 'municipalities', true),
        'streetaddress' => get_user_meta($current_user->ID, 'streetaddress', true),
        'Desiredtime' => get_user_meta($current_user->ID, 'Desiredtime', true),
        'years' => get_user_meta($current_user->ID, 'years', true),
        'qualification' => get_user_meta($current_user->ID, 'qualification', true),
        'user_email' => $current_user->user_email
    );
}

get_header();
?>

<div class="apply-page">
    <div class="apply-container">
        <h1>求人応募フォーム</h1>
        
        <?php if ($job_id && !empty($facility_name)): ?>
        <div class="job-info-summary">
            <h2>応募求人情報</h2>
            <div class="job-detas">
                <p><strong>施設名：</strong><?php echo esc_html($facility_name); ?></p>
                <p><strong>職種：</strong><?php echo esc_html($job_position); ?></p>
                <p><strong>雇用形態：</strong><?php echo esc_html($job_type); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (is_user_logged_in()): ?>
        <div class="logged-in-notice">
            <p><span class="notice-icon">ℹ</span> 会員情報から自動入力されます。必要に応じて修正してください。</p>
        </div>
        <?php else: ?>
        <div class="not-logged-in-notice">
            <p><span class="notice-icon">!</span> <a href="/login/">ログイン</a>すると、会員情報が自動入力されます。</p>
        </div>
        <?php endif; ?>
        
        <!-- Contact Form 7 フォームを表示 -->
        <?php echo do_shortcode('[contact-form-7 id="f77f7df" title="求人応募フォーム"]'); ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 求人情報を自動入力
    var jobData = {
        facility: '<?php echo esc_js($facility_name); ?>',
        position: '<?php echo esc_js($job_position); ?>',
        type: '<?php echo esc_js($job_type); ?>'
    };
    
    // ユーザー情報を自動入力
    var userData = <?php echo json_encode($user_data); ?>;
    
    // プレースホルダーを設定する関数
    function setPlaceholders() {
        var placeholders = {
            'full_name': 'お名前を入力してください',
            'age': '年齢を入力してください',
            'postcode': '例: 123-4567',
            'prefecture': '都道府県を入力してください',
            'municipality': '市区町村を入力してください',
            'street_address': '番地・建物名を入力してください',
            'phone_number': '例: 03-1234-5678',
            'email_address': 'example@email.com',
            'memo': 'その他ご連絡事項がございましたらご記入ください'
        };
        
        $.each(placeholders, function(name, placeholder) {
            var $input = $('input[name="' + name + '"], textarea[name="' + name + '"]');
            if ($input.length) {
                $input.attr('placeholder', placeholder);
            }
        });
    }
    
    // フォーム要素の値をチェックしてプレースホルダーの表示を制御
    function updatePlaceholders() {
        $('input, textarea').each(function() {
            if ($(this).val()) {
                $(this).attr('placeholder', '');
            }
        });
    }
    
    // 遅延実行でフォーム要素が確実に読み込まれた後に処理
    setTimeout(function() {
        // プレースホルダーを設定
        setPlaceholders();
        
        // 求人情報の自動入力
        if ($('input[name="job_facility"]').length && jobData.facility) {
            $('input[name="job_facility"]').val(jobData.facility);
        }
        if ($('input[name="job_position"]').length && jobData.position) {
            $('input[name="job_position"]').val(jobData.position);
        }
        if ($('input[name="job_type"]').length && jobData.type) {
            $('input[name="job_type"]').val(jobData.type);
        }
        
        // ユーザー情報の自動入力
        if (userData.Desiredtime && $('select[name="desired_time"]').length) {
            $('select[name="desired_time"]').val(userData.Desiredtime);
        }
        if (userData.years && $('select[name="industry_years"]').length) {
            $('select[name="industry_years"]').val(userData.years);
        }
        if (userData.qualification && $('select[name="qualification"]').length) {
            $('select[name="qualification"]').val(userData.qualification);
        }
        if (userData.oname && $('input[name="full_name"]').length) {
            $('input[name="full_name"]').val(userData.oname);
        }
        if (userData.seibetu && $('select[name="gender"]').length) {
            $('select[name="gender"]').val(userData.seibetu);
        }
        if (userData.age && $('input[name="age"]').length) {
            $('input[name="age"]').val(userData.age);
        }
        if (userData.postcode && $('input[name="postcode"]').length) {
            $('input[name="postcode"]').val(userData.postcode);
        }
        if (userData.prefectures && $('input[name="prefecture"]').length) {
            $('input[name="prefecture"]').val(userData.prefectures);
        }
        if (userData.municipalities && $('input[name="municipality"]').length) {
            $('input[name="municipality"]').val(userData.municipalities);
        }
        if (userData.streetaddress && $('input[name="street_address"]').length) {
            $('input[name="street_address"]').val(userData.streetaddress);
        }
        if (userData.tel && $('input[name="phone_number"]').length) {
            $('input[name="phone_number"]').val(userData.tel);
        }
        if (userData.user_email && $('input[name="email_address"]').length) {
            $('input[name="email_address"]').val(userData.user_email);
        }
        
        // 値が入っているフィールドのプレースホルダーを非表示
        updatePlaceholders();
        
        // 入力イベント監視
        $('input, textarea').on('input', function() {
            if ($(this).val()) {
                $(this).attr('placeholder', '');
            } else {
                // 元のプレースホルダーを復元
                var name = $(this).attr('name');
                var placeholders = {
                    'full_name': 'お名前を入力してください',
                    'age': '年齢を入力してください',
                    'postcode': '例: 123-4567',
                    'prefecture': '都道府県を入力してください',
                    'municipality': '市区町村を入力してください',
                    'street_address': '番地・建物名を入力してください',
                    'phone_number': '例: 03-1234-5678',
                    'email_address': 'example@email.com',
                    'memo': 'その他ご連絡事項がございましたらご記入ください'
                };
                if (placeholders[name]) {
                    $(this).attr('placeholder', placeholders[name]);
                }
            }
        });
        
    }, 1000); // 1秒遅延にして確実に読み込み
});
</script>

<style>
.apply-page .wpcf7-form input[type="submit"] {
    width: auto; 
    min-width: 200px;
    display: block;
    margin: 0 auto 10px auto;
}

	
.apply-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.apply-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.apply-container h1 {
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-size: 24px;
}

.job-info-summary {
    background-color: #f8f9fa;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #26b7a0;
}

.job-info-summary h2 {
    color: #26b7a0;
    font-size: 18px;
    margin-bottom: 15px;
}

.job-detas p {
    margin: 8px 0;
    color: #333;
}

.logged-in-notice,
.not-logged-in-notice {
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.logged-in-notice {
    background-color: #e8f5e9;
    border-left: 4px solid #4caf50;
    color: #2e7d32;
}

.not-logged-in-notice {
    background-color: #fff3e0;
    border-left: 4px solid #ff9800;
    color: #e65100;
}

.notice-icon {
    font-weight: bold;
    margin-right: 10px;
    font-size: 18px;
}

.not-logged-in-notice a {
    color: #e65100;
    text-decoration: underline;
}

.not-logged-in-notice a:hover {
    color: #bf360c;
}

/* Contact Form 7 カスタマイズ - このページ専用 */
.apply-page .wpcf7 {
    max-width: 100%;
    margin: 0 !important;
    background-color: transparent !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
    padding: 0 !important;
}

.apply-page .wpcf7-form {
    max-width: 100%;
}

.apply-page .wpcf7-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.apply-page .wpcf7-form .required {
    color: #e74c3c;
    margin-left: 5px;
}

.apply-page .wpcf7-form input[type="text"],
.apply-page .wpcf7-form input[type="email"],
.apply-page .wpcf7-form input[type="tel"],
.apply-page .wpcf7-form input[type="number"],
.apply-page .wpcf7-form textarea,
.apply-page .wpcf7-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 14px;
}

.apply-page .wpcf7-form input[readonly] {
    background-color: #f5f5f5;
    color: #666;
}

/* プレースホルダーのスタイル - このページ専用 */
.apply-page .wpcf7-form input::placeholder,
.apply-page .wpcf7-form textarea::placeholder {
    color: #ccc;
    font-style: italic;
}

.apply-page .wpcf7-form input::-webkit-input-placeholder {
    color: #ccc;
    font-style: italic;
}

.apply-page .wpcf7-form input::-moz-placeholder {
    color: #ccc;
    opacity: 1;
    font-style: italic;
}

.apply-page .wpcf7-form input:-ms-input-placeholder {
    color: #ccc;
    font-style: italic;
}

.apply-page .wpcf7-form textarea {
    resize: vertical;
    min-height: 100px;
}

.apply-page .wpcf7-form input[type="submit"] {
    background-color: #26b7a0;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    width: 100%;
    transition: background-color 0.3s;
}

.apply-page .wpcf7-form input[type="submit"]:hover {
    background-color: #1f9688;
}

/* エラーメッセージのスタイル - このページ専用 */
.apply-page .wpcf7-validation-errors {
    background-color: #ffeaa7;
    border: 1px solid #f1c40f;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.apply-page .wpcf7-not-valid-tip {
    font-size: 12px;
    color: #e74c3c;
    display: block;
    margin-top: 3px;
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
    .apply-page {
        padding: 10px;
    }
    
    .apply-container {
        padding: 20px;
    }
    
    .apply-container h1 {
        font-size: 20px;
    }
}
</style>

<?php get_footer(); ?>