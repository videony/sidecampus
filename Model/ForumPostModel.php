<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumPostModel
 *
 * @author videony
 */
class ForumPostModel {
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
    public function create($date, $post, $id_user, $id_topic) {
        $query = "INSERT INTO forum_posts(date, post, id_user, id_topic)"
                . " VALUES(:date, :post, :id_user, :id_topic)";
        DB::update($query, array(
            ':date' => $date, 
            ':post' => nl2br($post),  //save carriage returns
            ':id_user' => $id_user, 
            ':id_topic' => $id_topic
        ));
        $this->pid = DB::lastInsertId();
        return $this->pid;
    }
    
    public function get() {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "SELECT * FROM forum_posts WHERE id_post = :id";            
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
            $query = 'UPDATE forum_posts SET ';
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
            $query .= ' WHERE id_post = :id';
            $vals[':id'] = $this->pid;
            return DB::update($query, $vals);
        }
    }
    public function remove()
    {
        if($this->pid == NULL)
            return NULL;
        else
        {
            $query = "DELETE FROM forum_posts WHERE id_post = :id";
            return DB::update($query, array(':id'=>$this->pid));
        }
    }
    public function del($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "DELETE FROM forum_posts WHERE ";
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
    public function getForumPostByFilter($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT * FROM forum_posts WHERE ";
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
        $query .= ' ORDER BY id_post DESC';
        return DB::query($query, $vals);
    }
    
    public function getForumPostsByTopic($filters, $mode = self::AND_MODE, $equality = self::EQUAl)
    {
        $query = "SELECT p.id_post, p.id_topic, pe.tx_login, pe.id_pers, p.id_user, p.post, p.date, t.nom_topic FROM forum_posts p "
                . "JOIN forum_topics t ON p.id_topic=t.id_topic "
                . "JOIN membre m ON p.id_user = m.id_membre "
                . "JOIN personne pe ON pe.id_pers=m.id_personne WHERE ";
        $first = TRUE;
        $vals = array();
        foreach($filters as $col=>$val)
        {
            if($first === FALSE)
                $query .= ' '.$mode.' ';
            
            $query .= $col.' '.$equality.$val;
           // $vals[':'.strtolower($col)] = $val;
            if($first === TRUE)
                $first = FALSE;
        }
        $query .= ' ORDER BY p.date ASC';        
        return DB::query($query, $vals);
    }
    
     public function createdBy($creator, $idpost){
        $query = "SELECT * from forum_posts WHERE id_user = :id_creator && id_post = :id";
        
        return  DB::query($query, array(
            ':id_creator' => $creator,
            ':id' => $idpost));
    }
    
    public function getAllForumPosts()
    {
        $query = 'SELECT * FROM forum_posts ORDER BY id_post DESC';
        return DB::query($query, array());
    }
    
    public function getLocation()
    {
        $query = "SELECT * from forum_categories, forum_topics, forum_posts "
                . "WHERE forum_categories.id_categorie = forum_topics.id_categorie "
                . "AND forum_topics.id_topic = forum_posts.id_topic "
                . "AND forum_posts.id_post = :post";
        $result = DB::query($query, array(":post"=>$this->pid));
        return $result[0];
    }
}
