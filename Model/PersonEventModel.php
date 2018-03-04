<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PersonEventModel
 *
 * @author videony
 */
class PersonEventModel {
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
    public function create($dt_end, $dt_begin, $int_adder, $tx_description, $tx_titre) {
        $query = "INSERT INTO person_events(dt_end, dt_begin, int_adder, tx_description, tx_titre)"
                . " VALUES(:dt_end, :dt_begin, :int_adder, :tx_description, :tx_titre)";
        DB::update($query, array(
            ':dt_end' => $dt_end, 
':dt_begin' => $dt_begin, 
':int_adder' => $int_adder, 
':tx_description' => $tx_description, 
':tx_titre' => $tx_titre
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
            $query = "SELECT * FROM person_events WHERE id_person_event = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
            if(!empty($res))
                return $res[0];
            else
                return NULL;
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
            $query = 'UPDATE person_events SET ';
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
            $query .= ' WHERE id_person_event = :id';
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
            $query = "DELETE FROM person_events WHERE id_person_event = :id";
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
        $query = "DELETE FROM person_events WHERE ";
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
    public function getPersonEventByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM person_events WHERE ";
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
        $query .= ' ORDER BY id_person_event DESC';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllPersonEvents()
    {
        $query = 'SELECT * FROM person_events ORDER BY id_person_event DESC';
        return DB::query($query, array());
    }
    
    /**
     * Renvoie les évènements inférieurs à une date et appartenant à une personne
     * /!\ Y compris évènements des plateformes
     * @param type $id_personne
     * @param type $min_date    NULL = since beginning of time
     * @param type $max_date    NULL = 'till the world ends
     */
    public function getFullAgenda($id_personne, $min_date = null, $max_date = null)
    {
        $dates = '';
        if($min_date != null)
        {
            if( is_numeric($min_date) && (int)$min_date == $min_date )
                $min_date = date('d-m-Y', $min_date);
            else
                $min_date = date('d-m-Y', strtotime($min_date));
            $dates = $dates." AND dt_begin > STR_TO_DATE('".$min_date."','%d-%m-%Y') ";
        }
        if($max_date != null)
        {
            if( is_numeric($max_date) && (int)$max_date == $max_date )
                $max_date = date('d-m-Y', $max_date);
            else
                $max_date = date('d-m-Y', strtotime($max_date));
            $dates = $dates." AND dt_begin < STR_TO_DATE('".$max_date."', '%d-%m-%Y') ";
        }
        $query = "SELECT id_person_event AS id, tx_titre, tx_description, int_adder, dt_begin, dt_end, "
            ."NULL as id_platforme, 'private' AS type "
            ."FROM person_events WHERE int_adder = :person ".$dates
            ."UNION "
            ."SELECT id_platform_event AS id, tx_titre, tx_description, int_adder, dt_begin, dt_end, id_plateforme, "
            ."'public' AS type "
            ."FROM platform_events "
            ."WHERE id_plateforme IN (SELECT id_plateforme FROM membre WHERE id_personne = :person) ".$dates;
        return DB::query($query, array(':person'=>$id_personne));
    }
}
