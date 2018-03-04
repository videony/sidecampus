<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BloqueController
 *
 * @author videony
 */
class BloqueController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/Bloque.css'
    );
    private $js = array(
        'js/jquery-ui/ui/jquery.ui.tabs.js',
        'js/Moment.js',
        'js/fullcalendar/fullcalendar.min.js',
        'js/fullcalendar/lang/fr.js',
        'js/form.js',
       'js/Bloque.js'
    );
    
    public function canAccess() {
        return isset($_SESSION[SessionController::ID_PERSONNE]);
    }
    public function getTitle() {
        return 'Ma bloque';
    }

    

    public function getContent() {
        $template = GenerateUtils::getTemplate('Bloque');
        
        $model = new BloqueSemaineModel();
        $semaines = $model->getBloqueSemaineByFilter(array(
            'id_personne'   =>  $_SESSION[SessionController::ID_PERSONNE]
        ));
        
        $semainetemplate = GenerateUtils::subPart($template, '###SUB_SEMAINE###');
        
        $semainesHtml = '';
        
        foreach($semaines as $key=>$semaine)
        {
            $subtemplate = $semainetemplate;
            
            // Hour range & intervals
            $range = $model->getHourRange($semaine['id_semaine_bloque']);
            $hourmin = $range['hourmin'];
            $hourmax = $range['hourmax'];
            
            $subtemplate = GenerateUtils::replaceStrings($subtemplate, array(
                'date_depart'    =>  date('d/m', strtotime($semaine['dt_begin'])) ,
                'date_fin'    =>  date('d/m', strtotime($semaine['dt_end'])) ,
                'min_range'     =>  $hourmin,
                'max_range'     =>  $hourmax,
                'id_semaine_bloque' =>  $semaine['id_semaine_bloque']
            ));
            $days = array(
                'monday'    =>  array(),
                'tuesday'   =>  array(),
                'wednesday' =>  array(),
                'thursday'  =>  array(),
                'friday'    =>  array(),
                'saturday'  =>  array(),
                'sunday'    =>  array()
            );
            
            
            $intervals = array();
            for($i = $hourmin;$i < $hourmax; $i++)
            {
                $intervals[] = array('hour'=>$this->convertHour($i));
            }
            $subtemplate = GenerateUtils::generateRS($subtemplate, $intervals, '', '###SUB_INTERVAL###');
            
            $sessionmodel = new BloqueSessionModel();
            $sessions = $sessionmodel->getBloqueSessionByFilter(array(
                'id_semaine_bloque' =>  $semaine['id_semaine_bloque']
            ));
            foreach($sessions as $session)
            {
                $duree = $session['heure_fin'] - $session['heure_debut'];
                $days[$session['jour_semaine']][] = array(
                    'colors'        =>  $session['tx_color'],
                    'id_session'    =>  $session['id_seance_bloque'],
                    'height'        =>  ($duree)*20 + ($duree-1),
                    'top'           =>  ($session['heure_debut']-$hourmin)*21 - 1,
                    'tx_sujet'      =>  $session['tx_sujet'],
                    'begin'         =>  $session['heure_debut'],
                    'end'           =>  $session['heure_fin'],
                    'resize'        =>  'resizable',
                    'if_remove'     =>  TRUE
                );
            }
            $days = $this->fillSessions($days, $hourmin, $hourmax, $semaine['id_semaine_bloque']);
            foreach($days as $day=>$data)
            {
                $subtemplate = GenerateUtils::generateRS($subtemplate, $data, '', '###SUB_'.strtoupper($day).'_SESSION###');
            }
            $semainesHtml .= $subtemplate;
        }
        $template = GenerateUtils::replaceSubPart($template, '###SUB_SEMAINE###', $semainesHtml);
        return $template;
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }
    private function fillSessions($sessions, $min, $max, $semaine)
    {
        for($i = $min;$i<$max;$i++)
        {
            foreach($sessions as $day=>$daysessions)
            {
                    $sessions[$day][] = array(
                        'colors'        =>  'virgin',
                        'id_session'    =>  $semaine.'_'.$day.'_'.$i,
                        'height'        =>  20,
                        'top'           =>  ($i-$min)*21 - 1,
                        'tx_sujet'      =>  '',
                        'begin'         =>  $i,
                        'end'           =>  $i+1,
                        'resize'        => '',
                        'if_remove'     =>  FALSE
                    );
            }
        }
        return $sessions;
    }
    public function convertHour($hour)
    {
        if($hour >= 0 && $hour < 24)
            $return = $hour;
        if($hour >= 24)
            $return = $hour-24;
        elseif($hour < 0)
            $return = $hour + 24;
        return $return;
    }

    public function handlePostRequest() {
        if(!isset($_POST['action']))
           return '';
        if(isset($_REQUEST['id_session']))
        {
            $model = new BloqueSessionModel($_REQUEST['id_session']);
            $data = $model->get();
            $model = new BloqueSemaineModel($data['id_semaine_bloque']);
            $data = $model->get();
            if($data['id_personne'] != $_SESSION[SessionController::ID_PERSONNE])
                return 'FORBIDDEN_ACTION';
        }
        // Check if has right to edit this week
        if(isset($_REQUEST['id_semaine']))
        {
            $model = new BloqueSemaineModel($_REQUEST['id_semaine']);
            $data = $model->get();
            if($data['id_personne'] != $_SESSION[SessionController::ID_PERSONNE])
                return 'FORBIDDEN_ACTION';
        }
        switch($_POST['action'])
        {
            case 'resize_session':
                if(isset($_REQUEST['id_session']))
                {
                    $model = new BloqueSessionModel($_REQUEST['id_session']);
                    $data = $model->get();
                    $model->set(array(
                       'heure_fin'  =>  $data['heure_debut'] + $_REQUEST['nb_hours'] 
                    ));
                }
                break;
            case 'move_session':
                if(isset($_REQUEST['id_session']))
                {
                    $model = new BloqueSessionModel($_REQUEST['id_session']);
                    $data = $model->get();
                    $model->set(array(
                        'heure_fin'     =>  $data['heure_fin'] - ($data['heure_debut']-$_REQUEST['start']),
                       'heure_debut'  =>  $_REQUEST['start']
                    ));
                }
                break;
            case 'add_session':
                if(isset($_REQUEST['course']))
                {
                    $model = new BloqueSessionModel();
                    $id = $model->create($_REQUEST['end_hour'], 
                            $_REQUEST['color'], 
                            $_REQUEST['start_hour'], 
                            $_REQUEST['day'], 
                            $_REQUEST['course'], 
                            $_REQUEST['id_semaine']);
                    echo $id;
                }
                break;
            case 'set_text':
                if(isset($_REQUEST['id_session']))
                {
                    $model = new BloqueSessionModel($_REQUEST['id_session']);
                    $id = $model->set(array(
                       'tx_sujet'   =>  $_REQUEST['course'] 
                    ));
                }
                break;
            case 'change_color':
                if(isset($_REQUEST['id_session']))
                {
                    $model = new BloqueSessionModel($_REQUEST['id_session']);
                    $id = $model->set(array(
                       'tx_color'   =>  $_REQUEST['color'] 
                    ));
                }
                break;
            case 'delete_session':
                if(isset($_REQUEST['id_session']))
                {
                    $model = new BloqueSessionModel($_REQUEST['id_session']);
                    $data = $model->get();
                    $model->remove();
                    $template = GenerateUtils::getTemplate('Bloque');
                    $part = GenerateUtils::subPart($template, '###SUB_WEDNESDAY_SESSION###');
                    $part = '<!-- ###SUB_WEDNESDAY_SESSION### -->'.$part.'<!-- ###SUB_WEDNESDAY_SESSION### -->';
                    $rows = array();
                    $model = new BloqueSemaineModel();
                    $range = $model->getHourRange($data['id_semaine_bloque']);
                    $hourmin = $range['hourmin'];
                    $hourmax = $range['hourmax'];
                    for($i = $data['heure_debut']; $i < $data['heure_fin'];$i++)
                    {
                        $rows[] = array(
                            'colors'        =>  'virgin',
                            'id_session'    =>  $data['id_semaine_bloque'].'_'.$data['jour_semaine'].'_'.$i,
                            'height'        =>  20,
                            'top'           =>  ($i-$hourmin)*21 - 1,
                            'tx_sujet'      =>  '',
                            'begin'         =>  $i,
                            'end'           =>  $i+1,
                            'resize'        => '',
                            'if_remove'     =>  FALSE
                        );
                    }
                    return GenerateUtils::generateRS($part, $rows, '', '###SUB_WEDNESDAY_SESSION###');
                }
                break;
            case 'get_default_case':
                    $template = GenerateUtils::getTemplate('Bloque');
                    $part = GenerateUtils::subPart($template, '###SUB_MONDAY_SESSION###');
                    return GenerateUtils::replaceStrings($part, array(
                            'colors'        =>  'virgin',
                            'id_session'    =>  '',
                            'height'        =>  20,
                            'top'           =>  '-1',
                            'tx_sujet'      =>  '',
                            'resize'        => '',
                            'if_remove'     =>  FALSE
                    ));
                break;
            case 'reset_bloque':
                $model = new BloqueSemaineModel();
                $model->resetBloque($_SESSION[SessionController::ID_PERSONNE]);
                break;
            case 'delete_semaine':
                if(isset($_REQUEST['id_semaine']))
                {
                   $model = new BloqueSemaineModel($_REQUEST['id_semaine']);
                   $model->remove();
                }
                break;
            case 'add_semaine':
                if(isset($_REQUEST['lundi']))
                {
                    $time = strtotime($_REQUEST['lundi']);
                    if(date('w', $time) != '1')
                        return 'NOT_MONDAY';
                    $model = new BloqueSemaineModel();
                    $semaines = $model->getBloqueSemaineByFilter(array(
                        'id_personne'=>$_SESSION[SessionController::ID_PERSONNE],
                        'dt_begin'=>date('Y-m-d', $time)
                    ));
                    if(!empty($semaines))
                        return 'TAKEN';
                    $model->create(date('Y-m-d', $time+(7*24*60*60)), 
                            date('Y-m-d', $time), $_SESSION[SessionController::ID_PERSONNE]);
                    return '';
                }
                else
                {
                    return 'NO_DATE';
                }
                break;
            default:
                return '';
                break;
        }
    }

}
