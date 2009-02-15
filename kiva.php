<?
/*
Plugin Name: Kiva
Plugin URI: http://www.davidjmiller.org/2009/kiva/
Description: Returns links to Kiva loans. Based on code first written by Connor Boyack (http://www.connorboyack.com/)
Version: 1.0
Author: David Miller
Author URI: http://www.davidjmiller.org/
*/

/*
	Template Tag: Returns a list of kiva loans needing donations.
		e.g.: <?php show_kiva(); ?> 
	Full help and instructions at http://www.davidjmiller.org/2009/kiva/
*/

load_plugin_textdomain('kiva', 'wp-content/plugins/kiva'); 

function show_kiva() {
	$options = get_option(basename(__FILE__, ".php"));
	$limit = stripslashes($options['limit']);
	$format = stripslashes($options['format']);
	switch ($format)
	{
	case 'image':
		$width = 85;
		break;  
	case 'text':
		$width = 215;
		break;
	case 'full':
	default:
		$width = 300;
		break;
	}
	echo '<img src="http://images.kiva.org/images/logoLeafy3.gif" alt="visit Kiva" /><br />';
	echo '<strong><a href="http://www.kiva.org/">'.__('Fund a loan today!', 'kiva').'</a></strong>';
	echo '<table cellspacing="0" cellpadding="2" style="width:$width px">';
	for ($i=0; $i < $limit; $i++) {
		$contents = file_get_contents("http://api.kivaws.org/v1/loans/newest.json?status=fundraising");
		$contents = utf8_encode($contents);
		$results = json_decode($contents, true);
		$rand = array_rand($results["loans"]);
		$loan = $results["loans"][$rand];
		$name = (strlen($loan["name"]) > 21) ? substr($loan["name"],0,18)."..." : $loan["name"];
		
		if(!isset($results) || !isset($loan) || ($loan["name"] == "") ){ // no loan
		} else { //there is a loan, so show
			echo '<tr>';
			if ($format != 'text') {
				echo '<td rowspan="4" align="left" width="85">';
				if ($format == 'image') { 
					echo '<a href="http://kiva.org/app.php?page=businesses&action=about&id='.$loan["id"].'">';
				}
				echo '<img src="http://kiva.org/img/w80h80/'.$loan["image"]["id"].'.jpg" alt="Image for '.$name.'\'s Kiva Loan"/>';
				if ($format == 'image') { 
					echo '</a></td></tr><tr></tr><tr></tr><tr>';
				} else {
					echo '</td>';
				}
			}
			if ($format != 'image') {
				echo '<td width="60"><strong>'.__('Name', 'kiva').'</strong></td>';
				echo '<td><a href="http://kiva.org/app.php?page=businesses&action=about&id='.$loan["id"].'">'.$name.'</a></td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="60"><strong>'.__('Business', 'kiva').'</strong></td>';
				echo '<td>'.$loan["activity"].'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="60"><strong>'.__('Country', 'kiva').'</strong></td>';
				echo '<td>'.$loan["location"]["country"].'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="60"><strong>'.__('Raised', 'kiva').'</strong></td>';
				echo '<td><span style="color:green;font-weight:bold;">$'.$loan["funded_amount"].'/'.$loan["loan_amount"].'</span></td>';
			}
			echo '</tr><tr><td>&nbsp;</td></tr>';
		} //there is a kiva loan 
	} //for loop
	echo '</table>';
} //function  show_kiva()

/*
	Define the options menu
*/

function kiva_option_menu() {
	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) return;
	} else {
		global $user_level;
		get_currentuserinfo();
		if ($user_level < 8) return;
	}
	if (function_exists('add_options_page')) {
		add_options_page(__('Kiva Options', 'kiva'), __('Kiva', 'kiva'), 1, __FILE__, 'kiva_options_page');
	}
}

// Install the options page
add_action('admin_menu', 'kiva_option_menu');

// Prepare the default set of options
$default_options['limit'] = 1;
$default_options['format'] = 'full';
// the plugin options are stored in the options table under the name of the plugin file sans extension
add_option(basename(__FILE__, ".php"), $default_options, 'options for the Kiva plugin');

// This method displays, stores and updates all the options
function kiva_options_page(){
	global $wpdb;
	// This bit stores any updated values when the Update button has been pressed
	if (isset($_POST['update_options'])) {
		// Fill up the options array as necessary



		$options['limit'] = $_POST['limit'];
		$options['format'] = $_POST['format'];

		// store the option values under the plugin filename
		update_option(basename(__FILE__, ".php"), $options);
		
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Options saved', 'kiva') . '</p></div>';
	} else {
		// If we are just displaying the page we first load up the options array
		$options = get_option(basename(__FILE__, ".php"));
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap">
		<h2><?php echo ucwords(str_replace('-', ' ', basename(__FILE__, ".php"). __(' Options', 'kiva'))); ?></h2>
		<h3><a href="http://www.davidjmiller.org/2009/kiva/"><?php _e('Help and Instructions', 'kiva') ?></a></h3>
		<form method="post" action="">
		<fieldset class="options">
		<table class="optiontable">
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Number of loans to list', 'kiva') ?>:</th>
				<td><input name="limit" type="text" id="limit" value="<?php echo $options['limit']; ?>" size="2" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Display format for loan list', 'kiva') ?>:</th>
				<td>
					<input type="radio" name="format" id="format" value="image"<?php if ($options['format'] == 'image') echo ' checked'; ?>><?php _e('Image only', 'kiva') ?></input>&nbsp;
					<input type="radio" name="format" id="format" value="full"<?php if ($options['format'] == 'full') echo ' checked'; ?>><?php _e('Both', 'kiva') ?></input>&nbsp;
					<input type="radio" name="format" id="format" value="text"<?php if ($options['format'] == 'text') echo ' checked'; ?>><?php _e('Text only', 'kiva') ?></input>&nbsp;
				</td>
			</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="update_options" value="<?php _e('Update', 'kiva') ?>"  style="font-weight:bold;" /></div>
		</form>    		
	</div>
	<?php	
}

$options = get_option(basename(__FILE__, ".php"));
?>