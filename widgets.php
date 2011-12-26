<?php	

add_action( 'widgets_init', 'gad_reclinks_widgets' );

function gad_reclinks_widgets() {

	/*
	 * Widget for adding links
	 *
	 *
	 */
	class RecLinks_Add_Form extends WP_Widget {
		function RecLinks_Add_Form() {
		//Constructor
			$widget_ops = array(
				'classname' => 'widget_reclinks_addlink',
				'description' => 'Form to display to allow users to submit links'
			);
			$this->WP_Widget('reclinks_addlink', 'RecLinks Add Link Form', $widget_ops);
		}
		function widget($args, $instance) {
		// prints the widget
//			if ( !current_user_can('add_reclink') )
//				return;
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			$entry_title = empty($instance['entry_title']) ? ' ' : apply_filters('widget_entry_title', $instance['entry_title']);
			if ( !empty( $title ) ) 
				echo $before_title . $title . $after_title;
		?>
			<form class="reclinks_addlink" action="" method="POST">
				<label for="reclink_URL"><?php _e('Link URL', 'gad_reclinks'); ?></label>
				<input type="text" name="reclink_URL" />
				<label for="reclink_title"><?php _e('Link Title', 'gad_reclinks'); ?></label>
				<input type="text" name="reclink_title" />
				<label for="reclink_description"><?php _e('Link Description', 'gad_reclinks'); ?></label>
				<textarea id="reclink_description" name="reclink_description" rows="10" cols="30"></textarea>
				<button type="submit" id="reclink_submit"><?php _e( 'Submit Link', 'gad_reclinks' ); ?></button>
			</form>
		<?php	
			echo $after_widget;
		}
		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}
		function form($instance) {
			//widgetform in backend
			$instance = wp_parse_args( 
				(array) $instance, 
				array( 'title' => '' ) );
				$title = strip_tags($instance['title']);
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</p>
				<?php
				}
		}

	register_widget('RecLinks_Add_Form');

}

add_action( 'wp_enqueue_scripts', 'gad_reclinks_enqueues' );

function gad_reclinks_enqueues() {
	wp_enqueue_script( 'reclinks-scripts', plugin_dir_url( __FILE__ ) . 'js/reclinks-scripts.js', array( 'jquery' ), false, true );
	wp_localize_script( 'reclinks-scripts', 'reclinks', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
//	wp_enqueue_style("reclinks-theme-".$reclinks_theme_options['theme'],WP_RECLINKS_THEME_DIR.'/style.css');
	/* elseif (file_exists(get_stylesheet_directory().'/plugins/gad-link-recommendations/gad-link-recommendations.css')){ 
		//Child Theme (or just theme)
		wp_enqueue_style( "gad-link-recommendations", get_stylesheet_directory_uri().'/plugins/gad-link-recommendations/gad-link-recommendations.css' );
	} elseif (file_exists(get_template_directory().'/plugins/gad-link-recommendations/gad-link-recommendations.css')) { 
		//Parent Theme (if parent exists)
		wp_enqueue_style( "gad-link-recommendations", get_template_directory_uri().'/plugins/gad-link-recommendations/gad-link-recommendations.css' );
	} else { 
		//Default file in plugin folder
		wp_enqueue_style( "gad-link-recommendations", WP_RECLINKS_PLUGIN_DIR.'/gad-link-recommendations.css' );
	}	*/
	
	//echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') .'/'. PLUGINDIR . '/gad-link-recommendations/gad-link-recommendations.css" />' . "\n";
}
