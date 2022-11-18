<?php
/**
 * cs_recaptcha_v2 front-end module version 1.0.0 for Prestashop 1.6, 1.7
 * Support contact : prestashop@comonsoft.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is the property of Com'onSoft
 * that is bundled with this package.
 * It is also available through the world-wide-web at this URL:
 * https://boutique.comonsoft.com/
 *
 * @category  front-end
 * @package   cs_recaptcha_v2
 * @author    Com'onSoft (http://www.comonsoft.com/)
 * @copyright 2016-2018 Com'onSoft and contributors
 * @version   1.0.0
 */
class ContactController extends ContactControllerCore
{
    /**
    * Start forms process
    * @see FrontController::postProcess()
    */
    /*
    * module: cs_recaptcha_v2
    * date: 2020-10-20 17:23:42
    * version: 1.0.0
    */
    public function postProcess()
    {
        if (Tools::isSubmit('submitMessage')) {
            if(!$this->context->customer->id){
                $data = array(
                    'secret' => Tools::getValue('RECAPTCHA_PRIVATE_KEY', Configuration::get('RECAPTCHA_PRIVATE_KEY')),
                    'response' => $_POST['g-recaptcha-response']
                );
                $verify = curl_init();
                if(isset($verify) && $verify){
                    curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
                    curl_setopt($verify, CURLOPT_POST, true);
                    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
                    $response = @curl_exec($verify);
                    $decode = json_decode($response, true);
                    if (!$decode['success'] == true) {
                        $this->errors[] = Tools::displayError('Formulaire invalide.');
                    }else{
                        parent::postProcess();
                    }
                }else{
                    $this->errors[] = Tools::displayError('Erreur de traitement');
                }
            }else{
                parent::postProcess();
            }
        }
    }
}
