<?php
class OrderCarrier extends OrderCarrierCore
{
    /**
     * @param Order $order Required
     *
     * @return bool
     */
    public function sendInTransitEmail($order)
    {
        return true;

        $customer = new Customer((int) $order->id_customer);
        $carrier = new Carrier((int) $order->id_carrier, $order->id_lang);
        $address = new Address((int) $order->id_address_delivery);

        if (!Validate::isLoadedObject($customer)) {
            throw new PrestaShopException('Can\'t load Customer object');
        }
        if (!Validate::isLoadedObject($carrier)) {
            throw new PrestaShopException('Can\'t load Carrier object');
        }
        if (!Validate::isLoadedObject($address)) {
            throw new PrestaShopException('Can\'t load Address object');
        }

        $products = $order->getCartProducts();
        $link = Context::getContext()->link;

        $metadata = '';
        foreach ($products as $product) {
            $prod_obj = new Product((int) $product['product_id']);

            //try to get the first image for the purchased combination
            $img = $prod_obj->getCombinationImages($order->id_lang);
            $link_rewrite = $prod_obj->link_rewrite[$order->id_lang];
            $combination_img = $img[$product['product_attribute_id']][0]['id_image'];
            if ($combination_img != null) {
                $img_url = $link->getImageLink($link_rewrite, $combination_img, 'large_default');
            } else {
                //if there is no combination image, then get the product cover instead
                $img = $prod_obj->getCover($prod_obj->id);
                $img_url = $link->getImageLink($link_rewrite, $img['id_image']);
            }
            $prod_url = $prod_obj->getLink();

            $metadata .= "\n" . '<div itemprop="itemShipped" itemscope itemtype="http://schema.org/Product">';
            $metadata .= "\n" . '   <meta itemprop="name" content="' . htmlspecialchars($product['product_name']) . '"/>';
            $metadata .= "\n" . '   <link itemprop="image" href="' . $img_url . '"/>';
            $metadata .= "\n" . '   <link itemprop="url" href="' . $prod_url . '"/>';
            $metadata .= "\n" . '</div>';
        }

        $orderLanguage = new Language((int) $order->id_lang);
        $templateVars = [
            '{followup}' => str_replace('@', $this->tracking_number, $carrier->url),
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{id_order}' => $order->id,
            '{shipping_number}' => $this->tracking_number,
            '{order_name}' => $order->getUniqReference(),
            '{carrier}' => $carrier->name,
            '{address1}' => $address->address1,
            '{country}' => $address->country,
            '{postcode}' => $address->postcode,
            '{city}' => $address->city,
            '{meta_products}' => $metadata,
        ];

        if (@Mail::Send(
            (int) $order->id_lang,
            'in_transit',
            $this->trans(
                'Package in transit',
                [],
                'Emails.Subject',
                $orderLanguage->locale
            ),
            $templateVars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MAIL_DIR_,
            false,
            (int) $order->id_shop
        )) {
            return true;
        } else {
            return false;
        }
    }
}
