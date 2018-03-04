<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PermissionModel
 *
 * @author videony
 */
class PermissionModel {
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    const SECTION_FILES = 'Fichiers';
    const SECTION_EVENTS = 'Calendrier';
    const SECTION_FORUM = 'Forum';
    const SECTION_PLATFORM_MANAGE = 'Gestion de la plateforme';
    const SECTION_DIVERS = 'Divers';
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($name, $tx_description, $section, $member_default, $moderator_default, $admin_default) {
        $query = "INSERT INTO permission(permission_name, tx_description, section, member_default, moderator_default, admin_default)"
                . " VALUES(:name, :tx_description, :section, :member_default, :moderator_default, :admin_default)";
        DB::update($query, array(
            ':name'         =>  $name,
            ':tx_description' => $tx_description, 
            ':section' => $section, 
            ':member_default' => $member_default, 
            ':moderator_default' => $moderator_default, 
            ':admin_default' => $admin_default
        ));
        return DB::lastInsertId();
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM permission WHERE permission_name = :id";
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
            $query = 'UPDATE permission SET ';
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
            $query .= ' WHERE permission_name = :id';
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
            $query = "DELETE FROM permission WHERE permission_name = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM permission WHERE ";
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
    public function getPermissionByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM permission WHERE ";
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
        $query .= ' ORDER BY permission_name DESC';
        return DB::query($query, $vals);
    }
    public function getDefaultPermission($member) {
        $perm_data = $this->get();
        $member_model = new MemberModel($member);
        $memberdata = $member_model->get();
        if($memberdata['hierarchie'] == MemberModel::MEMBER)
            return $perm_data['member_default'];
        elseif($memberdata['hierarchie'] == MemberModel::MODERATOR)
            return $perm_data['moderator_default'];
        elseif($memberdata['hierarchie'] == MemberModel::ADMIN)
            return $perm_data['admin_default'];
    }
    public function getAllPermissions()
    {
        $query = 'SELECT * FROM permission ORDER BY permission_name DESC';
        return DB::query($query, array());
    }
}
