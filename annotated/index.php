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

session_start();

require_once('/api/src/Google/autoload.php');

$client_id = '22810186404-mjc04ropcn29un5r4o7vbcjvmgambtqe.apps.googleusercontent.com';
$client_secret = '7a8ijFBMVYW9wLF5AhlWSGc7';
$redirect_uri = 'http://localhost/test/annotated/index.php';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes(['https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.google.com/m8/feeds', 'https://www.googleapis.com/auth/calendar']);

/************************************************
If we're logging out we just need to clear our
local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}


/************************************************
If we have a code back from the OAuth 2.0 flow,
we need to exchange that with the authenticate()
function. We store the resultant access token
bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

/************************************************
If we have an access token, we can make
requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    $authUrl = $client->createAuthUrl();
}

/************************************************
If we're signed in we can go ahead and retrieve
the ID token, which is part of the bundle of
data that is exchange in the authenticate step
- we only need to do a network call if we have
to retrieve the Google certificate to verify it,
and that can be cached.
 ************************************************/
if ($client->getAccessToken()) {
    /** Get User information **/
    $req = new Google_Http_Request('https://www.googleapis.com/oauth2/v1/userinfo');
    $val = $client->getAuth()->authenticatedRequest($req);
    $user = json_decode($val->getResponseBody());

    /** Get Contact information **/
    $req = new Google_Http_Request('https://www.google.com/m8/feeds/contacts/default/full?alt=json');
    $val = $client->getAuth()->authenticatedRequest($req);
    $contacts = json_decode($val->getResponseBody());

    /** Get Calendar list **/
    $req = new Google_Http_Request('https://www.googleapis.com/calendar/v3/users/me/calendarList');
    $val = $client->getAuth()->authenticatedRequest($req);
    $calendar = json_decode($val->getResponseBody());

    /** Get Calendar information only if we've chosen a calendar */
    if (isset($_REQUEST['calendar'])) {
        $req = new Google_Http_Request('https://www.googleapis.com/calendar/v3/calendars/'.$_REQUEST['calendar'].'/events');
        $val = $client->getAuth()->authenticatedRequest($req);
        $events = json_decode($val->getResponseBody());
    }
}
?>

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

<div class="data">
    <?php if (isset($user)) { ?>
        <div>
            <h1>My Profile</h1>
            <pre><?php //var_dump($user); ?></pre>
            <img width="100" align="left" src="<?= $user->picture ?>" />
            <h2><?= $user->name ?></h2>
            <h3><a href="mailto:<?= $user->email ?>"><?= $user->email ?></a></h3>
            <h3><a href="<?= $user->link ?>">Profile</a></h3>
        </div>
        <hr />
    <?php } ?>

    <?php if(isset($contacts)) { ?>
        <div>
            <h1>My Contacts</h1>
            <pre><?php //var_dump($contacts); ?></pre>
            <ul>
                <?php foreach($contacts->feed->entry as $contact){ $t = '$t'; ?>
                    <?php if($contact->title->$t != ''){ ?>
                        <li><?= $contact->title->$t ?></li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
        <hr />
    <?php } ?>

    <?php if(isset($calendar)){ ?>
        <h1>My Calendars</h1>
        <pre><?php //var_dump($calendar); ?></pre>
        <ul>
            <?php foreach($calendar->items as $calendar){ ?>
                <li><a href="?calendar=<?= $calendar->id ?>"><?= $calendar->summary ?></a></li>
            <?php } ?>
        </ul>
        <hr />
    <?php } ?>

    <?php if(isset($events)){ ?>

        <h1>Events:</h1>
        <pre><?php //var_dump($events); ?></pre>

        <?php foreach($events->items as $event){ ?>
            <?php if(isset($event->summary) && isset($event->start->dateTime) && isset($event->end->dateTime)){ ?>

                <h2><?= $event->summary ?></h2>
                <?php
                $start = new DateTime($event->start->dateTime);
                $end = new DateTime($event->end->dateTime);
                ?>
                <h3><?php echo date_format($start, 'M d, Y g:i a') . ' - ' . date_format($end, 'M d, Y g:i a'); ?></h3>

            <?php } ?>
        <?php } ?>
        <hr />
    <?php } ?>
</div>