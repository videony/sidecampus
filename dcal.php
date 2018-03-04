<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
$session = new SessionController();

//SecurityUtils::secureWhatIsSent();

if(SecurityUtils::isBlocked())
{
    echo file_get_contents('View/blocked.html');
    die();
}
if(!isset($_REQUEST['person']) || !isset($_REQUEST['code']))
{
    echo file_get_contents('View/blocked.html');
    die();
}
/*
* FUCKING HACK FOR EXPORT BECAUSE FUCKING CALENDARS DON'T HANDLE SUMMER HOUR
* FUCK THIS SHIT SERIOULSY
* 
*/
$toadd = date('H')-gmdate('H') - 1;
$model = new PersonneModel($_REQUEST['person']);
$data = $model->get();
if($_REQUEST['code'] == $data['confirm_code'])
{
    $template = file_get_contents(WEB_ROOT."View/ICS/Template");
    $event_template = file_get_contents(WEB_ROOT."View/ICS/EventTemplate");
    $events = "";

    $model = new PersonEventModel();
    $personevents = $model->getPersonEventByFilter(array(
        'int_adder'   =>  $_REQUEST['person']
    ));
    foreach($personevents as $ev)
    {
        $ev['id_event'] = 'sidecampus_person_'.$ev['id_person_event'];
		$ev['dt_begin'] = gmdate('Ymd\THis', strtotime($ev['dt_begin']));
		$ev['dt_end'] = gmdate('Ymd\THis', strtotime($ev['dt_end']));
        $events = $events.GenerateUtils::replaceStrings($event_template, $ev);
    }
    
    $memberModel = new MemberModel();
    $memberships = $memberModel->getMembersByFilter(array(
       'id_personne'    =>  $_REQUEST['person']
    ));
    foreach($memberships as $membership)
    {
        if(UserPermissionModel::can($membership['id_membre'], "CAN_SEE_PUBLIC_EVENTS"))
        {
            $model = new PlatformEventModel();
            $platformevents = $model->getPlatformEventByFilter(array(
                'id_plateforme'   =>  $membership['id_plateforme']
            ));
            foreach($platformevents as $ev)
            {
				$ev['dt_begin'] = gmdate('Ymd\THis', strtotime($ev['dt_begin']));
				$ev['dt_end'] = gmdate('Ymd\THis', strtotime($ev['dt_end']));
                $ev['id_event'] = 'sidecampus_platform_'.$ev['id_plateforme'].'_'.$ev['id_platform_event'];
                $events = $events.GenerateUtils::replaceStrings($event_template, $ev);
            }
        }
    }

    $generated = GenerateUtils::replaceStrings($template, array('events'=>$events));
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream; charset=UTF-8');
    header('Content-Disposition: attachment; filename=sidecampus.ics');
    header('Set-Cookie: fileDownload=true; path=/');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . strlen($generated));
    flush();
    echo $generated;
}
else
{
    echo file_get_contents('View/blocked.html');
    die();
}

DB::disconnect();
exit;	

?>