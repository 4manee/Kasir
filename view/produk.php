<?php
session_start();
require_once '../connection/connection.php';

if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: produk.php");
    exit;
}

if (
    isset($_POST['nama_produk'], $_POST['harga'], $_POST['stok'], $_POST['id_kategori'], $_POST['unique_id'])
) {
    $produk   = trim($_POST['nama_produk']);
    $uniqueId = trim($_POST['unique_id']);
    $harga    = (int) $_POST['harga'];
    $stok     = (int) $_POST['stok'];
    $kategori = (int) $_POST['id_kategori'];

    if ($produk !== '' && $kategori > 0 && $uniqueId !== '') {

        // 🔍 CEK UNIQUE_ID
        $cek = $conn->prepare("SELECT id FROM produk WHERE unique_id = ?");
        $cek->bind_param("s", $uniqueId);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            echo "<script>alert('Kode unique sudah digunakan!'); window.location='produk.php';</script>";
            exit;
        }

        // ✅ INSERT DATA
        $query = $conn->prepare(
            "INSERT INTO produk (id_kategori, nama_produk, unique_id, harga, stok)
             VALUES (?, ?, ?, ?, ?)"
        );

        $query->bind_param("issii", $kategori, $produk, $uniqueId, $harga, $stok);
        $query->execute();
    }

    header("Location: produk.php");
    exit;
}

$data = $conn->query("
    SELECT p.id, p.nama_produk, p.unique_id, p.harga, p.stok, k.nama_kategori
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id
    WHERE p.status = 1
    ORDER BY p.id ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Produk</title>
</head>

<body>
    <div class="flex">
        <?php include '../components/sidebar.php'; ?>

        <main class="flex-1 p-6 bg-zinc-900">
            <h1 class="text-6xl font-bold text-white">Produk</h1>
            <br>

            <div class="bg-zinc-800 rounded-lg shadow-lg p-10 min-h-screen">

                <form method="post" class="text-white">
                    <label>Nama Produk :</label><br>
                    <input type="text" name="nama_produk" class="border-b border-gray-300 w-200 mt-2 focus:outline-none"><br><br>

                    <label>Harga :</label><br>
                    <input type="number" name="harga" class="border-b border-gray-300 w-200 mt-2 focus:outline-none"><br><br>

                    <label>Stok :</label><br>
                    <input type="number" name="stok" class="border-b border-gray-300 w-200 mt-2 focus:outline-none"><br><br>

                    <label>Kode Unique :</label><br>
                    <input type="text" name="unique_id" class="border-b border-gray-300 w-200 mt-2 focus:outline-none"><br><br>

                    <label>Kategori :</label><br>
                    <select name="id_kategori" required class="bg-zinc-700 rounded mt-2">
                        <option value="" hidden>Pilih Kategori</option>
                        <?php
                        $kat = $conn->query("SELECT * FROM kategori");
                        while ($k = $kat->fetch_assoc()):
                        ?>
                            <option value="<?= $k['id'] ?>">
                                <?= $k['nama_kategori'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br><br>

                    <button type="submit" class="bg-blue-900 mt-4 w-200 rounded text-white py-2">Kirim</button>
                </form>

                <br>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Cari produk / kategori / kode..."
                    class="mb-4 p-2 w-1/3 rounded bg-zinc-700 text-white focus:outline-none"
                    onkeyup="searchTable()">
                <br>
                <table class="w-full text-center bg-zinc-900 text-white">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Kategori</th>
                        <th>Kode Unique</th>
                        <th>Edit</th>
                        <th>Hapus</th>
                    </tr>

                    <tbody id="tableBody">
                        <?php while ($row = $data->fetch_assoc()) : ?>
                            <tr class="bg-zinc-700">
                                <td><?= $row['nama_produk'] ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td><?= $row['stok'] ?></td>
                                <td><?= $row['nama_kategori'] ?></td>
                                <td><?= $row['unique_id'] ?></td>
                                <td class="bg-blue-500">
                                    <a href="produk_edit.php?id=<?= $row['id'] ?>">Edit</a>
                                </td>
                                <td class="bg-red-500">
                                    <form method="post" onsubmit="return confirm('Hapus Produk?')">
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div id="pagination" class="flex justify-center gap-2 mt-4"></div>

            </div>
        </main>
    </div>
</body>

</html>

<script>
    const rows = [...document.querySelectorAll("#tableBody tr")],
        perPage = 5,
        maxBtn = 3,
        pag = document.getElementById("pagination");
    let page = 1,
        total = Math.ceil(rows.length / perPage);

    function show(p) {
        page = p;
        rows.forEach((r, i) => r.style.display =
            i >= (p - 1) * perPage && i < p * perPage ? "" : "none");

        pag.innerHTML = "";
        let s = Math.max(1, p - 1),
            e = Math.min(total, s + maxBtn - 1);
        if (e - s < maxBtn - 1) s = Math.max(1, e - maxBtn + 1);

        for (let i = s; i <= e; i++)
            pag.innerHTML += `<button onclick="show(${i})"
  class="px-3 py-1 rounded ${i==p?'bg-blue-600':'bg-zinc-700'} text-white">${i}</button>`;
    }
    show(1);

    function searchTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let rows = document.querySelectorAll("#tableBody tr");

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? "" : "none";
        });
    }
</script>