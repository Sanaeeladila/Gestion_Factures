<?php
    session_start();
    include 'config/db_config.php';

    if(isset($_POST['submit'])) {
        $role = $_POST['role'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($role === "user") {
            $sql = "SELECT id_client, nom_client FROM client WHERE nom_client = '$username' AND password_client = '$password'";
        } elseif ($role === "admin") {
            $sql = "SELECT id_admin, nom FROM fournisseur WHERE nom = '$username' AND password = '$password'";
        } else {
            echo "Invalid role";
            exit(); 
        }

        $result = mysqli_query($conn, $sql);

        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Ajouter les identifiants id_client ou id_admin à la session en fonction du rôle
            if ($role === "user") {
                $_SESSION['id_client'] = $row['id_client'];
            } elseif ($role === "admin") {
                $_SESSION['id_admin'] = $row['id_admin'];
            }

            $_SESSION['username'] = $row['nom_client']; 
            $_SESSION['role'] = $role;

            if ($role === "user") {
                header("Location: client_dashboard/client_dash.php");
                exit();
            } elseif ($role === "admin") {
                header("Location: admin_dashboard/dash.php");
                exit();
            }
        } else {
            $error_message = "Invalid username or password";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Form</title>
        <script src="https://kit.fontawesome.com/1e94604817.js" crossorigin="anonymous"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #0c4563;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            p {
                color: #ffffff;
                text-align: center;
                font-size: 50px;
                margin-bottom: 30px;
                font-family: "Poiret One", sans-serif;
            }
            p.error-message {
                color: red;
                margin-bottom: 15px;
                font-size: 16px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                color: #ffffff;
                font-size: 20px;
                font-family: "Poiret One", sans-serif;
            }
            select, input[type="text"], input[type="password"], input[type="submit"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 2px solid #ffffff;
                border-radius: 3px;
                box-sizing: border-box;
                background-color: #0c4563;
                color: #BBC4CB;
                font-size: 16px;
            }
            input[type="submit"] {
                background-color: #0c4563;
                color: #fff;
                cursor: pointer;
            } 
            .log{
                color: #ffffff;
                text-align: center;
                font-size: 20px;
                margin-bottom: 20px;
                cursor: pointer;
            }
            #role option {
                font-size: 16px; 
                color: #333; 
                background-color: #fff; 
                padding: 5px 10px; 
            }
            #role {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 2px solid #ffffff;
                border-radius: 3px;
                box-sizing: border-box;
                background-color: #0c4563;
                color: #BBC4CB;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <form action="login.php" method="POST" enctype="multipart/form-data" autocomplete="off">
        
            <p>WELCOME BACK</p>
            <label for="role">You are a</label>
            <select name="role" id="role">
                <option value="user">Client</option>
                <option value="admin">Fournisseur</option>
            </select>
            <label for="name">Username</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <?php if(isset($error_message)) { ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php } ?>
            <!--<button type="submit" ><i class="fa-solid fa-lock" style="color: #f7f7f7;"></i> LOG IN </button> -->
            <input type="submit" name="submit" value="LOG IN">
        </form>
    </body>
</html>
