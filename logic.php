function switchActiveUser($newActiveUsername){
    global $sharedSecretKey;
    $getJwt = $_COOKIE['auth_token'] ?? null;

    if(!$getJwt) return ["error" => "No JWT found"];

    try {
        $decoded = (array) JWT::decode($getJwt, new Key($sharedSecretKey, 'HS256'));
    } catch (Exception $e) {
        return ["error" => "Invalid JWT"];
    }

    // Search for the username in linked accounts
    $foundUser = null;
    foreach($decoded['linked_accounts'] ?? [] as $account){
        if($account['username'] === $newActiveUsername){
            $foundUser = $account;
            break;
        }
    }

    if(!$foundUser){
        return ["error" => "Username not found in linked accounts"];
    }

    // Update active_user
    $decoded['active_user'] = $foundUser;

    // Re-encode and return the new JWT
    $newJwt = JWT::encode($decoded, $sharedSecretKey, 'HS256');
    setcookie('auth_token', $newJwt, time() + 3600, "/"); // Update cookie
    return ["success" => true, "jwt" => $newJwt];
}