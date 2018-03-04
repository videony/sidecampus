<?php

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

require_once '../AutoLoader.php';


if(isset($_POST['setting_name']))
{
   
    $membermodel = new MemberModel();
    $members = $membermodel->getAllMembers();
    foreach($members as $member)
    {
        switch($member['hierarchie'])
        {
            case MemberModel::ADMIN:
                $value = $_POST['admin'];
                break;
            case MemberModel::MODERATOR:
                $value = $_POST['mod'];
                break;
            case MemberModel::MEMBER:
                $value = $_POST['member'];
                break;
            
        }
        $permModel = new UserSettingModel();
        $permModel->create($member['id_membre'], $_POST['setting_name'], $value);
    }
}

?>
<html>
    <head>
        <title>SideCampus new setting</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="../css/main.css" /> 
        <link rel="stylesheet" type="text/css" href="../css/form.css" /> 
        <script type="text/javascript" src="../js/jquery.js"></script>
        
    </head>
    <body>
<h1>NOUVEAU PARAMETRE</h1>
/!\ Le setting doit déjà être dans la table settings. Ici ne complète que la table user_settings
<form method="POST">
    <fieldset>
       <label for='setting_name'>Setting name *</label>
       <input type="text" name="setting_name" id='setting_name'/>
       <label for='admin'>Valeur par défaut admin *</label>
       <input type="text" name="admin" id='admin'/>
       <label for='mod'>Valeur par défaut modérateur *</label>
       <input type="text" name="mod" id='mod'/>
       <label for='member'>Valeur par défaut membre *</label>
       <input type="text" name="member" id='member'/>
    </fieldset><br/>
   
    <div class="buttonwrapper">
       <input type="submit" value="Créer" class="button"/>
    </div>
</form>
    </body></html>

