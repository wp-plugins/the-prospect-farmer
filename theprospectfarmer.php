<?php
/*
Plugin Name: The Prospect Farmer 
Plugin URI: http://www.theprospectfarmer.com/wordpress
Description: The Prospect Farmer is a plugin which allows to insert the The Prospect Farmer's forms in posts and pages using a shortcode.
Author: The Prospect Farmer
Version: 1.0
Author URI: http://www.theprospectfarmer.com/

This plugin is based on the work of Aakash Chakravarthy (http://www.aakashweb.com/) - the Shortcoder Plugin
*/

define('TPF_VERSION', '1.0');
define('TPF_AUTHOR', 'The Prospect Farmer');
define('TPF_URL', plugins_url('',__FILE__) );
define('TPF_ADMIN', admin_url( 'options-general.php?page=theprospectfarmer' ) );

// Load languages
load_plugin_textdomain('theprospectfarmer', false, basename(dirname(__FILE__)) . '/languages/');


// Add admin menu
function tpf_add_menu() {
	add_options_page( 'The Prospect Farmer', 'The Prospect Farmer', 'manage_options', 'theprospectfarmer', 'tpf_admin_page' );
}

add_action('admin_menu','tpf_add_menu');



// Load the Javascripts
function tpf_admin_js(){
	// Check whether the page is the The Prospect Farmer admin page.
	if (isset($_GET['page']) && $_GET['page'] == 'theprospectfarmer'){
		wp_enqueue_script(array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-draggable',
			'jquery-ui-droppable'
		));
		wp_enqueue_script('theprospectfarmer-admin-js', TPF_URL . '/tpf-admin-js.js?v=' . TPF_VERSION);
	}
}
add_action('admin_print_scripts', 'tpf_admin_js');



// Load the CSS
function tpf_admin_css(){
	if (isset($_GET['page']) && $_GET['page'] == 'theprospectfarmer') {
		wp_enqueue_style('theprospectfarmer-admin-css', TPF_URL . '/tpf-admin-css.css?v=' . TPF_VERSION);
	}
}
add_action('admin_print_styles', 'tpf_admin_css');



// Plugin on activate fixes
function tpf_onactivate(){
	$tpf_options = get_option('theprospectfarmer_data');
	$tpf_flags = get_option('theprospectfarmer_flags');
	
	// Move the flag version fix to tpf_flags option
	if(isset($tpf_options['_version_fix'])){
		unset($tpf_options['_version_fix']);
		update_option('theprospectfarmer_data', $tpf_options);
	}
	
	$tpf_flags['version'] = TPF_VERSION;
	update_option('theprospectfarmer_flags', $tpf_flags);

}
register_activation_hook(__FILE__, 'tpf_onactivate');




// Register the shortcode
add_shortcode('tpf', 'theprospectfarmer');

function theprospectfarmer_all_ok($name){

	$tpf_options = get_option('theprospectfarmer_data');
	
	if($tpf_options[$name]['disabled'] == 0){
		if(current_user_can('level_10') && $tpf_options[$name]['hide_admin'] == 1){
			return false;
		}else{
			return true;
		}
	}else{
		return false;
	}
}

// Main function
function theprospectfarmer($atts, $content) { 
	
	$tpf_options = get_option('theprospectfarmer_data');
	
	// Get the Shortcode name
	if(isset($atts[0])){
		$tpf_name = str_replace(array('"', "'", ":"), '', $atts[0]);
		unset($atts[0]);
	}else{
		// Old version with "name" param support
		if(array_key_exists("name", $atts)){
			$tVal = $atts['name'];
			if(array_key_exists($tVal, $tpf_options)){
				$tpf_name = $tVal;
				unset($atts['name']);
			}
		}
	}
	
	if(!isset($tpf_name)){
		return '';
	}
	
	// Check whether theprospectfarmer can execute
	if(theprospectfarmer_all_ok($tpf_name)){
	
		$tpf_content_final = '';
	
		// If SC has parameters, then replace it
		if(!empty($atts)){
			$keys = array();
			$values = array();
			$i = 0;
	
			// Seperate Key and value from atts
			foreach($atts as $k=>$v){
				if($k !== 0){
					$keys[$i] = "%%" . $k . "%%";
					$values[$i] = $v;
				}
				$i++;
			}
			
			// Replace the params
			$tpf_content = $tpf_options[$tpf_name]['content'];	
			$tpf_content_rep1 = str_replace($keys, $values, $tpf_content);
			$tpf_content_final = preg_replace('/%%[^%\s]+%%/', '', $tpf_content_rep1);
			
		}
		else{
		
			// If the SC has no params, then replace the %vars%
			$tpf_content = $tpf_options[$tpf_name]['content'];	
			$tpf_content_final = preg_replace('/%%[^%\s]+%%/', '', $tpf_content);
			
		}
		
		return "<!-- Start The Prospect Farmer form -->" . do_shortcode( $tpf_content_final ) . "<!-- End The Prospect Farmer form -->";
		
	}else{
		return '';
	}
}


