<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Hook extends HookCore
{
    /*
    * module: wkproductsubscription
    * date: 2022-11-18 14:11:56
    * version: 5.1.0
    */
    public static function getHookModuleExecList($hook_name = null)
    {
        if ($list = parent::getHookModuleExecList($hook_name)) {
            if (Module::isEnabled('wkproductsubscription')) {
                include_once _PS_MODULE_DIR_.'wkproductsubscription/classes/WkSubscriptionRequired.php';
                ###################### override content ####################################
                if ($hook_name == 'paymentOptions') {
                    if (!empty(Context::getContext()->cart)) {
                        $products = Context::getContext()->cart->getProducts();
                        $idCart = (int)Context::getContext()->cart->id;
                        $product_ids = array(); // get products in cart
                        if ($products) {
                            foreach ($products as $product) {
                                $idProduct = (int)$product['id_product'];
                                $idProductAttribute = (int)$product['id_product_attribute'];
                                $isCartExist = WkSubscriptionCartProducts::getByIdProductByIdCart(
                                    $idCart,
                                    $idProduct,
                                    $idProductAttribute,
                                    true
                                );
                                if ($isCartExist) {
                                    $product_ids[] = (int)$product['id_product'];
                                }
                            }
                        }
                        $allowedPayments = Tools::jsonDecode(Configuration::get('WK_SUBSCRIPTION_PAYMENT_METHODS'));
                        if (count($product_ids)) {
                            if (isset($allowedPayments) && $allowedPayments) {
                                if ($list) {
                                    foreach ($list as $key => $productPayment) {
                                        if (is_array($allowedPayments)) {
                                            if (!in_array($productPayment['id_module'], $allowedPayments)) {
                                                unset($list[$key]);
                                            }
                                        }
                                    }
                                }
                            } else {
                                foreach ($list as $key => $productPayment) {
                                    unset($list[$key]);
                                }
                            }
                        }
                    }
                }
            }
            return $list;
        }
    }
}
