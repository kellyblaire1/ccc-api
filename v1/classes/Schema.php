<?php

class Schema
{
    use DataTypes; //inherit DataTypes trait
    // use Email; //inherit Email trait
    // use Functions; //inherit Functions trait

    protected $host = HOST;
    protected $port = PORT;
    protected $username = USERNAME;
    protected $password = PASSWORD;
    protected $database = DATABASE;
    protected $charset = CHARSET;
    
    protected $conn;
    
    private $row;

    private $response = array();
    private $values = array();
    
	protected $query;
    protected $show_errors = TRUE;
    protected $query_closed = TRUE;
	public $query_count = 0;

    public $id;

    public function __construct() {
	}

    public function openConn() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
		if ($this->conn->connect_error) {
			die('Failed to connect to MySQL - ' . $this->conn->connect_error);
		}
        
		$this->conn->set_charset($this->charset);
    }

    public function closeConn() {
        return $this->conn->close();
    }

    public function pingConn()
    {
        // Check if server is alive
        if ($this->conn-> ping()) {
            return "Connection is ok!";
        } else {
            return "Error: ". $this->conn->error;
        }
    }

    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function createdAt()
    {
        return date('Y-m-d H:i:s');
    }

    public function column($name, $type, $options)
    {
        return "{$name} {$type} {$options}";
    }

    public function create($table, $arrays)
    {
        $this->openConn(); //open db connection
        $id = $table==USERS?'uid':'id';
        $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (";
        $sql .= "{$id} INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";
        $sql .= implode(",", $arrays);
        $sql .= ")";

        if ($this->conn->query($sql) === true) {
            // set response code - 200 OK
            http_response_code(200);

            $response = true;
        } else {
            // set response code - 503 service unavailable
            http_response_code(503);
            // echo $this->conn->error;

            $response = false;
        }

        $this->closeConn();
        return $response;
    }

    public function drop($table)
    {
        $this->openConn(); //open db connection
        $sql = "DROP TABLE IF EXISTS ".$table;

        if($this->conn->query($sql)) {
            http_response_code(200);

            $response = true;
        }
        
        // set response code - 503 service unavailable
        http_response_code(503);
        $response = false;
        $this->closeConn(); //open db connection

        return $response;
    }

    public function insert($table, $data)
    {
        $this->openConn();
        // $response = 'false'; //response is truity here
        $columns = array();
        $values = array();

        foreach ($data as $key => $value) {
            $columns[] = $key;
            $values[] = $value;
        }

        $variables = str_repeat('?,', sizeof($columns) - 1) . "?"; //repeat query params to match columns count
        $types = str_repeat('s', sizeof($values)); //repeat types using string data type to match columns count
        $columns = implode(",", $columns); //implode and separate columns name with a semi-colon

        /* RUN INSERT QUERY */
        $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $variables . ")"; 
        
        // Prepare Statement
        $stmt = $this->conn->prepare($query);
        // Bind Parameters
        $stmt->bind_param($types, ...$values);
        // Execute Statement
        if ($stmt->execute()) {
            // set response code - 200 OK
            // http_response_code(200);
            $id = $stmt->insert_id; //get the unique id of the data inserted
            $stmt->close(); //close statement
            $response = $id;
        } else {
            $response = false;
        }
        
        $this->closeConn();

        return $response;
    }

    public function insertWithID($table, $data,$callBack)
    {
        $this->openConn(); //open db connection
        $response = null;
        $columns = array();
        $values = array();

        foreach ($data as $key => $value) {
            $columns[] = $key;
            $values[] = $value;
        }

        $variables = str_repeat('?,', sizeof($columns) - 1) . "?"; //repeat query params to match columns count
        $types = str_repeat('s', sizeof($values)); //repeat types using string data type to match columns count
        $columns = implode(",", $columns); //implode and separate columns name with a semi-colon

        /* RUN INSERT QUERY */
        $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $variables . ")";
        
        // prepare statement
        $stmt = $this->conn->prepare($query);
        //bind parameters
        $stmt->bind_param($types, ...$values); 
        if ($stmt->execute()) {
            $callBack;
            // set response code - 200 OK
            http_response_code(200);
            $id = $stmt->insert_id; //get the unique id of the data inserted
            $response = array('response' => "success", 'id' => $id);
            $stmt->close();
        } else {
            http_response_code(503);
            $response = array('response' => 'error', 'message' => 'Error inserting data.');
        }
        //close connection
        $this->closeConn(); 

        return $response;
    }

    public function update($table, $data, $clause)
    {
        $this->openConn();
        // $response = false; //truity
        $columns = [];
        $values = [];
        $condition = [];
        $conditionValues = [];

        foreach ($data[0] as $key => $value) {
            $columns[] = $key;
            $values[] = $value;
        }

        foreach ($data[1] as $key => $value) {
            $condition[] = $key;
            $conditionValues[] = $value;
        }

        $columns = implode("=?,", $columns) . "=?"; //implode and separate columns name with a semi-colon
        $conditionVariables = implode("=? AND ", $condition) . "=?";

        $dataTypes = str_repeat('s', sizeof($values)) . str_repeat('s', sizeof($conditionValues));
        // $dataValues = implode(',', $values) . ',' . implode(',', $conditionValues);
        $dataValues = array_merge($values, $conditionValues);

        /* RUN INSERT QUERY */
        $query = "UPDATE " . $table . " SET " . $columns . " WHERE " . $clause;

        // Prepare Statement
        $stmt = $this->conn->prepare($query);
        // Bind Parameters
        $stmt->bind_param($dataTypes, ...$dataValues);
        // Execute Statement
        if ($stmt->execute()) {
            // set response code - 200 OK
            // http_response_code(200);
            $stmt->close(); //close statement
            $response = true;
        } else {
            // http_response_code(500);
            $response = false;
        }
        
        $this->closeConn();

        return $response;
    }

    public function select($table, $selector, $data, $clause)
    {
        $this->openConn();
        $response = [];
        $selector = implode(',', $selector);

        if (!empty($clause) && count($data) > 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " WHERE " . $clause;
        } elseif (!empty($clause) && count($data) === 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " " . $clause;
        } else {
            $query = "SELECT " . $selector . " FROM " . $table;
        }

        //PREPARE STATEMENT        
        $stmt = $this->conn->prepare($query);
        //bind parameters if condition exists, else remove it
        if (!empty($clause) && count($data) > 0) {
            $values = [];
            $dataTypes = str_repeat('s', sizeof($data));
            foreach ($data as $value) {
                $values[] = $value;
            }
            $stmt->bind_param($dataTypes, ...$values);
        }

        if($stmt->execute()){
            // set response code - 200 OK
            // http_response_code(200);

            $res = $stmt->get_result();
            $num = $res->num_rows;

            $response['count'] = $num;
            while ($row = $res->fetch_assoc()) {
                $response['data'][] = $row;
            }
        }
        // close statement
        $stmt->close();

        //close connection
        $this->closeConn();

        return $response;
    }

    public function selectNoCount($table, $selector, $data, $clause)
    {
        $this->openConn();
        $response = [];
        $selector = implode(',', $selector);

        if (!empty($clause) && count($data) > 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " WHERE " . $clause;
        } elseif (!empty($clause) && count($data) === 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " " . $clause;
        } else {
            $query = "SELECT " . $selector . " FROM " . $table;
        }
        //PREPARE STATEMENT
        $stmt = $this->conn->prepare($query);
        
        //bind parameters if condition exists, else remove it
        if (!empty($clause) && count($data) > 0) {
            $values = [];
            $dataTypes = str_repeat('s', sizeof($data));
            foreach ($data as $value) {
                $values[] = $value;
            }

            $stmt->bind_param($dataTypes, ...$values);
        }

        if($stmt->execute()) {
            // set response code - 200 OK
            http_response_code(200);

            $res = $stmt->get_result();
            $num = $res->num_rows;
            $stmt->close();// close statement
    
            if ($num > 0) {
                while ($row = $res->fetch_assoc()) {
                    $response[] = $row;
                }
            }
        }

        //close connection
        $this->closeConn();

        return $response;        
    }

    public function selectCount($table, $selector, $data, $clause)
    {
        $this->openConn();
        $response = 0;
        $selector = implode(',', $selector);

        if (!empty($clause) && count($data) > 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " WHERE " . $clause;
        } elseif (!empty($clause) && count($data) === 0) {
            $query = "SELECT " . $selector . " FROM " . $table . " " . $clause;
        } else {
            $query = "SELECT " . $selector . " FROM " . $table;
        }

        //PREPARE STATEMENT        
        $stmt = $this->conn->prepare($query);
        //bind parameters if condition exists, else remove it
        if (!empty($clause) && count($data) > 0) {
            $values = [];
            $dataTypes = str_repeat('s', sizeof($data));
            foreach ($data as $value) {
                $values[] = $value;
            }
            $stmt->bind_param($dataTypes, ...$values);
        }

        if($stmt->execute()){
            // set response code - 200 OK
            // http_response_code(200);

            $res = $stmt->get_result();
            $num = $res->num_rows;

            $response = $num;
        }
        // close statement
        $stmt->close();

        //close connection
        $this->closeConn();

        return $response;
    }

    public function customSelect($query, $data)
    {
        $this->openConn();
        $response = [];
        //PREPARE STATEMENT
        $stmt = $this->conn->prepare($query);
        //bind parameters if condition exists, else remove it
        if (count($data) > 0) {
            $values = [];
            $dataTypes = str_repeat('s', sizeof($data));
            foreach ($data as $value) {
                $values[] = $value;
            }

            $stmt->bind_param($dataTypes, ...$values);
        }

        if($stmt->execute()) {
            // set response code - 200 OK
            http_response_code(200);

            $res = $stmt->get_result();
            $num = $res->num_rows;
            $stmt->close(); // close statement
            
            $response['count'] = $num;
            while ($row = $res->fetch_assoc()) {
                $response['data'][] = $row;
            }
        }
        
        //close connection
        $this->closeConn();
        return $response;
    }

    public function delete($table, $data, $clause)
    {
        $this->openConn();
        $response = false;

        $query = "DELETE FROM " . $table . " WHERE " . $clause;

        if($stmt = $this->conn->prepare($query)) {

            if (!empty($clause) && count($data) > 0) {
                $values = [];
                $dataTypes = str_repeat('s', sizeof($data));
                foreach ($data as $value) {
                    $values[] = $value;
                }
                $stmt->bind_param($dataTypes, ...$values);
            }

            if ($stmt->execute()) {
                // set response code - 200 OK
                http_response_code(200);
                $response = true;
            }

            // close statement
            $stmt->close();
        }
        
        //close connection
        $this->closeConn();

        return $response;
    }

    public function first($data)
    {
        return $data['data'][0];
    }

}