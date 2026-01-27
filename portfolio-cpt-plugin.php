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





/** Register CPT */

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


/** Register Custom Taxonomies */

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


/** Register Common Techs */
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
        'Sketch',
        'React',
        'Vue.js',
        'Angular',
        'Next.js',
        'WordPress',
        'PHP',
        'JavaScript',
        'TypeScript',
        'Tailwind CSS',
        'Bootstrap',
        'SASS',
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


/** Register Custom Metaboxes */

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
            <div class="project-content-item">

                <h4>Item <?php echo $index + 1; ?></h4>
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
                        <textarea name="project_content[<?php echo $index; ?>][content]"> <?php echo esc_attr($item['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="portfolio-field col-2">
                        <label>CTA</label>
                        <input type="text" name="project_content[<?php echo $index; ?>][cta]" value="<?php echo esc_attr($item['cta'] ?? ''); ?>">
                    </div>
                    <div class="portfolio-field col-2">
                        <label>Link</label>
                        <input type="text" name="project_content[<?php echo $index; ?>][link]" value="<?php echo esc_attr($item['link'] ?? ''); ?>">
                    </div>
                    <div class="portfolio-field col-4 image-row">
                        <label>Image</label>
                        <div class="image-preview-wrapper">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo esc_url($item['image']); ?>" class="image-preview" style="display: block; margin-bottom: 10px;">
                            <?php else: ?>
                                <img src="" class="image-preview" style="display: none; margin-bottom: 10px;">
                            <?php endif; ?>
                            <input type="hidden" class="project-image-field" name="project_content[<?php echo $index; ?>][image]" value="<?php echo esc_attr($item['image'] ?? ''); ?>">
                            <div class="btn-container">
                                <button type="button" class="button select-project-image">Select Image</button>
                                <button type="button" class="button remove-project-image" style="<?php echo empty($item['image']) ? 'display:none;' : ''; ?>">Remove Image</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 btn-container">
                        <button type="button" class="button remove-project-content-item">Remove Item</button>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <button type="button" id="add-project-content-item" class="button button-primary">+ Add Item</button>

    <!-- Template hidden -->
    <template id="project-content-template">
        <div class="project-content-item">
            <h4>New Item</h4>
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
                    <textarea name="__name__[content]"></textarea>
                </div>
                <div class="portfolio-field col-2">
                    <label>CTA</label>
                    <input type="text" name="__name__[cta]" value="">
                </div>
                <div class="portfolio-field col-2">
                    <label>Link</label>
                    <input type="text" name="__name__[link]" value="">
                </div>
                <div class="portfolio-field col-4 image-row">
                    <label>Image</label>
                    <div class="image-preview-wrapper">
                        <img src="" class="image-preview" style="display: none; margin-bottom: 10px;">
                        <input type="hidden" class="project-image-field" name="__name__[image]" value="">
                        <div class="btn-container">
                            <button type="button" class="button select-project-image">Select Image</button>
                            <button type="button" class="button remove-project-image" style="display:none;">Remove Image</button>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <button type="button" class="button remove-project-content-item">Remove Item</button>
                </div>
            </div>

        </div>
    </template>

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

        $clean = [];

        foreach ($_POST['project_content'] as $item) {
            $clean[] = [
                'title'     => sanitize_text_field($item['title'] ?? ''),
                'subtitle'  => sanitize_text_field($item['subtitle'] ?? ''),
                'content' => wp_kses_post($item['content'] ?? ''),
                'cta'       => sanitize_text_field($item['cta'] ?? ''),
                'link'      => esc_url_raw($item['link'] ?? ''),
                'image'     => esc_url_raw($item['image'] ?? ''),
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
                return get_post_meta($object['id'], $meta_key, false);
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
    <div class="experience-content-item">
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
        update_post_meta($post_id, '_responsabilities', sanitize_textarea_field($_POST['responsabilities']));
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