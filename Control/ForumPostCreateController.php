<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumPostCreateController
 *
 * @author videony
 */
class ForumPostCreateController implements BodyController{
  
    private $css = array(        
        'css/form.css',
        'css/Tables.css',
        'css/ForumTopic.css',
        'css/sceditor/themes/office-toolbar.min.css',
        'css/sceditor.css'
    );
    private $js = array(
        'js/form.js',
        'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/ForumPostCreate.js',
        'js/ForumTopic.js',
        'js/sceditor/jquery.sceditor.bbcode.min.js',
        'js/sceditor.js'
        
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
            return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_NEW_MESSAGE');
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Nouveau Post';
    }

    

    public function getContent() {
            if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

             if(empty($errors))
            {
                 //CREATION POST                                  
                 $modelPost = new ForumPostModel();
                 $id = $modelPost->create(date('Y-m-d H:i:s'), $_POST['post'], $_SESSION[SessionController::ID_MEMBRE], $_GET['idtopic']);
                 $data = $modelPost->get();
                 $location = $modelPost->getLocation();
                 
                 NotificationModel::fireNotif(NotificationModel::NOTIF_ON_NEW_MESSAGE,
                         'Nouvelle réponse pour "'.$location['nom_topic'].'" dans '.$location['nom_categorie'],
                         'request.php?action=ForumTopic&idcategorie='.$_GET['idcategorie'].'&idtopic='.$_GET['idtopic'].'#'.$id);
                 
                 $modelTopic = new ForumTopicModel();
                 $modelTopic->updateDateLastPost(date('Y-m-d H:i:s'), $_GET['idtopic']);
                 
                 //$success = FormUtils::successBox('Nouveau post ajouté');                 
                 
                 //Si retour avec controller, bug javascript ensuite
                 $header = header("Location: request.php?action=ForumTopic&idcategorie=".$_GET['idcategorie']."&idtopic=".$_GET['idtopic']);
                 
                 return $header;


             }
              else
            {
                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox();
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
            }
//        }
    }
    
     public function getForm($wrong_fields = array()){
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('ForumPostCreate');
            
            $post = FormUtils::textAreaBoxFormated('post', '');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_TOPIC###",$post);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumPostCreate');
            $wrong_fields = $this->processErrors($wrong_fields);
           
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ', $wrong_fields['post']['val'], '150px', $wrong_fields['post']['status'], $wrong_fields['post']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_TOPIC###",$post);


        }
        return $template;
    }
    
     public function check(){
        $errors = array();        
        // Post
        $result = strip_tags($_POST['post']);
        if(empty($result)) {
            $errors['post'] = array('val' => "", 'status' => TRUE, 'reason' => 'Veuillez écrire votre message.');
        }
        return $errors;
    }
    
    public function processErrors($errors) {       
        if(!isset($errors['post'])){
            $errors['post'] = array('val' => $_POST['post'], 'status' => FALSE,'reason'=>'');
        }   
        
        return $errors;
    }


    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    public function handlePostRequest() {
//        if(!isset($_POST['action']))
//           return '';
//        switch($_POST['action'])
//        {
//            default:
//                return '';
//                break;
//        }
    }

}
