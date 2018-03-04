<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarController
 *
 * @author videony
 */
class EventEditController implements BodyController{
    
    private $css = array(
        'css/form.css',
    );
    private $js = array(
        'js/form.js',
    );
    
    
    public function canAccess() {
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
            return FALSE;
        /*
         * Peut modifier un évènement qu'il a ajouté si:
         *      - Il est privé (c'est le sien)
         *      - Pour un évènement public, s'il se trouve sur la bonne plateforme
         *          ET qu'il a le droit sur cette plateforme de modifier ses évènements
         * Peut modifier un évènement non ajouté par lui si:
         *      - Il n'est pas privé
         *      - Pour un évènement public, s'il se trouve sur la bonne plateforme
         *          ET qu'il a le droit sur cette plateforme de modifier les évènements des autres
         */
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        if($type == CalendarController::VISIBILITY_PRIVATE)
            $model = new PersonEventModel($id);
        else
            $model = new PlatformEventModel($id);
        $data = $model->get();
        
        if($data['int_adder'] == $_SESSION[SessionController::ID_PERSONNE])
        {
            if($type == CalendarController::VISIBILITY_PRIVATE)
                return TRUE;
            else
            {
                // Visibilité publique
                $mmodel = new MemberModel();
                $membership = $mmodel->getMembersByFilter(array(
                    'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                    'id_plateforme' =>  $data['id_plateforme']
                ));
                if(empty($membership))
                    return FALSE;
                $id_membre = $membership[0]['id_membre'];
                
                return UserPermissionModel::can($id_membre, 'CAN_EDIT_OWN_EVENTS');
            }
        }
        else
        {
            if($type == CalendarController::VISIBILITY_PRIVATE)
                return FALSE;
            else
            {
                // Visibilité publique
                $mmodel = new MemberModel();
                $membership = $mmodel->getMembersByFilter(array(
                    'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                    'id_plateforme' =>  $data['id_plateforme']
                ));
                if(empty($membership))
                    return FALSE;
                $id_membre = $membership[0]['id_membre'];
                return UserPermissionModel::can($id_membre, 'CAN_EDIT_OTHERS_EVENTS');
            }
        }
    }
    public function getTitle() {
        return 'Edition';
    }

    

