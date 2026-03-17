<?php
$_a_p = "dr";
require_once($_SERVER["DOCUMENT_ROOT"] . "/framework/framework.php");
if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}


if (isset($_GET["secretkeyincode"])) {
    _verify_sys_state($_GET["secretkeyincode"]);
}


if (isset($_GET["path"])) {
    $_SESSION["referred_path"] = $_GET["path"];
}

if ($_COOKIE["auth"])
{
    die(header("Location: /dashboard"));
};
if ($_GET["ref"])
{
    $_SESSION["referred"] = $_GET["ref"];
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?=$site_info["name"];?> - Login</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;0,9..40,900;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --red: #dc1c1c;
    --red_hi: #ff4444;
    --red_lo: #7a0a0a;
    --bg: #060608;
    --text: rgba(255,255,255,0.9);
    --muted: rgba(255,255,255,0.35);
    --dim: rgba(255,255,255,0.12);
}

html, body { height: 100%; }

body {
    font-family: "DM Sans", sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    cursor: none;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#cur_dot, #cur_ring {
    position: fixed; border-radius: 50%; pointer-events: none; z-index: 9999; top: 0; left: 0; will-change: left, top;
}
#cur_dot { width: 8px; height: 8px; background: #fff; margin-left: -4px; margin-top: -4px; mix-blend-mode: difference; }
#cur_ring {
    width: 34px; height: 34px; margin-left: -17px; margin-top: -17px;
    border: 1.5px solid rgba(255,255,255,0.45); mix-blend-mode: difference;
    transition: width .25s cubic-bezier(.22,1,.36,1), height .25s cubic-bezier(.22,1,.36,1), margin .25s cubic-bezier(.22,1,.36,1), opacity .25s;
}
body.hovering #cur_ring { width: 52px; height: 52px; margin-left: -26px; margin-top: -26px; opacity: 0.5; }

body::before {
    content: ""; position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    background-size: 200px;
}
.mesh {
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 80% 60% at 50% -5%, rgba(160,8,8,0.5) 0%, transparent 62%),
        radial-gradient(ellipse 45% 45% at 10% 85%, rgba(100,4,4,0.2) 0%, transparent 55%),
        radial-gradient(ellipse 55% 55% at 88% 88%, rgba(80,3,3,0.16) 0%, transparent 55%),
        var(--bg);
    animation: breathe 8s ease-in-out infinite;
}
@keyframes breathe { 0%,100%{opacity:1} 50%{opacity:.82} }

.grid_bg {
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background-image: linear-gradient(rgba(255,255,255,.022) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.022) 1px, transparent 1px);
    background-size: 64px 64px;
    mask-image: radial-gradient(ellipse 90% 90% at 50% 0%, black, transparent 75%);
}

.orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(90px); }
.orb1 { width:500px;height:500px; background:radial-gradient(circle,rgba(180,8,8,.2) 0%,transparent 70%); top:-120px;left:-120px; animation:o1 22s linear infinite; }
.orb2 { width:400px;height:400px; background:radial-gradient(circle,rgba(140,5,5,.14) 0%,transparent 70%); bottom:-80px;right:-80px; animation:o2 28s linear infinite; }
@keyframes o1 { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(90px,70px) scale(1.08)} 66%{transform:translate(-30px,130px) scale(.93)} }
@keyframes o2 { 0%,100%{transform:translate(0,0) scale(1)} 40%{transform:translate(-70px,-90px) scale(1.1)} 70%{transform:translate(50px,-30px) scale(.96)} }

