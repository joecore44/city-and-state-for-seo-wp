<?php
/*
Plugin Name: PMD City States
Description: A simple plugin to inject city and state names into content and display a list of cities in the footer.
Version: 1.0
Author: Joe Shepard
*/

// Register the shortcodes
add_shortcode('state_name', 'state_name_shortcode');
add_shortcode('city_name', 'city_name_shortcode');
add_shortcode('state_data', 'state_data_shortcode');
add_shortcode('states_data', 'states_data_shortcode');

// Rewrite rules for state and city paraemters
function custom_rewrite_rules() {
    add_rewrite_rule(
        '^affordable-trt-page/([^/]+)/?$',
        'index.php?page_id=53861&state=$matches[1]',
        'top'
    );
}

add_action('init', 'custom_rewrite_rules');

function custom_query_vars($query_vars) {
    $query_vars[] = 'state';
    return $query_vars;
}

add_filter('query_vars', 'custom_query_vars');

function state_name_shortcode() {
    // Attempt to directly access the state name from the URL
    $state_name = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';

    // If the state name is not in the query parameter, try to get it from the URL structure
    if (empty($state_name)) {
        global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $url_parts = explode('/', $current_url);

        // Get the last part of the URL (state name)
        $state_name = end($url_parts);

        // Remove any additional parameters
        $state_name_parts = explode('?', $state_name);
        $state_name = $state_name_parts[0];
    }

    // Remove any non-alphanumeric characters
    $state_name = preg_replace('/[^a-zA-Z0-9]/', '', $state_name);

    return $state_name;
}


function city_name_shortcode($atts) {
    return isset($_GET['city_name']) ? sanitize_text_field($_GET['city_name']) : '';
}


function state_data_shortcode($atts) {
    $state_name = state_name_shortcode($atts);
    $state_key = ucwords(str_replace('-', ' ', $state_name));
    

    $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

    // Check if the state exists in the JSON data
    if (isset($city_data[$state_key])) {
        $cities = $city_data[$state_key];
        $num_cities = count($cities);

        // Calculate the number of rows and columns for an even layout
        $num_columns = 3; // Adjust this value based on your preference
        $num_rows = ceil($num_cities / $num_columns);

        // Output a Bootstrap table with even columns
        $html = '<div class="container">';
        $html .= '<h3>List of Cities in '.$state_key.'</h3>';
        $html .= '<table class="table table-striped">';

        // Loop through cities
        for ($index = 0; $index < $num_cities; $index++) {
            // Open a new row for every 4 links
            if ($index % 4 === 0) {
                $html .= '<tr>';
            }

            $city = $cities[$index];
            $city_slug = sanitize_title($city);

            // Initialize the title text for each link
            $hyperlink_title_text = 'Click here to learn more about Affordable TRT in ' . $city . ', ' . $state_key . '.';

            // Create a hyperlink with the desired URL structure
            $url = 'https://getpetermd.com/affordable-trt-page/' . $state_key . '/' . $city_slug;

            // Create a table cell with the link
            $html .= '<td>';
            $html .= '<p><a href="' . esc_url($url) . '" title="' . $hyperlink_title_text . '">' . esc_html($city) . '</a></p>';
            $html .= '</td>';

            // Close the row for every 4 links
            if (($index + 1) % 4 === 0 || $index === $num_cities - 1) {
                $html .= '</tr>';
            }
        }

        $html .= '</table>';
        $html .= '</div>'; // Close the container

        return $html;
    } else {
        return empty_state_data_shortcode($atts);
    }
    
}    

function empty_state_data_shortcode($atts) {
    $state_name = state_name_shortcode($atts);

    $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

    // Display a list of states in a Bootstrap table
    $html = '<div class="container">';
    $html .= '<h3>List of States PeterMD Offers Affordable TRT</h3>';
    $html .= '<table class="table table-striped">';

    $column_count = 0;

    foreach ($city_data as $state => $cities) {
        // Open a new row for every 4 states
        if ($column_count % 4 === 0) {
            $html .= '<tr>';
        }

        $state_slug = sanitize_title($state);
        $url = 'https://getpetermd.com/affordable-trt-page/' . $state_slug;

        // Initialize the title text for each link
        $hyperlink_title_text = 'Click here to learn more about affordable online TRT in ' . $state . '.';

        // Create a table cell with the link
        $html .= '<td>';
        $html .= '<p><a href="' . esc_url($url) . '" title="' . $hyperlink_title_text . '">' . esc_html($state) . '</a></p>';
        $html .= '</td>';

        // Close the row for every 4 states
        if (($column_count + 1) % 4 === 0 || $column_count === count($city_data) - 1) {
            $html .= '</tr>';
        }

        $column_count++;
    }

    $html .= '</table>';
    $html .= '</div>'; // Close the container

    return $html;
}


