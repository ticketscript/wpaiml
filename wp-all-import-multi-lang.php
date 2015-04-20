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
$logger = create_function('$m', 'echo "<p>$m</p>\\n";');

$wpaiml->add_field('wpaiml_content_language', 'Content language', 'radio', $language_list, 'The language of the content to be importerd');
$wpaiml->add_field('wpaiml_source_key', 'Source unique key', 'text', false, 'The WP All Import key to reference the source content. Example: {title[1]} - {id[1]} - {url_title[1]}');
$wpaiml->add_field('wpaiml_source_language', 'Source language', 'radio', $language_list, 'The language of the exisitng source post referenced by the unique key above');

$wpaiml->set_import_function("wpaiwpmlImport");

$wpaiml->run();

function wpaiwpmlImport($post_id, $data, $import_options)
{
    global $sitepress, $language_list, $logger;
    $logger and call_user_func($logger, __("<strong>WPAI MULTI LANG: </strong>", 'wp_all_import_plugin'));

    if(!empty($data['wpaiml_content_language']) && !empty($data['wpaiml_source_key'])){
        $pmxi_post = new PMXI_Post_Record();
        $source_post = $pmxi_post->getByunique_key($data['wpaiml_source_key']);
        $content_lang = $data['wpaiml_content_language'];
        $post_type = $import_options['options']['custom_type'];
        $el_type = 'post_' . $post_type;

        if ($source_post){
            $source_lang = $sitepress->get_language_for_element($source_post->post_id, $el_type);
            if ($source_lang && $source_lang != $content_lang){
                $translation_source = $sitepress->get_element_language_details($source_post->post_id, $el_type);
                $translation_map = $sitepress->get_element_translations($translation_source->trid);
                if (array_key_exists($content_lang, $translation_map)){
                    $logger and call_user_func($logger, __("- <strong>WARNING:</strong> {$translation_map[$content_lang]->element_id} already set as {$language_list[$content_lang]} translation for {$source_post->post_id}", 'wp_all_import_plugin'));
                } else {
                    if($sitepress->set_element_language_details($post_id, $el_type, $translation_source->trid, $content_lang)){
                        $logger and call_user_func($logger, __("- Updating $post_id as {$language_list[$content_lang]} translation of {$language_list[$source_lang]} source $post_type {$source_post->post_id}", 'wp_all_import_plugin'));
                    } else {
                        $logger and call_user_func($logger, __("- <strong>ERROR:</strong> Unable to set $post_id as {$language_list[$content_lang]} translation of {$language_list[$source_lang]} source $post_type {$source_post->post_id}", 'wp_all_import_plugin'));
                    }
                }

            }else {
                $logger and call_user_func($logger, __("- <strong>ERROR:</strong> Source language is not valid!", 'wp_all_import_plugin'));
            }
        }else{
            $logger and call_user_func($logger, __("- <strong>ERROR:</strong> Source post not found for key: <em>" . $data['wpaiml_source_key'] . "</em>", 'wp_all_import_plugin'));
        }
    }
}