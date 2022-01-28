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

// Handle manual print request via PrintNode
function on_manual_remote_print_request(int $orderId, string $printerId) {
    if (!com\sakuraplugins\appetit\services\PrintNodeService::getInstance()->canUsePrintNode()) {
        return;
    }
    $instPrint = new InstafoodRemotePrint();
    $instPrint->handleRemotePrint($orderId, $printerId);
}

// hooks
if (isInstFoodInstalled()) {
    add_action('manual_remote_print_request', 'on_manual_remote_print_request', 10, 2);
}


?>