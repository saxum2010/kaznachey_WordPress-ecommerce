<?php
/*
Plugin Name: Kaznachey
Plugin URI: http://kaznachey.ua
Description: Кредитная карта Visa/MC, Webmoney, Liqpay, Qiwi... (www.kaznachey.ua)
Version: 1.1
Author: kaznachey
Author email : info@kaznachey.ua
*/

global $wpdb, $wpsc_cart, $wpsc_coupons;
function kzn_activation_control () {
global $wpdb;
		$kzn_chek_path = 0;
		$kzn_chek_code = 0;
		$kzn_chek_merchant = 0;
		$kzn_chek_readme = 0;
		$kzn_installation_errors = "<br />";
		$kzn_plugin_path = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."kzn_code";
		$kzn_plugin_code = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."kaznachey.php";
		$kzn_merchant_path = WPSC_FILE_PATH."/wpsc-merchants/kaznachey.php";
		$kzn_plugin_readme = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."readme.txt";
		if (file_exists($kzn_plugin_path)){$kzn_chek_path = 1;} else {$kzn_installation_errors .= "Path_ERROR ";}
		if (file_exists($kzn_plugin_code)){$kzn_chek_code = 1;} else {$kzn_installation_errors .= "Code_ERROR ";}
		if (file_exists($kzn_merchant_path)){$kzn_chek_merchant = 1;} else {$kzn_installation_errors .= "Merchant_ERROR ";}
		if (file_exists($kzn_plugin_readme)){$kzn_chek_readme = 1;} else {$kzn_installation_errors .= "Readme_ERROR<br />Put readme file in kzn plugin directory!";}
		$kzn_activation_result = $kzn_chek_path + $kzn_chek_code + $kzn_chek_merchant + $kzn_chek_readme;
		If ($kzn_activation_result == 4){echo "kaznachey plugin installed!";} else {kzn_plugin_deinit(); echo $kzn_installation_errors;}
}

//Plugin activation function --------------------------------------------------------------------------------------
	function initialize_kzn_plugin() {
	global $wpdb;
		$kzn_plugin_path = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."kzn_code";
		$kzn_merchant_path = WPSC_FILE_PATH."/wpsc-merchants/kaznachey.php";
		if (file_exists($kzn_plugin_path)) {
			$coded_kzn_code = urlencode(file_get_contents($kzn_plugin_path));
			$decoded_kzn_code = urldecode($coded_kzn_code);
			$decoded_kzn_file_write_result = file_put_contents($kzn_merchant_path, $decoded_kzn_code);
		} else {
		return;	
		}	
		
		$kzn_result_path = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."kaznachey_result";
		$kaznachey_result = WPSC_FILE_PATH."/wpsc-merchants/kaznachey_result.php";
		if (file_exists($kzn_result_path)) {
			$coded_kzn_code = urlencode(file_get_contents($kzn_result_path));
			$decoded_kzn_code = urldecode($coded_kzn_code);
			$decoded_kzn_file_write_result = file_put_contents($kaznachey_result, $decoded_kzn_code);
		}
		else {
		return;	
		}		
		
		$kzn_img_path = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))."kaznachey.png";
		$kaznachey_img = WPSC_FILE_PATH."/images/kaznachey.png";
		/* $kaznachey_img = WPSC_CORE_IMAGES_URL."/kaznachey.png"; */
		if (file_exists($kzn_img_path)) {
			$coded_kzn_code = urlencode(file_get_contents($kzn_img_path));
			$decoded_kzn_code = urldecode($coded_kzn_code);
			$decoded_kzn_file_write_result = file_put_contents($kaznachey_img, $decoded_kzn_code);
		}
		else {
		return;	
		}
	}

// XML payment confirm function ---------------------------------------------------

