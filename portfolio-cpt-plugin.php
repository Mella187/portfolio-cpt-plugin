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


/** Register CPT */

function portfolio_register_post_types()
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
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    ));
}

add_action('init', 'portfolio_register_post_types');


/** Register Custom Taxonomies */

function portfolio_register_taxonomies()
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

    register_taxonomy('project_tag', 'project', array(
        'labels' => array(
            'name' => 'Project Tags',
            'singular_name' => 'Project Tag',
        ),
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
    ));
}
add_action('init', 'portfolio_register_taxonomies');
