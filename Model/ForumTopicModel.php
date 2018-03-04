<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumTopicModel
 *
 * @author videony
 */
class ForumTopicModel {
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
    public function create($date_last_post, $creation_date, $user_id, $plateforme_id, $nom_topic, $first_post, $id_categorie) {
        $query = "INSERT INTO forum_topics(date_last_post, creation_date, user_id, plateforme_id, nom_topic, first_post, id_categorie)"
                . " VALUES(:date_last_post, :creation_date, :user_id, :plateforme_id, :nom_topic, :first_post, :id_categorie)";
        DB::update($query, array(
            ':date_last_post' => $date_last_post, 
            ':user_id' => $user_id, 
            ':plateforme_id' => $plateforme_id, 
            ':first_post' => nl2br($first_post),
            ':nom_topic' => $nom_topic,
            ':id_categorie' => $id_categorie,
            ':creation_date' => $creation_date
        ));
        $this->pid = DB::lastInsertId();
        return $this->pid;
    }
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM forum_topics WHERE id_topic = :id";
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
            $query = 'UPDATE forum_topics SET ';
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
            $query .= ' WHERE id_topic = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    
    public  function updateDateLastPost($value, $id_topic){        
            $query = 'UPDATE forum_topics SET date_last_post =\''.$value.'\' '
                    . ' WHERE id_topic = :id';
            
            $vals[':id'] = $id_topic;
            return DB::update($query, $vals);
    }
    
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query1 = "DELETE FROM forum_posts WHERE id_topic = :id";
            DB::update($query1, array(':id'=>$this->pid));
            $query2 = "DELETE FROM forum_topics WHERE id_topic = :id";
            return DB::update($query2, array(':id'=>$this->pid));
        }
    }
    
    
    
    public function createdBy($creator, $idtopic){
        $query = "SELECT * from forum_topics WHERE user_id = :id_creator && id_topic = :id";
        
        return  DB::query($query, array(
            ':id_creator' => $creator,
            ':id' => $idtopic));
    }
    
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM forum_topics WHERE ";
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
    public function getForumTopicByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM forum_topics WHERE ";
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
        $query .= ' ORDER BY id_topic DESC';
        return DB::query($query, $vals);
    }
    public function getAllForumTopics()
    {
        $query = 'SELECT * FROM forum_topics ORDER BY id_topic DESC';
        return DB::query($query, array());
    }
    
    public function getForumTopicByFilterWithUserName($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT f.id_topic, f.user_id, f.plateforme_id, f.date_last_post, f.nom_topic, p.tx_login, p.id_pers, "
                . "cnts.post_cnt "
                . "FROM forum_topics f "
                . "JOIN membre m ON f.user_id = m.id_membre "
                . "LEFT JOIN (SELECT id_topic, COUNT(*) as post_cnt FROM forum_posts GROUP BY id_topic) AS cnts "
                . "ON cnts.id_topic = f.id_topic "
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
        $query .= ' ORDER BY f.date_last_post DESC';
        return DB::query($query, $vals);
    }
    public function getCategory()
    {
        $query = "SELECT * from forum_categories, forum_topics "
                . "WHERE forum_categories.id_categorie = forum_topics.id_categorie "
                . "AND forum_topics.id_topic = :topic";
        $result = DB::query($query, array(":topic"=>$this->pid));
        return $result[0];
    }
}
