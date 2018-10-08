<?php
session_start();
include_once("connect.php"); // Include yout connection script
// Files that inculde this file at the very top would NOT require 
// connection to database or session_start(), be careful.
// Initialize some vars
$user_ok = false;
$log_id = "";
$log_username = "";
$log_password = "";
// User Verify function
function evalLoggedUser($link,$id,$username,$password){
	$sql = "SELECT ip FROM users WHERE id='$id' AND username='$username' AND password='$password' LIMIT 1";
    $query = mysqli_query($link, $sql);
    $numrows = mysqli_num_rows($query);
	if($numrows > 0){
		return true;
	}
}
if(isset($_SESSION["id"]) && isset($_SESSION["username"]) && isset($_SESSION["password"])) {
	$log_id = preg_replace('#[^0-9]#', '', $_SESSION['id']);
	$log_username = preg_replace('#[^a-z0-9]#i', '', $_SESSION['username']);
	$log_password = preg_replace('#[^a-z0-9]#i', '', $_SESSION['password']);
	// Verify the user
	$user_ok = evalLoggedUser($link,$log_id,$log_username,$log_password);
} else if(isset($_COOKIE["id"]) && isset($_COOKIE["user"]) && isset($_COOKIE["pass"])){
	$_SESSION['id'] = preg_replace('#[^0-9]#', '', $_COOKIE['id']);
    $_SESSION['username'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['user']);
    $_SESSION['password'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['pass']);
	$log_id = $_SESSION['id'];
	$log_username = $_SESSION['username'];
	$log_password = $_SESSION['password'];
	// Verify the user
	$user_ok = evalLoggedUser($link,$log_id,$log_username,$log_password);
	if($user_ok == true){
		// Update their lastlogin datetime field
		$sql = "UPDATE users SET lastlogin=now() WHERE id='$log_id' LIMIT 1";
        $query = mysqli_query($link, $sql);
	}
}
?>
