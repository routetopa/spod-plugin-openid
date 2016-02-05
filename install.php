<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 15/12/2015
 * Time: 15.25
 */

$path = OW::getPluginManager()->getPlugin('openidconnect')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'openidconnect');

//This is the prefix of all tables in Oxwall.
$dbPrefix = OW_DB_PREFIX;
