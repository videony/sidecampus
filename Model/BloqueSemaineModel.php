<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BloqueSemaineModel
 *
 * @author videony
 */
class BloqueSemaineModel {
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
    public function create($dt_end, $dt_begin, $id_personne) {
        $query = "INSERT INTO semaine_bloque(dt_end, dt_begin, id_personne)"
                . " VALUES(:dt_end, :dt_begin, :id_personne)";
        DB::update($query, array(
            ':dt_end' => $dt_end, 
':dt_begin' => $dt_begin, 
':id_personne' => $id_personne
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
            $query = "SELECT * FROM semaine_bloque WHERE id_semaine_bloque = :id";
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
            $query = 'UPDATE semaine_bloque SET ';
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
            $query .= ' WHERE id_semaine_bloque = :id';
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
            $query = "DELETE FROM seance_bloque WHERE id_semaine_bloque = :id";
            DB::update($query, array(':id'=>$this->pid));
            $query = "DELETE FROM semaine_bloque WHERE id_semaine_bloque = :id";
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
        $query = "DELETE FROM semaine_bloque WHERE ";
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
    public function getBloqueSemaineByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM semaine_bloque WHERE ";
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
        $query .= ' ORDER BY dt_begin ASC';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllBloqueSemaines()
    {
        $query = 'SELECT * FROM semaine_bloque ORDER BY dt_begin ASC';
        return DB::query($query, array());
    }
    
    public function getHourRange($id_semaine_bloque)
    {
        $query = "SELECT MIN(heure_debut) as hourmin, MAX(heure_fin) AS hourmax "
                . "FROM seance_bloque WHERE id_semaine_bloque = :id";
        $result = DB::query($query, array(':id'=>$id_semaine_bloque));
        if(empty($result) || $result[0]['hourmin'] == null)
            return array('hourmin'=>8, 'hourmax'=>22);
        else {
            $array = $result[0];
            if($array['hourmin'] > 8)
                $array['hourmin'] = 8;
            if($array['hourmax'] < 22)
                $array['hourmax'] = 22;
            
            return $array;
        }
    }
    public function resetBloque($id_personne)
    {
        $semaines = $this->getBloqueSemaineByFilter(array(
           'id_personne'    =>  $id_personne 
        ));
        foreach($semaines as $semaine)
        {
            $model = new BloqueSemaineModel($semaine['id_semaine_bloque']);
            $model->remove();
        }
    }
}
