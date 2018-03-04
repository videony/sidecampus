<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumTopicCreateController
 *
 * @author videony
 */
class ForumTopicCreateController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/ForumTopicCreate.css',
        'css/ForumCategories.css',
        'css/sceditor/themes/office-toolbar.min.css',
        'css/sceditor.css'
    );
    private $js = array(
        'js/form.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/ForumTopicCreate.js',
        'js/ForumCategories.js',
        'js/sceditor/jquery.sceditor.bbcode.min.js',
        'js/sceditor.js'
        
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
            return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_SUBJECT');
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Creation d\'un sujet';
    }

    

    public function getContent() {
            if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

             if(empty($errors))
            {
                 //CREATION SUJET
                 $model = new ForumTopicModel();
                 $topic = $model->create(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $_SESSION[SessionController::ID_MEMBRE], $_SESSION[SessionController::PLATFORM_ID], $_POST['sujet'], $_POST['post'], $_GET['idcategorie']);             
                 $location = $model->getCategory();
//                 $modelPost = new ForumPostModel();
//                 $post = $modelPost->create(date('Y-m-d H:i:s'), $_POST['post'], $_SESSION[SessionController::ID_PERSONNE], $topic);
                 
                 $modelCat = new ForumCategoriesModel();
                 $modelCat->updateDateLastPost(date('Y-m-d H:i:s'), $_GET['idcategorie']);
                 
                 $success = FormUtils::successBox('Un nouveau sujet a bien été créé');
                 $controller = new ForumCategoriesController();
                 NotificationModel::fireNotif(NotificationModel::NOTIF_ON_NEW_SUBJECT, 
                         'Nouveau sujet dans '.$location['nom_categorie'].': '.$_POST['sujet'],
                         'request.php?action=ForumCategories&idcategorie='.$_GET['idcategorie'].'#'.$topic);
                 return $success.$controller->getContent();

             }
              else
            {

                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox();
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
            }
//        }
        
    }
    
    public function check(){
        $errors = array();
        // Titre
        if(empty($_POST['sujet'])) {
            $errors['sujet'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le sujet doit avoir un titre');
        }
        // Post
        //L'editeur de texte ajoute automatiquement un <br/>, ce qui empeche le empty de le détecter vide
        //Il ajoute egalement des blocs <div> si quelqu'un fait un retour chariot
        //On supprime les tags ici afin de vérifier que le post est vide      
        $result = strip_tags($_POST['post']);
        if(empty($result))
        {
            $errors['post'] = array('val' => "", 'height' => '150px','status' => TRUE, 'reason' => 'Un nouveau sujet doit avoir un premier post');
        }
        return $errors;
    }
    
    public function getForm($wrong_fields = array()){
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('ForumTopicCreate');

            // Information générales
            $nomSujet = FormUtils::stringBox('sujet', 'Titre : ');
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_TOPIC###",$nomSujet.$post);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumTopicCreate');
            $wrong_fields = $this->processErrors($wrong_fields);

            $nomSujet = FormUtils::stringBox('sujet', 'Titre : ', $wrong_fields['sujet']['val'], $wrong_fields['sujet']['status'], $wrong_fields['sujet']['reason']);
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ', $wrong_fields['post']['val'], $wrong_fields['post']['height'], $wrong_fields['post']['status'], $wrong_fields['post']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_TOPIC###",$nomSujet.$post);


        }
        return $template;
    }
    
    public function processErrors($errors) {
       
        if(!isset($errors['sujet'])){
            $errors['sujet'] = array('val' => $_POST['sujet'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['post'])){
            $errors['post'] = array('val' => $_POST['post'], 'height'=> '300px', 'status' => FALSE,'reason'=>'');
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
        
    }

}
