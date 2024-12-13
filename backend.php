
<?php
// ========================
// CONFIGURATION ET INITIALISATION
// ========================

// Configuration de la base de données
$host = 'localhost';
$user = 'root';
$password = ''; // Remplacez par votre mot de passe si nécessaire
$database = 'parc_instrumentation';

// Création de la connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    error_log("Erreur de connexion à la base de données : " . $conn->connect_error);
    die("Impossible de se connecter à la base de données.");
}


// Liste des tables autorisées
$allowed_tables = [
    'systeme' => 'Systèmes',
    'capteur_process' => 'Capteurs Procédés',
    'exigence' => 'Exigences',
    'fonction_cc' => 'Fonctions Contrôle-Commande',
    'digramme' => 'Diagrammes',
    'fonctionnement' => 'Fonctionnements',
    'grandeur_physique' => 'Grandeurs Physiques',
    'surete' => 'Sûreté',
    'environnement' => 'Environnement',
    'capteurs_fournisseurs' => 'Capteurs Fournisseurs',
    'coord_fournisseur' => 'Liste des Fournisseurs'
];

// Tableau des noms conviviaux pour les colonnes
$friendly_titles = [
    // Table "fonctionnement"
    'id_fonctionnement' => 'ID Fonctionnement',
    'type_fonctionnement' => 'Type de Fonctionnement',

    // Table "systeme"
    'id_systeme' => 'ID Système',
    'nom_systeme' => 'Nom du Système',
    'description_systeme' => 'Description du Système',
    'etat_systeme' => 'État du Système',

    // Table "capteur_process"
    'id_capteur_process' => 'ID Capteur Process',
    'nom_capteur' => 'Nom du Capteur',
    'type_capteur' => 'Type de Capteur',
    'valeur_capteur' => 'Valeur Mesurée',
    'unite_capteur' => 'Unité de Mesure',
    'precision_capteur' => 'Précision du Capteur',

    // Table "grandeur_physique"
    'id_grandeur' => 'ID Grandeur Physique',
    'nom_grandeur' => 'Nom de la Grandeur',
    'unite_grandeur' => 'Unité de la Grandeur',
    'type_grandeur' => 'Type de Grandeur',

    // Table "exigence"
    'id_exigence' => 'ID Exigence',
    'nom_exigence' => 'Nom de l’Exigence',
    'description_exigence' => 'Description de l’Exigence',
    'niveau_priorite' => 'Niveau de Priorité',

    // Table "fonction_cc"
    'id_fonction_cc' => 'ID Fonction CC',
    'nom_fonction' => 'Nom de la Fonction',
    'description_fonction' => 'Description de la Fonction',
    'categorie_fonction' => 'Catégorie de la Fonction',

    // Table "diagramme"
    'id_diagramme' => 'ID Diagramme',
    'nom_diagramme' => 'Nom du Diagramme',
    'type_diagramme' => 'Type de Diagramme',
    'chemin_fichier' => 'Chemin du Fichier',

    // Table "surete"
    'id_surete' => 'ID Sûreté',
    'type_surete' => 'Type de Sûreté',
    'niveau_surete' => 'Niveau de Sûreté',

    // Table "environnement"
    'id_environnement' => 'ID Environnement',
    'nom_environnement' => 'Nom de l’Environnement',
    'type_environnement' => 'Type d’Environnement',
    'description_environnement' => 'Description de l’Environnement',

    // Table "capteurs_fournisseurs"
    'id_capteur_fournisseur' => 'ID Capteur Fournisseur',
    'nom_fournisseur' => 'Nom du Fournisseur',
    'adresse_fournisseur' => 'Adresse du Fournisseur',
    'contact_fournisseur' => 'Contact Fournisseur',
    'email_fournisseur' => 'Email du Fournisseur',

    // Table "coord_fournisseur"
    'id_coord_fournisseur' => 'ID Coord Fournisseur',
    'nom_entreprise' => 'Nom de l’Entreprise',
    'adresse' => 'Adresse',
    'telephone' => 'Téléphone',
    'email' => 'Email',
    'site_web' => 'Site Web'
];

