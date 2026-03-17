<?php
$_e_p = "ro";
setcookie("auth", "", [
    "expires"  => time() - 3600,
    "path"     => "/",
    "secure"   => true,
    "httponly" => true,
    "samesite" => "Strict",
]);
unset($_COOKIE["auth"]); 
die(header("Location: /login"));
?>