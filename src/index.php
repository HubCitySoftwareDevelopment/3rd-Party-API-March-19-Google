<style>
    .box {
        width:100%;
        text-align:center;
    }
    .request {
        margin-top:100px;
        display:block;
    }
</style>

<?php

/** Start the session and require our API */
session_start();
require_once('/api/src/Google/autoload.php');

/** Fill this out with our registere Google API keys  */
$client_id = 'INSERT HERE';
$client_secret = 'INSERT HERE';
$redirect_uri = 'INSERT HERE';

/** Establish a api client, set the id, secret, and redirect uri, then set our scope(s) */
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes('INSERT HERE');

/** If we're logging out, clear our access token */
if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}

/** If code was sent from Google, authenticate it and use it to generate and set an access token */
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

/** If our access token exists in the session, set it for our current use - otherwise create our url to pull Google data from */
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    $authUrl = $client->createAuthUrl();
}

/** If we have a valid access token, make some requests */
if ($client->getAccessToken()) {

}
?>

<!-- Display logic below; if we have an auth url set, show the sign in buttons, otherwise show our logout buttons and our data -->
<div class="box">
    <?php if (isset($authUrl)) { ?>
        <div class="request outerT">
            <h1>Welcome!</h1>
            <h2>Sign in below using Google:</h2>
            <a class='login' href='<?= $authUrl ?>'><img src="img/google.png" /></a>
        </div>
    <?php } else { ?>
        <div class="request">
            <a class='logout' href='?logout'><h1>Logout</h1></a>
        </div>
    <?php } ?>
</div>

<?php if (!isset($authUrl)) { ?>
<div class="data">

    <div>
        <h1>My Profile</h1>
        <h2>Name</h2>
        <h3><a href="mailto:example@example.com">example.com</a></h3>
        <h3><a href="http://www.example.com">Profile</a></h3>
    </div>
    <hr />

    <div>
        <h1>My Contacts</h1>
        <ul>
            <li>Contact 1</li>
            <li>Contact 2</li>
        </ul>
    </div>
    <hr />

    <h1>My Calendars</h1>
    <ul>
        <li><a href="#">Calendar 1</a></li>
        <li><a href="#">Calendar 2</a></li>
    </ul>
    <hr />

    <h1>Events:</h1>
    <h2>Event 1</h2>
    <h3>March 3, 2015 6:30 p.m. - 7:30 p.m.</h3>
    <h2>Event 2</h2>
    <h3>March 4, 2015 7:30 p.m. - 8:30 p.m.</h3>
    <hr />

</div>
<?php } ?>