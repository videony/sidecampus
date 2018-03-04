<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnnoncesController
 *
 * @author videony
 */
class AnnoncesController implements BodyController{
  
    private $title = 'Petites annonces';
    private $css = array(
        'css/form.css',
        'css/Annonces.css'
    );
    private $js = array(
       'js/Annonces.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return $this->title;
    }

    

    public function getContent() {
        if(isset($_SESSION[SessionController::ID_PERSONNE]))
        {
            $model = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
            $model->set(array(
               'nb_annonces'    =>  0 
            ));
        }
        $template = GenerateUtils::getTemplate('Annonces');
        $markers = array();
        $model = new AnnonceModel();
        if(isset($_REQUEST['mine']))
        {
            // Only show my annonces
            $annonces = $model->getAnnonceByFilter(array(
               'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE]
            ));
            $markers['if_all'] = FALSE;
        }
        elseif(isset($_REQUEST['id_annonce']))
        {
            $annonces = $model->getAnnonceByFilter(array(
               'id_annonce'    =>  $_REQUEST['id_annonce']
            ));
            $this->title = $annonces[0]['tx_titre'];
        }
        else
        {
            $annonces = $model->getAnnonceByFilter(array(
               'cd_sold'    =>  0 
            ));
            $markers['if_mine'] = FALSE;
        }
        if(isset($_SESSION[SessionController::ID_PERSONNE]))
        {
            $markers['if_visitor'] = FALSE;
        }
        else
        {
            $markers['if_all'] = FALSE;
            $markers['if_mine'] = FALSE;
        }
        $template = GenerateUtils::replaceStrings($template, $markers); 
        foreach($annonces as $key=>$annonce)
        {
            $time = strtotime($annonce['dt_posted']);
            $annonces[$key]['posted'] = 'Posté le '.date('d-m-Y', $time).' à '.date('H:i', $time);
            $annonces[$key]['link'] = ConfigUtils::get('website.url').'/request.php?action=Annonces&id_annonce='.$annonce['id_annonce'];
            if(isset($_SESSION[SessionController::ID_PERSONNE]) 
                    && $annonce['id_personne'] == $_SESSION[SessionController::ID_PERSONNE])
            {
                $annonces[$key]['if_not_annonceur'] = FALSE;
                $annonces[$key]['mail_class'] = '';
                $annonces[$key]['tx_description'] = nl2br($annonce['tx_description']);
                $reponsemodel = new AnnonceReponseModel();
                $reponses = $reponsemodel->getAnnonceReponseByFilter(array(
                   'id_annonce' =>  $annonce['id_annonce'] 
                ));
                foreach($reponses as $rpkey=>$reponse)
                {
                    $time = strtotime($reponse['dt_answered']);
                    $reponses[$rpkey]['tx_reponse'] = nl2br($reponse['tx_reponse']);
                    $reponses[$rpkey]['dt_answered'] = 'Reçu le '.date('d-m-Y', $time).' à '.date('H:i', $time);
                }
                $annonces[$key]['nb_messages'] = count($reponses);
                $html = '<!-- ###SUB_REPONSES### -->'.
                        GenerateUtils::subPart($template, '###SUB_REPONSES###')
                        .'<!-- ###SUB_REPONSES### -->';
                $annonces[$key]['###SUB_REPONSES###'] = GenerateUtils::generateRS($html, $reponses, '', '###SUB_REPONSES###');
            }
            else
            {
                $annonces[$key]['if_annonceur'] = FALSE;
                $annonces[$key]['mail_class'] = 'mail_annonceur';
            }
        }
        $template = GenerateUtils::generateRS($template, $annonces, 'Aucune annonce pour le moment', '###SUB_ANNONCE###');
        
        return $template;
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
            case 'send_message':
                $email = '';
                $name = '';
                if(isset($_SESSION[SessionController::ID_PERSONNE]))
                {
                    $model = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
                    $data = $model->get();
                    $email = $data['tx_email'];
                    $name = $data['tx_prenom'].' '.$data['tx_nom'];
                }
                else
                {
                    $email = $_REQUEST['mail'];
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return "INVALID_EMAIL";
                    }
                    $name = $_REQUEST['name'];
                    if(strlen($name) < 3)
                        return "INVALID_NAME";
                }
                $message = $_REQUEST['message'];
                $id_annonce = $_REQUEST['id_annonce'];
                $model = new AnnonceReponseModel();
                $model->create($name, $email, $message, $id_annonce);
                
                $model = new AnnonceModel($id_annonce);
                $data = $model->get();
                $model = new PersonneModel($data['id_personne']);
                $person = $model->get();
                // ENVOI EMAIL
                $html = GenerateUtils::getTemplate('email_annonce_reponse');
                $html = GenerateUtils::replaceStrings($html, array(
                    'tx_titre'=>$data['tx_titre'],
                    'tx_nom'=>$name,
                    'tx_email'=>$email,
                    'tx_reponse'=>nl2br($message),
                    'website' => ConfigUtils::get('website.title'),
                    'url' => ConfigUtils::get('website.url')
                ));
                
                $from = ConfigUtils::get('website.title').' <'.ConfigUtils::get('noreply.email').'>';
                $to = $person['tx_prenom'].' '.$person['tx_nom']." <".$person['tx_email'].">";
                $mail = new Mail($from, $to, "Quelqu'un a répondu à votre annonce sur ".ConfigUtils::get('website.title'));
                $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
                $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
                $mail->addMessagePart($html);
                if (!$mail->send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                } 
                return 'SENT';
                break;
            case 'remove_annonce':
                if(isset($_REQUEST['id_annonce']) && isset($_SESSION[SessionController::ID_PERSONNE]))
                {
                    $model = new AnnonceModel($_REQUEST['id_annonce']);
                    $data = $model->get();
                    if($data['id_personne'] == $_SESSION[SessionController::ID_PERSONNE])
                        $model->remove();
                }
                break;
            default:
                return '';
                break;
        }
    }

}
