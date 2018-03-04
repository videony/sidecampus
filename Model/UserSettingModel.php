<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ###CLASS###Model
 *
 * @author videony
 */
class UserSettingModel {
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($member, $setting, $value = NULL) {
        if($value == NULL) {
            // Option par défaut
            $model = new SettingModel($setting);
            $value = $model->getDefaultSetting($member);
        }
        $query = "INSERT INTO user_setting(id_membre, setting_name, value)"
                . " VALUES(:member, :setting, :value)";
        DB::update($query, array(
            ':member'       =>  $member,
            ':setting'   =>  $setting,
            ':value'        =>  $value
        ));
        return DB::lastInsertId();
    }
    public function setDefaultSettings($memberid) {
        
        $model = new SettingModel();
        $settings = $model->getAllSettings();
        $member_model = new MemberModel($memberid);
            $member = $member_model->get();
        foreach($settings as $setting) 
        {
            if($member['hierarchie'] == MemberModel::MEMBER)
                $value = $setting['member_default'];
            elseif($member['hierarchie'] == MemberModel::MODERATOR)
                $value = $setting['moderator_default'];
            elseif($member['hierarchie'] == MemberModel::ADMIN)
                $value = $setting['admin_default'];
            $query = "INSERT INTO user_setting(id_membre, setting_name, value)
                    VALUES(:member, :setting, :value)
            ON DUPLICATE KEY UPDATE value=:value";
            
            DB::update($query, array(
                ':member'       =>  $memberid,
                ':setting'   =>  $setting['setting_name'],
                ':value'        =>  $value
                ));
        }
        return TRUE;     
    }
    public function getSettingData($setting) {
        $query = "SELECT * FROM setting WHERE setting_name = :perm";
        $res = DB::query($query, array(':perm'=>$setting));
        return $res[0];
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM user_setting WHERE id_us = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
            if(!empty($res))
                return $res[0];
        }
    }
    public function set($modifications) {
        if($this->pid == NULL)
            return FALSE;
        else
        {
            $query = 'UPDATE user_setting SET ';
            $first = TRUE;
            $vals = array();
            foreach($modifications as $col=>$val)
            {
                if($first === FALSE)
                    $query .= ', ';
                $query .= $col.' = :'.strtolower($col);
                $vals[':'.strtolower($col)] = $val;
                if($first === TRUE)
                    $first = FALSE;
            }
            $query .= ' WHERE id_us = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    
    
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "DELETE FROM user_setting WHERE id_us = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM user_setting WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            $query .= $col.' '.$equality.' :'.strtolower($col);
            $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        return DB::update($query, $vals);
    }
    public function getUserSettingByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM user_setting WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            $query .= $col.' '.$equality.' :'.strtolower($col);
            $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        $query .= ' ORDER BY id_us DESC';
        return DB::query($query, $vals);
    }
    
    /**
     * 
     * @param type $member Membre concerné
     * @param type $setting Setting (table setting)
     * @return type
     */
    public static function abonne($member, $setting) {
        $query = "SELECT 1 FROM user_setting WHERE id_membre = :id AND setting_name = :setting AND value = 1";
        $result = DB::query($query, array(':id'=>$member, ':setting' => $setting));
        return (!empty($result));
    }
}
