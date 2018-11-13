<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/13
 * Time: 11:45
 */

class GameServerMiddleLayerServerScoreOperation extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;

    public function __construct($reader = null) {
        parent::__construct($reader);
        $this->fields["1"] = "PBInt";
        $this->values["1"] = "";
        $this->fields["2"] = "PBInt";
        $this->values["2"] = "";
        $this->fields["3"] = "PBInt";
        $this->values["3"] = "";
        $this->fields["4"] = "PBInt";
        $this->values["4"] = "";
        $this->fields["5"] = "EnumAddScoreType";
        $this->values["5"] = "";
        $this->fields["6"] = "PBString";
        $this->values["6"] = "";
        $this->fields["7"] = "PBInt";
        $this->values["7"] = "";
        $this->fields["8"] = "PBInt";
        $this->values["8"] = "";
        $this->fields["9"] = "PBInt";
        $this->values["9"] = "";
        $this->fields["10"] = "PBInt";
        $this->values["10"] = "";
        $this->fields["11"] = "PBInt";
        $this->values["11"] = "";
        $this->fields["12"] = "PBInt";
        $this->values["12"] = "";
    }

    function userid() {
        return $this->_get_value("1");
    }

    function set_userid($value) {
        return $this->_set_value("1", $value);
    }

    function score() {
        return $this->_get_value("2");
    }

    function set_score($value) {
        return $this->_set_value("2", $value);
    }

    function adwalltype() {
        return $this->_get_value("3");
    }

    function set_adwalltype($value) {
        return $this->_set_value("3", $value);
    }

    function gameCode() {
        return $this->_get_value("4");
    }

    function set_gameCode($value) {
        return $this->_set_value("4", $value);
    }

    function addtype() {
        return $this->_get_value("5");
    }

    function set_addtype($value) {
        $this->_set_value("5", $value);
//        return $this->_set_value("5", $value);
    }

    function ipAddress() {
        return $this->_get_value("6");
    }

    function set_ipAddress($value) {
        return $this->_set_value("6", $value);
    }

    function roomID() {
        return $this->_get_value("7");
    }

    function set_roomID($value) {
        return $this->_set_value("7", $value);
    }

    function tableID() {
        return $this->_get_value("8");
    }

    function set_tableID($value) {
        return $this->_set_value("8", $value);
    }

    function seatID() {
        return $this->_get_value("9");
    }

    function set_seatID($value) {
        return $this->_set_value("9", $value);
    }

    function baseScore() {
        return $this->_get_value("10");
    }

    function set_baseScore($value) {
        return $this->_set_value("10", $value);
    }

    function playCountToday() {
        return $this->_get_value("11");
    }

    function set_playCountToday($value) {
        return $this->_set_value("11", $value);
    }

    function continuousWinCountToday() {
        return $this->_get_value("12");
    }

    function set_continuousWinCountToday($value) {
        return $this->_set_value("12", $value);
    }
}