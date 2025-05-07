<?php
require_once 'config.php';

// Vérifier si un token est présent
if (!isset($_GET['token'])) {
    header("Location: connexion2.html");
    exit();
}

$token = $_GET['token'];

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            // Vérifier si le token est valide et non expiré
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expiration_date > NOW() AND used = 0");
            $stmt->execute([$token]);

            if ($row = $stmt->fetch()) {
                $email = $row['email'];
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Mettre à jour le mot de passe
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashedPassword, $email]);

                // Marquer le token comme utilisé
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);

                header("Location: connexion2.html?status=password_updated");
                exit();
            } else {
                $error = "Le lien de réinitialisation est invalide ou a expiré.";
            }
        } catch(PDOException $e) {
            $error = "Une erreur est survenue.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe - HYPEZA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: rgb(200,155,60);
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #222;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Réinitialisation du mot de passe</h2>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</div>
</body>
</html>