<?php

include_once "config/config.php";
include_once "business/dbManager.php";
include_once "services/registrationService.php";


$dbManager = new DBManager($config);

$service = null;
switch($_GET['service']){
    case "saveForm" :
        $service = new RegistrationService($config, $dbManager, null);
        break;


}

if($service->validate())
    $service->run();
else
    $service->failure();
?>