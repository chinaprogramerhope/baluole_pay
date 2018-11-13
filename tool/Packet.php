<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/13
 * Time: 11:53
 */

class Packet extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;

    public function __construct($reader = null) {
        parent::__construct($reader);
        $this->fields["1"] = "PBInt";
        $this->values["1"] = "";
        $this->fields["2"] = "PBInt";
        $this->values["2"] = "";
        $this->fields["3"] = "PBString";
        $this->values["3"] = "";
        $this->fields["4"] = "PBInt";
        $this->values["4"] = "";
        $this->fields["5"] = "PBInt";
        $this->values["5"] = "";
        $this->fields["6"] = "PBInt";
        $this->values["6"] = "";
        $this->fields["7"] = "PBInt";
        $this->values["7"] = "";
        $this->fields["8"] = "PBInt";
        $this->values["8"] = "";
    }

    function version() {
        return $this->_get_value("1");
    }

    function set_version($value) {
        return $this->_set_value("1", $value);
    }

    function command() {
        return $this->_get_value("2");
    }

    function set_command($value) {
        return $this->_set_value("2", $value);
    }

    function serialized() {
        return $this->_get_value("3");
    }

    function set_serialized($value) {
        return $this->_set_value("3", $value);
    }

    function connectionid() {
        return $this->_get_value("4");
    }

    function set_connectionid($value) {
        return $this->_set_value("4", $value);
    }

    function gameserverconnectionid() {
        return $this->_get_value("5");
    }

    function set_gameserverconnectionid($value) {
        return $this->_set_value("5", $value);
    }

    function targettype() {
        return $this->_get_value("6");
    }

    function set_targettype($value) {
        return $this->_set_value("6", $value);
    }

    function userID() {
        return $this->_get_value("7");
    }

    function set_userID($value) {
        return $this->_set_value("7", $value);
    }

    function selftype() {
        return $this->_get_value("8");
    }

    function set_selftype($value) {
        return $this->_set_value("8", $value);
    }
}