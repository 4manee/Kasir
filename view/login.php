<?php
require_once '../connection/connection.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['username'];
    $password = $_POST['password'];

    $query = $conn->prepare ( 'SELECT * FROM admin WHERE username=? AND password=?');
    $query->bind_param('ss', $name, $password);
    $query->execute();

    $result = $query->get_result();
    if ($result->num_rows > 0){
        $admin = $result->fetch_assoc();

        if($password === $admin['password']){
            $_SESSION['admin'] = $admin;
            header('Location: dashboard.php');
            exit();
        } else { 
            $error = "Salah Password";
        }
    }else {
        $error = "User tidak ditemukan";
    }
    $query->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Document</title>
</head>
<body>
    <div class="bg-zinc-900 min-h-screen flex items-center justify-center">
        <div class="bg-zinc-800 p-8 rounded shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-white">Login Kasir</h2>
            <form action="" method="post">
                <div class="text-white">
                    <label for="" class="block text-sm font-medium text-gray-700 text-white">Username</label>
                    <input type="text" name="username" placeholder="Nama" class="border-b border-gray-300 w-full mt-1 focus:outline-none">
                </div>
                <br>
                <div class="text-white">
                    <label for="" class="block text-sm font-medium text-gray-700 text-white">Password</label>
                    <input type="password" name="password" placeholder="Password" class="border-b border-gray-300 w-full mt-1 focus:outline-none">
                </div>
                <?php if (isset($error)) echo "<p>$error</p>"?>
                <br>
                <button 
                    type="submit"
                    class="bg-blue-900 mt-4 w-full rounded text-white py-2 cursor-pointer">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>