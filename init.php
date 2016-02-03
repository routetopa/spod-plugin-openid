<?php

//Get the reference to this plug-in.
$plugin = OW::getPluginManager()->getPlugin('openidconnect');

//ROUTING: login for pages.
OW::getRouter()->addRoute(new OW_Route('openidconnect_login', 'openid-connect/login', 'OPENIDCONNECT_CTRL_Connect', 'login'));
OW::getRouter()->addRoute(new OW_Route('openidconnect_loginsuccess', 'openid-connect/loginSuccess', 'OPENIDCONNECT_CTRL_Connect', 'loginSuccess'));

//ROUTING: logout from SPOD.
//OW::getRouter()->addRoute(new OW_Route('openidconnect_login', 'openid-connect/sign-out', 'OPENIDCONNECT_CTRL_Connect', 'login'));
OW::getRouter()->removeRoute('base_sign_out');
OW::getRouter()->addRoute(new OW_Route('base_sign_out', 'sign-out', 'OPENIDCONNECT_CTRL_Connect', 'logout'));

//This route replaces the Oxwall existing ROUTE-TO-PA sign-in which loads when the user enters
//in a page that requires the log-in.
OW::getRouter()->removeRoute('static_sign_in');
OW::getRouter()->addRoute(new OW_Route('static_sign_in', 'sign-in', 'OPENIDCONNECT_CTRL_Connect', 'login'));

//This route replaces the original Oxwall ajax-form route.
//OW::getRouter()->removeRoute('ajax-form');
//OW::getRouter()->addRoute(new OW_Route('ajax-form', 'ajax-form', 'BASE_CTRL_AjaxForm', 'index'));
//OW::getRouter()->addRoute(new OW_Route('ajax-form', 'ajax-form', 'OPENIDCONNECT_CTRL_Connect', 'login'));

//Route change profile to disable the e-mail field and the change password.
OW::getRouter()->removeRoute('base_edit');
//OW::getRouter()->removeRoute('base_edit_user_datails');
OW::getRouter()->addRoute(new OW_Route('base_edit', 'profile/edit', 'OPENIDCONNECT_CTRL_Edit', 'index'));
//$router->addRoute(new OW_Route('base_edit', 'profile/edit', 'BASE_CTRL_Edit', 'index'));
//$router->addRoute(new OW_Route('base_edit_user_datails', 'profile/:userId/edit/', 'BASE_CTRL_Edit', 'index'));


//Registry.
$registry = OW::getRegistry();
$registry->addToArray(BASE_CTRL_Join::JOIN_CONNECT_HOOK, array(new OPENIDCONNECT_CMP_ConnectButton()), 'render');

//Administrator section.
OW::getRouter()->addRoute(new OW_Route('openidconnect_settings', 'openid-connect/settings', 'OPENIDCONNECT_CTRL_Admin', 'settings'));

$eventHandler = new OPENIDCONNECT_CLASS_EventHandler();
$eventHandler->init();
