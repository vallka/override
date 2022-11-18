<?php
/**
 * 2015 Prestaworks AB.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@prestaworks.se so we can send you a copy immediately.
 *
 *  @author    Prestaworks AB <info@prestaworks.se>
 *  @copyright 2015 Prestaworks AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Prestaworks AB
 */
class Tools extends ToolsCore
{
    /*
    * module: klarnaofficial
    * date: 2021-10-21 16:56:48
    * version: 2.2.14-v-1
    */
    public static function switchLanguage(Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $old_id_lang = $context->cookie->id_lang;
        if (($iso = Tools::getValue('isolang')) &&
                Validate::isLanguageIsoCode($iso) &&
                ($new_id_lang = (int)Language::getIdByIso($iso))
            ) {
            if ($new_id_lang != $old_id_lang) {
                if (version_compare(phpversion(), '5.4.0', '>=')) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                } else {
                    if (session_id() === '') {
                        session_start();
                    }
                }
                if (isset($_SESSION['klarna_checkout'])) {
                    unset($_SESSION['klarna_checkout']);
                }
                if (isset($_SESSION['klarna_checkout_uk'])) {
                    unset($_SESSION['klarna_checkout_uk']);
                }
            }
        }
        return parent::switchLanguage($context);
    }
    
    /*
    * module: klarnaofficial
    * date: 2021-10-21 16:56:48
    * version: 2.2.14-v-1
    */
    public static function setCurrency($cookie)
    {
        if (Tools::getIsset('SubmitCurrency')) {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
            } else {
                if (session_id() === '') {
                    session_start();
                }
            }
            if (isset($_SESSION['klarna_checkout'])) {
                unset($_SESSION['klarna_checkout']);
            }
            if (isset($_SESSION['klarna_checkout_uk'])) {
                unset($_SESSION['klarna_checkout_uk']);
            }
        }
        return parent::setCurrency($cookie);
    }
}
