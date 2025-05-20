<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur manquant']);
    exit();
}

// Connexion à la base de données
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

try {
    $conn = mysqli_init();
    $ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';
    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Erreur de connexion : " . mysqli_connect_error());
    }

    $userId = mysqli_real_escape_string($conn, $_GET['id']);

    // Récupérer les informations de l'utilisateur
    $query = "SELECT u.*, 
              (SELECT COUNT(*) FROM purchases WHERE user_id = u.id) as orders_count,
              (SELECT COUNT(*) FROM tickets WHERE user_id = u.id) as tickets_count
              FROM users u 
              WHERE u.id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userData = mysqli_fetch_assoc($result);

    if (!$userData) {
        throw new Exception('Utilisateur non trouvé');
    }

    // Récupérer les activités récentes (à adapter selon votre structure de base de données)
    $activities = [];

    // Exemple : Récupérer les dernières commandes
    $query = "SELECT creation_date as date, 
              CONCAT('Commande #', order_number, ' - ', total, '€') as description 
              FROM purchases 
              WHERE user_id = ? 
              ORDER BY creation_date DESC LIMIT 5";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }

    // Récupérer les derniers tickets
    $query = "SELECT created_at as date,
              CONCAT('Ticket: ', title) as description
              FROM tickets 
              WHERE user_id = ?
              ORDER BY created_at DESC LIMIT 5";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }

    // Trier les activités par date
    usort($activities, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    $userData['activities'] = array_slice($activities, 0, 5); // Garder les 5 dernières activités

    echo json_encode($userData);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>