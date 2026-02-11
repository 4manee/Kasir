<?php
require_once '../connection/connection.php';

$id = $_GET['id'] ?? 0;

// Ambil data transaksi
$trx = $conn->query("SELECT * FROM transaksi WHERE id = $id")->fetch_assoc();
if (!$trx) {
    echo "Transaksi tidak ditemukan";
    exit;
}

// Ambil detail transaksi
$detail = $conn->query("
    SELECT td.*, p.nama_produk
    FROM transaksi_detail td
    JOIN produk p ON td.id_produk = p.id
    WHERE td.id_transaksi = $id
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Resi</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            body {
                background: white;
            }
        }
    </style>
</head>

<body class="bg-zinc-200 flex justify-center py-6 print:bg-white">

    <div class="w-[300px] bg-white text-black p-4 text-xs font-mono">

        <div class="text-center mb-2">
            <h1 class="font-bold text-sm">TOKO KASIR</h1>
            <p class="border-b border-dashed border-black my-2"></p>
        </div>

        <div class="mb-2 space-y-1">
            <p>Tanggal : <?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?></p>
            <p>Nama : <?= htmlspecialchars($trx['nama_pelanggan']) ?></p>
            <p>HP : <?= htmlspecialchars($trx['no_hp']) ?></p>
        </div>

        <div class="border-b border-dashed border-black my-2"></div>

        <div class="space-y-1">
            <?php while ($d = $detail->fetch_assoc()): ?>
                <div>
                    <p><?= htmlspecialchars($d['nama_produk']) ?></p>
                    <div class="flex justify-between">
                        <span><?= $d['jumlah'] ?> x <?= number_format($d['harga']) ?></span>
                        <span><?= number_format($d['subtotal']) ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="border-b border-dashed border-black my-2"></div>

        <div class="flex justify-between font-bold">
            <span>TOTAL</span>
            <span><?= number_format($trx['total']) ?></span>
        </div>

        <div class="flex justify-between">
            <span>BAYAR</span>
            <span><?= number_format($trx['bayar']) ?></span>
        </div>

        <div class="flex justify-between">
            <span>KEMBALIAN</span>
            <span><?= number_format($trx['kembalian']) ?></span>
        </div>

        <div class="mt-4 flex gap-2 print:hidden">
            <button onclick="window.print()"
                class="flex-1 bg-black text-white py-1 rounded">
                Print
            </button>
            <button onclick="window.close()"
                class="flex-1 bg-zinc-400 py-1 rounded">
                Tutup
            </button>
        </div>
    </div>

    <script>
        window.addEventListener("load", () => {
            window.print();
        });
    </script>

</body>

</html>