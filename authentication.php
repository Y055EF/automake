<?php
// Check if we received a code from TikTok
$authorizationCode = isset($_GET['code']) ? $_GET['code'] : null;

// These would be your actual credentials from your TikTok developer app
$client_key = "sbawoez1q1n6qknyar"; // Replace with your actual client key
$client_secret = "5tBHU0JXIOJeDEGUL6pk0gnJQ08cukRU"; // Replace with your actual client secret
$redirect_uri = "https://y055ef.github.io/automake/authentication.php"; // The same redirect URI you registered in TikTok developer portal

// If we have a code, exchange it for an access token
$accessToken = null;
$refreshToken = null;
$tokenResponse = null;

if ($authorizationCode) {
    // Prepare the request to exchange code for token
    $tokenUrl = "https://open.tiktokapis.com/v2/oauth/token/";
    $postData = [
        'client_key' => $client_key,
        'client_secret' => $client_secret,
        'code' => $authorizationCode,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri
    ];

    // Initialize cURL session
    $ch = curl_init($tokenUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Process the response
    if ($response) {
        $tokenResponse = json_decode($response, true);
        if (isset($tokenResponse['access_token'])) {
            $accessToken = $tokenResponse['access_token'];
            $refreshToken = $tokenResponse['refresh_token'] ?? null;
        }
    }
}

// Generate TikTok authorization URL
$authUrl = "https://www.tiktok.com/v2/auth/authorize/";
$authParams = [
    'client_key' => $client_key,
    'response_type' => 'code',
    'scope' => 'user.info.basic,user.info.profile,user.info.stats,video.publish,video.upload',
    'redirect_uri' => $redirect_uri,
    'state' => bin2hex(random_bytes(16)) // Generate a random state for security
];
$fullAuthUrl = $authUrl . '?' . http_build_query($authParams);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .tiktok-login-button {
            display: inline-block;
            background-color: #000000;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .token-display {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
            word-break: break-all;
        }
        pre {
            white-space: pre-wrap;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>TikTok Access Token Generator</h1>
    
    <?php if (!$authorizationCode): ?>
        <p>Click the button below to log in with TikTok and get your access token:</p>
        <a href="<?php echo htmlspecialchars($fullAuthUrl); ?>" class="tiktok-login-button">Continue with TikTok</a>
    <?php else: ?>
        <?php if ($accessToken): ?>
            <div class="token-display">
                <h2>Your Access Token:</h2>
                <pre><?php echo htmlspecialchars($accessToken); ?></pre>
                
                <?php if ($refreshToken): ?>
                    <h2>Your Refresh Token:</h2>
                    <pre><?php echo htmlspecialchars($refreshToken); ?></pre>
                <?php endif; ?>
                
                <h2>Full Response:</h2>
                <pre><?php echo htmlspecialchars(print_r($tokenResponse, true)); ?></pre>
            </div>
            
            <p><strong>Important:</strong> Save these tokens securely! The access token will expire in 24 hours, but you can use the refresh token to get a new access token.</p>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="tiktok-login-button">Start Over</a>
        <?php else: ?>
            <p>Error retrieving access token. Please try again.</p>
            <a href="<?php echo htmlspecialchars($fullAuthUrl); ?>" class="tiktok-login-button">Try Again</a>
        <?php endif; ?>
    <?php endif; ?>
    
    <div>
        <h2>Instructions:</h2>
        <ol>
            <li>Replace <code>YOUR_CLIENT_KEY</code>, <code>YOUR_CLIENT_SECRET</code>, and <code>YOUR_REDIRECT_URI</code> at the top of this file with your actual TikTok developer credentials</li>
            <li>Upload this file to your web server (must be accessible via HTTPS)</li>
            <li>Make sure this file's URL matches exactly with the redirect URI you registered in your TikTok developer app</li>
            <li>Click the "Continue with TikTok" button to authenticate and get your access token</li>
        </ol>
    </div>
</body>
</html>
