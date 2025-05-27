<?php
/**
 * Template Name: 加盟教室用の募集求人一覧ページ
 * 
 * ログインユーザーが投稿した求人を一覧表示するテンプレート
 */

// 専用のヘッダーを読み込み
include(get_stylesheet_directory() . '/agency-header.php');

// ログインチェック
if (!is_user_logged_in()) {
    // 非ログインの場合はログインページにリダイレクト
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// 現在のユーザー情報を取得
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// ユーザーが加盟教室（agency）の権限を持っているかチェック
$is_agency = in_array('agency', $current_user->roles);
if (!$is_agency && !current_user_can('administrator')) {
    // 権限がない場合はエラーメッセージ表示
    echo '<div class="error-message">この機能を利用する権限がありません。</div>';
    include(get_stylesheet_directory() . '/agency-footer.php');
    exit;
}
?>

<div class="job-list-container">
    <h1 class="page-title">求人情報管理</h1>
    
    <div class="job-action-buttons">
        <a href="<?php echo home_url('/post-job/'); ?>" class="btn-new-job">新しい求人を投稿</a>
    </div>
    
    <?php
    // ステータスメッセージの表示
    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        if ($status === 'published') {
            echo '<div class="status-message success">求人を公開しました。</div>';
        } elseif ($status === 'drafted') {
            echo '<div class="status-message info">求人を下書きに変更しました。</div>';
        } elseif ($status === 'deleted') {
            echo '<div class="status-message warning">求人を削除しました。</div>';
        }
    }
    
    // 求人投稿の取得
    $args = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'author' => $current_user_id,
        'post_status' => array('publish', 'draft', 'pending')
    );
    
    // 管理者の場合は全ての投稿を表示
    if (current_user_can('administrator')) {
        unset($args['author']);
    }
    
    $job_query = new WP_Query($args);
    
    if ($job_query->have_posts()) :
    ?>
    
    <div class="job-lis">
        <div class="job-list-header">
            <div class="job-header-item job-title-header">求人タイトル</div>
            <div class="job-header-item job-status-header">ステータス</div>
            <div class="job-header-item job-date-header">最終更新日</div>
            <div class="job-header-item job-actions-header">操作</div>
        </div>
        
        <?php while ($job_query->have_posts()) : $job_query->the_post(); ?>
        
        <div class="job-list-item">
            <div class="job-item-cell job-title-cell">
                <a href="<?php the_permalink(); ?>" class="job-title-link"><?php the_title(); ?></a>
                
                <div class="job-taxonomy-info">
                    <?php
                    // 職種を表示
                    $job_positions = get_the_terms(get_the_ID(), 'job_position');
                    if ($job_positions && !is_wp_error($job_positions)) {
                        foreach ($job_positions as $position) {
                            echo '<span class="job-position-tag">' . $position->name . '</span>';
                        }
                    }
                    
                    // 雇用形態を表示
                    $job_types = get_the_terms(get_the_ID(), 'job_type');
                    if ($job_types && !is_wp_error($job_types)) {
                        foreach ($job_types as $type) {
                            echo '<span class="job-type-tag">' . $type->name . '</span>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="job-item-cell job-status-cell">
                <?php
                // 投稿ステータスの表示
                $status = get_post_status();
                $status_label = '';
                
                switch ($status) {
                    case 'publish':
                        $status_label = '<span class="status-publish">公開中</span>';
                        break;
                    case 'draft':
                        $status_label = '<span class="status-draft">下書き</span>';
                        break;
                    case 'pending':
                        $status_label = '<span class="status-pending">承認待ち</span>';
                        break;
                    default:
                        $status_label = '<span class="status-other">' . $status . '</span>';
                }
                
                echo $status_label;
                ?>
            </div>
            <div class="job-item-cell job-date-cell">
    <?php 
    // 現在のループ内の投稿IDを明示的に取得
    $current_post_id = get_the_ID(); 
    
    // 最終更新日を取得して表示
    $modified_date = get_the_modified_date('Y年m月d日', $current_post_id);
    
    // もし最終更新日が取得できない場合は投稿日をバックアップとして使用
    if (empty($modified_date)) {
        $modified_date = get_the_date('Y年m月d日', $current_post_id);
    }
    
    echo $modified_date;
    ?>
</div>
            <div class="job-item-cell job-actions-cell">
    <a href="<?php echo home_url('/edit-job/?job_id=' . get_the_ID()); ?>" class="btn-edit">編集</a>
    
    <?php if (get_post_status() == 'publish') : ?>
    <button class="btn-draft frontend-action" data-action="draft" data-job-id="<?php echo get_the_ID(); ?>">下書きにする</button>
    <?php else : ?>
    <button class="btn-publish frontend-action" data-action="publish" data-job-id="<?php echo get_the_ID(); ?>">公開する</button>
    <?php endif; ?>
    
    <button class="btn-delete frontend-action" data-action="delete" data-job-id="<?php echo get_the_ID(); ?>">削除</button>
</div>
        </div>
        
        <?php endwhile; ?>
    </div>
    
    <?php
    else :
        // 求人がない場合
        echo '<div class="no-jobs-message">';
        echo '<p>投稿した求人情報はありません。</p>';
        echo '<p><a href="' . home_url('/post-job/') . '" class="btn-new-job">最初の求人を投稿する</a></p>';
        echo '</div>';
    endif;
    
    wp_reset_postdata();
    ?>
</div>

<script>
jQuery(document).ready(function($) {
    // フロントエンドでのアクション処理
    $('.frontend-action').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var action = $button.data('action');
        var jobId = $button.data('job-id');
        
        // 削除確認
        if (action === 'delete' && !confirm('本当にこの求人を削除しますか？この操作は元に戻せません。')) {
            return;
        }
        
        // AJAX処理
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'frontend_' + action + '_job',
                job_id: jobId,
                nonce: '<?php echo wp_create_nonce('frontend_job_action'); ?>'
            },
            beforeSend: function() {
                $button.prop('disabled', true).text('処理中...');
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data || 'エラーが発生しました。');
                    $button.prop('disabled', false).text(getButtonText(action));
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
                $button.prop('disabled', false).text(getButtonText(action));
            }
        });
    });
    
    // ボタンのテキストを取得
    function getButtonText(action) {
        switch(action) {
            case 'draft': return '下書きにする';
            case 'publish': return '公開する';
            case 'delete': return '削除';
            default: return action;
        }
    }
});
</script>

<?php
// 専用のフッターを読み込み
include(get_stylesheet_directory() . '/agency-footer.php');
?>
