<?php

/*

Plugin Name: Castlegate IT WP User Guide Contents
Plugin URI: http://github.com/castlegateit/cgit-wp-user-guide-contents
Description: Add a table of contents to the Castlegate IT WordPress User Guide plugin.
Version: 1.0
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: MIT

*/

add_action('plugins_loaded', function() {

    $name = 'Cgit\UserGuide';

    // If the user guide plugin is not installed or if the user guide plugin
    // is a recent version that already supports tables of contents, do
    // nothing.
    if (!class_exists($name) || method_exists($name, 'addSection')) {
        return;
    }

    // Enqueue CSS
    add_action('admin_enqueue_scripts', function($hook) {
        if ($hook != 'toplevel_page_cgit-user-guide') {
            return;
        }

        $url = plugin_dir_url(__FILE__);

        wp_enqueue_style(
            'cgit-wp-user-guide-contents',
            $url . 'css/user-guide-contents.css'
        );
    });

    /**
     * Generate table of contents
     *
     * Extracts the first heading from each section and adds it to the table of
     * contents. Adds an empty element with the correct ID to allow linking.
     * Also adds "Back to top" links to the end of each section.
     *
     * The table of contents is added to the start of the user guide as another
     * section.
     */
    add_filter('cgit_user_guide_sections', function($sections) {
        $pattern = '/<(h[1-6])[^>]*>(.+?)<\/\1>/i';
        $back = '<p class="cgit-user-guide-contents-back">'
            . '<a href="#">Back to top</a></p>';
        $contents = '<h3>Contents</h3>'
            . '<ol class="cgit-user-guide-contents-list">';
        $headings = array();
        $number = 0;

        foreach ($sections as &$section) {

            $match = preg_match($pattern, $section, $matches);
            $id = 'cgit-user-guide-section-' . $number;
            $headings[$id] = strip_tags($matches[2]);
            $section = '<span id="' . $id
                . '" class="cgit-user-guide-contents-anchor"></span>'
                . $section . $back;
            $number++;

        }

        if (count($headings) == 0) {
            return $sections;
        }

        foreach ($headings as $id => $heading) {
            $contents .= '<li><a href="#' . $id . '">' . $heading . '</a></li>';
        }

        $contents .= '</ol>';
        array_unshift($sections, $contents);

        return $sections;
    }, 9999);

}, 20);
