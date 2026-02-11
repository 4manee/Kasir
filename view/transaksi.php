<?php
session_start();
require_once '../connection/connection.php';

$produkResult = $conn->query("SELECT * FROM produk WHERE stok > 0");
$produk = $produkResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama   = $_POST['nama'];
    $hp     = $_POST['hp'];
    $alamat = $_POST['alamat'];

    $id_produk = $_POST['id_produk'];
    $jumlah    = $_POST['jumlah'];

    $total = 0;
    $bayar = (int) $_POST['bayar'];

    for ($i = 0; $i < count($id_produk); $i++) {
        $q = $conn->query("SELECT harga FROM produk WHERE id = {$id_produk[$i]}");
        $p = $q->fetch_assoc();
        $subtotal = $p['harga'] * $jumlah[$i];
        $total += $subtotal;
    }
        // VALIDASI BAYAR
    if ($bayar < $total) {
        echo "<script>alert('Uang bayar kurang!'); window.history.back();</script>";
        exit;
    }

    $kembalian = $bayar - $total;

    $stmt = $conn->prepare("
        INSERT INTO transaksi (nama_pelanggan, no_hp, alamat, total, bayar, kembalian)
        VALUES (?,?,?,?,?,?)
    ");
    $stmt->bind_param("sssiii", $nama, $hp, $alamat, $total, $bayar, $kembalian);
    $stmt->execute();

    $id_transaksi = $conn->insert_id;

    for ($i = 0; $i < count($id_produk); $i++) {
        $q = $conn->query("SELECT harga FROM produk WHERE id = {$id_produk[$i]}");
        $p = $q->fetch_assoc();

        $harga   = $p['harga'];
        $subtotal = $harga * $jumlah[$i];

        $conn->query("
            INSERT INTO transaksi_detail
            (id_transaksi, id_produk, jumlah, harga, subtotal)
            VALUES
            ($id_transaksi, {$id_produk[$i]}, {$jumlah[$i]}, $harga, $subtotal)
        ");

        $conn->query("
            UPDATE produk
            SET stok = stok - {$jumlah[$i]}
            WHERE id = {$id_produk[$i]}
        ");
    }

    header("Location: transaksi.php?success=1");
    exit;
}

$riwayat = $conn->query("SELECT * FROM transaksi ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-zinc-900 text-white">

    <div class="flex">
        <?php include '../components/sidebar.php'; ?>

        <main class="flex-1 p-6">

            <h1 class="text-4xl font-bold mb-6">Transaksi</h1>

            <?php if (isset($_GET['success'])): ?>
            <script>
                alert("Transaksi berhasil disimpan");
            </script>
            <?php endif; ?>

            <form method="POST" class="bg-zinc-800 p-6 rounded space-y-4">

                <input name="nama" required placeholder="Nama Pelanggan"
                    class="w-full p-2 bg-zinc-700 rounded">

                <input name="hp" required placeholder="No HP"
                    class="w-full p-2 bg-zinc-700 rounded">
                    
                    <textarea name="alamat" required placeholder="Alamat"
                    class="w-full p-2 bg-zinc-700 rounded"></textarea>

                    <input type="number" name="bayar" required placeholder="Bayar"
                    class="w-full p-2 bg-zinc-700 rounded">

                <hr class="border-zinc-600">

                <h2 class="font-bold">Produk</h2>

                <div id="produk-list" class="space-y-2">
                    <div class="flex gap-2 bg-zinc-700 p-2 rounded">
                        <select name="id_produk[]" required class="w-1/2 bg-zinc-700 p-2 rounded">
                            <option value="">Pilih Produk</option>
                            <?php foreach ($produk as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= $p['nama_produk'] ?> (stok: <?= $p['stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" name="jumlah[]" min="1" required
                            class="w-1/4 bg-zinc-700 p-2 rounded" placeholder="Jumlah">
                    </div>
                </div>

                <button type="button" onclick="tambahProduk()"
                    class="bg-blue-600 px-4 py-2 rounded">
                    + Tambah Produk
                </button>

                <button type="submit"
                    class="bg-green-600 px-6 py-2 rounded font-bold">
                    Simpan Transaksi
                </button>
            </form>
            <a href="export_excel.php"
                class="bg-green-600 px-4 py-2 rounded inline-block mb-4">
                Export Excel
            </a>
            <h2 class="text-2xl font-bold mt-10 mb-4">Riwayat Transaksi</h2>
            <div class="bg-zinc-800 p-6 rounded">
                <table class="w-full bg-zinc-800 rounded">
                    <thead class="bg-zinc-900">
                        <tr>
                            <th class="p-2">Tanggal</th>
                            <th class="p-2">Nama</th>
                            <th class="p-2">Total</th>
                            <th class="p-2">Resi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $riwayat->fetch_assoc()): ?>
                            <tr class="border-t border-zinc-700 text-center">
                                <td><?= $r['tanggal'] ?></td>
                                <td><?= $r['nama_pelanggan'] ?></td>
                                <td>Rp <?= number_format($r['total']) ?></td>
                                <td>
                                    <a href="resi.php?id=<?= $r['id'] ?>"
                                        class="bg-blue-600 px-3 py-1 rounded">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const produkData = <?= json_encode($produk) ?>;

        function tambahProduk() {
            let opsi = '<option value="">Pilih Produk</option>';
            produkData.forEach(p => {
                opsi += `<option value="${p.id}">${p.nama_produk}</option>`;
            });

            const div = document.createElement('div');
            div.className = 'flex gap-2 bg-zinc-700 p-2 rounded mt-2';
            div.innerHTML = `
        <select name="id_produk[]" required class="w-1/2 bg-zinc-700 p-2 rounded">
            ${opsi}
        </select>
        <input type="number" name="jumlah[]" min="1" required
            class="w-1/4 bg-zinc-700 p-2 rounded"
            placeholder="Jumlah">
    `;
            document.getElementById('produk-list').appendChild(div);
        }
    </script>

</body>

</html>