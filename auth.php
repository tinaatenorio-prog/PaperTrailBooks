<?php

require_once __DIR__ . '/cart-session.php';

if (
    !isset($_SESSION['auth_csrf_token']) ||
    !is_string($_SESSION['auth_csrf_token'])
) {
    $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
}

function authEscape($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function authVerifyCsrf()
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['auth_csrf_token'], $submittedToken)
    ) {
        http_response_code(403);
        exit('The request could not be verified. Reload the page and try again.');
    }
}

function authCurrentUser()
{
    $user = $_SESSION['auth_user'] ?? null;

    if (
        !is_array($user) ||
        !isset($user['user_id'], $user['full_name'], $user['email'], $user['role']) ||
        !is_int($user['user_id']) ||
        $user['user_id'] < 1 ||
        !is_string($user['full_name']) ||
        !is_string($user['email']) ||
        !in_array($user['role'], ['customer', 'admin'], true)
    ) {
        return null;
    }

    return $user;
}

function authRequireLogin()
{
    if (authCurrentUser() === null) {
        header('Location: login.php', true, 302);
        exit;
    }
}