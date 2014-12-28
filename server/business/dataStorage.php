<?php

class DataStorage {
    function __construct($pConfig, $pDBManager){
        if($pDBManager != null)
            $this->dbManager = $pDBManager;


    }

    function storeTeamData($pData){
        if ($this->dbManager && $stmt = $this->dbManager->connection -> prepare("
                insert into una_teams
                (name, telephone, address, city,
                country, contact_name, contact_surname,
                contact_telephone, contact_email)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {


                $stmt -> bind_param("sssssssss",
                        $pData->club->name,
                        $pData->club->telNumber,
                        $pData->club->address,
                        $pData->club->city,
                        $pData->club->country,
                        $pData->contact->name,
                        $pData->contact->surname,
                        $pData->contact->telNumber,
                        $pData->contact->email);

                /* execute query */
                $stmt -> execute();
                $teamId = $stmt->insert_id;
                $stmt -> close();

        }else{
            print $this->dbManager->connection->error;
        }

        if($teamId > 0){
            $crewCnt = 0;

            foreach($pData->crews as $crew){
                $crewCnt++;

                $crewId = 0;
                if ($this->dbManager && $stmt = $this->dbManager->connection -> prepare("
                        insert into una_crews
                        (category, fk_team_id)
                        values (?, ?, ?, ?, ?)")) {
                        $stmt -> bind_param("ssssi", $crew->category, $teamId);
                        $stmt -> execute();

                        $crewId = $stmt->insert_id;
                        $stmt -> close();

                }else{
                    print $this->dbManager->connection->error;
                }
                if($crewId > 0){
                    $membersCnt = 0;
                    foreach($crew->members as $member){
                        if ($this->dbManager && $stmt = $this->dbManager->connection -> prepare("
                                insert into una_members
                                (name, surname, sex, licence, captain, fk_crew_id)
                                values (?, ?, ?, ?, ?, ?)")) {
                                $captain = ($membersCnt == $crew->captain) ? 1 : 0;
                                $stmt -> bind_param("ssssii", $member->name, $member->surname, $member->sex, $member->licence, $captain, $crewId);
                                $stmt -> execute();
                                $stmt -> close();


                        }else{
                            print $this->dbManager->connection->error;
                        }
                    }
                }
            }
            foreach($pData->coach as $coach){
                if ($this->dbManager && $stmt = $this->dbManager->connection -> prepare("
                        insert into una_coaches
                        (name, surname, sex, fk_team_id)
                        values (?, ?, ?, ?)")) {
                        $stmt -> bind_param("sssi", $coach->name, $coach->surname, $coach->sex, $teamId);
                        $stmt -> execute();
                        $stmt -> close();
                }else{
                    print $this->dbManager->connection->error;
                }
            }
        }
        return $teamId; 
    }
}
