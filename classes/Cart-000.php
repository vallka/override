<?php
class Cart extends CartCore
{
    /*
    * module: quantitydiscountpro
    * date: 2021-04-04 22:05:51
    * version: 2.1.36
    */
    public function addCartRule($id_cart_rule, bool $useOrderPrices = false)
    {
        $result = parent::addCartRule($id_cart_rule, $useOrderPrices);
        if (Module::isEnabled('quantitydiscountpro')) {
            include_once(_PS_MODULE_DIR_.'quantitydiscountpro/quantitydiscountpro.php');
            $quantityDiscountRulesAtCart = QuantityDiscountRule::getQuantityDiscountRulesAtCart((int)Context::getContext()->cart->id);
            if (is_array($quantityDiscountRulesAtCart) && count($quantityDiscountRulesAtCart)) {
                foreach ($quantityDiscountRulesAtCart as $quantityDiscountRuleAtCart) {
                    $quantityDiscountRuleAtCartObj = new QuantityDiscountRule((int)$quantityDiscountRuleAtCart['id_quantity_discount_rule']);
                    if (!$quantityDiscountRuleAtCartObj->compatibleCartRules()) {
                        QuantityDiscountRule::removeQuantityDiscountCartRule($quantityDiscountRuleAtCart['id_cart_rule'], (int)Context::getContext()->cart->id);
                    }
                }
            }
        }
        return $result;
    }
    /*
    * module: quantitydiscountpro
    * date: 2021-04-04 22:05:51
    * version: 2.1.36
    */
    public function getCartRules($filter = CartRule::FILTER_ACTION_ALL, $autoAdd = true, $useOrderPrices = false)
    {
        $cartRules = parent::getCartRules($filter, $autoAdd, $useOrderPrices);
        if (Module::isEnabled('quantitydiscountpro')) {
            include_once(_PS_MODULE_DIR_.'quantitydiscountpro/quantitydiscountpro.php');
            foreach ($cartRules as &$cartRule) {
                if (QuantityDiscountRule::isQuantityDiscountRule($cartRule['id_cart_rule'])
                    && !QuantityDiscountRule::isQuantityDiscountRuleWithCode($cartRule['id_cart_rule'])) {
                    $cartRule['code'] = '';
                }
            }
            unset($cartRule);
        }
        return $cartRules;
    }
    /*
    * module: klarnaofficial
    * date: 2021-10-21 16:56:48
    * version: 2.2.14-v-1
    */
    public function getDeliveryOption($default_country = null, $dontAutoSelectOptions = false, $use_cache = false)
    {
        return parent::getDeliveryOption($default_country, $dontAutoSelectOptions, $use_cache);
    }
}
