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
class UserPermissionModel {
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
    public function create($member, $permission, $value = NULL) {
        if($value == NULL) {
            // Option par défaut
            $model = new PermissionModel($permission);
            $value = $model->getDefaultPermission($member);
        }
        $query = "INSERT INTO user_permission(id_membre, permission_name, value)"
                . " VALUES(:member, :permission, :value)";
        DB::update($query, array(
            ':member'       =>  $member,
            ':permission'   =>  $permission,
            ':value'        =>  $value
        ));
        return DB::lastInsertId();
    }
    public function setDefaultPermissions($memberid) {
        $model = new PermissionModel();
        $permissions = $model->getAllPermissions();
        foreach($permissions as $permission) 
        {
            $member_model = new MemberModel($memberid);
            $member = $member_model->get();
            
            if($member['hierarchie'] == MemberModel::MEMBER)
                $value = $permission['member_default'];
            elseif($member['hierarchie'] == MemberModel::MODERATOR)
                $value = $permission['moderator_default'];
            elseif($member['hierarchie'] == MemberModel::ADMIN)
                $value = $permission['admin_default'];
            $query = "INSERT INTO user_permission(id_membre, permission_name, value)
                    VALUES(:member, :permission, :value)
            ON DUPLICATE KEY UPDATE value=:value";
            DB::update($query, array(
                ':member'       =>  $memberid,
                ':permission'   =>  $permission['permission_name'],
                ':value'        =>  $value
            ));
        }
    }
    public function getPermissionData($permission) {
        $query = "SELECT * FROM permission WHERE permission_name = :perm";
        $res = DB::query($query, array(':perm'=>$permission));
        return $res[0];
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM user_permission WHERE id_up = :id";
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
            $query = 'UPDATE user_permission SET ';
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
            $query .= ' WHERE id_up = :id';
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
            $query = "DELETE FROM user_permission WHERE id_up = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM user_permission WHERE ";
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
    public function getUserPermissionByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM user_permission WHERE ";
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
        $query .= ' ORDER BY id_up DESC';
        return DB::query($query, $vals);
    }
    
    /**
     * 
     * @param type $member Membre concerné
     * @param type $permission Permission (table permission)
     * @return type
     */
    public static function can($member, $permission) {
        $query = "SELECT 1 FROM user_permission WHERE id_membre = :id AND permission_name = :permission AND value = 1";
        $result = DB::query($query, array(':id'=>$member, ':permission' => $permission));
        return (!empty($result));
    }
}
