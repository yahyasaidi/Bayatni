<?php
session_start();
require_once __DIR__.'/../../config/config.php';
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

if (isset($_SESSION['user_id'])) {
  header('Location: ../views/profile.php');
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email est invalide.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id, fullname, firstname , password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['firstname'];
                    $_SESSION['loggedin'] = true;
                    header("Location: /development/public/index.php");
                    exit();
                } else {
                    $errors[] = "Mot de passe incorrect.";
                }
            } else {
                $errors[] = "Aucun compte trouvé avec cet email.";
            }

            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Une erreur est survenue lors de la connexion.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        header("Location: signin.php");

        exit();
    }

}
include __DIR__.'/../../includes/header.php';
$conn->close();
?>




<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Bayatni.tn</title>
    <link rel="stylesheet" href="/development/public/css/index.css">
    <link rel="stylesheet" href="/development/public/css/signin.css"> 
    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script> 
    <!-- Bootstrap CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous"> 
    <!-- Google Fonts CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"></head>
<body style="overflow: hidden;">


  <div id="background-container">
    <div class="bg-layer" id="bg1"></div>
    <div class="bg-layer" id="bg2"></div>
  </div>
        <main>
          <section class="auth-card">
          <form class="signinForm" method="POST" action="">
              <div class="inputForm" style="padding-left:15px">
                <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg"><g id="Layer_3"><path d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"></path></g></svg>
                <input type="text" class="input" name="email"  placeholder="Enter your Email">
              </div>
              <div class="inputForm" style="padding-left:15px">
                <svg height="20" viewBox="-64 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path><path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"></path></svg>
                <input type="password" class="input" name="password" placeholder="Enter your Password">
                </div>
                <div id="errors">
                  <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                      <p style="color: red;  margin: 0.25rem 0; font-size: 0.9rem; font-weight: 500; font-family: 'Poppins', sans-serif; font-weight: 400; font-style: normal;"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>  
                <div class="inputSubmit">
                  <button class="button-submit">Sign In</button>
                </div>  
                <div class="remember-container">
                  <div class="remember-left">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember me</label>
                  </div>
                  <a href="forgotpwd.php"><span class="forgot-password">Forgot password?</span></a>
                </div>          
              
                <p class="p">Don't have an account? <a href="signup.php"><span class="span">Sign Up</span></p></a>
            </form>
          </section>
        </main>
  <script src="/development/public/js/bg.js"></script>
</body>
</html>
