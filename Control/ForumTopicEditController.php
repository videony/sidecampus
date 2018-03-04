<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EditTopicController
 *
 * @author videony
 */
class ForumTopicEditController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/ForumTopicEdit.css',
        'css/ForumCategories.css',
        'css/sceditor/themes/office-toolbar.min.css',
        'css/sceditor.css'
    );
    private $js = array(
        'js/form.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/ForumTopicEdit.js',
        'js/ForumCategories.js',
        'js/sceditor/jquery.sceditor.bbcode.min.js',
        'js/sceditor.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
        {
            $topic_model = new ForumTopicModel();  
            $result = $topic_model->createdBy($_SESSION[SessionController::ID_MEMBRE],$_GET['idtopic']);
            
            if(empty($result))
            {
                //He did not create the topic
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OTHERS_SUBJECT");
            } 
            else
            {
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OWN_SUBJECT");
            }
        } 
        else 
        {
            return FALSE;
        }
    }
    public function getTitle() {
        return 'Modification d\'un topic';
    }

    public function getContent() {
        if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

             if(empty($errors))
            {
                 //Modification d'un sujet                
                 $model = new ForumTopicModel($_GET['idtopic']);   
                 
                 $return = $model->set(array( 
                     'nom_topic'        =>  $_POST['sujet'],
                     'first_post'       => str_replace("\n", "<br/>", $_POST['post'])
                 ));
                 
                 if($return)
                 {
                    $success = FormUtils::successBox('Le sujet a bien été modifié');
                    $controller = new ForumCategoriesController();
                    return $success.$controller->getContent();                     
                    
                 }
                 else
                 {
                    $form = $this->getForm($errors);
                    $errorbox = FormUtils::errorBox("Une erreur est survenue, le sujet n'a pas été modifiée");
                    return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                 }
                 
             }
              else
            {

                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox("Une erreur est survenue, le sujet n'a pas été modifiée");
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
        $result = strip_tags($_POST['post']);
        if(empty($result)){
            $errors['post'] = array('val' => "", 'status' => TRUE, 'reason' => 'Un sujet doit avoir un premier post');
        }
        return $errors;
    }
    
    public function getForm($wrong_fields = array()){
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('ForumTopicEdit');

            $model = new ForumTopicModel($_GET['idtopic']);
            $data = $model->get();
            
            $nomSujet = FormUtils::stringBox('sujet', 'Titre : ', $data["nom_topic"]);
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ',preg_replace("/\<br\s*\/?\>/i", "\n", $data["first_post"]));
            
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_TOPIC###",$nomSujet.$post);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumTopicEdit');
            $wrong_fields = $this->processErrors($wrong_fields);

            $nomSujet = FormUtils::stringBox('sujet', 'Titre : ', $wrong_fields['sujet']['val'], $wrong_fields['sujet']['status'], $wrong_fields['sujet']['reason']);
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ', $wrong_fields['post']['val'], '150px', $wrong_fields['post']['status'], $wrong_fields['post']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_TOPIC###",$nomSujet.$post);


        }
        return $template;
    }
    
    public function processErrors($errors) {
       
        if(!isset($errors['sujet'])){
            $errors['sujet'] = array('val' => $_POST['sujet'], 'status' => FALSE, 'reason'=>'');
        }
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
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            default:
                return '';
                break;
        }
    }

}
