<?php
/*
   Plugin Name: Article Terminology
   Plugin URI: http://wordpress.org/extend/plugins/article-terminology/
   Version: 1.3
   Author: Alex Disertinsky
   Description: Плагин для составления словарей терминов, найденных в записях 
   Text Domain: article-terminology
   License: GPLv3
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_VERSION', '1.0.0' );
require_once plugin_dir_path( __FILE__ ) . 'includes/article-terminology-class.php';

function activate_article_terminology() {
	ActiveTerminology::activate();
}

function deactivate_article_terminology() {
	ActiveTerminology::deactivate();
}

function run_article_terminology() {
	$plugin = new ActiveTerminology();
}
add_action( 'init', 'activate_article_terminology' );
run_article_terminology();