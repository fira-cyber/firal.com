<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Update online status
            $stmt = $pdo->prepare("UPDATE users SET is_online = TRUE WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } catch(PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2 class="amharic">ግባ</h2>
    <p>Login to your Sewasew account</p>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
        <div style="background: #e8f5e8; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            Registration successful! Please login.
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>

    <p style="text-align: center; margin-top: 20px;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>