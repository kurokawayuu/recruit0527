<?php

/**
 * Cocoon WordPress Theme
 * @author: yhira
 * @link: https://wp-cocoon.com/
 * @license: http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */
if (!defined('ABSPATH')) exit; ?>
<?php get_header(); ?>

<!-- スライダー部分 -->
<div class="peek-slider-wrapper">
  <div class="peek-slider">
    <div class="peek-slider-container">
      <?php
      $args = array(
        'post_type'      => 'slide',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC'
      );
      $slides = new WP_Query($args);
      
      if ($slides->have_posts()) :
        while ($slides->have_posts()) : $slides->the_post();
          $slide_image_id = get_post_meta(get_the_ID(), 'slide_image_id', true);
          $slide_image_url = wp_get_attachment_image_url($slide_image_id, 'full');
          $slide_link = get_post_meta(get_the_ID(), 'slide_link', true);
          
          if (!empty($slide_image_url)) :
          ?>
          <div class="peek-slide">
            <?php if (!empty($slide_link)) : ?>
            <a href="<?php echo esc_url($slide_link); ?>" class="slide-link">
            <?php endif; ?>
              <img src="<?php echo esc_url($slide_image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
            <?php if (!empty($slide_link)) : ?>
            </a>
            <?php endif; ?>
          </div>
          <?php
          endif;
        endwhile;
        wp_reset_postdata();
      else :
        // デモスライド
        for ($i = 1; $i <= 3; $i++) :
        ?>
        <div class="peek-slide">
          <div class="demo-slide">
            <h2>スライド <?php echo $i; ?></h2>
            <p>管理画面で「スライダー」からスライドを追加してください</p>
          </div>
        </div>
        <?php
        endfor;
      endif;
      ?>
    </div>
    <!-- ナビゲーションボタン -->
    <button class="peek-slider-button prev">❮</button>
    <button class="peek-slider-button next">❯</button>
    
    <!-- ドットインジケーター -->
    <div class="peek-slider-dots">
      <?php 
      $total_slides = $slides->post_count > 0 ? $slides->post_count : 3;
      for ($i = 0; $i < $total_slides; $i++) : 
      ?>
        <button class="peek-slider-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
      <?php endfor; ?>
    </div>
  </div>
</div>
<main class="main-content">
<!-- 以下、元のfront-page.phpの内容 -->
        
<!-- 求人検索 -->
<?php get_template_part('search', 'form'); ?>

<!-- 職種から探す -->
<section class="job-category">
  <h2 class="section-tit">職種から探す</h2>
  <div class="category-container">
    <?php
    // 職種のスラッグとリンク先を配列で定義
    $positions = array(
      '児童発達支援管理責任者' => 'jidou-kanrisha',
      '児童指導員' => 'jidou-shidouin',
      '保育士' => 'hoikushi',
      '理学療法士' => 'pt',
      '作業療法士' => 'ot',
      '言語聴覚士' => 'st',
      'その他' => 'other'
    );
    
    // 職種のアイコンクラスを定義
    $position_icons = array(
      '児童発達支援管理責任者' => 'fas fa-user-shield',
      '児童指導員' => 'fas fa-users',
      '保育士' => 'fas fa-baby-carriage',
      '理学療法士' => 'fas fa-running',
      '作業療法士' => 'fas fa-heart',
      '言語聴覚士' => 'fas fa-comment-dots',
      'その他' => 'fas fa-ellipsis-h'
    );
    
    // 各職種のリンクを生成
    foreach ($positions as $position_name => $position_slug) {
      $icon_class = isset($position_icons[$position_name]) ? $position_icons[$position_name] : 'fas fa-briefcase';
      ?>
      <a href="<?php echo home_url('/jobs/position/' . $position_slug . '/'); ?>" class="category-item">
        <h3><?php echo esc_html($position_name); ?></h3>
        <div class="category-icon">
          <i class="<?php echo esc_attr($icon_class); ?>"></i>
        </div>
      </a>
      <?php
    }
    ?>
  </div>
</section>

