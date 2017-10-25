<?php

include 'Broker.php';
include 'NotAttachedException.php';

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
$broker->attach(true);

try {
    if (!empty($_GET['logout'])) {
        $broker->logout();
    } elseif ($broker->getUserInfo() || ($_SERVER['REQUEST_METHOD'] == 'POST' && $broker->login($_POST['username'], $_POST['password']))) {
        header("Location: index.php", true, 303);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') $errmsg = "Login failed";
} catch (NotAttachedException $e) {
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
} catch (Exception $e) {
    $errmsg = $e->getMessage();
}

?>
<!doctype html>
<html>
    <head>
        <title><?= $broker->broker ?> | Login (Single Sign-On demo)</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

        <style>
            h1 {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?= $broker->broker ?> <small>(Single Sign-On demo)</small></h1>

            <?php if (isset($errmsg)): ?><div class="alert alert-danger"><?= $errmsg ?></div><?php endif; ?>

            <form class="form-horizontal" action="login.php" method="post">
                <div class="form-group">
                    <label for="inputUsername" class="col-sm-2 control-label">Username</label>
                    <div class="col-sm-10">
                        <input type="text" name="username" class="form-control" id="inputUsername">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPassword" class="col-sm-2 control-label">Password</label>
                    <div class="col-sm-10">
                        <input type="password" name="password" class="form-control" id="inputPassword">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-default">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
