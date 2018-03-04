<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PersonneModel
 *
 * @author videony
 */
class MemberModel {
    
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    // Hiérarchie
    const MEMBER = 1;
    const MODERATOR = 2;
    const ADMIN = 3;
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($personne, $plateforme, $hierarchie = self::MEMBER, $actif = 1) {
        $query = "INSERT INTO membre(id_plateforme, id_personne, hierarchie, actif)"
                . " VALUES(:id_plateforme, :id_personne, :hierarchie, :actif)";
        DB::update($query, array(
            ':id_plateforme'          =>  $plateforme,
            ':id_personne'        =>  $personne,
            ':hierarchie'      =>  $hierarchie,
            ':actif'   =>  $actif
        ));
        $memberid = DB::lastInsertId();
        // Attribution de la plateforme par défaut s'il n'en a pas
        $personModel = new PersonneModel($personne);
        $personne = $personModel->get();
        if($personne['int_categorie'] == PageController::MEMBER_NO_DEFAULT_PLATFORM)
        {
            // Si pas de plateforme par défaut, on lui attribue cette plateforme
            $personModel->set(array(
                'id_plateforme' => $plateforme,
                'int_categorie' => PageController::MEMBER_DEFAULT_PLATFORM
            ));
            if($_SESSION[SessionController::ID_PERSONNE] == $personne)
            {
                $_SESSION[SessionController::CATEGORIE] = PageController::MEMBER_DEFAULT_PLATFORM;
            }
        }
        $permissionModel = new UserPermissionModel();
        $permissionModel->setDefaultPermissions($memberid);
        $settingModel = new UserSettingModel();
        $settingModel->setDefaultSettings($memberid);
        return $memberid;
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM membre WHERE id_membre = :id";
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
            $query = 'UPDATE membre SET ';
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
            $query .= ' WHERE id_membre = :id';
            $vals[':id'] = $this->pid;
            $return = DB::update($query, $vals);
            if($return == FALSE)
                return FALSE;
            if(in_array('actif', array_keys($modifications)))
            {
                // Si on le bloque, il faut résoudre sa plateforme par défaut
                $data = $this->get();
                if($modifications['actif'] == 0)
                    $this->resolve($data['id_personne'], $data['id_plateforme']);
                else
                    $this->activatePlatform($data['id_personne'], $data['id_plateforme']);
            }
            if(in_array('hierarchie', array_keys($modifications)))
            {
                // Si il change de hierarchie, il faut changer les autorisations
                $permissionModel = new UserPermissionModel();
                $permissionModel->setDefaultPermissions($this->pid);
                return TRUE;
            }
            else
                return TRUE;
        }
    }
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            if($this->canQuit())
            {
                $member = $this->get();
                $query = "DELETE FROM membre WHERE id_membre = :id";
                $return = DB::update($query, array(':id'=>$this->pid));
                $this->resolve($member['id_personne'], $member['id_plateforme']);
                $this->propagate($this->pid);
                return $return;
            }
            else
                return FALSE;
        }
    }
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $return = TRUE;
        $members = $this->getMembersByFilter($filters, $mode, $equality);
        foreach($members as $member)
        {
            $delete = TRUE;
            if($member['hierarchie'] == self::ADMIN)
            {
                $members = $this->getMembersByFilter(array(
                    'id_plateforme' =>  $member['id_plateforme'],
                    'hierarchie'    =>  self::ADMIN
                ));
                if(count($members) == 1)
                    $delete = FALSE;
            }
            if($delete == TRUE)
            {
                $query = "DELETE FROM membre WHERE id_membre = :id";
                $return = ($return && DB::update($query, array(':id'=>$member['id_membre'])));
                $this->resolve($member['id_personne'], $member['id_plateforme']);
                $this->propagate($member['id_membre']);
            }
            else
            {
                $return = FALSE;
            }
        }
        return $return;
    }
    public function getMembersByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM membre WHERE ";
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
        $query .= ' ORDER BY hierarchie, id_membre DESC';
        return DB::query($query, $vals);
    }
    public function getAllMembers()
    {
        $query = 'SELECT * FROM membre ORDER BY id_membre DESC';
        return DB::query($query, array());
    }
    /**
     * 
     */
    public function canQuit() {
        // Test if only admin
       if($this->pid == NULL)
            return FALSE;
        else
        {
            $member = $this->get();
            if($member['hierarchie'] != self::ADMIN)
                return TRUE;
            else
            {
                $query = "SELECT COUNT(1) FROM membre WHERE id_membre <> :id AND hierarchie = :hierarchie";
				$result = DB::query($query, array(':id'=>$this->pid, ':hierarchie' => self::ADMIN));
                return (!empty($result));
                
            }
        }
    }
    
    public function incrementNotifCount($idmembre){
        $query = "UPDATE membre SET compteurNotif = compteurNotif + 1 WHERE id_membre = :idmembre";
        $result = DB::update($query, array(':idmembre'=>$idmembre));
        return (!empty($result));
    }
    
    /**
     * Vrai si $member1 a une hiérarchie plus élévée que $member2
     * @param type $member1
     * @param type $member2
     * @return type
     */
    public static function hasHigherHierarchy($member1, $member2) {
        $model = new MemberModel($member1);
        $data1 = $model->get();
        $model = new MemberModel($member2);
        $data2 = $model->get();
        return ($data1['hierarchie'] >= $data2['hierarchie']);
    }
    
    /**
     * Resoud les problèmes de platformes par défaut etc 
     */
    private function resolve($personID, $platformID) {
        $pModel = new PersonneModel($personID);
        $person = $pModel->get();
        $membresModel = new MemberModel();
        $memberships = $membresModel->getMembersByFilter(array(
           'id_personne'  =>  $personID ,
            'actif'         =>  1
        ));
        // 1. Si seule plateforme
        //      - changement du type d'utilisateur
        //      - session: categorie, id_platform, platform_name
        if(empty($memberships))
        {
            $pModel->set(array(
               'int_categorie'     => PageController::MEMBER_NO_DEFAULT_PLATFORM,
               'id_plateforme'     => NULL
            ));
            if($personID == $_SESSION[SessionController::ID_PERSONNE])
            {
                $_SESSION[SessionController::CATEGORIE] = PageController::MEMBER_NO_DEFAULT_PLATFORM;
                unset($_SESSION[SessionController::PLATFORM_ID]);
                unset($_SESSION[SessionController::PLATFORM_NAME]);
            }
        }
        else
        {
            // 2. Si plateforme par défaut vient d'être supprimée
            //      - plateforme au hasard, changement de la plateforme par défaut.
            if($person['id_plateforme'] == $platformID)
            {
                $platform = $this->getOnePlatform($personID);
                $pModel->set(array(
                    'id_plateforme'     => $platform
                 ));
            }
            // 3. Si plateforme active au moment de la suppression
            //      - session: platform_id, platform_name
            if($_SESSION[SessionController::PLATFORM_ID] == $platformID)
            {
                // Actualiser les infos
                $person = $pModel->get();
                $sess = new SessionController(SessionController::JUST_INSTANTIATE);
                $sess->changePlatform($person['id_plateforme']);
            }
        }
    }
    private function activatePlatform($personID, $platformID)
    {
        $model = new PersonneModel($personID);
        $data = $model->get();
        if($data['int_categorie'] == PageController::MEMBER_NO_DEFAULT_PLATFORM)
        {
            // Il faut lui assigner cette plateforme comme plateforme par défaut
            $model->set(array(
               'int_categorie'     => PageController::MEMBER_DEFAULT_PLATFORM,
               'id_plateforme'     => $platformID
            ));
        }
        // Sinon, on garde la config d'avant
    }
    private function propagate($memberid)
    {
        // Permissions
        $permissionModel = new UserPermissionModel();
        $permissionModel->del(array(
           'id_membre'  =>  $memberid 
        ));
        
        // Settings
        $settingsModel = new UserSettingModel();
        $settingsModel->del(array(
           'id_membre'  =>  $memberid 
        ));
    }
    private function getOnePlatform($personID) {
        $membresModel = new MemberModel();
        $memberships = $membresModel->getMembersByFilter(array(
           'id_personne'  =>  $personID 
        ));
        return $memberships[0]['id_plateforme'];
    }
    
    
}
