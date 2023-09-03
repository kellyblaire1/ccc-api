<?php

class Accounts extends Schema
{
    protected $query;

    private $table = ACCOUNTS;
    private $stmt;
    private $result;
    private $num;
    private $row;
    private $msg = array();
    private $errMsg = array();
    private $data = array();
    private $response = array();

    private $rand = null;
    private $ip = null;
    private $created = null;

    private $dt;

    public function __construct()
    {}

    public function createTable()
    {

        $array = [
            $this->column('name', $this->varchar(100), ''),
            $this->column('created', $this->datetime(), ''),
            $this->column('updated', $this->datetime(), ''),
        ];

        return $this->create($this->table, $array);
    }

    public function add($name)
    {
        //create table
        if ($this->createTable()) {
            $data = [
                'name' => $name,
                'created' => $this->createdAt(),
            ];

            if(empty($name)) {
                return array('response' => 'error', 'message' => 'Please enter account group name');
            } elseif ($this->checkAccountExists($name) > 0) {
                return array('response' => 'error', 'message' => 'This account group already exists.');
            } else {
                if ($this->insert($this->table, $data)) {
                    http_response_code(200);
                            
                    return array('response' => 'success', 'message' => 'Account group created successfully.');
                } else {
                    http_response_code(503);
                    return array('response' => 'error', 'message' => 'Error creating account.');
                }
            }
        }
    }
    
    public function checkAccountExists($name)
    {        

        $selector = ['*'];

        $conditionData = [$name];

        $clause = "name = ? LIMIT 1";

        return $this->selectCount($this->table, $selector, $conditionData, $clause);
    }    

    public function allAccounts()
    {
        $selector = ["*"];

        $conditionData = [];

        $clause = "";

        return $this->select($this->table, $selector, $conditionData, $clause);
    }

    public function deleteAccount($id)
    {
        $selector = [];

        $conditionData = [$id];

        $clause = "id = ?";

        if ($this->delete($this->table, $conditionData, $clause)) {
            return array('response' => 'success', 'message' => 'Deleted');
        } else {
            return array('response' => 'error', 'message' => 'Not Deleted');
        }
    }

}