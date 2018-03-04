<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DemandeModel
 *
 * @author videony
 */
class DemandeModel {
    //put your code here
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    
    // Hiérarchie
    const PENDING = 1;
    const ACCEPTED = 2;
    const BANNED = 3;
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($personne, $plateforme, $message = '', $statut = self::PENDING) {
        $query = "INSERT INTO demande(id_plateforme, id_personne, status, message)"
                . " VALUES(:id_plateforme, :id_personne, :status, :message)";
        DB::update($query, array(
            ':id_plateforme'          =>  $plateforme,
            ':id_personne'        =>  $personne,
            ':status'      =>  $statut,
            ':message'   =>  $message
        ));
        return DB::lastInsertId();
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM demande WHERE id_demande = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
            if(empty($res))
                return null;
            else
                return $res[0];
        }
    }
    public function set($modifications) {
        if($this->pid == NULL)
            return FALSE;
        else
        {
            $query = 'UPDATE demande SET ';
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
            $query .= ' WHERE id_demande = :id';
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
            $query = "DELETE FROM demande WHERE id_demande = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    public function del($filters, $mode = self::AND_MODE)
    {
        $query = "DELETE FROM demande WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            $query .= $col.' = :'.strtolower($col);
            $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        return DB::update($query, $vals);
    }
    public function getDemandesByFilter($filters, $mode = self::AND_MODE)
    {
        $query = "SELECT * FROM demande WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            $query .= $col.' = :'.strtolower($col);
            $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        $query .= ' ORDER BY id_demande DESC';
        return DB::query($query, $vals);
    }
}
