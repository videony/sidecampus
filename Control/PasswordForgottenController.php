<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PasswordForgottenController
 *
 * @author videony
 */
class PasswordForgottenController implements BodyController{
    
    const SEND_EMAIL_MODALITY = 1;
    const CHANGE_PASSWORD_MODALITY = 2;
    private $modality = 1;
  
    private $css = array(
        'css/form.css'
    );
    private $js = array(
       'js/form.js'
    );
    
    public function canAccess() {
        return !isset($_SESSION[SessionController::ID_PERSONNE]);
    }
    public function getTitle() {
        return 'Oubli de mot de passe';
    }

    

    public function getContent() {
        if(isset($_GET['ReinitPassword']))
        {
            $this->modality = self::CHANGE_PASSWORD_MODALITY;
            $id = $_REQUEST['id'];
            $model = new PersonneModel($id);
            $data = $model->get();
            // Si ID incorrect
            if($data == null)
            {
                return FormUtils::errorBox("Requête incorrecte...");
            }
            if($data['reinit_pwd_sent'] == 1 && $_REQUEST['code'] == $data['confirm_code'])
            {
                //$_SESSION[SessionController::RAND_STRING] = $data['tx_login_code'];
                if(isset($_POST['sent']))
                    $errors = $this->check();
                else
                    return $this->getForm();

                if(empty($errors))
                {
                    //Mot de passe changé, changement du code de confirmation 
                    $confirmCode = sha1($data['tx_nom'].$data['tx_prenom'].time().rand(0,50000).$_POST['pwd1']);
                    
                    //enregistrement des changements
                    $model->set(array(
                        'reinit_pwd_sent' => 0,
                        'tx_mdp'    =>  $_POST['pwd1'],
                        'confirm_code' => $confirmCode,
                        'tx_login_code' => $_SESSION[SessionController::RAND_STRING] 
                    ));
                    $successbox = FormUtils::successBox('Votre mot de passe a été changé avec succès');
                    return $successbox;
                }
                else
                {
                    $form = $this->getForm($errors);
                    $errorbox = FormUtils::errorBox();
                    return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                }
            }
            else
            {
                $msg = FormUtils::errorBox('Une erreur s\'est produite. Avez-vous déjà utiliser ce lien pour changer votre mot de passe ?<br/>'
                        . 'Veuillez réeffectuer une demande de changement. Si l\'erreur persiste, veuillez-nous en informer.');
                
                return $msg;
            }
        }
        else
        {
            $this->modality = self::SEND_EMAIL_MODALITY;
            if(isset($_POST['sent']))
                $errors = $this->check();
            else
                return $this->getForm();

            if(empty($errors))
            {
                // succès
                $email = $_POST['email'];
                $model = new PersonneModel();
                $results = $model->getPersonnesByFilter(array('tx_email'=>$email));
                $data = $results[0];
                $this->sendEmail($data['confirm_code'],$data['id_pers']);
                $model = new PersonneModel($data['id_pers']);
                $model->set(array('reinit_pwd_sent' => 1));
                $form = $this->getForm();
                $successbox = FormUtils::successBox('La demande de réinitialisation de mot de passe a été envoyée à l\'adresse '
                        .$email);
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $successbox);
            }
            else
            {
                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox();
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
            }
        }
    }
    public function check(){
        $errors = array();
        
        if($this->modality == self::SEND_EMAIL_MODALITY)
        {
            if(empty($_POST['email'])) {
                $errors['email'] = array('val' => '', 'status' => TRUE, 'reason' => 'L\'e-mail est vide');
            }
            else
            {
                $model = new PersonneModel();
                $results = $model->getPersonnesByFilter(array('tx_email'=>$_POST['email']));
                if(empty($results))
                    $errors['email'] = array('val' => $_POST['email'], 'status' => TRUE, 'reason' => 'Cette adresse e-mail n\'est pas liée à un client');
            }
            
            if(isset ($_SESSION[SessionController::CAPTCHA])){
                if(!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION[SessionController::CAPTCHA]) {
                    $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => TRUE, 'reason' => 'Veuillez entrer une valeur correcte pour le captcha');
                }
            } else {
                $errors['captcha'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le captcha a expiré, veuillez entrer le nouveau.');
            }
        }
        else
        {
            if(empty($_POST['pwd1'])) {
                $errors['pwd1'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le mot de passe est vide');
            }
            if(strlen($_POST['pwd1']) < 5) {
                $errors['pwd1'] = array('val' => $_POST['pwd1'], 'status' => TRUE, 'reason' => 'Le mot de passe doit compter au moins 5 caractères');
            }
            if(empty($_POST['pwd2'])) {
                $errors['pwd2'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le mot de passe est vide');
            }
            if(strlen($_POST['pwd2']) < 5) {
                $errors['pwd2'] = array('val' => $_POST['pwd1'], 'status' => TRUE, 'reason' => 'Le mot de passe doit compter au moins 5 caractères');
            }
            if($_POST['pwd2'] !== $_POST['pwd1']) {
                $errors['pwd2'] = array('val' => $_POST['pwd2'], 'status' => TRUE, 'reason' => 'Les deux mots de passe ne correspondent pas');
            }
        }
        
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        if($this->modality == self::SEND_EMAIL_MODALITY)
        {
            if(empty($wrong_fields)) 
            {
                $_SESSION[SessionController::RENEW_CAPTCHA] = TRUE;
                $template = GenerateUtils::getTemplate('PasswordForgotten');
                $email = FormUtils::mailBox('email', 'Veuillez entrer votre e-mail pour procéder à la récupération. *');
                $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *');
                $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $email.$captcha);
            }
            else
            {
                $_SESSION[SessionController::RENEW_CAPTCHA] = FALSE;
                $template = GenerateUtils::getTemplate('PasswordForgotten');
                $wrong_fields = $this->processErrors($wrong_fields);
                $email = FormUtils::mailBox('email', 'Veuillez entrer votre e-mail pour procéder à la récupération. *', $wrong_fields['email']['val'], $wrong_fields['email']['status'], $wrong_fields['email']['reason']);
                $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *', $wrong_fields['captcha']['val'], $wrong_fields['captcha']['status'], $wrong_fields['captcha']['reason']);
                $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $email.$captcha);
            }
        }
        else
        {
            if(empty($wrong_fields)) 
            {
                $template = GenerateUtils::getTemplate('PasswordForgotten');
                $pwd1 = FormUtils::passwordBox('pwd1', 'Nouveau mot de passe *');
                $pwd2 = FormUtils::passwordBox('pwd2', 'Confirmation *');
                $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $pwd1.$pwd2);
            }
            else
            {
                $template = GenerateUtils::getTemplate('PasswordForgotten');
                $wrong_fields = $this->processErrors($wrong_fields);
                $pwd1 = FormUtils::passwordBox('pwd1', 'Nouveau mot de passe *', $wrong_fields['pwd1']['val'], $wrong_fields['pwd1']['status'], $wrong_fields['pwd1']['reason'], FALSE);
                $pwd2 = FormUtils::passwordBox('pwd2', 'Confirmation *', $wrong_fields['pwd2']['val'], $wrong_fields['pwd2']['status'], $wrong_fields['pwd2']['reason'], FALSE);
                $template = GenerateUtils::replaceSubPart($template, "###SUB_FORM###", $pwd1.$pwd2);
            }
        }
        return $template;
    }
    public function processErrors($errors) {
        if($this->modality == self::SEND_EMAIL_MODALITY)
        {
            if(!isset($errors['email'])){
                $errors['email'] = array('val' => $_POST['email'], 'status' => FALSE, 'reason'=>'');
            }
            if(!isset($errors['captcha'])){
                $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => FALSE, 'reason'=>'');
            }
        }
        else
        {
            if(!isset($errors['pwd1'])){
                $errors['pwd1'] = array('val' => $_POST['pwd1'], 'status' => FALSE, 'reason'=>'');
            }
            if(!isset($errors['pwd2'])){
                $errors['pwd2'] = array('val' => $_POST['pwd2'], 'status' => FALSE, 'reason'=>'');
            }
        }
       
        return $errors;
    }
    private function sendEmail($code, $id) {
        $template = file_get_contents("View/reinit_password.html");
        $model = new PersonneModel($id);
        $data = $model->get();
        $domain = ConfigUtils::get('website.url');
        $template = GenerateUtils::replaceStrings($template, array(
            'link'       =>  $domain.'/request.php?action=PasswordForgotten&ReinitPassword=1&id='.$id.'&code='.$code,
            'nom'         =>  $data['tx_nom'],
            'prenom'       => $data['tx_prenom'],
            'login'         => $data['tx_login'],
            'website' => ConfigUtils::get('website.title')
        ));
        $from = ConfigUtils::get('website.title')." <".ConfigUtils::get('noreply.email').">";
        $to = $data['tx_prenom'].' '.$data['tx_nom']."<".$data['tx_email'].">";
        $mail = new Mail($from, $to, 'Demande de changement de mot de passe sur '.ConfigUtils::get('website.title'));
        $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
        $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
        $mail->addMessagePart($template, "text/html");
        if (!$mail->send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
        } 
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
