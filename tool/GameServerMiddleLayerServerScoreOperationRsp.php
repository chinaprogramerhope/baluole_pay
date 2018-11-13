<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/13
 * Time: 11:47
 */

class GameServerMiddleLayerServerScoreOperationRsp extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;

    public function __construct($reader = null) {
        parent::__construct($reader);
        $this->fields["1"] = "PBInt";
        $this->values["1"] = "";
    }

    function returncode() {
        return $this->_get_value("1");
    }

    function set_returncode($value) {
        return $this->_set_value("1", $value);
    }
}