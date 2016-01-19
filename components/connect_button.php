<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 17/12/2015
 * Time: 10.59
 */

class OPENIDCONNECT_CMP_ConnectButton extends OW_Component
{

    public function render() {
        //$cssUrl = OW::getPluginManager()->getPlugin('openidconnect')->getStaticCssUrl() . 'fbconnect.css';
        //OW::getDocument()->addStyleSheet($cssUrl);

        //FBCONNECT_BOL_Service::getInstance()->initializeJs(array('email', 'user_about_me', 'user_birthday'), $_GET);

        return parent::render();
    }

}