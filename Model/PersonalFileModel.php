<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileModel
 *
 * @author videony
 */
class PersonalFileModel {
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
    public function create($id_adder, $tx_commentaire, $tx_extension, $tx_name, $id_folder, $size, $encoded) {
        $query = "INSERT INTO personal_file(id_adder, dt_ajout, tx_commentaire, tx_extension, tx_name, id_folder, int_size, tx_encoded_name)"
                . " VALUES(:id_adder, NOW(), :tx_commentaire, :tx_extension, :tx_name, :id_folder, :size, :encoded)";
        DB::update($query, array(
            ':id_adder' => $id_adder, 
        ':tx_commentaire' => $tx_commentaire, 
        ':tx_extension' => $tx_extension, 
        ':tx_name' => $tx_name, 
        ':id_folder' => $id_folder,
            ':size' =>  $size,
            ':encoded'  =>  $encoded
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
            $query = "SELECT * FROM personal_file WHERE id_file = :id";
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
            $query = 'UPDATE personal_file SET ';
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
            $query .= ' WHERE id_file = :id';
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
            // Suppression physique
            $targetFolder = getcwd().PersonalFilesController::$filesFolder;
            $data = $this->get();
            $encoded = $data['tx_encoded_name'];
            $dir1       = substr($encoded, 0, 1);
            $dir2       = substr($encoded, 1, 1);
            if(file_exists($targetFolder.$encoded))
                unlink($targetFolder.$encoded);
            // Suppression logique
            $query = "DELETE FROM personal_file WHERE id_file = :id";
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
        $files = $this->getFileByFilter($filters, $mode, $equality);
        foreach($files as $file)
        {
            $model = new PersonalFileModel($file['id_file']);
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
    public function getFileByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM personal_file WHERE ";
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
     * Renvoie toutes les instances.
     * @return array Resultset de la requête
     */
    public function getAllFiles()
    {
        $query = 'SELECT * FROM personal_file ORDER BY tx_name';
        return DB::query($query, array());
    }
}
