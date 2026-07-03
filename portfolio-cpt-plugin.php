<?php

/**
 * Plugin Name: Portfolio CPT Plugin
 * Description: Custom post types for my portfolio
 * Version: 1.0.0
 * Author: Mella
 * Text Domain: portfolio-cpt
 */

if (!defined('ABSPATH')) {
    exit;
}



/** Add admin files */

function portfolio_enqueue_admin_assets($hook)
{

    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    global $post_type;
    if ('project' !== $post_type && 'experience' !== $post_type) {
        return;
    }

    wp_enqueue_editor();

    // JS
    wp_enqueue_script(
        'portfolio-admin',
        plugin_dir_url(__FILE__) . 'admin-scripts.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // CSS
    wp_enqueue_style(
        'portfolio-admin-style',
        plugin_dir_url(__FILE__) . 'admin-style.css',
        array(),
        '1.0.0'
    );
}

add_action('admin_enqueue_scripts', 'portfolio_enqueue_admin_assets');


function about_enqueue_scripts($hook)
{
    // Just about page
    if ($hook !== 'toplevel_page_about-settings') {
        return;
    }

    // Media uploader
    wp_enqueue_media();

    // Load Scripts
    wp_enqueue_script(
        'portfolio-admin',
        plugin_dir_url(__FILE__) . 'admin-scripts.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_style(
        'portfolio-admin-style',
        plugin_dir_url(__FILE__) . 'admin-style.css',
        array(),
        '1.0.0'
    );
}

add_action('admin_enqueue_scripts', 'about_enqueue_scripts');



function portfolio_enqueue_taxonomy_assets($hook)
{
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'technology') {
        return;
    }
    wp_enqueue_script(
        'portfolio-admin',
        plugin_dir_url(__FILE__) . 'admin-scripts.js',
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_style(
        'portfolio-admin-style',
        plugin_dir_url(__FILE__) . 'admin-style.css',
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'portfolio_enqueue_taxonomy_assets');


// Register CPT 

function projects_register_cpt()
{
    register_post_type('project', array(
        'labels' => array(
            'name' => 'Projects',
            'singular_name' => 'Project',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Project',
            'edit_item' => 'Edit Project',
            'new_item' => 'New Project',
            'view_item' => 'View Project',
            'search_items' => 'Search Projects',
            'not_found' => 'No projects found',
            'not_found_in_trash' => 'No projects found in trash',
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'project',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'thumbnail', 'excerpt'),
    ));
}

add_action('init', 'projects_register_cpt');


// Register Custom Taxonomies 

function projects_register_taxonomies()
{
    register_taxonomy('project_type', 'project', array(
        'labels' => array(
            'name' => 'Project Types',
            'singular_name' => 'Project Type',
        ),
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
    ));

    register_taxonomy('technology',  array('project', 'experience'), array(
        'labels' => array(
            'name' => 'Technologies',
            'singular_name' => 'Technology',
        ),
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'show_ui' => true,
        'meta_box_cb' => 'post_tags_meta_box',
    ));
}
add_action('init', 'projects_register_taxonomies');


//Register Common Techs
function projects_insert_default_technologies()
{

    if (!taxonomy_exists('technology')) {
        return;
    }

    $technologies = array(
        'Figma',
        'Adobe XD',
        'Adobe Illustrator',
        'Adobe Photoshop',
        'React',
        'Vue.js',
        'Angular',
        'Next.js',
        'WordPress',
        'PHP',
        'JavaScript',
        'TypeScript',
        'Tailwind CSS',
        'HTML/CSS',
        'Git',
        'MySQL',
        'Woocomerce',
    );

    foreach ($technologies as $tech) {
        if (!term_exists($tech, 'technology')) {
            wp_insert_term($tech, 'technology');
        }
    }
}

add_action('init', 'projects_insert_default_technologies', 20);



// Checkbox new technology
function technology_add_main_skill_field()
{
?>
    <div class="form-field">
        <label>
            <input type="checkbox" name="is_main_skill" id="is_main_skill_new" value="1">
            Main Skill (show in About page)
        </label>
    </div>
    <div class="form-field" id="skill_order_field_new" style="display:none;">
        <label for="skill_order">Display Order</label>
        <input type="number" name="skill_order" id="skill_order" min="1" step="1">
        <p>Lower number = shown first.</p>
    </div>
<?php
}
add_action('technology_add_form_fields', 'technology_add_main_skill_field');

// Checkbox edit technology existent
function technology_edit_main_skill_field($term)
{
    $is_main = get_term_meta($term->term_id, 'is_main_skill', true);
    $order   = get_term_meta($term->term_id, 'skill_order', true);
?>
    <tr class="form-field">
        <th scope="row"><label>Is this a main skill?</label></th>
        <td>
            <input type="checkbox" name="is_main_skill" id="is_main_skill_edit" value="1" <?php checked($is_main, '1'); ?>>
        </td>
    </tr>
    <tr class="form-field" id="skill_order_field_edit" style="<?php echo $is_main ? '' : 'display:none;'; ?>">
        <th scope="row"><label for="skill_order">Display Order</label></th>
        <td>
            <input type="number" name="skill_order" id="skill_order"
                value="<?php echo esc_attr($order); ?>"
                min="1" step="1">
            <p class="description">Lower number = shown first.</p>
        </td>
    </tr>
<?php
}
add_action('technology_edit_form_fields', 'technology_edit_main_skill_field');

// Save checkbox
function save_technology_main_skill($term_id)
{
    if (isset($_POST['is_main_skill'])) {
        update_term_meta($term_id, 'is_main_skill', '1');
    } else {
        delete_term_meta($term_id, 'is_main_skill');
    }

    if (isset($_POST['skill_order']) && $_POST['skill_order'] !== '') {
        update_term_meta($term_id, 'skill_order', absint($_POST['skill_order']));
    } else {
        delete_term_meta($term_id, 'skill_order');
    }
}
add_action('created_technology', 'save_technology_main_skill');
add_action('edited_technology', 'save_technology_main_skill');

// Add is_main_skill to REST API
function technology_register_main_skill_rest()
{
    register_rest_field('technology', 'is_main_skill', [
        'get_callback' => function ($object) {
            return get_term_meta($object['id'], 'is_main_skill', true) === '1';
        },
        'schema' => null,
    ]);

    register_rest_field('technology', 'skill_order', [
        'get_callback' => function ($object) {
            $val = get_term_meta($object['id'], 'skill_order', true);
            return $val !== '' ? (int) $val : null;
        },
        'schema' => null,
    ]);
}
add_action('rest_api_init', 'technology_register_main_skill_rest');



// Register Custom Metaboxes 

function projects_add_meta_box()
{
    add_meta_box(
        'project_details',
        'Project Details',
        'projects_render_meta_box',
        'project',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'projects_add_meta_box');

function projects_render_meta_box($post)
{
    wp_nonce_field('projects_save_meta', 'projects_meta_nonce');
    $client = get_post_meta($post->ID, '_project_client', true);
    $duration = get_post_meta($post->ID, '_project_duration', true);
    $role = get_post_meta($post->ID, '_project_role', true);
    $link = get_post_meta($post->ID, '_project_link', true);
?>

    <div class="portfolio-row">
        <div class="portfolio-field col-2 ">
            <label for="project_client">Client:</label>
            <input type="text" id="project_client" name="project_client" value="<?php echo esc_attr($client); ?>">
        </div>
        <div class="portfolio-field col-2 ">
            <label for="project_role">Role:</label>
            <input type="text" id="project_role" name="project_role" value="<?php echo esc_attr($role); ?>" placeholder="e.g., Lead UX/UI Designer">
        </div>
        <div class="portfolio-field col-2">
            <label for="project_link">External Link (optional):</label>
            <input type="text" id="project_link" name="project_link" value="<?php echo esc_attr($link); ?>" placeholder="www...">
        </div>

        <div class="portfolio-field col-2 ">
            <label for="project_duration">Duration:</label>
            <input type="text" id="project_duration" name="project_duration" value="<?php echo esc_attr($duration); ?>" placeholder="e.g., 3 months">
        </div>
    </div>

<?php

}



function projects_save_meta($post_id)
{

    if (!isset($_POST['projects_meta_nonce']) || !wp_verify_nonce($_POST['projects_meta_nonce'], 'projects_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['project_client'])) {
        update_post_meta($post_id, '_project_client', sanitize_text_field($_POST['project_client']));
    }

    if (isset($_POST['project_duration'])) {
        update_post_meta($post_id, '_project_duration', sanitize_text_field($_POST['project_duration']));
    }

    if (isset($_POST['project_role'])) {
        update_post_meta($post_id, '_project_role', sanitize_text_field($_POST['project_role']));
    }

    if (isset($_POST['project_link'])) {
        update_post_meta($post_id, '_project_link', esc_url_raw($_POST['project_link']));
    }
}

add_action('save_post_project', 'projects_save_meta');



function projects_add_content_metabox()
{
    add_meta_box(
        'project_content',
        'Project Content',
        'projects_render_content_metabox',
        'project',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'projects_add_content_metabox');




function projects_render_content_metabox($post)
{
    wp_nonce_field('projects_save_content', 'project_content_nonce');

    $items = get_post_meta($post->ID, '_project_content', true);
    if (!is_array($items)) $items = [];
?>

    <div id="project-content-wrapper">

        <?php foreach ($items as $index => $item): ?>
            <div class="repeater-item">
                <div class="flex-row space-between ">
                    <h4>Item <?php echo $index + 1; ?></h4>
                    <div class="portfolio-field full-width-field  <?php echo $index === 0 ? '' : 'hidden'; ?>">
                        <div class="fullwidth-toggle flex-row gap-8">
                            <input type="checkbox" name="project_content[<?php echo $index; ?>][full_width]" value="1" <?php checked($item['full_width'] ?? '', '1'); ?>>
                            <label> Full width </label>
                        </div>
                    </div>
                </div>

                <div class="portfolio-row ">
                    <div class="portfolio-field col-2">
                        <label>Title</label>
                        <input type="text" name="project_content[<?php echo $index; ?>][title]" value="<?php echo esc_attr($item['title'] ?? ''); ?>">
                    </div>
                    <div class="portfolio-field col-2">
                        <label>Subtitle</label>
                        <input type="text" name="project_content[<?php echo $index; ?>][subtitle]" value="<?php echo esc_attr($item['subtitle'] ?? ''); ?>">
                    </div>
                    <div class="portfolio-field col-4">
                        <label>Content</label>
                        <textarea
                            id="prcontent<?php echo $index; ?>"
                            name="project_content[<?php echo $index; ?>][content]"
                            class="project-rich-editor"><?php echo esc_textarea($item['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="portfolio-field col-3">
                        <label>Gallery</label>
                        <div class="gallery-wrapper">
                            <?php foreach ($item['gallery'] ?? [] as $url): ?>
                                <div class="gallery-item relative">
                                    <img src="<?php echo esc_url($url); ?>">
                                    <input type="hidden" name="project_content[<?php echo $index; ?>][gallery][]" value="<?php echo esc_attr($url); ?>">
                                    <button type="button" class="button gallery-remove-image">✕</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button add-gallery-images">+ Add Images</button>
                    </div>
                    <div class="portfolio-field col-1">
                        <label>Layout</label>
                        <select name="project_content[<?php echo $index; ?>][layout]">
                            <option value="stack" <?php selected($item['layout'] ?? '', 'stack'); ?>>Image below content (Default)</option>
                            <option value="side" <?php selected($item['layout'] ?? '', 'side'); ?>>Image on the side</option>
                        </select>
                    </div>
                    <?php
                    $ctas = $item['ctas'] ?? [];
                    if (empty($ctas) && (!empty($item['cta']) || !empty($item['link']))) {
                        $ctas = [['cta' => $item['cta'] ?? '', 'link' => $item['link'] ?? '']];
                    }
                    ?>
                    <div class="portfolio-field col-4 cta-section">
                        <div class="cta-pairs-wrapper">
                            <?php foreach ($ctas as $ci => $cta_item): ?>
                                <div class="cta-pair portfolio-row">
                                    <div class="portfolio-field col-2">
                                        <label>CTA</label>
                                        <input type="text" name="project_content[<?php echo $index; ?>][ctas][<?php echo $ci; ?>][cta]" value="<?php echo esc_attr($cta_item['cta'] ?? ''); ?>">
                                    </div>
                                    <div class="portfolio-field col-1">
                                        <label>Link</label>
                                        <input type="text" name="project_content[<?php echo $index; ?>][ctas][<?php echo $ci; ?>][link]" value="<?php echo esc_attr($cta_item['link'] ?? ''); ?>">
                                    </div>
                                    <div class="btn-container">
                                        <button type="button" class="button btn-remove-cta">Remove CTA</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($ctas) < 2): ?>
                            <button type="button" class="button btn-add-cta"><?php echo empty($ctas) ? '+ Add CTA' : '+ Add second CTA'; ?></button>
                        <?php endif; ?>
                    </div>

                    <div class="col-4 btn-container">
                        <button type="button" class="button btn-remove">Remove Item</button>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <button type="button" id="add-project-content-item" class="button button-primary add-repeater-btn">+ Add Item</button>

    <!-- Template hidden -->
    <template id="project-content-template">
        <div class="repeater-item">
            <div class="flex-row space-between">
                <h4>New Item</h4>
                <div class="portfolio-field full-width-field hidden  ">
                    <div class="fullwidth-toggle flex-row gap-8">
                        <input type="checkbox" name="project_content[<?php echo $index; ?>][full_width]" value="1" <?php checked($item['full_width'] ?? '', '1'); ?>>
                        <label> Full width </label>
                    </div>
                </div>
            </div>

            <div class="portfolio-row ">
                <div class="portfolio-field col-2">
                    <label>Title</label>
                    <input type="text" name="__name__[title]" value="">
                </div>
                <div class="portfolio-field col-2">
                    <label>Subtitle</label>
                    <input type="text" name="__name__[subtitle]" value="">
                </div>
                <div class="portfolio-field col-4">
                    <label>Content</label>
                    <textarea
                        id="prcontent__INDEX__"
                        name="__name__[content]"
                        class="project-rich-editor"></textarea>
                </div>
                <div class="portfolio-field col-3">
                    <label>Gallery</label>
                    <div class="gallery-wrapper"></div>
                    <button type="button" class="button add-gallery-images">+ Add Images</button>
                </div>
                <div class="portfolio-field col-1">
                    <label>Layout</label>
                    <select name="__name__[layout]">
                        <option value="stack">Image below content (Default)</option>
                        <option value="side">Image on the side</option>
                    </select>
                </div>
                <div class="portfolio-field col-4 cta-section">
                    <div class="cta-pairs-wrapper"></div>
                    <button type="button" class="button btn-add-cta">+ Add CTA</button>
                </div>

                <div class="col-4 btn-container">
                    <button type="button" class="button btn-remove">Remove Item</button>
                </div>
            </div>

        </div>
    </template>
    <template id="cta-pair-template">
        <div class="cta-pair portfolio-row">
            <div class="portfolio-field col-2">
                <label>CTA</label>
                <input type="text" name="__ctaname__[cta]" value="">
            </div>
            <div class="portfolio-field col-1">
                <label>Link</label>
                <input type="text" name="__ctaname__[link]" value="">
            </div>
            <div class="btn-container">
                <button type="button" class="button btn-remove-cta">Remove CTA</button>
            </div>
        </div>
    </template>
    <?php $existing_count = count($items); ?>
    <script>
        window.projectContentCounter = <?php echo $existing_count; ?>;
    </script>
<?php
}


function projects_save_content($post_id)
{
    if (
        !isset($_POST['project_content_nonce']) ||
        !wp_verify_nonce($_POST['project_content_nonce'], 'projects_save_content')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['project_content']) && is_array($_POST['project_content'])) {
        file_put_contents(WP_CONTENT_DIR . '/debug-content.txt', print_r($_POST['project_content'], true));

        $clean = [];

        foreach ($_POST['project_content'] as $index => $item) {
            $clean[] = [
                'title'     => sanitize_text_field($item['title'] ?? ''),
                'subtitle'  => sanitize_text_field($item['subtitle'] ?? ''),
                'content' => wp_unslash($item['content'] ?? ''),
                'gallery' => isset($item['gallery']) && is_array($item['gallery']) ? array_map('esc_url_raw', $item['gallery']) : [],
                'layout'    => sanitize_text_field($item['layout'] ?? 'stack'),
                'full_width' => $index === 0 ? (isset($item['full_width']) ? '1' : '') : '',
                'ctas' => (function () use ($item) {
                    $ctas = [];
                    foreach (array_values($item['ctas'] ?? []) as $c) {
                        $t = sanitize_text_field($c['cta'] ?? '');
                        $l = esc_url_raw($c['link'] ?? '');
                        if ($t || $l) $ctas[] = ['cta' => $t, 'link' => $l];
                        if (count($ctas) >= 2) break;
                    }
                    return $ctas;
                })(),
                'link'      => esc_url_raw($item['link'] ?? ''),
            ];
        }

        update_post_meta($post_id, '_project_content', $clean);
    } else {
        delete_post_meta($post_id, '_project_content');
    }
}
add_action('save_post_project', 'projects_save_content');



function projects_register_rest_fields()
{

    $fields = [
        'project_client'   => '_project_client',
        'project_duration' => '_project_duration',
        'project_role'     => '_project_role',
        'project_link'     => '_project_link',
        'project_content'  => '_project_content',
    ];

    foreach ($fields as $rest_key => $meta_key) {
        register_rest_field('project', $rest_key, [
            'get_callback' => function ($object) use ($meta_key) {
                return get_post_meta($object['id'], $meta_key, true);
            },
            'schema' => null,
        ]);
    }
}

add_action('rest_api_init', 'projects_register_rest_fields');


function experience_register_cpt()
{
    $args = array(
        'labels' => array(
            'name'               => 'Work Experiences',
            'singular_name'      => 'Position',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Work Experience',
            'edit_item'          => 'Edit Position',
            'new_item'           => 'New Work Experience',
            'view_item'          => 'View Position',
            'search_items'       => 'Search Positions',
            'not_found'          => 'No positions found',
            'not_found_in_trash' => 'No positions found in Trash',
        ),
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-businessperson',
        'supports'     => array('title', 'excerpt'),
    );
    register_post_type('experience', $args);
}
add_action('init', 'experience_register_cpt');

function experience_add_meta_box()
{
    add_meta_box(
        'experience_details',
        'Position Details',
        'experience_render_meta_box',
        'experience',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'experience_add_meta_box');


function experience_render_meta_box($post)
{
    wp_nonce_field('experience_save_meta', 'experience_meta_nonce');
    $position = get_post_meta($post->ID, '_position', true);
    $start_date = get_post_meta($post->ID, '_start_date', true);
    $end_date = get_post_meta($post->ID, '_end_date', true);
    $currently_working = get_post_meta($post->ID, '_currently_working', true);
    $company = get_post_meta($post->ID, '_company', true);
    $company_url = get_post_meta($post->ID, '_company_url', true);
    $location = get_post_meta($post->ID, '_location', true);
    $responsabilities = get_post_meta($post->ID, '_responsabilities', true);
?>
    <div class="repeater-item">
        <div class="portfolio-row">
            <div class="portfolio-field col-2 ">
                <label for="company">Position:</label>
                <input type="text" name="position" value="<?php echo esc_attr($position); ?>" placeholder="Position">
            </div>
            <div class="portfolio-field col-2 ">
                <label for="company">Company:</label>
                <input type="text" name="company" value="<?php echo esc_attr($company); ?>" placeholder="Company Name">
            </div>
            <div class="portfolio-field col-2 ">
                <label for="company">Company Link:</label>
                <input type="text" name="company_url" value="<?php echo esc_attr($company_url); ?>" placeholder="Company Website">
            </div>
            <div class="portfolio-field col-2 ">
                <label for="company">Company Location:</label>
                <input type="text" name="location" value="<?php echo esc_attr($location); ?>" placeholder="Location">
            </div>
            <div class="portfolio-field col-1 ">
                <label for="start_date">Start date:</label>
                <input type="text" name="start_date" value="<?php echo esc_attr($start_date); ?>" placeholder="January 2024">
            </div>
            <div class="portfolio-field col-1 ">
                <label for="end_date">End date:</label>
                <input type="text" name="end_date" value="<?php echo esc_attr($end_date); ?>" placeholder="January 2024">
            </div>
            <div class="portfolio-field col-1 ">
                <div class="checkbox">
                    <input type="checkbox" name="currently_working" value="1" <?php checked($currently_working, '1'); ?>>
                    <span> Currently working here</span>
                </div>
            </div>
            <div class="portfolio-field col-4">
                <label>Responsibilities</label>
                <?php
                wp_editor($responsabilities, 'responsabilities', array(
                    'textarea_name' => 'responsabilities',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny' => true,
                ));
                ?>
            </div>
        </div>
    </div>

<?php

}


function experience_save_meta($post_id)
{
    if (!isset($_POST['experience_meta_nonce']) || !wp_verify_nonce($_POST['experience_meta_nonce'], 'experience_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['position'])) {
        update_post_meta($post_id, '_position', sanitize_text_field($_POST['position']));
    }

    if (isset($_POST['company'])) {
        update_post_meta($post_id, '_company', sanitize_text_field($_POST['company']));
    }

    if (isset($_POST['company_url'])) {
        update_post_meta($post_id, '_company_url', esc_url_raw($_POST['company_url']));
    }

    if (isset($_POST['location'])) {
        update_post_meta($post_id, '_location', sanitize_text_field($_POST['location']));
    }

    if (isset($_POST['start_date'])) {
        update_post_meta($post_id, '_start_date', sanitize_text_field($_POST['start_date']));
    }

    if (isset($_POST['end_date'])) {
        update_post_meta($post_id, '_end_date', sanitize_text_field($_POST['end_date']));
    }

    $currently_working = isset($_POST['currently_working']) ? '1' : '0';
    update_post_meta($post_id, '_currently_working', $currently_working);

    if (isset($_POST['responsabilities'])) {
        update_post_meta($post_id, '_responsabilities', wp_kses_post($_POST['responsabilities']));
    }
}
add_action('save_post', 'experience_save_meta');


function experience_register_rest_fields()
{
    $fields = [
        'position'           => '_position',
        'company'            => '_company',
        'company_url'        => '_company_url',
        'location'           => '_location',
        'start_date'         => '_start_date',
        'end_date'           => '_end_date',
        'currently_working'  => '_currently_working',
        'responsabilities'   => '_responsabilities',
    ];

    foreach ($fields as $rest_key => $meta_key) {
        register_rest_field('experience', $rest_key, [
            'get_callback' => function ($object) use ($meta_key) {
                return get_post_meta($object['id'], $meta_key, true);
            },
            'schema' => null,
        ]);
    }
}

add_action('rest_api_init', 'experience_register_rest_fields');



// ABOUT PAGE OPTIONS

function about_add_admin_menu()
{
    add_menu_page(
        'About Page',
        'About',
        'manage_options',
        'about-settings',
        'about_render_page',
        'dashicons-id-alt',
        20
    );
}
add_action('admin_menu', 'about_add_admin_menu');

function about_render_page()
{
    // Guardar datos
    if (isset($_POST['about_submit'])) {
        check_admin_referer('about_save_settings', 'about_nonce');

        // Section Visor
        update_option('about_visor_title', sanitize_text_field($_POST['visor_title'] ?? ''));
        update_option('about_visor_image', esc_url_raw($_POST['visor_image'] ?? ''));

        // Section Links
        if (isset($_POST['about_links']) && is_array($_POST['about_links'])) {
            $clean_links = [];
            foreach ($_POST['about_links'] as $link) {
                $clean_links[] = [
                    'url'  => sanitize_text_field($link['url'] ?? ''),
                    'label' => sanitize_text_field($link['label'] ?? ''),
                    'icon' => sanitize_text_field($link['icon'] ?? ''),
                ];
            }
            update_option('about_links', $clean_links);
        } else {
            delete_option('about_links');
        }

        // Section About 
        if (isset($_POST['about_items']) && is_array($_POST['about_items'])) {
            $clean_about = [];
            foreach ($_POST['about_items'] as $item) {
                $clean_about[] = [
                    'title'   => sanitize_text_field($item['title'] ?? ''),
                    'content' => wp_kses_post($item['content'] ?? ''),
                ];
            }
            update_option('about_items', $clean_about);
        } else {
            delete_option('about_items');
        }

        // Section Languages 
        if (isset($_POST['about_languages']) && is_array($_POST['about_languages'])) {
            $clean_languages = [];
            foreach ($_POST['about_languages'] as $lang) {
                $clean_languages[] = [
                    'language' => sanitize_text_field($lang['language'] ?? ''),
                    'level'    => sanitize_text_field($lang['level'] ?? ''),
                ];
            }
            update_option('about_languages', $clean_languages);
        } else {
            delete_option('about_languages');
        }

        // Section Education 
        if (isset($_POST['about_education']) && is_array($_POST['about_education'])) {
            $clean_education = [];
            foreach ($_POST['about_education'] as $edu) {
                $clean_education[] = [
                    'year'    => sanitize_text_field($edu['year'] ?? ''),
                    'course'  => sanitize_text_field($edu['course'] ?? ''),
                    'school'  => sanitize_text_field($edu['school'] ?? ''),
                    'content' => wp_kses_post($edu['content'] ?? ''),
                ];
            }
            update_option('about_education', $clean_education);
        } else {
            delete_option('about_education');
        }
        update_option('about_highlight_label', sanitize_text_field(stripslashes($_POST['highlight_label'] ?? '')));
        update_option('about_highlight_excerpt',  sanitize_textarea_field($_POST['highlight_excerpt'] ?? ''));
        update_option('about_highlight_image',    esc_url_raw($_POST['highlight_image'] ?? ''));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }

    // Obtener datos guardados
    $visor_title = get_option('about_visor_title', '');
    $visor_image = get_option('about_visor_image', '');
    $links = get_option('about_links', []);
    if (!is_array($links) || empty($links)) {
        $links = [['url' => '', 'label' => '', 'icon' => '']];
    }

    $about_items = get_option('about_items', []);
    if (!is_array($about_items) || empty($about_items)) {
        $about_items = [['title' => '', 'content' => '']];
    }

    $languages = get_option('about_languages', []);
    if (!is_array($languages) || empty($languages)) {
        $languages = [['language' => '', 'level' => '']];
    }

    $education = get_option('about_education', []);
    if (!is_array($education) || empty($education)) {
        $education = [['year' => '', 'course' => '', 'school' => '', 'content' => '']];
    }

    $highlight_label   = get_option('about_highlight_label', '');
    $highlight_excerpt = get_option('about_highlight_excerpt', '');
    $highlight_image   = get_option('about_highlight_image', '');
?>
    <div class="wrap about-settings-page">
        <h1>About Page Settings</h1>
        <form method="post">
            <?php wp_nonce_field('about_save_settings', 'about_nonce'); ?>
            <?php submit_button('Save Settings', 'primary', 'about_submit'); ?>
            <div class="section">
                <h2>Section Home Highlight</h2>
                <div class="repeater-item">
                    <div class="portfolio-row">
                        <div class="portfolio-field col-2">
                            <label>Label:</label>
                            <input type="text" name="highlight_label"
                                value="<?php echo esc_attr($highlight_label); ?>"
                                placeholder="e.g., UX Design">
                        </div>
                        <div class="portfolio-field col-2">
                            <label>Excerpt:</label>
                            <textarea name="highlight_excerpt"><?php echo esc_textarea($highlight_excerpt); ?></textarea>
                        </div>
                        <div class="portfolio-field col-2 image-row">
                            <label>Photo:</label>
                            <div class="image-preview-wrapper">
                                <?php if (!empty($highlight_image)): ?>
                                    <img src="<?php echo esc_url($highlight_image); ?>" class="image-preview">
                                <?php else: ?>
                                    <img src="" class="image-preview hidden" style="display:none;">
                                <?php endif; ?>
                                <input type="hidden" class="project-image-field" name="highlight_image"
                                    value="<?php echo esc_attr($highlight_image); ?>">
                                <div class="btn-container">
                                    <button type="button" class="button select-project-image">Select Image</button>
                                    <button type="button" class="button btn-remove" style="<?php echo empty($highlight_image) ? 'display:none;' : ''; ?>">Remove Image</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section">
                <!-- SECTION VISOR -->
                <h2>Section Visor</h2>
                <div class="repeater-item">
                    <div class="portfolio-row">
                        <div class="portfolio-field col-2">
                            <label for="visor_title">Title:</label>
                            <input type="text" id="visor_title" name="visor_title"
                                value="<?php echo esc_attr($visor_title); ?>">
                        </div>
                        <div class="portfolio-field col-2">
                            <label>Image:</label>
                            <div class="image-preview-wrapper">
                                <?php if (!empty($visor_image)): ?>
                                    <img src="<?php echo esc_url($visor_image); ?>"
                                        class="image-preview">
                                <?php else: ?>
                                    <img src="" class="image-preview" style="display: none;">
                                <?php endif; ?>
                                <input type="hidden" id="visor_image" name="visor_image"
                                    value="<?php echo esc_attr($visor_image); ?>">
                                <div class="btn-container">
                                    <button type="button" class="button about-select-image">Select Image</button>
                                    <button type="button" class="button btn-remove"
                                        style="<?php echo empty($visor_image) ? 'display:none;' : ''; ?>">
                                        Remove Image
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SECTION LINKS -->
            <div class="section">
                <h2>Section Links</h2>
                <div id="about-links-wrapper">
                    <?php foreach ($links as $index => $link): ?>
                        <div class="repeater-item">
                            <h4>Link <?php echo $index + 1; ?></h4>
                            <div class="portfolio-row">
                                <div class="portfolio-field col-2">
                                    <label>URL or email:</label>
                                    <input type="text" name="about_links[<?php echo $index; ?>][url]"
                                        value="<?php echo esc_attr($link['url'] ?? ''); ?>"
                                        placeholder="https://... or @">
                                </div>
                                <div class="portfolio-field col-1">
                                    <label>Label:</label>
                                    <input type="text" name="about_links[<?php echo $index; ?>][label]"
                                        value="<?php echo esc_attr($link['label'] ?? ''); ?>"
                                        placeholder="e.g., LinkedIn">
                                </div>
                                <div class="portfolio-field col-1">
                                    <label>Icon Class:</label>
                                    <input type="text" name="about_links[<?php echo $index; ?>][icon]"
                                        value="<?php echo esc_attr($link['icon'] ?? ''); ?>"
                                        placeholder="e.g., material-icons-outlined">
                                </div>
                                <div class="col-2 btn-container">
                                    <button type="button" class="button btn-remove">Remove Link</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-about-link" class="button button-primary add-repeater-btn">+ Add Link</button>
            </div>
            <div class="section">
                <!-- SECTION ABOUT -->
                <h2>Section About</h2>
                <div id="about-items-wrapper">
                    <?php foreach ($about_items as $index => $item): ?>
                        <div class="repeater-item">
                            <h4>Item <?php echo $index + 1; ?></h4>
                            <div class="portfolio-row">
                                <div class="portfolio-field col-2">
                                    <label>Title:</label>
                                    <input type="text" name="about_items[<?php echo $index; ?>][title]"
                                        value="<?php echo esc_attr($item['title'] ?? ''); ?>">
                                </div>
                                <div class="portfolio-field col-3">
                                    <label>Content:</label>
                                    <textarea name="about_items[<?php echo $index; ?>][content]"><?php echo esc_textarea($item['content'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-1 btn-container">
                                    <button type="button" class="button btn-remove">Remove Item</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-about-item" class="button button-primary add-repeater-btn">+ Add About Item</button>
            </div>

            <div class="section">
                <!-- SECTION LANGUAGES -->
                <h2>Section Languages</h2>
                <div id="about-languages-wrapper">
                    <?php foreach ($languages as $index => $lang): ?>
                        <div class="repeater-item">
                            <h4>Language <?php echo $index + 1; ?></h4>
                            <div class="portfolio-row">
                                <div class="portfolio-field col-2">
                                    <label>Language:</label>
                                    <input type="text" name="about_languages[<?php echo $index; ?>][language]"
                                        value="<?php echo esc_attr($lang['language'] ?? ''); ?>"
                                        placeholder="e.g., English">
                                </div>
                                <div class="portfolio-field col-2">
                                    <label>Level:</label>
                                    <input type="text" name="about_languages[<?php echo $index; ?>][level]"
                                        value="<?php echo esc_attr($lang['level'] ?? ''); ?>"
                                        placeholder="e.g., Native, Fluent, Intermediate">
                                </div>
                                <div class="col-2 btn-container">
                                    <button type="button" class="button btn-remove">Remove Language</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-language" class="button button-primary add-repeater-btn">+ Add Language</button>
            </div>
            <div class="section">
                <!-- SECTION EDUCATION -->
                <h2>Section Education</h2>
                <div id="about-education-wrapper">
                    <?php foreach ($education as $index => $edu): ?>
                        <div class="repeater-item">
                            <h4>Education <?php echo $index + 1; ?></h4>
                            <div class="portfolio-row">
                                <div class="portfolio-field col-2">
                                    <label>Year:</label>
                                    <input type="text" name="about_education[<?php echo $index; ?>][year]"
                                        value="<?php echo esc_attr($edu['year'] ?? ''); ?>"
                                        placeholder="e.g., 2020">
                                </div>
                                <div class="portfolio-field col-2">
                                    <label>Course:</label>
                                    <input type="text" name="about_education[<?php echo $index; ?>][course]"
                                        value="<?php echo esc_attr($edu['course'] ?? ''); ?>"
                                        placeholder="e.g., Web Development">
                                </div>
                                <div class="portfolio-field col-2">
                                    <label>School:</label>
                                    <input type="text" name="about_education[<?php echo $index; ?>][school]"
                                        value="<?php echo esc_attr($edu['school'] ?? ''); ?>"
                                        placeholder="e.g., University Name">
                                </div>
                                <div class="portfolio-field col-4">
                                    <label>Content:</label>
                                    <textarea name="about_education[<?php echo $index; ?>][content]"><?php echo esc_textarea($edu['content'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-2 btn-container">
                                    <button type="button" class="button btn-remove">Remove Education</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-education" class="button button-primary add-repeater-btn">+ Add Education</button>
            </div>

            <?php submit_button('Save Settings', 'primary', 'about_submit'); ?>
        </form>
    </div>

    <!-- Template Links -->
    <template id="about-link-template">
        <div class="repeater-item">
            <h4>New Link</h4>
            <div class="portfolio-row">
                <div class="portfolio-field col-2">
                    <label>URL or email:</label>
                    <input type="text" name="__name__[url]" value="" placeholder="https://... or @">
                </div>
                <div class="portfolio-field col-1">
                    <label>Label:</label>
                    <input type="text" name="__name__[label]" value="" placeholder="e.g., LinkedIn">
                </div>
                <div class="portfolio-field col-1">
                    <label>Icon Class:</label>
                    <input type="text" name="__name__[icon]" value="" placeholder="e.g., material-icons-outlined">
                </div>
                <div class="col-2 btn-container">
                    <button type="button" class="button btn-remove">Remove Link</button>
                </div>
            </div>
        </div>
    </template>

    <!-- Template About Item -->
    <template id="about-item-template">
        <div class="repeater-item">
            <h4>New Item</h4>
            <div class="portfolio-row">
                <div class="portfolio-field col-2">
                    <label>Title:</label>
                    <input type="text" name="__name__[title]" value="">
                </div>
                <div class="portfolio-field col-3">
                    <label>Content:</label>
                    <textarea name="__name__[content]"></textarea>
                </div>
                <div class="col-1 btn-container">
                    <button type="button" class="button btn-remove">Remove Item</button>
                </div>
            </div>
        </div>
    </template>

    <!-- Template Language -->
    <template id="language-template">
        <div class="repeater-item">
            <h4>New Language</h4>
            <div class="portfolio-row">
                <div class="portfolio-field col-2">
                    <label>Language:</label>
                    <input type="text" name="__name__[language]" value="" placeholder="e.g., English">
                </div>
                <div class="portfolio-field col-2">
                    <label>Level:</label>
                    <input type="text" name="__name__[level]" value="" placeholder="e.g., Native, Fluent">
                </div>
                <div class="col-2 btn-container">
                    <button type="button" class="button btn-remove">Remove Language</button>
                </div>
            </div>
        </div>
    </template>

    <!-- Template Education -->
    <template id="education-template">
        <div class="repeater-item">
            <h4>New Education</h4>
            <div class="portfolio-row">
                <div class="portfolio-field col-2">
                    <label>Year:</label>
                    <input type="text" name="__name__[year]" value="" placeholder="e.g., 2020">
                </div>
                <div class="portfolio-field col-2">
                    <label>Course:</label>
                    <input type="text" name="__name__[course]" value="" placeholder="e.g., Web Development">
                </div>
                <div class="portfolio-field col-2">
                    <label>School:</label>
                    <input type="text" name="__name__[school]" value="" placeholder="e.g., University Name">
                </div>
                <div class="portfolio-field col-4">
                    <label>Content:</label>
                    <textarea name="__name__[content]"></textarea>
                </div>
                <div class="col-2 btn-container">
                    <button type="button" class="button btn-remove">Remove Education</button>
                </div>
            </div>
        </div>
    </template>
<?php
}

// Register REST API endpoint for About page data
function about_register_rest_route()
{
    register_rest_route('wp/v2', '/about', array(
        'methods'  => 'GET',
        'callback' => 'about_get_rest_data',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'about_register_rest_route');

function about_get_rest_data()
{
    return array(
        'visor' => array(
            'title' => get_option('about_visor_title', ''),
            'image' => get_option('about_visor_image', ''),
        ),
        'links' => get_option('about_links', []),
        'about_items' => get_option('about_items', []),
        'languages' => get_option('about_languages', []),
        'education' => get_option('about_education', []),
        'highlight'   => array(
            'label'   => get_option('about_highlight_label', ''),
            'excerpt' => get_option('about_highlight_excerpt', ''),
            'image'   => get_option('about_highlight_image', ''),
        ),
    );
}
