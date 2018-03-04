<?php

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

require_once '../AutoLoader.php';


if(isset($_POST['permission_name']))
{
    $model = new PermissionModel();
    $model->create($_POST['permission_name'], $_POST['description'], 
            $_POST['section'], $_POST['member'], $_POST['mod'], $_POST['admin']);
    
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
        $permModel = new UserPermissionModel();
        $permModel->create($member['id_membre'], $_POST['permission_name'], $value);
    }
}

?>
<html>
    <head>
        <title>SideCampus new permission</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="../css/main.css" /> 
        <link rel="stylesheet" type="text/css" href="../css/form.css" /> 
        <script type="text/javascript" src="../js/jquery.js"></script>
        
    </head>
    <body>
<h1>NOUVELLE PERMISSION</h1>
<form method="POST">
    <fieldset>
       <label for='permission_name'>Permission *</label>
       <input type="text" name="permission_name" id='permission_name'/>
       <label for='description'>Description *</label>
       <input type="text" name="description" id='description'/>
       <label for='section'>Section *</label>
       <input type="text" name="section" id='section'/>
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

