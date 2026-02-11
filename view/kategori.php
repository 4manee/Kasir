<?php
session_start();
require_once '../connection/connection.php';

// TAMBAH KATEGORI
if (isset($_POST['nama_kategori'])) {
    $nama = trim($_POST['nama_kategori']);

    if ($nama != '') {
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
    }

    header("Location: kategori.php");
    exit;
}

// HAPUS KATEGORI
// HAPUS KATEGORI
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    // CEK APAKAH KATEGORI DIPAKAI PRODUK
    $cek = $conn->prepare("SELECT id FROM produk WHERE id_kategori = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        echo "<script>alert('Kategori tidak bisa dihapus karena masih digunakan produk!'); window.location='kategori.php';</script>";
        exit;
    }

    // JIKA TIDAK DIPAKAI → HAPUS
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: kategori.php");
    exit;
}


// AMBIL DATA KATEGORI
$data = $conn->query("SELECT * FROM kategori ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Kategori</title>
</head>
<body>
<div class="flex">
<?php include '../components/sidebar.php'; ?>

<main class="flex-1 p-6 bg-zinc-900 text-white">
    <h1 class="text-5xl font-bold mb-6">Kategori</h1>

    <!-- FORM TAMBAH -->
    <form method="post" class="mb-6">
        <label>Nama Kategori :</label><br>
        <input type="text" name="nama_kategori" required
               class="border-b bg-transparent border-gray-300 mt-2 focus:outline-none">
        <br><br>
        <button type="submit" class="bg-blue-700 px-4 py-2 rounded">Tambah</button>
    </form>

    <!-- TABEL -->
    <table class="w-full text-center bg-zinc-800">
        <tr class="bg-zinc-700">
            <th>Nama Kategori</th>
            <th>Hapus</th>
        </tr>

        <?php while($row = $data->fetch_assoc()): ?>
        <tr class="border-b border-zinc-700">
            <td><?= $row['nama_kategori'] ?></td>
            <td class="bg-red-600">
                <form method="post" onsubmit="return confirm('Hapus kategori?')">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</div>
</body>
</html>
