<?php 

function wppp_render_ipay_button_with_other_amt($args)
{
	// Extract shortcode variables
	extract( shortcode_atts( array(
				'amount' => '',
				'title' => ''
				), $args));	
	
	// Validate shortcode
	if(empty($amount)){
		$output = '<p style="color: red;">Error! Please enter your amount using the "amount" parameter in the shortcode</p>';
		return $output;
	}
		
	if(empty($title)){
		$output = '<p style="color: red;">Error! Please enter your title for the payment using the "title" parameter in the shortcode</p>';
		return $output;
	}
	
	// Set var
	$ipay_payment_merchant_key = get_option('ipay_payment_merchant_key');
	$ipay_payment_merchant_code = get_option('ipay_payment_merchant_code');
	$payment_currency = get_option('ipay_payment_currency');
	$ipay_payment_response_url = get_option('ipay_payment_response_url');
	$ref_no = 'A0000001';
	$amount = $amount;
	$title = $title;
	$ipay_signature = iPay88_signature($ipay_payment_merchant_key.$ipay_payment_merchant_code.$ref_no.str_replace('.', '', $amount).$payment_currency);
		
    /* === iPay form === */
	$output = '';
    $output .= '<div id="accept_ipay_payment_form">';
    $output .= '
			<p><strong>Pay using iPay88 Online Payment</strong></p>
			<form method="post" name="ePayment" action="https://www.mobile88.com/ePayment/entry.asp">
				<input type="hidden" name="MerchantCode" value="' . $ipay_payment_merchant_code . '">
				<input type="hidden" name="PaymentId" value="">
				<input type="hidden" name="RefNo" value="' . $ref_no . '">
				<input type="hidden" name="Currency" value="' . $payment_currency . '">
				<input type="hidden" name="ProdDesc" value="' . $title . '">
				<label for="UserName">Name</label><input type="text" name="UserName" value="John Doe"><br>
				<label for="UserEmail">Email</label><input type="text" name="UserEmail" value="ruslyrossi47@gmail.com"><br>
				<label for="UserContact">Phone</label><input type="text" name="UserContact" value="0102009587"><br>
				<label for="Amount">Amount ('.$payment_currency.')</label><strong>'.$amount.'</strong><input type="hidden" name="Amount" value="' . $amount . '"><br />
				<input type="hidden" name="Remark" value="">
				<input type="hidden" name="Lang" value="UTF-8">
				<input type="hidden" name="Signature" value="' . $ipay_signature . '">
				<input type="hidden" name="ResponseURL" value="' . $ipay_payment_response_url . '">
				<input type="image" src="wp-content/plugins/ipay88-online-payment-gateway/button_ipay88a.png" name="submit" alt="Make payments with iPay88 - it\'s fast, free and secure!" />
			</form>';

    $output .= '</div>';
		
	return $output;
}




