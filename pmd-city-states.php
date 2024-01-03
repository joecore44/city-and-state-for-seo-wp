<?php
/*
Plugin Name: PMD City States
Description: A simple plugin to inject city and state names into content and display a list of cities in the footer.
Version: 1.0
Author: Joe Shepard
*/

// Add action hook to modify meta tags
add_action('wp_head', 'update_meta_tags');

// Function to update meta tags
function update_meta_tags() {
    $state = state_name_shortcode();
    $city = city_name_shortcode([]);

    if (!empty($state)) {
 
        $meta_title = "Testosterone Replacement Therapy";


        if (!empty($city)) {
            $meta_title .= " in {$city}, {$state}";
        } else {
            $meta_title .= " in {$state}";
        }

        echo '<title>' . esc_html($meta_title) . '</title>' . "\n";
        // Update og:title
        echo '<meta property="og:title" content="' . esc_attr($meta_title) . '" />' . "\n";

        // Initialize meta description
        $meta_description = "Looking for testosterone replacement therapy near {$state}";

        if (!empty($city)) {
            $meta_description .= " {$city}";
        }

        echo '<meta property="og:description" content="' . esc_attr($meta_description) . '" />' . "\n";

        $og_image_url = 'https://getpetermd.com/wp-content/uploads/Drawer_Box-new.png';

        // Update og:image
        echo '<meta property="og:image" content="' . esc_url($og_image_url) . '" />' . "\n";

        // Update og:url
        $current_url = home_url(add_query_arg(array(), $wp->request));
        echo '<meta property="og:url" content="' . esc_url($current_url) . '" />' . "\n";

        // Update og:type
        echo '<meta property="og:type" content="website" />' . "\n";

        // Update og:site_name
        $site_name = get_bloginfo('name');
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />' . "\n";
    }
}


// Enqueue styles to fix ugly tables
function enqueue_custom_styles() {

    global $post;
    $target_shortcodes = array('state_name', 'city_name', 'state_data', 'states_data', 'display_name', 'service_state_data');

    foreach ($target_shortcodes as $shortcode) {
        wp_enqueue_style('custom-table-styles', plugin_dir_url(__FILE__) . 'wp-city-table-styles.css?v=22');
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, $shortcode)) {
            // Enqueue the custom CSS
            wp_enqueue_style('custom-table-styles', plugin_dir_url(__FILE__) . 'wp-city-table-styles.css?v=20');
            break; 
        }
    }
}

add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

// Register the shortcodes
add_shortcode('state_name', 'state_name_shortcode');
add_shortcode('city_name', 'city_name_shortcode');
add_shortcode('state_data', 'state_data_shortcode');
add_shortcode('states_data', 'states_data_shortcode');
add_shortcode('display_name', 'display_name_shortcode');
add_shortcode('service_state_data', 'service_state_data_shortcode');

// Rewrite rules for state and city paraemters
function custom_rewrite_rules() {
    add_rewrite_rule(
        '^affordable-trt-page/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=53861&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^trt-for-men/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55727&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^ketamine-therapy/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55385&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^peptide-therapy/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55386&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^semiglutide-therapy/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55390&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^seboxone-treatment/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55387&state=$matches[1]&city=$matches[2]',
        'top',
    );
    add_rewrite_rule(
        '^womens-hormone-replacement-therapy/([^/]+)(?:/([^/]+))?/?$',
        'index.php?page_id=55389&state=$matches[1]&city=$matches[2]',
        'top',
    );

}

add_action('init', 'custom_rewrite_rules');

function custom_query_vars($query_vars) {
    $query_vars[] = 'state';
    $query_vars[] = 'city';
    return $query_vars;
}

add_filter('query_vars', 'custom_query_vars');

function state_name_shortcode() {
    $state_name = get_query_var('state');
    return ucwords($state_name);
}

