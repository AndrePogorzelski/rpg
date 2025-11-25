<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema RPG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .character-card {
            transition: transform 0.2s;
        }
        .character-card:hover {
            transform: translateY(-5px);
        }
        .stats-bar {
            height: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🏰 Sistema RPG</a>
            
            <?php if (isLoggedIn()): ?>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Meus Personagens</a>
                <a class="nav-link" href="character_form.php">Novo Personagem</a>
                <a class="nav-link" href="battle.php">Batalha</a>
                <a class="nav-link" href="ranking.php">Ranking</a>
                <a class="nav-link" href="export_csv.php">Exportar CSV</a>
                <span class="nav-link">Olá, <?php echo $_SESSION['username']; ?>!</span>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>