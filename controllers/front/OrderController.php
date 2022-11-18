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
use PrestaShop\PrestaShop\Core\Foundation\Templating\RenderableProxy;
class OrderController extends OrderControllerCore
{
    /*
    * module: wkproductsubscription
    * date: 2022-11-18 14:11:57
    * version: 5.1.0
    */
    public function initContent()
    {
        if (Configuration::isCatalogMode()) {
            Tools::redirect('index.php');
        }
        $this->restorePersistedData($this->checkoutProcess);
        $this->checkoutProcess->handleRequest(
            Tools::getAllValues()
        );
        $presentedCart = $this->cart_presenter->present($this->context->cart, true);
        if (count($presentedCart['products']) <= 0 || $presentedCart['minimalPurchaseRequired']) {
            $cartLink = $this->context->link->getPageLink('cart');
            Tools::redirect($cartLink);
        }
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
                    $product = array();
                } elseif (($subProdCount > 1) && $hasSubProd) {
                    $product = array();
                }
            }
        }
        if (is_array($product)) {
            $cartLink = $this->context->link->getPageLink('cart', null, null, ['action' => 'show']);
            Tools::redirect($cartLink);
        }
        $this->checkoutProcess
            ->setNextStepReachable()
            ->markCurrentStep()
            ->invalidateAllStepsAfterCurrent();
        $this->saveDataToPersist($this->checkoutProcess);
        if (!$this->checkoutProcess->hasErrors()) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$this->ajax) {
                return $this->redirectWithNotifications(
                    $this->checkoutProcess->getCheckoutSession()->getCheckoutURL()
                );
            }
        }
        $this->context->smarty->assign([
            'checkout_process' => new RenderableProxy($this->checkoutProcess),
            'cart' => $presentedCart,
        ]);
        $this->context->smarty->assign([
            'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
        ]);
        parent::initContent();
        $this->setTemplate('checkout/checkout');
    }
}
