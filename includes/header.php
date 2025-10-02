<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok - Abu Dafi 5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-calculator"></i> Kedai Kopi Abu Dafi 5
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stok.php"><i class="fas fa-boxes"></i> Stok Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan-harian.php"><i class="fas fa-file-alt"></i> Laporan Harian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cetak-laporan.php"><i class="fas fa-print"></i> Cetak Laporan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">