<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TodoModel
 *
 * @author videony
 */
class TodoModel {
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
    public function create($id_personne, $tx_todo, $dt_deadline, $position = -1) {
        if($position == -1)
        {
            $select = "SELECT MAX(position) as m FROM todo WHERE id_personne = :personne";
            $result = DB::query($select, array(":personne"=>$id_personne));
            if(empty($result))
                $position = 1;
            else
                $position = $result[0]['m'] + 1;
        }
        $query = "INSERT INTO todo(id_personne, tx_todo, dt_deadline, position)"
                . " VALUES(:id_personne, :tx_todo, :dt_deadline, :position)";
        DB::update($query, array(
            ':id_personne' => $id_personne, 
            ':tx_todo' => $tx_todo, 
            ':dt_deadline' => $dt_deadline,
            ':position' => $position
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
            $query = "SELECT * FROM todo WHERE id_todo = :id";
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
            $query = 'UPDATE todo SET ';
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
            $query .= ' WHERE id_todo = :id';
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
            $query = "DELETE FROM todo WHERE id_todo = :id";
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
        $query = "DELETE FROM todo WHERE ";
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
    public function getTodoByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM todo WHERE ";
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
        $query .= ' ORDER BY position ';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY id_todo DESC';
        return DB::query($query, array());
    }
    public function getPersonalDeadlines($id_personne, $min_date, $max_date)
    {
        $dates = '';
        if($min_date != null)
        {
            if( is_numeric($min_date) && (int)$min_date == $min_date )
                $min_date = date('d-m-Y', $min_date);
            else
                $min_date = date('d-m-Y', strtotime($min_date));
            $dates = $dates." AND dt_deadline > STR_TO_DATE('".$min_date."','%d-%m-%Y') ";
        }
        if($max_date != null)
        {
            if( is_numeric($max_date) && (int)$max_date == $max_date )
                $max_date = date('d-m-Y', $max_date);
            else
                $max_date = date('d-m-Y', strtotime($max_date));
            $dates = $dates." AND dt_deadline < STR_TO_DATE('".$max_date."', '%d-%m-%Y') ";
        }
        $query = 'SELECT * FROM todo WHERE id_personne = :person '.$dates.' ORDER BY id_todo DESC';
        return DB::query($query, array(':person'=>$id_personne));
    }
    
    public function changePosition($todo, $pos)
    {
        // Décalage de tous les suivants
        $query = "UPDATE todo SET position = position + 1 "
                . "WHERE position >= :pos AND id_personne = :pers";
        DB::update($query, array(
            ':pers'   =>  $_SESSION[SessionController::ID_PERSONNE],
            ':pos'      =>  $pos
        ));
        // Modification de la position du concerné
        $model1 = new TodoModel($todo);
        $model1->set(array(
           'position'   =>  $pos 
        ));
    }
}
