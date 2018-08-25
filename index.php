<?php

require_once 'facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '728550657481331', // Replace {app-id} with your app id
  'app_secret' => '0c1dec251d88f7da0668452f9172585c',
  'default_graph_version' => 'v2.2',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email,user_photos']; // Optional permissions
$loginUrl = $helper->getLoginUrl('https://localhost/Myfirst/fb-callback.php', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';

?>

