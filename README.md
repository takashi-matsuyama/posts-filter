# Posts Filter

This is the development repository for Posts Filter, a WordPress plugin that filters posts by taxonomy terms with Ajax. You can also download the plugin package installation from the [WordPress.org Plugin Directory](https://wordpress.org/plugins/posts-filter/).

Contributors: takashimatsuyama  
Donate link:  
Tags: posts filter, filter, taxonomy, term  
Requires at least: 4.8  
Tested up to: 5.7  
Requires PHP: 5.4.0  
Stable tag: 1.1.3  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Filter posts by taxonomy terms with Ajax.

## Description

Filter posts by taxonomy terms with Ajax and list them.

This plugin is simple. You can filter posts by taxonomy terms with Ajax just a install and display them anywhere you want with just a shortcode.

## Usage

* **Shortcode:** `[ccc_posts_filter_list post_type="" posts_per_page="" class="" style=""]`

Detailed usage is under preparation.

## Installation

1. Upload `posts-filter` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use shortcodes to display the posts filter.

## Changelog

### 1.1.3
Fixed Undefined variable warning.

### 1.1.2
Fixed PHP 8.0 warning.

### 1.1.1
[Bug fix] About is_plugin_active not working when locale="bogo".

### 1.1.0
Add shortcode attribute (`locale=""`) markup of thumbnails and modify CSS.

### 1.0.1
Add shortcode attribute (`style=""`) and modify CSS.

### 1.0.0
Initial release.