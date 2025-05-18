<?php
session_start();
require 'vendor/autoload.php'; // Charger PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérification du rôle administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// Vérification de l'ID du ticket
if (!isset($_GET['id'])) {
    die("ID du ticket manquant.");
}

$ticket_id = intval($_GET['id']);

// Database connection parameters
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate
$ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

// Check if certificate exists
if (!file_exists($ssl_cert)) {
    die("Erreur: Certificat SSL non trouvé à l'emplacement: " . $ssl_cert);
}

// Variable to track errors
$error_message = '';
$success_message = '';

try {
    // Create connection with SSL
    $conn = mysqli_init();

    if (!$conn) {
        throw new Exception("mysqli_init failed");
    }

    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Récupération des informations du ticket
    $stmt = mysqli_prepare($conn, "SELECT t.*, u.firstname, u.lastname, u.email FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $ticket_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ticket = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$ticket) {
        throw new Exception("Ticket introuvable.");
    }

    // Fonction pour envoyer un e-mail avec PHPMailer
    function envoyerNotificationEmail($email, $prenom, $titreTicket) {
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hypeza.test1@gmail.com';
            $mail->Password = 'kbfa wlby tjpf oqcq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinataires
            $mail->setFrom('hypeza.test1@gmail.com', 'HYPEZA Support');
            $mail->addAddress($email, $prenom);

            // Contenu de l'e-mail
            $mail->isHTML(true);
            $mail->Subject = "Votre ticket a été répondu";

            // Corps de l'e-mail stylisé
            $mail->Body = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <!-- En-tête -->
    <div style='background-color: #000000; padding: 20px; text-align: center;'>
        <h1 style='color: rgb(200,155,60); margin: 0; font-size: 28px;'>HYPEZA Support</h1>
    </div>

    <!-- Contenu principal -->
    <div style='padding: 20px; background-color: #f9f9f9;'>
        <p style='font-size: 16px; color: #333;'>Bonjour <strong>$prenom</strong>,</p>
        <p style='font-size: 16px; line-height: 1.5;'>
            Votre ticket intitulé <strong>\"$titreTicket\"</strong> a été répondu par notre équipe.
        </p>
        <p style='font-size: 16px; line-height: 1.5;'>
            Veuillez vous connecter à votre espace client pour consulter la réponse.
        </p>
        <div style='text-align: center; margin: 20px 0;'>
            <a href='http://localhost/connexion2.html' style='background-color: rgb(200,155,60); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Accéder à votre espace client</a>
        </div>
        <p style='font-size: 14px; color: #666;'>
            Si vous avez des questions, n'hésitez pas à nous contacter.
        </p>
    </div>

    <!-- Pied de page -->
    <div style='background-color: #000000; padding: 10px; text-align: center;'>
        <p style='color: #fff; font-size: 12px; margin: 0;'>© 2025 HYPEZA. Tous droits réservés.</p>
    </div>
</div>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo);
            return false;
        }
    }

    // Traitement du formulaire de réponse
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response'])) {
        $response = trim($_POST['response']);
        $admin_id = $_SESSION['user_id'];
        $status = isset($_POST['status']) ? $_POST['status'] : $ticket['status'];

        // Vérification que la réponse n'est pas vide
        if (empty($response)) {
            throw new Exception("La réponse ne peut pas être vide.");
        }

        // Début d'une transaction
        mysqli_begin_transaction($conn);

        try {
            // Insertion de la réponse dans la table ticket_responses
            $stmt_response = mysqli_prepare($conn, "INSERT INTO ticket_responses (ticket_id, admin_id, response, created_at) VALUES (?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt_response, "iis", $ticket_id, $admin_id, $response);
            $response_result = mysqli_stmt_execute($stmt_response);
            mysqli_stmt_close($stmt_response);

            if (!$response_result) {
                throw new Exception("Erreur lors de l'enregistrement de la réponse: " . mysqli_error($conn));
            }

            // Mise à jour du statut du ticket
            $stmt_status = mysqli_prepare($conn, "UPDATE tickets SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_status, "si", $status, $ticket_id);
            $status_result = mysqli_stmt_execute($stmt_status);
            mysqli_stmt_close($stmt_status);

            if (!$status_result) {
                throw new Exception("Erreur lors de la mise à jour du statut: " . mysqli_error($conn));
            }

            // Envoi de l'e-mail de notification
            $email_result = envoyerNotificationEmail($ticket['email'], $ticket['firstname'], $ticket['title']);

            if (!$email_result) {
                // On continue même si l'envoi d'e-mail échoue, mais on log l'erreur
                error_log("Erreur d'envoi d'e-mail pour le ticket #{$ticket_id}");
            }

            // Valider la transaction
            mysqli_commit($conn);

            $success_message = "Réponse envoyée avec succès et ticket mis à jour.";

            // Redirection après succès
            header("Location: admin_tickets.php?success=1");
            exit();

        } catch (Exception $e) {
            // Annulation de la transaction en cas d'erreur
            mysqli_rollback($conn);
            $error_message = "Erreur: " . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $error_message = "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre au Ticket</title>
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
            padding: 20px;
            background-color: #f8f8f8;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
        }

        .ticket-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .ticket-info p {
            margin: 8px 0;
        }

        .ticket-info strong {
            color: var(--primary-color);
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 16px;
        }

        textarea {
            height: 150px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
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

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Répondre au Ticket</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (isset($ticket)): ?>
        <div class="ticket-info">
            <p><strong>Ticket #:</strong> <?= htmlspecialchars($ticket['id']) ?></p>
            <p><strong>Titre:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
            <p><strong>Client:</strong> <?= htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($ticket['email']) ?></p>
            <p><strong>Statut actuel:</strong> <?= htmlspecialchars($ticket['status']) ?></p>
            <p><strong>Date de création:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
        </div>

        <form method="POST" action="respond_ticket.php?id=<?= $ticket_id ?>">
            <div class="form-group">
                <label for="status">Mettre à jour le statut:</label>
                <select name="status" id="status">
                    <option value="ouvert" <?= $ticket['status'] === 'ouvert' ? 'selected' : '' ?>>Ouvert</option>
                    <option value="en cours" <?= $ticket['status'] === 'en cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="résolu" <?= $ticket['status'] === 'résolu' ? 'selected' : '' ?>>Résolu</option>
                    <option value="fermé" <?= $ticket['status'] === 'fermé' ? 'selected' : '' ?>>Fermé</option>
                </select>
            </div>

            <div class="form-group">
                <label for="response">Votre réponse:</label>
                <textarea name="response" id="response" required></textarea>
            </div>

            <div class="button-group">
                <a href="admin_tickets.php" class="btn btn-secondary">Retour à la liste</a>
                <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>