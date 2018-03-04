<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FolderModel
 *
 * @author videony
 */
class FolderModel {
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
    public function create($tx_name, $id_adder, $id_parent_folder, $id_plateforme) {
        $query = "INSERT INTO folder(dt_ajout, tx_name, id_adder, id_parent_folder, id_plateforme)"
                . " VALUES(NOW(), :tx_name, :id_adder, :id_parent_folder, :id_plateforme)";
        DB::update($query, array(   
        ':tx_name' => $tx_name, 
        ':id_adder' => $id_adder, 
        ':id_parent_folder' => $id_parent_folder, 
        ':id_plateforme' => $id_plateforme
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
            $query = "SELECT * FROM folder WHERE id_folder = :id";
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
            $query = 'UPDATE folder SET ';
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
            $query .= ' WHERE id_folder = :id';
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
            // Remove inner files
            $model = new FileModel();
            $model->del(array('id_folder'=>$this->pid));
            
            // Remove inner folders
            $model = new FolderModel();
            $model->del(array('id_parent_folder'=>$this->pid));
            
            $query = "DELETE FROM folder WHERE id_folder = :id";
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
        $folders = $this->getFolderByFilter($filters, $mode, $equality);
        foreach($folders as $folder)
        {
            $model = new FolderModel($folder['id_folder']);
            $model->remove();
        }
        return TRUE;
    }
    /**
     * Renvoie les instances liées aux filtres donnés en argument
     * @param array $filters Ensemble de correspondances colonne=>valeur alimentant le where
     * @param type $mode Lie les différentes clauses du where par AND ou OR
     * @param type $equality EQUAL pour '=', UNEQUAL pour '<>'
     * @return array Resultset de la requête
     */
    public function getFolderByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM folder WHERE ";
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
        $query .= ' ORDER BY tx_name';
        return DB::query($query, $vals);
    }
    /**
     * Retourne le dossier par défaut de la plateforme
     * @param type $platform Plateforme sur laquelle chercher le dossier par défaut
     * @return type Les informations du dossier
     */
    public function getDefaultFolder($platform)
    {
        $query = "SELECT * FROM folder WHERE id_plateforme = :platform AND id_parent_folder IS NULL";
        $res = DB::query($query, array(':platform'=>$platform));
        return $res[0];
    }
    /**
     * Regarde si un dossier existe déjà dans le dossier passé en paramètre.
     * @param type $englobing L'id du dossier en question qui englobera le nouveau dossier
     * @param type $newFolder Le nouveau dossier sur lequel porte le test
     */
    public function folderExists($englobing, $newFolder)
    {
        $query = "SELECT 1 FROM folder WHERE id_parent_folder = :englobing AND tx_name = :name";
        $res = DB::query($query, array(':englobing'=>$englobing, ':name'=>$newFolder));
        return !empty($res);
    }
    /**
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllFolders()
    {
        $query = 'SELECT * FROM folder ORDER BY tx_name';
        return DB::query($query, array());
    }
    
    public function getSize($id)
    {
        $query = "SELECT SUM(int_size) as somme FROM file WHERE id_folder = :id";
        $result = DB::query($query, array(':id'=>$id));
        if(empty($result))
            $size = 0;
        else
            $size = $result[0]['somme'];
        $query = "SELECT * FROM folder WHERE id_parent_folder = :id";
        $results = DB::query($query, array(':id'=>$id));
        foreach($results as $res)
        {
            $size = $size + $this->getSize($res['id_folder']);
        }
        return $size;
    }
}
