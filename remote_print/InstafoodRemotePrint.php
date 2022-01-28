<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class InstafoodRemotePrint {
    
    function handleRemotePrint(int $orderId, string $printerId) {

        $this->order = new com\sakuraplugins\appetit\rest_api\models\Order();
        $orderResult = $this->order->findOne($orderId);
        
        if (!isset($orderResult) && !isset($orderResult['ID'])) {
            return;
        }
    
        $this->order->populate();
        $this->lineItemsData = $this->order->getLineItemsData();
    
        $this->restaurant_name = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_name", '');
        $this->restaurant_address = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_address", '');
        $this->restaurant_phone = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("restaurant_phone", '');
        $vat = com\sakuraplugins\appetit\utils\OptionUtil::getInstance()->getOption("vat_percentage", '');
        $this->vat_percentage = 0;
        if ($vat !== '' && is_numeric(floatval($vat))) {
            $this->vat_percentage = floatval($vat);
        }
    
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
        $cmd .= $BoldOn.$this->restaurant_name.$BoldOff." ".$nl;
        $cmd .= "Address: ".$this->restaurant_address.$nl;
        $cmd .= "Phone: ".$this->restaurant_phone.$nl;
        $cmd .= $dnl;
    
        $line_items = $this->lineItemsData['line_items'] ?? [];
        foreach ($line_items as $lineItem) {
            $type = $lineItem['type'] ?? '';
            if ($type === 'prduct') {
                $price = $lineItem['price'] ?? '';
                $quantity = $lineItem['quantity'] ?? '';
                $name = $quantity . 'x ' . $lineItem['name'] ?? '';
                $hasVariants = $lineItem['hasVariants'] ?? false;
                if ($hasVariants) {
                    $variantName = $lineItem['variant']['name'] ?? '';
                    $name .= " ($variantName)";
                }
                
                $choices = $lineItem['choices'] ?? [];
                $choicesText = '';
                if (sizeof($choices) > 0) {
                    for ($i = 0; $i < sizeof($choices); $i++) { 
                        $choiceName = $choices[$i]['name'] ?? '';
                        $choiceSeparator = $i < sizeof($choices) - 1 ? ', ' : '';
                        $choicesText .= $choiceName . $choiceSeparator;
                    }
                }
                $cmd .= $jLeft."$name:".$jRight.$this->_priceUtil($price).$nl;
                if ($choicesText !== '') {
                    $cmd .= $jLeft."($choicesText)".$nl;
                }
            }
            if ($type === 'tipping') {
                $tipName = $lineItem['name'] ?? '';
                $tipPrice = $lineItem['price'] ?? '';
                $cmd .= $jLeft."$tipName:".$jRight.$this->_priceUtil($tipPrice).$nl;
            }
            if ($type === 'delivery_cost') {
                $deliveryName = $lineItem['name'] ?? '';
                $deliveryPrice = $lineItem['price'] ?? '';
                $cmd .= $jLeft."$deliveryName:".$jRight.$this->_priceUtil($deliveryPrice).$nl;
            }
        }

        if (sizeof($line_items) > 0 && $this->vat_percentage) {
            $cmd .= $dnl;

            $order_total = $this->lineItemsData['total'] ?? '';
            $vatTotal = ($order_total * $this->vat_percentage) / 100;
            $totalNet = $order_total - $vatTotal;
            $totalNet = number_format((float)$totalNet, 2, '.', '');
            $vatTotal = number_format((float)$vatTotal, 2, '.', '');

            $cmd .= $jLeft.$BoldOn."Subtotal".$jRight.$this->_priceUtil($totalNet).$BoldOff." ".$nl;
            $cmd .= $jLeft.$BoldOn."VAT".$jRight.$this->_priceUtil($vatTotal).$BoldOff." ".$nl;
            $cmd .= $jLeft.$BoldOn."Total".$jRight.$this->_priceUtil($order_total).$BoldOff." ".$nl;
        }
    
        $cmd .= $ESC;

        $printResult = com\sakuraplugins\appetit\services\PrintNodeService::getInstance()->printOrder([
            'printerId' => $printerId,
            'contentType' => 'raw_base64',
            'content' => base64_encode($cmd)
        ]);
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