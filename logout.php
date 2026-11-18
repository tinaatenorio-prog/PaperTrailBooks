<?php

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the logout button on the login page to sign out.');
}

authVerifyCsrf();

unset(
    $_SESSION['auth_user'],
    $_SESSION['auth_registration_complete']
);

if (!session_regenerate_id(true)) {
    http_response_code(500);
    exit('The session could not be renewed. Close your browser and try again.');
}

$_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));

header('Location: login.php', true, 303);
exit;