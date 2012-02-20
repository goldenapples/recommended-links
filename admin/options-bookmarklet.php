<?php
	
/**
 * HTML markup for the "bookmarklet" tab under plugin settings
 *
 * Called from admin-functions.php
 *
 */

?>
	<tr>	
		<td style="min-width:40%">
			<table>
				<tbody>
					<tr>
						<th scope="row">
							<label for="bookmarklet_text">Bookmarklet Text</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="bookmarklet_text" value="" />
							<p class="description">When users drag your bookmarklet to their toolbars, this is the text that will appear there. Make it specific to your site.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="bookmarklet_class">Bookmarklet button class</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="bookmarklet_class" value="" />
							<p class="description">If you want to style the button displayed for users to grab your bookmarklet, you can give it a class here.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="bookmarklet_header">Bookmarklet header</label>
							<p class="description">Do you have text or an image that you want to display in the popup window that loads when users click the bookmarklet to submit a link? You can enter it here (some HTML allowed).</p>
						</th>
						<td>
						<textarea id="bookmarklet_header" name="bookmarklet_header" rows="10" cols="30"><?php echo ''; ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for=""></label>
						</th>
						<td>
							<input type="" />
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td valign="top">
<p>A <b>bookmarklet</b> is a javascript-powered browser script which makes it easier for users to submit links to this site. This bookmarklet is based heavily off the built-in "Press This!" functionality in WordPress. However, it is tailored to the needs of users submitting links and differs from "Press This!" in a few major ways:</p>
<ul>
	<li>All posting is done through the front end. Users don't need to see the WP dashboard.</li>
	<li></li>
	<li></li>
</ul>
<p>You can display the bookmarklet on your site through the "RecLinks Bookmarklet Form" widget, by using the shortcode <code>[reclinks_bookmarklet]</code> in a post or page, or by including the function <code>&lt;?php reclinks_bookmarklet( true ); ?&gt;</code> in a template file.</p>
		</td>
	</tr>
