<?php

ini_set('display_errors', '0');
require __DIR__ . '/cart-session.php';

$books = [];
$loadError = false;

$searchInput = $_GET['search'] ?? '';
$search = is_string($searchInput) ? trim($searchInput) : '';

if (strlen($search) > 200) {
    http_response_code(400);
    exit('Search text is too long. Please use a shorter search.');
}

try {
    require __DIR__ . '/db.php';

    $sql = 'SELECT book_id, title, author, category, price,
                   stock_quantity, description
            FROM books';

    if ($search !== '') {
        $sql .= ' WHERE LOCATE(?, title) > 0
                     OR LOCATE(?, author) > 0';
    }

    $sql .= ' ORDER BY title';

    $statement = $pdo->prepare($sql);
    $statement->execute(
        $search !== '' ? [$search, $search] : []
    );

    $books = $statement->fetchAll();
} catch (Throwable $error) {
    http_response_code(500);
    $loadError = true;
}

function escapeHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalogue - Paper Trail Books</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/catalogue.css">
</head>
<body>

<header>
    <h1>Paper Trail Books</h1>
    <nav aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="catalogue.php" aria-current="page">Catalogue</a>
        <a href="cart.php">Cart</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main class="catalogue-page">
    <h2>Book Catalogue</h2>
    <p>Browse our fictional demonstration collection. All prices are in AUD.</p>

    <form class="catalogue-search" action="catalogue.php" method="get">
    <label for="book-search">Search by title or author</label>

    <div class="search-controls">
        <input
            type="search"
            id="book-search"
            name="search"
            value="<?= escapeHtml($search) ?>"
            placeholder="Enter a title or author"
            maxlength="200"
        >
        <button type="submit">Search</button>
        <a href="catalogue.php">Show all books</a>
    </div>
</form>

    <?php if ($loadError): ?>
        <p role="alert">
            The catalogue is temporarily unavailable. Please try again later.
        </p>
    <?php elseif (empty($books)): ?>
    <?php if ($search !== ''): ?>
        <p>No books found for “<?= escapeHtml($search) ?>”.</p>
    <?php else: ?>
        <p>No books are currently available in the catalogue.</p>
    <?php endif; ?>
<?php else: ?>
    <?php if ($search !== ''): ?>
        <p>
            Search results for “<?= escapeHtml($search) ?>”:
            <?= count($books) ?> book(s) found.
        </p>
    <?php else: ?>
        <p><?= count($books) ?> books in the catalogue.</p>
    <?php endif; ?>

        <div class="book-grid">
            <?php foreach ($books as $book): ?>
                <article class="book-card">
                    <h3><?= escapeHtml($book['title']) ?></h3>

                    <p>By <?= escapeHtml($book['author']) ?></p>

                    <p>
                        Category: <?= escapeHtml($book['category']) ?>
                    </p>

                    <p><?= escapeHtml($book['description']) ?></p>

                    <p class="book-price">
                        AUD $<?= number_format((float) $book['price'], 2) ?>
                    </p>

                    <?php if ((int) $book['stock_quantity'] > 0): ?>
    <p class="stock-available">
        In stock:
        <?= (int) $book['stock_quantity'] ?> available
    </p>

    <form action="cart.php" method="post">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= cartEscape($_SESSION['cart_csrf_token']) ?>"
        >
        <input type="hidden" name="action" value="add">
        <input
            type="hidden"
            name="book_id"
            value="<?= (int) $book['book_id'] ?>"
        >
        <input type="hidden" name="quantity" value="1">

        <button type="submit">Add to cart</button>
    </form>
<?php else: ?>
    <p class="stock-unavailable">Out of stock</p>
    <button type="button" disabled>Out of stock</button>
<?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>