<?php
/*
	Plugin Name: 	CANDDi tracking
	Plugin URI: 	https://github.com/Canddi/CANDDi_Plugin_Wordpress
	Description: 	CANDDi tracking installation for wordpress users
	Version: 		1.0
	Author: 		Tim Langley
	Author URI: 	www.canddi.com
	License:
	Copyright 		(c) Campaign and Digital Intelligence 2014
*/

global $wpdb, $wp_version;

// PATH CONSTANTS
if ( ! defined( 'CT_PLUGIN_BASENAME' ) ) {
	define( 'CT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'CT_PLUGIN_NAME' ) ) {
	define( 'CT_PLUGIN_NAME', trim( dirname( CT_PLUGIN_BASENAME ), '/' ) );
}

if ( ! defined( 'CT_PLUGIN_DIR' ) ) {
	define( 'CT_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . CT_PLUGIN_NAME );
}

if ( ! defined( 'CT_PLUGIN_URL' ) ) {
	define( 'CT_PLUGIN_URL', WP_PLUGIN_URL . '/' . CT_PLUGIN_NAME );
}

define ('CT_FILE', __FILE__);

add_action('admin_menu', 'ctext_admin_menus');

function ctext_admin_menus()	{
	add_menu_page('Canddi Setup', 'Canddi Setup', 8, __FILE__, 'ctext_admin_settings');
	//add_submenu_page(__FILE__, 'Settings', 'Settings', 8, 'sub-page', 'wppostreviewbyadmin_settings');
}

function ctext_admin_settings()	{
	if (isset($_POST['canddisave'])&&($_POST['canddisave']=='1')) {
		update_option('canddicode', $_POST['canddicode']);
	}
?>
	<h2>CANDDi tracking Setup</h2>
	<pre style="font-family:Arial, Helvetica, sans-serif;">
		<form action="" method="post">
			CANDDi tracking ID:
			<input type="text" name="canddicode" value="<?php echo get_option('canddicode'); ?>" />
			Tracking code will be placed on the footer of each page on the website.
			You can also use [canddicode] shortcode to add it manually.

			<input type="submit" name="submit" value="Save" />
			<input type="hidden" name="canddisave" value="1" />
		</form>

		<div style="position: relative; top: -187px; left: 520px; margin-bottom: -130px; width: 300px;">

			<a href="https://auth.canddi.com/" target="blank" style="text-decoration: none;">
			<img height="70px" src="<?php echo CT_PLUGIN_URL ?>/canddi-logo.png" />
			Login to your Canddi account click here</a>
		</div>

		The plugin will fix functionality to allow the @ character in the URL
		(when a tracking email is sent then url's like http://wordpress-site.com?ce=tim@example.com
		are not re-written to http://wordpress-site.com?ce=timexample.com)

		<?php
			//wp-includes/pluggable.php line 914
			//function wp_sanitize_redirect($location)
			//$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $location);
			//$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!@]|i', '', $location);

			$file = file_get_contents('../wp-includes/pluggable.php');
			$fstro = '$location = preg_replace(\'|[^a-z0-9-~+_.?#=&;,/:%!]|i\', \'\', $location);';
			$fstrn = '$location = preg_replace(\'|[^a-z0-9-~+_.?#=&;,/:%!@]|i\', \'\', $location);';
			if (isset($_POST['canddifixsave']) && ($_POST['canddifixsave']=='1') && strpos($file, $fstro)!==false)
			{
				$filen = str_replace($fstro, $fstrn, $file);
				file_put_contents('../wp-includes/pluggable.php', $filen);
				$file = file_get_contents('../wp-includes/pluggable.php');
			}
			if (isset($_POST['canddifixundo']) && ($_POST['canddifixundo']=='1') && strpos($file, $fstrn)!==false)
			{
				$filen = str_replace($fstrn, $fstro, $file);
				file_put_contents('../wp-includes/pluggable.php', $filen);
				$file = file_get_contents('../wp-includes/pluggable.php');
			}

			if (strpos($file, $fstrn)===false)
			{
				$status = 'Fix Inactive';
				?>
				<form action="" method="post">
					<input type="submit" name="submit" value="Apply fix" />
					<input type="hidden" name="canddifixsave" value="1" />
				</form>
				<?php
			} else {
				$status = 'Fix Applied';
				?>
				<form action="" method="post">
					<input type="submit" name="submit" value="Remove fix" />
					<input type="hidden" name="canddifixundo" value="1" />
				</form>
				<?php
			}

			echo 'Status: '.$status.'';
		?>
	</pre>
<?php
}

function show_function() {
	$code = trim(get_option('canddicode'));
	if ($code != '') {
		?>
		<!-- CANDDi http://www.canddi.com/privacy -->
		<script type="text/javascript">
		(function(){var a=document.createElement("script"),b=document.getElementsByTagName("script")[0];a.type="text/javascript";a.async=true;a.src=("https:"===document.location.protocol?"https://cdns":"http://cdn")+".canddi.com/p/<?php echo $code; ?>.js";b.parentNode.insertBefore(a,b);}());
		</script>
		<noscript style='position: absolute; left: -10px;'><img src='https://i.canddi.com/i.gif?A=<?php echo $code; ?>'/></noscript>
		<!-- /END CANDDi -->
		<?php
	}
}

add_action('wp_footer', 'show_function');
add_shortcode('canddicode', 'show_function');

?>
