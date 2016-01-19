<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 15/12/2015
 * Time: 15.25
 */

//Get the reference to this plug-in.
$plugin = OW::getPluginManager()->getPlugin('openidconnect');

$pathLangZip = $plugin->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($pathLangZip, 'openidconnect');

//This is the prefix of all tables in Oxwall.
$dbPrefix = OW_DB_PREFIX;