function  kzn_payment_confirm() {
global $merchnatSecretKey;
global $urlGetMerchantInfo;
global $urlGetClientMerchantInfo;
$merchnatSecretKey = get_option('merchnatSecretKey'); 
$urlGetMerchantInfo = get_option('urlGetMerchantInfo'); 
$urlGetClientMerchantInfo = get_option('urlGetClientMerchantInfo');

// sign validator --------------------------------------------------------------------
	function income_sign_compare() {
	$merchnatSecretKey = get_option('merchnatSecretKey'); 
	$urlGetMerchantInfo = get_option('urlGetMerchantInfo'); 
	$urlGetClientMerchantInfo = get_option('urlGetClientMerchantInfo'); 

		$income_sign=$_POST['sign'];
		$decoded_income_sign = base64_decode($income_sign);
		$generated_sign = md5($urlGetMerchantInfo.base64_decode(strtr($_POST['xml'], ' ', '+')).$urlGetMerchantInfo);
		$sign_cmp_result = 9;
		$sign_cmp_result = strcmp ( $generated_sign, $decoded_income_sign ) ;
		return $sign_cmp_result;
	}

//------------- parser --------------------------------------------
	function kzn_request_parse($magik_tag) {
		$verified_income_xml = strtr($_POST['xml'], ' ', '+');
		$decoded_verified_income_xml = base64_decode($verified_income_xml);
		$inurl_decoded_verified_income_xml = urlencode($decoded_verified_income_xml);
		$inurl_opening_magictag = urlencode($magik_tag.">");
		$position_of_opening_tag = stripos ($inurl_decoded_verified_income_xml, $inurl_opening_magictag);
		$lengs_of_opening_tag = strlen($inurl_opening_magictag);
		$inurl_closing_magictag = urlencode("</".$magik_tag);
		$position_of_closing_tag = stripos ($inurl_decoded_verified_income_xml, $inurl_closing_magictag);
		$cutting_data_from_position = $position_of_opening_tag + $lengs_of_opening_tag;

			if ($position_of_closing_tag >  $position_of_opening_tag) {
				$parsed_data = urldecode( substr ( $inurl_decoded_verified_income_xml, $cutting_data_from_position, $position_of_closing_tag -  $cutting_data_from_position));
				return $parsed_data;
			}
			else {
				$parsed_data = "notFound";
				return $parsed_data;
			}
	}

// compare resuts function -----------------------------------------	
function kaznachey_callback($kzn_answer,$kzn_error) {
	$merchnatSecretKey = get_option('merchnatSecretKey');
	$urlGetMerchantInfo = get_option('urlGetMerchantInfo');
	$urlGetClientMerchantInfo = get_option('urlGetClientMerchantInfo');
	global $wpdb, $wpsc_cart, $wpsc_coupons;
	$data['pay_for'] = $purchase_log[0]['id'];
	$data['currency'] = get_option('kaznachey_curcode');
	$merchantGuid = get_option('merchantGuid');
	$income_cur = kzn_request_parse ( 'currency');
	$income_order_id = kzn_request_parse ( "description");
	$income_total_price = kzn_request_parse ( "amount");
	$income_status = kzn_request_parse ( 'status');
	$output_answer = '<?xml version="1.0" encoding="UTF-8"?><result><status>'.$kzn_answer.'</status><error_msg>'.$kzn_error.'</error_msg></result>';
	return $output_answer;
}
	
function kzn_db_update() {
	wpsc_core_constants_table_names();
	global $wpdb, $wpsc_cart, $wpsc_coupons;
	$loc_curr = get_option('kaznachey_curcode');
	$income_session_id = kzn_request_parse( "description");
	$income_curr = kzn_request_parse( 'currency');
	$merchantGuid = get_option('merchantGuid');
	$income_total_price = kzn_request_parse ( "amount");
	$income_status = kzn_request_parse ( 'status'); 
	$kzn_transactid = kzn_request_parse ('trans_id');
	$answer_error = '';
	$external_query = @mysql_query("SELECT * FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid='".$income_session_id."'") or die('Session id no founded');
	$kzn_old_status = mysql_result($external_query, 0, processed);
	$db_total_price = mysql_result($external_query, 0, totalprice);
	$db_id = mysql_result($external_query, 0, id);
	$db_transaction = mysql_result($external_query, 0, transactid);
	$db_sessionid = mysql_result($external_query, 0, sessionid);
	if (strcmp ( $income_session_id, $db_sessionid ) != 0) {$answer_error = 'no such purhase session in DB';}
	if (strcmp ( $db_total_price, $income_total_price )!= 0) {$answer_error = 'wrong price';}
	if (strcmp ( $loc_curr, $income_curr )!= 0) {$answer_error = 'wrong currency';}
	if ($answer_error == '') {
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET processed='3' WHERE sessionid='".$income_session_id."'") or die; 
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET transactid='".$kzn_transactid."' WHERE sessionid='".$income_session_id."'") or die; 	
	}
	else {
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET processed='6' WHERE sessionid='".$income_session_id."'") or die; 
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET transactid='".$kzn_transactid."' WHERE sessionid='".$income_session_id."'") or die;
	}
	return $answer_error;
	}
// compare resuts function ----------------------------------------		
	if ( income_sign_compare() == 0) {
	$kzn_error = kzn_db_update();
		if ($kzn_error == ''){
		echo kaznachey_callback("yes", "");
		}
		else {
		echo kaznachey_callback("no", $kzn_error);
		}
	}
	else {
		echo kaznachey_callback("no", "wrong sign");
	}
}

// Payment result function =======================================================================================	

