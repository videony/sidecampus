<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlatformAskingUsersController
 *
 * @author videony
 */
class PlatformAskingUsersController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/PlatformAskingUsers.css'
    );
    private $js = array(
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
       'js/PlatformAskingUsers.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
            return (UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_ACCEPT_USER"));
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Utilisateurs en demande';
    }

    

    public function getContent() {
       $template = GenerateUtils::getTemplate('PlatformAskingUsers');
        $markers['information'] = FormUtils::infoBox('Cliquez sur les en-têtes de colonne pour trier en ordre croissant '
                . 'ou décroissant');
        $template = GenerateUtils::replaceStrings($template, $markers);
        
        // Permissions
        $permissionmodel = new UserPermissionModel();
        $norights = $permissionmodel->getUserPermissionByFilter(array(
           'id_membre'  =>  $_SESSION[SessionController::ID_MEMBRE],
            'value'     =>  0
        ));
        $template = GenerateUtils::cleanForRights($template, $norights);
        
        $model = new DemandeModel();
        $demandes = $model->getDemandesByFilter(array(
           'id_plateforme'  =>  $_SESSION[SessionController::PLATFORM_ID] 
        ));
        if(empty($demandes))
        {
            return '';
        }
        
        foreach($demandes as $key=>$demande)
        {
            $model = new PersonneModel($demande['id_personne']);
            $personne = $model->get();
            $demandes[$key]['tx_nom'] = $personne['tx_nom'];
            $demandes[$key]['tx_prenom'] = $personne['tx_prenom'];
            $demandes[$key]['tx_email'] = $personne['tx_email'];
            $demandes[$key]['tx_login'] = $personne['tx_login'];
            if($demande['status'] == DemandeModel::BANNED)
            {
                $demandes[$key]['banned'] = 'banned';
                $demandes[$key]['if_can_ban_user'] = FALSE;
            }
            else
            {
                $demandes[$key]['banned'] = '';
                $demandes[$key]['if_can_unban_user'] = FALSE;
            }
        }
        
        $template = GenerateUtils::generateRS($template, $demandes);
        
        
        return $template;
    }
    
     private function sendEmail($persondata) {
        $template = file_get_contents("View/platformAccept.html");
        $template = GenerateUtils::replaceStrings($template, array(
            'link'          => ConfigUtils::get('website.url'),
            'nom'           => $persondata['tx_nom'],
            'prenom'        => $persondata['tx_prenom'],
            'plateforme'    => $_SESSION[SessionController::PLATFORM_NAME],
            'website' => ConfigUtils::get('website.title')
        ));
        $from = ConfigUtils::get('website.title')." <".ConfigUtils::get('noreply.email').">";
        $to = $persondata['tx_prenom'].' '.$persondata['tx_nom']." <".$persondata['tx_email'].">";
        $mail = new Mail($from, $to, 'Vous avez été accepté sur la plateforme');
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
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'accept_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_ACCEPT_USER"))
                        return '';
                $model = new DemandeModel($_POST['id']);
                $demande = $model->get();
                if($demande == null) // Déjà accepté
                    return 'OK';
                $memberModel = new MemberModel();
                $personModel = new PersonneModel($demande['id_personne']);
                $persondata = $personModel->get();
                echo '<br/>'.$_SESSION[SessionController::PLATFORM_ID];
                $id = $memberModel->create($demande['id_personne'], $_SESSION[SessionController::PLATFORM_ID]);                
                $model->remove();
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_ACCEPT_USER, 
                        'Utilisateur accepté sur la plateforme: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$id);
                $this->sendEmail($persondata);
                return 'OK';
                break;
            case 'reject_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REJECT_USER"))
                        return '';
                $model = new DemandeModel($_POST['id']);
                $data = $model->get();
                $personModel = new PersonneModel($data['id_personne']);
                $persondata = $personModel->get();
                $model->remove();
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_EJECT_USER,
                        'Utilisateur rejeté de la plateforme: '.$persondata['tx_prenom'].' '.$persondata['tx_nom']);
                return 'OK';
                break;
            case 'ban_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_BAN_USER"))
                        return '';
                $model = new DemandeModel($_POST['id']);
                $data = $model->get();
                var_dump($data);
                $personModel = new PersonneModel($data['id_personne']);
                $persondata = $personModel->get();
                $model->set(array(
                   'status' => DemandeModel::BANNED
                ));
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_BAN_USER,
                        'Utilisateur banni de la plateforme: '.$persondata['tx_prenom'].' '.$persondata['tx_nom']);
                return 'OK';
                break;
            case 'unban_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_UNBAN_USER"))
                        return '';
                $model = new DemandeModel($_POST['id']);
                $data = $model->get();
                $personModel = new PersonneModel($data['id_personne']);
                $persondata = $personModel->get();
                $model->set(array(
                   'status' => DemandeModel::PENDING
                ));
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_UNBAN_USER,
                        'Utilisateur débanni de la plateforme: '.$persondata['tx_prenom'].' '.$persondata['tx_nom']);
                return 'OK';
                break;
            default:
                return '';
                break;
        }
    }
    
    

}
