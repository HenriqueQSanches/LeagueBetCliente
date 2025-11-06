# ✅ URL CORRETA DO SITE

## 🌐 Acesse o site por esta URL:

```
http://localhost/Cliente/LeagueBetCliente-main/
```

**NÃO use:** `http://localhost:8000` ❌

---

## 🎯 Por que essa URL?

O arquivo `inc.config.php` está configurado assim:

```php
'dominio' => 'http://localhost/Cliente/LeagueBetCliente-main',
'uri' => 'http://localhost/Cliente/LeagueBetCliente-main',
```

Isso significa que **todas as URLs internas** do sistema (incluindo a API de jogos) usam esse caminho.

---

## 📋 Checklist Final:

1. ✅ Abra: `http://localhost/Cliente/LeagueBetCliente-main/`
2. ✅ Pressione **Ctrl + Shift + R** para limpar o cache
3. ✅ Os jogos devem aparecer automaticamente!

---

## 🎮 O que você deve ver:

Na área central do site, abaixo de "Jogos Disponíveis", você deve ver:

- **📍 Países** (ex: BRASIL, ESPANHA)
- **🏆 Campeonatos** (ex: Campeonato Brasileiro - Série A, La Liga)
- **⚽ Jogos** com:
  - Times (Casa x Fora)
  - Data e hora
  - Cotações (botões laranja com números)

---

## ❌ Se ainda não aparecer:

Pressione **F12** e me envie:
1. Erros em vermelho no **Console**
2. Na aba **Network**, procure por "jogos" e me mostre o **Status** e **Response**

---

## 🔖 Favoritos Recomendados:

Salve nos favoritos:
- **Site Principal**: `http://localhost/Cliente/LeagueBetCliente-main/`
- **Painel Admin**: `http://localhost/Cliente/LeagueBetCliente-main/admin-login.php`
- **Status API**: `http://localhost/Cliente/LeagueBetCliente-main/status-api.php`
- **Importar Jogos**: `http://localhost/Cliente/LeagueBetCliente-main/importar-agora.php`

