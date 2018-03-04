<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnnonceModel
 *
 * @author videony
 */
class AnnonceModel {
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
    public function create($id_personne, $int_price, $tx_description, $tx_titre, $dt_posted) {
        $query = "INSERT INTO annonces(dt_posted, id_personne, int_price, tx_description, tx_titre)"
                . " VALUES(:dt_posted, :id_personne, :int_price, :tx_description, :tx_titre)";
        DB::update($query, array(
            ':dt_posted' => $dt_posted, 
':id_personne' => $id_personne, 
':int_price' => $int_price, 
':tx_description' => $tx_description, 
':tx_titre' => $tx_titre
        ));
        $id = DB::lastInsertId();
        $query = "UPDATE personne SET nb_annonces = nb_annonces + 1";
        DB::update($query, array());
        return $id;
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
            $query = "SELECT * FROM annonces WHERE id_annonce = :id";
            $res = DB::query($query, array(':id'=>$this->pid));
            if(empty($res))
                return array();
            else
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
            $query = 'UPDATE annonces SET ';
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
            $query .= ' WHERE id_annonce = :id';
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
            $model = new AnnonceReponseModel();
            $model->del(array(
               'id_annonce' =>  $this->pid 
            ));
            $query = "UPDATE personne SET nb_annonces = nb_annonces - 1 WHERE nb_annonces > 1";
            DB::update($query, array());
            $query = "DELETE FROM annonces WHERE id_annonce = :id";
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
        $query = "DELETE FROM annonces WHERE ";
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
    public function getAnnonceByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM annonces WHERE ";
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
        $query .= ' ORDER BY id_annonce DESC';
        return DB::query($query, $vals);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllAnnonces()
    {
        $query = 'SELECT * FROM annonces ORDER BY id_annonce DESC';
        return DB::query($query, array());
    }
    
    public function getAnnoncesCount()
    {
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
        {
            $query = 'SELECT count(*) AS cnt FROM annonces';
            $result = DB::query($query, array());
            return $result[0]['cnt'];
        }
        else
        {
            $query = 'SELECT nb_annonces FROM personne WHERE id_pers = :pers';
            $result = DB::query($query, array(':pers'=>$_SESSION[SessionController::ID_PERSONNE]));
            return $result[0]['nb_annonces'];
        }
    }
}
