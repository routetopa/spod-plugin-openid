<?php

include 'keys.php';

class OPENIDCONNECT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function settings($params) {
        $this->setPageTitle(OW::getLanguage()->text('openidconnect', 'settings_title'));
        $this->setPageHeading(OW::getLanguage()->text('openidconnect', 'settings_heading'));

        $form = new Form('settings');
        $this->addForm($form);

        /* OPENIDPROVIDER LOGIN URL */
        $txtProviderUrl = new TextField(PREFERENCE_KEYS::$KEY_PROVIDER_LOGIN_URL);
        $preference = BOL_PreferenceService::getInstance()->findPreference(PREFERENCE_KEYS::$KEY_PROVIDER_LOGIN_URL);
        $openidconnect_provider_url = empty($preference) ? "http://spod.routetopa.eu/openid/server.php" : $preference->defaultValue;
        $txtProviderUrl->setValue($openidconnect_provider_url);
        $txtProviderUrl->setRequired();
        $form->addElement($txtProviderUrl);

        /* OPENIDPROVIDER LOGOUT URL */
        $txtLogoutUrl = new TextField(PREFERENCE_KEYS::$KEY_PROVIDER_LOGOUT_URL);
        $preference = BOL_PreferenceService::getInstance()->findPreference(PREFERENCE_KEYS::$KEY_PROVIDER_LOGOUT_URL);
        $openidconnect_logout_url = empty($preference) ? "/" : $preference->defaultValue;
        $txtLogoutUrl->setValue($openidconnect_logout_url);
        $txtLogoutUrl->setRequired();
        $form->addElement($txtLogoutUrl);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('openidconnect', 'add_key_submit'));
        $form->addElement($submit);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();

            $preference = BOL_PreferenceService::getInstance()->findPreference(PREFERENCE_KEYS::$KEY_PROVIDER_LOGIN_URL);
            if (empty($preference))
                $preference = new BOL_Preference();

            $preference->key = PREFERENCE_KEYS::$KEY_PROVIDER_LOGIN_URL;
            $preference->sectionName = 'general';
            $preference->defaultValue = $data[PREFERENCE_KEYS::$KEY_PROVIDER_LOGIN_URL];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);


            $preference = BOL_PreferenceService::getInstance()->findPreference(PREFERENCE_KEYS::$KEY_PROVIDER_LOGOUT_URL);
            if (empty($preference))
                $preference = new BOL_Preference();

            $preference->key = PREFERENCE_KEYS::$KEY_PROVIDER_LOGOUT_URL;
            $preference->sectionName = 'general';
            $preference->defaultValue = $data[PREFERENCE_KEYS::$KEY_PROVIDER_LOGOUT_URL];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);
            
        }//EndIf.
    }//EndFunction.

}//EndClass.