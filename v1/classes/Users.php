<?php

class Users extends Schema
{
    protected $query;

    private $table = USERS;
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
            $this->column('fname', $this->varchar(100), ''),
            $this->column('lname', $this->varchar(100), ''),
            $this->column('email', $this->varchar(100), ''),
            $this->column('phone', $this->varchar(50), ''),
            $this->column('photo', $this->varchar(100), 'DEFAULT \'avatar.png\''),
            $this->column('account', $this->varchar(5), ''),
            $this->column('token', $this->char(128), ''),
            $this->column('password', $this->char(128), ''),
            $this->column('vcode', $this->char(6), ''),
            $this->column('ip', $this->char(128), ''),
            $this->column('status', $this->tinyint(1), ''),
            $this->column('deleted', $this->tinyInt(1), 'DEFAULT \'0\''),
            $this->column('created', $this->datetime(), ''),
            $this->column('updated', $this->datetime(), ''),
        ];

        return $this->create($this->table, $array);
    }

    public function register($fname, $lname, $email, $phone, $account, $password)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $password = base64_encode(hash('SHA384', $password, true));
        $password = password_hash($password, PASSWORD_DEFAULT);

        $fn = new Functions();
        $vcode = $fn->randomStr(4);

        //create table

        if ($this->createTable()) {
            $data = [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'phone' => $phone,
                'account' => $account,
                'password' => $password,
                'token' => md5(rand().time()),
                'vcode' => $vcode,
                'status' => 0,
                'ip' => $this->ip(),
                'created' => $this->createdAt(),
            ];

            if(empty($fname) || empty($lname)) {
                return array('response' => 'error', 'message' => 'Please enter your full name');
            } elseif (empty($account)) {
                return array('response' => 'error', 'message' => 'No account type detected. Restart the app and try again.');
            } elseif (empty($email)) {
                return array('response' => 'error', 'message' => 'Please enter your email address');
            } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return array('response' => 'error', 'message' => "Invalid email format.");
            } elseif (empty($password)) {
                return array('response' => 'error', 'message' => 'Please enter your password');
            } else if (strlen($password) < 8) {
                return array('response' => 'error', 'message' => 'Password must be at least 8 characters long');
            } elseif ($this->checkUserExists($email) > 0) {
                return array('response' => 'error', 'message' => 'This account already exists.');
            } else {
                if ($this->insert($this->table, $data)) {
                    http_response_code(200);
                            
                    // $this->sendVCode($email, $fname, $vcode);

                    return array('response' => 'success', 'message' => 'Account created successfully.');
                } else {
                    //http_response_code(503);
                    return array('response' => 'error', 'message' => 'Error creating account.');
                }
            }
        } else {
            return array('response' => 'error', 'message' => 'Error creating table');
        }
    }

    public function login($username,$password) 
    {
        $hash = base64_encode(hash('SHA384', $password, true));
        
        $selector = ['*'];

        $conditionData = [$username,$username];

        $clause = "phone = ? OR email = ? AND deleted = 0 LIMIT 1";

        $res = $this->select($this->table, $selector, $conditionData, $clause);

        if($res['count'] > 0) {
            //password correct
            if (password_verify($hash, $res['data'][0]['password']) === true) {
                
                // if ($res['data'][0]['status'] === 0) {
                //     //account not verified
                //     return array("response" => "success", "verified" => "false", "email" => $res['data'][0]['email'], "phone" => $res['data'][0]['phone']);
                // } else if ($res['data'][0]['status'] === 1) {
                //     //account verified
                //     return array("response" => "success", "verified" => "true", "data" => $res['data'][0]);
                // } else {
                //     //account suspended/blocked
                //     return array("response" => "error", "message" => "Account suspended. Contact admin.");
                // }                
                
                /**
                 * $res['data'][0]['password'] = "";
                 * $res['data'][0]['token'] = "";
                 * return array("response" => "success", "verified" => "true", "data" => $res['data'][0]);
                 */          
                
                 $res['data'][0]['password'] = "";
                 $res['data'][0]['token'] = "";
                 return array("response" => "success", "verified" => "true", "data" => $res['data'][0]);      
            } else {
                //incorrect password
                return array('response' => 'error', 'message' => 'Incorrect password.');
            }

        } else {
            // account does not exist
            return array('response' => 'error', 'message' => 'This account does not exist.');
        }
    }
    
    public function checkUserExists($email)
    {        

        $selector = ['*'];

        $conditionData = [$email];

        $clause = "email = ? LIMIT 1";

        return $this->selectCount($this->table, $selector, $conditionData, $clause);
    }    

    public function resend($username){
        $vcode = $this->randomStr(4);
        $user = $this->userInfo($username);
        if(!empty($user)) {    
            $this->updateUserCol($username,'vcode', $vcode); //update the vcode after sending it to the user via email
            $this->sendVCode($user['email'], $user['fname'], $vcode);            
            $response = array('response'=>'success','message' => 'An OTP has been sent to your email.', 'data' => $user);
            
        } else {
            $response = array('response' => 'error', 'message' => 'This account does not exist.');
        }        
        
        return $response;
    }

    public function recover($username){
        $mail = new Email();
        $user = $this->userInfo($username);
        if(!empty($user)) {
            $message = "Your account reset code is: <br>";  
            $message .= "<h1>".$user['vcode']."</h1>";  
            $mail->sendUserMail($user['email'], "Account Reset Code", $message);
            $response = array('response'=>'success','message' => 'An OTP has been sent to your email.', 'data' => $user);
            
        } else {
            $response = array('response' => 'error', 'message' => 'This account does not exist.');
        }
        
        return $response;
    }

    public function resetPassword($email, $pwd, $otp)
    {
        $response = [];
        $code = $this->randomStr(4); //new verification code
        $vcode = $this->userInfo($email)['vcode']; //current verification code
        $fname = $this->userInfo($email)['fname']; //current verification code

        $password = base64_encode(hash('SHA384', $pwd, true));
        $password = password_hash($password, PASSWORD_DEFAULT);

        if ($vcode !== $otp) {
            $response = array('response' => 'error', 'message' => 'Invalid OTP!');
        } elseif (strlen($pwd) < 8) {
            $response = array('response' => 'error', 'message' => 'Wrong password format.');
        } else {
            $data = [
                'password' => $password,
                'vcode' => $code,
                'token' => md5(rand().time()),
                'updated' => $this->createdAt(),
            ];
    
            $conditionData = [$email,$otp];
    
            $array = [$data, $conditionData];
    
            $clause = "email = ? AND vcode = ?";
    
            if ($this->update($this->table, $array, $clause)) {
                $this->passwordReset($fname, $email);
                $response = array('response' => 'success', 'message' => 'Your password has been reset successfully!');
            } else {
                $response = array('response' => 'error', 'message' => 'Error resetting password. Confirm your OTP and try again.');
            }
    
        }

        return $response;
    }

    public function allUsers()
    {
        

        $selector = ["*"];

        $conditionData = [];

        $clause = "";

        return $this->select($this->table, $selector, $conditionData, $clause);
    }

    public function updateUserProfileCol($col, $string, $uid)
    {
        $response = false;
        $query = "UPDATE " . USERS . " SET " . $col . " = ? WHERE uid = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param('ss', $string, $uid);

        if ($stmt->execute()) {
            $stmt->close();
            $response = true;
        }
        
        return $response;
    }

    public function updateUser($uid, $fname, $lname, $phone, $photo)
    {
        $vcode = $this->randomStr(4);
        $data = [
            'fname' => $fname,
            'lname' => $lname,
            'phone' => $phone,
            'photo' => $photo,
            'vcode' => $vcode,
            'token' => md5(rand().time()),
            'updated' => $this->createdAt(),
        ];

        $conditionData = [$uid];

        $array = [$data, $conditionData];

        $clause = "uid = ?";

        if ($this->update($this->table, $array, $clause)) {
            return array('response' => 'success', 'message' => 'Profile Updated successfully!');
        } else {
            return array('response' => 'error', 'message' => 'Could not update profile.');
        }

    }

    public function editUser($fname, $lname, $phone, $practice, $photo, $gender, $uid)
    {
        $num = $this->checkUserExists($email, $phone);

        if ($num === 0) {
            //user does not exist
            $this->errMsg = 'User does not exist!';

        } else if ($num > 1) {
            //multiple user accounts detected
            $this->errMsg = 'Multiple user accounts with same data exists!';

        } else {
            if ($photo === "null") {
                $query1 = "UPDATE " . USERS . " SET fname = ?,lname = ?,phone = ?,updated_at = ? WHERE email = ? AND uid = ?";
                $this->stmt = $this->conn->prepare($query1);
                $this->stmt->bind_param('sssssi', $fname, $lname, $phone, $this->createdAt(), $email, $uid);
            }
            if ($photo !== "null") {
                $query2 = "UPDATE " . USERS . " SET fname = ?,lname = ?,phone = ?,photo=?,updated_at = ? WHERE email = ? AND uid = ?";
                $this->stmt = $this->conn->prepare($query2);
                $this->stmt->bind_param('ssssssi', $fname, $lname, $phone, $photo, $this->createdAt(), $email, $uid);
            }
            //PREPARE STATEMENT

            if ($this->stmt->execute()) {
                http_response_code(200);
                $this->msg = 'Updated successfully!';
                $userData = $this->userInfo($email);
            } else {
                // set response code - 503 service unavailable
                http_response_code(503);
                $this->errMsg = 'Something is not right!';
            }

        }
        if ($this->errMsg) {
            return array('response' => 'error', 'message' => $this->errMsg);
        }

        if ($this->msg) {
            return array('response' => 'success', 'message' => $this->msg, 'userData' => $userData);
        }

    }

    private function user($identifier)
    {
        // $this->response = [];
        $this->query = "SELECT * FROM " . USERS . " WHERE uid = ? OR email = ? OR phone = ?";
        //PREPARE STATEMENT
        $this->stmt = $this->conn->prepare($this->query);
        $this->stmt->bind_param('sss', $identifier, $identifier, $identifier);
        $this->stmt->execute();
        $this->result = $this->stmt->get_result();
        $this->num = $this->result->num_rows;
        $this->row = $this->result->fetch_assoc();

        $this->stmt->close();

        

        return $this->row;
    }

    public function updateUserCol($identifier,$column, $value)
    {
        $data = [
            $column => $value,
            'updated' => $this->createdAt(),
        ];

        $conditionData = [$identifier,$identifier,$identifier];

        $array = [$data, $conditionData];

        $clause = "uid = ? OR email = ? OR phone = ?";

        if ($this->update($this->table, $array, $clause)) {
            return array('response' => 'success', 'message' => 'Updated successfully!');
        } else {
            return array('response' => 'error', 'message' => 'An internal error occurred.');
        }
    }

    public function userInfo($identifier)
    {
        $selector = ['*',"'' AS password"];

        $conditionData = [$identifier,$identifier,$identifier];

        $clause = "uid = ? OR phone = ? OR email = ? LIMIT 1";

        $result = $this->select($this->table, $selector, $conditionData, $clause);

        if($result['count'] > 0) {
            return $result['data'][0];
        } else {
            return [];
        }
    }

    public function changePwd($uid, $email, $pwd, $repwd)
    {
        $notification = new Notifications();
        $response = [];
        $code = $this->randomStr(4); //new verification code
        $vcode = $this->userInfo($email)['vcode']; //current verification code
        $fname = $this->userInfo($email)['fname']; //current verification code

        $password = base64_encode(hash('SHA384', $pwd, true));
        $password = password_hash($password, PASSWORD_DEFAULT);

        if ($pwd !== $repwd) {
            $response = array('response' => 'error', 'message' => 'Passwords don\'t match. Try again.');
        } elseif (strlen($pwd) < 8) {
            $response = array('response' => 'error', 'message' => 'Wrong password format.');
        } else {
            $data = [
                'password' => $password,
                'vcode' => $code,
                'token' => md5(rand().time()),
                'updated' => $this->createdAt(),
            ];
    
            $conditionData = [$email,$uid];
    
            $array = [$data, $conditionData];
    
            $clause = "email = ? AND uid = ?";
    
            if ($this->update($this->table, $array, $clause)) {
                $this->passwordReset($fname, $email); //inform user via email
                $notification->add($uid, 'You have changed your password. Report this action to '.$this->supportEmail.', if you did not initiate it.', 'Password Change');
                $response = array('response' => 'success', 'message' => 'Your password has been reset successfully!');
            } else {
                $response = array('response' => 'error', 'message' => 'Error resetting password. Confirm your OTP and try again.');
            }
    
        }

        return $response;
    }

    public function validateVCode($vcode, $email, $phone)
    {
        $account = '';

        $query = "SELECT uid,fname,email,phone FROM " . USERS . " WHERE vcode = ? AND (email = ? AND phone = ?) LIMIT 1";

        //PREPARE STATEMENT
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $vcode, $email, $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        $num = $res->num_rows;
        
        $stmt->close();

        

        return $num;
    }

    public function checkAccountVerify($username)
    {
        $status = 1;

        $query = "SELECT * FROM " . USERS . " WHERE (email = ? OR phone = ?) AND status = ? LIMIT 1";
        //PREPARE STATEMENT
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $username, $username, $status);
        $stmt->execute();
        $res = $stmt->get_result();
        $num = $res->num_rows;

        return $num;

    }

    public function verifyX($vcode, $email, $phone)
    {
        //SANITIZE INPUTS
        $status = 1;
        // $nvcode = $this->randomStr();

        if (empty($vcode)) {
            $this->errMsg = 'Please enter verification code.';
        } else if (empty($email)) {
            $this->errMsg = 'Something is wrong. Try again later.';
        } else if (empty($phone)) {
            $this->errMsg = 'No phone number detected';
        } else if ($this->confirmVCode($vcode, $email, $phone) === 0) {
            $this->errMsg = 'Wrong code. Try again!';
        } else {
            $query = "UPDATE " . USERS . " SET status = ?, vcode = ? WHERE (email = ? AND phone = ?) AND vcode = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sssss', $status, $nvcode, $email, $phone, $vcode);
            if ($stmt->execute()) {
                $this->msg = 'Account verified successfully!';
            } else {
                $this->errMsg = 'Unable to verify account.';
            }
        }

        if ($this->errMsg) {
            return array('response' => 'error', 'message' => $this->errMsg);
        }

        if ($this->msg) {
            return array('response' => 'success', 'message' => $this->msg);
        }
    }

    public function verify($vcode, $email)
    {
        $fn = new Functions();
        $nvcode = $fn->randomStr(4); //new verification code to be updated after successful verification
        $status = 1;

        $welcome = "You are welcome!";//change this to the real welcome message

        $data = [
            'vcode' => $nvcode,
            'status' => $status,
        ];

        $conditionData = [
            'email' => $email,
            'vcode' => $vcode,
        ];

        $array = [$data, $conditionData];

        $clause = "email = ? AND vcode = ?";

        if($this->confirmVCode($vcode,$email) === 0){
            return array('response' => 'error', 'message' => 'Invalid OTP!');
        } else {
            if ($this->update($this->table, $array, $clause)) {
                $this->welcomeEmail($email, $this->userInfo($email)['fname']);
                return array('response' => 'success', 'message' => 'Account verified successfully');
            } else {
                return array('response' => 'error', 'message' => 'Error verifying account.');
            }
        }
    }
    
    public function confirmVCode($vcode,$email)
    {
        $selector = ['uid'];

        $conditionData = [$vcode,$email];

        $clause = "vcode = ? AND email = ?";

        return $this->selectCount($this->table, $selector, $conditionData, $clause);
    }

    public function listAllUsers()
    {
        

        $selector = ['*'];

        $conditionData = [];

        $clause = "ORDER BY created DESC";

        return $this->select($this->table, $selector, $conditionData, $clause);
    }
    
    public function selectUserBy($column,$value)
    {        
        $selector = ['*',"'' AS password", "'' AS token"];

        $conditionData = [$value];

        $clause = $column." = ? ORDER BY created DESC";

        return $this->select($this->table, $selector, $conditionData, $clause);
    }  

    public function trash($id)
    {
        $data = [
            'deleted' => 1,
            'updated' => $this->createdAt(),
        ];

        $conditionData = [$uid];

        $array = [$data, $conditionData];

        $clause = "uid = ?";

        if ($this->update($this->table, $array, $clause)) {
            return array('response' => 'success', 'message' => 'Moved to trash!');
        } else {
            return array('response' => 'error', 'message' => 'An internal error occurred.');
        }
    }

    public function restore($uid)
    {
        $data = [
            'deleted' => 0,
            'updated' => $this->createdAt(),
        ];

        $conditionData = [$uid];

        $array = [$data, $conditionData];

        $clause = "uid = ?";

        if ($this->update($this->table, $array, $clause)) {
            return array('response' => 'success', 'message' => 'Restored successfully!');
        } else {
            return array('response' => 'error', 'message' => 'An internal error occurred.');
        }
    }

    public function deleteUser($uid)
    {
        $selector = [];

        $conditionData = [$uid];

        $clause = "uid = ?";

        if ($this->delete($this->table, $conditionData, $clause)) {
            return array('response' => 'success', 'message' => 'Deleted');
        } else {
            return array('response' => 'error', 'message' => 'Not Deleted');
        }
    }

}