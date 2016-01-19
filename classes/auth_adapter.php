<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 17/12/2015
 * Time: 12.00
 */

class OPENIDCONNECT_CLASS_AuthAdapter extends OW_RemoteAuthAdapter
{

    public function __construct( $remoteId )
    {
        parent::__construct($remoteId, 'openid');
    }

}//EndClass.