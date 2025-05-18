<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log access to help debug
file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Page accessed: ' . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Security check - redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// Verify ticket ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("ID du ticket invalide.");
}

$ticket_id = (int)$_GET['id'];
$error_message = '';
$success_message = '';
$ticket = null;
$previous_responses = [];

// Database configuration directly in this file
$dbConfig = [
    'host' => "hypezaserversql.mysql.database.azure.com",
    'user' => "user",
    'pass' => "HPL1710COMPAq",
    'db' => "users_db",
    'ssl_cert' => __DIR__ . '/DigiCertGlobalRootCA.crt.pem'
];

// Function to connect to database
function dbConnect($config) {
    // Check if SSL cert exists
    if (!file_exists($config['ssl_cert'])) {
        file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - SSL cert not found: ' . $config['ssl_cert'] . "\n", FILE_APPEND);
        throw new Exception("Certificat SSL non trouvé: " . $config['ssl_cert']);
    }

    $conn = mysqli_init();
    if (!$conn) {
        throw new Exception("Initialisation mysqli échouée");
    }

    mysqli_ssl_set($conn, NULL, NULL, $config['ssl_cert'], NULL, NULL);

    try {
        if (!mysqli_real_connect($conn, $config['host'], $config['user'], $config['pass'],
                              $config['db'], 3306, MYSQLI_CLIENT_SSL)) {
            throw new Exception("Connexion échouée: " . mysqli_connect_error());
        }
    } catch (Exception $e) {
        file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - DB Connection error: ' . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }

    // Set charset to ensure proper encoding
    mysqli_set_charset($conn, 'utf8mb4');

    return $conn;
}

// Function to send notification email
function sendNotificationEmail($email, $prenom, $titreTicket) {
    try {
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hypeza.test1@gmail.com';
        $mail->Password = 'kbfa wlby tjpf oqcq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('hypeza.test1@gmail.com', 'HYPEZA Support');
        $mail->addAddress($email, $prenom);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Votre ticket a été répondu";

        // Email body with responsive design
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #000000; padding: 20px; text-align: center;'>
                <h1 style='color: rgb(200,155,60); margin: 0; font-size: 28px;'>HYPEZA Support</h1>
            </div>
            <div style='padding: 20px; background-color: #f9f9f9;'>
                <p style='font-size: 16px; color: #333;'>Bonjour <strong>$prenom</strong>,</p>
                <p style='font-size: 16px; line-height: 1.5;'>
                    Votre ticket intitulé <strong>\"$titreTicket\"</strong> a été répondu par notre équipe.
                </p>
                <p style='font-size: 16px; line-height: 1.5;'>
                    Veuillez vous connecter à votre espace client pour consulter la réponse.
                </p>
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='https://hypeza.tech/connexion2.html' style='background-color: rgb(200,155,60); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Accéder à votre espace client</a>
                </div>
            </div>
            <div style='background-color: #000000; padding: 10px; text-align: center;'>
                <p style='color: #fff; font-size: 12px; margin: 0;'>© 2025 HYPEZA. Tous droits réservés.</p>
            </div>
        </div>";

        return $mail->send();
    } catch (Exception $e) {
        file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Email error: ' . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Main process
try {
    // Connect to database
    $conn = dbConnect($dbConfig);
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - DB Connection successful' . "\n", FILE_APPEND);

    // Get ticket info
    $stmt = $conn->prepare("SELECT t.*, u.firstname, u.lastname, u.email
                           FROM tickets t
                           JOIN users u ON t.user_id = u.id
                           WHERE t.id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();
    $stmt->close();

    if (!$ticket) {
        throw new Exception("Ticket introuvable.");
    }

    // Get previous responses
    $stmt = $conn->prepare("SELECT tr.*, u.firstname, u.lastname
                           FROM ticket_responses tr
                           LEFT JOIN users u ON tr.admin_id = u.id
                           WHERE tr.ticket_id = ?
                           ORDER BY tr.created_at DESC");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $previous_responses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Log form submission
        file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Form submitted' . "\n", FILE_APPEND);

        // Verify CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception("Erreur de sécurité: jeton CSRF invalide.");
        }

        if (empty($_POST['response'])) {
            throw new Exception("La réponse ne peut pas être vide.");
        }

        $response = trim($_POST['response']);
        $admin_id = $_SESSION['user_id'];
        $status = isset($_POST['status']) ? $_POST['status'] : $ticket['status'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert response
            $stmt = $conn->prepare("INSERT INTO ticket_responses (ticket_id, admin_id, response, created_at)
                                  VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $ticket_id, $admin_id, $response);
            if (!$stmt->execute()) {
                throw new Exception("Erreur d'enregistrement de la réponse: " . $conn->error);
            }
            $stmt->close();

            // Update ticket status
// Update ticket status without using updated_at
// In respond_ticket.php - around line 195, update the ticket update query
$stmt = $conn->prepare("UPDATE tickets SET status = ?, has_new_response = 1 WHERE id = ?");
$stmt->bind_param("si", $status, $ticket_id);
            if (!$stmt->execute()) {
                throw new Exception("Erreur de mise à jour du statut: " . $conn->error);
            }
            $stmt->close();

            // Send notification email
            sendNotificationEmail($ticket['email'], $ticket['firstname'], $ticket['title']);

            $conn->commit();
            file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Response saved successfully' . "\n", FILE_APPEND);

            // Success message
            $success_message = "Réponse envoyée avec succès.";

            // Refresh ticket data
            $stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
            $stmt->bind_param("i", $ticket_id);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Refresh responses
            $stmt = $conn->prepare("SELECT tr.*, u.firstname, u.lastname
                                  FROM ticket_responses tr
                                  LEFT JOIN users u ON tr.admin_id = u.id
                                  WHERE tr.ticket_id = ?
                                  ORDER BY tr.created_at DESC");
            $stmt->bind_param("i", $ticket_id);
            $stmt->execute();
            $previous_responses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

        } catch (Exception $e) {
            $conn->rollback();
            file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Transaction error: ' . $e->getMessage() . "\n", FILE_APPEND);
            $error_message = "Erreur: " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Error: ' . $e->getMessage() . "\n", FILE_APPEND);
    $error_message = "Erreur: " . $e->getMessage();
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre au Ticket #<?= htmlspecialchars($ticket_id ?? '') ?></title>
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: var(--primary-color);
            text-align: center;
        }

        .ticket-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .ticket-info p {
            margin: 8px 0;
        }

        .ticket-info strong {
            color: var(--dark-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--dark-color);
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn-primary:hover {
            background-color: rgb(180,140,50);
        }

        .btn-secondary {
            background-color: #777;
            color: var(--light-color);
        }

        .btn-secondary:hover {
            background-color: #555;
        }

        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #721c24;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #155724;
        }

        .previous-responses {
            margin-top: 30px;
        }

        .response-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #666;
        }

        .response-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .response-content {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 15px;
            }

            .button-group {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Répondre au Ticket</h1>

        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if ($ticket): ?>
            <div class="ticket-info">
                <p><strong>Ticket #:</strong> <?= htmlspecialchars($ticket['id']) ?></p>
                <p><strong>Titre:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                <p><strong>Client:</strong> <?= htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($ticket['email']) ?></p>
                <p><strong>Statut actuel:</strong> <span style="font-weight:bold;color:<?= $ticket['status'] === 'résolu' ? 'green' : ($ticket['status'] === 'fermé' ? 'red' : 'orange') ?>"><?= htmlspecialchars($ticket['status']) ?></span></p>
                <p><strong>Date de création:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="status">Mettre à jour le statut:</label>
                    <select name="status" id="status">
                        <option value="ouvert" <?= ($ticket['status'] === 'ouvert') ? 'selected' : '' ?>>Ouvert</option>
                        <option value="en cours" <?= ($ticket['status'] === 'en cours') ? 'selected' : '' ?>>En cours</option>
                        <option value="résolu" <?= ($ticket['status'] === 'résolu') ? 'selected' : '' ?>>Résolu</option>
                        <option value="fermé" <?= ($ticket['status'] === 'fermé') ? 'selected' : '' ?>>Fermé</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="response">Votre réponse:</label>
                    <textarea name="response" id="response" required placeholder="Entrez votre réponse ici..."></textarea>
                </div>

                <div class="button-group">
                    <a href="admin_tickets.php" class="btn btn-secondary">Retour à la liste</a>
                    <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
                </div>
            </form>

            <?php if (!empty($previous_responses)): ?>
                <div class="previous-responses">
                    <h2>Historique des réponses</h2>
                    <?php foreach ($previous_responses as $response): ?>
                        <div class="response-item">
                            <div class="response-meta">
                                <strong>Répondu par:</strong> <?= htmlspecialchars($response['firstname'] . ' ' . $response['lastname']) ?>
                                <strong>Le:</strong> <?= htmlspecialchars($response['created_at']) ?>
                            </div>
                            <div class="response-content">
                                <?= nl2br(htmlspecialchars($response['response'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p>Ticket introuvable. <a href="admin_tickets.php">Retour à la liste des tickets</a></p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-resize textarea as content grows
        const textarea = document.getElementById('response');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const response = document.getElementById('response').value.trim();
            if (response === '') {
                e.preventDefault();
                alert('Veuillez entrer une réponse avant de soumettre.');
            }
        });
    });
    </script>
</body>
</html>