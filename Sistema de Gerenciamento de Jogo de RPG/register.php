<?php
// register.php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "As senhas não coincidem!";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "A senha deve ter pelo menos 6 caracteres!";
    } else {
        if (registerUser($username, $email, $password)) {
            $_SESSION['message'] = "Conta criada com sucesso! Faça login.";
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['error'] = "Erro ao criar conta. Usuário ou email já existem.";
        }
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

        <div class="card">
            <div class="card-header">
                <h4>Criar Conta</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Senha</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Criar Conta</button>
                    <a href="login.php" class="btn btn-link">Já tem conta? Faça login</a>
                </form>
            </div>
        </div>


<?php include 'includes/footer.php'; ?>