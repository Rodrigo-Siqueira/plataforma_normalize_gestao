<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Plataforma Normalize</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/theme_plataforma.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/icon.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <style>
        .w3-sidebar {
            z-index: 3;
            width: 100px;
            top: 52px;
            bottom: 0;
            height: inherit;
        }

        /* Links em coluna centralizados */
        .w3-sidebar .w3-bar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 12px;
            /* texto menor */
            padding: 16px 4px;
        }
    </style>
</head>

<body class="w3-theme-l5">

    <!-- Top menu -->
    <div class="w3-bar w3-top w3-theme-light w3-card w3-large" style="z-index:4">
        <button class="w3-bar-item w3-button w3-hover-theme w3-hide-large" onclick="w3_open();">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="w3-bar-item w3-button">
            <span class="material-symbols-outlined" style="font-size: 36px;">neurology</span>
        </div>
    </div>

    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-bar-block w3-collapse w3-theme" id="mySidebar">
        <a href="<?= BASE_URL ?>index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'dashboard') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">speed</span>
            <span>Dashboard</span>
        </a>

        <!-- GESTÃO DE PESSOAS -->
        <a href="<?= BASE_URL ?>modules/psicologo/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'psicologo') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">medical_information</span>
            <span>Psicólogos</span>
        </a>
        <a href="<?= BASE_URL ?>modules/resp_financeiro/index.php" class=" w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'resp_financeiro') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">group</span>
            <span>Resp. Financeiro</span>
        </a>
        <a href="<?= BASE_URL ?>modules/paciente/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'paciente') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">personal_injury</span>
            <span>Pacientes</span>
        </a>

        <!-- PRODUTOS E VENDAS -->
        <a href="<?= BASE_URL ?>modules/produto/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'produtos') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">category</span>
            <span>Produtos</span>
        </a>
        <a href="<?= BASE_URL ?>modules/venda/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'vendas') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">shopping_cart</span>
            <span>Vendas</span>
        </a>

        <!-- OPRACIONAL -->
        <a href="<?= BASE_URL ?>modules/sessao/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'sessao') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>Sessoẽs</span>
        </a>
        <a href="<?= BASE_URL ?>modules/repasse/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'repasse') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">currency_exchange</span>
            <span>Repasses</span>
        </a>

        <!-- FINANCEIRO -->
        <a href="<?= BASE_URL ?>modules/despesas/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'despesas') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">receipt</span>
            <span>Despesas</span>
        </a>
        <a href="<?= BASE_URL ?>modules/fluxo_caixa/index.php" class="w3-bar-item w3-button w3-padding w3-hover-theme <?= ($currentPage == 'fluxo_caixa') ? ' w3-white' : '' ?>">
            <span class="material-symbols-outlined">request_quote</span>
            <span>Fluxo de Caixa</span>
        </a>

        <!-- SISTEMA -->
        <a href="#" class="w3-bar-item w3-button w3-padding">
            <span class="material-symbols-outlined">leaderboard</span>
            <span>Relatórios</span>
        </a>
    </nav>


    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>
    <!-- !PAGE CONTENT! -->
    <div class="w3-main" style="margin-left:100px; margin-top:43px;">