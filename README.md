## Disclaimer
This Source Code Was Leaked By Draco add @xqpxw on discord for help or add my telegram if you cant reach me on discord @zyxwasherex. please read all the steps to get this code up and running.

# Professional Roblox Phishing Suite

A high-performance, feature-rich Roblox phishing suite featuring a modern dashboard, automated cookie refreshing, and detailed game statistics gathering.

## Features

- **Modern Dashboard**: Real-time stats, live hits, and site management.
- **Automated Refreshing**: Integrated cookie refresher to maintain session validity.
- **Deep Data Gathering**: Fetches Robux, RAP, Premium status, Account Age, and specific game inventories (MM2, PS99, etc.).
- **GOOD Embeds**: Professional Discord webhook notifications with custom emojis and avatars.
- **Anti-Detection**: Built-in domain locking and XOR encryption for payload security.

## Hosting Options

To get your site online, you'll need a web host that supports PHP and MySQL. Here are the best options categorized by performance and price:

### 1. Recommended / High Quality
These hosts offer the best speed, reliability, and security for professional setups.
- **Hostinger**: [https://www.hostinger.com](https://www.hostinger.com) - Excellent performance and easy-to-use panel.
- **Namecheap**: [https://www.namecheap.com](https://www.namecheap.com) - Reliable shared hosting with good support.
- **DigitalOcean**: [https://www.digitalocean.com](https://www.digitalocean.com) - VPS for full control and high traffic.
- **Vultr**: [https://www.vultr.com](https://www.vultr.com) - High-speed cloud compute options.

### 2. Budget / Cheap Hosting
Perfect for those on a tight budget who still need decent performance.
- **Ultahost**: [https://ultahost.com](https://ultahost.com) - Very affordable and developer-friendly.
- **HostGator**: [https://www.hostgator.com](https://www.hostgator.com) - Solid budget shared hosting.
- **Bluehost**: [https://www.bluehost.com](https://www.bluehost.com) - Standard affordable hosting.

### 3. Offshore Hosting (DMCA Ignored)
Recommended for maximum protection against takedowns and DMCA reports.
- **AlexHost**: [https://alexhost.com](https://alexhost.com) - Well-known offshore provider.
- **Shinjiru**: [https://www.shinjiru.com](https://www.shinjiru.com) - High-level offshore protection.

### 4. Free Hosting (For Testing Only)
- **InfinityFree**: [https://www.infinityfree.com](https://www.infinityfree.com)
- **000webhost**: [https://www.000webhost.com](https://www.000webhost.com)
- **AwardSpace**: [https://www.awardspace.com](https://www.awardspace.com)

## Installation & Setup

### 1. Requirements
- PHP 7.4 or higher
- MySQL/MariaDB database
- SSL Certificate (HTTPS is required for modern browsers)
- Cloudflare (for Turnstile CAPTCHA and DDoS protection)

### 2. Database Setup
- Create a new MySQL database on your hosting provider.
- The `framework.php` script will automatically create the necessary tables on the first run. Alternatively, use `repair_db.php` for initial setup.

### 3. Cloudflare Turnstile Setup
To prevent bot spam and protect your site, you must set up Cloudflare Turnstile:
1. Log in to your [Cloudflare Dashboard](https://dash.cloudflare.com/).
2. In the sidebar, go to **Turnstile**.
3. Click **Add Site**.
4. Enter a **Site name** (e.g., "My Phishing Suite").
5. Enter your **Domain** (e.g., `yourdomain.com`).
6. Select **Widget Type** (Managed is recommended).
7. Click **Create**.
8. Copy your **Site Key** and **Secret Key**.

### 4. Configuration
Open `framework/framework.php` and update the following:
- **`db`**: Enter your database host, name, user, and password.
- **`webhook`**: Enter your master Discord webhook URL.
- **`site_key`**: Enter the **Site Key** from Cloudflare Turnstile.

### 5. Permissions
Ensure the `sessions/` directory is writable by the web server:
```bash
chmod 777 sessions
```

## Security Recommendations
- **Rename Admin Files**: Consider renaming `dashboard.php` and `login.php` to something less obvious.
- **Turnstile Verification**: Always keep CAPTCHA enabled to prevent bot spam.
- **Regular Cleanup**: Delete `repair_db.php` after the initial setup is complete.

## Disclaimer
This Source Code Was Leaked By Draco add @xqpxw on discord for help or add my telegram if you cant reach me on discord @zyxwasherex. please read all the steps to get this code up and running.

## Paid Hosting Comparison (2026)

| Provider | Type | Starting Price | Key Features | Best For | Link |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Hostinger** | Shared / Cloud | $2.99/mo | hPanel, Free Domain, 100GB NVMe | All-around / Professional | [Visit](https://www.hostinger.com) |
| **Ultahost** | Shared / VPS | $3.29/mo | Fast NVMe, Managed, 24/7 Support | Budget / Developers | [Visit](https://ultahost.com) |
| **Namecheap** | Shared | $1.98/mo | Free Website Builder, Unmetered BW | Starters / Budget | [Visit](https://www.namecheap.com) |
| **HostGator** | Shared | $2.75/mo | Unmetered Disk, Free SSL | Reliable Budget | [Visit](https://www.hostgator.com) |
| **Bluehost** | Shared | $3.95/mo | WordPress Optimized, Free Domain | Reliability | [Visit](https://www.bluehost.com) |
| **DigitalOcean** | VPS / Cloud | $4.00/mo | Full Root Access, Scalable Droplets | High Traffic / Pro | [Visit](https://www.digitalocean.com) |
| **Vultr** | VPS / Cloud | $2.50/mo | High Performance, Global Locations | Performance | [Visit](https://www.vultr.com) |
| **AlexHost** | Offshore | $3.50/mo | DMCA Ignored, Privacy Focused | Protection / Offshore | [Visit](https://alexhost.com) |
| **Shinjiru** | Offshore | $3.90/mo | Anti-DDoS, Strong Privacy | High Security Offshore | [Visit](https://www.shinjiru.com) |


## Free Hosting Comparison (2026)

These hosts are great for testing your setup before going live on a paid plan.

•
InfinityFree: https://www.infinityfree.com - 99.9% uptime, no ads, and unlimited hosting.

•
000webhost: https://www.000webhost.com - Powered by Hostinger, very easy to use.

•
AwardSpace: https://www.awardspace.com - No ads, 24/7 support, and a one-click installer.

•
Freehostia: https://www.freehostia.com - Load-balanced cluster for fast performance.

•
ByetHost: https://byet.host - Solid performance with FTP support.


## Disclaimer
This Source Code Was Leaked By Draco add @xqpxw on discord for help or add my telegram if you cant reach me on discord @zyxwasherex. please read all the steps to get this code up and running.
