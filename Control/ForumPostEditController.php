<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EditPostController
 *
 * @author videony
 */
class ForumPostEditController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/ForumPostEdit.css',
        'css/ForumTopic.css',
        'css/sceditor/themes/office-toolbar.min.css',
        'css/sceditor.css'
    );
    private $js = array(
        'js/form.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/ForumPostEdit.js',
        'js/sceditor/jquery.sceditor.bbcode.min.js',
        'js/sceditor.js'
    );
    
    public function canAccess() {
        
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
        {
            $post_model = new ForumPostModel();    
            $result = $post_model->createdBy($_SESSION[SessionController::ID_MEMBRE], $_GET['idpost']); 
            
            if(empty($result))
            {
                //He did not create the topic
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OTHERS_MESSAGE");
            } 
            else
            {
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OWN_MESSAGE");
            }
        } 
        else 
        {
            return FALSE;
        }
    }
    public function getTitle() {
        return 'Modification d\'un post';
    }

    

    public function getContent() {
        if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

             if(empty($errors))
            {
                 //Modification d'un message                
                 $model = new ForumPostModel($_GET['idpost']);   
                 
                 $return = $model->set(array(                      
                     'post'       => str_replace("\n", "<br/>", $_POST['post'])
                 ));
                 
                 if($return)
                 {
                    //$success = FormUtils::successBox('Le message a bien été modifié');
                    //$controller = new ForumTopicController();
                     
                    $header = header("Location: request.php?action=ForumTopic&idcategorie=".$_GET['idcategorie']."&idtopic=".$_GET['idtopic']);
                    
                    return $header->getContent();                     
                    
                 }
                 else
                 {
                    $form = $this->getForm($errors);
                    $errorbox = FormUtils::errorBox("Une erreur est survenue, le message n'a pas été modifiée");
                    return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                 }
                 
             }
              else
            {

                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox("Une erreur est survenue, le message n'a pas été modifiée");
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
            }
//        }
        
    }
    
    public function check(){
        $errors = array();
        // Post
        $result = strip_tags($_POST['post']);
        if(empty($result)) {
            $errors['post'] = array('val' => "", 'status' => TRUE, 'reason' => 'Le post ne peut pas être vide.');
        }
        return $errors;
    }
    
    public function getForm($wrong_fields = array()){
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('ForumPostEdit');

            $model = new ForumPostModel($_GET['idpost']);
            $data = $model->get();
                        
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ',preg_replace("/\<br\s*\/?\>/i", "\n", $data["post"]));
            
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_POST###",$post);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumPostEdit');
            $wrong_fields = $this->processErrors($wrong_fields);
            
            $post = FormUtils::textAreaBoxFormated('post', 'Post : ', $wrong_fields['post']['val'], '150px', $wrong_fields['post']['status'], $wrong_fields['post']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_POST###",$post);


        }
        return $template;
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
