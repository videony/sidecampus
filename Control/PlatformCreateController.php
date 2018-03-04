<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccueilController
 *
 * @author videony
 */
class PlatformCreateController implements BodyController{
  
    private $css = array(
        'css/form.css'
    );
    private $js = array(
        'js/form.js'
    );
    
    public function canAccess() {
        return ConfigUtils::isPlaformCreateEnabled();
    }
    public function getTitle() {
        return 'Création d\'une plateforme';
    }

    public function getContent() {
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
        {
            $template = GenerateUtils::getTemplate('NewPlatform');
            return GenerateUtils::subPart($template, '###SUB_NOT_CONNECTED###');
        }
        if(isset($_POST['sent']))
            $errors = $this->check();
        else
            return $this->getForm();
        
        if(empty($errors))
        {
            // SUCCESS
            // Création de la plateforme
            $model = new PlatformModel();
            $platformid = $model->create($_POST['nom'], $_POST['annee'], $_POST['section'], 
                            $_POST['description'], $_POST['ecole'], $_POST['cp'], 
                            $_POST['ville']);
            
            // Création de son affiliation à cette plateforme
            $memberModel = new MemberModel();
            $memberid = $memberModel->create($_SESSION[SessionController::ID_PERSONNE], $platformid, MemberModel::ADMIN);
            // Dossier root pour les fichiers
            $model = new FolderModel();
            $model->create('', $_SESSION[SessionController::ID_PERSONNE], NULL, $platformid);
            // Changement de plateforme
            $session = new SessionController(SessionController::NOACTION);
            $session->changePlatform($platformid);
            //$this->sendEmail($platformid);
            return FormUtils::successBox('Votre plateforme a été créée avec succès. Vous en êtes l\'administrateur. '
                    . ' Vous pouvez dès à présent commencer'
                    . ' à l\'utiliser. ');
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
        // Nom
        if(empty($_POST['nom'])) {
            $errors['nom'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le nom de la plateforme doit être mentionné');
        }
        // Ecole
        if(empty($_POST['ecole'])) {
            $errors['ecole'] = array('val' => '', 'status' => TRUE, 'reason' => 'L\'école doit être mentionnée');
        }
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('NewPlatform');

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Nom de la plateforme *');
            $annee = FormUtils::stringBox('annee', 'Année d\'étude ');
            $section = FormUtils::stringBox('section', 'Section ');
            $descr = FormUtils::textAreaBox('description', 'Description générale');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$annee.$section.$descr);

            // Informations de localisation
            $ecole = FormUtils::stringBox('ecole', 'Ecole *');
            $cp = FormUtils::stringBox('cp', 'Code postal ');
            $ville = FormUtils::stringBox('ville', 'Ville ');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_LOCALISATION###", $ecole.$cp.$ville);
            
            // Info message
            $info = FormUtils::infoBox('Beaucoup de champs sont facultatifs. Néanmoins, il est intéressant de les compléter'
                    . ' pour améliorer les recherches.');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_INFOS###", $info);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('NewPlatform');
            $wrong_fields = $this->processErrors($wrong_fields);

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Nom de la plateforme *', $wrong_fields['nom']['val'], $wrong_fields['nom']['status'], $wrong_fields['nom']['reason']);
            $annee = FormUtils::stringBox('annee', 'Année d\'étude', $wrong_fields['annee']['val'], $wrong_fields['annee']['status'], $wrong_fields['annee']['reason']);
            $section = FormUtils::stringBox('section', 'Section d\'étude', $wrong_fields['section']['val'], $wrong_fields['section']['status'], $wrong_fields['section']['reason']);
            $descr = FormUtils::textAreaBox('description', 'Description d\'études', $wrong_fields['description']['val'], $wrong_fields['description']['status'], $wrong_fields['description']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$annee.$section.$descr);

            // Informations de localisation
            $ecole = FormUtils::stringBox('ecole', 'Ecole *', $wrong_fields['ecole']['val'], $wrong_fields['ecole']['status'], $wrong_fields['ecole']['reason']);
            $cp = FormUtils::stringBox('cp', 'Code postal', $wrong_fields['cp']['val'], $wrong_fields['cp']['status'], $wrong_fields['cp']['reason']);
            $ville = FormUtils::stringBox('ville', 'Ville', $wrong_fields['ville']['val'], $wrong_fields['ville']['status'], $wrong_fields['ville']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_LOCALISATION###", $ecole.$cp.$ville);

        }
        return $template;
    }
    public function processErrors($errors) {
        if(!isset($errors['nom'])){
            $errors['nom'] = array('val' => $_POST['nom'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['annee'])){
            $errors['annee'] = array('val' => $_POST['annee'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['section'])){
            $errors['section'] = array('val' => $_POST['section'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['description'])){
            $errors['description'] = array('val' => $_POST['description'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['ecole'])){
            $errors['ecole'] = array('val' => $_POST['ecole'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['cp'])){
            $errors['cp'] = array('val' => $_POST['cp'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['ville'])){
            $errors['ville'] = array('val' => $_POST['ville'], 'status' => FALSE, 'reason'=>'');
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
