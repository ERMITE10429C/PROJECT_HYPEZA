<?php
require_once 'config.php';
require_once 'mail_config.php'; // Ajout de la configuration mail

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Supprimer les anciens tokens
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Insérer le nouveau token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiration_date) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expiration]);

            // Préparation de l'email
            $resetLink = "http://localhost/new_password.php?token=" . $token;
            $subject = "Réinitialisation de votre mot de passe HYPEZA";
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #000000; padding: 20px; text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: rgb(200,155,60); margin: 0; font-size: 32px; letter-spacing: 2px;'>HYPEZA</h1>
                </div>

                <div style='padding: 20px; color: #333333;'>
                    <p style='font-size: 16px; line-height: 1.5;'>Bonjour,</p>
                    <p style='font-size: 16px; line-height: 1.5;'>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                    <p style='font-size: 16px; line-height: 1.5;'>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' style='background-color: #222222; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Réinitialiser mon mot de passe</a>
                    </div>
                    
                    <p style='font-size: 14px; color: #666666;'>Ce lien expirera dans 24 heures.</p>
                    <p style='font-size: 14px; color: #666666;'>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </div>

                <div style='margin-top: 40px; padding: 20px; background-color: #f8f8f8; text-align: center; border-radius: 5px;'>
                    <p style='margin: 0; color: #666666; font-size: 14px;'>
                        Cordialement,<br>
                        <strong style='color: rgb(200,155,60);'>L'équipe HYPEZA</strong>
                    </p>
                </div>
            </div>";

            // Envoi de l'email avec la fonction du mail_config.php
            if (sendMail($email, $subject, $message)) {
                header("Location: forgot_password.html?status=success");
            } else {
                header("Location: forgot_password.html?error=mail_error");
            }
        } else {
            header("Location: forgot_password.html?error=email_not_found");
        }
    } catch(PDOException $e) {
        header("Location: forgot_password.html?error=db_error");
    }
    exit();
}
?>