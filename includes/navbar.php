<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between">
            <div class="flex space-x-7">
                <div>
                    <a href="index.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">Sistem Pakar</span>
                    </a>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <span class="text-gray-500"><?= $_SESSION['nama_lengkap'] ?></span>
                <a href="auth/logout.php"
                    class="py-2 px-4 bg-red-500 hover:bg-red-600 text-white rounded transition duration-300">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>