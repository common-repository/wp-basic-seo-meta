<?php 
	/*
	Plugin Name: WP BASIC SEO META
	Plugin URI: http://gabrielcastillo.net/wp-basic-seo-meta/
	Description: This is a basic seo meta tag generator for wordpress.
	Version: 1.01
	Author: Gabriel Castillo
	Author URI: http://gabrielcastillo.net/
	*/
	
	/**
	 * Copyright (c) `date "+%Y"` Your Name. All rights reserved.
	 *
	 * Released under the GPL license
	 * http://www.opensource.org/licenses/gpl-license.php
	 *
	 * This is an add-on for WordPress
	 * http://wordpress.org/
	 *
	 * **********************************************************************
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 * **********************************************************************
	 */
	
	

	

	add_action('admin_init', 'seo_meta_box');
	function seo_meta_box()
	{

		foreach(array('post', 'page') as $type)
		{
			add_meta_box('SEO META BOX', 'SEO META BOX', 'seo_meta_setup', $type, 'normal', 'high');
		}
		add_action('save_post', 'save_seo_meta');
	}
	function seo_meta_setup()
	{
		global $post;
		$meta = get_post_meta($post->ID, '_seo_meta', TRUE);
		?>
		<table class="widefat">
			<tbody>
				<tr>
					<td><label for="seo-meta-author"><strong>Page Author</strong></label></td>
					<td><input id="seo-meta-title" class="widefat" type="text" name="_seo_meta[seo_meta_author]" value="<?php if(isset($meta['seo_meta_author'])){echo $meta['seo_meta_author'];} ?>" /></td>
				</tr>
				<tr>
					<td><label for="seo-meta-keyword"><strong>Page Keywords</strong></label></td>
					<td><input id="seo-meta-keyword" class="widefat" type="text" name="_seo_meta[seo_meta_keyword]" value="<?php if(isset($meta['seo_meta_keyword'])){echo $meta['seo_meta_keyword'];} ?>" /></td>
				</tr>
				<tr>
					<td><label for="seo-meta-description"><strong>Page Description</strong></label></td>
					<td><textarea id="seo-meta-description" class="widefat" name="_seo_meta[seo_meta_description]"><?php if(isset($meta['seo_meta_description'])){echo $meta['seo_meta_description'];} ?></textarea></td>
				</tr>
			</tbody>
		</table>
		<?php
		echo '<input type="hidden" name="theme_seo_meta_nonce" value="'.wp_create_nonce(__FILE__).'" />';
	}

	function save_seo_meta($post_id)
	{
		if( !wp_verify_nonce($_POST['theme_seo_meta_nonce'], __FILE__) ) return $post_id;

		if( $_POST['post_type'] == 'page')
		{
			if( !current_user_can('edit_page', $post_id) ) return $post_id;
		}
		else
		{
			if( !current_user_can('edit_post', $post_id) ) return $post_id;
		}

		$current_data = get_post_meta($post_id, '_seo_meta', TRUE);
		$new_data	  = $_POST['_seo_meta'];

		clean_seo_meta($new_data);

		if( $current_data )
		{
			if( is_null($new_data) )
			{
				delete_post_meta($post_id, '_seo_meta');
			}
			else
			{
				update_post_meta($post_id, '_seo_meta', $new_data);
			}
		}
		elseif( !is_null($new_data) )
		{
			add_post_meta($post_id, '_seo_meta', $new_data, TRUE);
		}

		return $post_id;

	}

	if(!function_exists(clean_seo_meta)){
		function clean_seo_meta()
		{
			if(is_array($arr))
			{
				foreach($arr as $i => $v)
				{
					if(is_array($arr[$i]))
					{
						clean_seo_meta($arr[$i]);
						if(!count($arr[$i]))
						{
							unset($arr[$i]);
						}
					}else
					{
						if(trim($arr[$i] = ''))
						{
							unset($arr[$i]);
						}
					}
				}
				if(!count($arr))
				{
					$arr = NULL;
				}
			}
		}
	}
	


	function insert_meta_wp_header()
	{	
		global $post;
		
		$meta = get_post_meta($post->ID, '_seo_meta', TRUE);

		if(isset($meta['seo_meta_author'])){
			echo '<meta name="author" content="'.$meta['seo_meta_author'].'" />'."\n";
		}

		if(isset($meta['seo_meta_keyword'])){
			echo  '<meta name="keywords" content="'.$meta['seo_meta_keyword'].'" />'."\n";
		}

		if(isset($meta['seo_meta_description'])){
			echo '<meta name="description" content="'.substr(trim($meta['seo_meta_description']), 0,160).'" />'."\n";
		}

	}

	add_action('wp_head', 'insert_meta_wp_header', 1);