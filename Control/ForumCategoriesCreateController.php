<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumCategoriesCreateController
 *
 * @author videony
 */
class ForumCategoriesCreateController implements BodyController{
    
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/Forum.css'
        
    );
    private $js = array(
        'js/form.js',
        'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/Forum.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
            return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_CATEGORY');
        else
            return FALSE;
    }
    
    public function getTitle() {
        return 'Création d\'une catégorie';
    }

    

    public function getContent() {
            if(isset($_POST['sent']))
                $errors = $this->check();
            else{
                $form = $this->getForm();
                if(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_CREATE_CATEGORY")
                        && !UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OWN_CATEGORY")){
                    $infobox = FormUtils::infoBox("<b>Attention!</b> En tant que membre, vous avez le droit d'ajouter une catégorie, mais"
                            . " vous n'avez pas le droit de la supprimer. Cela veut dire qu'une fois créée, vous ne pourrez"
                            . " pas la supprimer. Celle-ci pourra être néanmoins supprimée par un administrateur ou par"
                            . " vous si un des administrateurs vous en donne le droit.");
                    return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $infobox);
                }
                return $form;
            }
             

             if(empty($errors))
            {
                 //CREATION SUJET
                 $model = new ForumCategoriesModel();
                 $cat = $model->create(date('Y-m-d H:i:s'), $_POST['cat'], $_SESSION[SessionController::PLATFORM_ID], $_SESSION[SessionController::ID_MEMBRE]);             
                 
                 $success = FormUtils::successBox('Une nouvelle catégorie a bien été créé');
                 $controller = new ForumController();
                 NotificationModel::fireNotif(NotificationModel::NOTIF_ON_NEW_CATEGORY,
                         'Nouvelle catégorie: '.$_POST['cat'],
                         'request.php?action=Forum');
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
        if(empty($_POST['cat'])) {
            $errors['cat'] = array('val' => '', 'status' => TRUE, 'reason' => 'La catégorie doit avoir un nom!');
        }
       
        return $errors;
    }
    
    public function getForm($wrong_fields = array()){
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('ForumCategoriesCreate');

            // Information générales
            $nomCat = FormUtils::stringBox('cat', 'Nom : ');
           
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_CAT###",$nomCat);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumCategoriesCreate');
            $wrong_fields = $this->processErrors($wrong_fields);

            $nomCat = FormUtils::stringBox('cat', 'Nom : ', $wrong_fields['cat']['val'], $wrong_fields['cat']['status'], $wrong_fields['cat']['reason']);
            
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CREATE_CAT###",$nomCat);


        }
        return $template;
    }
    
    public function processErrors($errors) {
       
        if(!isset($errors['cat'])){
            $errors['cat'] = array('val' => $_POST['cat'], 'status' => FALSE, 'reason'=>'');
        }
        
        return $errors;
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }
    
//    
//    public void fireNotif(){
//        
//    }

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