function city_name_shortcode($atts) {
    $city_name = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
    $city_name = get_query_var('city');

    if (empty($city_name)) {
        global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $url_parts = explode('/', $current_url);

        if (count($url_parts) > 2) {

            $city_name = $url_parts[count($url_parts) - 2];

            
            $city_name_parts = explode('?', $city_name);
            $city_name = $city_name_parts[0];
        }
    }

    // Remove any non-alphanumeric characters
    $city_name = preg_replace('/[^a-zA-Z0-9]/', ' ', $city_name);
    $city_name = ucwords($city_name) . ' ' . ucwords(str_replace('-', ' ', $state));

    if (!empty($city_name)) {
        $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

        foreach ($city_data as $state => $cities) {
            foreach ($cities as $city) {
                $city = trim($city);
                $city_name = trim($city_name);
                if ($city === $city_name) {
                    return $city;
                }
            }
        }
    }

    return ''; 
}


function state_data_shortcode($atts) {
    $state_name = state_name_shortcode($atts);
    $state_key = ucwords(str_replace('-', ' ', $state_name));
    
    $current_url = $_SERVER['REQUEST_URI'];
    $segments = explode('/', trim($current_url, '/'));
    $service_slug = isset($segments[0]) ? $segments[0] : '';
    

    $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

    if (isset($city_data[$state_key])) {
        $cities = $city_data[$state_key];
        $num_cities = count($cities);

        $num_columns = 3;
        $num_rows = ceil($num_cities / $num_columns);

        $html = '<div class="container">';
        $html .= '<h3>List of Cities in '.$state_key.'</h3>';
        $html .= '<table class="table table-striped">';

        for ($index = 0; $index < $num_cities; $index++) {
            if ($index % 4 === 0) {
                $html .= '<tr>';
            }

            $city = $cities[$index];
            $city_slug = sanitize_title($city);

            $hyperlink_title_text = 'Click here to learn more about Affordable TRT in ' . $city . ', ' . $state_key . '.';

            $url = 'https://getpetermd.com/' . $service_slug . '/' . $state_key . '/' . $city_slug;

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
    $state_name = '';

    $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

    $html = '<div class="container">';
    $html .= '<h3>List of States PeterMD Offers Affordable Telehealth Services</h3>';
    $html .= '<table class="table table-striped">';

    $column_count = 0;

    foreach ($city_data as $state => $cities) {

        if ($column_count % 4 === 0) {
            $html .= '<tr>';
        }

        $state_slug = sanitize_title($state);
        $url = 'https://getpetermd.com/affordable-trt-page/' . $state_slug;

        $hyperlink_title_text = 'Click here to learn more about affordable online TRT in ' . $state . '.';

        $html .= '<td>';
        $html .= '<p><a href="' . esc_url($url) . '" title="' . $hyperlink_title_text . '">' . esc_html($state) . '</a></p>';
        $html .= '</td>';

        if (($column_count + 1) % 4 === 0 || $column_count === count($city_data) - 1) {
            $html .= '</tr>';
        }

        $column_count++;
    }

    $html .= '</table>';
    $html .= '</div>';

    return $html;
}

function display_name_shortcode() {
    $state = state_name_shortcode();
    $city = city_name_shortcode([]);

    if (!empty($state)) {
        $display_name = $state;

        if (!empty($city)) {
            $display_name = $city . ', ' . $state;
        }

       return $display_name;
    }

    return '';
}

function service_state_data_shortcode($atts) {
    $service_title = isset($atts['service_title']) ? sanitize_text_field($atts['service_title']) : '';
    $service_slug = sanitize_title($service_title);

    $city_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'cities.json'), true);

    $html .= '<style>';
    $html .= 'caption { text-align: left; }';
    $html .= '</style>';
    $html = '<div class="container">';
    $html .= '<div class="table-responsive">';
    $html .= '<table class="table table-striped">';
    $html .= '<caption><h3 align="left">List of States PeterMD Offers Affordable ' . esc_html($service_title) . '</h3></caption>';
    $html .= '<thead>';
    $html .= '<tr>';

    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $column_count = 0;

    foreach ($city_data as $state => $cities) {
        // Open a new row for every 4 states
        if ($column_count % 4 === 0) {
            $html .= '<tr>';
        }

        $state_slug = sanitize_title($state);

        $url = 'https://getpetermd.com/' . $service_slug . '/' . $state_slug;

        $hyperlink_title_text = 'Click here to learn more about ' . esc_html($service_title) . ' in ' . $state . '.';

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
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>'; // Close the container

    return $html;
}

// Usage: [service_state_data service_title="Affordable TRT"]









