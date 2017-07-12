<?php
// Register Custom Post Type
function register_cpt_simple_recruitment() {

	$labels = array(
		'name'                  => _x( 'Recruitments', 'Post Type General Name', 'simple-rec' ),
		'singular_name'         => _x( 'Recruitment', 'Post Type Singular Name', 'simple-rec' ),
		'menu_name'             => __( 'Recruitments', 'simple-rec' ),
		'name_admin_bar'        => __( 'Recruitment', 'simple-rec' ),
		'archives'              => __( 'Recruitment Archives', 'simple-rec' ),
		'attributes'            => __( 'Recruitment Attributes', 'simple-rec' ),
		'parent_item_colon'     => __( 'Parent recruitment:', 'simple-rec' ),
		'all_items'             => __( 'All Recruitments', 'simple-rec' ),
		'add_new_item'          => __( 'Add New Recruitment', 'simple-rec' ),
		'add_new'               => __( 'Add New', 'simple-rec' ),
		'new_item'              => __( 'New Recruitment', 'simple-rec' ),
		'edit_item'             => __( 'Edit Recruitment', 'simple-rec' ),
		'update_item'           => __( 'Update Recruitment', 'simple-rec' ),
		'view_item'             => __( 'View Recruitment', 'simple-rec' ),
		'view_items'            => __( 'View Recruitments', 'simple-rec' ),
		'search_items'          => __( 'Search Recruitment', 'simple-rec' ),
		'not_found'             => __( 'Not found', 'simple-rec' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'simple-rec' ),
		'featured_image'        => __( 'Featured Image', 'simple-rec' ),
		'set_featured_image'    => __( 'Set featured image', 'simple-rec' ),
		'remove_featured_image' => __( 'Remove featured image', 'simple-rec' ),
		'use_featured_image'    => __( 'Use as featured image', 'simple-rec' ),
		'insert_into_item'      => __( 'Insert into recruitment', 'simple-rec' ),
		'uploaded_to_this_item' => __( 'Uploaded to this recruitment', 'simple-rec' ),
		'items_list'            => __( 'Recruitments list', 'simple-rec' ),
		'items_list_navigation' => __( 'Recruitments list navigation', 'simple-rec' ),
		'filter_items_list'     => __( 'Filter recruitments list', 'simple-rec' ),
	);

	$args = array(
		'label'                 => __( 'Recruitment', 'simple-rec' ),
		'description'           => __( 'Recruitment data', 'simple-rec' ),
		'labels'                => $labels,
		'supports'              => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields',),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,		
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);

	register_post_type( 'simple_rec', $args );
}

add_action( 'init', 'register_cpt_simple_recruitment', 0 );

function cmb2_sample_metaboxes() {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_simple_rec_';

	/**
	 * Initiate the metabox
	 */
	$cmb = new_cmb2_box( array(
		'id'            => 'simple_rec_metabox',
		'title'         => __( 'Recruitment Data', 'simple-rec' ),
		'object_types'  => array( 'simple_rec'), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, 
	) );

	// Regular text field
	$cmb->add_field( array(
		'name'       => __( 'Name', 'simple-rec' ),
		'id'         => $prefix . 'name',
		'type'       => 'text',
	) );

	// Email text field
	$cmb->add_field( array(
		'name' => __( 'Email', 'simple-rec' ),
		'id'   => $prefix . 'email',
		'type' => 'text_email',
	) );
}

add_action( 'cmb2_admin_init', 'cmb2_sample_metaboxes' );
