<?php
require_once '../connection/connection.php';

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=transaksi.xls");

$data = $conn->query("SELECT * FROM transaksi ORDER BY id DESC");

echo "<table border='1'>";
echo "
<tr>
    <th>ID</th>
    <th>Tanggal</th>
    <th>Nama</th>
    <th>No HP</th>
    <th>Alamat</th>
    <th>Total</th>
    <th>Bayar</th>
    <th>Kembalian</th>
</tr>
";

while ($r = $data->fetch_assoc()) {
    echo "<tr>
        <td>{$r['id']}</td>
        <td>{$r['tanggal']}</td>
        <td>{$r['nama_pelanggan']}</td>
        <td>{$r['no_hp']}</td>
        <td>{$r['alamat']}</td>
        <td>{$r['total']}</td>
        <td>{$r['bayar']}</td>
        <td>{$r['kembalian']}</td>
    </tr>";
}

echo "</table>";
