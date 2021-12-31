<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
} //endif

if (!class_exists('CCC_Terms_Filter_Ajax_ShortCode_List')) {

  add_shortcode('ccc_posts_filter_list', array('CCC_Terms_Filter_Ajax_ShortCode_List', 'html'));

  class CCC_Terms_Filter_Ajax_ShortCode_List
  {

    public static function html($atts)
    {
      wp_enqueue_script('ccc_terms_filter_ajax-list');

      ob_start(); // returnでHTMLを返す：出力のバッファリングを有効にする

      $atts = shortcode_atts(array(
        "post_type" => '',
        "posts_per_page" => '',
        "class" => '',
        "style" => '',
        "taxonomy_name" => '',
        "term_parent_slug" => '',
        "locale" => '',
      ), $atts);
      if ($atts['post_type']) {
        $my_post_type = $atts['post_type'];
      } else {
        $my_post_type = 'all';
      }
      if ($atts['posts_per_page'] and ctype_digit($atts['posts_per_page'])) {
        $posts_per_page = $atts['posts_per_page'];
      } else {
        $posts_per_page = 10;
      }
      if ($atts['class']) {
        $class = 'class="' . $atts['class'] . '"';
      } else {
        $class = null;
      }
      if ($atts['style'] or $atts['style'] === 0 or $atts['style'] === '0') {
        $style = $atts['style'];
      } else {
        $style = 1;
      }
      /***** For WordPress Plugin "bogo" : START *****/
      /* Detect plugin. For use on Front End and Back End. */
      if ($atts['locale'] === 'bogo' and in_array('bogo/bogo.php', (array) get_option('active_plugins', array()))) {
        $locale = 'data-ccc_terms_filter_ajax-article-bogo="' . get_locale() . '"';
      } else {
        $locale = null;
      };
      /***** For WordPress Plugin "bogo" : END *****/


      /*** 初期設定 ***/
      /* URLからタームとタクソノミーのオブジェクトを取得 */
      $term_object = get_queried_object(); // タームオブジェクトを取得
      /* タクソノミー名 */
      if ($atts['taxonomy_name']) {
        $my_term_taxonomy = $atts['taxonomy_name'];
      } else {
        $my_term_taxonomy = $term_object->taxonomy ? $term_object->taxonomy : 'category'; // タクソノミー名
      }
      /* タームスラッグ */
      if ($atts['term_parent_slug']) {
        $my_term_slug = $atts['term_parent_slug']; // 親タームスラッグ限定
      } else {
        $my_term_slug = $term_object->slug; // タームスラッグ
      }
      $my_term_name = $term_object->name; // タームタイトル
      $my_term_parent = $term_object->parent; // 親カテゴリーのID。なければ0（つまり親）

      $my_term_id = $term_object->term_id; // タームスID
      $my_term_description = $term_object->description; // タームディスクリプション
      if ($my_term_parent === 0) {
        $my_term_hierarchy = 'parent';
      } else {
        $my_term_hierarchy = 'children';
      }

      $args = array(
        'post_type' => $my_post_type,
        'post_status' => 'publish', //公開済みのページのみ取得
        'posts_per_page' => -1, //全件取得
        'tax_query' => array(
          array(
            'taxonomy' => $my_term_taxonomy,
            'field'    => 'slug',
            'terms'    => $my_term_slug,
          ),
        ),
      );
      $the_query = new WP_Query($args);
?>

      <div id="ccc-terms_filter_ajax-article" data-ccc_terms_filter_ajax-article-taxonomy="<?php echo $my_term_taxonomy; ?>" data-ccc_terms_filter_ajax-article-term="<?php echo $my_term_slug; ?>" data-ccc_terms_filter_ajax-article-post_type="<?php echo $my_post_type; ?>" <?php echo $class; ?> data-ccc_posts_filter-style="<?php echo $style; ?>" <?php echo $locale; ?>>
        <div id="ccc-terms_filter_ajax-inner" class="clearfix">
          <div class="ccc-terms_filter_ajax-header clearfix">
            <p class="ccc-terms_filter_ajax-count">
              <span class="name"><?php echo $my_term_name; ?></span><span class="number"><?php echo $the_query->found_posts; ?></span><span class="unit"><?php printf(_n('item', 'items', $the_query->post_count, CCCTERMSFILTERAJAX_TEXT_DOMAIN), $the_query->post_count); ?></span>
            </p><!-- /.ccc-terms_filter_ajax-count -->
            <div class="nav-taxonomy-toggle"><a href="#" class="nav-taxonomy-toggle-button"><i class="icon-ccc_terms_filter_ajax-filter"></i><span class="text"><?php _e('Filter', CCCTERMSFILTERAJAX_TEXT_DOMAIN); ?></span></a></div><!-- /.nav-taxonomy-toggle -->
            <p class="select-reset-all"><label class="select-toggle"><i class="icon-ccc_terms_filter_ajax-close"></i><input type="checkbox"><?php _e('Deselect all', CCCTERMSFILTERAJAX_TEXT_DOMAIN); ?></label></p><!-- /.select-reset-all -->
          </div><!-- /.ccc-terms_filter_ajax-header -->
          <div id="ccc-terms_filter_ajax-nav_taxonomy">
            <div class="wrap-select-taxonomy">
              <?php self::all_taxonomies_terms_all($my_post_type, $my_term_taxonomy, $my_term_slug); ?>
            </div><!-- /.wrap-select-taxonomy -->
          </div><!-- /#ccc-terms_filter_ajax-nav_taxonomy -->
          <div id="ccc-terms_filter_ajax-post" data-ccc_terms_filter_ajax-posts_per_page-filter="<?php echo $posts_per_page; ?>"></div><!-- /#ccc-terms_filter_ajax-post -->
          <div class="clone-header-ccc_terms_filter_ajax" id="ccc-terms_filter_ajax-header-clone"></div><!-- /#ccc-terms_filter_ajax-header-clone -->
          <div class="results-more"><a href="#" id="ccc-terms_filter_ajax-results-more-trigger"><i class="icon-ccc_terms_filter_ajax-refresh"></i><span class="text"><?php _e('Read further', CCCTERMSFILTERAJAX_TEXT_DOMAIN); ?></span></a></div><!-- /.results-more -->
          <div id="ccc-terms_filter_ajax-loader">
            <div class="loader"><?php _e('Loading', CCCTERMSFILTERAJAX_TEXT_DOMAIN); ?>...</div>
          </div><!-- /#ccc-terms_filter_ajax-loader -->
        </div><!-- /.ccc-terms_filter_ajax-inner -->
      </div><!-- /#ccc-terms_filter_ajax-article -->
<?php
      return ob_get_clean(); // returnでHTMLを返す：関数からHTMLを返し、それをいろいろ編集したり、処理を加えてから出力する場面で有効：バッファリングの内容を出力した後にバッファリングを削除
    } //endfunction

    /*** すべてのカスタム分類のタームを取得する関数（START） ***/
    public static function all_taxonomies_terms_all($my_post_type, $my_term_taxonomy = false, $my_term_slug = false)
    {
      if ($my_post_type === 'all') {
        /*
      * 使用禁止：「any」だと「get_object_taxonomies()」を利用できないので使用禁止
      * $my_post_type = 'any'; // リビジョンと 'exclude_from_search' が true にセットされたものを除き、すべてのタイプを含める
      */
        $args = array(
          'public'   => true
        );
        $my_post_type = get_post_types($args, 'names');
        unset($my_post_type['attachment']); // 特定のカスタム投稿タイプを除外（PHP：連想配列から要素を除外）
      } // endif
      $taxonomies = get_object_taxonomies($my_post_type, 'objects');
      unset($taxonomies['post_format']); // 特定のタクソノミーを除外（PHP：連想配列から要素を除外）
      foreach ($taxonomies as $taxonomy) {
        echo '<div class="select-taxonomy" id="select-taxonomy-' . $taxonomy->name . '" data-select-taxonomy="' . $taxonomy->name . '">';
        echo '<div class="select-taxonomy-title ccc-terms_filter_ajax-accordion-trigger"><a href="#"><p class="text">' . $taxonomy->label . '</p>';
        echo '<div class="accordion-icon"><span class="accordion-icon-bar"></span><span class="accordion-icon-bar"></span></div>';
        echo '</a></div>'; //<!-- /.select-taxonomy-title -->
        echo '<div class="ccc-terms_filter_ajax-accordion-contents">';
        echo '<ul class="select-terms select-terms-parent">';
        $args = array(
          'parent' => 0,
        );
        if ($my_term_taxonomy and $my_term_slug) {
          $my_taxonomy_term_slug = array(
            'taxonomy' => $my_term_taxonomy,
            'field'  => 'slug',
            'terms' => $my_term_slug,
          );
        } else {
          $my_taxonomy_term_slug = null;
        } //endif
        $parent_terms = get_terms($taxonomy->name, $args);
        foreach ($parent_terms as $parent_term) {
          $parent_term_args = array(
            'post_type' => $my_post_type,
            'tax_query' => array(
              'relation' => 'AND',
              array(
                'taxonomy' => $taxonomy->name,
                'field'  => 'slug',
                'terms' => $parent_term->slug
              ),
              $my_taxonomy_term_slug
            )
          );
          $this_query = new WP_Query($parent_term_args);
          if ($this_query->found_posts > 0) {
            if (($taxonomy->name === $my_term_taxonomy) and ($parent_term->slug === $my_term_slug)) {
              $current = 'data-taxonomy_term-current="true"';
            } else {
              $current = null;
            }
            echo '<li class="select-term-item select-term-parent" data-select-term="' . $parent_term->slug . '" ' . $current . '>';
            echo '<label class="select-toggle select-toggle-' . $taxonomy->name . '">';
            echo '<input type="checkbox" name="' . $taxonomy->name . '[]" value="' . $parent_term->term_id . '">';
            echo '<span class="term-text">' . $parent_term->name . '</span>';
            echo '</label>';
          }
          $args = array(
            'parent' => $parent_term->term_id,
          );
          $child_terms = get_terms($taxonomy->name, $args);
          if ($child_terms) {
            echo '<ul class="select-terms select-terms-children">';
            foreach ($child_terms as $child_term) {
              $child_term_args = array(
                'post_type' => $my_post_type,
                'tax_query' => array(
                  'relation' => 'AND',
                  array(
                    'taxonomy' => $taxonomy->name,
                    'field'  => 'slug',
                    'terms' => $child_term->slug
                  ),
                  $my_taxonomy_term_slug
                )
              );
              $this_query = new WP_Query($child_term_args);
              if ($this_query->found_posts > 0) {
                if (($taxonomy->name === $my_term_taxonomy) and ($child_term->slug === $my_term_slug)) {
                  $current = 'data-taxonomy_term-current="true"';
                } else {
                  $current = null;
                }
                echo '<li class="select-term-item select-term-children" data-select-term="' . $child_term->slug . '" ' . $current . '>';
                echo '<label class="select-toggle select-toggle-' . $taxonomy->name . '">';
                echo '<input type="checkbox" name="' . $taxonomy->name . '[]" value="' . $child_term->term_id . '">';
                echo '<span class="term-text">' . $child_term->name . '</span>';
                echo '</label>';
                echo '</li>';
              }
            } // endforeach
            echo '</ul>'; //<!-- /.select-terms-children -->
          } // endif
          echo '</li>'; //<!-- /.select-term-item-parent -->
        } // endforeach
        echo '</ul>'; //<!-- /.select-terms-parent -->
        echo '<p class="select-reset select-reset-' . $taxonomy->name . '"><label class="select-toggle"><input type="checkbox">' . __('Deselect', CCCTERMSFILTERAJAX_TEXT_DOMAIN) . '</label></p>';
        echo '</div>'; //<!-- /.ccc-terms_filter_ajax-accordion-contents -->
        echo '</div>'; //<!-- /.select-taxonomy -->
      } // endforeach
    } //endfunction
    /*** すべてのカスタム分類のタームを取得する関数（END） ***/
  } //endclass
} //endif
