<?php

include_once "services/service.php";

include_once "business/dataStorage.php";
include_once "business/usersManager.php";
include_once "business/mailSender.php";
include_once "business/dataTemplater.php";
include_once "business/pdfGenerator.php";

class RegistrationService extends Service{

    function validate(){
        return ($this->inputData && $this->config && $this->dbManager);
    }
    
    function run(){

        $config = $this->config;
        $lPostData = $this->inputData;
        
        $dataStorage = new DataStorage($this->config, $this->dbManager);
        $usersManager = new UsersManager($this->config, $this->dbManager);
        $mailSender = new MailSender($this->config);
        $dataTemplater = new DataTemplater($this->config);
        $pdfGenerator = new PdfGenerator($this->config);

        $lLanguage = $this->inputData->language;
        

        $lMessagesUrl = "../shared/data/messages.".$lLanguage.".json";

        $lMessages = json_decode(file_get_contents($lMessagesUrl));

        $lStoredData = $lPostData;
        $lDump = $this->inputData;
        $tmpDocsToProvide = $lDump->docsToProvide;
        unset($lDump->docsToProvide);
        $lStoredData->dump = json_encode($lDump);
        $lPostData->regNumber = date("Ymd")."01000";

            
        $lTeamInsertId = $dataStorage->storeTeamData($lStoredData);
     
        
        
        $lData = array('data' => $lPostData, 'messages'=> $lMessages, 'config'=>$config );
        $lPdfContent = $dataTemplater->render("registrationForm.html", $lData);
        $lMailContent = $dataTemplater->render("mailNotification.".$lLanguage.".html", $lData);
        
        $lParams['title'] = $lMessages->mail->registration->title;
        $lParams['message'] = $lMailContent;
        $lParams['sender']['email'] = $config['mail'];
        $lParams['sender']['name'] = $config['senderName'];
        $lParams['addressee']['email'] = $lPostData->email;
        $lParams['addressee']['name'] = $lPostData->name . " " . $lPostData->surname;
        
        $lFileName = $config['root']."../storage/registrationForm_".$lPostData->name."_".$lPostData->surname."_".mktime().".pdf";
        
        print $lMailContent;
        
        $pdfGenerator->generatePdf($lPdfContent, $lFileName);

        $lParams['filePath'][] = $lFileName;

        
        
        $lRegConfirmMsg = $mailSender->generateMessage($lParams);

        
        

        $lAdminParams = $lParams;
        $lAdminParams['title'] = "[Regataiades] Nouvelle inscription";
        $lAdminParams['message'] = "Nouvelle inscription est effectuée sur le site des Régataïades";
        $lAdminParams['addressee']['email'] = $config['adminmail'];
        $lAdminParams['addressee']['name'] = $config['adminmail'];
        $lAdminMsg = $mailSender->generateMessage($lAdminParams);

        if($lUserCreated){
            $lAccountMailParams['title'] = $lMessages->mail->account->title;
            $lAccountMailParams['message'] = $lAccountMailContent;
            $lAccountMailParams['sender']['email'] = $config['mail'];
            $lAccountMailParams['sender']['name'] = $config['senderName'];
            $lAccountMailParams['addressee']['email'] = $lPostData->email;
            $lAccountMailParams['addressee']['name'] = $lPostData->name . " " . $lPostData->surname;
            $lAccountConfirmMsg = $mailSender->generateMessage($lAccountMailParams);
        }
        
        
        
        if($lAdminMsg){
            $mailSender->sendMessage($lAdminMsg);
            
        if($lAccountConfirmMsg && $lUserCreated)
            $mailSender->sendMessage($lAccountConfirmMsg);
        
        if($lRegConfirmMsg)
            $mailSender->sendMessage($lRegConfirmMsg);
            print "OK";
        }
        else{
            return failure();
        }
    }
}