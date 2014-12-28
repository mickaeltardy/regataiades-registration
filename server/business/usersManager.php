<?php

class UsersManager {

    function __construct($config, $pDBManager){
        if($pDBManager != null)
            $this->dbManager = $pDBManager;
    }

    function saveUser($pData){

        if($this->dbManager && $stmt = $this->dbManager->connection -> prepare("
                insert into una_custom_users
                (name, surname, login, email, password)
                values (?, ?, ?, ?, ?)")) {

                $stmt -> bind_param("sssss",
                        trim($pData->name) ,
                        trim($pData->surname),
                        trim($pData->email),
                        trim($pData->email),
                        md5(trim($pData->password)));

                /* execute query */
                $stmt -> execute();
                $userId = $stmt->insert_id;
                $stmt -> close();
                if($userId)
                    return $userId;
                else
                    return 0;

        }
        return 0;
    }

    function checkUserExistence($pData){
        if($this->dbManager){
            if($stmt = $this->dbManager->connection -> prepare("
                    select id from una_custom_users
                    where login = ?  and invalid = 0")){
                    $stmt -> bind_param("s",
                            trim($pData->email));
                    $stmt -> execute();
                    $stmt->bind_result($userId);
                    $stmt->fetch();
                    $stmt -> close();
                    if($userId)
                        return $userId;
                    else
                        return 0;
            }
        }
        return 0;
    }

    function getUserByField($pFieldName, $pFieldType, $pFieldValue){
        if($this->dbManager && $pFieldName && $pFieldValue && $stmt = $this->dbManager->connection -> prepare("
                select id, name, surname, login, email, password from una_custom_users
                where ".$pFieldName." = ?  and invalid = 0")){
                $stmt -> bind_param($pFieldType,
                        $pFieldValue);
                $stmt -> execute();

                $stmt->bind_result($userId, $userName, $userSurname, $userLogin, $userEmail, $userPassword);
                $stmt->fetch();
                $user['id'] = $userId;
                $user['name'] = $userName;
                $user['surname'] = $userSurname;
                $user['login'] = $userLogin;
                $user['email'] = $userEmail;
                $user['password'] = $userPassword;

                $stmt -> close();
                if($user['id']){
                    return $user;
                }
                else
                    return 0;
        }
    }

    function getUserByLogin($pData){

        if($pData->login != ""){
            $lFieldValue = $pData->login;
        }elseif ($pData->email != ""){
            $lFieldValue = $pData->email;
        }else
            return;

        return $this->getUserByField("login", "s", $lFieldValue);

    }

    function getUserById($pId){
        return $this->getUserByField("id", "i", $pId);


    }

    function checkUserCredentials($pData){
        $lUser = $this->getUserByLogin($pData);
        if($lUser['id']
                && $this->checkPassword($pData, $lUser)){
            return $lUser['id'];
        }
        else
            return 0;

    }

    function checkPassword($pData, $pUser){
        $lResult = false;

        if($pData->encrypt){
            $lResult = $pData->password == $pUser['password'];
        }else{
            $lResult = md5($pData->password) == $pUser['password'];
        }


        return $lResult;
    }

    function linkMember($pUserId, $pMemberId){

        if($this->dbManager && ($pMemberId > 0 && $pUserId > 0)){

            if($stmt = $this->dbManager->connection -> prepare("
                    insert into una_custom_join_users_members
                    (user_id, member_id)
                    values (?, ?)")){
                    $stmt -> bind_param("ii",
                            $pUserId, $pMemberId);
                    $stmt -> execute();
                    $linkId = $stmt->insert_id;
                    $stmt -> close();
                    if($linkId)
                        return true;
                    else
                        return false;

            }
        }

        return false;
    }
    
    function getRandomPassword(){
        $lSymbols = "ABCDEFGIJKLMNOPQRSTUVWXYZabcdefgijklmnopqrstuvwxyz1234567890";
        $lPassword = "";
        $lPasswordLength = 8;
        for($i = 0 ; $i < $lPasswordLength; $i++){
            $lRand = rand(0, strlen($lSymbols)-1);
            $lPassword .= $lSymbols[$lRand];
        }
        return $lPassword;
        
    }
    
    function resetPassword($pData){
        if($pData->login){
            $lUser = $this->getUserByLogin($pData);
            if($lUser['id'] && $stmt = $this->dbManager->connection -> prepare("
                update una_custom_users set password =? where id = ?")){
                $lPassword = $this->getRandomPassword();
                $stmt -> bind_param("si",
                        md5($lPassword), $lUser['id']);
                $stmt -> execute();
                
                return $lPassword;
                
            }
        }
        return false;
    }

    function validateService($pUserId, $pServiceCode){
        
        
        if($pUserId > 0 && $stmt = $this->dbManager->connection -> prepare("
                select distinct una_custom_modules.id as id,
                una_custom_modules.state as state,
                una_custom_modules.label as label,
                una_custom_modules.code as code
                from una_custom_modules,
                una_custom_join_modules_profiles,
                una_custom_join_users_profiles
                where una_custom_modules.id = una_custom_join_modules_profiles.module_id
                and (
                (una_custom_join_modules_profiles.profile_id = una_custom_join_users_profiles.profile_id
                and  una_custom_join_users_profiles.user_id  = ?)
                or una_custom_join_modules_profiles.profile_id = 1)
                and una_custom_modules.code = ?")){
                        $stmt -> bind_param("is",
                                trim($pUserId), 
                                $pServiceCode);
                        $stmt -> execute();
                        
                        $module = $this->dbManager->bindResultArray($stmt);
        
                        if(!$stmt->error)
                        {
                            while($stmt->fetch()){
                                $modules[] = $this->dbManager->getCopy($module);
                            }
                        }
        
                        $stmt -> close();
                        if(count($modules) > 0)
                            return true;
        }
    }
    
    function getModulesList($pUserId){
        if($pUserId > 0 && $stmt = $this->dbManager->connection -> prepare("
                select distinct una_custom_modules.id as id, 
                una_custom_modules.state as state, 
                una_custom_modules.label as label, 
                una_custom_modules.code as code 
                from una_custom_modules, 
                una_custom_join_modules_profiles, 
                una_custom_join_users_profiles 
                where una_custom_modules.id = una_custom_join_modules_profiles.module_id 
                and (
                (una_custom_join_modules_profiles.profile_id = una_custom_join_users_profiles.profile_id 
                and  una_custom_join_users_profiles.user_id  = ?) 
                or una_custom_join_modules_profiles.profile_id = 1) 
                order by una_custom_modules.order")){
                $stmt -> bind_param("i",
                        trim($pUserId));
                $stmt -> execute();
/*
                $stmt->bind_result($moduleId, $moduleState, $moduleLabel, $moduleCode);
                $modules = array();
                while ($stmt->fetch()) {

                    $module['id'] = $moduleId;
                    $module['state'] = $moduleState;
                    $module['label'] = $moduleLabel;
                    $module['code'] = $moduleCode;
                    

                    $modules[] = $module;
                }
*/
                $module = $this->dbManager->bindResultArray($stmt);
                
                if(!$stmt->error)
                {
                    while($stmt->fetch()){
                        $modules[] = $this->dbManager->getCopy($module);
                    }
                }
                
                $stmt -> close();
                if(count($modules) > 0)
                    return $modules;
        }
    }
}