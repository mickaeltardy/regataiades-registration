<?php
class DataProcessor {
    function formatDate($pDate){


        if(preg_match("/^\d{2}\/\d{2}\/\d{4}/", $pDate)){

            $lDateSplit = split("/", $pDate);

            $lDate = date_create($lDateSplit[1]."/".$lDateSplit[0]."/".$lDateSplit[2]);
            $lDate = date_format($lDate, 'Y-m-d');
            return $lDate;
        }else if(preg_match("/^\d{4}\-\d{2}\-\d{2}/", $pDate)){
            return $pDate;
        }
        return "";
    }

    function timeout($pTime){
        $lStartTime = microtime();
        $lWait = true;

        while($lWait){
            if ((microtime() - $lStartTime) > $pTime) {
                $lWait = false;
            }
            $lWait = false;
        }
        return true;
    }
}