function kaznachey_payment_result	() {
	wpsc_core_constants_table_names();

//parser
	function kzn_request_parse($magik_tag) {
		$verified_income_xml = strtr($_POST['xml'], ' ', '+');
		$decoded_verified_income_xml = base64_decode($verified_income_xml);
		$inurl_decoded_verified_income_xml = urlencode($decoded_verified_income_xml);
		$inurl_opening_magictag = urlencode($magik_tag.">");
		$position_of_opening_tag = stripos ($inurl_decoded_verified_income_xml, $inurl_opening_magictag);
		$lengs_of_opening_tag = strlen($inurl_opening_magictag);
		$inurl_closing_magictag = urlencode("</".$magik_tag);
		$position_of_closing_tag = stripos ($inurl_decoded_verified_income_xml, $inurl_closing_magictag);
		$cutting_data_from_position = $position_of_opening_tag + $lengs_of_opening_tag;
		$cutting_data_length = $position_of_closing_tag -  $cutting_data_from_position;

			if ($position_of_closing_tag >  $position_of_opening_tag) {
				$parsed_data = urldecode( substr ( $inurl_decoded_verified_income_xml, $cutting_data_from_position, $cutting_data_length));
				return $parsed_data;
			}
			else {
				$parsed_data = "notFound";
				return $parsed_data;
			}
	}
$session_id = kzn_request_parse("description");
//Cart update -----------------------------------------------
function my_cart_upd ($session_id) {
	global $wpdb, $wpsc_cart, $wpsc_coupons, $cart;
	$wpsc_cart = 0;
	$nyan = @mysql_query("SELECT * FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid='".$session_id."'") or die('Session id not founded');
	$old_status = mysql_result($nyan, 0, processed);
		if ($old_status == 1) {
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET processed='2' WHERE sessionid='".$session_id."'") or die ('Session id not founded'); 
		$db1=@mysql_query ("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET transactid='not yet confirmed' WHERE sessionid='".$session_id."'") or die ('Session id not founded'); 	
		$cart = 0;
		}

}
my_cart_upd($session_id);

$merchnatSecretKey = get_option('merchnatSecretKey'); //string 70
$urlGetMerchantInfo = get_option('urlGetMerchantInfo'); // string 103
$urlGetClientMerchantInfo = get_option('urlGetClientMerchantInfo'); // string 103
$kaznachey_payment_result_xml = base64_decode(strtr($_POST['xml'], ' ', '+'));
$kaznachey_payment_result_sign = base64_decode($_POST['sign']);
$kaznachey_payment_result_generated_sign = md5($urlGetMerchantInfo.$kaznachey_payment_result_xml.$urlGetMerchantInfo);

   	$kzn_error_status = get_option('error_msg');
	$kzn_paymode = kzn_request_parse("paymode");
	$kzn_cur = kzn_request_parse( 'currency');
	$income_total_price = kzn_request_parse ( 'amount');
	$income_status = kzn_request_parse ( 'status');

		switch ($income_status) {
			case "process": $kzn_status='<strong>ожидается оплата</strong>'; break;
			case "success": $kzn_status='<strong>заказ оплачен</strong>'; break; 
			case "fail": $kzn_status='<strong>оплата отменена</strong>'; break;
			case "error": $kzn_status='<strong>произошла ошибка</strong>'; break;
		default: $kzn_status='<strong>неизвестен</strong>'; break; 
		}

$nyan = @mysql_query("SELECT * FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid='".$session_id."'") or die('Session id no founded');
$db_total_price = mysql_result($nyan, 0, totalprice);
$db_id = mysql_result($nyan, 0, id);
$db_transaction = mysql_result($nyan, 0, transactid);

return  '<div><table><th colspan=2>Результат транзакции:</th>
<tr><td>Номер заказа:</td><td>&nbsp;'.$db_id.'</td></tr>
<tr><td>Вы оплатили:</td><td>&nbsp;'.$income_total_price.' '.$kzn_cur.'</td></tr>
<tr><td>Способ оплаты:</td><td>&nbsp;'.$kzn_paymode.'</td></tr>
<tr><td>Статус заказа:</td><td>&nbsp;'.$kzn_status.'</td></tr>
<tr><td>ID транзакции:</td><td>&nbsp;'.$db_transaction.'</td></tr>
</table></div>';

}

// Payment result function =======================================================================================	

//kaznachey plugin deinitialisation ------------------------------------------------------------------------------
function kzn_plugin_deinit () {
$kzn_merchant_path = WPSC_FILE_PATH."/wpsc-merchants/kaznachey.php";
unlink ( $kzn_merchant_path );
}

add_shortcode('success_url', 'kaznachey_payment_result'); 
add_shortcode('kzn_activation_control', 'kzn_activation_control');
add_shortcode('result_url', 'kzn_payment_confirm');
register_deactivation_hook( __FILE__, 'kzn_plugin_deinit');
register_activation_hook( __FILE__, 'initialize_kzn_plugin');
?>