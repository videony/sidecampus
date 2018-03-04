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
class CompteController implements BodyController{  
    private $css = array(
        'css/form.css',
        'css/uploadify.css',
        'css/Compte.css'
    );
    private $js = array(
        'js/jquery-ui/ui/jquery.ui.tabs.js',
        'js/form.js',
        'js/plupload/plupload.full.min.js',
        'js/Compte.js',
        'js/settings.js'
    );
    
    public function canAccess() {
        // Juste besoin de vérifier qu'il est connecté. 
        if(isset($_SESSION[SessionController::ID_PERSONNE]))
            return TRUE;
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Mon compte';
    }

    

    public function getContent() {
        if(isset($_REQUEST['change_profile_picture']))
            return $this->handleChangeProfilePictureRequest();
        
        if(!empty($_POST['compte'])){
            $errors = $this->check();
        }
        else
        {
            //Si form de notif posté : sauvegarde
            if(!empty($_POST['notifs'])){
                $model = new UserSettingModel();
                $settings = $model->getUserSettingByFilter(array(
                'id_membre'     =>  $_SESSION[SessionController::ID_MEMBRE]
                ));
                foreach($settings as $key=>$set){                    
                    $model2 = new UserSettingModel($set["id_us"]);
                    $usdata = $model2->get();
                    // On ne sauvegarde que s'il a le droit d'avoir ce setting
                    // NULL = irrelevant pour lui
                    if($usdata['value'] != NULL)
                    {
                        if(isset($_POST[$set["setting_name"]])){
                            $model2->set(array(
                                'value' => 1
                                ));
                        }
                        else {                        
                            $model2->set(array(
                                'value' => 0
                                ));
                        }
                    }
                }  
                       
                
            }
         
            $template = $this->getForm();
            return $template;
        }
        
        if(empty($errors))
        {
            // succès
            $model = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
            $return = $model->set(array(
                    'tx_nom'        =>  $_POST['nom'], 
                    'tx_prenom'     =>  $_POST['prenom'], 
                    'tx_email'      =>  $_POST['mail'], 
                    'tx_gsm'        =>  $_POST['gsm'], 
                    'dt_naissance'  =>  ($_POST['dn'] == ''?NULL:date('Y-m-d', strtotime($_POST['dn'])))
            ));
            if(isset($_POST['connexion']))
            {
                $return2 = $model->set(array(
                    'tx_login'      =>  $_POST['login'], 
                    'tx_mdp'        =>  $_POST['pwd1']
                ));
            }
            else
            {
                $return2 = TRUE;
            }
            // On actualise les informations de la personne
            SessionController::updatePersonInfo($_SESSION[SessionController::ID_PERSONNE]);
            if($return&&$return2 == TRUE)
            {
                $form = $this->getForm();
                $successbox = FormUtils::successBox('Les informations sur votre compte ont bien été sauvegardées.');
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $successbox);
            }
            else
            {
                $form = $this->getForm();
                $errorbox = FormUtils::errorBox('Les informations n\'ont pas pu être sauvées correctement. '
                        . 'Un rapport de l\'erreur a été envoyé aux webmasters.');
                return GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
            }
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
        if(isset($_POST['connexion']))
        {
            if(empty($_POST['login'])) {
                $errors['login'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le login est vide');
            }
            elseif($_POST['login'] != $_SESSION[SessionController::USERNAME]) {
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
        }
        if(empty($_POST['mail'])) {
            $errors['mail'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le mail n\'est pas valide');
        } else {
            $model = new PersonneModel();        
            if($model->isSameMail($_POST['mail'], $_SESSION[SessionController::USERNAME]) == NULL)
            {
                if($model->getByMail($_POST['mail']) != NULL)
                {
                    $errors['mail'] = array('val' => $_POST['mail'], 'status' => TRUE, 'reason' => 'Cette adresse email est déjà utilisée par un autre membre');
                }
            }
        }
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        $template = GenerateUtils::getTemplate('Compte');
        if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM){
            $template = GenerateUtils::replaceStrings($template, array('settings' => $this->getSettingsHTML()));
        }
        else{
            $template = GenerateUtils::replaceStrings ($template, array(
                'if_settings'   =>  FALSE,
                'if_settings_header'    => FALSE

            ));
        }
        if(empty($wrong_fields)) 
        {
            $model = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
            $data = $model->get();

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Votre nom *', $data['tx_nom']);
            $prenom = FormUtils::stringBox('prenom', 'Votre prénom *', $data['tx_prenom']);
            if($data['dt_naissance'] != NULL)
                $dn = FormUtils::dateBox('dn', 'Date de naissance', date('d-m-Y', strtotime($data['dt_naissance'])));
            else
                $dn = FormUtils::dateBox('dn', 'Date de naissance');
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$prenom.$dn);

            // Informations de connexion
            if($_SESSION[SessionController::ID_PERSONNE] != 0)
            {
                $login = FormUtils::stringBox('login', 'Votre login *', $data['tx_login']);
                $mdp1 = FormUtils::passwordBox('pwd1', 'Mot de passe *', $data['tx_mdp'], FALSE, '', FALSE);
                $mdp2 = FormUtils::passwordBox('pwd2', 'Confirmation *', $data['tx_mdp'], FALSE, '', FALSE);
                $template = GenerateUtils::replaceSubPart($template, "###SUB_CONNEXION###", $login.$mdp1.$mdp2);
            }

            // Informations de contact
            $mail = FormUtils::mailBox('mail', 'Votre e-mail *', $data['tx_email']);
            $gsm = FormUtils::telBox('gsm', 'Numéro de GSM', $data['tx_gsm']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONTACT###", $mail.$gsm);
            
            $template = GenerateUtils::replaceStrings($template, array('checked' => ''));
        }
        else
        {
            $wrong_fields = $this->processErrors($wrong_fields);

            // Information générales
            $nom = FormUtils::stringBox('nom', 'Votre nom *', $wrong_fields['nom']['val'], $wrong_fields['nom']['status'], $wrong_fields['nom']['reason']);
            $prenom = FormUtils::stringBox('prenom', 'Votre prénom *', $wrong_fields['prenom']['val'], $wrong_fields['prenom']['status'], $wrong_fields['prenom']['reason']);
            $dn = FormUtils::dateBox('dn', 'Date de naissance', $wrong_fields['dn']['val'], $wrong_fields['dn']['status'], $wrong_fields['dn']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_GENERAL###", $nom.$prenom.$dn);

            // Informations de connexion
            if($_SESSION[SessionController::ID_PERSONNE] != 0)
            {
                $login = FormUtils::stringBox('login', 'Votre login *', $wrong_fields['login']['val'], $wrong_fields['login']['status'], $wrong_fields['login']['reason']);
                $mdp1 = FormUtils::passwordBox('pwd1', 'Mot de passe *', $wrong_fields['pwd1']['val'], $wrong_fields['pwd1']['status'], $wrong_fields['pwd1']['reason'], FALSE);
                $mdp2 = FormUtils::passwordBox('pwd2', 'Confirmation *', $wrong_fields['pwd2']['val'], $wrong_fields['pwd2']['status'], $wrong_fields['pwd2']['reason'], FALSE);
                $template = GenerateUtils::replaceSubPart($template, "###SUB_CONNEXION###", $login.$mdp1.$mdp2);
            }

            // Informations de contact
            $mail = FormUtils::mailBox('mail', 'Votre e-mail *', $wrong_fields['mail']['val'], $wrong_fields['mail']['status'], $wrong_fields['mail']['reason']);
            $gsm = FormUtils::telBox('gsm', 'Numéro de GSM', $wrong_fields['gsm']['val'], $wrong_fields['gsm']['status'], $wrong_fields['gsm']['reason']);
            $template = GenerateUtils::replaceSubPart($template, "###SUB_CONTACT###", $mail.$gsm);
            
            if(isset($_POST['connexion']))
                $template = GenerateUtils::replaceStrings($template, array('checked' => 'checked'));
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
        return $errors;
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    public function handlePostRequest() {
        if(!isset($_REQUEST['action']))
           return '';
        switch($_REQUEST['action'])
        {
            case 'reset_default_settings':
                $model = new UserSettingModel();
                $return = $model->setDefaultSettings($_SESSION[SessionController::ID_MEMBRE]);                
                if($return == TRUE)
                    echo 'OK';
                else
                    echo 'KO';
                break;
            case 'notification_mark_as_read':
                if(!isset($_SESSION[SessionController::ID_MEMBRE]))
                {
                    header('HTTP/1.0 403 Forbidden');
                    return;
                }
                NotificationModel::resetCompteur();
                return NotificationModel::getTotalNbNotif();
                break;   
            case 'get_nb_notifs':
                if(!isset($_SESSION[SessionController::ID_MEMBRE]))
                {
                    header('HTTP/1.0 403 Forbidden');
                    return;
                }
                return json_encode(array(NotificationModel::getNbNotif(), NotificationModel::getTotalNbNotif()));
                break;
            case 'get_notifications':
                if(!isset($_SESSION[SessionController::ID_MEMBRE]))
                {
                    header('HTTP/1.0 403 Forbidden');
                    return;
                }
                $template = GenerateUtils::getTemplate('Page');
                $notifications = GenerateUtils::subPart($template, "###SUB_NOTIFICATIONS###");
                $notifsarray = NotificationModel::getNotifsOfMembre();
                $nbnotifs = NotificationModel::getNbNotif();
                $compteur = 1;
                foreach($notifsarray as $notifkey=>$notifdata)
                {
                    $notifsarray[$notifkey]['notif_date'] = date('d-m-Y H:i', strtotime($notifdata['notif_date']));
                    $notifsarray[$notifkey]['notif_section'] = str_replace(' ', '_', $notifdata['notif_section']);
                    if($compteur > $nbnotifs)
                        $notifsarray[$notifkey]['is_read'] = "read";
                    else
                        $notifsarray[$notifkey]['is_read'] = "notread";
                    $compteur++;
                }
                $notifications = GenerateUtils::generateRS($notifications, $notifsarray);
                return $notifications;
                break;
            case 'more_notifications':
                if(!isset($_SESSION[SessionController::ID_MEMBRE]))
                {
                    header('HTTP/1.0 403 Forbidden');
                    return;
                }
                $template = GenerateUtils::getTemplate('Page');
                $notifications = GenerateUtils::subPart($template, "###SUB_NOTIFICATIONS_ONLY###");
                $notifsarray = NotificationModel::getNotifsOfMembre(NotificationModel::NB_NOTIFS_DEFAULT, $_REQUEST['current']);
                foreach($notifsarray as $notifkey=>$notifdata)
                {
                    $notifsarray[$notifkey]['notif_date'] = date('d-m-Y H:i', strtotime($notifdata['notif_date']));
                    $notifsarray[$notifkey]['notif_section'] = str_replace(' ', '_', $notifdata['notif_section']);
                    $notifsarray[$notifkey]['is_read'] = "read";
                }
                $notifications = GenerateUtils::generateRS($notifications, $notifsarray);
                return $notifications;
                break;
            case 'next_platform':
            case 'previous_platform':
                $memberModel = new MemberModel();
                $memberships = $memberModel->getMembersByFilter(array('id_personne' => $_SESSION[SessionController::ID_PERSONNE]));
                //var_dump($memberships);
                while($membership = current($memberships))
                {
                    if($membership['id_membre'] == $_SESSION[SessionController::NOTIFICATION_MEMBER_ID])
                    {
                        // On est arrivé à la bonne plateforme
                        if($_REQUEST['action'] == 'next_platform')
                        {
                            $ent = next($memberships);
                            if($ent === FALSE)
                                $ent = reset($memberships);
                        }
                        else
                        {
                            $ent = prev($memberships);
                            if($ent === FALSE)
                                $ent = end($memberships);
                        }
                        $_SESSION[SessionController::NOTIFICATION_MEMBER_ID] = $ent['id_membre'];
                        $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID] = $ent['id_plateforme'];
                        $model = new PlatformModel($ent['id_plateforme']);
                        $platform = $model->get();
                        return json_encode(array($platform['id_plateforme'], strtoupper($platform['tx_nom'])));
                    }
                    else
                    {
                        next($memberships);
                    }
                }
                break;
            case 'notification_click':
                if($_SESSION[SessionController::NOTIFICATION_PLATFORM_ID] 
                        != $_SESSION[SessionController::PLATFORM_ID])
                {
                    // Need to change platform in session
                    // Because click on notification of other platform
                    $sess = new SessionController();
                    $sess->changePlatform($_SESSION[SessionController::NOTIFICATION_PLATFORM_ID]);
                }
                break;
            default:
                return '';
                break;
        }
    }
    private function handleChangeProfilePictureRequest() {   
        
        if (empty($_FILES) || $_FILES["file"]["error"]) {
            die('{"OK": 0}');
          }
        
    require_once 'Utils/SimpleImage.php';
        $targetFolder = getcwd().'/media'; // Relative to the root

        if (!empty($_FILES) && isset($_SESSION['id_user'])) {
                $tempFile = $_FILES['file']['tmp_name'];
                $targetPath = $targetFolder;
                $targetFile = rtrim($targetPath,'/') . '/' . $_SESSION['id_user'].'.jpg';

                // Validate the file type
                $fileTypes = array('jpg','jpeg', 'JPG', 'JPEG'); // File extensions
                $fileParts = pathinfo($_FILES['file']['name']);

                if (in_array($fileParts['extension'],$fileTypes)) {
                    
                    $image = new SimpleImage();
                    $image->load($tempFile); 
                    $image->resizeToWidth(500);
                    $image->save($tempFile);
                    $imgData = file_get_contents($tempFile);
                    $person = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
                    $person->set(array(
                        'img_profile_pic'   =>  $imgData,
                        'tx_profile_pic'    =>  $tempFile
                    ));
                } else {
                        var_dump('Veuillez fournir un fichier de type jpg');
                }
        }
    }
    public function getProfilePicData($personid) {
        if($this->canSeeProfilePic($personid))
        {
            $model = new PersonneModel($personid);
            $person = $model->get();
            return $person['img_profile_pic'];
        }
        else
        {
            return file_get_contents(WEB_ROOT."media/pics/default_profile_pic.png");
        }
    }
    private function canSeeProfilePic($personid)
    {
        // Connecté?
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
            return FALSE;
        // Peut voir son propre profil
        if($personid == $_SESSION[SessionController::ID_PERSONNE])
            return TRUE;
        // Si pas son propre profil, non si il n'est affilié à aucune plateforme
        if($_SESSION[SessionController::CATEGORIE] == PageController::MEMBER_NO_DEFAULT_PLATFORM)
            return FALSE;
        else
        {
            // A-t-il le droit de visualiser les profils sur la plateforme?
            if(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SEE_OTHERS_PROFILE"))
            {
                // La personne est-elle inscrite à la plateforme courante?
                $model = new MemberModel();
                $result = $model->getMembersByFilter(array(
                    'id_plateforme' =>  $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID],
                    'id_personne'   =>  $personid
                ));
                if(!empty($result))
                    return TRUE;
                else
                {
                    if(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_ACCEPT_USER"))
                    {
                        // La personne a-t-elle demandé à rejoindre la plateforme?
                        $model = new DemandeModel();
                        $result = $model->getDemandesByFilter(array(
                            'id_plateforme' =>  $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID],
                            'id_personne'   =>  $personid
                        ));
                        return !empty($result);
                    }
                    else
                        return FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
    }
    
    
    
    public function getSettingsHTML(){
        $files_html = '';
        $event_html = '';
        $forum_html = '';
        $manage_html = '';
        $divers_html = '';
        $model = new UserSettingModel();
        $settings = $model->getUserSettingByFilter(array(
            'id_membre'     =>  $_SESSION[SessionController::ID_MEMBRE]
        ));
        foreach($settings as $key=>$set)
        {
            //var_dump($set['setting_name'].' => '.$set['value']);
            if($set['value'] != null)
            {
                $pmodel = new SettingModel($set['setting_name']);
                $setdata = $pmodel->get();
                $html = FormUtils::boolBox($set['setting_name'], 
                                            $setdata['tx_description'], 
                                            ($set['value'] == 1)?TRUE:FALSE);
                switch($setdata['section'])
                {
                    case PermissionModel::SECTION_FILES:
                        $files_html .= $html;
                        break;
                    case PermissionModel::SECTION_EVENTS:
                        $event_html .= $html;
                        break;
                    case PermissionModel::SECTION_FORUM:
                        $forum_html .= $html;
                        break;
                    case PermissionModel::SECTION_PLATFORM_MANAGE:
                        $manage_html .= $html;
                        break;
                    default:
                        $divers_html .= $html;
                        break;

                }
            }
        }
        $template = GenerateUtils::getTemplate('settings');
        $template = GenerateUtils::replaceStrings($template, array(
            'if_file_settings'  =>  !empty($files_html),
            'file_settings'      =>  $files_html,
            'if_event_settings'  =>  !empty($event_html),
            'event_settings'      =>  $event_html,
            'if_forum_settings'  =>  !empty($forum_html),
            'forum_settings'     =>  $forum_html,
            'if_manage_settings'  =>  !empty($manage_html),
            'manage_settings'    =>  $manage_html,
            'if_divers_settings'  =>  !empty($divers_html),
            'divers_settings'    => $divers_html,
            'id_membre'     =>  $_SESSION[SessionController::ID_MEMBRE]
        ));
        return $template;
    }

}
