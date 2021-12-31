<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
} // endif

if (!class_exists('CCC_Terms_Filter_Ajax_Results')) {
  class CCC_Terms_Filter_Ajax_Results
  {

    public static function action()
    {
      /****** 初期設定 ******/

      /*** post_type用 ***/
      $posttype = sanitize_text_field($_POST['posttype']);
      if ($posttype) {
        $my_post_type = $posttype;
      } else {
        $my_post_type = 'all';
      } // endif
      if ($my_post_type === 'all') {
        /*
      * 使用禁止：「any」だと「get_object_taxonomies()」を利用できないので使用禁止
      * $my_post_type = 'any'; // リビジョンと 'exclude_from_search' が true にセットされたものを除き、すべてのタイプを含める
      */
        $args = array(
          'public' => true
        );
        $my_post_type = get_post_types($args, 'names');
        unset($my_post_type['attachment']); // 特定のカスタム投稿タイプを除外（PHP：連想配列から要素を除外）
      } // endif


      /*** tax_query用 ***/
      $taxqueries = array('relation' => 'AND');
      $taxonomies = get_object_taxonomies($my_post_type, 'objects');
      foreach ($taxonomies as $taxonomy) {
        if ($_POST['taxname_' . $taxonomy->name]) {
          ${'taxvalue_' . $taxonomy->name} = absint($_POST['taxname_' . $taxonomy->name]);
        } // endif
        if (${'taxvalue_' . $taxonomy->name}) {
          ${'taxquery_' . $taxonomy->name} = array(
            'taxonomy' => $taxonomy->name,
            'terms' => ${'taxvalue_' . $taxonomy->name},
            'field' => 'term_id',
            'include_children' => true, //階層を持つタクソノミーの場合に子孫タクソノミーを含めるかどうか。デフォルトは true（含める）です。
            'operator' => 'IN', // IN（いずれかに合致）/ AND（全てに合致）/ NOT IN（いずれにも合致しない）
          );
        } // endif
        array_push($taxqueries, ${'taxquery_' . $taxonomy->name});

        /* 主となるtax_query用 */
        $args = array(
          'parent' => 0,
        );
        $parent_terms = get_terms($taxonomy->name, $args);
        foreach ($parent_terms as $parent_term) {
          if ($_POST[$taxonomy->name] === $parent_term->slug) {
            $main_taxquery = array(
              'taxonomy' => $taxonomy->name,
              'terms' => $parent_term->slug,
              'field' => 'slug',
              'include_children' => true, //階層を持つタクソノミーの場合に子孫タクソノミーを含めるかどうか。デフォルトは true（含める）です。
              'operator' => 'IN', // IN（いずれかに合致）/ AND（全てに合致）/ NOT IN（いずれにも合致しない）
            );
            array_push($taxqueries, $main_taxquery);
          } // endif
          $args = array(
            'parent' => $parent_term->term_id,
          );
          $child_terms = get_terms($taxonomy->name, $args);
          if ($child_terms) {
            foreach ($child_terms as $child_term) {
              if ($_POST[$taxonomy->name] === $child_term->slug) {
                $main_taxquery = array(
                  'taxonomy' => $taxonomy->name,
                  'terms' => $child_term->slug,
                  'field' => 'slug',
                  'include_children' => true, //階層を持つタクソノミーの場合に子孫タクソノミーを含めるかどうか。デフォルトは true（含める）です。
                  'operator' => 'IN', // IN（いずれかに合致）/ AND（全てに合致）/ NOT IN（いずれにも合致しない）
                );
                array_push($taxqueries, $main_taxquery);
              } // endif
            } // endforeach
          } // endif
        } // endforeach
      } // endforeach


      /*** meta_query用 ***/
      $metaqueries = null;

      /*** 表示数の定義（指定が無ければ管理画面の表示設定（表示する最大投稿数）の値を取得） ***/
      if (isset($_POST['ccc-posts_per_page'])) {
        $posts_per_page = absint($_POST['ccc-posts_per_page']); //負ではない整数に変換;
      } else {
        $posts_per_page = get_option('posts_per_page');
      } // endif

      /*** すでに表示されている記事リストの個数 ***/
      if (isset($_POST['looplength'])) {
        $looplength = absint($_POST['looplength']); //負ではない整数に変換
      } else {
        $looplength = null;
      }

      $args = array(
        'post_type' => $my_post_type,
        'post_status' => 'publish', //公開済みのページのみ取得
        'posts_per_page' => $posts_per_page, //表示数を指定（初期値：指定しない場合は管理画面の表示設定の値）
        'offset' => $looplength, //指定した分だけ検索位置をずらす（ajaxから現在表示中の投稿数を取得）
        /* 'relation' => 'AND'：有効な値は 'AND' または 'OR' です。1つしかタクソノミー検索条件を含まない場合は指定しないでください。デフォルトは 'AND' です。*/
        'tax_query' => $taxqueries,
        'meta_query' => $metaqueries,
        'orderby' => array('type' => 'ASC', 'menu_order' => 'ASC'),
      );

      /***** For WordPress Plugin "bogo" : START *****/
      if (isset($_POST['bogo'])) {
        $locale = sanitize_text_field($_POST['bogo']);
        $args['lang'] = $locale;
      }
      /***** For WordPress Plugin "bogo" : END *****/

      $the_query = new WP_Query($args);
?>


      <?php
      if ($the_query->have_posts()) {
        $count = 0;
        while ($the_query->have_posts()) {
          $the_query->the_post();
          $count++;
      ?>
          <div class="list-ccc_terms_filter_ajax js-list-filter clearfix">
            <div class="img-post">
              <a href="<?php the_permalink(); ?>">
                <?php
                if (has_post_thumbnail()) {
                  echo '<div class="img-post-thumbnail has_post_thumbnail"><img src="' . get_the_post_thumbnail_url($the_query->post->ID, 'large') . '" alt="' . $the_query->post->post_title . '" loading="lazy" /></div>';
                } else {
                  echo '<div class="img-post-thumbnail has_post_thumbnail-no"><img src="' . CCC_Post_Thumbnail::get_first_image_url($the_query->post) . '" alt="' . $the_query->post->post_title . '" loading="lazy" /></div>';
                }
                ?>
              </a>
            </div><!-- /.img-post -->
            <?php if (shortcode_exists('ccc_my_favorite_select_button')) {
              echo do_shortcode('[ccc_my_favorite_select_button]');
            } ?>
            <h3 class="title-post"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><!-- /.title-post -->
            <div class="terms-post">
              <?php echo self::my_taxonomies_terms_all($my_post_type); ?>
            </div><!-- /.terms-post -->
          </div><!-- /.list-ccc_terms_filter_ajax -->
        <?php } //endwhile; 
        ?>
        <div id="ccc-terms_filter_ajax-wp_query-count">
          <span class="ccc-terms_filter_ajax-post-count"></span><span class="ccc-terms_filter_ajax-found_posts-count"><?php echo $the_query->found_posts; ?></span>
        </div><!-- /#ccc-terms_filter_ajax-wp_query-count -->
      <?php
        wp_reset_postdata(); /* オリジナルの投稿データを復元 */
      } else {
      ?>
        <div class="no-post">
          <p><?php _e('There are no articles.', CCCTERMSFILTERAJAX_TEXT_DOMAIN); ?></p>
        </div><!-- /.no-post -->
<?php
      } //endif
    } //endfunction


    /*** 指定した投稿タイプの投稿に紐付いたカスタム分類のタームを取得する関数（START） ***/
    /* （タクソノミーと）タームのリンクを取得する */
    public static function my_taxonomies_terms_all($my_post_type)
    {
      /* 指定した投稿タイプからタクソノミーを取得 */
      $taxonomies = get_object_taxonomies($my_post_type, 'objects');
      $out = array();
      foreach ($taxonomies as $taxonomy) {
        /* 投稿に紐付いたタームを取得 */
        $terms = get_the_terms($post->ID, $taxonomy->name);
        if (!empty($terms)) {
          $out[] = '<ul class="list-terms list-terms-' . $taxonomy->name . '">';
          foreach ($terms as $term) {
            if ($term->parent === 0) {
              $out[] = '<li class="list-term-item list-term-parent list-term-' . $term->slug . '" data-list-term="' . $term->slug . '"><a href="' . get_term_link($term->term_id, $taxonomy->name) . '">' . $term->name . '</a></li>';
            } else {
              $parent_id = $term->parent;
              $parent = get_term_by('id', $parent_id, $taxonomy->name);
              $out[] = '<li class="list-term-item list-term-children list-term-' . $term->slug . '" data-list-term="' . $term->slug . '" data-list-term-parent="' . $parent->slug . '"><a href="' . get_term_link($term->term_id, $taxonomy->name) . '">' . $term->name . '</a></li>';
            } //endif
          } //endforeach
          $out[] = "</ul>\n";
        } //endif
      } //endforeach
      return implode('', $out);
    } //endfunction
    /*** 指定した投稿タイプの投稿に紐付いたカスタム分類のタームを取得する関数（END） ***/
  } //endclass
} //endif