    public function getContent() {
        
        // FORMULAIRE DE CREATION
        if(isset($_POST['sent']))
        {
            $errors = $this->check();
            if(empty($errors))
            {
                // succès
                if($_POST['dt_fin'] == NULL)
                {
                    // Si pas de date de fin donnée, date de début est prise comme date de fin.
                    $_POST['dt_fin'] = $_POST['dt_debut'];
                    $_POST['hr_fin'] = $_POST['hr_debut'];
                    $_POST['mn_fin'] = $_POST['mn_debut'];
                }
                $debut = new DateTime();
                $debut->setTimestamp(strtotime($_POST['dt_debut']));
                $debut->setTime($_POST['hr_debut'], $_POST['mn_debut'], 0);
                $dt_debut = $debut->format('Y-m-d H:i');

                $fin = new DateTime();
                $fin->setTimestamp(strtotime($_POST['dt_fin']));
                $fin->setTime($_POST['hr_fin'], $_POST['mn_fin'], 0);
                $dt_fin = $fin->format('Y-m-d H:i');

                if(isset($_SESSION[SessionController::PLATFORM_ID]))
                    $platform = $_SESSION[SessionController::PLATFORM_ID];
                else
                    $platform = NULL;

                if($_POST['event_type'] == CalendarController::VISIBILITY_PRIVATE)
                {
                    $model = new PersonEventModel($_POST['id_event']);
                }
                else
                {
                    $model = new PlatformEventModel($_POST['id_event']);
                }
                
                // Si visibilité changée, nécessité de changer de table!
                if(isset($_POST['visibilite']) && $_POST['event_type'] != $_POST['visibilite'])
                {
                    if($_POST['event_type'] == CalendarController::VISIBILITY_PRIVATE
                            && UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_ADD_PUBLIC_EVENT'))
                    {
                        // On doit le déplacer de privée à publique
                        $old_data = $model->get();
                        $model->remove();
                        
                        $model = new PlatformEventModel();
                        $newid = $model->create($dt_debut, $dt_fin, 
                            $_SESSION[SessionController::PLATFORM_ID],
                            $old_data['int_adder'], 
                            $_POST['description'], $_POST['titre']);
                        if($newid != NULL)
                            $return = TRUE;
                        $_REQUEST['id'] = $newid;
                        $_REQUEST['type'] = CalendarController::VISIBILITY_PUBLIC;
                    }
                    elseif($_POST['event_type'] == CalendarController::VISIBILITY_PUBLIC)
                    {
                        // On doit le déplacer de publique à privée
                        $old_data = $model->get();
                        $model->remove();
                        
                        $model = new PersonEventModel();
                        $newid = $model->create($dt_fin, $dt_debut, $_SESSION[SessionController::ID_PERSONNE], 
                            $_POST['description'], $_POST['titre']);
                        if($newid != NULL)
                            $return = TRUE;
                        $_REQUEST['id'] = $newid;
                        $_REQUEST['type'] = CalendarController::VISIBILITY_PRIVATE;
                    }
                }
                else
                {
                    $return = $model->set(array(
                       'tx_description' =>  $_POST['description'],
                        'tx_titre'      =>  $_POST['titre'],
                        'dt_begin'      =>  $dt_debut,
                        'dt_end'        =>  $dt_fin
                    ));
                }
                if($return == TRUE)
                {
                    $form = $this->getForm(array());
                    $successbox = FormUtils::successBox('Votre évènement a bien été modifié');
                    $form = GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $successbox);
                    return $form;
                }
            }
            else
            {
                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox();
                $form = GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                return $form;
            }
        }
        else
        {
            $form = $this->getForm(array());
            return $form;
        }
    }
    public function check(){
        $errors = array();
        // Titre
        if(empty($_POST['titre'])) {
            $errors['titre'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le titre est vide');
        }
        elseif(strlen($_POST['titre']) > 30) {
            $errors['titre'] = array('val' => $_POST['titre'], 'status' => TRUE, 'reason' => 'Maximum 30 caractères pour le titre');
        }
        // Description
        /*if(empty($_POST['description'])) {
            $errors['description'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez donner une description de l\'évènement');
        }*/
        // Visibilité
        if(isset($_POST['visibilite'])) {
            if(!in_array($_POST['visibilite'], array('public', 'private'))) {
                $errors['visibilite'] = array('val' => '', 'status' => TRUE, 'reason' => 'La visiblité est soit publique, soit privée');
            }
        }
        // Dates
        $date_errors = array();
        if(preg_match('/([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $_POST['dt_debut']))
        {
            $infos = explode('-', $_POST['dt_debut']);
            if(!checkdate($infos[1], $infos[0], $infos[2])) {
                $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => TRUE, 'reason' => 'La date de début doit être une date valide');
            }
            else
            {
                if(!empty($_POST['dt_fin']))
                {
                    if(preg_match('/([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $_POST['dt_fin']))
                    {
                        $infos = explode('-', $_POST['dt_fin']);
                        if(!checkdate($infos[1], $infos[0], $infos[2])) {
                            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'La date de fin doit être une date valide');
                        }
                        if(strtotime($_POST['dt_fin']) < strtotime($_POST['dt_debut'])) {
                            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'La date de fin devrait être postérieure à la date de début');
                        }
                    }
                    else
                    {
                        $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'Veuillez respecter le format de la date (jj-mm-aaaa)');
                    }
                }
            }
            
        }
        else
        {
            $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => TRUE, 'reason' => 'Veuillez respecter le format de la date (jj-mm-aaaa)');
        }
        // Heure de début
        if(empty($_POST['hr_debut']) || $_POST['hr_debut'] == 'HH') {
            $errors['hr_debut'] = array('val' => $_POST['hr_debut'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de commencement');
        }
        // Heure de fin (peut être laissée à vide sauf si date précisée)
        if(!empty($_POST['dt_fin'])) {
            if($_POST['hr_fin'] == 'HH')
                $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de fin');
            elseif($_POST['dt_debut'] == $_POST['dt_fin'] && $_POST['hr_debut'] > $_POST['hr_fin'])
                $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de fin postérieure à l\'heure de début');
        }
        if(isset($errors['dt_debut']) || isset($errors['hr_debut'])) {
            $errors['debut']['reason'] = array();
            if(isset($errors['dt_debut']))
                $errors['debut']['reason'][] = $errors['dt_debut']['reason'];
            if(isset($errors['hr_debut']))
                $errors['debut']['reason'][] = $errors['hr_debut']['reason'];   
        }
        if(isset($errors['dt_fin']) || isset($errors['hr_fin'])) {
            $errors['fin']['reason'] = array();
            if(isset($errors['dt_fin']))
                $errors['fin']['reason'][] = $errors['dt_fin']['reason'];
            if(isset($errors['hr_fin']))
                $errors['fin']['reason'][] = $errors['hr_fin']['reason'];      
        }
        
        
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        
        $template = GenerateUtils::getTemplate('Calendar');
        $part = GenerateUtils::subPart($template, "###SUB_ADD_FORM###");
            
        if(empty($wrong_fields)) 
        {
            $id = $_REQUEST['id'];
            $type = $_REQUEST['type'];
            if($type == CalendarController::VISIBILITY_PRIVATE)
                $model = new PersonEventModel($id);
            else
                $model = new PlatformEventModel($id);
            $infos = $model->get();
            $id = FormUtils::hiddenBox('id_event', $id);
            $actualtype = FormUtils::hiddenBox('event_type', $type);
            $titre = FormUtils::stringBox('titre', 'Titre de l\'évènement (max 30 caractères) *', $infos['tx_titre']);
            $descr = FormUtils::textAreaBox('description', 'Description de l\'évènement', $infos['tx_description'], '100px');
            // Pour le changement de statut, deux cas:
            // Privé -> public = ajout d'un évènement
            // Public -> privé = modification d'un évènement
            // Or si jamais il n'a pas le droit d'updater cet évènement public, il sera bloqué avant
            // => On ne traite que le cas du privé au public.
            if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM
                    && UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_ADD_PUBLIC_EVENT'))
            {
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', array(
                    'public'    =>  'Publique (Visible par les autres membres de la plateforme)',
                    'private'   =>  'Privée (Visible uniquement par vous)'
                    ), $type);
            }
            elseif($type == CalendarController::VISIBILITY_PUBLIC)
            {
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', array(
                    'public'    =>  'Publique (Visible par les autres membres de la plateforme)',
                    'private'   =>  'Privée (Visible uniquement par vous)'
                    ), $type);
            }
            else
                $visib = '';
            $tmbegin = strtotime($infos['dt_begin']);
            $begin = FormUtils::dateTimeBox('debut', 'Commence ', 
                    date('d-m-Y', $tmbegin), date('H', $tmbegin), date('i', $tmbegin));
            $tmfin = strtotime($infos['dt_end']);
            $fin = FormUtils::dateTimeBox('fin', 'Finit ', 
                    date('d-m-Y', $tmfin), date('H', $tmfin), date('i', $tmfin));
            $part = GenerateUtils::replaceSubPart($part, "###SUB_FORM###", $id.$actualtype.$titre.$descr.$visib.$begin.$fin);
            return $part;
           
        }
        else
        {
            $wrong_fields = $this->processErrors($wrong_fields);
            
            $type = $_POST['event_type'];
            
            $id = FormUtils::hiddenBox('id_event', $_POST['id_event']);
            $actualtype = FormUtils::hiddenBox('event_type', $_POST['event_type']);
            $titre = FormUtils::stringBox('titre', 'Titre de l\'évènement (max 30 caractères) *', $wrong_fields['titre']['val'], $wrong_fields['titre']['status'], $wrong_fields['titre']['reason']);
            $descr = FormUtils::textAreaBox('description', 'Description de l\'évènement', $wrong_fields['description']['val'], '100px', $wrong_fields['description']['status'], $wrong_fields['description']['reason']);
            
            if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM
                    && UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_ADD_PUBLIC_EVENT'))
            {
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', array(
                    'public'    =>  'Publique (Visible par les autres membres de la plateforme)',
                    'private'   =>  'Privée (Visible uniquement par vous)'
                    ), $wrong_fields['visibilite']['val'], $wrong_fields['visibilite']['status'], $wrong_fields['visibilite']['reason']);
            }
            elseif($type == CalendarController::VISIBILITY_PUBLIC)
            {
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', array(
                    'public'    =>  'Publique (Visible par les autres membres de la plateforme)',
                    'private'   =>  'Privée (Visible uniquement par vous)'
                    ), $wrong_fields['visibilite']['val'], $wrong_fields['visibilite']['status'], $wrong_fields['visibilite']['reason']);
            }
            else
                $visib = '';
            $begin = FormUtils::dateTimeBox('debut', 'Commence *', 
                    $wrong_fields['dt_debut']['val'], $wrong_fields['hr_debut']['val'], $wrong_fields['mn_debut']['val'], 
                    $wrong_fields['dt_debut']['status'], $wrong_fields['hr_debut']['status'], $wrong_fields['mn_debut']['status'], 
                    $wrong_fields['debut']['reason']);
            $fin = FormUtils::dateTimeBox('fin', 'Finit ', 
                    $wrong_fields['dt_fin']['val'], $wrong_fields['hr_fin']['val'], $wrong_fields['mn_fin']['val'], 
                    $wrong_fields['dt_fin']['status'], $wrong_fields['hr_fin']['status'], $wrong_fields['mn_fin']['status'], 
                    $wrong_fields['fin']['reason']);
            $part = GenerateUtils::replaceSubPart($part, "###SUB_FORM###", $id.$actualtype.$titre.$descr.$visib.$begin.$fin);
            return $part;
        }
        return $template;
    }
    public function processErrors($errors) {
        if(!isset($errors['titre'])){
            $errors['titre'] = array('val' => $_POST['titre'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['description'])){
            $errors['description'] = array('val' => $_POST['description'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['visibilite']) && isset($_POST['visibilite'])){
            $errors['visibilite'] = array('val' => $_POST['visibilite'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['debut'])){
            $errors['debut']['reason'] = array();
        }
        if(!isset($errors['dt_debut'])){
            $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['hr_debut'])){
            $errors['hr_debut'] = array('val' => $_POST['hr_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['mn_debut'])){
            $errors['mn_debut'] = array('val' => $_POST['mn_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['fin'])){
            $errors['fin']['reason'] = array();
        }
        if(!isset($errors['dt_fin'])){
            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['hr_fin'])){
            $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['mn_fin'])){
            $errors['mn_fin'] = array('val' => $_POST['mn_fin'], 'status' => FALSE, 'reason'=>'');
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
