<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlatformMessageModel
 *
 * @author videony
 */
class PlatformMessageModel {
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    /**
     * Construit l'objet et se connecte à la base de données
     * @param type $uid Identifiant de l'instance. Peut être NULL si le modèle n'est pas lié à une instance.
     */
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($id_plateforme, $id_writer, $tx_message) {
        $query = "INSERT INTO platform_message(id_plateforme, id_writer, tx_message)"
                . " VALUES(:id_plateforme, :id_writer, :tx_message)";
        DB::update($query, array(
            ':id_plateforme' => $id_plateforme, 
            ':id_writer' => $id_writer, 
            ':tx_message' => $tx_message
                    ));
        return DB::lastInsertId();
    }
    /**
     * Si l'objet est lié à un identifiant, retourne les informations de l'instance. Sinon retourne NULL
     * @return Array (Tableau contenant la valeur de toutes les colonnes)
     */
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM platform_message WHERE id_message = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
            if(!empty($res))
                return $res[0];
        }
    }
    /**
     * Met à jour l'instance liée à l'objet dans la base de données.
     * @param array $modifications Liste des modifications à apporter (colonne=>valeur)
     * @return boolean TRUE si mise à jour réussie, FALSE si ratée ou que l'objet n'est lié à aucun autre
     */
    public function set($modifications) {
        if($this->pid == NULL)
            return FALSE;
        else
        {
            $query = 'UPDATE platform_message SET ';
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
            $query .= ' WHERE id_message = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    /**
     * Supprime l'instance dans la base de données
     * @return TRUE si l'objet est retiré, FALSE si opération échouée ou l'objet n'est pas associé à une instance
     */
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "DELETE FROM platform_message WHERE id_message = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    /**
     * Supprime les instances liées aux filtres donnés en argument
     * @param array $filters Ensemble de correspondances colonne=>valeur alimentant le where
     * @param type $mode Lie les différentes clauses du where par AND ou OR
     * @param type $equality EQUAL pour '=', UNEQUAL pour '<>'
     * @return boolean
     */
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM platform_message WHERE ";
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
    /**
     * Renvoie les instances liées aux filtres donnés en argument
     * @param array $filters Ensemble de correspondances colonne=>valeur alimentant le where
     * @param type $mode Lie les différentes clauses du where par AND ou OR
     * @param type $equality EQUAL pour '=', UNEQUAL pour '<>'
     * @return array Resultset de la requête
     */
    public function getPlatformMessageByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM platform_message, personne WHERE "
                . "platform_message.id_writer = personne.id_pers AND (";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            if($col == 'id_plateforme')
                $query .= 'platform_message.id_plateforme'.' '.$equality.' :'.strtolower($col);
            else
                $query .= $col.' '.$equality.' :'.strtolower($col);
            $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        $query .= ') ORDER BY id_message DESC';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllPlatformMessages()
    {
        $query = 'SELECT * FROM platform_message ORDER BY id_message DESC';
        return DB::query($query, array());
    }
}
