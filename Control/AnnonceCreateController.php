<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnnonceCreateController
 *
 * @author videony
 */
class AnnonceCreateController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/AnnonceCreate.css'
    );
    private $js = array(
        'js/form.js',
       'js/AnnonceCreate.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'Nouvelle annonce';
    }

    

    public function getContent() {
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
        {
            $template = GenerateUtils::getTemplate('AnnonceCreate');
            return GenerateUtils::subPart($template, '###SUB_NOT_CONNECTED###');
        }
        if(isset($_POST['sent']))
            $errors = $this->check();
        else
            return $this->getForm();
        
        if(empty($errors))
        {
            // SUCCESS
            // Création de l'annonce
            $model = new AnnonceModel();
            $id = $model->create($_SESSION[SessionController::ID_PERSONNE], 
                    $_POST['prix'], $_POST['description'], $_POST['titre'], date('Y-m-d H:i:s', time()));
            
            header('Location: request.php?action=Annonces&mine=1');
        }
        else
        {
            $form = $this->getForm($errors);
            $errorbox = FormUtils::errorBox();
            return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
        }
    }
    public function check(){
        $errors = array();
        // Titre
        if(empty($_POST['titre'])) {
            $errors['titre'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez préciser un libellé pour cette annonce');
        }
        $_POST['prix'] = str_replace(',', '.', $_POST['prix']);
        if(!is_numeric($_POST['prix'])) {
            $errors['prix'] = array('val' => $_POST['prix'], 'status' => TRUE, 'reason' => 'Veuillez préciser un nombre');
        }
        // Description
        if(empty($_POST['description'])) {
            $errors['description'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez décrire brièvement votre annonce');
        }
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('AnnonceCreate');

            // Information générales
            $titre = FormUtils::stringBox('titre', 'Intitulé de l\'annonce *');
            $prix = FormUtils::stringBox('prix', 'Prix *');
            $descr = FormUtils::textAreaBox('description', 'Courte description de l\'annonce *');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $titre.$prix.$descr);
        }
        else
        {
            $template = GenerateUtils::getTemplate('AnnonceCreate');
            $wrong_fields = $this->processErrors($wrong_fields);

            // Information générales
            $titre = FormUtils::stringBox('titre', 'Intitulé de l\'annonce *', $wrong_fields['titre']['val'], $wrong_fields['titre']['status'], $wrong_fields['titre']['reason']);
            $prix = FormUtils::stringBox('prix', 'Prix *', $wrong_fields['prix']['val'], $wrong_fields['prix']['status'], $wrong_fields['prix']['reason']);
            $descr = FormUtils::textAreaBox('description', 'Courte description de l\'annonce *', $wrong_fields['description']['val'], '150px', $wrong_fields['description']['status'], $wrong_fields['description']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $titre.$prix.$descr);

        }
        return $template;
    }
    public function processErrors($errors) {
        if(!isset($errors['titre'])){
            $errors['titre'] = array('val' => $_POST['titre'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['prix'])){
            $errors['prix'] = array('val' => $_POST['prix'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['description'])){
            $errors['description'] = array('val' => $_POST['description'], 'status' => FALSE, 'reason'=>'');
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