<!-- 特徴から探す -->
<section class="feature-search">
  <h2 class="section-tit">特徴から探す</h2>
  <div class="tokuchou-container">
    <?php
    // 特徴のデータ（名前、スラッグ、画像ファイル名）
    $features = array(
      array(
        'name' => '未経験歓迎の求人',
        'slug' => 'mikeiken',
        'image' => 'mikeikenn.webp'
      ),
      array(
        'name' => 'オープニングスタッフの求人',
        'slug' => 'openingstaff',
        'image' => 'opening-staff.webp'
      ),
      array(
        'name' => '高収入の求人',
        'slug' => 'koushuunixyuu',
        'image' => 'high-income.webp'
      )
    );
    
    // 各特徴のリンクを生成
    foreach ($features as $feature) {
      ?>
      <a href="<?php echo home_url('/jobs/feature/' . $feature['slug'] . '/'); ?>" class="tokuchou-item">
        <div class="tokuchou-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/<?php echo esc_attr($feature['image']); ?>" alt="<?php echo esc_attr($feature['name']); ?>">
          <div class="tokuchou-title">
            <h3><?php echo esc_html($feature['name']); ?></h3>
          </div>
        </div>
      </a>
      <?php
    }
    ?>
  </div>
</section>


<!-- 新着求人情報（最終版） -->
<section class="new-jobs">
  <div class="container">
    <h2 class="section-tit">新着求人情報</h2>
    <div class="job-slider-wrapper">
      
      <div class="job-container">
        <?php
        // テキストの長さを制限する関数
        function limit_text_job($text, $limit = 30) {
          if (mb_strlen($text) > $limit) {
            return mb_substr($text, 0, $limit) . '...';
          }
          return $text;
        }
        
        // 求人投稿を取得するクエリ（PC/スマホ共通で15件取得）
        $job_args = array(
          'post_type' => 'job',
          'posts_per_page' => 15,
          'orderby' => 'date',
          'order' => 'DESC'
        );
        
        $job_query = new WP_Query($job_args);
        
        // 求人が見つかった場合
        if ($job_query->have_posts()) :
          $card_count = 0;
          
          while ($job_query->have_posts()) : $job_query->the_post();
            $card_count++;
            
            // モバイル用のクラスを追加（6件目以降は非表示）
            $mobile_hide_class = ($card_count > 5) ? ' mobile-hide' : '';
            
            // 求人情報を取得（既存のコードをそのまま使用）
            $facility_name = get_post_meta(get_the_ID(), 'facility_name', true);
            $facility_company = get_post_meta(get_the_ID(), 'facility_company', true);
            $facility_address = get_post_meta(get_the_ID(), 'facility_address', true);
            $salary_range = get_post_meta(get_the_ID(), 'salary_range', true);
            $facility_address = preg_replace('/〒\d{3}-\d{4}\s*/', '', $facility_address);

            // テキストの長さを制限
            $facility_name = limit_text_job($facility_name, 20);
            $facility_company = limit_text_job($facility_company, 20);
            $facility_address = limit_text_job($facility_address, 30);
            $salary_range = limit_text_job($salary_range, 30);
            
            // タクソノミーから職種と雇用形態を取得
            $job_position = wp_get_object_terms(get_the_ID(), 'job_position', array('fields' => 'names'));
            $position_name = !empty($job_position) ? limit_text_job($job_position[0], 20) : '';
            
            $job_type = wp_get_object_terms(get_the_ID(), 'job_type', array('fields' => 'names'));
            $type_name = !empty($job_type) ? $job_type[0] : '';
            
            // 特徴タグを取得
            $job_features = wp_get_object_terms(get_the_ID(), 'job_feature', array('fields' => 'names'));
            
            // サムネイル画像URL
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            if (!$thumbnail_url) {
              $thumbnail_url = get_stylesheet_directory_uri() . '/images/job-image-default.jpg';
            }
            
            // 雇用形態タグのクラス決定
            $type_class = 'other';
            if ($type_name == '正社員') {
              $type_class = 'full-time';
            } elseif (strpos($type_name, 'パート') !== false || strpos($type_name, 'アルバイト') !== false) {
              $type_class = 'part-time';
            }
        ?>
        <!-- 求人カード -->
        <div class="jo-card<?php echo $mobile_hide_class; ?>">
          <div class="jo-header">
            <div class="cmpany-name">
              <p class="bold-text"><?php echo esc_html($facility_name); ?></p>
              <p><?php echo esc_html($facility_company); ?></p>
            </div>
            <div class="employment-type <?php echo $type_class; ?>">
              <?php echo esc_html($type_name); ?>
            </div>
          </div>
          <div class="jo-image">
            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="求人画像">
          </div>
          <div class="jo-info">
            <h3 class="jo-title"><?php echo esc_html($position_name); ?></h3>
            <div class="inf-item">
              <span class="inf-icon"><i class="fa-solid fa-location-dot"></i></span>
              <p class="job-location"><?php echo esc_html($facility_address); ?></p>
            </div>
            <div class="inf-item">
              <span class="inf-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
              <p class="job-sala">
                <?php 
                $salary_type = get_post_meta(get_the_ID(), 'salary_type', true);
                $salary_type_text = ($salary_type == 'HOUR') ? '時給 ' : '月給 ';
                echo esc_html($salary_type_text . $salary_range);
                if (strpos($salary_range, '円') === false) {
                  echo '円';
                }
                ?>
              </p>
            </div>
            <div class="job-tags">
              <?php if (!empty($job_features)) : 
                $count = 0;
                foreach ($job_features as $feature) : 
                  if ($count < 3) :
                    $feature = limit_text_job($feature, 15);
              ?>
                <span class="feature-tag"><?php echo esc_html($feature); ?></span>
              <?php 
                  endif;
                  $count++;
                endforeach; 
              endif; 
              ?>
            </div>
          </div>
          <div class="job-footer">
            <a href="<?php the_permalink(); ?>" class="detail-btn">詳細を見る <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <?php
          endwhile;
          wp_reset_postdata();
        else :
        ?>
        <p>現在、求人情報はありません。</p>
        <?php endif; ?>
      </div>
      
      <!-- スライドインジケーター -->
      <div class="slide-indicators">
        <?php 
        if ($card_count > 0) {
          // PC用インジケーター（15件÷5件/画面 = 3個）
          $pc_slides = ceil($card_count / 5);
          
          // モバイル用インジケーター（5件÷1件/画面 = 5個）
          $mobile_slides = min($card_count, 5);
        ?>
          <!-- PC用インジケーター -->
          <div class="indicators-pc">
            <?php for ($i = 0; $i < $pc_slides; $i++) : ?>
              <div class="indicator<?php echo ($i == 0) ? ' active' : ''; ?>" data-slide="<?php echo $i; ?>"></div>
            <?php endfor; ?>
          </div>
          
          <!-- モバイル用インジケーター -->
          <div class="indicators-mobile">
            <?php for ($i = 0; $i < $mobile_slides; $i++) : ?>
              <div class="indicator<?php echo ($i == 0) ? ' active' : ''; ?>" data-slide="<?php echo $i; ?>"></div>
            <?php endfor; ?>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</section>

<!-- サイト案内 -->
<section class="about-site">
  <div class="about-container">
    <h2 class="about-main-title">こどもプラス求人サイトへようこそ！<br>あなたに最適な職場が見つかる場所。</h2>
    
    <div class="about-items">
      <div class="about-item">
        <h3 class="about-item-title">他にはない充実した求人情報</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-unique.webp" alt="充実した求人情報">
        </div>
        <p class="about-item-text">一般的な給与・勤務時間の情報だけでなく、実際に働くスタッフの生の声や職場の雰囲気まで、リアルな情報をお届けします。「どんな職場なのか」が具体的にイメージできる求人情報を提供しています。</p>
      </div>
      
      <div class="about-item">
        <h3 class="about-item-title">スムーズな応募プロセス</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-process.webp" alt="スムーズな応募プロセス">
        </div>
        <p class="about-item-text">会員登録が完了すると、応募フォームに情報が自動入力されます。そのため、面倒な手続きなしで、効率良く求人への応募が可能です。</p>
      </div>
      
      <div class="about-item">
        <h3 class="about-item-title">あなたにぴったりの求人をお届け</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-matching.webp" alt="ぴったりの求人">
        </div>
        <p class="about-item-text">ご登録いただいた希望条件に合わせて、あなたにマッチした求人情報をお知らせします。また、最新の求人情報もいち早くチェックできるので、理想の職場との出会いを逃しません。</p>
      </div>
    </div>
  </div>
</section>

<!-- マッチング案内 -->
<section class="matching-section">
  <div class="matching-container">
    <h2 class="matching-title">あなたにぴったりの<br>求人情報を見てみよう</h2>
    <p class="matching-desc">あなたのスキルや経験、希望に合った求人情報を閲覧できます。<br>会員登録をして、簡単に応募を行うましょう。</p>
    <div class="matching-image">
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/matching-puzzle.webp" alt="マッチング">
    </div>
    <div class="matching-label">matching</div>
    <a href="<?php echo is_user_logged_in() ? '/members/' : '/register/'; ?>" class="register-large-btn">
      <span class="btn-icon">▶</span>登録して情報を見る
    </a>
  </div>
</section>
<!-- トップページ用スマホ固定フッターバー -->
<div class="home-fixed-footer-bar">
    <div class="home-fixed-footer-container">
        <?php if (is_user_logged_in()) : ?>
            <!-- ログイン中ユーザー向けメニュー -->
            <a href="<?php echo home_url('/members/'); ?>" class="home-footer-button">
                <i class="fas fa-user"></i>
                <span>マイページ</span>
            </a>
            <a href="<?php echo home_url('/favorites/'); ?>" class="home-footer-button">
                <i class="fas fa-star"></i>
                <span>お気に入り</span>
            </a>
        <?php else : ?>
            <!-- 未ログインユーザー向けメニュー -->
            <a href="<?php echo home_url('/register/'); ?>" class="home-footer-button">
                <i class="fas fa-user-plus"></i>
                <span>会員登録</span>
            </a>
            <a href="<?php echo home_url('/login/'); ?>" class="home-footer-button">
                <i class="fas fa-sign-in-alt"></i>
                <span>ログイン</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<style>
/* モバイルで6件目以降を非表示 */
@media (max-width: 768px) {
  .jo-card.mobile-hide {
    display: none !important;
  }
}

/* インジケーターの表示制御 */
.indicators-pc {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.indicators-mobile {
  display: none;
  justify-content: center;
  gap: 6px;
}

@media (max-width: 768px) {
  .indicators-pc {
    display: none;
  }

  .indicators-mobile {
    display: flex;
  }
}

/* インジケーターのスタイル */
.indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #ddd;
  cursor: pointer;
  transition: all 0.3s ease;
}

.indicator.active {
  background-color: #26b7a0;
  transform: scale(1.2);
}

@media (max-width: 768px) {
  .indicator {
    width: 8px;
    height: 8px;
  }
}
/* 新着求人情報のインジケーター修正 */

/* スライドインジケーターの基本スタイル強化 */
.slide-indicators {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 20px;
  padding: 10px 0;
}

.indicator {
  width: 12px !important;
  height: 12px !important;
  border-radius: 50% !important;
  background-color: #ddd !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
  border: none !important;
  outline: none !important;
  display: block !important;
}

/* アクティブなインジケーター（緑色）- 優先度を最高に */
.slide-indicators .indicator.active,
.indicators-pc .indicator.active,
.indicators-mobile .indicator.active {
  background-color: #26b7a0 !important;
  transform: scale(1.2) !important;
  box-shadow: 0 2px 8px rgba(38, 183, 160, 0.4) !important;
}

.indicator:hover {
  background-color: #bbb !important;
  transform: scale(1.1) !important;
}

/* PC用インジケーター */
.indicators-pc {
  display: flex !important;
  justify-content: center !important;
  gap: 8px !important;
}

.indicators-mobile {
  display: none !important;
  justify-content: center !important;
  gap: 6px !important;
}

/* モバイル対応 */
@media (max-width: 768px) {
  .indicators-pc {
    display: none !important;
  }

  .indicators-mobile {
    display: flex !important;
  }
  
  .indicator {
    width: 10px !important;
    height: 10px !important;
  }

}

/* 既存のスタイルを上書き */
.new-jobs .indicator {
  width: 12px !important;
  height: 12px !important;
  border-radius: 50% !important;
  background-color: #ddd !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
}

.new-jobs .indicator.active {
  background-color: #26b7a0 !important;
  transform: scale(1.2) !important;
}
	

</style>
	
<?php get_footer(); ?>