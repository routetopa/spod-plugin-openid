<?php

class OPENIDCONNECT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    private $KEY_PROVIDER_URL = 'openidconnect_provider_url';

    public function settings($params) {
        $this->setPageTitle(OW::getLanguage()->text('openidconnect', 'settings_title'));
        $this->setPageHeading(OW::getLanguage()->text('openidconnect', 'settings_heading'));

        $form = new Form('settings');
        $this->addForm($form);

        /* OPENIDPROVIDER URL */
        $txtProviderUrl = new TextField($this->KEY_PROVIDER_URL);

        echo $_SERVER['HTTP_HOST'];

        $preference = BOL_PreferenceService::getInstance()->findPreference($this->KEY_PROVIDER_URL);
        $openidconnect_provider_url = empty($preference) ? "http://spod.routetopa.eu/openid/server.php" : $preference->defaultValue;
        $txtProviderUrl->setValue($openidconnect_provider_url);
        $txtProviderUrl->setRequired();
        $form->addElement($txtProviderUrl);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('openidconnect', 'add_key_submit'));
        $form->addElement($submit);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();

            $preference = BOL_PreferenceService::getInstance()->findPreference($this->KEY_PROVIDER_URL);

            if (empty($preference))
                $preference = new BOL_Preference();

            $preference->key = $this->KEY_PROVIDER_URL;
            $preference->sectionName = 'general';
            $preference->defaultValue = $data[$this->KEY_PROVIDER_URL];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);
        }//EndIf.
    }//EndFunction.

}//EndClass.