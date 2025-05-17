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
            <a href='localhost/connexion2.html' style='background-color: rgb(200,155,60); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Accéder à votre espace client</a>
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
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}");
            echo "Erreur : {$mail->ErrorInfo}";
        }
    }

    // Gestion de la réponse
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = $_POST['response'];
        $admin_id = $_SESSION['user_id'];

        // Enregistrer la réponse dans la base de données
        $stmt = mysqli_prepare($conn, "INSERT INTO ticket_responses (ticket_id, admin_id, response, created_at) VALUES (?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "iis", $ticket_id, $admin_id, $response);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Mettre à jour le statut du ticket
        $stmt = mysqli_prepare($conn, "UPDATE tickets SET status = 'Répondu', is_notified = 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Envoyer une notification par e-mail
        envoyerNotificationEmail($ticket['email'], $ticket['firstname'], $ticket['title']);

        mysqli_close($conn);
        header("Location: admin_tickets.php?status=responded");
        exit();
    }

    // Close connection if not in POST request
    mysqli_close($conn);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre au Ticket</title>
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
            --background-color: #f5f5f5;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }

        .ticket-details {
            margin-bottom: 20px;
        }

        .ticket-details p {
            margin: 10px 0;
            font-size: 1rem;
            color: var(--dark-color);
        }

        .ticket-details strong {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--dark-color);
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }
        .btn-back {
            background-color: #6c757d;
            margin-top: 20px;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Répondre au Ticket</h1>
    <div class="ticket-details">
        <p><strong>ID :</strong> <?= htmlspecialchars($ticket['id']) ?></p>
        <p><strong>Utilisateur :</strong> <?= htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']) ?></p>
        <p><strong>Titre :</strong> <?= htmlspecialchars($ticket['title']) ?></p>
        <p><strong>Description :</strong> <?= htmlspecialchars($ticket['description']) ?></p>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="response">Votre réponse</label>
            <textarea id="response" name="response" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn">Envoyer la réponse</button>
    </form>
    <a href="admin_tickets.php" class="btn btn-back">Retour à la gestion des tickets</a>
</div>
</body>
</html>