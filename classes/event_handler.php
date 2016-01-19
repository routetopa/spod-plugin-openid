<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 15/12/2015
 * Time: 16.27
 */

class OPENIDCONNECT_CLASS_EventHandler
{
    public function collectAuthLinkItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        $urlAuthPage = "/openid-connect/login";

        //It replaces the Sign in link with the custom link.
        $a = $event->getData()[0];
        print_r($a);
        $item = $a['item'];

        if ($item instanceof BASE_CMP_ConsoleItem) {
            $item->setControl('<a href="' . $urlAuthPage . '">Log in</a>');

            $unbindClick = "$('#".$item->getUniqId()."').unbind('click');";
            OW::getDocument()->addOnloadScript($unbindClick);
        }
    }//EndFunction.

    public function init() {
        //It binds to the class which shows the Sign in/Sign up links in Oxwall.
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectAuthLinkItems'));
    }//EndFunction.

}//EndClass.