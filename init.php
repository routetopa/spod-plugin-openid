<?php

//Get the reference to this plug-in.
$plugin = OW::getPluginManager()->getPlugin('openidconnect');

//ROUTING: login for pages.
OW::getRouter()->addRoute(new OW_Route('openidconnect_login', 'openid-connect/login', 'OPENIDCONNECT_CTRL_Connect', 'login'));
OW::getRouter()->addRoute(new OW_Route('openidconnect_loginsuccess', 'openid-connect/loginSuccess', 'OPENIDCONNECT_CTRL_Connect', 'loginSuccess'));

//ROUTING: logout from SPOD.
//OW::getRouter()->addRoute(new OW_Route('openidconnect_login', 'openid-connect/sign-out', 'OPENIDCONNECT_CTRL_Connect', 'login'));
//OW::getRouter()->addRoute(new OW_Route('base_sign_out', 'sign-out', 'OPENIDCONNECT_CTRL_Connect', 'logout'));

//Registry.
$registry = OW::getRegistry();
$registry->addToArray(BASE_CTRL_Join::JOIN_CONNECT_HOOK, array(new OPENIDCONNECT_CMP_ConnectButton()), 'render');

//Administrator section.
OW::getRouter()->addRoute(new OW_Route('openidconnect_settings', 'openid-connect/settings', 'OPENIDCONNECT_CTRL_Admin', 'settings'));

$eventHandler = new OPENIDCONNECT_CLASS_EventHandler();
$eventHandler->init();
