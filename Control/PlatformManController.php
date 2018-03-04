<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlatformManController
 *
 * @author videony
 */
class PlatformManController implements BodyController{
  
    private $css = array(
        'css/form.css'
        
    );
    private $js = array(
       'js/form.js',
        'js/PlatformMan.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
            return (UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_PLATFORM"));
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Gestion de la plateforme';
    }

    

    public function getContent() {
        if(isset($_POST['sent']))
            $errors = $this->check();
        else
            return $this->getForm(array());
        
        if(empty($errors))
        {
            // SUCCESS
            // Création de la plateforme
            $model = new PlatformModel($_SESSION[SessionController::PLATFORM_ID]);
            $model->set(array(
                'tx_nom'        =>  $_POST['nom'], 
                'annee'         =>  $_POST['annee'], 
                'section'       =>  $_POST['section'], 
                'descriptif'    =>  $_POST['description'], 
                'ecole'         =>  $_POST['ecole'], 
                'cp'            =>  $_POST['cp'], 
                'ville'         =>  $_POST['ville']
            ));
            
            NotificationModel::fireNotif(NotificationModel::NOTIF_ON_PLATFORM_EDIT,
                    'Plateforme éditée',
                        'request.php?action=PlatformMan');
            // On change la plateforme pour être sur que la session soit en bon état
            //$session = new SessionController(SessionController::NOACTION);
            //$session->changePlatform($_SESSION[SessionController::PLATFORM_ID]);
            $_SESSION[SessionController::PLATFORM_NAME] = $_POST['nom'];
            
            return FormUtils::successBox('Votre plateforme a été modifiée avec succès');
        }
        else
        {
            $form = $this->getForm($errors);
            $errorbox = FormUtils::errorBox();
            return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
        }
    }
    
    public function getForm($wrong_fields = array()) {
        $model = new PlatformModel($_SESSION[SessionController::PLATFORM_ID]);
        $platformdata = $model->get();
        
        if(empty($wrong_fields)) 
        {
            $template = GenerateUtils::getTemplate('EditPlatform');

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Nom de la plateforme *', $platformdata['tx_nom']);
            $annee = FormUtils::stringBox('annee', 'Année d\'étude ', $platformdata['annee']);
            $section = FormUtils::stringBox('section', 'Section ', $platformdata['section']);
            $descr = FormUtils::textAreaBox('description', 'Description générale', $platformdata['descriptif']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$annee.$section.$descr);

            // Informations de localisation
            $ecole = FormUtils::stringBox('ecole', 'Ecole *', $platformdata['ecole']);
            $cp = FormUtils::stringBox('cp', 'Code postal ', $platformdata['cp']);
            $ville = FormUtils::stringBox('ville', 'Ville ', $platformdata['ville']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_LOCALISATION###", $ecole.$cp.$ville);
            
            // Info message
            $info = FormUtils::infoBox('Beaucoup de champs sont facultatifs. Néanmoins, il est intéressant de les compléter'
                    . ' pour améliorer les recherches.');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_INFOS###", $info);
                    
        }
        else
        {
            $template = GenerateUtils::getTemplate('EditPlatform');
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
        if($platformdata['visible'] == 1)
            $template = GenerateUtils::emptyPart($template, 'IF_PLATFORM_HIDDEN');
        else
            $template = GenerateUtils::emptyPart($template, 'IF_PLATFORM_NOT_HIDDEN');
            
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
            case 'hide_platform':
                $model = new PlatformModel($_SESSION[SessionController::PLATFORM_ID]);
                $model->set(array(
                    'visible'   =>  0
                ));
                break;
            case 'unhide_platform':
                $model = new PlatformModel($_SESSION[SessionController::PLATFORM_ID]);
                $model->set(array(
                    'visible'   =>  1
                ));
                break;
            default:
                return '';
                break;
        }
    }

}
