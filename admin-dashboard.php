<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Conectar ao banco para pegar estatísticas
try {
    $pdo = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar estatísticas
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM sis_users")->fetchColumn();
    $total_apostas = $pdo->query("SELECT COUNT(*) FROM sis_apostas")->fetchColumn();
    $total_depositos = $pdo->query("SELECT COUNT(*) FROM sis_depositos")->fetchColumn();
    $total_saques = $pdo->query("SELECT COUNT(*) FROM sis_saques")->fetchColumn();
    
    // Somas
    $soma_entradas = $pdo->query("SELECT COALESCE(SUM(valor), 0) FROM sis_depositos WHERE status = 1")->fetchColumn();
    $soma_saidas = $pdo->query("SELECT COALESCE(SUM(valor), 0) FROM sis_saques WHERE status = 1")->fetchColumn();
    
} catch (PDOException $e) {
    $total_usuarios = 0;
    $total_apostas = 0;
    $total_depositos = 0;
    $total_saques = 0;
    $soma_entradas = 0;
    $soma_saidas = 0;
}

$admin_nome = $_SESSION['admin_nome'] ?? 'Administrador';
$admin_credito = $_SESSION['admin_credito'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wolf Sistemas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        /* HEADER */
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: 600;
        }
        
        .header-right {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .header-info {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .header-info i {
            font-size: 16px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: #8B1A1A;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* LAYOUT */
        .container {
            display: flex;
            min-height: calc(100vh - 65px);
        }
        
        /* SIDEBAR */
        .sidebar {
            width: 230px;
            background: #8B1A1A;
            color: white;
            overflow-y: auto;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
            font-size: 14px;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(0,0,0,0.2);
        }
        
        .sidebar-menu i {
            width: 20px;
            font-size: 16px;
        }
        
        .sidebar-menu .submenu-toggle {
            cursor: pointer;
            position: relative;
        }
        
        .sidebar-menu .submenu-toggle::after {
            content: '▶';
            position: absolute;
            right: 20px;
            font-size: 10px;
            transition: transform 0.3s;
        }
        
        /* CONTENT */
        .content {
            flex: 1;
            padding: 30px;
        }
        
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        /* CARDS DE ESTATÍSTICAS */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 25px;
            border-radius: 8px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 150px;
            height: 150px;
            opacity: 0.2;
            font-size: 100px;
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stat-card.red {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        
        .stat-card.yellow {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }
        
        .stat-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* GRÁFICOS */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .chart-box h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }
        
        /* TABELA */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .table-container h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        table tr:hover {
            background: #f9f9f9;
        }
        
        /* FOOTER */
        .footer {
            text-align: center;
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 13px;
            color: #666;
        }
        
        .logout-btn {
            background: #d32f2f;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #b71c1c;
        }
        
        .date-filter {
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <h1>Wolf Sistemas</h1>
        <div class="header-right">
            <div class="header-info">
                <i class="fas fa-wallet"></i>
                <span>Saldo</span>
                <strong>R$ <?= number_format($admin_credito, 2, ',', '.') ?></strong>
            </div>
            <div class="header-info">
                <i class="fas fa-shield-alt"></i>
                <span>Permissão</span>
                <strong>Administrador</strong>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <strong><?= htmlspecialchars($admin_nome) ?></strong><br>
                    <small><?= date('d/m/Y H:i') ?></small>
                </div>
            </div>
            <a href="admin-logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
    
    <!-- CONTAINER -->
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> RETORNAR AO SITE</a></li>
                <li><a href="#" class="active"><i class="fas fa-chart-line"></i> DASHBOARD</a></li>
                <li><a href="#"><i class="fas fa-gem"></i> PLANO</a></li>
                <li><a href="#"><i class="fas fa-star"></i> NOVIDADES</a></li>
                <li><a href="#"><i class="fas fa-gamepad"></i> JOGOS MAIS JOGADOS</a></li>
                <li><a href="#" class="submenu-toggle"><i class="fas fa-cog"></i> ADMINISTRAÇÃO</a></li>
                <li><a href="#" class="submenu-toggle"><i class="fas fa-dollar-sign"></i> FINANCEIRO</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> RELATÓRIOS</a></li>
                <li><a href="#"><i class="fas fa-book"></i> JOGOS MANUAIS</a></li>
                <li><a href="#"><i class="fas fa-ticket-alt"></i> BILHETES</a></li>
                <li><a href="#"><i class="fas fa-times-circle"></i> CANCELAR BILHETE</a></li>
                <li><a href="#"><i class="fas fa-trophy"></i> SORTEIOS</a></li>
                <li><a href="#"><i class="fas fa-check-circle"></i> CONFERIR BILHETE</a></li>
                <li><a href="#"><i class="fas fa-credit-card"></i> CARTÕES PRÉ PAGOS</a></li>
                <li><a href="#"><i class="fas fa-flag"></i> BILHETE PARA BANNER</a></li>
                <li><a href="#"><i class="fas fa-volume-up"></i> LANÇAR RESULTADOS</a></li>
                <li><a href="#"><i class="fas fa-headset"></i> AUDITORIA</a></li>
                <li><a href="#"><i class="fas fa-layer-group"></i> ACUMULADÃO</a></li>
                <li><a href="#"><i class="fas fa-sliders-h"></i> CONTROLE DE TAXAS</a></li>
                <li><a href="#" class="submenu-toggle"><i class="fas fa-percentage"></i> MANUSEIO DE COTAÇÕES</a></li>
                <li><a href="#"><i class="fas fa-plus-circle"></i> ADICIONAR COTAÇÕES NOS JOGOS</a></li>
                <li><a href="#"><i class="fas fa-exclamation-triangle"></i> GERENCIAMENTO DE RISCO</a></li>
                <li><a href="#"><i class="fas fa-history"></i> HISTÓRICO DE LOGINS</a></li>
                <li><a href="#"><i class="fas fa-map-marked-alt"></i> MAPA DE APOSTAS</a></li>
                <li><a href="#"><i class="fas fa-ruler"></i> REGRAS</a></li>
                <li><a href="#" class="submenu-toggle"><i class="fas fa-money-bill-wave"></i> SALDOS</a></li>
                <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> SAIR</a></li>
            </ul>
        </div>
        
        <!-- CONTENT -->
        <div class="content">
            <!-- ALERTA -->
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                Altere sua senha de administração, <a href="#" style="color: #856404; font-weight: bold;">clique aqui</a>.
            </div>
            
            <!-- TÍTULO -->
            <h2 class="page-title">DASHBOARD</h2>
            
            <!-- FILTRO DE DATA -->
            <div style="margin-bottom: 20px;">
                <label><strong>Data</strong></label><br>
                <select class="date-filter">
                    <option>Segunda-feira</option>
                    <option>Terça-feira</option>
                    <option>Quarta-feira</option>
                    <option>Quinta-feira</option>
                    <option>Sexta-feira</option>
                    <option>Sábado</option>
                    <option>Domingo</option>
                </select>
            </div>
            
            <!-- CARDS DE ESTATÍSTICAS -->
            <div class="stats-container">
                <div class="stat-card green">
                    <h3>R$ <?= number_format($soma_entradas, 2, ',', '.') ?></h3>
                    <p>Entradas</p>
                </div>
                <div class="stat-card red">
                    <h3>R$ <?= number_format($soma_saidas, 2, ',', '.') ?></h3>
                    <p>Saídas</p>
                </div>
                <div class="stat-card yellow">
                    <h3><?= $total_apostas ?></h3>
                    <p>Bilhetes hoje</p>
                </div>
                <div class="stat-card blue">
                    <h3><?= $total_usuarios ?></h3>
                    <p>Usuários</p>
                </div>
            </div>
            
            <!-- GRÁFICOS -->
            <div class="charts-container">
                <div class="chart-box">
                    <h3>Tipos de apostas</h3>
                    <p style="text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-chart-pie" style="font-size: 48px;"></i><br><br>
                        Gráfico de tipos de apostas
                    </p>
                </div>
                <div class="chart-box">
                    <h3>Depósitos e saques</h3>
                    <p style="text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-chart-line" style="font-size: 48px;"></i><br><br>
                        Gráfico de depósitos e saques
                    </p>
                </div>
            </div>
            
            <!-- TABELA -->
            <div class="table-container">
                <h3>JOGOS MAIS JOGADOS</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Jogo</th>
                            <th>Apostas</th>
                            <th>Taxa</th>
                            <th>Valor</th>
                            <th>Acumulado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                                Nenhum jogo encontrado para hoje
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- FOOTER -->
            <div class="footer">
                © 2025 <strong>WOLF SISTEMAS</strong>. Todos os direitos reservados. | V4.0.0 B179
            </div>
        </div>
    </div>
</body>
</html>

