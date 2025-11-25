<?php
// battle.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$characters = getUserCharacters($_SESSION['user_id']);
$battle_result = null;

// Processar batalha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['character1_id']) && isset($_POST['character2_id'])) {
        // Batalha PvP
        $character1_id = intval($_POST['character1_id']);
        $character2_id = intval($_POST['character2_id']);
        
        $char1 = getCharacter($character1_id, $_SESSION['user_id']);
        $char2 = getCharacter($character2_id, $_SESSION['user_id']);
        
        if ($char1 && $char2 && $char1['id'] != $char2['id']) {
            $battle_result = simulateBattle($char1, $char2);
        } else {
            $_SESSION['error'] = "Selecione dois personagens diferentes para batalhar!";
        }
    } elseif (isset($_POST['character_id']) && isset($_POST['zombie_type'])) {
        // Batalha contra zumbi
        $character_id = intval($_POST['character_id']);
        $zombie_type = $_POST['zombie_type'];
        
        $character = getCharacter($character_id, $_SESSION['user_id']);
        if ($character) {
            $battle_result = simulateZombieBattle($character, $zombie_type);
        }
    }
}

// Função para simular batalha PvP
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
        'final_hp2' => max(0, $hp2),
        'vs_zombie' => false
    ];
}

// Função para calcular dano PvP
function calculateDamage($attacker, $defender) {
    $base_damage = 5;
    
    // Dano baseado na força do atacante e defesa baseada na agilidade do defensor
    $damage = $base_damage + ($attacker['strength'] * 0.5) - ($defender['agility'] * 0.3);
    
    // Chance de crítico baseada na inteligência
    $crit_chance = $attacker['intelligence'] * 0.01;
    if (mt_rand(1, 100) <= ($crit_chance * 100)) {
        $damage *= 2;
        $damage = round($damage);
    }
    
    return max(1, round($damage));
}

// Função para criar zumbi
function createZombie($type) {
    $zombies = [
        'walker' => [
            'name' => 'Zumbi Caminhante',
            'strength' => 8,
            'agility' => 3,
            'intelligence' => 1,
            'health_points' => 50,
            'max_health' => 50
        ],
        'runner' => [
            'name' => 'Zumbi Corredor', 
            'strength' => 6,
            'agility' => 12,
            'intelligence' => 2,
            'health_points' => 40,
            'max_health' => 40
        ],
        'tank' => [
            'name' => 'Zumbi Tanque',
            'strength' => 15,
            'agility' => 2,
            'intelligence' => 1,
            'health_points' => 80,
            'max_health' => 80
        ],
        'infected' => [
            'name' => 'Humano Infectado',
            'strength' => 10,
            'agility' => 8,
            'intelligence' => 5,
            'health_points' => 60,
            'max_health' => 60
        ]
    ];
    
    return $zombies[$type] ?? $zombies['walker'];
}

