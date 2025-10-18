<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $full_name, $phone_number]);
        
        header("Location: login.php?success=registered");
        exit();
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2 class="amharic">መለያ ፍጠር</h2>
    <p>Create your Sewasew account</p>

    <?php if (isset($error)): ?>
        <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
    </form>

    <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>