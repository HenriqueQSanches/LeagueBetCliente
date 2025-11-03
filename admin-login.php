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
            color: #8B1A1A;
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
            border-color: #8B1A1A;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #8B1A1A;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #6B0A0A;
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
            background: #8B1A1A;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">WS</div>
            <h1>Wolf Sistemas</h1>
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

