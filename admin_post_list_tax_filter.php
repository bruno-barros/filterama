<?php
defined( 'ABSPATH' ) OR exit;
/**
 * Plugin Name: (WCM) Admin Post List Taxonomy Filter
 * Plugin URI:  http://example.com
 * Description: Adds a taxonomy filter in the admin list page for a custom post type.
 * Version:     0.1
 * Author:      Franz Josef Kaiser <wecodemore@gmail.com>
 * Author URI:  http://example.com
 * License:     MIT
 *
 * Originally written by: Mike Schinkel - http://mikeschinkel.com/custom-workpress-plugins
 * @link http://wordpress.stackexchange.com/posts/582/
 */

add_action( 'plugins_loaded', array( 'WCM_Admin_PT_List_Tax_Filter', 'init' ) );
class WCM_Admin_PT_List_Tax_Filter
{
	private static $instance;

	public $post_type;

	public $taxonomies;

	public $new_cols = array();

	static function init()
	{
		null === self :: $instance AND self :: $instance = new self;
		return self :: $instance;
	}

	public function __construct()
	{
		add_action( 'load-edit.php', array( $this, 'setup' ) );
	}

	public function setup()
	{
		add_action( current_filter(), array( $this, 'setup_vars' ), 20 );
		add_action( 'restrict_manage_posts', array( $this, 'get_select' ) );
		add_filter( "manage_taxonomies_for_{$this->post_type}_columns", array( $this, 'add_columns' ) );
	}

	public function setup_vars()
	{
		$this->post_type  = get_current_screen()->post_type;
		$this->taxonomies = array_diff(
			 get_object_taxonomies( $this->post_type )
			,get_taxonomies( array( 'show_admin_column' => 'false' ) )
		);
	}

	public function add_columns( $taxonomies )
	{
		return array_merge(
			 $taxonomies
			,$this->taxonomies
		);
	}

	/**
	 * Select form element to filter the post list
	 * @return string HTML
	 */
	public function get_select()
	{
		$html = '';
		foreach ( $this->taxonomies as $tax )
		{
			$options = sprintf(
				 '<option value="">%s %s</option>'
				,__( 'View All' )
				,get_taxonomy( $tax )->label
			);
			$class = is_taxonomy_hierarchical( $tax ) ? ' class="level-0"' : '';
			foreach ( get_terms( $tax ) as $taxon )
			{
				$options .= sprintf(
					 '<option %s%s value="%s">%s%s</option>'
					,isset( $_GET[ $tax ] ) ? selected( $taxon->slug, $_GET[ $tax ], false ) : ''
					,'0' !== $taxon->parent ? ' class="level-1"' : $class
					,$taxon->slug
					,'0' !== $taxon->parent ? str_repeat( '&nbsp;', 3 ) : ''
					,"{$taxon->name} ({$taxon->count})"
				);
			}
			$html .= sprintf(
				'<select name="%s" id="%s" class="postform">%s</select>'
				,$tax
				,$tax
				,$options
			);
		}

		return print $html;
	}
}