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
class Cart extends CartCore
{
    /**
     * Add a CartRule to the Cart.
     *
     * @param int $id_cart_rule CartRule ID
     * @param bool $useOrderPrices
     *
     * @return bool Whether the CartRule has been successfully added
     */
    /*
    * module: wkproductsubscription
    * date: 2022-11-18 14:11:55
    * version: 5.1.0
    */
    public function addCartRule($id_cart_rule, bool $useOrderPrices = false)
    {
        if (Module::isEnabled('wkproductsubscription')) {
            include_once _PS_MODULE_DIR_.'wkproductsubscription/classes/WkSubscriptionRequired.php';
            if (!empty(Context::getContext()->cart)) {
                $idCart = (int)Context::getContext()->cart->id;
                $isCartExist = WkSubscriptionCartProducts::checkIfCartRuleExists($id_cart_rule);
                if ($isCartExist
                    && ($id_cart_rule == $isCartExist['id_cart_rule'])
                    && ($isCartExist['id_cart'] != $idCart)
                ) {
                    return false;
                } else {
                    return parent::addCartRule($id_cart_rule, $useOrderPrices);
                }
            } else {
                return parent::addCartRule($id_cart_rule, $useOrderPrices);
            }
        } else {
            return parent::addCartRule($id_cart_rule, $useOrderPrices);
        }
    }
}
