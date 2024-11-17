
<?php
$mac = $_SESSION["mac"] ?? "26:17:33:0A:33:E0";
$phoneNumber = $_SESSION['phoneNumber'] ?? "254796869402";
$TransactionCode = $_SESSION['TransactionCode'] ?? "SKH4423CPI"; // Ensure default for testing
$identity = $_SESSION['routername'] ?? "enockMikrotik";
$profile = $_SESSION['profile'] ?? "1week";
$link_login_only = "http://192.168.88.1/login";
$linkorig = 'blackienetworks.com';

 ?>
 <script type="text/javascript">
     function formAutoSubmit() {
         var frm = document.getElementById("login");
         frm.submit();
     }
     window.onload = setTimeout(formAutoSubmit, 2500);
 </script>
 <form id="login" method="post" action="<?php echo $link_login_only; ?>" onSubmit="return doLogin()">
     <input name="dst" type="hidden" value="<?php echo $linkorig; ?>" />
     <input name="popup" type="hidden" value="false" />
     <input name="username" type="hidden" value="<?php echo $phoneNumber; ?>" />
     <input name="domain" type="hidden" value="" />
     <input name="password" type="hidden" value="" />
 </form>
 <?php
?>