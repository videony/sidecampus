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
class PlatformModel {
    
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const LIKE_MODE = 1;
    const EQUAL_MODE = 2;
    
    // Hiérarchie
    const MEMBER = 1;
    const MODERATOR = 2;
    const ADMIN = 3;
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($nom, $annee, $section, $description, $ecole, $cp, $ville) {
        $query = "INSERT INTO plateforme(tx_nom, annee, section, descriptif, ecole, cp, ville, dt_created)"
                . " VALUES(:nom, :annee, :section, :descriptif, :ecole, :cp, :ville, NOW())";

        DB::update($query, array(
            ':nom'          =>  $nom,
            ':annee'        =>  $annee,
            ':section'      =>  $section,
            ':descriptif'   =>  $description,
            ':ecole'        =>  $ecole,
            ':cp'           =>  $cp,
            ':ville'        =>  $ville
        ));
        $newid = DB::lastInsertId();
        return $newid;
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM plateforme WHERE id_plateforme = :id";
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
            $query = 'UPDATE plateforme SET ';
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
            $query .= ' WHERE id_plateforme = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    /*public function getByLogin($login) {
        $query = "SELECT * FROM plateforme WHERE tx_login = :login";
        $result = DB::query($query, array(':login'=>$login));
        if(empty($result))
            return NULL;
        else
            return $result[0];
    }*/
    public function getAllPlatforms()
    {
//        $query = "SELECT * FROM plateforme WHERE visible = 1 ORDER BY dt_created DESC";
        $query = "SELECT p.*, count(m.id_membre) as nb_membres "
                . "FROM plateforme p "
                . "JOIN membre m ON m.id_plateforme = p.id_plateforme "
                . "WHERE p.visible = 1 "
                . "GROUP BY p.id_plateforme "
                . "ORDER BY p.dt_created DESC ";
        return DB::query($query, array());
    }
    public function getPlatformByFilter($filters, $mode = self::AND_MODE, $opacity = self::LIKE_MODE)
    {
        $query = "SELECT * FROM plateforme WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            if($opacity == self::LIKE_MODE)
            {
                $query .= $col.' LIKE :'.strtolower($col);
                $vals[':'.strtolower($col)] = '%'.$val.'%';
            }
            else
            {
                $query .= $col.' = :'.strtolower($col);
                $vals[':'.strtolower($col)] = $val;
            }
            if($first === TRUE)
                $first = FALSE;
        }
        $query .= ' ORDER BY dt_created DESC';
        return DB::query($query, $vals);
    }
}
