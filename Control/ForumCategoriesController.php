<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumCategoriesController
 *
 * @author videony
 */
class ForumCategoriesController implements BodyController{
  
    private $css = array(
        'css/Forum.css',
        'css/ForumCategories.css',
        'css/Tables.css',
        'css/form.css'
    );
    private $js = array(
       'js/ForumCategories.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/form.js'
    );
    
    public function canAccess() {
        if(!isset($_SESSION[SessionController::PLATFORM_ID]))
            return FALSE;
        else
            return TRUE;
    }
    public function getTitle() {
        return 'Forum Categories';
    }

    

    public function getContent() {
        $model = new ForumTopicModel($_SESSION[SessionController::PLATFORM_ID]);

        $template = GenerateUtils::getTemplate('ForumCategories');

        //Récuperer sujets en fonction de la plateforme
        $topics = $model->getForumTopicByFilterWithUserName(array(                
            'plateforme_id' => $_SESSION[SessionController::PLATFORM_ID],
            'id_categorie' => $_GET['idcategorie']
        ));

        //get Categorie Model
        $catModel = new ForumCategoriesModel( $_GET['idcategorie']);
        $catdata = $catModel->get();

         $template = GenerateUtils::replaceStrings($template, array(
            'id_categorie'=>$_GET['idcategorie'],
            'nom_categorie' =>  $catdata["nom_categorie"]                   
        ));   

         //Transforme date
         foreach($topics as $key=>$data)
        {
            $topics[$key]['date_last_post']  = date('d-m-Y H:i:s', strtotime($topics[$key]['date_last_post']));
            if($data['post_cnt'] == null)
                $topics[$key]['post_cnt'] = 0;
        }
         
        $template = GenerateUtils::generateRS($template, $topics);

        $template = $this::setPermissions($template, $topics); 

        return $template;  
             
//        }
    }
    
    public function setPermissions($template,$topics){
             
        //permissions
        $permissionmodel = new UserPermissionModel();

        $resultCan = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_SUBJECT');

        if(empty($resultCan)){
            $template = GenerateUtils::emptyPart($template, "IF_CAN_CREATE_SUBJECT");
        }
        
        
        $resultDelOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OWN_SUBJECT');
        $resultDelOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OTHERS_SUBJECT');

        $resultEditOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OWN_SUBJECT');
        $resultEditOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OTHERS_SUBJECT');
        
        foreach($topics as $key=>$data)
        {
            if($topics[$key]['user_id'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultDelOwn))
                    $template = GenerateUtils::emptyPart($template, "SUBDEL".$topics[$key]['id_topic']);
            }
            if($topics[$key]['user_id'] != $_SESSION[SessionController::ID_MEMBRE])
            {
                 if(empty($resultDelOthers))
                    $template = GenerateUtils::emptyPart($template, "SUBDEL".$topics[$key]['id_topic']);
            }
            if($topics[$key]['user_id'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultEditOwn))
                    $template = GenerateUtils::emptyPart($template, "SUBED".$topics[$key]['id_topic']);
            }
            if($topics[$key]['user_id'] != $_SESSION[SessionController::ID_MEMBRE])
            {
                 if(empty($resultEditOthers))
                    $template = GenerateUtils::emptyPart($template, "SUBED".$topics[$key]['id_topic']);
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
            case 'delete_subject': 
                $forum_model = new ForumTopicModel();
                $result = $forum_model->createdBy($_SESSION[SessionController::ID_MEMBRE],$_POST['id']);    
                
                $topic_model = new ForumTopicModel($_POST['id']);
                $data = $topic_model->get();
                $location = $topic_model->getCategory();
                
                if(empty($result))
                {
                    //He did not create the topic
                    if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OTHERS_SUBJECT")))
                        $return = FALSE;
                    else{
                        $return = $topic_model->remove();
                    }
                    
                }                               
                if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OWN_SUBJECT")))
                    $return = FALSE;
                else{                   
                    $return = $topic_model->remove();
                }
                
                if($return == TRUE){
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_DELETE_SUBJECT,
                            'Sujet supprimé dans '.$location['nom_categorie'].' : '.$data['nom_topic']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;            
            
            case 'edit_subject':                
//                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OWN_MESSAGE"))
//                        return '';
//                $model = new ForumPostModel($_POST['id']);
//                $return = new ForumPostCreateController().getContent();
//                if($return == TRUE)
//                    echo 'OK';
//                else
//                    echo 'KO';
                break;   
        }
    }

}
