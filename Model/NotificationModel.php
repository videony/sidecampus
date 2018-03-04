<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationModel
 *
 */
class NotificationModel {
    
    //Calendrier
       const NOTIF_ON_NEW_EVENT         = "NOTIF_ON_NEW_EVENT";
       const NOTIF_ON_DELETE_EVENT      = "NOTIF_ON_DELETE_EVENT";
      
    //Fichiers
       const NOTIF_ON_NEW_FOLDER        = "NOTIF_ON_NEW_FOLDER";
       const NOTIF_ON_NEW_FILE          = "NOTIF_ON_NEW_FILE";
       const NOTIF_ON_DELETE_FILE       = "NOTIF_ON_DELETE_FILE";
       const NOTIF_ON_DELETE_FOLDER     = "NOTIF_ON_DELETE_FOLDER";
       
       
    //Forum
       const NOTIF_ON_NEW_CATEGORY      = "NOTIF_ON_NEW_CATEGORY";
       const NOTIF_ON_NEW_MESSAGE       = "NOTIF_ON_NEW_MESSAGE";
       const NOTIF_ON_DELETE_SUBJECT    = "NOTIF_ON_DELETE_SUBJECT";
       const NOTIF_ON_DELETE_MESSAGE    = "NOTIF_ON_DELETE_MESSAGE";
       const NOTIF_ON_DELETE_CATEGORY   = "NOTIF_ON_DELETE_CATEGORY";
       const NOTIF_ON_NEW_SUBJECT       = "NOTIF_ON_NEW_SUBJECT";
       
    //Plateforme
       const NOTIF_ON_SET_ADMIN         = "NOTIF_ON_SET_ADMIN";
       const NOTIF_ON_PLATFORM_EDIT     = "NOTIF_ON_PLATFORM_EDIT";
       const NOTIF_ON_SET_MEMBER        = "NOTIF_ON_SET_MEMBER";
       const NOTIF_ON_SET_MODERATOR     = "NOTIF_ON_SET_MODERATOR";
       const NOTIF_ON_UNBAN_USER        = "NOTIF_ON_UNBAN_USER";
       const NOTIF_ON_ACCEPT_USER       = "NOTIF_ON_ACCEPT_USER";
       const NOTIF_ON_EJECT_USER        = "NOTIF_ON_EJECT_USER";
       const NOTIF_ON_CHANGE_PERM_USER  = "NOTIF_ON_CHANGE_PERM_USER";
       const NOTIF_ON_BLOCK_USER        = "NOTIF_ON_BLOCK_USER";
       const NOTIF_ON_BAN_USER          = "NOTIF_ON_BAN_USER";
       const NOTIF_ON_UNBLOCK_USER      = "NOTIF_ON_UNBLOCK_USER";
       const NOTIF_ON_NEW_ASK_TO_JOIN   = "NOTIF_ON_NEW_ASK_TO_JOIN";
       
       const NB_NOTIFS_DEFAULT = 10;
       
       
    /**
     * Récupère le nombre de notification du membre connecté
     * @return type
     */ 
    public static function getNbNotif(){
         $query = "SELECT compteurNotif FROM membre WHERE id_membre = :id_membre";
         $result = DB::query($query, array('id_membre' => $_SESSION[SessionController::NOTIFICATION_MEMBER_ID]));
		 if(!empty($result))
			return $result[0]['compteurNotif'];
		else
            return 0;    
    }
    public static function getTotalNbNotif(){
         $query = "SELECT SUM(compteurNotif) AS total FROM membre WHERE id_personne = :id_pers";
         $result = DB::query($query, array(':id_pers' => $_SESSION[SessionController::ID_PERSONNE]));
         return $result[0]['total'];
                
    }
    public static function getNotifsOfMembre($nb = -1, $start = 0){
        if($nb == -1)
            $nb = self::getNbNotif();
        if(!is_numeric($nb))
            return null;
        if($nb < self::NB_NOTIFS_DEFAULT)
            $nb = self::NB_NOTIFS_DEFAULT;
        $query = "SELECT personne.id_pers as id_notif_personne, personne.tx_nom as tx_notif_nom,
            personne.tx_prenom as tx_notif_prenom, notif.date as notif_date,
            notif.type as notif_type, notif.texte as notif_texte,
            setting.section as notif_section, notif.tx_link as tx_link
            FROM notif_plateforme notif, user_setting param, membre, personne, setting
            WHERE notif.type = param.setting_name 
            AND param.value IS NOT NULL AND param.value = 1
            AND membre.id_membre = param.id_membre
            AND notif.id_plateforme = membre.id_plateforme
            AND membre.id_membre = :id_membre
            AND notif.id_user = personne.id_pers
            AND setting.setting_name = param.setting_name
            AND notif.id_user <> :id_personne
            ORDER BY notif.date DESC
            LIMIT ".$start.", ".$nb;
        $result = DB::query($query, array(
            'id_membre' => $_SESSION[SessionController::NOTIFICATION_MEMBER_ID],
            'id_personne' => $_SESSION[SessionController::ID_PERSONNE]
        ));
        return $result;
    }
    
    public static function resetCompteur(){
        $query = "UPDATE membre SET compteurNotif = 0 WHERE id_membre = :id_membre";
        DB::update($query, array('id_membre' => $_SESSION[SessionController::NOTIFICATION_MEMBER_ID]));
    }
    
    
    //Ajoute une notification + la personne qui l'a initiée
    //Et met à jour le compteur de notification des personnes abonnées
    public static function fireNotif($typeNotif, $textNotif, $link = '#', $platform = null){
        //id_user => id_personne
       $query = "INSERT INTO notif_plateforme (id_user, type, texte, date, id_plateforme, tx_link)"
                . " VALUES (:id_user, :type, :texte, :date, :id_plateforme, :tx_link)";
        
       if($platform == null)
           $platform = $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID];
       
        DB::update($query, array(
            ':id_user'          =>  $_SESSION[SessionController::ID_PERSONNE],
            ':type'        =>  $typeNotif,
            ':texte'      => self::sanitize($textNotif),
            ':date'       => date('Y-m-d H:i:s'), // @TODO today 
            ':id_plateforme' => $platform,
            ':tx_link'			=>	$link
        ));
        
        // @TODO: REGLER L AFFICHAGE        
        
        //select  membre de plateforme 
        $memModel = new MemberModel();
        $results = $memModel->getMembersByFilter(array(
            'id_plateforme' => $platform
        ));
        //Parcours de ces membres       
        foreach($results as $result){
            //Si abonné a la notification, ajout du compteur ++
            if( (!isset($_SESSION[SessionController::NOTIFICATION_MEMBER_ID]) ||
                    $result['id_membre'] != $_SESSION[SessionController::NOTIFICATION_MEMBER_ID])
               && UserSettingModel::abonne($result['id_membre'], $typeNotif)){
                $memModel->incrementNotifCount($result['id_membre']);
            }      
        }
        
    }
    
    public static function sanitize($string)
    {
        return strip_tags($string);
    }
   
}
