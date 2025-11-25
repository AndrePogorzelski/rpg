<?php
// index.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$characters = getUserCharacters($_SESSION['user_id']);
?>

<?php include 'includes/header.php'; ?>

<h2>Meus Personagens</h2>

<?php if (empty($characters)): ?>
    <div class="alert alert-info">
        Você ainda não criou nenhum personagem. <a href="character_form.php">Crie seu primeiro personagem!</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($characters as $character): ?>
            <div class="col-md-4 mb-4">
                <div class="card character-card">
                    <?php if ($character['image_path']): ?>
                        <img src="uploads/<?php echo $character['image_path']; ?>" class="card-img-top" alt="<?php echo $character['name']; ?>" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($character['name']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo $character['class']; ?> - Nível <?php echo $character['level']; ?></h6>
                        
                        <div class="mb-2">
                            <small>HP: <?php echo $character['health_points']; ?>/<?php echo $character['max_health']; ?></small>
                            <div class="progress stats-bar">
                                <div class="progress-bar bg-danger" style="width: <?php echo ($character['health_points'] / $character['max_health']) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <small>FOR</small><br>
                                <strong><?php echo $character['strength']; ?></strong>
                            </div>
                            <div class="col-4">
                                <small>AGI</small><br>
                                <strong><?php echo $character['agility']; ?></strong>
                            </div>
                            <div class="col-4">
                                <small>INT</small><br>
                                <strong><?php echo $character['intelligence']; ?></strong>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="character_form.php?id=<?php echo $character['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="character_form.php?delete=<?php echo $character['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este personagem?')">Excluir</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>