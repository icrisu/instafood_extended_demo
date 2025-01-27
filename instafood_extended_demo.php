<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
/*
Plugin Name: InstaFood - Extended demo
Plugin URI: https://www.sakuraplugins.com
Description: Extend Instafood demo
Author: SakuraPlugins
Version: 1.1.0
Author URI: https://www.sakuraplugins.com/contact
Text Domain: instafood-extended
Domain Path: /languages
*/

require_once(plugin_dir_path(__FILE__) . 'remote_print/InstafoodRemotePrint.php');



function isInstFoodInstalled(): bool {
    return class_exists('\com\sakuraplugins\appetit\AppetitCore');
}

// Handle manual print request via PrintNode sample
// Manual Print can be triggered from admin view/edit order
function on_instafood_manual_remote_print_request(int $orderId, string $printerId) {
    if (!com\sakuraplugins\appetit\services\PrintNodeService::getInstance()->canUsePrintNode()) {
        return;
    }
    $instPrint = new InstafoodRemotePrint();
    $instPrint->handleRemotePrint($orderId, $printerId);
}

// handle new order from user in the background
function on_instafood_new_order(int $orderId) {
    // sample on how to retrive order data
    // could be used to inform other API's Ex: Send SMS, emails, send alerts to waiter
    // can olso be used to automatically print invoice via PrintNode (see InstafoodRemotePrint example above)

    $order = new com\sakuraplugins\appetit\rest_api\models\Order();
    $order->findOne($orderId);
    if (!$order->getProperty('ID')) {
        return;
    }
    // $_orderAll = $order->getAllProperties();
    // $lineItemsData = $order->getLineItemsData();

    // Dynamically set a different order status exaample
    // $newStatus = com\sakuraplugins\appetit\rest_api\models\OrderStatus::ACCEPTED;
    // $order->setOrderStatus($newStatus);

    // send emails
    // try {
    //     wp_mail('yourEmail1@domain.com,yourEmail2@domain.com', 'New order received', 'Dynamic message body');
    // } catch (Exception $e) {
    //     // error
    // }
}

// handle order status change (Ex: Send SMS, send alerts to waiter)
function on_instafood_order_status_changed(int $orderId, string $newStatus) {
    // possible newStatus values ['NEW_ORDER', 'ACCEPTED', 'REJECTED', 'PREPARED', 'DELIVERED', 'CLOSED']
    
    $order = new com\sakuraplugins\appetit\rest_api\models\Order();
    $order->findOne($orderId);
    if (!$order->getProperty('ID')) {
        return;
    }
    $_orderAll = $order->getAllProperties();
    $lineItemsData = $order->getLineItemsData();

    // Example send email
    // $sendto = 'your.organization@domain.com';
    // $subject = $newStatus === 'NEW_ORDER' ? 'New order - ' . $orderId : $newStatus . ' - ' . $orderId;

    // $_orderType = $_orderAll['_orderType'] ?? '';
    // $_orderTotal = $_orderAll['_orderTotal'] ?? '';

    // $tableNo = '';
    // if ($_orderType === 'DINEIN') {
    //     $tableNo = $_orderAll['_delivery_info']['_table'] ?? '';
    // }

    // $msg = "Order\r\n";
    // $msg .= "Order type: $_orderType\n";
    // $msg .= "Order total: $_orderTotal\n";
    // $msg .= "Order status: $newStatus\n";
    // $msg .= $tableNo !== '' ? "Table no: $tableNo\n" : '';

    // try {
    //     wp_mail($sendto, $subject, $msg);
    // } catch (Exception $e) {
    //     // error
    // }
}

// override price format display (server-side)
function on_instafood_price_format($price) {
    // return null if don't want to alter the price format server side
    return NULL;
    $frontend_enabled_decimal_dot = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("frontend_enabled_decimal_dot", '');
    $separator = $frontend_enabled_decimal_dot === 'ON' ? '.' : ',';
    if (isset($price) && is_numeric($price)) {
        return number_format($price, 2, $separator , ".");
    }
    return number_format(0, 2, $separator , ".");
}

// custom scripts (Ex: handle override price format within the frontend)
function on_instafood_extended_js_scrips($position = 'body') {
    return '<script src="' . plugins_url('',  __FILE__ ) . '/assets/js/instafood_custom.js' . '"></script>';
}

// handle order payment status change (Ex: Send SMS, send alerts to waiter)
function on_instafood_order_payment_status_changed(int $orderId, string $paymentStatus) {
    // possible paymentStatus values ['PENDING_TRANSACTION', 'PAYMENT_INTENT_SUCCEDED', 'PENDING_ON_DELIVERY', 'PAYMENT_FAILED', 'PAYMENT_CANCELED']
    // use a similar logic within on_instafood_order_status_changed
}

// instafood custom hooks
if (isInstFoodInstalled()) {
    add_action('instafood_manual_remote_print_request', 'on_instafood_manual_remote_print_request', 10, 2);
    add_action('instafood_new_order', 'on_instafood_new_order', 10, 1);
    add_action('instafood_order_status_changed', 'on_instafood_order_status_changed', 10, 2);
    add_filter('instafood_price_format_filter', 'on_instafood_price_format', 11, 1);
    add_filter('instafood_extended_js_scrips', 'on_instafood_extended_js_scrips', 11, 1);
    add_action('instafood_order_payment_status_changed', 'on_instafood_order_payment_status_changed', 10, 2);
}


?>