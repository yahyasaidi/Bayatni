<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (isset($_SESSION['user_id'])) {
  header('Location: ../views/profile.php');
  exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../includes/PHPMailer-master/src/Exception.php';
require __DIR__ . '/../../includes/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../../includes/PHPMailer-master/src/SMTP.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['recover']) && !empty($_POST['email'])) {
        $email = trim($_POST['email']);
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();
            $stmt->close();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'contact.bayatni@gmail.com';
                $mail->Password = 'dsqhfhkedctrixnj'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('contact.bayatni@gmail.com', 'Bayatni');
                $mail->addAddress($email);

                $resetLink = 'http://localhost/development/app/auth/resetpwd.php?token=' . urlencode($token);

                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation du mot de passe';
                $mail->Body = "
                    Bonjour,<br><br>
                    Pour reinitialiser votre mot de passe, cliquez sur le lien suivant :<br>
                    <a href=\"$resetLink\">Reinitialiser mon mot de passe</a><br><br>
                    Ce lien expirera dans une heure.
                ";

                $mail->send();
                $_SESSION['success'] = "Un lien a été envoyé à votre adresse e-mail.";
            } catch (Exception $e) {
                $errors[] = "Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo;
            }
        } else {
            $errors[] = "Adresse e-mail inconnue.";
        }
    } else {
        $errors[] = "Veuillez entrer une adresse e-mail.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Réinitialisation - Bayatni</title>
  <link rel="stylesheet" href="/development/public/css/index.css">
  <link rel="stylesheet" href="/development/public/css/pwd.css"> 
  <script src="https://cdn.tailwindcss.com"></script> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body style="overflow: hidden;">
  <div id="background-container">
    <div class="bg-layer" id="bg1"></div>
    <div class="bg-layer" id="bg2"></div>
  </div>
  <?php include __DIR__.'/../../includes/header.php'; ?>
  <main>
    <!-- Message de Erreur -->
    <?php if (!empty($errors)): ?>
      <div id="errors">
        <?php foreach ($errors as $error): ?>
          <div style="position:absolute; left:42%; text-align:center;" class="p-4 mt-10 mb-4 text-sm text-red-700 bg-red-100 rounded-lg"> <?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Message de succès -->
    <?php if (!empty($_SESSION['success'])): ?>
      <div style="position:absolute; left:37.5%; text-align:center;" class="p-4 mt-10 mb-5 text-sm text-green-700 bg-green-100 rounded-lg"> <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Formulaire de réinitialisation du mot de passe -->

    <section class="auth-card">
      <form class="form" method="POST" action="">
        <h1 class="fma" style="">Réinitialisation du </br> Mot de Passe</h1>
        
        <div class="inputForm mt-3"> 
          <svg height="20" viewBox="-64 0 512 512" style="margin-left:10px;" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path><path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"></path></svg>
          <input type="email" class="input" name="email" placeholder="Entrez votre adresse e-mail" required>
        </div>

        <div class="inputSubmit">
          <button class="button-submit" type="submit" name="recover">Envoyer le lien</button>
        </div>

        </p>
      </form>
    </section>
  </main>

  <script src="/development/public/js/bg.js"></script>
</body>
</html>