// Função para simular batalha contra zumbi
function simulateZombieBattle($character, $zombie_type) {
    $pdo = getDBConnection();
    $battle_log = [];
    
    $zombie = createZombie($zombie_type);
    
    $battle_log[] = "[INICIO] {$character['name']} vs {$zombie['name']}";
    
    $hp_char = $character['health_points'];
    $hp_zombie = $zombie['health_points'];
    
    $turn = 1;
    $max_turns = 10;
    
    while ($hp_char > 0 && $hp_zombie > 0 && $turn <= $max_turns) {
        $battle_log[] = "--- Turno $turn ---";
        
        // Personagem ataca
        $damage_char = calculateDamage($character, $zombie);
        $hp_zombie -= $damage_char;
        $battle_log[] = "[ATAQUE] {$character['name']} ataca {$zombie['name']} causando $damage_char de dano!";
        
        if ($hp_zombie <= 0) {
            $battle_log[] = "[VITORIA] {$zombie['name']} foi derrotado!";
            $winner_id = $character['id'];
            $experience_gain = calculateExperience($zombie_type);
            $battle_log[] = "[EXPERIENCIA] +{$experience_gain} pontos de experiência!";
            break;
        }
        
        // Zumbi ataca
        $damage_zombie = calculateZombieDamage($zombie, $character);
        $hp_char -= $damage_zombie;
        $battle_log[] = "[ATAQUE ZUMBI] {$zombie['name']} ataca {$character['name']} causando $damage_zombie de dano!";
        
        if ($hp_char <= 0) {
            $battle_log[] = "[DERROTA] {$character['name']} foi infectado!";
            $winner_id = null;
            break;
        }
        
        $battle_log[] = "[VIDA] {$character['name']} ($hp_char) | {$zombie['name']} ($hp_zombie)";
        $turn++;
    }
    
    // Empate
    if ($hp_char > 0 && $hp_zombie > 0) {
        $battle_log[] = "[RETIRADA] Batalha interrompida - sobrevivente recuou!";
        $winner_id = null;
    }
    
    // Registrar batalha - USANDO -1 PARA ZUMBI (Solução 2)
    $stmt = $pdo->prepare("
        INSERT INTO battles (character1_id, character2_id, winner_id, battle_log) 
        VALUES (?, -1, ?, ?)
    ");
    $stmt->execute([$character['id'], $winner_id, implode("\n", $battle_log)]);
    
    return [
        'char1' => $character,
        'char2' => $zombie,
        'winner_id' => $winner_id,
        'log' => $battle_log,
        'final_hp1' => max(0, $hp_char),
        'final_hp2' => max(0, $hp_zombie),
        'vs_zombie' => true
    ];
}

function calculateZombieDamage($zombie, $character) {
    $base_damage = 3;
    $damage = $base_damage + ($zombie['strength'] * 0.3);
    
    // Chance de infecção
    $infection_chance = 5; // 5% de chance
    if (mt_rand(1, 100) <= $infection_chance) {
        $damage *= 1.5;
        $damage = round($damage);
    }
    
    return max(1, round($damage));
}

function calculateExperience($zombie_type) {
    $exp_values = [
        'walker' => 10,
        'runner' => 15, 
        'tank' => 25,
        'infected' => 20
    ];
    
    return $exp_values[$zombie_type] ?? 10;
}
?>

<?php include 'includes/header.php'; ?>

<h2>Sistema de Batalha - Modo Sobrevivência</h2>

<div class="row">
    <!-- Batalha PvP -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>🔄 Batalha entre Sobreviventes</h5>
            </div>
            <div class="card-body">
                <?php if (count($characters) < 2): ?>
                    <div class="alert alert-warning">
                        Você precisa de pelo menos 2 sobreviventes para batalhar!
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="character1_id" class="form-label">Sobrevivente 1</label>
                            <select class="form-select" id="character1_id" name="character1_id" required>
                                <option value="">Selecione um sobrevivente</option>
                                <?php foreach ($characters as $char): ?>
                                    <option value="<?php echo $char['id']; ?>">
                                        <?php echo htmlspecialchars($char['name']); ?> (<?php echo $char['class']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="character2_id" class="form-label">Sobrevivente 2</label>
                            <select class="form-select" id="character2_id" name="character2_id" required>
                                <option value="">Selecione um sobrevivente</option>
                                <?php foreach ($characters as $char): ?>
                                    <option value="<?php echo $char['id']; ?>">
                                        <?php echo htmlspecialchars($char['name']); ?> (<?php echo $char['class']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Iniciar Batalha PvP</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Batalha contra Zumbis -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>🧟 Batalha contra Zumbis</h5>
            </div>
            <div class="card-body">
                <?php if (empty($characters)): ?>
                    <div class="alert alert-warning">
                        Você precisa de pelo menos 1 sobrevivente para lutar contra zumbis!
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="character_id" class="form-label">Seu Sobrevivente</label>
                            <select class="form-select" id="character_id" name="character_id" required>
                                <option value="">Selecione um sobrevivente</option>
                                <?php foreach ($characters as $char): ?>
                                    <option value="<?php echo $char['id']; ?>">
                                        <?php echo htmlspecialchars($char['name']); ?> (<?php echo $char['class']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="zombie_type" class="form-label">Tipo de Zumbi</label>
                            <select class="form-select" id="zombie_type" name="zombie_type" required>
                                <option value="walker">🧟 Zumbi Caminhante (Fácil)</option>
                                <option value="runner">🏃 Zumbi Corredor (Médio)</option>
                                <option value="infected">😷 Humano Infectado (Difícil)</option>
                                <option value="tank">💪 Zumbi Tanque (Muito Difícil)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Lutar contra Zumbis</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Resultado da Batalha -->
<?php if ($battle_result): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Resultado da Batalha</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($battle_result['winner_id']): ?>
                        <?php if (isset($battle_result['vs_zombie']) && $battle_result['vs_zombie']): ?>
                            <h4 class="text-success">🎉 Vitória! Zumbi derrotado!</h4>
                        <?php else: ?>
                            <?php $winner = $battle_result['winner_id'] == $battle_result['char1']['id'] ? $battle_result['char1'] : $battle_result['char2']; ?>
                            <h4 class="text-success">🏆 Vencedor: <?php echo $winner['name']; ?></h4>
                        <?php endif; ?>
                    <?php else: ?>
                        <h4 class="text-warning">🤝 Empate/Retirada!</h4>
                    <?php endif; ?>
                </div>
                
                <div class="battle-log">
                    <?php foreach ($battle_result['log'] as $log_entry): ?>
                        <div><?php echo htmlspecialchars($log_entry); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>