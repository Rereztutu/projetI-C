<?php
// Connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=parc_instrumentation;charset=utf8mb4';
$username = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $username, $password, $options);

// Détection de l'action demandée
$action = $_GET['action'] ?? null;

if ($action === 'create') {
    // Ajouter une entrée
    $table = $_GET['table'];
    $data = $_POST;
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    echo json_encode(['status' => 'success', 'message' => 'Données ajoutées avec succès.']);
}

if ($action === 'update') {
    // Modifier une entrée
    $table = $_GET['table'];
    $id = $_POST['id'];
    unset($_POST['id']);
    $columns = implode(' = ?, ', array_keys($_POST)) . ' = ?';
    
    $stmt = $pdo->prepare("UPDATE $table SET $columns WHERE id = ?");
    $stmt->execute([...array_values($_POST), $id]);
    echo json_encode(['status' => 'success', 'message' => 'Données mises à jour avec succès.']);
}

if ($action === 'delete') {
    // Supprimer une entrée
    $table = $_GET['table'];
    $id = $_POST['id'];
    
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Données supprimées avec succès.']);
}

if ($action === 'export') {
    // Exporter les données en CSV
    $table = $_GET['table'];
    $stmt = $pdo->query("SELECT * FROM $table");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=export.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($data[0])); // En-têtes
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

if ($action === 'import') {
    // Importer les données depuis un fichier CSV
    $table = $_GET['table'];
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');
    $columns = fgetcsv($handle); // Récupérer les en-têtes
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    
    $stmt = $pdo->prepare("INSERT INTO $table (" . implode(', ', $columns) . ") VALUES ($placeholders)");
    while (($data = fgetcsv($handle)) !== false) {
        $stmt->execute($data);
    }
    fclose($handle);
    echo json_encode(['status' => 'success', 'message' => 'Données importées avec succès.']);
}
?>
