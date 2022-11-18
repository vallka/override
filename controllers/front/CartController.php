<?php
class CartController extends CartControllerCore
{
    
    /*
    * module: quantitydiscountpro
    * date: 2021-04-04 22:05:51
    * version: 2.1.36
    */
    protected function updateCart()
    {
        if (Module::isEnabled('quantitydiscountpro')) {
            include_once(_PS_MODULE_DIR_.'quantitydiscountpro/quantitydiscountpro.php');
            if ($this->context->cookie->exists() && !$this->errors && !($this->context->customer->isLogged() && !$this->isTokenValid())) {
                if (Tools::getIsset('add') || Tools::getIsset('update')) {
                    $this->processChangeProductInCart();
                } elseif (Tools::getIsset('delete')) {
                    $this->processDeleteProductInCart();
                } elseif (CartRule::isFeatureActive()) {
                    if (Tools::getIsset('addDiscount') || Tools::getIsset('searchcoupon')) {
                        if (!($code = trim((Tools::getValue('discount_name') ? Tools::getValue('discount_name') : Tools::getValue('coupon'))))) {
                            $this->errors[] = $this->trans('You must enter a voucher code.', array(), 'Shop.Notifications.Error');
                        } elseif (!Validate::isCleanHtml($code)) {
                            $this->errors[] = $this->trans('The voucher code is invalid.', array(), 'Shop.Notifications.Error');
                        } else {
                            $quantityDiscount = new QuantityDiscountRule();
                            if (($quantityDiscount = new quantityDiscountRule(QuantityDiscountRule::getQuantityDiscountRuleByCode($code))) && Validate::isLoadedObject($quantityDiscount)) {
                                if ($quantityDiscount->createAndRemoveRules($code) !== true) {
                                    $this->errors[] = $this->trans('The voucher code is invalid.', array(), 'Shop.Notifications.Error');
                                }
                            } elseif (($cartRule = new CartRule(CartRule::getIdByCode($code))) && Validate::isLoadedObject($cartRule)) {
                                if ($quantityDiscount->cartRuleGeneratedByAQuantityDiscountRuleCode($code)) {
                                    $this->errors[] = $this->trans('The voucher code is invalid.', array(), 'Shop.Notifications.Error');
                                } elseif ($error = $cartRule->checkValidity($this->context, false, true)) {
                                    $this->errors[] = $error;
                                } else {
                                    $this->context->cart->addCartRule($cartRule->id);
                                }
                            } else {
                                $this->errors[] = $this->trans('This voucher does not exist.', array(), 'Shop.Notifications.Error');
                            }
                        }
                    } elseif (($id_cart_rule = (int)Tools::getValue('deleteDiscount')) && Validate::isUnsignedId($id_cart_rule)) {
                        if (!QuantityDiscountRule::removeQuantityDiscountCartRule($id_cart_rule, (int)$this->context->cart->id)) {
                            $this->context->cart->removeCartRule($id_cart_rule);
                        }
                        CartRule::autoAddToCart($this->context);
                    }
                }
            } elseif (!$this->isTokenValid() && Tools::getValue('action') !== 'show' && !Tools::getValue('ajax')) {
                Tools::redirect('index.php');
            }
        } else {
            parent::updateCart();
        }
    }
    
    /*
    * module: klarnaofficial
    * date: 2021-10-21 16:56:50
    * version: 2.2.14-v-1
    */
    public function initContent()
    {
        parent::initContent();
    }
    
    /*
    * module: wkproductsubscription
    * date: 2022-11-18 14:11:57
    * version: 5.1.0
    */
    protected function areProductsAvailable()
    {
        $product = $this->context->cart->checkQuantities(true);
        if (Module::isEnabled('wkproductsubscription')) {
            $context = Context::getContext();
            include_once _PS_MODULE_DIR_.'wkproductsubscription/classes/WkSubscriptionRequired.php';
            if ($cartProducts = $context->cart->getProducts()) {
                $subProdCount = 0;
                $cartProdCount = 0;
                $hasSubProd = false;
                foreach ($cartProducts as $productData) {
                    $idProduct = $productData['id_product'];
                    $idAttr = $productData['id_product_attribute'];
                    $idCart = $context->cart->id;
                    if (WkProductSubscriptionModel::checkIfSubscriptionProduct($idProduct)
                        && WkSubscriptionCartProducts::getByIdProductByIdCart($idCart, $idProduct, $idAttr, true)
                    ) {
                        $subProdCount++;
                        $hasSubProd = true;
                    }
                    $cartProdCount++;
                }
                if (($subProdCount !== $cartProdCount) && $hasSubProd) {
                    return $this->trans(
                        'You can not purchase subscription product and normal product together.',
                        array(),
                        'Shop.Notifications.Error'
                    );
                } elseif (($subProdCount > 1) && $hasSubProd) {
                    return $this->trans(
                        'You can not purchase more than one subscription product at a time.',
                        array(),
                        'Shop.Notifications.Error'
                    );
                }
            }
        }
        if (true === $product || !is_array($product)) {
            return true;
        }
        if ($product['active']) {
            return $this->trans(
                'The item %product% in your cart is no longer available in this quantity. Please adjust the quantity.',
                ['%product%' => $product['name']],
                'Shop.Notifications.Error'
            );
        }
        return $this->trans(
            'This product (%product%) is no longer available.',
            ['%product%' => $product['name']],
            'Shop.Notifications.Error'
        );
    }
}
