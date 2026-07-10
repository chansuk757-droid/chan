<?php
session_start();

// ຖ້າເຂົ້າສູ່ລະບົບແລ້ວ ໃຫ້ເດັ້ງໄປໜ້າ dashboard ເລີຍ
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 🔒 ທ່ານສາມາດປ່ຽນ Username & Password ຢູ່ບ່ອນນີ້ໄດ້ເລີຍເດີ້
    if ($username === "admin" && $password === "12345678") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Username ຫຼື Password ບໍ່ຖືກຕ້ອງ!";
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>Login - Cummins Evaluation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f6f9; }
        .login-card { max-width: 400px; margin: 100px auto; border: none; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card shadow">
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h4 class="mb-0">🔐 ເຂົ້າສູ່ລະບົບ Dashboard</h4>
        </div>
        <div class="card-body p-4">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger p-2 text-center small"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username:</label>
                    <input type="text" name="username" class="form-control" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Password:</label>
                    <input type="password" name="password" class="form-control" placeholder="ປ້ອນລະຫັດຜ່ານ" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">ເຂົ້າສູ່ລະບົບ</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>