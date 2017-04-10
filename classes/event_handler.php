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
        $urlAuthPage = "openid-connect/login";

        //It replaces the Sign in link with the custom link.
        $a = $event->getData()[0];
        //print_r($a);
        $item = $a['item'];

        if ($item instanceof BASE_CMP_ConsoleItem) {
//            $item->setControl('<a href="' . $urlAuthPage . '">Log in</a>');
            $login = OW::getLanguage()->text('openidconnect', 'login');
            $item->setControl('<a href="' . $urlAuthPage . '"><span class="ow_signin_label">' . $login . '</span></a>');

            $unbindClick = "$('#".$item->getUniqId()."').unbind('click');";
            OW::getDocument()->addOnloadScript($unbindClick);

            $item->setIsHidden(true);
        }
    }//EndFunction.

    public function onUserRegister( $event )
    {
        if ( $event->getParams()['method'] == 'facebook' ) {
            $logger = OW::getLogger('openidconnect.newfbuser');
            $logger->addEntry(print_r($event, true));
            $logger->writeLog();

            // Get user details
            $userService = BOL_UserService::getInstance();
            $user = $userService->findUserById($event->getParams()['userId']);
            $userUrl = $userService->getUserUrlForUsername($user->getUsername());
            $displayName = $userService->getDisplayName($user->getId());

            // Retrieve language strings
            $language = OW::getLanguage();
            $subject = $language->text('openidconnect', 'new_facebook_user_email_subject');
            $template_html = $language->text('openidconnect', 'new_facebook_user_email_template_html');
            $template_text = $language->text('openidconnect', 'new_facebook_user_email_template_text');

            $vars = array(
               'user_url' => $userUrl,
               'display_name' => $displayName,
            );

            $subject = UTIL_String::replaceVars($subject, $vars);
            $template_html = UTIL_String::replaceVars($template_html, $vars);
            $template_text = UTIL_String::replaceVars($template_text, $vars);

            // Create a mail and sent it to admin
            $mail = OW::getMailer()->createMail();
            $mail->addRecipientEmail(  OW::getConfig()->getValue('base', 'site_email') );
            //$mail->setSender(  OW::getConfig()->getValue('base', 'site_email') );
            //$mail->setSenderSuffix(false);
            $mail->setSubject( $subject );
            $mail->setHtmlContent($template_html);
            $mail->setTextContent($template_text);


            if ($user) {

            } else {

            }


            try
            {
                OW::getMailer()->send($mail);
            }
            catch (Exception $e)
            {
                $logger = OW::getLogger('admin.send_password_message');
                $logger->addEntry($e->getMessage());
                $logger->writeLog();
            }
        }
    }

    public function genericInit() {
        // It is really important that 3rd parameter is set to a very high priority (i.e. a low value), or else
        // the event will be intercepted and cancelled before reaching this handler.
        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onUserRegister'), 1);
    }

    public function init() {
        $this->genericInit();
        //It binds to the class which shows the Sign in/Sign up links in Oxwall.
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectAuthLinkItems'));
    }//EndFunction.

}//EndClass.
