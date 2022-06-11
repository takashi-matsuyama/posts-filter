/*
 * Author: Takashi Matsuyama
 * Author URI: https://profiles.wordpress.org/takashimatsuyama/
 * Description: WordPressのタクソノミーでANDフィルタ表示、タームでORフィルタ表示するスクリプト
 * Version: 1.2.0 or later
 */
(function ($) {
  /*** 初期設定 ***/
  var nav_taxonomy_elm = $("#ccc-terms_filter_ajax-nav_taxonomy");
  var filter_data_select = "select-term";
  var filter_data_list = "list-term";
  var select_reset_elm = $(".select-reset");
  var select_reset_all_elm = $(".select-reset-all");
  var select_reset_common_elm = $(".select-reset, .select-reset-all");
  var select_taxonomy = "select-taxonomy";
  var select_taxonomy_elm = $("." + select_taxonomy);
  var select_term_item = "select-term-item";
  var select_term_item_elm = $("." + select_term_item);
  var select_toggle = "select-toggle";
  var select_display = "select-display";
  var post_area_elm = $("#ccc-terms_filter_ajax-post");
  var list_filter = "js-list-filter";
  var list_filter_elm = $("." + list_filter);
  var list_term_item = "list-term-item";
  var list_term_item_elm = $("." + list_term_item);
  var has_active = "has-active"; //動的に付加する要素の名前
  var sub_open = "sub-open"; //動的に付加する要素の名前
  var active_select_term = "active-select-term"; //動的に付加する要素の名前
  var active_list_term = "active-list-term"; //動的に付加する要素の名前
  var active_list = "active-list"; //動的に付加する要素の名前
  var count_series_elm = $(".ccc-terms_filter_ajax-count");
  var count_series_number = count_series_elm.children(".number");
  var accordion_trigger = "ccc-terms_filter_ajax-accordion-trigger";
  var accordion_trigger_elm = $("." + accordion_trigger);
  var accordion_contents_elm = $(".ccc-terms_filter_ajax-accordion-contents");

  /*** 実行本体：ターム切り替えとカスタム投稿のフィルタ表示 ***/
  function filter_term(taxonomy_type, this_elm, option) {
    var data_value = this_elm.data(filter_data_select + option);
    //console.log('this_elmのdata属性の値：' + data_value);
    //console.log('セレクトした条件に一致するリストタームのクラス名：' + $('[data-'+ filter_data_list + option +'="'+ data_value +'"]').attr('class'));
    if (this_elm.hasClass(active_select_term) === false) {
      //タクソノミーが同一の場合は切り替え（択一）※最初に行う処理
      $("#" + select_taxonomy + "-" + taxonomy_type + option)
        .find("." + select_term_item)
        .removeClass(active_select_term);
      $(".list-terms-" + taxonomy_type)
        .find("." + list_term_item + option)
        .removeClass(active_list_term);
      //基本の切り替え
      this_elm.toggleClass(active_select_term);
      this_elm.closest(select_taxonomy_elm).addClass(has_active);
      $(
        "[data-" + filter_data_list + option + '="' + data_value + '"]'
      ).toggleClass(active_list_term);
    } else {
      //既に選択しているメニューをクリックしたら選択解除（リセット）
      this_elm.removeClass(active_select_term);
      this_elm.closest(select_taxonomy_elm).removeClass(has_active);
      $(
        "[data-" + filter_data_list + option + '="' + data_value + '"]'
      ).removeClass(active_list_term);
    }
    //選択されたメニューの数をクリック毎に更新してdata属性に挿入
    var active_select_term_count = nav_taxonomy_elm.find(
      "." + active_select_term
    ).length;
    nav_taxonomy_elm.attr("data-match-select_term", active_select_term_count);
    //選択されたメニューと一致するタームの数をクリック毎に更新して各リストのdata属性に挿入
    list_filter_elm.each(function () {
      var active_list_term_count = $(this).find("." + active_list_term).length;
      $(this).attr("data-match-list_term", active_list_term_count);
      //もし選択されたメニューの数とタームの数が一致する場合
      if (
        $(this).attr("data-match-list_term") ==
        nav_taxonomy_elm.attr("data-match-select_term")
      ) {
        //一致するつリスト
        $(this).fadeIn();
        $(this).addClass(active_list);
      } else {
        //一致しないリスト
        $(this).hide();
        $(this).removeClass(active_list);
      }
      //console.log('list-term：' + active_list_term_count);
    });
  }

  /****** 初期ロード ******/

  /*** チェンジ：ターム切り替えとカスタム投稿のフィルタ表示する関数 ***/
  function select_taxonomy_standard(taxonomy_type) {
    $("#" + select_taxonomy + "-" + taxonomy_type)
      .find(".select-toggle-" + taxonomy_type)
      .on("change", function () {
        var this_elm = $(this).parent(select_term_item_elm);
        var option = "";
        filter_term(taxonomy_type, this_elm, option); // 実行本体：ターム切り替えとカスタム投稿のフィルタ表示
        //console.log('クリックした要素のid名' + this_elm.attr('id'));
        reset_select_taxonomy(taxonomy_type, this_elm); // リセット：セレクトをタクソノミー単位で解除するボタンの表示を切り替える関数を呼び出し
        reset_select_all(); // リセット：全てのセレクトの解除をするボタンの表示切り替え & 表示：該当アイテム件数を更新する関数を呼び出し
      });
  }
  //出力されたタクソノミーを取得して各タクソノミーごとの識別子を引数にしてフィルタ関数の呼び出しをループする
  nav_taxonomy_elm.find(select_taxonomy_elm).each(function () {
    var data_standard = $(this).data(select_taxonomy);
    select_taxonomy_standard(data_standard); // クリック：ターム切り替えとカスタム投稿のフィルタ表示する関数を呼び出し
  });

  /*** リセット：セレクトをタクソノミー単位で解除するボタンの表示を切り替える関数 ***/
  select_reset_elm.hide();
  function reset_select_taxonomy(taxonomy_type, this_elm) {
    var select_taxonomy_this = this_elm.closest(
      "#" + select_taxonomy + "-" + taxonomy_type
    );
    var select_reset_this = select_taxonomy_this.find(select_reset_elm);
    if (this_elm.hasClass(active_select_term) === true) {
      select_reset_this.fadeIn();
    } else {
      select_reset_this.hide();
    }
  }

  /*** リセット：セレクトをタクソノミー単位で解除するボタンを押下した時の処理 ***/
  select_reset_elm.on("change", "." + select_toggle, function () {
    var taxonomy_type = $(this)
      .closest(select_taxonomy_elm)
      .data(select_taxonomy);
    var this_elm = $(this)
      .closest(select_taxonomy_elm)
      .find("." + active_select_term);
    var option = "";
    filter_term(taxonomy_type, this_elm, option); // 実行本体：ターム切り替えとカスタム投稿のフィルタ表示
    if (
      $(this)
        .closest(select_taxonomy_elm)
        .find(select_term_item_elm)
        .hasClass(active_select_term) === true
    ) {
      $(this).closest(select_reset_elm).fadeIn();
    } else {
      $(this).closest(select_reset_elm).hide();
    }
    reset_select_all(); // リセット：全てのセレクトの解除をするボタンの表示切り替え & 表示：該当アイテム件数を更新する関数を呼び出し
    /* タクソノミー単位でcheckboxのcheckedをリセット */
    $(this)
      .closest(select_taxonomy_elm)
      .find('input[type="checkbox"]')
      .prop("checked", false);
  });

  /*** リセット：全てのセレクトの解除をするボタンの表示切り替え & 表示：該当アイテム件数を更新する関数 ***/
  select_reset_all_elm.hide();
  function reset_select_all() {
    if (select_term_item_elm.hasClass(active_select_term)) {
      select_reset_all_elm.fadeIn();
    } else {
      select_reset_all_elm.hide();
    }
    /* 表示：該当アイテム件数を更新 */
    // 注意：セレクトメニューのアコーディオン開閉ボタンは除く（カウントがバグる）
    if ($(this).parent().hasClass(accordion_trigger) === false) {
      count_series_number.text($("." + active_list).length); // 該当アイテム件数を更新
    }
  }

  /*** リセット：全てのセレクトを解除するボタンを押下した時の処理（一括処理が可能なグループ：それ以外は各関数内に別途記述） ***/
  /*** appendで追加した要素を読み込んでから処理 ***/
  select_reset_all_elm.on("change", "." + select_toggle, function () {
    var my_promise = $.when(
      select_term_item_elm.removeClass(active_select_term),
      list_term_item_elm.removeClass(active_list_term),
      select_taxonomy_elm.removeClass(has_active),
      $("." + select_display).removeClass(sub_open),
      nav_taxonomy_elm.attr("data-match-select_term", 0),
      list_filter_elm.attr("data-match-list_term", 0),
      list_filter_elm.fadeIn(), // 全て表示
      select_reset_common_elm.hide(), // すべてのリセットボタンを非表示
      /* リセット：アイテム件数 */
      count_series_number.text(list_filter_elm.length) // 全てのアイテム件数で上書き
    );
    my_promise.done(function () {
      /* すべてのcheckboxのcheckedをリセット */
      select_taxonomy_elm.find('input[type="checkbox"]').prop("checked", false);
    });
    /* キャッシュを利用してリロード（最終手段） */
    //window.location.reload(false);
  });

  /*** セレクトメニューのアコーディオン ***/
  accordion_contents_elm.hide();
  /* タームが0個なら非表示 */
  accordion_contents_elm.each(function () {
    if ($(this).find(select_term_item_elm).length < 1) {
      $(this).closest(select_taxonomy_elm).hide();
    }
  });
  /* クリック：アコーディオンの開閉 */
  accordion_trigger_elm.on("click", "a", function (e) {
    e.preventDefault();
    $(this).closest(select_taxonomy_elm).toggleClass(sub_open);
    $(this)
      .closest(accordion_trigger_elm)
      .next(accordion_contents_elm)
      .fadeToggle("fast");
  });

  /*** チェックボックスを択一に変更 ***/
  $("." + select_toggle)
    .children('input[type="checkbox"]')
    .on("change", function () {
      if ($(this).prop("checked")) {
        /* タクソノミー単位でcheckboxのcheckedをリセット */
        $(this)
          .closest(select_taxonomy_elm)
          .find('input[type="checkbox"]')
          .prop("checked", false);
        $(this).prop("checked", true);
      }
    });

  /******
   * 上記の基本の処理が完了した後の個別のローカル設定
   * かつては「initial.js」と「filter.j」にファイルを分けて非同期でGETしてdoneした後に記述していました。
   * ここより上の記述を「initial.js」とし、以降を「filter.j」としていました。
   * 他のスクリプトでも「initial.js」の処理を流用していたため上記部分を汎用化してエコシステムを作っていました。
   * WPプラグイン化にあたり他のスクリプトとは切り離して考えた際に上下で分離する必要がなくなったため1つのファイルに統合しました。
   ******/

  var loader = $("#ccc-terms_filter_ajax-loader");
  var more_trigger = "ccc-terms_filter_ajax-results-more-trigger"; // 注意：クリックイベントで使用する時には動的要素に変わっているためオブジェクト変数に格納する事は出来ない
  var loop = list_filter; // 注意：動的要素のためオブジェクト変数に格納する事は出来ない
  var looplength_val = $("." + loop).length; // 現在表示中の投稿数を取得（注意：動的要素のためオブジェクト変数に格納する事は出来ない）
  var wp_query_count = "ccc-terms_filter_ajax-wp_query-count"; // 注意：動的要素のためオブジェクト変数に格納する事は出来ない
  var post_count = "ccc-terms_filter_ajax-post-count"; // 注意：動的要素のためオブジェクト変数に格納する事は出来ない
  var found_posts_count = "ccc-terms_filter_ajax-found_posts-count"; // 注意：動的要素のためオブジェクト変数に格納する事は出来ない
  var header_results = $(".ccc-terms_filter_ajax-header");
  var clone_header_results = $("#ccc-terms_filter_ajax-header-clone");
  var article_filter_elm = $("#ccc-terms_filter_ajax-article");
  var article_filter_data_term = "ccc_terms_filter_ajax-article-term";

  /*** 投稿をさらに読み込むトリガーの表示を切り替える条件分岐の関数 ***/
  function more_trigger_toggle() {
    var post_count_val = $("." + loop).length; // 再取得：現在表示中の投稿数を再取得（注意：動的要素のためオブジェクト変数に格納する事は出来ない）
    $("." + post_count).text(post_count_val);
    var found_posts_count_val = $("#" + wp_query_count)
      .children("." + found_posts_count)
      .text();
    if (Number(post_count_val) < Number(found_posts_count_val)) {
      $("#" + more_trigger).fadeIn();
      $("." + post_count).fadeIn();
    } else {
      $("#" + more_trigger).hide();
      $("." + post_count).hide();
    }
    //console.log( post_count_val +'/'+ found_posts_count_val );
    var wp_query_count_html = $("#" + wp_query_count).html();
    count_series_number.html(wp_query_count_html);
    count_series_number.removeAttr("style"); // リセット：該当する投稿数を更新するまで透明にするを解除
    /* header_resultsを複製 */
    clone_header_results.children().remove(); // リセット：cloneが重複するため毎回削除
    if (post_count_val > 16) {
      header_results.clone(true).appendTo(clone_header_results); // clone：引数にtrueをセットすることでイベントもコピー可能
    }
  }

  /*** 絞り込み結果の投稿のタームに選択中のものがあればスタイルを追加する関数 ***/
  function active_list_term_election(event) {
    var event = event || "change"; // 引数の初期値を設定（引数がnullの場合は初期値）
    if (event !== "load") {
      $("." + active_select_term).each(function () {
        var active_data_select_term = $(this).attr("data-select-term");
        $('[data-list-term="' + active_data_select_term + '"]').addClass(
          active_list_term
        );
      });
    }
    var active_data_article_term = article_filter_elm.attr(
      "data-" + article_filter_data_term
    );
    $('[data-list-term="' + active_data_article_term + '"]').addClass(
      active_list_term
    );
  }

  /*** お気に入りの投稿を保存（/ccc-my_favorite/select.js）の「initial」をajaxで呼び出す関数 ****/
  /* ajaxで投稿を動的に生成するためajax通信後に「CCC.favorite.initial()」を呼び出す必要がある */
  function ccc_my_favorite_initial_ajax() {
    /* 注意：他のプラグインのJavaScript変数が未定義の場合に発生するエラーを回避するために"typeof"と"undefined"を使用して判定 */
    if (
      typeof CCC_MY_FAVORITE_UPDATE !== "undefined" &&
      typeof CCC_MY_FAVORITE_GET !== "undefined"
    ) {
      //console.log('wp plugin is exists that my-favorites.');
      if (CCC_MY_FAVORITE_UPDATE.user_logged_in == false) {
        var favorite_key = CCC.favorite.storage_key(); // お気に入りの投稿のストレージキーの名前を変数に格納（CCC.favoriteのstorage_key関数を呼び出し）
        var favorite_value = localStorage.getItem(favorite_key); // ローカルストレージから指定したキーの値を取得
        CCC.favorite.initial(favorite_value); // CCC.favoriteのinitial関数を呼び出し
      } else {
        $.ajax({
          url: CCC_MY_FAVORITE_GET.api, // admin-ajax.phpのパスをローカライズ（wp_localize_script関数）
          type: "POST",
          data: {
            action: CCC_MY_FAVORITE_GET.action, // wp_ajax_フックのサフィックス
            nonce: CCC_MY_FAVORITE_GET.nonce, // wp nonce
          },
        })
          .fail(function () {
            console.log("my_favorite_get : ajax error");
          })
          .done(function (response) {
            var favorite_value = response; // MySQLのユーザーメタ（wp_usermeta）からお気に入りの投稿の値を取得
            //console.log(favorite_value);
            CCC.favorite.initial(favorite_value); // CCC.favoriteのinitial関数を呼び出し
          });
      }
    }
  }

  /*** リセット：全てのセレクトを解除するボタンを押下した時の処理（重要：initial.js側ではタイミング的に不足なので再度実行） ***/
  select_reset_all_elm.on("change", "." + select_toggle, function () {
    $(".js-accordion-trigger").find("a").removeClass(sub_open);
    $(".js-accordion-contents").hide();
  });

  /*** 初回ロード：WPクエリ（wp_ajax_）に送信 ***/
  var post_type = article_filter_elm.data(
    "ccc_terms_filter_ajax-article-post_type"
  );
  var article_taxonomy = article_filter_elm.data(
    "ccc_terms_filter_ajax-article-taxonomy"
  );
  var article_term = article_filter_elm.data(article_filter_data_term);

  var posts_per_page_value = post_area_elm.data(
    "ccc_terms_filter_ajax-posts_per_page-filter"
  ); //末尾 -filter を削除 shortcode-list.php:103 に1箇所

  var data_set = {};
  /*** Ajaxフック用 ***/
  data_set["action"] = CCC_TERMS_FILTER_AJAX.action;
  data_set["nonce"] = CCC_TERMS_FILTER_AJAX.nonce;
  /* 主たるタクソノミーとそのタームを送信データに追加 */
  data_set[article_taxonomy] = article_term;
  /* 投稿の表示数を送信データに追加 */
  data_set["ccc-posts_per_page"] = posts_per_page_value;
  /* 投稿タイプを送信データに追加 */
  if (post_type) {
    data_set["posttype"] = post_type;
  } //endif
  /* 現在表示中の投稿数を送信データに追加 */
  data_set["looplength"] = looplength_val;

  /***** For WordPress Plugin "bogo" : START *****/
  /* WordPressの現在のロケール情報を送信データに追加 */
  var bogo_locale = article_filter_elm.data(
    "ccc_terms_filter_ajax-article-bogo"
  );
  if (bogo_locale) {
    data_set["bogo"] = bogo_locale;
  }
  /***** For WordPress Plugin "bogo" : END *****/

  /* 読み込み中のローディングを表示 */
  loader.show();
  /* WPクエリ（wp_ajax_）にajaxでPOSTして第一引数をhtml */
  $.ajax({
    type: "POST",
    url: CCC_TERMS_FILTER_AJAX.api,
    data: data_set,
  })
    .fail(function () {
      loader.fadeOut();
      alert("error");
    })
    .done(function (response) {
      post_area_elm.html(response);
      active_list_term_election("load"); // 絞り込み結果の投稿のタームに選択中のものがあればスタイルを追加する関数を呼び出し
      more_trigger_toggle(); // 投稿をさらに読み込むトリガーの表示を切り替える条件分岐の関数を呼び出し
      ccc_my_favorite_initial_ajax(); // お気に入りの投稿を保存（/ccc-my_favorite/select.js）の「initial」をajaxで呼び出す関数を呼び出し
      loader.fadeOut();
    });
  //console.log(data_set);

  /*** チェンジ：WPクエリ（wp_ajax_）にリクエスト送信 ***/
  $(document).on("change", "." + select_toggle, function () {
    //出力されたタクソノミーを取得して各タクソノミーごとの識別子を引数にしてフィルタ関数の呼び出しをループする
    nav_taxonomy_elm.find(select_taxonomy_elm).each(function () {
      /* 標準のタクソノミー用 */
      var data_standard = $(this).data(select_taxonomy);
      if (data_standard) {
        data_set["taxname_" + data_standard] = null;
        $("[name='" + data_standard + "[]']:checked").each(function () {
          data_set["taxname_" + data_standard] = this.value;
        });
      } //endif
    });
    //console.log(data_set);
    /*** Ajaxフック用 ***/
    data_set["action"] = CCC_TERMS_FILTER_AJAX.action;
    data_set["nonce"] = CCC_TERMS_FILTER_AJAX.nonce;
    /* リセット：現在表示中の投稿数をリセットして送信データに追加 */
    data_set["looplength"] = 0;
    /* 該当する投稿数を更新するまで透明にする */
    count_series_number.css({ opacity: "0" });
    /* 読み込み中のローディングを表示 */
    loader.show();
    /* WPクエリ（/ajax/wp_query.php）にajaxでPOSTして第一引数をhtml */
    $.ajax({
      type: "POST",
      url: CCC_TERMS_FILTER_AJAX.api,
      data: data_set,
    })
      .fail(function () {
        loader.fadeOut();
        alert("error");
      })
      .done(function (response) {
        post_area_elm.html(response);
        active_list_term_election(); // 絞り込み結果の投稿のタームに選択中のものがあればスタイルを追加する関数を呼び出し
        more_trigger_toggle(); // 投稿をさらに読み込むトリガーの表示を切り替える条件分岐の関数を呼び出し
        ccc_my_favorite_initial_ajax(); // お気に入りの投稿を保存（/ccc-my_favorite/select.js）の「initial」をajaxで呼び出す関数を呼び出し
        select_taxonomy_elm.removeClass(sub_open); // "v1.2.0"で追加（絞り込み結果表示後に標準セレクトメニューを非表示）
        accordion_contents_elm.hide(); // "v1.2.0"で追加（絞り込み結果表示後に標準セレクトメニューを非表示）
        loader.fadeOut();
      });
    //console.log(data_set);
  });

  /* クリック（さらに読み込むトリガー）：検索結果を表示する関数を呼び出し */
  $(document).on("click", "#" + more_trigger, function (e) {
    e.preventDefault();
    /*** Ajaxフック用 ***/
    data_set["action"] = CCC_TERMS_FILTER_AJAX.action;
    data_set["nonce"] = CCC_TERMS_FILTER_AJAX.nonce;
    /* ループ数を更新：表示中の投稿数を再取得してdataオブジェクトのkey（ループ数）のvalue（値）を更新 */
    var looplength_val = $("." + loop).length; // 再取得：現在表示中の投稿数を再取得（注意：動的要素のためオブジェクト変数に格納する事は出来ない）
    data_set["looplength"] = looplength_val;
    /* 読み込み中のローディングを表示 */
    loader.show();
    /* WPクエリ（/ajax/wp_query.php）にajaxでPOSTして第一引数をappend */
    $.ajax({
      type: "POST",
      url: CCC_TERMS_FILTER_AJAX.api,
      data: data_set,
    })
      .fail(function () {
        loader.fadeOut();
        alert("error");
      })
      .done(function (response) {
        post_area_elm.append(response);
        active_list_term_election(); // 絞り込み結果の投稿のタームに選択中のものがあればスタイルを追加する関数を呼び出し
        more_trigger_toggle(); // 投稿をさらに読み込むトリガーの表示を切り替える条件分岐の関数を呼び出し
        ccc_my_favorite_initial_ajax(); // お気に入りの投稿を保存（/ccc-my_favorite/select.js）の「initial」をajaxで呼び出す関数を呼び出し
        loader.fadeOut();
      });
    //console.log(data_set);
  });

  /****** フィルター用のタクソノミーセレクトの表示/非表示を切り替える（※初期はスマホのみに設定しています。CSSのみで変更可能） ******/
  if (select_term_item_elm.length < 1) {
    article_filter_elm.find(".nav-taxonomy-toggle-button").hide();
  }
  article_filter_elm
    .find(".nav-taxonomy-toggle-button")
    .on("click", function (e) {
      e.preventDefault();
      $(this).toggleClass("active");
      article_filter_elm.find(nav_taxonomy_elm).toggleClass("tax-oppend");
    });
})(jQuery);
