<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContactController
 *
 * @author videony
 */
class ContactController implements BodyController{
  
    private $css = array(
        'css/Accueil.css',
        'css/form.css',
        'css/Contact.css'
    );
    private $js = array(
        'js/form.js',
        'js/Contact.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'Contact';
    }

    

    public function getContent() {
        
        $template = GenerateUtils::getTemplate('Contact');
        
        if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();
        
        if(empty($errors))
            {
                if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
                {
                    $from = $_POST['nom']." <".$_POST['email'].">";
                } 
                else
                {
                    $pModel = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
                    $pData = $pModel->get();
                    $from = $pData["tx_prenom"]." ".$pData["tx_nom"]." <".$pData["tx_email"].">";
                }
                $mail = new Mail($from, ConfigUtils::get('contact.email'), $_POST['sujetMessage']);
                $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
                $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
                $mail->addMessagePart(nl2br($_POST['message']), 'text/html');                
                $mail->send();
                
                $success = FormUtils::successBox('Votre mail a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
                $controller = new AccueilController();
                return $success.$controller->getContent();

            }
            else
            {
                $form = $this->getForm($errors);                
                $errorbox = FormUtils::errorBox();
                return GenerateUtils::replaceSubPart($form, "###CONTACT_FORM###", $errorbox);
            }

    }
    
     public function check(){
        $errors = array();
       
        if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR){
            //NOM
            if(empty($_POST['nom'])) {
                $errors['nom'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez entrez votre nom');
            }
        
            // Email
            if(empty($_POST['email'])) {
                $errors['email'] = array('val' => "", 'status' => TRUE, 'reason' => 'Veuillez entrez votre email afin que l\'on puisse vous répondre');           
            }
        }
        
        //Titre message
        if(empty($_POST['sujetMessage'])) {
            $errors['sujetMessage'] = array('val' => "", 'status' => TRUE, 'reason' => 'Veuillez donnez un titre à votre message');           
        }
        
        //Message
        $message=strip_tags($_POST['message']);
         if(empty($message)) {
            $errors['message'] = array('val' => "", 'height' => '150px', 'status' => TRUE, 'reason' => 'Veuillez entrer votre message');           
        }
		
	// Captcha        
        if(isset($_SESSION[SessionController::CAPTCHA])){
            if(!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION[SessionController::CAPTCHA]) {
                $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => TRUE, 'reason' => 'Veuillez entrer une valeur correcte pour le captcha');
            }
        } else {
            $errors['captcha'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le captcha a expiré, veuillez entrer le nouveau.');
        }
        
        
        return $errors;
    }
    
    public function getForm($wrong_fields = array()){
        $template = GenerateUtils::getTemplate('Contact');
        
        if(empty($wrong_fields)) 
        {
            $_SESSION[SessionController::RENEW_CAPTCHA] = TRUE;
            if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
                {            
                    $nom = FormUtils::stringBox('nom', 'Nom* : ');
                    if(!isset($_REQUEST['email']))
                        $email = FormUtils::mailBox('email', 'Email* : ');
                    else
                        $email = FormUtils::mailBox('email', 'Email* : ',$_REQUEST['email']);
                        
                }
                if(!isset($_REQUEST['title']))
                    $titre = FormUtils::stringBox('sujetMessage', 'Sujet de votre message* : ');
                else
                    $titre = FormUtils::stringBox('sujetMessage', 'Sujet de votre message* : ', $_REQUEST['title']);
            $message = FormUtils::textAreaBox('message', 'Votre message* : ');
            $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *');
			if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
            {            
                    $template = GenerateUtils::replaceSubPart($template, "###CONTACT_FORM###",$nom.$email.$titre.$message.$captcha);
            }
            else
            {
                $template = GenerateUtils::replaceSubPart($template, "###CONTACT_FORM###",$titre.$message.$captcha);
            }
                    
        }
        else
        {            
            $wrong_fields = $this->processErrors($wrong_fields);

            if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
            {            
                $nom = FormUtils::stringBox('nom', 'Nom* : ', $wrong_fields['nom']['val'], $wrong_fields['nom']['status'], $wrong_fields['nom']['reason']);
                $email = FormUtils::mailBox('email', 'Email* : ', $wrong_fields['email']['val'], $wrong_fields['email']['status'], $wrong_fields['email']['reason']);
            }
            $titre = FormUtils::stringBox('sujetMessage', 'Sujet de votre message* : ', $wrong_fields['sujetMessage']['val'], $wrong_fields['sujetMessage']['status'], $wrong_fields['sujetMessage']['reason']);
            $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *', $wrong_fields['captcha']['val'], $wrong_fields['captcha']['status'], $wrong_fields['captcha']['reason']);
			$message = FormUtils::textAreaBox('message', 'Votre message* : ', $wrong_fields['message']['val'], $wrong_fields['message']['height'], $wrong_fields['message']['status'], $wrong_fields['message']['reason']);
            if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
            {            
                $template = GenerateUtils::replaceSubPart($template, "###CONTACT_FORM###",$nom.$email.$titre.$message.$captcha);
            } else 
            {
                $template = GenerateUtils::replaceSubPart($template, "###CONTACT_FORM###",$titre.$message.$captcha);
            }
        }
        return $template;
    }
    
    public function processErrors($errors) {
       
            
        if($_SESSION[SessionController::CATEGORIE] == PageController::VISITOR)
        {
            if(!isset($errors['nom'])){
                $errors['nom'] = array('val' => $_POST['nom'], 'status' => FALSE, 'reason'=>'');
            }
            
            if(!isset($errors['email'])){
                $errors['email'] = array('val' => $_POST['email'], 'status' => FALSE,'reason'=>'');
            }   
        }
        if(!isset($errors['sujetMessage'])){
            $errors['sujetMessage'] = array('val' => $_POST['sujetMessage'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['message'])){
            $errors['message'] = array('val' => $_POST['message'], 'height' => '150px', 'status' => FALSE,'reason'=>'');
        }   
		
        if(!isset($errors['captcha'])){
            $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => FALSE, 'reason'=>'');
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