// ========================
// GESTION DES REQUÊTES
// ========================

// Récupération des paramètres GET
$action = isset($_GET['action']) ? $_GET['action'] : null;
$table = isset($_GET['table']) ? $_GET['table'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Validation de la table
if ($table && !array_key_exists($table, $allowed_tables)) {
    echo json_encode(['error' => 'Table non autorisée']);
    exit;
}
// ========================
// FONCTIONS UTILITAIRES
// ========================

/**
 * Affiche une table sous forme de tableau HTML.
 */
function displayTable($conn, $table, $search, $friendlyName, $friendly_titles) {
    // Construction de la requête SQL
    $query = "SELECT * FROM `$table`";
    if (!empty($search)) {
        // Prépare les colonnes pour la recherche
        $columns = $conn->query("SHOW COLUMNS FROM `$table`");
        if (!$columns) {
            error_log("Erreur lors de la récupération des colonnes : " . $conn->error);
            echo "<p>Une erreur est survenue lors de la récupération des données.</p>";
            return;
        }

        $conditions = [];
        while ($column = $columns->fetch_assoc()) {
            $conditions[] = "`" . $column['Field'] . "` LIKE '%" . $conn->real_escape_string($search) . "%'";
        }
        $query .= " WHERE " . implode(" OR ", $conditions);
    }

    // Exécute la requête
    $result = $conn->query($query);

    if (!$result) {
        error_log("Erreur lors de l'exécution de la requête : " . $conn->error);
        echo "<p>Une erreur est survenue lors de la récupération des données.</p>";
        return;
    }

    // Vérifie si des résultats sont disponibles
    if ($result->num_rows > 0) {
        echo "<h2>" . htmlspecialchars($friendlyName) . "</h2>";
        echo "<table border='1' style='width:100%;'>";
        echo "<thead><tr>";

        // Affichage des en-têtes de colonnes
        $fields = $result->fetch_fields();
        foreach ($fields as $index => $field) {
            if ($index === 0) continue; // Ignore la première colonne (ID)
            $columnName = $field->name;
            $friendlyColumnName = isset($friendly_titles[$columnName]) ? $friendly_titles[$columnName] : ucfirst(str_replace('_', ' ', $columnName));
            echo "<th>" . htmlspecialchars($friendlyColumnName) . "</th>";
        }

        echo "</tr></thead><tbody>";

        // Affichage des données
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            $i = 0; // Index de la colonne
            foreach ($row as $key => $value) {
                if ($i === 0) { // Ignore la première colonne (ID)
                    $i++;
                    continue;
                }
                echo "<td>" . htmlspecialchars($value) . "</td>";
                $i++;
            }
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        // Aucun résultat trouvé
        echo "<p>Aucun résultat trouvé pour : " . htmlspecialchars($search) . ".</p>";
    }
}

/**
 * Gère l'ajout de données dans une table.
 */

// ========================
// ROUTAGE DES ACTIONS
// ========================

// Vérification de la validité de l'action
if (!isset($action) || empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Aucune action spécifiée.']);
    exit;
}

// Gestion des actions CRUD
switch ($action) {
    case 'read':
        displayTable($conn, $table, $search, $allowed_tables[$table], $friendly_titles);
        break;

    case 'add':
        addData($conn, $table);
        break;

    case 'edit':
        editData($conn, $table);
        break;

    case 'delete':
        deleteData($conn, $table);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action non reconnue.']);
        break;
}


if ($action === 'read') {
    $query = "SELECT * FROM `$table`";
    if (!empty($search)) {
        $columns = $conn->query("SHOW COLUMNS FROM `$table`");
        $conditions = [];
        while ($column = $columns->fetch_assoc()) {
            $conditions[] = "`" . $column['Field'] . "` LIKE '%" . $conn->real_escape_string($search) . "%'";
        }
        $query .= " WHERE " . implode(" OR ", $conditions);
    }

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<p>Aucun résultat trouvé pour la recherche : <strong>" . htmlspecialchars($search) . "</strong>.</p>";
    }
    exit;
}


// ========================
// FERMETURE DE LA CONNEXION
// ========================
$conn->close();
?>
