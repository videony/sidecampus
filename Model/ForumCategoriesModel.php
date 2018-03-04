<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumCategoriesModel
 *
 * @author videony
 */
class ForumCategoriesModel {
    private $pid;
    // Filtres
    const AND_MODE = 'AND';
    const OR_MODE= 'OR';
    const EQUAl = '=';
    const UNEQUAL = '<>';
    
    public function __construct($uid = NULL)
    {
        DB::connect();
        $this->pid = $uid;
    }
    public function create($date_last_post, $nom_categorie, $plateforme_id, $user_id) {
        $query = "INSERT INTO forum_categories(date_last_post, nom_categorie, plateforme_id, user_id)"
                . " VALUES(:date_last_post, :nom_categorie, :plateforme_id, :user_id)";
        DB::update($query, array(
            ':date_last_post' => $date_last_post, 
            ':nom_categorie' => $nom_categorie, 
            ':plateforme_id' => $plateforme_id, 
            ':user_id' => $user_id
        ));
        return DB::lastInsertId();
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM forum_categories WHERE id_categorie = :id";
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
            $query = 'UPDATE forum_categories SET ';
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
            $query .= ' WHERE id_categorie = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    
     public  function updateDateLastPost($value, $id_cat){        
            $query = 'UPDATE forum_categories SET date_last_post =\''.$value.'\' '
                    . ' WHERE id_categorie = :id';
            
            $vals[':id'] = $id_cat;
            return DB::update($query, $vals);
    }
    
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            
            $queryTopics = "SELECT * from forum_topics WHERE id_categorie = :id";
            $topics = DB::query($queryTopics, array(':id'=>$this->pid));
            foreach($topics as $key=>$data){
                $topicModel = new ForumTopicModel($topics[$key]["id_topic"]);
                $topicModel->remove();
            }
                
            $query = "DELETE FROM forum_categories WHERE id_categorie = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    
     public function createdBy($creator, $idcat){
        $query = "SELECT * from forum_categories WHERE user_id = :id_creator && id_categorie = :id";
        
        return  DB::query($query, array(
            ':id_creator' => $creator,
            ':id' => $idcat));
    }
    
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM forum_categories WHERE ";
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
    public function getForumCategoriesByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM forum_categories WHERE ";
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
        $query .= ' ORDER BY id_categorie DESC';
        return DB::query($query, $vals);
    }
    
    public function getForumCategoriesByFilterWithUserName($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT c.id_categorie, c.user_id, c.plateforme_id, c.date_last_post, c.nom_categorie, p.tx_login, "
                . "cnts.topic_cnt "
                . "FROM forum_categories c "                
                . "JOIN membre m ON c.user_id = m.id_membre "
                . "LEFT JOIN (SELECT id_categorie, COUNT(*) as topic_cnt FROM forum_topics GROUP BY id_categorie) AS cnts "
                . "ON cnts.id_categorie = c.id_categorie "
                . "JOIN personne p ON m.id_personne = p.id_pers WHERE ";
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
        $query .= ' ORDER BY c.date_last_post DESC';
        return DB::query($query, $vals);
    }
    
    public function getAllForumCategoriess()
    {
        $query = 'SELECT * FROM forum_categories ORDER BY id_categorie DESC';
        return DB::query($query, array());
    }
}
