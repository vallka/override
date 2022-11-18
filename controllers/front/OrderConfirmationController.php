<?php
/**
 * Prestaworks AB
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://license.prestaworks.se/license.html
 *
 * @author    Prestaworks AB <info@prestaworks.se>
 * @copyright Copyright Prestaworks AB (https://www.prestaworks.se/)
 * @license   http://license.prestaworks.se/license.html
 */
class OrderConfirmationController extends OrderConfirmationControllerCore
{
    /**
    * Initialize order confirmation controller
    * @see FrontController::init()
    */
    /*
    * module: klarnaofficial
    * date: 2021-10-21 16:56:50
    * version: 2.2.14-v-1
    */
    public function init()
    {
        $id_cart = (int)(Tools::getValue('id_cart', 0));
        $id_order = Order::getOrderByCartId((int)($id_cart));
        $secure_key = Tools::getValue('key', false);
        $order = new Order((int)($id_order));
        if ($order->module=='klarnaofficial') {
            $customer = new Customer((int) $order->id_customer);
            if ($customer->secure_key == $secure_key) {
                $this->context->customer = $customer;
            }
        }
        parent::init();
    }
}
