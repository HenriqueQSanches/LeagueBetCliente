<?php
session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Conectar ao banco
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar usuário
        $stmt = $pdo->prepare("SELECT * FROM sis_users WHERE login = :login LIMIT 1");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verificar senha (SHA512)
            $senha_hash = hash('sha512', $senha);
            
            if ($user['senha'] === $senha_hash) {
                // Login bem-sucedido
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_nome'] = $user['nome'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_login'] = $user['login'];
                $_SESSION['admin_credito'] = $user['credito'];
                
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = 'Usuário ou senha incorretos!';
            }
        } else {
            $error = 'Usuário ou senha incorretos!';
        }
        
    } catch (PDOException $e) {
        $error = 'Erro ao conectar ao banco de dados!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #ff9800;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff9800;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #ff9800;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #f57c00;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #fcc;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #ff9800;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
        }
        
        /* ===== RESPONSIVO MOBILE - LOGIN ===== */
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .login-container {
                padding: 30px 25px;
                max-width: 100%;
            }
            
            .logo {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-header p {
                font-size: 13px;
            }
            
            .form-group label {
                font-size: 13px;
            }
            
            .form-group input {
                font-size: 15px;
                padding: 12px;
            }
            
            .btn-login {
                font-size: 15px;
                padding: 14px;
            }
            
            .error-message {
                font-size: 13px;
                padding: 10px;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 25px 20px;
            }
            
            .logo {
                width: 60px;
                height: 60px;
                font-size: 28px;
                margin-bottom: 15px;
            }
            
            .login-header h1 {
                font-size: 22px;
            }
            
            .login-header p {
                font-size: 12px;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-group label {
                font-size: 12px;
            }
            
            .form-group input {
                font-size: 14px;
                padding: 11px;
            }
            
            .btn-login {
                font-size: 14px;
                padding: 13px;
            }
        }
        
        @media (max-width: 400px) {
            .login-container {
                padding: 20px 15px;
            }
            
            .logo {
                width: 55px;
                height: 55px;
                font-size: 24px;
            }
            
            .login-header h1 {
                font-size: 20px;
            }
            
            .form-group input {
                font-size: 13px;
                padding: 10px;
            }
            
            .btn-login {
                font-size: 13px;
                padding: 12px;
            }
        }
        
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">LB</div>
            <h1>LeagueBet</h1>
            <p>Painel Administrativo</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="login">Usuário</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn-login">ENTRAR</button>
        </form>
    </div>
</body>
</html>

