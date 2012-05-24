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
			$plugin_settings = get_option('reclinks_plugin_options');

			if ( !$plugin_settings['allow-unregistered-vote'] && !current_user_can('add_reclink') )
				return;
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			$entry_title = empty($instance['entry_title']) ? ' ' : apply_filters('widget_entry_title', $instance['entry_title']);
			if ( !empty( $title ) ) 
				echo $before_title . $title . $after_title;

			echo output_addlink_form();

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

	/*
	 * Widget for displaying current links
	 *
	 *
	 */
	class RecLinks_Display_Links extends WP_Widget {
		
		function RecLinks_Display_Links() {
		//Constructor
			$widget_ops = array(
				'classname' => 'widget_reclinks_current',
				'description' => 'Display list of current recommended links'
			);
			$this->WP_Widget('reclinks_current', 'Current Recommended Links', $widget_ops);
		}

		function widget($args, $instance) {
		// prints the widget
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			if ( !empty( $title ) ) 
				echo $before_title . $title . $after_title;


			global $wp_query, $comment;
			$old_query = $wp_query;
			$old_comment_query = $GLOBALS['comment'] = $comment;
			unset( $comment, $GLOBALS['comment'] );

			$wp_query = new WP_Query( array(
				'post_type' => 'reclink',
				'posts_per_page' => intval( $instance['number'] ),
				'reclinks_sort' => 'current'
			) );

			if ( '' === locate_template( 'loop-reclinks.php', true, false ) )
				include( 'loop-reclinks.php' );

			$wp_query = $old_query;
			$GLOBALS['comment'] = $old_comment_query;

			if ( $instance['links'] !== 'none' ):

				$l = get_post_type_archive_link( 'reclink' );

				switch ( $instance['links'] ) :
					case 'linkonly':
						echo '<p><a href="'.$l.'">'.__( 'View current links', 'gad_reclinks' ).'</a></p>';
						break;
					default:
						echo '<ul>';
						echo '<li><a href="' . add_query_arg( 'sort', 'newest', $l ) . '">' . __( 'Newest', 'gad_reclinks' ) . '</a></li>';
						echo '<li><a href="' . add_query_arg( 'sort', 'hot', $l ) . '">' . __( 'Hot', 'gad_reclinks' ) . '</a></li>';
						echo '<li><a href="' . add_query_arg( 'sort', 'current', $l ) . '">' . __( 'Current', 'gad_reclinks' ) . '</a></li>';
						echo '<li><a href="' . add_query_arg( 'sort', 'score', $l ) . '">' . __( 'Top ranked', 'gad_reclinks' ) . '</a></li>';
						echo '</ul>';
						break;
				endswitch;

			endif;

			echo $after_widget;
		}
		
		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['domain'] = ( isset( $new_instance['domain'] ) && $new_instance['domain'] );
			$instance['number'] = intval( $new_instance['number'] );
			if ( in_array( $new_instance['links'], array( 'none', 'linkonly', 'all' ) ) )
				$instance['links'] = $new_instance['links'];
			return $instance;
		}

		function form($instance) {
			//widgetform in backend
			$instance = wp_parse_args( 
				(array) $instance, 
				array( 
					'title' => __( 'User-submitted Links', 'gad_reclinks' ),
					'domain' => false,
			   		'number' => 5,
					'links' => 'all'
					) );
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('domain'); ?>"><?php _e( 'Show link domain beside title?', 'gad_reclinks' ); ?> </label>
					<input type="checkbox" id="<?php echo $this->get_field_id('domain'); ?>" name="<?php echo $this->get_field_name('domain'); ?>" <?php checked( true, $instance['domain'] ) ;?>/>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of Links to display:', 'gad_reclinks' ); ?> </label>
					<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo intval( $instance['number'] ); ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('links'); ?>"><?php _e( 'Show links below list?', 'gad_reclinks' ); ?> </label>
					<select id="<?php echo $this->get_field_id('links'); ?>" name="<?php echo $this->get_field_name('links'); ?>">
						<option value="none" <?php selected( $instance['links'], 'none' ); ?>><?php _e( 'None', 'gad_reclinks' ); ?></option>
						<option value="linkonly" <?php selected( $instance['links'], 'linkonly' ); ?>><?php _e( 'Link to current posts only', 'gad_reclinks' ); ?></option>
						<option value="all" <?php selected( $instance['links'], 'all' ); ?>><?php _e( 'Link to all sorting options', 'gad_reclinks' ); ?></option>
					</select>
				</p>
				<?php
				}
		}

	register_widget('RecLinks_Display_Links');


	/*
	 * Widget for displaying bookmarklet button.
	 *
	 * ALso includes textarea where site owners can include description
	 * and instructions, etc.
	 *
	 */
	class RecLinks_Bookmarklet extends WP_Widget {

		function RecLinks_Bookmarklet() {
		//Constructor
			$widget_ops = array(
				'classname' => 'widget_reclinks_bookmarklet',
				'description' => 'Display a bookmarklet that your users can drag to their address bar'
			);
			$this->WP_Widget('reclinks_bookmarklet', 'RecLinks Bookmarklet Form', $widget_ops);
		}

		function widget($args, $instance) {
		// prints the widget
			if ( !current_user_can('add_reclink') )
				return;
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			$entry_title = empty($instance['entry_title']) ? ' ' : apply_filters('widget_entry_title', $instance['entry_title']);
			if ( !empty( $title ) ) 
				echo $before_title . $title . $after_title;

			echo reclinks_bookmarklet();

			if ( !empty( $description ) )
				echo '<p>' . $description . '</p>';

			echo $after_widget;
		}

		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			$instance['title'] = sanitize_text_field($new_instance['title']);
			$instance['description'] = wp_filter_post_kses($new_instance['description']);
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
				<p>					
					<label for="<?php echo $this->get_field_id('description'); ?>">Description: </label>
					<textarea id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" rows="10" cols="30"><?php echo esc_textarea( $description ); ?></textarea>
					<span class="description">(Give your users an idea of how to use the bookmarklet, or instructions, etc.)</span>
				</p>
				<?php
				}
		}

	register_widget( 'RecLinks_Bookmarklet' );
}

