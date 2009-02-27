<?
/*
Plugin Name: Kiva
Plugin URI: http://www.davidjmiller.org/2009/kiva/
Description: Returns links to Kiva loans. Based on code first written by Connor Boyack (http://www.connorboyack.com/)
Version: 1.2
Author: David Miller
Author URI: http://www.davidjmiller.org/
*/

/*
	Template Tag: Returns a list of kiva loans needing donations.
		e.g.: <?php show_kiva(); ?> 
	Shortcode: For those using widegets, place this shortcode in a text widget
		e.g.: [SHOW-KIVA]
	Full help and instructions at http://www.davidjmiller.org/2009/kiva/
*/

load_plugin_textdomain('kiva', 'wp-content/plugins/kiva'); 

function show_kiva() {
	$options = get_option(basename(__FILE__, ".php"));
	$limit = stripslashes($options['limit']);
	$format = stripslashes($options['format']);
	$command = 'http://api.kivaws.org/v1/loans/search.json?status=fundraising';
	$gender = stripslashes($options['gender']);
	$region = stripslashes($options['region']);
	$sector = stripslashes($options['sector']); // I can't figure out the proper syntax to retrieve the personal use sector.
	switch ($format)
	{
	case 'image':
		break;  
	case 'text':
		$widtht = '30%';
		break;
	case 'full':
	default:
		$widtht = '20%';
		break;
	}
	switch ($gender)
	{
	case 'male':
		$command .='&gender=male';
		$gender = ' for men';
		break;
	case 'female':
		$command .='&gender=female';
		$gender = ' for women';
		break;
	default:
		$gender = '';
		break;
	}
	switch ($region)
	{
	case 'af':
		$command .='&region='.$region;
		$region = ' in Africa';
		break;
	case 'as':
		$command .='&region='.$region;
		$region = ' in Asia';
		break;
	case 'me':
		$command .='&region='.$region;
		$region = ' in the Middle East';
		break;
	case 'ee':
		$command .='&region='.$region;
		$region = ' in Eastern Europe';
		break;
	case 'sa':
		$command .='&region='.$region;
		$region = ' in South America';
		break;
	case 'ca':
		$command .='&region='.$region;
		$region = ' in Central America';
		break;
	case 'na':
		$command .='&region='.$region;
		$region = ' in North America';
		break;
	default:
		$region = '';
		break;
	}
	if ($sector != 'any') {
		$command .='&sector='.$sector;
	} else {
		$sector = '';
	}
	echo '<img src="http://images.kiva.org/images/logoLeafy3.gif" alt="visit Kiva" /><br />';
	echo '<strong><a href="http://www.kiva.org/">'.__('Fund a loan today!', 'kiva').'</a></strong>';
	echo '<table cellspacing="0" cellpadding="2">';
	for ($i=0; $i < $limit; $i++) {
		$contents = file_get_contents($command);
		$contents = utf8_encode($contents);
		$results = json_decode($contents, true);
		$rand = array_rand($results["loans"]);
		$loan = $results["loans"][$rand];
		$name = (strlen($loan["name"]) > 21) ? substr($loan["name"],0,18)."..." : $loan["name"];
		
		if(!isset($results) || !isset($loan) || ($loan["name"] == "") ){ // no loan
			echo '<tr><td>No '.$sector.' loans could be found'.$gender.$region.'.</td></tr>';
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
				echo '<td width="'.$widtht.'"><strong>'.__('Business', 'kiva').'</strong></td>';
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
$default_options['gender'] = 'any';
$default_options['region'] = 'any';
$default_options['sector'] = 'any';
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
		$options['gender'] = $_POST['gender'];
		$options['region'] = $_POST['region'];
		$options['sector'] = $_POST['sector'];

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
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Gender', 'kiva') ?>:</th>
				<td>
					<input type="radio" name="gender" id="gender" value="male"<?php if ($options['gender'] == 'male') echo ' checked'; ?>><?php _e('Men only', 'kiva') ?></input>&nbsp;
					<input type="radio" name="gender" id="gender" value="female"<?php if ($options['gender'] == 'female') echo ' checked'; ?>><?php _e('Women only', 'kiva') ?></input>&nbsp;
					<input type="radio" name="gender" id="gender" value="any"<?php if ($options['gender'] == 'any') echo ' checked'; ?>><?php _e('Either', 'kiva') ?></input>&nbsp;
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Region', 'kiva') ?>:</th>
				<td>
					<select name="region" id="region">
						<option value="any"<?php if ($options['region'] == 'any') echo ' selected'; ?>><?php _e('Any', 'kiva') ?></option>
						<option value="af"<?php if ($options['region'] == 'af') echo ' selected'; ?>><?php _e('Africa', 'kiva') ?></option>
						<option value="as"<?php if ($options['region'] == 'as') echo ' selected'; ?>><?php _e('Asia', 'kiva') ?></option>
						<option value="me"<?php if ($options['region'] == 'me') echo ' selected'; ?>><?php _e('Middle East', 'kiva') ?></option>
						<option value="ee"<?php if ($options['region'] == 'ee') echo ' selected'; ?>><?php _e('Eastern Europe', 'kiva') ?></option>
						<option value="sa"<?php if ($options['region'] == 'sa') echo ' selected'; ?>><?php _e('South America', 'kiva') ?></option>
						<option value="ca"<?php if ($options['region'] == 'ca') echo ' selected'; ?>><?php _e('Central America', 'kiva') ?></option>
						<option value="na"<?php if ($options['region'] == 'na') echo ' selected'; ?>><?php _e('North America', 'kiva') ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Sector', 'kiva') ?>:</th>
				<td>
					<select name="sector" id="sector">//personal
						<option value="any"<?php if ($options['sector'] == 'any') echo ' selected'; ?>><?php _e('Any', 'kiva') ?></option>
						<option value="agriculture"<?php if ($options['sector'] == 'agriculture') echo ' selected'; ?>><?php _e('Agriculture', 'kiva') ?></option>
						<option value="arts"<?php if ($options['sector'] == 'arts') echo ' selected'; ?>><?php _e('Arts', 'kiva') ?></option>
						<option value="clothing"<?php if ($options['sector'] == 'clothing') echo ' selected'; ?>><?php _e('Clothing', 'kiva') ?></option>
						<option value="construction"<?php if ($options['sector'] == 'construction') echo ' selected'; ?>><?php _e('Construction', 'kiva') ?></option>
						<option value="education"<?php if ($options['sector'] == 'education') echo ' selected'; ?>><?php _e('Education', 'kiva') ?></option>
						<option value="entertainment"<?php if ($options['sector'] == 'entertainment') echo ' selected'; ?>><?php _e('Entertainment', 'kiva') ?></option>
						<option value="food"<?php if ($options['sector'] == 'food') echo ' selected'; ?>><?php _e('Food', 'kiva') ?></option>
						<option value="health"<?php if ($options['sector'] == 'health') echo ' selected'; ?>><?php _e('Health', 'kiva') ?></option>
						<option value="housing"<?php if ($options['sector'] == 'housing') echo ' selected'; ?>><?php _e('Housing', 'kiva') ?></option>
						<option value="manufacturing"<?php if ($options['sector'] == 'manufacturing') echo ' selected'; ?>><?php _e('Manufacturing', 'kiva') ?></option>
						<option value="retail"<?php if ($options['sector'] == 'retail') echo ' selected'; ?>><?php _e('Retail', 'kiva') ?></option>
						<option value="services"<?php if ($options['sector'] == 'services') echo ' selected'; ?>><?php _e('Services', 'kiva') ?></option>
						<option value="transportation"<?php if ($options['sector'] == 'transportation') echo ' selected'; ?>><?php _e('Transportation', 'kiva') ?></option>
						<option value="wholesale"<?php if ($options['sector'] == 'wholesale') echo ' selected'; ?>><?php _e('Wholesale', 'kiva') ?></option>
					</select>
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
add_shortcode('SHOW-KIVA', 'show_kiva');
?>