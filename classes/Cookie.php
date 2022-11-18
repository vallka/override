<?php
class Cookie extends CookieCore
{
    /**
     * Encrypt and set the Cookie.
     *
     * @param string|null $cookie Cookie content
     *
     * @return bool Indicates whether the Cookie was successfully set
     *
     * @since 1.7.0
     */
    protected function encryptAndSetCookie($cookie = null)
    {
        //PrestaShopLogger::addLog("encryptAndSetCookie:UA:".$_SERVER['HTTP_USER_AGENT'], 3, NULL, "Cookie", 1);

        // Check if the content fits in the Cookie
        $length = (ini_get('mbstring.func_overload') & 2) ? mb_strlen($cookie, ini_get('default_charset')) : strlen($cookie);
        if ($length >= 1048576) {
            return false;
        }
        if ($cookie) {
            $content = $this->cipherTool->encrypt($cookie);
            $time = $this->_expire;
        } else {
            $content = 0;
            $time = 1;
        }

        /*
         * The alternative signature supporting an options array is only available since
         * PHP 7.3.0, before there is no support for SameSite attribute.
         */
        if (PHP_VERSION_ID < 70300) {
            //PrestaShopLogger::addLog("encryptAndSetCookie:PHP_VERSION_ID < 70300:".PHP_VERSION_ID, 3, NULL, "Cookie", 1);
            return setcookie(
                $this->_name,
                $content,
                $time,
                $this->_path,
                $this->_domain . '; SameSite=' . $this->_sameSite,
                $this->_secure,
                true
            );
        }


        $options = [
            'expires' => $time,
            'path' => $this->_path,
            'domain' => $this->_domain,
            'secure' => $this->_secure,
            'httponly' => true,
        ];

        // Safari misbehaves when SameSite is set (presumably when set to ANY value)
        // Safari = 'safari' is present in UA and 'chrome' or 'chromium' - not
        if ((stripos($_SERVER['HTTP_USER_AGENT'],'Chrome')!==false) or (stripos($_SERVER['HTTP_USER_AGENT'],'Chromium')!==false)
                or (stripos($_SERVER['HTTP_USER_AGENT'],'Safari')===false)
        ) {
            $options['samesite'] = $this->_sameSite;
        }

        return setcookie(
            $this->_name,
            $content,
            $options
        );
    }
}
