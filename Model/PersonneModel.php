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
class PersonneModel {
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
    public function create($nom, $prenom, $login, $pwd, $email, $gsm, $dn, $confirm_code, $categ = 0, $logincode, $pers = '') {
        $picdata = file_get_contents('media/pics/default_profile_pic.png');
        $query = "INSERT INTO personne(tx_nom, tx_prenom, tx_login, tx_mdp, tx_email, tx_gsm, "
                . "dt_naissance, confirm_code, int_categorie, tx_msg_perso, img_profile_pic, tx_profile_pic, tx_login_code)"
                . " VALUES(:nom, :prenom, :login, :pwd, :email, :gsm, :dn, :confirm, :categ, :pers, :img, :imgname, :logincode)";
        DB::update($query, array(
            ':nom'      =>  $nom,
            ':prenom'   =>  $prenom,
            ':login'    =>  $login,
            ':pwd'      =>  $pwd,
            ':email'    =>  $email,
            ':gsm'      =>  $gsm,
            ':dn'       =>  $dn,
            ':confirm'  =>  $confirm_code,
            ':categ'    =>  $categ,
            ':pers'     =>  $pers,
            ':img'      =>  $picdata,
            ':imgname'  =>  'default_profile_pic.png',
            ':logincode'    =>  $logincode
        ));
        return DB::lastInsertId();
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM personne WHERE id_pers = :id";
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
            $query = 'UPDATE personne SET ';
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
            $query .= ' WHERE id_pers = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    public function getByLogin($login) {
        $query = "SELECT * FROM personne WHERE tx_login = :login OR tx_email = :login";
        $result = DB::query($query, array(':login'=>$login));
        if(empty($result))
            return NULL;
        else
            return $result[0];
    }
    
     public function getByMail($email) {
        $query = "SELECT * FROM personne WHERE tx_email = :email";
        $result = DB::query($query, array(':email'=>$email));
        if(empty($result))
            return NULL;
        else
            return $result[0];
    }
    
    /**
     * Vérifie si le mail a été modifié
     * Utilisé lors de la modification du compte courant, évite d'obtenir une erreur
     * @param type $filters
     * @param type $mode
     * @param type $equality
     * @return type
     */
    public function isSameMail($email,$login){
        $query = "SELECT * FROM personne WHERE tx_email = :email AND tx_login = :login";
        $result = DB::query($query, array(
            ':email'=>$email,
            ':login'=>$login
            ));
        if(empty($result))
            return NULL;
        else
            return $result[0];
    }
    
    public function getPersonnesByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM personne WHERE ";
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
        $query .= ' ORDER BY id_pers DESC';
        return DB::query($query, $vals);
    }
    /**
     * Regarde si la personne est inscrite à une des plateformes de $person
     * @param type $person
     */
    public function isFellowMember($person)
    {
        $query = "SELECT 1 FROM membre WHERE id_personne = :person1"
                . " AND id_plateforme IN(SELECT id_plateforme FROM membre WHERE id_personne = :person2)";
        $result = DB::query($query, array(':person1'=>$this->pid, ':person2' => $person));
        return !empty($result);
    }
    
    public function getAllPersonnes(){
        $query = "SELECT * FROM personne";
        return DB::query($query, array());
    }
    
    public function isConnectedToPlatform($id_plateforme){
        if($this->pid == NULL)
            return FALSE;
        $query = "SELECT 1 FROM membre WHERE id_personne = :pers ANC id_plateforme = :platform";
        return !empty(DB::query($query, array(
            ':pers' =>  $this->pid,
            ':platform' =>  $id_plateforme
        )));
    }
}