// The Prospect Farmer admin page
function tpf_admin_page(){
	
	$tpf_updated = false;
	$tpf_options = get_option('theprospectfarmer_data');
	$tpf_flags = get_option('theprospectfarmer_flags');
	
	$title = __( "Create a The Prospect Farmer Form Shortcode", 'theprospectfarmer' );
	$button = __( "Create Shortcode", 'theprospectfarmer' );
	$edit = 0;
	$tpf_content = '';
	$tpf_disable = 0;
	$tpf_hide_admin = 0;
	
	// Insert shortcode
	if (isset($_POST["tpf_form_main"]) && $_POST["tpf_form_main"] == '1' && check_admin_referer('theprospectfarmer_create_form')){
		$tpf_options = get_option('theprospectfarmer_data');
		$tpf_name = stripslashes($_POST['tpf_name']);
		
		$tpf_post_disabled = isset( $_POST['tpf_disable'] ) ? intval( $_POST['tpf_disable'] ) : 0;
		$tpf_post_hideadmin = isset( $_POST['tpf_hide_admin'] ) ? intval( $_POST['tpf_hide_admin'] ) : 0;
		
		$tpf_options[$tpf_name] = array(
			'content' => stripslashes($_POST['tpf_content']),
			'disabled' => $tpf_post_disabled,
			'hide_admin' => $tpf_post_hideadmin
		);
		
		// Updating the DB
		update_option("theprospectfarmer_data", $tpf_options);
		$tpf_updated = true;
		
		// Insert Message
		if($tpf_updated == 'true'){
			echo '<div class="message updated fade"><p>' . __('Shortcode updated successfully !', 'theprospectfarmer') . '</p></div>';
		}else{
			echo '<div class="message error fade"><p>' . __('Unable to create shortcode !', 'theprospectfarmer') . '</p></div>';
		}
	}
	
	// Edit shortcode
	if (isset($_POST["tpf_form_edit"]) && $_POST["tpf_form_edit"] == '1' && check_admin_referer('theprospectfarmer_edit_form')){
		$tpf_options = get_option('theprospectfarmer_data');
		$tpf_name_edit = stripslashes($_POST['tpf_name_edit']);
		
		if($_POST["tpf_form_action"] == "edit"){
			$tpf_content = stripslashes($tpf_options[$tpf_name_edit]['content']);
			$tpf_disable = $tpf_options[$tpf_name_edit]['disabled'];
			$tpf_hide_admin = $tpf_options[$tpf_name_edit]['hide_admin'];
			
			$title = __( 'Edit this Shortcode - ', 'theprospectfarmer' ) . '<small>' . $tpf_name_edit . '</small>';
			$button = __( 'Update Shortcode', 'theprospectfarmer' );
			$edit = 1;
		}else{
			unset($tpf_options[$tpf_name_edit]);
			unset($tpf_name_edit);
			update_option("theprospectfarmer_data", $tpf_options);
			echo '<div class="message updated fade"><p>' . __('Shortcode deleted successfully !', 'theprospectfarmer') . '</p></div>';
		}
	}

	
?>

<!-- The Prospect Admin page --> 

<div class="wrap">
<?php tpf_admin_buttons('fbrec'); ?>
<h2><img width="150" height="73" src="<?php echo TPF_URL; ?>/images/theprospectfarmer.png" align="absmiddle"/><sup class="smallText"> v<?php echo TPF_VERSION; ?></sup></h2>

<div id="content">
	
	<h3><?php echo $title; ?> <?php if($edit == 1) echo '<span class="button tpf_back">&lt;&lt; ' . __( "Back", 'theprospectfarmer' ) . '</span>'; ?> </h3>
	
	<form method="post" id="tpf_form">
	
		<div class="tpf_section">
			<label for="tpf_name" class="tpf_fld_title"><?php _e( "Form name", 'theprospectfarmer' ); ?>:</label>
			<span class="tpf_name_wrap"><input type="text" name="tpf_name" id="tpf_name" value="<?php echo isset($tpf_name_edit) ? $tpf_name_edit : ''; ?>" placeholder="Enter a name to identify the form (don't use spaces or special characters)" class="widefat" required="required"/><div id="tpf_code"></div></span>
		</div>
		

		<div class="tpf_section">
			<label for="tpf_content" class="tpf_fld_title"><?php _e( "Paste The Prospect Farmer Code here", 'theprospectfarmer' ); ?>:</label>
			<textarea name="tpf_content" id="tpf_content" class="widefat" cols="50" rows="5"><?php echo $tpf_content; ?></textarea>
		</div>

		<div class="tpf_section"><p><b>Note:</b> You can get form code using the option "get the code to publish the form in your website" on the Form list at The Prospect Farmer tool. If you don't have a The Prospect Farmer account, you won't be able to use this plugin. Create your free account at <a href="http://app.theprospectfarmer.com/index?pag=SUBSCRIBE" target="_blank">http://app.theprospectfarmer.com/index?pag=SUBSCRIBE</a></p></div>
		
		<div class="tpf_section">
		
			<table width="100%"><tr>
				
				<td width="50%" class="tpf_settings"><label><input name="tpf_disable" type="checkbox" value="1" <?php echo $tpf_disable == "1" ? 'checked="checked"' : ""; ?>/> <?php _e( "Temporarily disable this form", 'theprospectfarmer' ); ?></label>
				<label><input name="tpf_hide_admin" type="checkbox" value="1" <?php echo $tpf_hide_admin == "1" ? 'checked="checked"' : ""; ?>/> <?php _e( "Disable this form to admins", 'theprospectfarmer' ); ?></label></td>
				
				<td><p align="right"><input type="submit" name="tpf_submit" id="tpf_submit" class="button-primary" value="<?php echo $button; ?>" /></p></td>
	
			</tr></table>
	
		</div>
		
		<?php wp_nonce_field('theprospectfarmer_create_form'); ?>
		<input name="tpf_form_main" type="hidden" value="1" />
	</form>
	
	<h3><?php _e( "Available Forms", 'theprospectfarmer' ); ?> <small>(<?php _e( "Click to edit", 'theprospectfarmer' ); ?>)</small></h3>
	<form method="post" id="tpf_edit_form">
		<ul id="tpf_list" class="clearfix">
		<?php
			$tpf_options = get_option('theprospectfarmer_data');
			if(is_array($tpf_options)){
				foreach($tpf_options as $key=>$value){
					echo '<li>' . $key . '</li>';
				}
			}
		?>
		</ul>
		
		<?php wp_nonce_field('theprospectfarmer_edit_form'); ?>
		<input name="tpf_form_edit" type="hidden" value="1" />
		<input name="tpf_form_action" id="tpf_form_action" type="hidden" value="edit" />
		<input name="tpf_name_edit" id="tpf_name_edit" type="hidden" />
	</form>
	
	<div id="tpf_delete" title="Drag & drop forms to delete"></div>
	
</div><!-- Content -->

<br/>
<p align="center">
	<a href="http://www.theprospectfarmer.com/contact/" target="_blank">Report bugs</a> | <a href="http://app.theprospectfarmer.com/index?pag=SUBSCRIBE" target="_blank">Create a The Prospect Farmer Account</a> | <a href="http://guide.theprospectfarmer.com/wordpress/" target="_blank">Help</a><br/><br/>
	<a href="http://www.aakashweb.com/" target="_blank" class="tpf_credits">This plugin is based on the Shortcoder from Aakash Web</a>
</p>

</div><!-- Wrap -->

<?php
}



