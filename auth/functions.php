<?php
session_start();

// Fungsi untuk login
function login($email, $password)
{
    global $conn;

    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['tipe_user'] = $user['tipe_user'];
            return true;
        }
    }
    return false;
}

// Fungsi untuk registrasi
function register($nama_lengkap, $email, $password, $tipe_user = 'User')
{
    global $conn;

    // Cek email sudah terdaftar
    $query = "SELECT email FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($result)) {
        return "Email sudah terdaftar!";
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru dengan tipe_user
    $query = "INSERT INTO users (nama_lengkap, email, password, tipe_user) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nama_lengkap, $email, $password_hash, $tipe_user);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    }
    return "Registrasi gagal!";
}

// Tambahkan fungsi untuk membuat admin
function createAdmin($nama_lengkap, $email, $password) {
    return register($nama_lengkap, $email, $password, 'Admin');
}

// Fungsi untuk cek login
function isLoggedIn()
{
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

// Fungsi untuk cek role admin
function isAdmin()
{
    return isset($_SESSION['tipe_user']) && $_SESSION['tipe_user'] === 'Admin';
}

// Fungsi untuk cek role user
function isUser()
{
    return isset($_SESSION['tipe_user']) && $_SESSION['tipe_user'] === 'User';
}

// Fungsi untuk logout
function logout()
{
    $_SESSION = [];
    session_unset();
    session_destroy();
}
