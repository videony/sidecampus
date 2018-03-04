<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EditCategorieController
 *
 * @author videony
 */
class ForumCategoriesEditController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/Forum.css',
        'css/ForumCategoriesEdit.css'
    );
    private $js = array(
       'js/ForumCategoriesEdit.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/Forum.js',
        'js/form.js'
    );
    
    public function canAccess() { 
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
        {
            $category_model = new ForumCategoriesModel();                
            $result = $category_model->createdBy($_SESSION[SessionController::ID_MEMBRE],$_GET['idcategorie']);
            if(empty($result))
            {
                //He did not create the category
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OTHERS_CATEGORY");
            } 
            else
            {
                return UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_OWN_CATEGORY");
            }
        } 
        else 
        {
            return FALSE;
        }
    }
    public function getTitle() {
        return 'Modification d\'une catégorie';
    }

    

    public function getContent() {
        GenerateUtils::getTemplate('ForumCategoriesEdit');
        
        if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

             if(empty($errors))
            {
                 //EDIT Categorie
                 $model = new ForumCategoriesModel($_GET['idcategorie']);   
                 $return = $model->set(array( 
                     'nom_categorie'        =>  $_POST['cat']                     
                 ));
                 
                 if($return)
                 {
                    $success = FormUtils::successBox('La catégorie a bien été modifiée');
                    $controller = new ForumController();
                    return $success.$controller->getContent();                     
                 }
                 else
                 {
                    $form = $this->getForm($errors);
                    $errorbox = FormUtils::errorBox("Une erreur est survenue, la catégorie n'a pas été modifiée");
                    return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                 }
                 
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
            $template = GenerateUtils::getTemplate('ForumCategoriesEdit');
            
            $model = new ForumCategoriesModel($_GET['idcategorie']);
            $data = $model->get();
            $nomCat = FormUtils::stringBox('cat', 'Nom : ', $data["nom_categorie"]);
           
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_CAT###",$nomCat);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('ForumCategoriesEdit');
            $wrong_fields = $this->processErrors($wrong_fields);

            $model = new ForumCategoriesModel($_GET['idcategorie']);
            $data = $model->get();
            $nomCat = FormUtils::stringBox('cat', 'Nom : ', $data["nom_categorie"]);
            
            $nomCat = FormUtils::stringBox('cat', 'Nom : ', $wrong_fields['cat']['val'], $wrong_fields['cat']['status'], $wrong_fields['cat']['reason']);
            
            $template = GenerateUtils::replaceSubPart($template, "###SUB_EDIT_CAT###",$nomCat);


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