// Helper for SC TinyMCE button
function tpf_admin_footer(){

	if (isset($_GET['page']) && $_GET['page'] == 'theprospectfarmer')
		return;
	
	echo "
<script>
window.onload = function(){
	if( typeof QTags === 'function' )
		QTags.addButton( 'QT_tpf_insert', 'The Prospect Farmer', tpf_show_insert );
}
function tpf_show_insert(){
	tb_show('Insert a The Prospect Farmer Form', '" . TPF_URL . "/tpf-insert.php?TB_iframe=true');
}
</script>
";
}
add_action('admin_footer', 'tpf_admin_footer');

function tpf_admin_buttons($type){
	switch($type){
		case 'fbrec':
		echo '<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Ftheprospectfarmer&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=80&amp;appId=138985052853750" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px;" allowTransparency="true"></iframe>';
		break;
	}
}


// Action Links
function tpf_plugin_actions($links, $file){
	static $this_plugin;
	global $tpf_donate_link;
	
	if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if( $file == $this_plugin ){
		$settings_link = "";
		$links = array_merge(array($settings_link), $links);
	}
	return $links;
}
add_filter('plugin_action_links', 'tpf_plugin_actions', 10, 2);




// Shortcoder tinyMCE buttons
function tpf_addbuttons() {
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
		return;
	
	if (isset($_GET['page']) && $_GET['page'] == 'theprospectfarmer')
		return;
	
	if ( get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "tpf_add_tinymce_plugin");
		add_filter('mce_buttons', 'tpf_register_button');
	}
}
 
function tpf_register_button($buttons) {
   array_push($buttons, "separator", "tpfbutton");
   return $buttons;
}

function tpf_add_tinymce_plugin($plugin_array) {
   $plugin_array['tpfbutton'] = TPF_URL . '/js/tinymce/editor_plugin.js';
   return $plugin_array;
}
add_action('init', 'tpf_addbuttons'); // init process for button control

?>