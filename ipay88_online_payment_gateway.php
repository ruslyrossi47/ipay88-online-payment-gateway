<?php
/*
Plugin Name: iPay88 - Online Payment Gateway
Version: v1.0
Author: Rusly
Author URI: https://www.facebook.com/ruslyrossi46
Description: Easy to use Wordpress plugin to accept iPay88 payment for a service or product in one click. Can be used in the sidebar, posts and pages.
License: GPL2
*/

define('WP_IPAY_PAYMENT_ACCEPT_PLUGIN_VERSION', '1.0');
define('WP_IPAY_PAYMENT_ACCEPT_PLUGIN_URL', plugins_url('',__FILE__));

include_once('shortcode_view.php');

function wp_ipay_plugin_install ()
{
	// Some default options
	add_option('ipay_payment_response_url', home_url());
	add_option('ipay_payment_merchant_key', '');
	add_option('ipay_payment_merchant_code', '');
	add_option('ipay_payment_currency', 'MYR');
}
register_activation_hook(__FILE__,'wp_ipay_plugin_install');

add_shortcode('wp_ipay88', 'wpapp_buy_now_any_amt_handler');
function wpapp_buy_now_any_amt_handler($args)
{
	$output = wppp_render_ipay_button_with_other_amt($args);
	return $output;
}


add_action( 'init', 'wpapp_shortcode_plugin_enqueue_jquery' );
function wpapp_shortcode_plugin_enqueue_jquery() {
	wp_enqueue_script('jquery');
}

function iPay88_signature($source)
{
  return base64_encode(hex2bin(sha1($source)));
}

if (!function_exists('hex2bin')){
	function hex2bin($hexSource)
	{
		for ($i=0;$i<strlen($hexSource);$i=$i+2)
		{
		  $bin .= chr(hexdec(substr($hexSource,$i,2)));
		}
	  return $bin;
	}
}

function wp_ipayp_process($content)
{
    if (strpos($content, "<!-- wp_ipay_payment -->") !== FALSE)
    {
        $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        $content = str_replace('<!-- wp_ipay_payment -->', iPay_payment_accept(), $content);
    }
    return $content;
}

// Displays iPay88 Payment Accept Options menu
function ipay_payment_add_option_pages() {
    if (function_exists('add_options_page')) {
        add_options_page('WP iPay88 Payment Accept', 'WP iPay88 Payment', 'manage_options', __FILE__, 'ipay_payment_options_page');
    }
}

function ipay_payment_options_page() {

    if (isset($_POST['info_update']))
    {
        echo '<div id="message" class="updated fade"><p><strong>';

        update_option('ipay_payment_currency', (string)$_POST["ipay_payment_currency"]);
		update_option('ipay_payment_merchant_key', $_POST["ipay_payment_merchant_key"]);
		update_option('ipay_payment_merchant_code', $_POST["ipay_payment_merchant_code"]);
        update_option('ipay_payment_response_url', (string)$_POST["ipay_payment_response_url"]);       
                
        echo 'Options Updated!';
        echo '</strong></p></div>';
    }

    $ipay_payment_currency = stripslashes(get_option('ipay_payment_currency'));

    ?>

    <div class=wrap>
       <h2>Accept iPay88 Payment Settings v <?php echo WP_IPAY_PAYMENT_ACCEPT_PLUGIN_VERSION; ?></h2>
       <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
          <input type="hidden" name="info_update" id="info_update" value="true" />
          <fieldset class="options">
             <h3>Plugin Usage:</h3>
             <p>There are a few ways you can use this plugin:</p>
             <ol>
                <li>Add the shortcode <strong>[wp_ipay88]</strong> to a post or page</li>
                <li>Use the shortcode with custom parameter option to add multiple different payment widget in different areas of the site.</li>
             </ol>
          </fieldset>
          <fieldset class="options">
             <strong>Plugin Options</strong><br />
             <table width="100%" border="0" cellspacing="0" cellpadding="6">
                <tr valign="top">
                   <td width="25%" align="right">
                      <strong>Merchant Key : </strong>
                   </td>
                   <td align="left">
                      <input type="text" name="ipay_payment_merchant_key" value="<?php echo get_option('ipay_payment_merchant_key') ?>" />
                   </td>
                </tr>
                <tr valign="top">
                   <td width="25%" align="right">
                      <strong>Merchant Code : </strong>
                   </td>
                   <td align="left">
                      <input type="text" name="ipay_payment_merchant_code" value="<?php echo get_option('ipay_payment_merchant_code') ?>" />
                   </td>
                </tr>
                <tr valign="top">
                   <td width="25%" align="right">
                      <strong>Choose Payment Currency : </strong>
                   </td>
                   <td align="left">
                      <input type="text" name="ipay_payment_currency" value="<?php echo $ipay_payment_currency ?>" />
                      <br /><i>This is the currency for your visitors to make Payments or Donations in.</i><br /><br />
                   </td>
                </tr>
             </table>
             <br />
             <strong>Return URL from iPay88 :</strong>
             <input name="ipay_payment_response_url" type="text" size="60" value="<?php echo get_option('ipay_payment_response_url'); ?>"/>
             <br /><i>Enter a return URL (could be a Thank You page). iPay88 will redirect visitors to this page after Payment</i><br />
          </fieldset>
          <div class="submit">
             <input type="submit" class="button-primary" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
          </div>
       </form>
    </div>
    <!-- end of .wrap -->
<?php
}

function wp_ipay_payment_widget_control()
{
    ?>
    <p>
    <? _e("Set the Plugin Settings from the Settings menu"); ?>
    </p>
    <?php

}
function wp_ipay_payment_init()
{
	wp_register_style('wpapp-styles', WP_IPAY_PAYMENT_ACCEPT_PLUGIN_URL.'/wpapp-styles.css');
    wp_enqueue_style('wpapp-styles');
	
	if(isset($_GET['response']) && ($_GET['response'] == 1)) {
		callback('response callback');
	}
	

}

function callback($alert) {

		$expected_sign = $_POST['Signature'];
	    $merId = get_option('ipay_payment_merchant_code');
        $ikey = get_option('ipay_payment_merchant_key');
		
		$check_sign = "";
		$ipaySignature = "";
		$str = "";
		$HashAmount = "";
		
		$HashAmount = str_replace(array(',','.'), "", $_POST['Amount']);
		$str = $ikey . $merId . $_POST['PaymentId'].trim(stripslashes($_POST['RefNo'])). $HashAmount . $_POST['Currency'].$_POST['Status'];
	
	
		$str = sha1($str);
	   
	    for ($i=0;$i<strlen($str);$i=$i+2)
		{
        	$ipaySignature .= chr(hexdec(substr($str,$i,2)));
		}
       
		$check_sign = base64_encode($ipaySignature);
		
			
	if ($_POST['Status'] == 1 && $check_sign == $expected_sign) {
		echo "<script>alert('Your transaction has been accepted. Your reference number is ".$_POST['RefNo']."');</script>";
		//wp_redirect( home_url().'?status=success' ); exit; 
	} else {
		echo "<script>alert('Sorry, your transaction has failed');</script>";
		//wp_redirect( home_url().'?status=failed' ); exit; 
	}
}

add_filter('the_content', 'wp_ipayp_process');

if (!is_admin()) {
	add_filter('widget_text', 'do_shortcode');
}

add_action('init', 'wp_ipay_payment_init');

// Insert the ipay_payment_add_option_pages in the 'admin_menu'
add_action('admin_menu', 'ipay_payment_add_option_pages');
