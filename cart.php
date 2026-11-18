<?php

ini_set('display_errors', '0');

require __DIR__ . '/cart-session.php';

$loadError = false;
$booksById = [];
$cartItems = [];
$totalCents = 0;
$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

function cartPriceCents($price)
{
    $parts = explode('.', (string) $price, 2);
    $fraction = str_pad($parts[1] ?? '', 2, '0');

    return ((int) $parts[0] * 100) + (int) substr($fraction, 0, 2);
}

function cartMoney($cents)
{
    return number_format($cents / 100, 2);
}

try {
    require __DIR__ . '/db.php';

    // Suitable for the small demonstration catalogue.
    $statement = $pdo->query(
        'SELECT book_id, title, price, stock_quantity
         FROM books
         ORDER BY title'
    );

    foreach ($statement->fetchAll() as $book) {
        $booksById[(int) $book['book_id']] = $book;
    }
} catch (Throwable $error) {
    http_response_code(500);
    $loadError = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cartVerifyCsrf();

    if (!$loadError) {
        $action = $_POST['action'] ?? '';
        $bookIdInput = $_POST['book_id'] ?? '';

        $bookId = is_string($bookIdInput)
            ? filter_var(
                $bookIdInput,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            )
            : false;

        if (
            !is_string($action) ||
            !in_array($action, ['add', 'update', 'remove'], true) ||
            $bookId === false
        ) {
            http_response_code(400);
            exit('Invalid cart request.');
        }

        if ($action === 'remove') {
            unset($_SESSION['cart'][$bookId]);
            $message = 'The book was removed from your cart.';
        } elseif (!isset($booksById[$bookId])) {
            $message = 'That book is no longer available.';
        } else {
            $book = $booksById[$bookId];
            $stock = (int) $book['stock_quantity'];
            $quantityInput = $_POST['quantity'] ?? '';

            $quantity = is_string($quantityInput)
                ? filter_var(
                    $quantityInput,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                )
                : false;

            if ($quantity === false) {
                $message = 'Enter a valid whole-number quantity of at least 1.';
            } else {
                $existing = (int) ($_SESSION['cart'][$bookId] ?? 0);

                if ($stock < 1) {
                    $message = 'This book is out of stock.';
                } elseif (
                    $quantity > $stock ||
                    ($action === 'add' && $existing > $stock - $quantity)
                ) {
                    $message = 'The requested quantity exceeds available stock.';
                } else {
                    $_SESSION['cart'][$bookId] = $action === 'add'
                        ? $existing + $quantity
                        : $quantity;

                    $message = $action === 'add'
                        ? 'The book was added to your cart.'
                        : 'The quantity was updated.';
                }
            }
        }

        $_SESSION['cart_message'] = $message;
        session_write_close();
        header('Location: cart.php', true, 303);
        exit;
    }
}

if (!$loadError) {
    foreach ($_SESSION['cart'] as $bookId => $quantity) {
        if (!isset($booksById[$bookId])) {
            unset($_SESSION['cart'][$bookId]);
            $message = 'An unavailable book was removed from your cart.';
            continue;
        }

        $book = $booksById[$bookId];
        $quantity = (int) $quantity;

        if ($quantity < 1) {
            unset($_SESSION['cart'][$bookId]);
            continue;
        }

        $unitCents = cartPriceCents($book['price']);
        $lineCents = $unitCents * $quantity;
        $totalCents += $lineCents;

        $cartItems[] = [
            'book_id' => (int) $bookId,
            'title' => $book['title'],
            'stock' => (int) $book['stock_quantity'],
            'quantity' => $quantity,
            'unit_cents' => $unitCents,
            'line_cents' => $lineCents,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Paper Trail Books</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/catalogue.css">
</head>
<body>

<header>
    <h1>Paper Trail Books</h1>
    <nav aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="catalogue.php">Catalogue</a>
        <a href="cart.php" aria-current="page">Cart</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main class="catalogue-page">
    <h2>Shopping Cart</h2>
    <p>Demonstration cart. All prices are in AUD.</p>
    <p>Adding a book does not reserve stock. No orders or payments are processed.</p>

    <?php if ($loadError): ?>
        <p role="alert">
            The cart is temporarily unavailable. Please try again later.
        </p>
    <?php else: ?>
        <?php if ($message !== ''): ?>
            <p role="status"><?= cartEscape($message) ?></p>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($cartItems as $item): ?>
                    <article class="book-card">
                        <h3><?= cartEscape($item['title']) ?></h3>

                        <p>
                            Unit price:
                            AUD $<?= cartMoney($item['unit_cents']) ?>
                        </p>

                        <p>
                            Subtotal:
                            AUD $<?= cartMoney($item['line_cents']) ?>
                        </p>

                        <p>
                            Available stock: <?= $item['stock'] ?>
                        </p>

                        <?php if ($item['quantity'] > $item['stock']): ?>
                            <p role="alert">
                                Stock has changed. Reduce the quantity
                                or remove this book.
                            </p>
                        <?php endif; ?>

                        <form action="cart.php" method="post">
                            <input type="hidden" name="csrf_token"
                                value="<?= cartEscape($_SESSION['cart_csrf_token']) ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="book_id"
                                value="<?= $item['book_id'] ?>">

                            <label for="quantity-<?= $item['book_id'] ?>">
                                Quantity
                            </label>
                            <input type="number"
                                id="quantity-<?= $item['book_id'] ?>"
                                name="quantity"
                                min="1"
                                step="1"
                                value="<?= $item['quantity'] ?>"
                                required>

                            <button type="submit">Update quantity</button>
                        </form>

                        <form action="cart.php" method="post">
                            <input type="hidden" name="csrf_token"
                                value="<?= cartEscape($_SESSION['cart_csrf_token']) ?>">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="book_id"
                                value="<?= $item['book_id'] ?>">

                            <button type="submit">Remove book</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="book-price">
                Book total: AUD $<?= cartMoney($totalCents) ?>
            </p>
            <p>This is a book-price total, not a final checkout quote.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="catalogue.php">Continue browsing</a></p>
</main>

</body>
</html>