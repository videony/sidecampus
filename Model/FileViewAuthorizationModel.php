<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileViewAuthorizationModel
 *
 * @author videony
 */
class FileViewAuthorizationModel {
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    const PERSONAL_FILE = "personal";
    const PLATFORM_FILE = "platform";
    
    /**
     * Construit l'objet et se connecte à la base de données
     * @param type $uid Identifiant de l'instance. Peut être NULL si le modèle n'est pas lié à une instance.
     */
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($tx_key, $dt_perished, $id_file, $type) {
        $query = "INSERT INTO file_external_authorization(tx_key, dt_perished, id_file, tx_type)"
                . " VALUES(:tx_key, :dt_perished, :id_file, :type)";
        DB::update($query, array(
            ':tx_key' => $tx_key, 
':dt_perished' => $dt_perished, 
':id_file' => $id_file,
            ':type'=>$type
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
            $query = "SELECT * FROM file_external_authorization WHERE id_authorization = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
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
            $query = 'UPDATE file_external_authorization SET ';
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
            $query .= ' WHERE id_authorization = :id';
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
            $query = "DELETE FROM file_external_authorization WHERE id_authorization = :id";
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
        $query = "DELETE FROM file_external_authorization WHERE ";
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
    public function getFileViewAuthorizationByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM file_external_authorization WHERE ";
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
        $query .= ' ORDER BY id_authorization DESC';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllFileViewAuthorizations()
    {
        $query = 'SELECT * FROM file_external_authorization ORDER BY id_authorization DESC';
        return DB::query($query, array());
    }
}