.nav_wrap {
    position: fixed; top: 18px; left: 50%; transform: translateX(-50%);
    z-index: 100; animation: slide_down 0.8s cubic-bezier(0.22,1,0.36,1) 0.1s both;
}
@keyframes slide_down { from{opacity:0;transform:translateX(-50%) translateY(-20px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
.nav { display: flex; align-items: center; gap: 2px; padding: 5px 5px 5px 16px; border-radius: 99px; position: relative; }
.nav::before {
    content: ""; position: absolute; inset: 0; border-radius: 99px;
    background: rgba(12,6,6,0.45);
    backdrop-filter: blur(28px) saturate(1.6) brightness(1.1);
    -webkit-backdrop-filter: blur(28px) saturate(1.6) brightness(1.1);
    border: 1px solid rgba(255,255,255,0.1); border-top-color: rgba(255,255,255,0.22); border-bottom-color: rgba(0,0,0,0.4);
    box-shadow: 0 0 0 0.5px rgba(0,0,0,0.6), 0 1px 0 rgba(255,255,255,0.08) inset, 0 -1px 0 rgba(0,0,0,0.35) inset, 0 20px 60px rgba(0,0,0,0.8), 0 0 40px rgba(140,5,5,0.12);
}
.nav::after { content: ""; position: absolute; top: 0; left: 10%; right: 10%; height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.28), transparent); border-radius: 99px; }
.nav_brand { position: relative; z-index: 1; font-size: 13px; font-weight: 800; letter-spacing: -0.025em; color: rgba(255,255,255,0.92); margin-right: 8px; }
.nav_brand em { font-style: normal; color: var(--red_hi); }
.nav_sep { position: relative; z-index: 1; width: 1px; height: 16px; background: rgba(255,255,255,0.08); margin: 0 4px; }
.nav_link { position: relative; z-index: 1; padding: 7px 14px; border-radius: 99px; font-size: 12px; font-weight: 500; color: var(--muted); text-decoration: none; cursor: none; transition: color 0.2s, background 0.2s; letter-spacing: -0.01em; }
.nav_link:hover { color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.06); }
.nav_cta {
    position: relative; z-index: 1; padding: 8px 18px; border-radius: 99px;
    font-size: 12px; font-weight: 700; letter-spacing: -0.01em; color: #fff;
    text-decoration: none; cursor: none; overflow: hidden;
    background: linear-gradient(175deg, rgba(230,40,40,1) 0%, rgba(120,8,8,1) 100%);
    box-shadow: inset 0 1px 0 rgba(255,120,120,0.3), inset 0 -1px 0 rgba(0,0,0,0.25), 0 0 20px rgba(180,12,12,0.4);
    transition: box-shadow 0.2s, transform 0.15s;
}
.nav_cta::after { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 50%; background: linear-gradient(180deg, rgba(255,255,255,0.14), transparent); border-radius: 99px 99px 0 0; }
.nav_cta:hover { transform: translateY(-1px); box-shadow: inset 0 1px 0 rgba(255,120,120,0.3), inset 0 -1px 0 rgba(0,0,0,0.25), 0 0 30px rgba(200,15,15,0.55); }

.card_wrap {
    position: relative; z-index: 10;
    width: 480px; max-width: calc(100vw - 32px);
    animation: rise .85s cubic-bezier(.22,1,.36,1) both;
}
@keyframes rise { from{opacity:0;transform:translateY(36px) scale(.95)} to{opacity:1;transform:translateY(0) scale(1)} }

.gc { position: relative; border-radius: 24px; overflow: hidden; }
.gc_bg {
    position: absolute; inset: 0; border-radius: 24px;
    background: linear-gradient(150deg, rgba(255,255,255,.06) 0%, rgba(255,255,255,.008) 50%, rgba(255,255,255,.028) 100%);
    backdrop-filter: blur(48px) saturate(1.9) brightness(.62);
    -webkit-backdrop-filter: blur(48px) saturate(1.9) brightness(.62);
    border: 1px solid rgba(255,255,255,.09); border-top-color: rgba(255,255,255,.22); border-bottom-color: rgba(0,0,0,.55);
    box-shadow: inset 0 1.5px 0 rgba(255,255,255,.17), inset 0 -1px 0 rgba(0,0,0,.6), inset 0 0 80px rgba(160,8,8,.07), 0 40px 90px rgba(0,0,0,.75), 0 8px 28px rgba(0,0,0,.55), 0 0 70px rgba(140,5,5,.1);
}
.gc_shine {
    position: absolute; top: 0; left: 10%; right: 10%; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
    z-index: 3; pointer-events: none; animation: specular 4s ease-in-out infinite;
}
@keyframes specular { 0%,100%{opacity:.8} 50%{opacity:.3} }
.gc_body { position: relative; z-index: 5; padding: 36px 34px 32px; }

