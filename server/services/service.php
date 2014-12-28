<?php

include_once "business/dataProcessor.php";

class Service {

    function __construct($pConfig, $pDbManager, $pData){
        
        $this->config = $pConfig;
        $this->dbManager = $pDbManager;
        $this->data = $pData;
        
        $this->rawInput = file_get_contents("php://input");
        $this->inputData = json_decode($this->rawInput);
        
        
    }
    
    function run(){
        print "";
    }
    
    function failure(){
        print "NOK";
        return false;
    }
    
    function validate(){
        return true;
    }

    function printJsonHeader(){
        header('Content-type: application/json');
    }
    function printCsvHeader(){
        header('Content-type: text/csv; charset = UTF-8');
    }
}