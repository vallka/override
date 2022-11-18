<?php
class QuickAccess extends QuickAccessCore
{
    /**
     * Get all available quick_accesses with token.
     *
     * @return bool|array QuickAccesses
     */
    public static function getQuickAccessesWithToken($idLang, $idEmployee)
    {
        $quickAccess = self::getQuickAccesses($idLang);

        if (empty($quickAccess)) {
            return false;
        }

        foreach ($quickAccess as $index => $quick) {
            // any link staring with http(s) pass as is
            if (preg_match('/^https?:\/\//i',$quick['link'])) {
                continue;
            }

            // first, clean url to have a real quickLink
            $quick['link'] = Context::getContext()->link->getQuickLink($quick['link']);
            $tokenString = $idEmployee;


            if ('../' === $quick['link'] && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $url = Context::getContext()->shop->getBaseURL();
                if (!$url) {
                    unset($quickAccess[$index]);

                    continue;
                }
                $quickAccess[$index]['link'] = $url;
            } else {
                preg_match('/controller=(.+)(&.+)?$/', $quick['link'], $admin_tab);
                if (isset($admin_tab[1])) {
                    if (strpos($admin_tab[1], '&')) {
                        $admin_tab[1] = substr($admin_tab[1], 0, strpos($admin_tab[1], '&'));
                    }
                    $quick_access[$index]['target'] = $admin_tab[1];

                    $tokenString = $admin_tab[1] . (int) Tab::getIdFromClassName($admin_tab[1]) . $idEmployee;
                }
                $quickAccess[$index]['link'] = Context::getContext()->link->getBaseLink() . basename(_PS_ADMIN_DIR_) . '/' . $quick['link'];
            }

            if (false === strpos($quickAccess[$index]['link'], 'token')) {
                $separator = strpos($quickAccess[$index]['link'], '?') ? '&' : '?';
                $quickAccess[$index]['link'] .= $separator . 'token=' . Tools::getAdminToken($tokenString);
            }
        }

        return $quickAccess;
    }
}
