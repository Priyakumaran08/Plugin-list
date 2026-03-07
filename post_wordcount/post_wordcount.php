<?php
/**
 * Plugin Name: Post Word Count
 * Description: Shows word count for each post in admin.
 * Version: 1.0
 * Author: DevyMuse
 */

function pwc_get_word_count($post_id) {

    $post = get_post($post_id);

    if (!$post) {
        return 0;
    }

    $content = strip_tags($post->post_content);

    $word_count = str_word_count($content);

    return $word_count;
}

function pwc_add_wordcount_column($columns) {

    $columns['wordcount'] = 'Word Count';

    return $columns;
}

add_filter('manage_posts_columns', 'pwc_add_wordcount_column');

function pwc_show_wordcount_column($column_name, $post_id) {

    if ($column_name == 'wordcount') {

        echo pwc_get_word_count($post_id);

    }

}

add_action('manage_posts_custom_column', 'pwc_show_wordcount_column', 10, 2);

function pwc_display_wordcount($content) {

    if (is_single() && is_main_query()) {

        $word_count = str_word_count(strip_tags($content));

        $wordcount_html = "<p style='font-weight:bold;'>Word Count: " . $word_count . "</p>";

        $content .= $wordcount_html;
    }

    return $content;
}

add_filter('the_content', 'pwc_display_wordcount');