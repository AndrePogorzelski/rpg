<?php
// battle.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$characters = getUserCharacters($_SESSION['user_id']);
$battle_result = null;

// Processar batalha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['character1_id']) && isset($_POST['character2_id'])) {
    $character1_id = intval($_POST['character1_id']);
    $character2_id = intval($_POST['character2_id']);
    
    $char1 = getCharacter($character1_id, $_SESSION['user_id']);
    $char2 = getCharacter($character2_id, $_SESSION['user_id']);
    
    if ($char1 && $char2 && $char1['id'] != $char2['id']) {
        $battle_result = simulateBattle($char1, $char2);
    } else {
        $_SESSION['error'] = "Selecione dois personagens diferentes para batalhar!";
    }
}

// Função para simular batalha
function simulateBattle($char1, $char2) {
    $pdo = getDBConnection();
    $battle_log = [];
    
    $battle_log[] = "[INICIO] Batalha iniciada: {$char1['name']} vs {$char2['name']}";
    
    $hp1 = $char1['health_points'];
    $hp2 = $char2['health_points'];
    
    $turn = 1;
    $max_turns = 10;
    
    while ($hp1 > 0 && $hp2 > 0 && $turn <= $max_turns) {
        $battle_log[] = "--- Turno $turn ---";
        
        // Personagem 1 ataca
        $damage1 = calculateDamage($char1, $char2);
        $hp2 -= $damage1;
        $battle_log[] = "[ATAQUE] {$char1['name']} ataca {$char2['name']} causando $damage1 de dano!";
        
        if ($hp2 <= 0) {
            $battle_log[] = "[VITORIA] {$char2['name']} foi derrotado!";
            $winner_id = $char1['id'];
            break;
        }
        
        // Personagem 2 ataca
        $damage2 = calculateDamage($char2, $char1);
        $hp1 -= $damage2;
        $battle_log[] = "[ATAQUE] {$char2['name']} ataca {$char1['name']} causando $damage2 de dano!";
        
        if ($hp1 <= 0) {
            $battle_log[] = "[VITORIA] {$char1['name']} foi derrotado!";
            $winner_id = $char2['id'];
            break;
        }
        
        $battle_log[] = "[VIDA] Vida restante: {$char1['name']} ($hp1) | {$char2['name']} ($hp2)";
        $turn++;
    }
    
    // Empate
    if ($hp1 > 0 && $hp2 > 0) {
        $battle_log[] = "[EMPATE] Batalha terminou em empate!";
        $winner_id = null;
    }
    
    // Registrar batalha no banco
    $stmt = $pdo->prepare("
        INSERT INTO battles (character1_id, character2_id, winner_id, battle_log) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$char1['id'], $char2['id'], $winner_id, implode("\n", $battle_log)]);
    
    return [
        'char1' => $char1,
        'char2' => $char2,
        'winner_id' => $winner_id,
        'log' => $battle_log,
        'final_hp1' => max(0, $hp1),
        'final_hp2' => max(0, $hp2)
    ];
}

function calculateDamage($attacker, $defender) {
    $base_damage = 5;
    $damage = $base_damage + ($attacker['strength'] * 0.5) - ($defender['agility'] * 0.3);
    $crit_chance = $attacker['intelligence'] * 0.01;
    
    if (mt_rand(1, 100) <= ($crit_chance * 100)) {
        $damage *= 2;
    }
    
    return max(1, round($damage));
}
?>

<?php include 'includes/header.php'; ?>

<h2>Sistema de Batalha</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Selecionar Personagens para Batalha</h5>
            </div>
            <div class="card-body">
                <?php if (count($characters) < 2): ?>
                    <div class="alert alert-warning">
                        Você precisa de pelo menos 2 personagens para batalhar!
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="character1_id" class="form-label">Personagem 1</label>
                            <select class="form-select" id="character1_id" name="character1_id" required>
                                <option value="">Selecione um personagem</option>
                                <?php foreach ($characters as $char): ?>
                                    <option value="<?php echo $char['id']; ?>">
                                        <?php echo htmlspecialchars($char['name']); ?> (Nível <?php echo $char['level']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="character2_id" class="form-label">Personagem 2</label>
                            <select class="form-select" id="character2_id" name="character2_id" required>
                                <option value="">Selecione um personagem</option>
                                <?php foreach ($characters as $char): ?>
                                    <option value="<?php echo $char['id']; ?>">
                                        <?php echo htmlspecialchars($char['name']); ?> (Nível <?php echo $char['level']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Iniciar Batalha!</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($battle_result): ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Resultado da Batalha</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($battle_result['winner_id']): ?>
                        <?php $winner = $battle_result['winner_id'] == $battle_result['char1']['id'] ? $battle_result['char1'] : $battle_result['char2']; ?>
                        <h4 class="text-success">Vencedor: <?php echo $winner['name']; ?></h4>
                    <?php else: ?>
                        <h4 class="text-warning">Empate!</h4>
                    <?php endif; ?>
                </div>
                
                <div class="battle-log" style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <?php foreach ($battle_result['log'] as $log_entry): ?>
                        <div><?php echo htmlspecialchars($log_entry); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>