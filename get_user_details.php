<?php
header('Content-Type: application/json');
session_start();

// Activation des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit();
}

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur invalide']);
    exit();
}

// Connexion à la base de données
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

try {
    $conn = mysqli_init();

    if (!$conn) {
        throw new Exception("Échec de l'initialisation MySQL");
    }

    // Configuration SSL
    $ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';
    if (!file_exists($ssl_cert)) {
        throw new Exception("Certificat SSL non trouvé: " . $ssl_cert);
    }

    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Erreur de connexion MySQL: " . mysqli_connect_error());
    }

    // Récupération des données utilisateur
    $userId = intval($_GET['id']);
    $query = "SELECT 
                u.*, 
                COALESCE((SELECT COUNT(*) FROM purchases WHERE user_id = u.id), 0) as orders_count,
                COALESCE((SELECT COUNT(*) FROM tickets WHERE user_id = u.id), 0) as tickets_count
              FROM users u 
              WHERE u.id = ?";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erreur d'exécution de la requête: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("Erreur de récupération du résultat: " . mysqli_error($conn));
    }

    $userData = mysqli_fetch_assoc($result);
    if (!$userData) {
        throw new Exception("Utilisateur non trouvé");
    }

    // Récupération des activités
    $activities = [];

    // Commandes récentes
    $query = "SELECT 
                creation_date as date,
                CONCAT('Commande #', order_number, ' - ', total, '€') as description
              FROM purchases 
              WHERE user_id = ? 
              ORDER BY creation_date DESC 
              LIMIT 5";

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = $row;
            }
        }
    }

    // Tickets récents
    $query = "SELECT 
                created_at as date,
                CONCAT('Ticket: ', title) as description
              FROM tickets 
              WHERE user_id = ?
              ORDER BY created_at DESC 
              LIMIT 5";

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = $row;
            }
        }
    }

    // Tri des activités par date
    if (!empty($activities)) {
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $activities = array_slice($activities, 0, 5);
    }

    $userData['activities'] = $activities;

    // Envoi de la réponse
    echo json_encode($userData);

} catch (Exception $e) {
    error_log("Erreur dans get_user_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur: ' . $e->getMessage(),
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) {
        mysqli_close($conn);
    }
}