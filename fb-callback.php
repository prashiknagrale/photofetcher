<?php
 
session_start();
require_once __DIR__ . '/Facebook/autoload.php'; // download official fb sdk for php @ https://github.com/facebook/php-graph-sdk

$fb = new Facebook\Facebook([
  'app_id' => '728550657481331',
  'app_secret' => '0c1dec251d88f7da0668452f9172585c',
  'default_graph_version' => 'v2.2',
]);

$helper = $fb->getRedirectLoginHelper();

if(isset($_GET['state'])){
	$helper->getPersistentDataHandler()->set('state',$_GET['state']);
}

$permissions = ['user_photos']; // optionnal

try {
	if (isset($_SESSION['facebook_access_token'])) {
	$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		$_SESSION['facebook_access_token'] = (string) $accessToken;

	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	// validating the access token
	try {
		$request = $fb->get('/me');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		if ($e->getCode() == 190) {
			unset($_SESSION['facebook_access_token']);
			$helper = $fb->getRedirectLoginHelper();
			$loginUrl = $helper->getLoginUrl('https://rtcampassignment.ml/webpage/', $permissions);
			echo "<script>window.top.location.href='".$loginUrl."'</script>";
		}
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	// getting all photos of user
	try {
		$photos_request = $fb->get('/me/photos/');
		$photos = $photos_request->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		 	echo 'I\'m here 2 already';

		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}



	$all_photos = array();
	if ($fb->next($photos)) {
		$photos_array = $photos->asArray();
		$all_photos = array_merge($photos_array, $all_photos);
		while ($photos = $fb->next($photos)) {
			$photos_array = $photos->asArray();
			$all_photos = array_merge($photos_array, $all_photos);
		}
	} else {
		$photos_array = $photos->asArray();
		$all_photos = array_merge($photos_array, $all_photos);
	}

	foreach ($all_photos as $key) {
		$photo_request = $fb->get('/'.$key['id'].'?fields=images');
		$photo = $photo_request->getGraphNode()->asArray();
		
		// echo '<img src="'.$photo['images'][2]['source'].'"><br>';
		
		echo '<div class="card">
		<img src="'.$photo['images'][2]['source'].'" width="400" height="300" alt="Computer Hope"><br>  <div class="container">

		    <a href="'.$photo['images'][2]['source'].'" download=" new title "><button class="button" >Download image</button></a>
		  </div>
		</div>';
	}

} else {
	$helper = $fb->getRedirectLoginHelper();
	$loginUrl = $helper->getLoginUrl('https://local/Myfirst/index.php');
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}