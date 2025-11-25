<?php
// character_form.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$character = null;
$editing = false;

// Processar exclusão
if (isset($_GET['delete'])) {
    $character_id = intval($_GET['delete']);
    if (deleteCharacter($character_id, $_SESSION['user_id'])) {
        $_SESSION['message'] = "Personagem excluído com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao excluir personagem!";
    }
    header('Location: index.php');
    exit;
}

// Carregar personagem para edição
if (isset($_GET['id'])) {
    $character_id = intval($_GET['id']);
    $character = getCharacter($character_id, $_SESSION['user_id']);
    if ($character) {
        $editing = true;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $class = $_POST['class'];
    $strength = intval($_POST['strength']);
    $agility = intval($_POST['agility']);
    $intelligence = intval($_POST['intelligence']);
    
    // Validar atributos
    $total_points = $strength + $agility + $intelligence;
    $max_points = $editing ? 100 : 30;
    
    if ($total_points > $max_points) {
        $_SESSION['error'] = "Total de pontos de atributos não pode exceder $max_points!";
        header('Location: character_form.php' . ($editing ? '?id=' . $character['id'] : ''));
        exit;
    }
    
    $max_health = calculateMaxHealth($strength, $editing ? $character['level'] : 1);
    $health_points = $editing ? min($character['health_points'], $max_health) : $max_health;
    
    $pdo = getDBConnection();
    
    // Processar upload de imagem
    $image_path = $editing ? $character['image_path'] : null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image']);
        if ($upload_result['success']) {
            $image_path = $upload_result['filename'];
            if ($editing && $character['image_path']) {
                unlink("uploads/" . $character['image_path']);
            }
        } else {
            $_SESSION['error'] = $upload_result['message'];
        }
    }
    
    if ($editing) {
        $stmt = $pdo->prepare("
            UPDATE characters 
            SET name = ?, class = ?, strength = ?, agility = ?, intelligence = ?, 
                max_health = ?, health_points = ?, image_path = ?
            WHERE id = ? AND user_id = ?
        ");
        $success = $stmt->execute([
            $name, $class, $strength, $agility, $intelligence,
            $max_health, $health_points, $image_path,
            $character['id'], $_SESSION['user_id']
        ]);
        
        if ($success) {
            $_SESSION['message'] = "Personagem atualizado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao atualizar personagem!";
        }
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO characters 
            (user_id, name, class, strength, agility, intelligence, health_points, max_health, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $success = $stmt->execute([
            $_SESSION['user_id'], $name, $class, $strength, $agility, $intelligence,
            $health_points, $max_health, $image_path
        ]);
        
        if ($success) {
            $_SESSION['message'] = "Personagem criado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao criar personagem!";
        }
    }
    
    header('Location: index.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<h2><?php echo $editing ? 'Editar Sobrevivente' : 'Criar Novo Sobrevivente'; ?></h2>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Sobrevivente</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $editing ? htmlspecialchars($character['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="class" class="form-label">Classe</label>
                                <select class="form-select" id="class" name="class" required>
                                    <option value="">Selecione uma classe</option>
                                    <option value="Militar" <?php echo ($editing && $character['class'] == 'Militar') ? 'selected' : ''; ?>>🎖️ Militar</option>
                                    <option value="Civil Sobrevivente" <?php echo ($editing && $character['class'] == 'Civil Sobrevivente') ? 'selected' : ''; ?>>👨‍🔧 Civil Sobrevivente</option>
                                    <option value="Milícia" <?php echo ($editing && $character['class'] == 'Milícia') ? 'selected' : ''; ?>>🔫 Milícia</option>
                                    <option value="Sobrevivente Solo" <?php echo ($editing && $character['class'] == 'Sobrevivente Solo') ? 'selected' : ''; ?>>🧟‍♂️ Sobrevivente Solo</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagem do Sobrevivente</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if ($editing && $character['image_path']): ?>
                                    <div class="mt-2">
                                        <img src="uploads/<?php echo $character['image_path']; ?>" alt="Imagem atual" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Atributos</h5>
                            <small class="text-muted">Total de pontos: <span id="totalPoints">0</span>/<?php echo $editing ? '100' : '30'; ?></small>
                            
                            <div class="mb-3">
                                <label for="strength" class="form-label">💪 Força</label>
                                <input type="range" class="form-range" id="strength" name="strength" min="1" max="20" 
                                       value="<?php echo $editing ? $character['strength'] : '10'; ?>" oninput="updatePoints()">
                                <span id="strengthValue"><?php echo $editing ? $character['strength'] : '10'; ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <label for="agility" class="form-label">🏃 Agilidade</label>
                                <input type="range" class="form-range" id="agility" name="agility" min="1" max="20" 
                                       value="<?php echo $editing ? $character['agility'] : '10'; ?>" oninput="updatePoints()">
                                <span id="agilityValue"><?php echo $editing ? $character['agility'] : '10'; ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <label for="intelligence" class="form-label">🧠 Inteligência</label>
                                <input type="range" class="form-range" id="intelligence" name="intelligence" min="1" max="20" 
                                       value="<?php echo $editing ? $character['intelligence'] : '10'; ?>" oninput="updatePoints()">
                                <span id="intelligenceValue"><?php echo $editing ? $character['intelligence'] : '10'; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Atualizar' : 'Criar'; ?> Sobrevivente</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updatePoints() {
    const strength = parseInt(document.getElementById('strength').value);
    const agility = parseInt(document.getElementById('agility').value);
    const intelligence = parseInt(document.getElementById('intelligence').value);
    
    document.getElementById('strengthValue').textContent = strength;
    document.getElementById('agilityValue').textContent = agility;
    document.getElementById('intelligenceValue').textContent = intelligence;
    
    const total = strength + agility + intelligence;
    document.getElementById('totalPoints').textContent = total;
}

updatePoints();
</script>

<?php include 'includes/footer.php'; ?>