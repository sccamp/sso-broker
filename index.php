<?php

include 'src/Broker.php';
include 'src/NotAttachedException.php';
include 'src/SsoException.php';

if (isset($_GET['sso_error'])) {
    header("Location: error.php?sso_error=" . $_GET['sso_error'], true, 307);
    exit;
}

if (php_sapi_name() == 'cli-server') {
    $ssoBrokers = [
        '9001' => [
            'SSO_SERVER' => 'http://localhost:9000',
            'SSO_BROKER_ID' => 'Betelgeuse',
            'SSO_BROKER_SECRET' => 'd6cfca165058a7d43f85d5bb5ffcbe45'
        ],
        '9002' => [
            'SSO_SERVER' => 'http://localhost:9000',
            'SSO_BROKER_ID' => 'Vega',
            'SSO_BROKER_SECRET' => '64d8c5e0e73635dc894032f156884f23'
        ]
    ];

    $port = $_SERVER['SERVER_PORT'];
    $server = $ssoBrokers[$port]['SSO_SERVER'];
    $broker = $ssoBrokers[$port]['SSO_BROKER_ID'];
    $secret = $ssoBrokers[$port]['SSO_BROKER_SECRET'];
    error_log(__FILE__ . ' | Set vars: ' . $server . ', ' . $broker . ', ' . $secret);
} else {
    $server = getenv('SSO_SERVER');
    $broker = getenv('SSO_BROKER_ID');
    $secret = getenv('SSO_BROKER_SECRET');
}

$broker = new Broker($server, $broker, $secret);
error_log('Created new Broker');
$broker->attach(true);
error_log('Attached sessions');

try {
    $user = $broker->getUserInfo();
} catch (NotAttachedException $e) {
    error_log('Not attached, redirecting to: ' . $_SERVER['REQUEST_URI']);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
} catch (SsoException $e) {
    error_log('SsoException, redirecting to: error.php');
    header("Location: error.php?sso_error=" . $e->getMessage(), true, 307);
}

if (!$user) {
    error_log('No user, redirecting to login');
    header("Location: login.php", true, 307);
    exit;
}
?>
<!doctype html>
<html>
    <head>
        <title><?= $broker->broker ?> (Single Sign-On demo)</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <h1><?= $broker->broker ?> <small>(Single Sign-On demo)</small></h1>
            <h3>Logged in</h3>

            <pre><?= json_encode($user, JSON_PRETTY_PRINT); ?></pre>

            <a id="logout" class="btn btn-default" href="login.php?logout=1">Logout</a>
        </div>
    </body>
</html>

