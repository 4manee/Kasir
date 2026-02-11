<?php
session_start();
require_once '../connection/connection.php';

// function format rupiah
function rupiah($angka)
{
    return "Rp " . number_format($angka ?? 0, 0, ',', '.');
}

// total produk
$totalProduk = $conn->query("SELECT COUNT(*) AS total FROM produk")
    ->fetch_assoc()['total'];

// total rupiah hari ini
$hariIni = $conn->query("
    SELECT COALESCE(SUM(total),0) AS total
    FROM transaksi
    WHERE DATE(tanggal) = CURDATE()
")->fetch_assoc()['total'];

// total rupiah bulan ini
$bulanIni = $conn->query("
    SELECT COALESCE(SUM(total),0) AS total
    FROM transaksi
    WHERE MONTH(tanggal) = MONTH(CURDATE())
      AND YEAR(tanggal) = YEAR(CURDATE())
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Dashboard</title>
</head>

<body class="bg-zinc-900">
    <div class="flex">

        <?php include '../components/sidebar.php'; ?>

        <main class="flex-1 p-6">
            <h1 class="text-5xl font-bold text-white mb-10">Dashboard</h1>

            <h2 class="text-2xl font-bold text-white mb-6">Catatan Harian</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-white">

                <div class="bg-zinc-800 h-40 rounded-lg shadow-lg p-4 hover:shadow-white transition">
                    <h1 class="text-xl font-bold">Total Produk</h1>
                    <p class="text-4xl font-bold mt-4"><?= $totalProduk ?></p>
                </div>

                <div class="bg-zinc-800 h-40 rounded-lg shadow-lg p-4 hover:shadow-white transition">
                    <h1 class="text-xl font-bold">Transaksi Hari Ini</h1>
                    <p class="text-4xl font-bold mt-4"><?= rupiah($hariIni) ?></p>
                </div>

                <div class="bg-zinc-800 h-40 rounded-lg shadow-lg p-4 hover:shadow-white transition">
                    <h1 class="text-xl font-bold">Transaksi Bulan Ini</h1>
                    <p class="text-4xl font-bold mt-4"><?= rupiah($bulanIni) ?></p>
                </div>
            </div>

    </div>


    </main>
    </div>
</body>

</html>