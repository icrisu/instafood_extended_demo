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
    // could be used to inform other API's Ex: Send SMS, send alerts to waiter
    // can olso be used to automatically print invoice via PrintNode (see InstafoodRemotePrint example above)

    // $order = new com\sakuraplugins\appetit\rest_api\models\Order();
    // $order->findOne($orderId);
    // if (!$order->getProperty('ID')) {
    //     return;
    // }
    // $_orderAll = $order->getAllProperties();
    // $lineItemsData = $order->getLineItemsData();
}

// handle order status change (Ex: Send SMS, send alerts to waiter)
function on_instafood_order_status_changed(int $orderId, string $newStatus) {
    // possible newStatus values ['NEW_ORDER', 'ACCEPTED', 'REJECTED', 'PREPARED', 'DELIVERED', 'CLOSED']
}

// hooks
if (isInstFoodInstalled()) {
    add_action('instafood_manual_remote_print_request', 'on_instafood_manual_remote_print_request', 10, 2);
    add_action('instafood_new_order', 'on_instafood_new_order', 10, 1);
    add_action('instafood_order_status_changed', 'on_instafood_order_status_changed', 10, 2);
}


?>