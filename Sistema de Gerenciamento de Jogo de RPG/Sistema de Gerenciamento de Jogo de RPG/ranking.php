<?php
// ranking.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$ranking = getCharacterRanking();
?>

<?php include 'includes/header.php'; ?>

<h2>Ranking de Personagens</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Personagem</th>
                        <th>Jogador</th>
                        <th>Classe</th>
                        <th>Nível</th>
                        <th>Experiência</th>
                        <th>HP</th>
                        <th>Força</th>
                        <th>Agilidade</th>
                        <th>Inteligência</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $index => $character): ?>
                        <tr>
                            <td>
                                <?php if ($index == 0): ?>🥇
                                <?php elseif ($index == 1): ?>🥈
                                <?php elseif ($index == 2): ?>🥉
                                <?php else: ?>
                                    #<?php echo $index + 1; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($character['image_path']): ?>
                                    <img src="uploads/<?php echo $character['image_path']; ?>" alt="<?php echo htmlspecialchars($character['name']); ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 50%;" class="me-2">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($character['name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($character['user_name']); ?></td>
                            <td><?php echo $character['class']; ?></td>
                            <td><?php echo $character['level']; ?></td>
                            <td><?php echo $character['experience']; ?></td>
                            <td><?php echo $character['health_points']; ?>/<?php echo $character['max_health']; ?></td>
                            <td><?php echo $character['strength']; ?></td>
                            <td><?php echo $character['agility']; ?></td>
                            <td><?php echo $character['intelligence']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>