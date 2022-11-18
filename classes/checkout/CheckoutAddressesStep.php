<?php
use Symfony\Component\Translation\TranslatorInterface;
class CheckoutAddressesStep extends CheckoutAddressesStepCore
{
    /*
    * module: quantitydiscountpro
    * date: 2021-04-04 22:05:51
    * version: 2.1.36
    */
    public function handleRequest(array $requestParams = array())
    {
        parent::handleRequest($requestParams);
        if (Module::isEnabled('quantitydiscountpro')) {
            include_once(_PS_MODULE_DIR_.'quantitydiscountpro/quantitydiscountpro.php');
            $quantityDiscount = new QuantityDiscountRule();
            $quantityDiscount->createAndRemoveRules();
        }
        return $this;
    }
}
