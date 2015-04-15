<?php
/*
Plugin Name: WP All Import Multi Lang
Description: Add-on to migrate existing multi language content to WPML
Version: 0.9
Author: Niels Harland @ ticketscript b.v.
*/


include "vendor/autoload.php";

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

$GLOBALS[ 'wp_log_plugins' ][] = 'wpaiml';

$wpaiml = new RapidAddon('WP All Import Multi Lang', 'wpaiml');

global $sitepress;
$default_lang = $sitepress->get_default_language();
$active_languages = ( array ) $sitepress->get_active_languages();
$language_list = array_column($active_languages, 'display_name', 'code');
//{title[1]} - {id[1]} - {url_title[1]}

$wpaiml->add_field('wpaiml_content_language', 'Content language', 'radio', $language_list, 'The language of the content to be importerd');
$wpaiml->add_field('wpaiml_source_key', 'Source unique key', 'text', false, 'The WP All Import key to reference the source content. Example: {title[1]} - {id[1]} - {url_title[1]}');
$wpaiml->add_field('wpaiml_source_language', 'Source language', 'radio', $language_list, 'The language of the exisitng source post referenced by the unique key above');

$wpaiml->set_import_function("wpaiwpmlImport");

$wpaiml->run();

function wpaiwpmlImport($post_id, $data, $import_options)
{
    global $sitepress, $language_list;
    if(!empty($data['wpaiml_content_language']) && !empty($data['wpaiml_source_key'])){
        $pmxi_post = new PMXI_Post_Record();
        $source_post = $pmxi_post->getByunique_key($data['wpaiml_source_key']);
        $source_lang = $language_list[$sitepress->get_language_for_element($source_post->post_id, 'post_post')];
        $content_lang = $language_list[$data['wpaiml_content_language']];

        $GLOBALS[ 'wp_log' ][ 'wpaiml' ][] = "Updating $post_id as $content_lang translation of $source_lang source post {$source_post->post_id}";
    }
}
