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
class RegisterController implements BodyController{
  
    private $css = array(
        'css/form.css'
    );
    private $js = array(
        'js/form.js',
        'js/register.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'Inscription';
    }

    

    public function getContent() {
        if(isset($_GET['confirm']))
        {
            $model = new PersonneModel($_GET['id']);
            $infos = $model->get();
            if($infos['confirm'] == 1)
            {
                $msg = FormUtils::errorBox('Ce compte a déjà été confirmé.');
                return $msg;
            }
            if($_GET['code'] == $infos['confirm_code'])
            {
                $model->set(array('confirm' => 1, 'int_categorie' => PageController::MEMBER_NO_DEFAULT_PLATFORM));
                $msg = FormUtils::successBox('Votre compte a bien été confirmé. Vous pouvez dès à présent vous connecter.');
                return $msg;
            }
            else
            {
                $msg = FormUtils::errorBox('Le code de confirmation du compte n\'est pas correct. Veuillez-nous en informer.');
                return $msg;
            }
        }
        if(isset($_POST['sent']))
            $errors = $this->check();
        else
            return $this->getForm();
        
        if(empty($errors))
        {
            // succès
            $confirmCode = sha1($_POST['nom'].$_POST['prenom'].time().rand(0,50000).$_POST['pwd1']);
            $password = $_POST['pwd1'];
            $_POST['nom'] = ucfirst($_POST['nom']);
            $_POST['prenom'] = ucfirst($_POST['prenom']);
            $model = new PersonneModel();
            $id = $model->create($_POST['nom'], $_POST['prenom'], $_POST['login'], 
                            $password, $_POST['mail'], $_POST['gsm'], 
                            ($_POST['dn'] == ''?NULL:date('Y-m-d', strtotime($_POST['dn']))),
                            $confirmCode, PageController::MEMBER_NOT_CONFIRMED, 
                            $_SESSION[SessionController::RAND_STRING]);
            $this->sendEmail($confirmCode,$id);
            $form = $this->getForm();
            $successbox = FormUtils::successBox('Votre compte a été créé avec succès. Un e-mail vous a été envoyé '
                    . 'pour confirmer votre compte. Vous ne pourrez pas vous connecter tant que vous n\'aurez pas '
                    . 'suivi les instructions de cet e-mail. Après confirmation du compte, vous pourrez vous '
                    . 'connecter et utiliser les services de '.ConfigUtils::get('website.title'));
            $successbox.= FormUtils::successBox('Si le mail de confirmation ne vous parvient pas, pensez'
                    . ' à vérifier vos courriers indésirables avant de nous contacter.');
            return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $successbox);
        }
        else
        {
            $form = $this->getForm($errors);
            $errorbox = FormUtils::errorBox();
            $infobox = FormUtils::infoBox('Le mot de passe que vous aurez tapé s\'allongera avant l\'envoi du'
                .' formulaire. Ceci est un processus de sécurité et de protection de votre mot de passe.');
            return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $infobox.$errorbox);
        }
    }
    public function check(){
        $errors = array();
        // Nom
        if(empty($_POST['nom'])) {
            $errors['nom'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le nom est vide');
        }
        if(empty($_POST['prenom'])) {
            $errors['prenom'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le prénom est vide');
        }
        /*if(empty($_POST['dn'])) {
            $errors['dn'] = array('val' => '', TRUE, 'La date de naissance n\'est pas précisée');
        }*/
        if(!empty($_POST['dn']))
        {
            if(preg_match('/([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $_POST['dn']) && strlen($_POST['dn']) == 10)
            {
				$infos = explode('-', $_POST['dn']);
                if(!checkdate($infos[1], $infos[0], $infos[2])) {
                    $errors['dn'] = array('val' => $_POST['dn'], 'status' => TRUE, 'reason' => 'La date de naissance doit être une date valide');
                }
				elseif(strtotime($_POST['dn']) > time()) {
                    $errors['dn'] = array('val' => $_POST['dn'], 'status' => TRUE, 'reason' => 'L\'age négatif n\'existe pas');
				}
            }
            else
            {
                $errors['dn'] = array('val' => $_POST['dn'], 'status' => TRUE, 'reason' => 'Veuillez respecter le format de la date (jj-mm-aaaa)');
            }
        }
        if(empty($_POST['login'])) {
            $errors['login'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le login est vide');
        }
        else {
            $model = new PersonneModel();
            if($model->getByLogin($_POST['login']) != NULL)
            {
                $errors['login'] = array('val' => $_POST['login'], 'status' => TRUE, 'reason' => 'Ce login est déjà utilisé par un autre membre');
            }
        }
        
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
        if(!isset($errors['pwd1']) && !isset($errors['pwd2']) && !isset($_SESSION[SessionController::RAND_STRING]))
        {
            $errors['pwd1'] = array('val' => '', 'status' => TRUE, 'reason' => 'Les deux mots de passe ne correspondent pas');
            $errors['pwd2'] = array('val' => '', 'status' => TRUE, 'reason' => 'Les deux mots de passe ne correspondent pas');
        }
        if(empty($_POST['mail'])) {
            $errors['mail'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le mail n\'est pas valide');
        } 
        else {
             $model = new PersonneModel();
            if($model->getByMail($_POST['mail']) != NULL)
            {
                $errors['mail'] = array('val' => $_POST['mail'], 'status' => TRUE, 'reason' => 'Cette adresse email est déjà utilisée par un autre membre');
            }
        }
        
        if(!isset($_POST['terms_agreement'])) {
            $errors['terms_agreement'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez accepter les conditions d\'utilisation');
        }
        
        if(isset($_SESSION[SessionController::CAPTCHA])){
            if(!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION[SessionController::CAPTCHA]) {
                $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => TRUE, 'reason' => 'Veuillez entrer une valeur correcte pour le captcha');
            }
        } else {
            $errors['captcha'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le captcha a expiré, veuillez entrer le nouveau.');
        }
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        
        if(empty($wrong_fields)) 
        {
            $_SESSION[SessionController::RENEW_CAPTCHA] = TRUE;
            $template = GenerateUtils::getTemplate('Inscription');
            
            // Infos
            $infobox = FormUtils::infoBox('Le mot de passe que vous aurez tapé s\'allongera avant l\'envoi du'
                .' formulaire. Ceci est un processus de sécurité et de protection de votre mot de passe.');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_INFOS###", $infobox);

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Votre nom *');
            $prenom = FormUtils::stringBox('prenom', 'Votre prénom *');
            $dn = FormUtils::stringBox('dn', 'Date de naissance (jj-mm-aaaa)');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$prenom.$dn);

            // Informations de connexion
            $login = FormUtils::stringBox('login', 'Votre login *');
            $mdp1 = FormUtils::passwordBox('pwd1', 'Mot de passe *');
            $mdp2 = FormUtils::passwordBox('pwd2', 'Confirmation *');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONNEXION###", $login.$mdp1.$mdp2);

            // Informations de contact
            $mail = FormUtils::mailBox('mail', 'Votre e-mail *');
            $gsm = FormUtils::telBox('gsm', 'Numéro de GSM');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONTACT###", $mail.$gsm);

            // Agreements
            $agrees = FormUtils::boolBox('terms_agreement', 'En vous inscrivant, vous acceptez nos <u><a href="request.php?action=CGU" target="_blank">conditions d\'utilisation</a></u> *');
            $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_AGREEMENTS###", $captcha.$agrees);
        }
        else
        {
            $_SESSION[SessionController::RENEW_CAPTCHA] = FALSE;
            $template = GenerateUtils::getTemplate('Inscription');
            $wrong_fields = $this->processErrors($wrong_fields);

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Votre nom *', $wrong_fields['nom']['val'], $wrong_fields['nom']['status'], $wrong_fields['nom']['reason']);
            $prenom = FormUtils::stringBox('prenom', 'Votre prénom *', $wrong_fields['prenom']['val'], $wrong_fields['prenom']['status'], $wrong_fields['prenom']['reason']);
            $dn = FormUtils::stringBox('dn', 'Date de naissance (jj-mm-aaaa)', $wrong_fields['dn']['val'], $wrong_fields['dn']['status'], $wrong_fields['dn']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$prenom.$dn);

            // Informations de connexion
            $login = FormUtils::stringBox('login', 'Votre login *', $wrong_fields['login']['val'], $wrong_fields['login']['status'], $wrong_fields['login']['reason']);
            $mdp1 = FormUtils::passwordBox('pwd1', 'Mot de passe *', $wrong_fields['pwd1']['val'], $wrong_fields['pwd1']['status'], $wrong_fields['pwd1']['reason'], FALSE);
            $mdp2 = FormUtils::passwordBox('pwd2', 'Confirmation *', $wrong_fields['pwd2']['val'], $wrong_fields['pwd2']['status'], $wrong_fields['pwd2']['reason'], FALSE);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONNEXION###", $login.$mdp1.$mdp2);

            // Informations de contact
            $mail = FormUtils::mailBox('mail', 'Votre e-mail *', $wrong_fields['mail']['val'], $wrong_fields['mail']['status'], $wrong_fields['mail']['reason']);
            $gsm = FormUtils::telBox('gsm', 'Numéro de GSM', $wrong_fields['gsm']['val'], $wrong_fields['gsm']['status'], $wrong_fields['gsm']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONTACT###", $mail.$gsm);

            // Agreements
            $agrees = FormUtils::boolBox('terms_agreement', 'En vous inscrivant, vous acceptez nos <u><a href="request.php?action=CGU" target="_blank">conditions d\'utilisation</a></u> *', $wrong_fields['terms_agreement']['val'], $wrong_fields['terms_agreement']['status'], $wrong_fields['terms_agreement']['reason']);
            $captcha = FormUtils::captcha('captcha', 'Veuillez recopier le texte de l\'image ci-dessous *', $wrong_fields['captcha']['val'], $wrong_fields['captcha']['status'], $wrong_fields['captcha']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_AGREEMENTS###", $captcha.$agrees);
        }
        return $template;
    }
    public function processErrors($errors) {
        if(!isset($errors['nom'])){
            $errors['nom'] = array('val' => $_POST['nom'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['prenom'])){
            $errors['prenom'] = array('val' => $_POST['prenom'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['dn'])){
            $errors['dn'] = array('val' => $_POST['dn'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['login'])){
            $errors['login'] = array('val' => $_POST['login'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['pwd1'])){
            $errors['pwd1'] = array('val' => $_POST['pwd1'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['pwd2'])){
            $errors['pwd2'] = array('val' => $_POST['pwd2'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['mail'])){
            $errors['mail'] = array('val' => $_POST['mail'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['gsm'])){
            $errors['gsm'] = array('val' => $_POST['gsm'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['terms_agreement'])){
            $errors['terms_agreement'] = array('val' => $_POST['terms_agreement'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['captcha'])){
            $errors['captcha'] = array('val' => $_POST['captcha'], 'status' => FALSE, 'reason'=>'');
        }
        return $errors;
    }
    private function sendEmail($code, $id) {
        $template = file_get_contents("View/confirm.html");
        $template = GenerateUtils::replaceStrings($template, array(
            'link'       =>  ConfigUtils::get('website.url').'/request.php?action=Register&confirm=1&id='.$id.'&code='.$code,
            'nom'         =>  $_POST['nom'],
            'prenom'       => $_POST['prenom'],
            'login'         => $_POST['login'],
            'website' => ConfigUtils::get('website.title')
        ));
        $from = ConfigUtils::get('website.title').' <'.ConfigUtils::get('noreply.email').'>';
        $to = $_POST['prenom'].' '.$_POST['nom']." <".$_POST['mail'].">";
        $mail = new Mail($from, $to, 'Confirmation d\'inscription sur '.ConfigUtils::get('website.title'));
        $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
        $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
        $mail->addMessagePart($template);
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
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'get_login_code':
                if(!isset($_SESSION[SessionController::RAND_STRING]))
                    $_SESSION[SessionController::RAND_STRING] = GenerateUtils::randomString(10).' '.GenerateUtils::randomString(9);
                return $_SESSION[SessionController::RAND_STRING];
                break;
        }
    }

}
