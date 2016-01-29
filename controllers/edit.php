<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 26/01/2016
 * Time: 16.24
 */

class OPENIDCONNECT_CTRL_Edit extends BASE_CTRL_Edit {

    public function index($params) {
        parent::index($params);
        $this->removeComponent("changePassword");
        $this->removeComponent("editForm");
    }

}