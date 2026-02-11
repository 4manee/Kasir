<?php
session_start();
require_once '../connection/connection.php';

if (!isset($_GET['id'])) {
    header("Location: produk.php");
    exit;
}

$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk   = $_POST['nama_produk'];
    $harga    = (int) $_POST['harga'];
    $stok     = (int) $_POST['stok'];
    $kategori = (int) $_POST['id_kategori'];

    $stmt = $conn->prepare("
        UPDATE produk
        SET id_kategori=?, nama_produk=?, harga=?, stok=?
        WHERE id=?
    ");
    $stmt->bind_param("isiii", $kategori, $produk, $harga, $stok, $id);
    $stmt->execute();

    header("Location: produk.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT * FROM produk WHERE id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$kategori = $conn->query("SELECT * FROM kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Edit Produk</title>
</head>
<body class="bg-zinc-900 text-white">

<div class="p-6 bg-zinc-800 max-w-md mx-auto mt-10 rounded">
    <h1 class="text-3xl font-bold mb-4">Edit Produk</h1>

    <form method="post" class="space-y-3 ">
        <input name="nama_produk" value="<?= $data['nama_produk'] ?>" required class=" p-2 rounded bg-zinc-700">
        <input name="harga" type="number" value="<?= $data['harga'] ?>" required class=" p-2 rounded bg-zinc-700">
        <input name="stok" type="number" value="<?= $data['stok'] ?>" required class=" p-2 rounded bg-zinc-700">

        <select name="id_kategori" required class=" p-2 rounded bg-zinc-700">
            <?php while ($k = $kategori->fetch_assoc()): ?>
                <option value="<?= $k['id'] ?>"
                    <?= $k['id'] == $data['id_kategori'] ? 'selected' : '' ?>>
                    <?= $k['nama_kategori'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button class="bg-blue-600 px-4 py-2 rounded">Update</button>
        <a href="produk.php" class="bg-zinc-600 px-4 py-2 rounded">Batal</a>
    </form>
</div>

</body>
</html>