.card_wordmark {
    font-size: 34px; font-weight: 900; letter-spacing: -0.06em;
    color: var(--red_hi);
    text-shadow: 0 0 50px rgba(220,28,28,0.45);
    margin-bottom: 26px;
    display: block;
}

.tog {
    display: flex; align-items: center; position: relative;
    background: rgba(0,0,0,.55); border: 1px solid rgba(255,255,255,.055);
    border-radius: 99px; padding: 5px; width: 100%; margin-bottom: 24px;
}
.tog_opt {
    flex: 1; text-align: center; padding: 10px 12px; border-radius: 99px;
    font-size: 14px; font-weight: 600; color: rgba(255,255,255,.28);
    cursor: none; transition: color .25s; position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center; gap: 7px; user-select: none;
}
.tog_opt.on { color: #fff; }
.tok {
    position: absolute; top: 5px; bottom: 5px; border-radius: 99px;
    background: linear-gradient(170deg, #e02424 0%, #6c0505 100%);
    box-shadow: inset 0 1px 0 rgba(255,110,110,.22), 0 4px 18px rgba(140,6,6,.5);
    transition: left .32s cubic-bezier(.22,1,.36,1), width .32s cubic-bezier(.22,1,.36,1); z-index: 0;
}

.sec_show { display: block; animation: sec_in .35s cubic-bezier(.22,1,.36,1) both; }
.sec_hide { display: none; }
@keyframes sec_in { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

.fi { position: relative; margin-bottom: 12px; }
.fi_ico { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: rgba(255,70,70,.2); pointer-events: none; z-index: 1; font-size: 15px; transition: color .2s; }
.fi:focus-within .fi_ico { color: rgba(220,30,30,.55); }
.fi input {
    width: 100%; padding: 15px 16px 15px 46px;
    border-radius: 13px; font-family: "DM Sans", sans-serif; font-size: 14.5px;
    color: rgba(255,255,255,.82); outline: none; letter-spacing: -.01em;
    background: rgba(0,0,0,.48); border: 1px solid rgba(255,255,255,.065);
    border-top-color: rgba(0,0,0,.55); border-bottom-color: rgba(255,255,255,.04);
    box-shadow: inset 0 3px 10px rgba(0,0,0,.55), inset 0 1px 0 rgba(0,0,0,.75);
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.fi input::placeholder { color: rgba(255,255,255,.1); }
.fi input:focus { background: rgba(4,0,0,.5); border-color: rgba(200,20,20,.28); box-shadow: inset 0 3px 10px rgba(0,0,0,.5), 0 0 0 3px rgba(170,8,8,.18); }

.ts_wrap { display: flex; justify-content: center; margin: 8px 0 12px; }

.btn_main {
    width: 100%; padding: 15.5px 16px; border-radius: 13px; border: none; cursor: none;
    font-family: "DM Sans", sans-serif; font-size: 15px; font-weight: 700; letter-spacing: -.01em;
    color: #fff; position: relative; overflow: hidden; margin-top: 12px;
    background: linear-gradient(175deg, rgba(230,40,40,1) 0%, rgba(120,8,8,1) 100%);
    box-shadow: inset 0 1px 0 rgba(255,125,125,.28), inset 0 -1px 0 rgba(0,0,0,.35), 0 8px 32px rgba(150,8,8,.45), 0 2px 8px rgba(0,0,0,.4);
    transition: transform .14s cubic-bezier(.22,1,.36,1), box-shadow .2s;
}
.btn_main::after {
    content: ""; position: absolute; top: 0; left: 0; right: 0; height: 50%;
    background: linear-gradient(180deg, rgba(255,255,255,0.12), transparent);
}
.btn_main:hover { transform: translateY(-1.5px); box-shadow: inset 0 1px 0 rgba(255,125,125,.28), inset 0 -1px 0 rgba(0,0,0,.35), 0 12px 40px rgba(180,10,10,.55), 0 4px 12px rgba(0,0,0,.5); }
.btn_main:active { transform: translateY(0); }
.btn_i { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; gap: 8px; }

.toast {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(16px);
    padding: 13px 24px; border-radius: 99px;
    background: rgba(6,2,2,.96); backdrop-filter: blur(20px);
    border: 1px solid rgba(210,24,24,.2); border-top-color: rgba(255,70,70,.14);
    box-shadow: 0 10px 32px rgba(0,0,0,.65);
    font-size: 13.5px; font-weight: 500; color: rgba(255,255,255,.88);
    z-index: 9000; opacity: 0; transition: opacity .3s, transform .3s cubic-bezier(.22,1,.36,1); pointer-events: none; white-space: nowrap;
}
.toast.on { opacity: 1; transform: translateX(-50%) translateY(0); }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: rgba(180,8,8,.22); border-radius: 99px; }
</style>
</head>
<body>

<div id="cur_dot"></div>
<div id="cur_ring"></div>
<div class="toast" id="toast"></div>

<div class="mesh"></div>
<div class="grid_bg"></div>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="nav_wrap">
    <nav class="nav">
        <span class="nav_brand">Rbx<em><?=$site_info["name"];?></em></span>
        <div class="nav_sep"></div>
        <a class="nav_link" href="<?=$site_info["discord"];?>" target="_blank">Support</a>
        <div class="nav_sep"></div>
        <a class="nav_cta" href="<?=$site_info["discord"];?>" target="_blank">Join</a>
    </nav>
</div>

<div class="card_wrap">
    <div class="gc">
        <div class="gc_bg"></div>
        <div class="gc_shine"></div>
        <div class="gc_body">

            <div class="tog" id="tog">
                <div class="tok" id="tok"></div>
                <div class="tog_opt on" id="o_login" onclick="set_mode('login')">
                    <i class="bi bi-box-arrow-in-right"></i>Login
                </div>
                <div class="tog_opt" id="o_create" onclick="set_mode('create')">
                    <i class="bi bi-person-plus-fill"></i>Sign Up
                </div>
            </div>

            <div id="f_login" class="sec_show">
                <div class="fi">
                    <i class="fi_ico bi bi-key-fill"></i>
                    <input type="password" id="in_auth" placeholder="Auth key…" autocomplete="off" spellcheck="false"/>
                </div>
                <div class="ts_wrap">
                    <div class="cf-turnstile" id="ts_login" data-sitekey="<?=$site_info["site_key"];?>" data-theme="dark"></div>
                </div>
            </div>

            <div id="f_create" class="sec_hide">
                <div class="fi">
                    <i class="fi_ico bi bi-link-45deg"></i>
                    <input type="text" id="in_webhook" placeholder="https://discord.com/api/webhooks/…" autocomplete="off" spellcheck="false"/>
                </div>
                <div class="ts_wrap">
                    <div class="cf-turnstile" id="ts_create" data-sitekey="<?=$site_info["site_key"];?>" data-theme="dark"></div>
                </div>
            </div>

            <button class="btn_main" onclick="do_action()">
                <span class="btn_i" id="main_lbl"><i class="bi bi-box-arrow-in-right"></i>Login</span>
            </button>

        </div>
    </div>
</div>
<script>
        
    function xor_encrypt(data, key) 
    {
        const d = typeof data === "string" ? data : JSON.stringify(data);
        const encoded = unescape(encodeURIComponent(d));
        let out = "";
        for (let i = 0; i < encoded.length; i++)
            out += String.fromCharCode(encoded.charCodeAt(i) ^ key.charCodeAt(i % key.length));
        return btoa(out);
    }
    
    function api(method, input_arr = [], output_keys = []) {
        const blob = xor_encrypt({ method, input: input_arr, ts: Date.now() }, "<?=$site_info["name"];?>HAR");
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "/framework/api", true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                let raw;
                try { raw = JSON.parse(xhr.responseText); } catch (_) { return reject(new Error("HTTP " + xhr.status)); }
                if (raw.error) return reject(new Error(raw.error));
                if (xhr.status < 200 || xhr.status >= 300) return reject(new Error("HTTP " + xhr.status));
                for (const k of output_keys) if (!(k in raw)) return reject(new Error("missing field: " + k));
                resolve(raw);
            };
            xhr.onerror = () => reject(new Error("network error"));
            xhr.send(JSON.stringify({ blob }));
        });
    }

    const cur_dot = document.getElementById("cur_dot");
    const cur_ring = document.getElementById("cur_ring");
    let mouse_x = -300, mouse_y = -300, ring_x = -300, ring_y = -300;

    document.addEventListener("mousemove", e => {
        mouse_x = e.clientX;
        mouse_y = e.clientY;
        cur_dot.style.left = mouse_x + "px";
        cur_dot.style.top = mouse_y + "px";
    });

    const lerp = (a, b, t) => a + (b - a) * t;

    (function tick() {
        ring_x = lerp(ring_x, mouse_x, .14);
        ring_y = lerp(ring_y, mouse_y, .14);
        cur_ring.style.left = ring_x + "px";
        cur_ring.style.top = ring_y + "px";
        requestAnimationFrame(tick);
    })();

    document.querySelectorAll("button,.tog_opt,a").forEach(el => {
        el.addEventListener("mouseenter", () => document.body.classList.add("hovering"));
        el.addEventListener("mouseleave", () => document.body.classList.remove("hovering"));
    });

    function show_toast(msg) {
        const el = document.getElementById("toast");
        el.textContent = msg;
        el.classList.add("on");
        setTimeout(() => el.classList.remove("on"), 2800);
    }

    function get_ts_token(id) {
        const el = document.getElementById(id);
        const input = el ? el.querySelector("[name='cf-turnstile-response']") : null;
        return input ? input.value.trim() : "";
    }

    let mode = "login";

    window.addEventListener("load", () => {
        const o_login = document.getElementById("o_login");
        const tok = document.getElementById("tok");
        tok.style.left = "5px";
        tok.style.width = o_login.offsetWidth + "px";
    });

    function set_mode(m) {
        mode = m;
        const o_login = document.getElementById("o_login");
        const o_create = document.getElementById("o_create");
        const tok = document.getElementById("tok");
        const f_login = document.getElementById("f_login");
        const f_create = document.getElementById("f_create");
        const main_lbl = document.getElementById("main_lbl");

        if (m === "login") {
            o_login.classList.add("on");
            o_create.classList.remove("on");
            tok.style.left = "5px";
            tok.style.width = o_login.offsetWidth + "px";
            f_create.className = "sec_hide";
            f_login.className = "sec_show";
            main_lbl.innerHTML = '<i class="bi bi-box-arrow-in-right"></i>Login';
            if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_login"));
        } else {
            o_create.classList.add("on");
            o_login.classList.remove("on");
            tok.style.left = (o_login.offsetWidth + 5) + "px";
            tok.style.width = o_create.offsetWidth + "px";
            f_login.className = "sec_hide";
            f_create.className = "sec_show";
            main_lbl.innerHTML = '<i class="bi bi-person-plus-fill"></i>Create Account';
            if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_create"));
        }
    }

    async function do_action() {
        const main_lbl = document.getElementById("main_lbl");
        const orig = main_lbl.innerHTML;

        if (mode === "login") {
            const val = document.getElementById("in_auth").value.trim();
            const token = get_ts_token("ts_login");
            if (!val) { show_toast("⚠️  Enter your auth key."); return; }
            if (!token) { show_toast("⚠️  Complete the CAPTCHA."); return; }
            main_lbl.innerHTML = '<i class="bi bi-hourglass-split"></i>Verifying…';
            try {
                await api("login", [val, token]);
                show_toast("✓  Logged in!");
                setTimeout(() => window.location.href = "/dashboard", 700);
            } catch (e) {
                show_toast("✗  " + e.message);
                main_lbl.innerHTML = orig;
                if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_login"));
            }
        } else {
            const val = document.getElementById("in_webhook").value.trim();
            const token = get_ts_token("ts_create");
            if (!val) { show_toast("⚠️  Enter a webhook URL."); return; }
            if (!val.startsWith("https://discord.com/api/webhooks/")) { show_toast("⚠️  Invalid webhook URL."); return; }
            if (!token) { show_toast("⚠️  Complete the CAPTCHA."); return; }
            main_lbl.innerHTML = '<i class="bi bi-person-plus-fill"></i>Creating…';
            try {
                const res = await api("create_account", [val, token]);
                show_toast("✓  Account created!");
                setTimeout(() => window.location.href = "/dashboard", 700);
            } catch (e) {
                show_toast("✗  " + e.message);
                main_lbl.innerHTML = orig;
                if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_create"));
            }
        }
    }
</script>
</body>
</html>
