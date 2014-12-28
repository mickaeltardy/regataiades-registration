<?php
class DBManager{
    function __construct($pConfig){
        $this->connection = new mysqli($pConfig['host'], $pConfig['user'], $pConfig['password']);

        //$selected = mysqli_select_db($db, $conf -> database);
        $this->dbSelection = $this->connection->select_db($pConfig['database']);

        if (!$this->dbSelection) {
            die ('Cannot use DB : '.mysqli_error($this->connection));
        }
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        if (!$this->connection)
            die('Failed to connect to the database. Please retry the request later.');
        
        $this->connection->query("SET character_set_results=utf8_general_ci");
        $this->connection->query("set names 'utf8'");
    }
    
    
    function bindResultArray($stmt)
    {
        
        $meta = $stmt->result_metadata();
        
        $result = array();
        while ($field = $meta->fetch_field())
        {
            $result[$field->name] = NULL;
            $params[] = &$result[$field->name];
        }
        
        call_user_func_array(array($stmt, 'bind_result'), $params);
        return $result;
    }
    
    function getCopy($row)
    {
        return array_map(create_function('$a', 'return $a;'), $row);
    }
    
}