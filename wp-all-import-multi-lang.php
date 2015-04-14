<?php
/*
Plugin Name: WP All Import Multi Lang
Description: Add-on to migrate existing multi language content to WPML
Version: 0.9
Author: Niels Harland @ ticketscript b.v.
*/


include "rapid-addon.php";

global $sitepress;

$wpai_wpml = new RapidAddon('WP All Import Multi Lang', 'wpai_wpml');

foreach($sitepress->get_active_languages() as $language){
    $wpai_wpml->add_field('wpaiml_'.$language['code'], 'Code for ' . $language['display_name'], 'text');
}

$wpai_wpml->run();

/*
$wpai_wpml->set_import_function("wpaiwpmlImport");

function wpaiwpmlImport($post_id, $data, $import_options)
{

}
*/