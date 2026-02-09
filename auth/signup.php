<?php
$pageTitle = "Sign Up";
require_once './includes/auth.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    redirectToDashboard();
    exit;
}

require_once './config/db.php';

$errors = [];
$success = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the data
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'All fields are required';
    }

    if (!empty($name) && strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 characters long';
    }

    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password';
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }

    if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            $errors[] = 'Email already exists';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Get the "client" role_id
            $stmt = $db->prepare("SELECT role_id FROM roles WHERE role_name = :role_name");
            $stmt->execute(['role_name' => 'client']);
            $clientRole = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$clientRole) {
                $error = 'System error: Client role not found';
            } else {
                // Insert user into database
                $stmt = $db->prepare("
                INSERT INTO users (name, email, password, role_id) 
                VALUES (:name, :email, :password, :role_id)");
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role_id' => $clientRole->role_id
                ]);

                $success = 'Successfully registered! You can now login.';

                // Auto-login after signup
                $_SESSION['authenticated'] = true;
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['name'] = $user->name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'client';
                redirectToDashboard();
                exit;
            }

        }
    }
}

require_once './includes/header.php';
?>

<div class="form">
    <h2>Create an account</h2>
    <p class="subtitle">Join as a client and start collaborating</p>

    <div class="info-box"
        style="margin: 20px 0; padding: 20px; border-radius: 10px; background-color: #172c9820; border: 1px solid #7878ff4c;">
        ℹ️ New accounts are created as <strong>Client</strong> users. Contact an admin to change your role.
    </div>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <strong>Please fix the following error(s):</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?= $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>"
                required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <div class="link">
        Already have an account? <a href="login">Login here</a>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>