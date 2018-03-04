<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TodoController
 *
 * @author videony
 */
class TodoController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Todo.css'
    );
    private $js = array(
        'js/form.js',
        'js/jquery-ui/ui/jquery.ui.sortable.js',
       'js/Todo.js'
    );
    
    public function canAccess() {
        return isset($_SESSION[SessionController::ID_PERSONNE]);
    }
    public function getTitle() {
        return 'A faire';
    }

    

    public function getContent() {
        $template = GenerateUtils::getTemplate('Todo');
        $model = new TodoModel();
        $todos = $model->getTodoByFilter(array(
           'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE] 
        ));
        foreach($todos as $key=>$todo)
        {
            $todos[$key]['tx_todo'] = nl2br($todo['tx_todo']);
            if($todo['dt_deadline'] == null || $todo['dt_deadline'] == '0000-00-00')
            {
                $todos[$key]['if_deadline'] = FALSE;
                $todos[$key]['class'] = 'normal';
            }
            else
            {
                $todos[$key]['deadline'] = date('d-m-Y', strtotime($todo['dt_deadline']));
            
                if(time() > strtotime($todo['dt_deadline']))
                    $todos[$key]['class'] = 'passed';
                elseif(strtotime($todo['dt_deadline'])-time() < 60*60*24*2)
                    $todos[$key]['class'] = 'urgent';
                elseif(strtotime($todo['dt_deadline'])-time() < 60*60*24*7)
                    $todos[$key]['class'] = 'alert';
                else
                    $todos[$key]['class'] = 'normal';
            }
        }
        $template = GenerateUtils::generateRS($template, $todos);
        $markers['datebox'] = FormUtils::dateBox("dt_deadline", "date");
        $template = GenerateUtils::replaceStrings($template, $markers);
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
            case 'remove':
                $model = new TodoModel($_REQUEST['id']);
                if($model->remove())
                    return 'OK';
                else
                    return 'NO';
                break;
            case 'add':
                $model = new TodoModel();
                $date = isset($_REQUEST['date'])?$_REQUEST['date']:'';
                if(strlen($date) > 0 && strtotime($date) < time())
                    return "KO"."Veuillez préciser une date valide et supérieure à aujourd'hui";
                $note = $_REQUEST['note'];
                if(strlen($note) == 0)
                    return "KO"."Vous ne pouvez pas enregistrer une note vide.";
                if(strlen($date) == 0)
                    $dateval = "";
                else
                    $dateval = date('Y-m-d', strtotime($date));
                $id = $model->create($_SESSION[SessionController::ID_PERSONNE], 
                        $note, $dateval);
                return $id;
                break;
            case 'edit':
                $model = new TodoModel($_REQUEST['id']);
                $date = isset($_REQUEST['date'])?$_REQUEST['date']:'';
                if(strlen($date) > 0 && strtotime($date) < time())
                    return "KO"."Veuillez préciser une date valide et supérieure à aujourd'hui";
                $note = $_REQUEST['note'];
                if(strlen($note) == 0)
                    return "KO"."Vous ne pouvez pas enregistrer une note vide.";
                if(strlen($date) == 0)
                    $dateval = "";
                else
                    $dateval = date('Y-m-d', strtotime($date));
                $model->set(array(
                    'dt_deadline'   =>  $dateval,
                    'tx_todo'       =>  $note
                ));
                return $_REQUEST["id"];
                break;
            case 'get_status':
                $model = new TodoModel($_REQUEST['id']);
                $data = $model->get();
                $time = strtotime($data['dt_deadline']);
            
                if($data['dt_deadline'] == null || $data['dt_deadline'] == '0000-00-00')
                    return 'normal';
                elseif(time() > $time)
                    return 'passed';
                elseif($time-time() < 60*60*24*2)
                    return 'urgent';
                elseif($time-time() < 60*60*24*7)
                    return 'alert';
                else
                    return 'normal';
                break;
            case 'exchange_position':
                $model = new TodoModel();
                $model->changePosition($_REQUEST['id'], $_REQUEST['position']);
                break;
            default:
                return '';
                break;
        }
    }

}
