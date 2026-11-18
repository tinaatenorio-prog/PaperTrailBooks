<?php

require_once __DIR__ . '/auth.php';

$errors = [];
$email = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && authCurrentUser() === null
) {
    authVerifyCsrf();

    $email = isset($_POST['email']) && is_string($_POST['email'])
        ? strtolower(trim($_POST['email']))
        : '';

    $password = isset($_POST['password']) && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    if (
        strlen($email) > 254
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        || $password === ''
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $errors[] = 'Invalid email or password.';
    } else {
        try {
            require_once __DIR__ . '/db.php';

            $statement = $pdo->prepare(
                'SELECT user_id, full_name, email, password_hash, role
                 FROM users
                 WHERE email = :email
                 LIMIT 1'
            );

            $statement->execute(['email' => $email]);
            $user = $statement->fetch();

            if (
                $user !== false
                && password_verify($password, $user['password_hash'])
                && in_array($user['role'], ['customer', 'admin'], true)
            ) {
                if (!session_regenerate_id(true)) {
                    throw new RuntimeException('Session renewal failed.');
                }

                $newCsrfToken = bin2hex(random_bytes(32));

                $_SESSION['auth_user'] = [
                    'user_id' => (int) $user['user_id'],
                    'full_name' => (string) $user['full_name'],
                    'email' => (string) $user['email'],
                    'role' => (string) $user['role'],
                ];

                $_SESSION['auth_csrf_token'] = $newCsrfToken;

                header('Location: login.php', true, 303);
                exit;
            }

            $errors[] = 'Invalid email or password.';
        } catch (Throwable $exception) {
            $errors[] = 'Login is temporarily unavailable. Please try again later.';
            error_log('Paper Trail Books login processing failure.');
        }
    }

    unset($password);
}

$currentUser = authCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Paper Trail Books</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>Paper Trail Books</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="catalogue.php">Catalogue</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main>
    <h2>Customer Login</h2>

    <?php if ($currentUser !== null): ?>

        <p role="status">
            You are signed in as
            <?= authEscape($currentUser['full_name']) ?>.
        </p>

        <p>
            Account role:
            <?= authEscape($currentUser['role']) ?>
        </p>

        <p><a href="catalogue.php">Browse the catalogue</a></p>

                <form action="logout.php" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= authEscape($_SESSION['auth_csrf_token']) ?>"
            >
            <button type="submit">Logout</button>
        </form>

    <?php else: ?>

        <?php if ($errors !== []): ?>
            <div role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= authEscape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= authEscape($_SESSION['auth_csrf_token']) ?>"
            >

            <label for="email">Email:</label><br>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                autocomplete="username"
                maxlength="254"
                value="<?= authEscape($email) ?>"
                required
            >
            <br><br>

            <label for="password">Password:</label><br>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                maxlength="72"
                required
            >
            <br><br>

            <button type="submit">Login</button>
        </form>

    <?php endif; ?>

    <p>
        Local assessment demonstration.
        Use fictional accounts and test-only passwords.
    </p>
</main>

</body>
</html>