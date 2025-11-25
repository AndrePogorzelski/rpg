<?php
// export_csv.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

if (isset($_GET['export'])) {
    $characters = getUserCharacters($_SESSION['user_id']);
    
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=personagens_rpg.csv');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho do CSV
    fputcsv($output, [
        'Nome', 'Classe', 'Nível', 'Experiência', 
        'Força', 'Agilidade', 'Inteligência', 
        'HP Atual', 'HP Máximo', 'Data de Criação'
    ], ';');
    
    // Dados
    foreach ($characters as $character) {
        fputcsv($output, [
            $character['name'],
            $character['class'],
            $character['level'],
            $character['experience'],
            $character['strength'],
            $character['agility'],
            $character['intelligence'],
            $character['health_points'],
            $character['max_health'],
            $character['created_at']
        ], ';');
    }
    
    fclose($output);
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<h2>Exportar Dados</h2>

<div class="card">
    <div class="card-body text-center">
        <h5>Exportar meus personagens para CSV</h5>
        <p class="text-muted">Clique no botão abaixo para baixar um arquivo CSV com todos os seus personagens.</p>
        
        <a href="export_csv.php?export=1" class="btn btn-success">
            📥 Exportar para CSV
        </a>
        
        <div class="mt-4">
            <h6>O arquivo CSV conterá:</h6>
            <ul class="list-group">
                <li class="list-group-item">Nome, Classe e Nível dos personagens</li>
                <li class="list-group-item">Atributos (Força, Agilidade, Inteligência)</li>
                <li class="list-group-item">Pontos de vida atuais e máximos</li>
                <li class="list-group-item">Data de criação</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>