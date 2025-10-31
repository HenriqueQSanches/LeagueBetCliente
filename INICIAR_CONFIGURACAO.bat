@echo off
chcp 65001 >nul
cls
echo ╔════════════════════════════════════════════════════════╗
echo ║                                                        ║
echo ║        BANCA ESPORTIVA - CONFIGURADOR AUTOMÁTICO      ║
echo ║                                                        ║
echo ╚════════════════════════════════════════════════════════╝
echo.
echo 🎰 Bem-vindo ao Sistema de Banca Esportiva!
echo.
echo Este script irá iniciar o configurador automático.
echo.
echo ═══════════════════════════════════════════════════════
echo.

REM Verificar se o PHP está disponível
set "PHP_PATH="

REM Tentar encontrar PHP no PATH
where php >nul 2>&1
if %errorlevel% equ 0 (
    set "PHP_PATH=php"
    goto :php_found
)

REM Tentar encontrar PHP do XAMPP (locais comuns)
if exist "C:\xampp\php\php.exe" (
    set "PHP_PATH=C:\xampp\php\php.exe"
    goto :php_found
)

if exist "%CD%\..\..\..\..\..\php\php.exe" (
    set "PHP_PATH=%CD%\..\..\..\..\..\php\php.exe"
    goto :php_found
)

if exist "C:\Program Files\xampp\php\php.exe" (
    set "PHP_PATH=C:\Program Files\xampp\php\php.exe"
    goto :php_found
)

if exist "D:\xampp\php\php.exe" (
    set "PHP_PATH=D:\xampp\php\php.exe"
    goto :php_found
)

REM PHP não encontrado
echo ❌ ERRO: PHP não encontrado!
echo.
echo O script tentou encontrar o PHP nos seguintes locais:
echo   - PATH do sistema
echo   - C:\xampp\php\php.exe
echo   - C:\Program Files\xampp\php\php.exe
echo   - D:\xampp\php\php.exe
echo.
echo OPÇÕES:
echo 1. Se você tem XAMPP instalado em outro local,
echo    edite este arquivo e adicione o caminho correto
echo 2. Ou abra o arquivo 'painel-configuracao.html' no navegador
echo.
pause
exit /b 1

:php_found
echo ✅ PHP encontrado!
echo 📍 Usando PHP em: %PHP_PATH%
echo.

REM Mostrar versão do PHP
"%PHP_PATH%" -v | findstr /i "PHP"
echo.
echo ═══════════════════════════════════════════════════════
echo.

REM Verificar se os arquivos necessários existem
if not exist "inc.config.php" (
    echo ❌ ERRO: Arquivo inc.config.php não encontrado!
    echo Certifique-se de estar na pasta correta do projeto.
    echo.
    pause
    exit /b 1
)

if not exist "conexao.php" (
    echo ❌ ERRO: Arquivo conexao.php não encontrado!
    echo Certifique-se de estar na pasta correta do projeto.
    echo.
    pause
    exit /b 1
)

echo ✅ Arquivos necessários encontrados!
echo.
echo ═══════════════════════════════════════════════════════
echo.
echo 🚀 Iniciando configurador...
echo.
echo ═══════════════════════════════════════════════════════
echo.

REM Executar o script de configuração
"%PHP_PATH%" configurar.php

echo.
echo ═══════════════════════════════════════════════════════
echo.
echo ✅ Processo concluído!
echo.
echo 📋 PRÓXIMOS PASSOS:
echo    1. Criar o banco de dados no MySQL
echo    2. Importar o arquivo SQL (se houver)
echo    3. Testar o acesso ao site
echo    4. Configurar os CRON jobs
echo    5. Acessar /admin/ com login: admin / senha: 123456
echo.
echo 📖 Para mais informações, abra 'painel-configuracao.html'
echo    ou consulte 'CONFIGURACAO.md'
echo.
echo ═══════════════════════════════════════════════════════
echo.
pause

