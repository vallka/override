<?php

class OrderHistory extends OrderHistoryCore
{
    public function sendEmail($order, $template_vars = false)
    {
PrestaShopLogger::addLog('OrderHistory-o:sendEmail:'.$this->id_order.':'.$order->id_carrier);  

        $result = Db::getInstance()->getRow('
            SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`
            FROM `' . _DB_PREFIX_ . 'order_history` oh
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON oh.`id_order` = o.`id_order`
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON oh.`id_order_state` = os.`id_order_state`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
            WHERE oh.`id_order_history` = ' . (int) $this->id . ' AND os.`send_email` = 1');
        if (isset($result['template']) && Validate::isEmail($result['email'])) {

PrestaShopLogger::addLog('OrderHistory-o:sendEmail:template_vars'.var_export($template_vars,1));  

            ShopUrl::cacheMainDomainForShop($order->id_shop);

            $topic = $result['osname'];
            $carrierUrl = '';
            $carrierName = '';
            if (Validate::isLoadedObject($carrier = new Carrier((int) $order->id_carrier, $order->id_lang))) {
                $carrierUrl = $carrier->url;
                $carrierName = $carrier->name;
            }

            $shipping_number = $order->getWsShippingNumber();

            if (preg_match('/^P2G\d+$/',$shipping_number)) {
                $carrierUrl='https://www.parcel2go.com/tracking/@';
                $carrierName = 'parcel2go';
            }
            else if (preg_match('/^TN\w+$/',$shipping_number)) {
                $carrierUrl='https://www.royalmail.com/track-your-item#/tracking-results/@';
                $carrierName = 'RoyalMail';
            }
            else if (preg_match('/^1Z\w+$/',$shipping_number)) {
                $carrierUrl='https://www.ups.com/track?loc=en_GB&track$shipping_numberm=@&requester=ST/trackdetails';
                $carrierName = 'UPS';
            }
            else if (preg_match('/^DPD\d+$/',$shipping_number)) {
                $carrierUrl='https://www.dpdlocal-online.co.uk/tracking/@';
                $carrierName = 'DPD';
            }
            else if (preg_match('/^\d{10}$/',$shipping_number)) {
                $carrierUrl='https://www.dhl.co.uk/en/express/tracking.html?AWB=@';
                $carrierName = 'DHL';
            }
            else if (preg_match('/^\d{16}$/',$shipping_number)) {
                $carrierUrl='https://new.myhermes.co.uk/track.html#/parcel/@';
                $carrierName = 'Hermes';
            }


            $data = [
                '{lastname}' => $result['lastname'],
                '{firstname}' => $result['firstname'],
                '{id_order}' => (int) $this->id_order,
                '{order_name}' => $order->getUniqReference(),
                '{followup}' => str_replace('@', $order->getWsShippingNumber(), $carrierUrl),
                '{shipping_number}' => $order->getWsShippingNumber(),
                '{carrier}' => $carrierName,
            ];

PrestaShopLogger::addLog("OrderHistory-o:sendEmail:getWsShippingNumber:".$order->getWsShippingNumber());  
PrestaShopLogger::addLog("OrderHistory-o:sendEmail:followup:".str_replace('@', $order->getWsShippingNumber(), $carrierUrl));  

            if ($result['module_name']) {
                $module = Module::getInstanceByName($result['module_name']);
                if (Validate::isLoadedObject($module) && isset($module->extra_mail_vars) && is_array($module->extra_mail_vars)) {
                    $data = array_merge($data, $module->extra_mail_vars);
                }
            }

            /* ** commented by vallka out as:
               ** $data['{followup}] is the only var passed in $template_vars and this is exactly what we want to change above!!!

            PrestaShopLogger::addLog("OrderHistory-o:data1:".$data['{followup}']);

            if (is_array($template_vars)) {
                $data = array_merge($data, $template_vars);
            }
            PrestaShopLogger::addLog("OrderHistory-o:data2:".$data['{followup}']);
            */

            $context = Context::getContext();
            $data['{total_paid}'] = Tools::getContextLocale($context)->formatPrice((float) $order->total_paid, Currency::getIsoCodeById((int) $order->id_currency));

            if (Validate::isLoadedObject($order)) {
                // Attach invoice and / or delivery-slip if they exists and status is set to attach them
                if (($result['pdf_invoice'] || $result['pdf_delivery'])) {
                    $invoice = $order->getInvoicesCollection();
                    $file_attachement = [];

                    if ($result['pdf_invoice'] && (int) Configuration::get('PS_INVOICE') && $order->invoice_number) {
                        Hook::exec('actionPDFInvoiceRender', ['order_invoice_list' => $invoice]);
                        $pdf = new PDF($invoice, PDF::TEMPLATE_INVOICE, $context->smarty);
                        $file_attachement['invoice']['content'] = $pdf->render(false);
                        $file_attachement['invoice']['name'] = Configuration::get('PS_INVOICE_PREFIX', (int) $order->id_lang, null, $order->id_shop) . sprintf('%06d', $order->invoice_number) . '.pdf';
                        $file_attachement['invoice']['mime'] = 'application/pdf';
                    }
                    if ($result['pdf_delivery'] && $order->delivery_number) {
                        $pdf = new PDF($invoice, PDF::TEMPLATE_DELIVERY_SLIP, $context->smarty);
                        $file_attachement['delivery']['content'] = $pdf->render(false);
                        $file_attachement['delivery']['name'] = Configuration::get('PS_DELIVERY_PREFIX', Context::getContext()->language->id, null, $order->id_shop) . sprintf('%06d', $order->delivery_number) . '.pdf';
                        $file_attachement['delivery']['mime'] = 'application/pdf';
                    }
                } else {
                    $file_attachement = null;
                }

                if (!Mail::Send(
                    (int) $order->id_lang,
                    $result['template'],
                    $topic,
                    $data,
                    $result['email'],
                    $result['firstname'] . ' ' . $result['lastname'],
                    null,
                    null,
                    $file_attachement,
                    null,
                    _PS_MAIL_DIR_,
                    false,
                    (int) $order->id_shop
                )) {
                    return false;
                }
            }

            ShopUrl::resetMainDomainCache();
        }

        return true;
    }
}
