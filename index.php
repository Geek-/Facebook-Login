<?php
ob_start();
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
$fb = new Facebook\Facebook([
  'app_id' => '757964754360580',
  'app_secret' => '0e01c0a241e8f173f701f9d57939a866',
  'default_graph_version' => 'v2.8',
  ]);
  
$redirect = 'http://iedu-eg.com/facebook/';
$helper = $fb->getRedirectLoginHelper();
	# Get the access token and catch the exceptions if any
	try 
	{
		 if(isset($_SESSION['facebook_access_token']))
		 {
		 	$accessToken=$_SESSION['facebook_access_token'];
		 }
		 else
		 	 {
			 $accessToken = $helper->getAccessToken();
		 	}
	
	} 
	
	catch(Facebook\Exceptions\FacebookResponseException $e)
	 {
	  // When Graph returns an error
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	 }
	  catch(Facebook\Exceptions\FacebookSDKException $e)
	   {
	  // When validation fails or other local issues
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
		}
	# If the 
	if (isset($accessToken)) 
	{
	  	// Logged in!
	 	// Now you can redirect to another page and use the
  		// access token from $_SESSION['facebook_access_token'] 
  		// But we shall we the same page
		// Sets the default fallback access token so 
		// we don't have to pass it to each request
		if(isset($_SESSION['facebook_access_token']))
		{
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
		 }
		else
		{
		 	$_SESSION['facebook_access_token']=(string) $accessToken;
			$oAuth2Client = $fb->getOAuth2Client();
			$longLivedAccessToken=$oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
			$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
		 }
		
		try 
		{
		  $response = $fb->get('/me?fields=email,name');
		  $userNode = $response->getGraphUser();
		}
		catch(Facebook\Exceptions\FacebookResponseException $e)
		 {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} 
		catch(Facebook\Exceptions\FacebookSDKException $e) 
		{
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
		// Fetching user Details
		// Connect to DB
		$link=mysqli_connect("localhost","iedu","@*K)?6Ev!eK9","iedu");
		//Storing user name
		$_SESSION['username']=$userNode->getName();
		$username=mysqli_real_escape_string($link,$_SESSION['username']);
		//Storing id
		$_SESSION['id']=$userNode->getId();
		$id=$_SESSION['id'];
		//Storing the ip Address
		$ip=preg_replace('#[^0-9.]#','',getenv('REMOTE_ADDR'));
		//Storing user FB profile image
		$image = 'https://graph.facebook.com/'.$userNode->getId().'/picture?width=200';
		//Storing the email 
		$_SESSION['email']=$userNode->getProperty('email');
		$email=$_SESSION['email'];
		//Checking User Email in the Database
		$query="SELECT * FROM users WHERE email='".mysqli_real_escape_string($link, $_SESSION['email'])."'";		
		$result=mysqli_query($link, $query);
		$results=mysqli_num_rows($result); // It will return 1 if the email exists and 0 if not...
		// COOKIES 
		/*
		setcookie("id",$id,strtotime('+30 days'),"/","","",TRUE);
		setcookie("username",$username,strtotime('+30 days'),"/","","",TRUE);
		setcookie("email",$email,strtotime('+30 days'),"/","","",TRUE);
		*/
				 
		// Checking user Status if FOUND redirect to mainpage 
		if($results) 
		{
			//UPDATE THEIR "IP" AND "LASTLOGIN" FIELDS
			$sql="UPDATE `users` SET `ip`='$ip', `lastlogin`=now() WHERE `username`='$username' LIMIT 1 ";
			$query=mysqli_query($link, $query);
			header("location:../");
			
		}
		// if NOT insert into DB and create a folder for him		
		else
		{
			//Insert the user into main users table in DB
			$sql = "INSERT INTO `users` (`username`, `email`, `id`,`avatar`,`ip`,`signup`,`lastlogin`,`notescheck`) VALUES ('$username', '$email', '$id','$image','$ip',now(),now(),now())";
			$query=mysqli_query($link, $sql);
			//Insert into useroptions table
			$useroptions="INSERT INTO `useroptions` (`username`, `background`, `id`) VALUES ('$username', 'original', '$id')";
			$queryOption=mysqli_query($link, $useroptions);
			//create user folder 
			if(!file_exists("facebookuser/$username"))
			{
				mkdir("facebookuser/$username", 0755);
				
			}
			if($query)
			{  	
    			header("location:../");
			} 
			else
			{
				echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
			}
		}
	
	}
	else
	{
		$permissions  = ['email'];
		$loginUrl = $helper->getLoginUrl($redirect,$permissions);
		echo '<a href="' . $loginUrl . '" style="text-decoration:none;">Log in with Facebook!</a>';
	}
?>
