<?php

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

require_once '../AutoLoader.php';

if(isset($_REQUEST['page']))
{
    // Controller
    if(isset($_REQUEST['controller'])) {
        $control_template = file_get_contents('control_template.txt');
        $control_template = GenerateUtils::replaceStrings($control_template, array(
            'class'  =>  $_REQUEST['class'],
            'page'  =>  $_REQUEST['page'],
            'css'   =>  ((isset($_REQUEST['css']))?'\'css/'.$_REQUEST['class'].'.css\'':''),
            'js'   =>  ((isset($_REQUEST['js']))?'\'js/'.$_REQUEST['class'].'.js\'':'')
        ));
        $control_file = WEB_ROOT.'Control/'.$_REQUEST['class'].'Controller.php';
        if(!file_exists($control_file))
            file_put_contents($control_file, $control_template);
    }
    
    // View
    if(isset($_REQUEST['view'])) {
        $html_file = WEB_ROOT.'View/'.$_REQUEST['class'].'.html';
        if(!file_exists($html_file))
            file_put_contents($html_file, '');
    }
    
    // CSS
    if(isset($_REQUEST['css'])) {
        $css_file = WEB_ROOT.'css/'.$_REQUEST['class'].'.css';
        if(!file_exists($css_file))
            file_put_contents($css_file, '');
    }
    
    // JS
    if(isset($_REQUEST['js'])) {
        $js_file = WEB_ROOT.'js/'.$_REQUEST['class'].'.js';
        if(!file_exists($js_file))
            file_put_contents($js_file, '$(document).ready(function(){});');
    }
    
    // MODEL
    if(isset($_REQUEST['model'])) 
    {
        $model_file = WEB_ROOT.'Model/'.$_REQUEST['class'].'Model.php';
        $model_template = file_get_contents('model_template.txt');
        
        $cols = array();
        foreach($_REQUEST as $key=>$val)
        {
            if(substr($key, 0, 8) == 'col_val_')
            {
                $id = substr($key, 8);
                // Pas la colonne identifiante ni vide
                if($_REQUEST['col_name_'.$id] != $_REQUEST['id_col'] && $_REQUEST['col_name_'.$id] != '')
                {
                    $cols[$_REQUEST['col_name_'.$id]] = $val;
                }
            }
        }
        arsort($cols);
        $function_cols = array();
        foreach($cols as $col=>$val)
        {
            if($val == ':null')
                $function_cols[] = '$'.$col;
            else
                $function_cols[] = '$'.$col.' = '.$val;
        }
        $function_cols = implode(', ', $function_cols);
        $table_cols = implode(', ', array_keys($cols));
        function dot($n){return ':'.$n;}
        $table_cols_dot = implode(', ', array_map("dot", array_keys($cols)));
        $columns_array = array();
        foreach($cols as $col=>$val)
        {
            $columns_array[] = '\':'.$col.'\' => $'.$col;
        }
        $columns_array = implode(", \n", $columns_array);
        
        $model_template = GenerateUtils::replaceStrings($model_template, array(
            'class'  =>  $_REQUEST['class'],
            'table'  =>  $_REQUEST['table'],
            'id_column' =>  $_REQUEST['id_col'],
            'function_columns'  => $function_cols,
            'table_columns' => $table_cols,
            'table_columns_dots'    => $table_cols_dot,
            'columns_array' => $columns_array
        ));
        
        if(!file_exists($model_file))
            file_put_contents($model_file, $model_template);
    }
}

?>
<html>
    <head>
        <title>SideCampus page generation</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="../css/main.css" /> 
        <link rel="stylesheet" type="text/css" href="../css/form.css" /> 
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                var nbcols = 1;
                var html = $('tbody').html();
               $('#more_cols').click(function(event){
                    event.preventDefault();
                   nbcols = nbcols + 1;
                   $('tbody').append(html);
                   $('tbody tr:last-child').attr('id', nbcols);
                   $('tbody tr:last-child td:first-child input').attr('name', 'col_name_'+nbcols);
                   $('tbody tr:last-child td:last-child input').attr('name', 'col_val_'+nbcols);
                   $('tbody tr:last-child td:last-child input').attr('value', ':null');
               });
               $('#model').click(function(){
                  if($(this).attr('checked') == 'checked')
                  {
                      $('#model_fieldset').hide();
                      $(this).removeAttr('checked');
                  }
                  else
                  {
                      $('#model_fieldset').show();
                      $(this).attr('checked','check');
                  }
               });
               $('form').submit(function(event){
                   if($('#class').val() == '')
                   {
                       event.preventDefault();
                       alert('Veuillez remplir tous les champs obligatoires');
                   }
                   elseif($('#model').attr('checked') == 'checked')
                   {
                       if($('#id_col').val() == '' || $('#table').val() == '')
                       {
                           event.preventDefault();
                           alert('Veuillez remplir tous les champs obligatoires');
                       }
                   }
               })
            });
        </script>
    </head>
    <body>
<h1>PAGE GENERATION</h1>
<form method="POST">
    <fieldset>
       <label for='page'>Nom de la page *</label>
       <input type="text" name="page" id='page'/>
       <label for='class'>Nom de la classe *</label>
       <input type="text" name="class" id='class'/><br/><br/>
       <input type="checkbox" id="view" name="controller" value="Yes" checked="checked"> Générer le contrôleur<br/>
       <input type="checkbox" id="view" name="view" value="Yes" checked="checked"> Générer la vue<br/>
       <input type="checkbox" id="css" name="css" value="Yes"> Générer un fichier css<br/>
       <input type="checkbox" id="js" name="js" value="Yes"> Générer un fichier js<br/>
       <input type="checkbox" id="model" name="model" value="Yes" checked="checked"> Générer le modèle<br/>
    </fieldset><br/>
    <fieldset id="model_fieldset">
        <legend>MODEL</legend>
    <label for='table'>Nom de la table *</label>
       <input type="text" name="table" id='table'/>
    <label for='id_col'>Colonne identifiante *</label>
       <input type="text" name="id_col" id='ic_cole'/>
    <label for='cols'>Colonnes</label>
    <table id="cols">
        <thead>
            <tr>
                <th>Nom de la colonne</th>
                <th>Valeur par défaut (:null = pas de valeur par défaut)</th>
            </tr>
        </thead>
        <tbody>
            <tr id="1">
                <td><input type="text" name="col_name_1" class="colname"/></td>
                <td><input type="text" name="col_val_1" class="colval" value=":null"/></td>
            </tr>
        </tbody>
    </table>
    <a href="#" id="more_cols">+ Ajouter une colonne</a>
    </fieldset><br/>
    <div class="buttonwrapper">
       <input type="submit" value="Générer" class="button"/>
    </div>
</form>
    </body></html>

