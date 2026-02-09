<?php
$pageTitle = "Login";
require_once './includes/auth.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    redirectToDashboard();
    exit;
}

require_once './config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = 'All fields are required';
    } else {

        $stmt = $db->prepare("
            SELECT users.*, roles.role_name
            FROM users
            JOIN roles ON users.role_id = roles.role_id
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user && password_verify($password, $user->password)) {

            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['name'] = $user->name;
            $_SESSION['email'] = $user->email;
            $_SESSION['role'] = $user->role_name;

            redirectToDashboard();
            exit;

        } else {
            $errors[] = 'Invalid credentials';
        }
    }
}

require_once './includes/header.php';
?>

<div class="form">
    <h2 style="margin-bottom: 10px;">Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <strong>Please fix the following error(s):</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= htmlspecialchars($error); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>"
                required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        Don't have an account? <a href="signup">Sign up here</a>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>