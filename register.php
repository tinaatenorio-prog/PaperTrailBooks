<?php

require_once __DIR__ . '/auth.php';

$errors = [];
$fullName = '';
$email = '';
$registrationComplete = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    authVerifyCsrf();

    $fullName = isset($_POST['full_name']) && is_string($_POST['full_name'])
        ? trim($_POST['full_name'])
        : '';

    $email = isset($_POST['email']) && is_string($_POST['email'])
        ? strtolower(trim($_POST['email']))
        : '';

    $password = isset($_POST['password']) && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    $confirmPassword = isset($_POST['confirm_password'])
        && is_string($_POST['confirm_password'])
        ? $_POST['confirm_password']
        : '';

    if ($fullName === '' || strlen($fullName) > 150) {
        $errors[] = 'Enter a full name of no more than 150 bytes.';
    }

    if (
        $email === ''
        || strlen($email) > 254
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false
    ) {
        $errors[] = 'Enter a valid email address of no more than 254 bytes.';
    }

    if (
        strlen($password) < 15
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $errors[] = 'Use a password between 15 and 72 bytes, without null characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'The passwords do not match.';
    }

    if ($errors === []) {
        try {
            require_once __DIR__ . '/db.php';

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            if ($passwordHash === false) {
                throw new RuntimeException('Password hashing failed.');
            }

            $statement = $pdo->prepare(
                "INSERT INTO users (full_name, email, password_hash, role)
                 VALUES (:full_name, :email, :password_hash, 'customer')"
            );

            if ($statement === false) {
                throw new RuntimeException('Registration preparation failed.');
            }

            $saved = $statement->execute([
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            if (!$saved) {
                throw new RuntimeException('Registration insert failed.');
            }

            $_SESSION['auth_registration_complete'] = true;

            header('Location: register.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            if (
                isset($exception->errorInfo[1])
                && (int) $exception->errorInfo[1] === 1062
            ) {
                $errors[] = 'That email address is already registered.';
                        } else {
                $errors[] = 'Registration is temporarily unavailable. Please try again later.';
                error_log('Paper Trail Books registration database failure.');
            }
        } catch (Throwable $exception) {
            $errors[] = 'Registration is temporarily unavailable. Please try again later.';
            error_log('Paper Trail Books registration processing failure.');
        }
    }

    unset($password, $confirmPassword);
} elseif (!empty($_SESSION['auth_registration_complete'])) {
    $registrationComplete = true;
    unset($_SESSION['auth_registration_complete']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Paper Trail Books</title>
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
    <h2>Create an Account</h2>

    <?php if ($registrationComplete): ?>
        <p role="status">
            Your demonstration account was created successfully.
            You are not signed in.
        </p>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div role="alert">
            <p>Please correct the following:</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= authEscape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= authEscape($_SESSION['auth_csrf_token']) ?>"
        >

        <label for="full_name">Full Name:</label><br>
        <input
            type="text"
            id="full_name"
            name="full_name"
            placeholder="Enter your fictional name"
            autocomplete="name"
            maxlength="150"
            value="<?= authEscape($fullName) ?>"
            required
        >
        <br><br>

        <label for="email">Email:</label><br>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter a fictional email"
            autocomplete="email"
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
            placeholder="Create a test-only password"
            autocomplete="new-password"
            minlength="15"
            maxlength="72"
            aria-describedby="password-help"
            required
        >
        <p id="password-help">
            Use 15–72 ASCII characters for this local demonstration.
            Do not reuse a real password.
        </p>

        <label for="confirm_password">Confirm Password:</label><br>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Repeat your test-only password"
            autocomplete="new-password"
            minlength="15"
            maxlength="72"
            required
        >
        <br><br>

        <button type="submit">Register</button>
    </form>

    <p>
        Local assessment demonstration. Use fictional details only.
        Submitted accounts are stored in the local database.
    </p>
</main>

</body>
</html>