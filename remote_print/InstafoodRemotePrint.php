<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class InstafoodRemotePrint {
    static function handleRemotePrint(int $orderId, string $printerId) {

        $this->order = new com\sakuraplugins\appetit\rest_api\models\Order();
        $orderResult = $this->order->findOne($orderId);
        
        if (!isset($orderResult) && !isset($orderResult['ID'])) {
            return;
        }
    
        $this->order->populate();
        $lineItemsData = $this->order->getLineItemsData();
    
        $this->restaurant_name = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_name", '');
        $this->restaurant_address = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_address", '');
        $this->restaurant_phone = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_phone", '');
        $vat = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("vat_percentage", '');
        $this->vat_percentage = 0;
        if ($vat !== '' && is_numeric(floatval($vat))) {
            $this->vat_percentage = floatval($vat);
        }
    
        file_put_contents('php://stderr', print_r(['orderResultsss' => $lineItemsData], TRUE));
    
        $ESC = "\e";
        $GS = chr(29);
        $InitializePrinter = "@";
        $nl = "\n"; //Single new line
        $dnl = $nl.$nl; //Double new line
        $BoldOn = $ESC . "E" . "1";
        $BoldOff = $ESC . "E" . "0";
        $DoubleOn = $GS . "!" . "16";  // 2x sized text (double-high + double-wide)
        $DoubleOff = $GS . "!" . "0";
        $jLeft =$ESC."a"."0";//allign Left
        $jCenter =$ESC."a"."1";//allign Center
        $jRight =$ESC."a"."2";//allign Right
        
        $cmd = $ESC.$InitializePrinter."Appetit receipt #$orderId".$nl;
        $cmd .= $BoldOn."$this->restaurant_name".$BoldOff." ".$nl;
        $cmd .= "Address: $this->restaurant_address".$nl;
        $cmd .= "Phone: $this->restaurant_phone".$nl;
        $cmd .= $dnl;
    
        $line_items = $lineItemsData['line_items'] ?? [];
        foreach ($line_items as $lineItem) {

        }
    
    
        // $cmd .= "Regular allign".$nl;
        // $cmd .= $jLeft."Left allign".$nl;
        // $cmd .= $jCenter."Center allign".$nl;
        // $cmd .= $jRight."Right allign".$nl;
        // $cmd .= $jLeft."VAT:".$jRight."45 $".$nl;
        // $cmd .= $ESC;
    
        file_put_contents('php://stderr', print_r(['cmd>>>' => $cmd], TRUE));
    
    
        // $printResult = com\sakuraplugins\appetit\services\PrintNodeService::getInstance()->printOrder([
        //     'printerId' => $printerId,
        //     'contentType' => 'raw_base64',
        //     'content' => base64_encode($cmd)
        // ]);
    }

    private function _priceUtil($price) {
        $formattedPrice = com\sakuraplugins\appetit\utils\PriceUtil::getInstance()->numberTwoDecimals($price);
        $currencySymbol = $this->lineItemsData['currencySymbol'] ?? '';
        $currencyPosition = $this->lineItemsData['currencyPosition'] ?? '';
        if ($currencyPosition === 'BEFORE') {
            return $currencySymbol . $formattedPrice;
        }
        return $formattedPrice . $currencySymbol;
    }
}
?>