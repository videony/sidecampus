<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumTopicController
 *
 * @author videony
 */
class ForumTopicController implements BodyController{
    
    private $css = array(     
        'css/Tables.css',  
        'css/ForumTopic.css',
        'css/sceditor.css'
    );
    private $js = array(   
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',     
        'js/Forum.js',
        'js/ForumTopic.js'
       
    );
    
    public function canAccess() {
        return isset($_SESSION[SessionController::ID_MEMBRE]);
    }
    public function getTitle() {
        return 'Forum Topic';
    }

    

    public function getContent() {
             $template = GenerateUtils::getTemplate('ForumTopic');
            
            
            $id_topic = $_GET["idtopic"];
            $model = new ForumPostModel($id_topic);
            
            //get Topic Title
            $topicModel = new ForumTopicModel($id_topic);
            $topicdata = $topicModel->get();
            //
            
            $memberModel = new MemberModel($topicdata["user_id"]);
            $personId = $memberModel->get();
            
            $personModel = new PersonneModel($personId["id_personne"]);
            $personName = $personModel->get();


            //Récuperer posts en fonction du  topic
            $posts = $model->getForumPostsByTopic(array(                
                'p.id_topic' => $id_topic
            ));
            
            
            $template = GenerateUtils::replaceStrings($template, array(
                    'topic'=>$id_topic,
                    'nom_topic' =>  $topicdata["nom_topic"],
                    'first_post' => GenerateUtils::tohtml($topicdata["first_post"], TRUE),
                    'creator' => $personId["id_personne"],
                    'creation_date' => date('d-m-Y à H:i:s', strtotime($topicdata["creation_date"])),
                    'creatorLogin' => $personName["tx_login"],
                    'id_categorie' => $_GET["idcategorie"]
                ));            

            foreach($posts as $key=>$data)
            {
                $posts[$key]['date'] = date('d-m-Y à H:i:s', strtotime($posts[$key]['date']));
                $posts[$key]['post'] = GenerateUtils::tohtml($data['post'], TRUE);
            }
            
            $template = GenerateUtils::generateRS($template, $posts);
            
            $template = $this->setPermissions($template, $posts);
            
            return $template;  
             
                
           
//        }
    }
    
    public function setPermissions($template,$posts){
             
        //permissions
        $permissionmodel = new UserPermissionModel();

        $resultCan = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_NEW_MESSAGE');

        if(empty($resultCan)){
            $template = GenerateUtils::emptyPart($template, "IF_CAN_CREATE_NEW_MESSAGE");
        }
        
        
        $resultDelOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OWN_MESSAGE');
        $resultDelOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OTHERS_MESSAGES');

        $resultEditOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OWN_MESSAGE');
        $resultEditOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OTHERS_MESSAGE');
        
        foreach($posts as $key=>$data)
        {
            if($posts[$key]['id_user'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultDelOwn))
                    $template = GenerateUtils::emptyPart($template, "PODEL".$posts[$key]['id_post']);
            }
            if($posts[$key]['id_user'] != $_SESSION[SessionController::ID_MEMBRE])
            {
                 if(empty($resultDelOthers))
                    $template = GenerateUtils::emptyPart($template, "PODEL".$posts[$key]['id_post']);
            }
            if($posts[$key]['id_user'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultEditOwn))
                    $template = GenerateUtils::emptyPart($template, "POED".$posts[$key]['id_post']);
            }
            if($posts[$key]['id_user'] != $_SESSION[SessionController::ID_MEMBRE])
            {
               
                 if(empty($resultEditOthers))
                    $template = GenerateUtils::emptyPart($template, "POED".$posts[$key]['id_post']);
            }           
        }
        
        return $template;
    }
        
    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    public function handlePostRequest() {
       
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'delete_post':  
                $model = new ForumPostModel($_POST['id']);
                $data = $model->get();
                $location = $model->getLocation();
                
                if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OTHERS_MESSAGES")))
                    $return = FALSE;
                else{                    
                    $return = $model->remove();
                }
                
                if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OWN_MESSAGE")))
                        $return = FALSE;
                else{
                    $return = $model->remove();
                }
                
                
                if($return == TRUE){
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_DELETE_MESSAGE, 
                            'Réponse supprimée pour "'.$location['nom_topic'].'" '
                            . 'dans '.$location['nom_categorie']);
                    return 'OK';
                }
                else
                    return 'KO';
                break;            
            
            case 'edit_post':                
//                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OWN_MESSAGE"))
//                        $return = '';
//                $model = new ForumPostModel($_POST['id']);                
//                
//                $post = $model->get();
//                
//                return $post["post"];
//                if($return == TRUE)
//                    echo 'OK';
//                else
//                    echo 'KO';
                break;   
        }
        
    }

}
