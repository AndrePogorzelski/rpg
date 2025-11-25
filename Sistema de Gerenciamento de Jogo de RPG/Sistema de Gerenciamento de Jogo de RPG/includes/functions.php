<?php
// includes/functions.php
require_once 'config/database.php';

// Função para calcular vida máxima baseada nos atributos
function calculateMaxHealth($strength, $level) {
    return 80 + ($strength * 2) + ($level * 10);
}

// Função para calcular experiência necessária para o próximo nível
function expForNextLevel($currentLevel) {
    return $currentLevel * 100;
}

// Função para upload de imagem
function uploadImage($file) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Verificar se é uma imagem real
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "message" => "Arquivo não é uma imagem."];
    }
    
    // Verificar tamanho do arquivo (max 2MB)
    if ($file["size"] > 2000000) {
        return ["success" => false, "message" => "Arquivo muito grande. Máximo 2MB."];
    }
    
    // Permitir apenas certos formatos
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        return ["success" => false, "message" => "Apenas JPG, JPEG, PNG e GIF são permitidos."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $filename];
    } else {
        return ["success" => false, "message" => "Erro ao fazer upload da imagem."];
    }
}

// Função para obter todos os personagens do usuário
function getUserCharacters($user_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM characters WHERE user_id = ? ORDER BY level DESC, name ASC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter um personagem específico
function getCharacter($character_id, $user_id = null) {
    $pdo = getDBConnection();
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ? AND user_id = ?");
        $stmt->execute([$character_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ?");
        $stmt->execute([$character_id]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para deletar personagem
function deleteCharacter($character_id, $user_id) {
    $pdo = getDBConnection();
    
    // Primeiro verificar se o personagem pertence ao usuário
    $character = getCharacter($character_id, $user_id);
    if (!$character) {
        return false;
    }
    
    // Deletar imagem se existir
    if ($character['image_path'] && file_exists("uploads/" . $character['image_path'])) {
        unlink("uploads/" . $character['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM characters WHERE id = ? AND user_id = ?");
    return $stmt->execute([$character_id, $user_id]);
}

// Função para obter ranking de personagens
function getCharacterRanking() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT c.*, u.username as user_name 
        FROM characters c 
        JOIN users u ON c.user_id = u.id 
        ORDER BY c.level DESC, c.experience DESC 
        LIMIT 20
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>