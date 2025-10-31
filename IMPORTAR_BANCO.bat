@echo off
chcp 65001 >nul
cls
echo ╔════════════════════════════════════════════════════════╗
echo ║                                                        ║
echo ║      BANCA ESPORTIVA - IMPORTADOR DE BANCO DE DADOS   ║
echo ║                                                        ║
echo ╚════════════════════════════════════════════════════════╝
echo.
echo 🗄️ Este script irá importar o banco de dados automaticamente
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
echo 2. Ou importe manualmente via phpMyAdmin
echo    ^(Consulte IMPORTAR-BANCO.md^)
echo.
pause
exit /b 1

:php_found

echo ✅ PHP encontrado!
echo.

REM Verificar se o arquivo SQL existe
if not exist "reidoscript bancas.sql" (
    echo ❌ ERRO: Arquivo 'reidoscript bancas.sql' não encontrado!
    echo.
    echo Certifique-se de que o arquivo está na mesma pasta deste script.
    echo.
    echo Caminho atual: %CD%
    echo.
    pause
    exit /b 1
)

echo ✅ Arquivo SQL encontrado!
echo.

REM Verificar se o script PHP existe
if not exist "importar-banco.php" (
    echo ❌ ERRO: Arquivo importar-banco.php não encontrado!
    echo.
    pause
    exit /b 1
)

echo ✅ Script de importação encontrado!
echo.
echo ═══════════════════════════════════════════════════════
echo.
echo 📋 INFORMAÇÕES IMPORTANTES:
echo.
echo • O banco será criado automaticamente
echo • Nome sugerido: banca_esportiva
echo • Usuário admin: admin
echo • Senha padrão: 123456
echo.
echo ⚠️ Certifique-se de que o MySQL está rodando!
echo    ^(XAMPP: Inicie o Apache e MySQL^)
echo.
echo ═══════════════════════════════════════════════════════
echo.
pause
echo.
echo 🚀 Iniciando importação...
echo.
echo ═══════════════════════════════════════════════════════
echo.

REM Executar o script PHP
echo 📍 Usando PHP em: %PHP_PATH%
echo.
"%PHP_PATH%" importar-banco.php

echo.
echo ═══════════════════════════════════════════════════════
echo.
echo ✅ Processo de importação concluído!
echo.
echo 📖 Próximos passos:
echo    1. Verifique se não houve erros acima
echo    2. Configure os arquivos do sistema
echo    3. Execute: INICIAR_CONFIGURACAO.bat
echo.
echo 📚 Documentação:
echo    - IMPORTAR-BANCO.md (guia de importação)
echo    - CONFIGURACAO.md (guia de configuração)
echo    - painel-configuracao.html (interface visual)
echo.
echo ═══════════════════════════════════════════════════════
echo.
pause

