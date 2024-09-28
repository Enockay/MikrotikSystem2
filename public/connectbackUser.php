<?php
require './header.php';

$mac = $_SESSION["mac"];
$ip = $_SESSION["ip"];
$link_login = $_SESSION["link-login"];
$link_login_only = $_SESSION["link-login-only"];
$linkorig = "https://www.google.com";

// Get the remaining time from the form submission
$remainingTime = isset($_POST['remainingTime']) ? intval($_POST['remainingTime']) : 0;
$phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : "";

// Determine the user profile based on the remaining time range
// Determine the user profile based on the remaining time range
if ($remainingTime >= 0 && $remainingTime < 300) {
    $username = "user_5min";
} elseif ($remainingTime >= 300 && $remainingTime < 600) {
    $username = "user_10min";
} elseif ($remainingTime >= 600 && $remainingTime < 900) {
    $username = "user_15min";
} elseif ($remainingTime >= 900 && $remainingTime < 1200) {
    $username = "user_20min";
} elseif ($remainingTime >= 1200 && $remainingTime < 1500) {
    $username = "user_25min";
} elseif ($remainingTime >= 1500 && $remainingTime < 1800) {
    $username = "user_30min";
} elseif ($remainingTime >= 1800 && $remainingTime < 2100) {
    $username = "user_35min";
} elseif ($remainingTime >= 2100 && $remainingTime < 2400) {
    $username = "user_40min";
} elseif ($remainingTime >= 2400 && $remainingTime < 2700) {
    $username = "user_45min";
} elseif ($remainingTime >= 2700 && $remainingTime < 3000) {
    $username = "user_50min";
} elseif ($remainingTime >= 3000 && $remainingTime < 3300) {
    $username = "user_55min";
} elseif ($remainingTime >= 3300 && $remainingTime < 3600) {
    $username = "user_60min";
} elseif ($remainingTime >= 3600 && $remainingTime < 7200) {
    $username = "user_2hr";
} elseif ($remainingTime >= 7200 && $remainingTime < 10800) {
    $username = "user_3hr";
} elseif ($remainingTime >= 10800 && $remainingTime < 14400) {
    $username = "user3";
} elseif ($remainingTime >= 14400 && $remainingTime < 18000) {
    $username = "user_5hr";
} elseif ($remainingTime >= 18000 && $remainingTime < 21600) {
    $username = "user4";
} elseif ($remainingTime >= 21600 && $remainingTime < 25200) {
    $username = "user_7hr";
} elseif ($remainingTime >= 25200 && $remainingTime < 28800) {
    $username = "user_8hr";
} elseif ($remainingTime >= 28800 && $remainingTime < 32400) {
    $username = "user_9hr";
} elseif ($remainingTime >= 32400 && $remainingTime < 36000) {
    $username = "user_10hr";
} elseif ($remainingTime >= 36000 && $remainingTime < 39600) {
    $username = "user_11hr";
} elseif ($remainingTime >= 39600 && $remainingTime < 43200) {
    $username = "user_12hr";
} elseif ($remainingTime >= 43200 && $remainingTime > 86400) {
    $username = "user_1day";
}else{
    $username = "user1";
}

$domain =  $phoneNumber;



// Rest of your code...
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>
      <?php echo htmlspecialchars($business_name); ?> WiFi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link rel="stylesheet" href="assets/styles/bulma.min.css"/>
    <link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.css"/>
    <link rel="icon" type="image/png" href="assets/images/favicomatic/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png" href="assets/images/favicomatic/favicon-16x16.png" sizes="16x16"/>
    <link rel="stylesheet" href="assets/styles/style.css"/>
</head>
<body>
<div class="page">

    <div class="head">
        <br>
        <figure id="logo">
            <img src="assets/images/logo.png">
        </figure>
    </div>
</div>

<script type="text/javascript">
    function doLogin() {
        document.sendin.username.value = document.login.username.value;
        document.sendin.password.value = hexMD5('\011\373\054\364\002\233\266\263\270\373\173\323\234\313\365\337\356');
        document.sendin.submit();
        return false;
    }
</script>
<script type="text/javascript">
    function formAutoSubmit () {
        var frm = document.getElementById("login");
        document.getElementById("login").submit();
        frm.submit();
    }
    // window.onload = formAutoSubmit;
    window.onload = setTimeout(formAutoSubmit, 2500);

</script>

<form id="login" method="post" action="<?php echo $link_login_only; ?>" onSubmit="return doLogin()">
    <input name="dst" type="hidden" value="<?php echo $linkorig; ?>" />
    <input name="popup" type="hidden" value="false" />
    <input name="username" type="hidden" value="<?php echo $username; ?>"/>
    <input name="domain" type="hidden" value=""/>
    <input name="password" type="hidden"/>
</form>

</body>
</html>
