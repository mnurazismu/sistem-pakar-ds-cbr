<?php
function renderUserSidebar($active_page) {
    $nama_lengkap = $_SESSION['nama_lengkap'];
    
    // Definisi menu items untuk user
    $menu_items = [
        'dashboard' => [
            'url' => '/ds-cbr/user/dashboard.php',
            'icon' => '<svg class="w-5 h-5 transition duration-75" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                        <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                        <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                      </svg>',
            'text' => 'Dashboard'
        ],
        'diagnosis' => [
            'url' => '/ds-cbr/user/diagnosis/index.php',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                      </svg>',
            'text' => 'Diagnosis Penyakit'
        ],
        'riwayat' => [
            'url' => '/ds-cbr/user/riwayat/index.php',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>',
            'text' => 'Riwayat Diagnosis'
        ],
        'profil' => [
            'url' => '/ds-cbr/user/profil/index.php',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>',
            'text' => 'Profil'
        ]
    ];
    ?>
    
    <!-- Sidebar Trigger Button -->
    <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" 
            class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
        <span class="sr-only">Open sidebar</span>
        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
        </svg>
    </button>

    <!-- Sidebar -->
    <aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-white border-r border-gray-200">
            <!-- Header/Brand Section -->
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold text-gray-800 mb-1">Sistem Pakar</h1>
                <p class="text-sm text-gray-600">Metode DS & CBR</p>
                <div class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
            </div>

            <!-- User Profile Section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($nama_lengkap, 0, 1)) ?>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <?= htmlspecialchars($nama_lengkap) ?>
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            User
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <ul class="space-y-2 font-medium">
                <?php foreach ($menu_items as $key => $item): ?>
                    <li>
                        <a href="<?= $item['url'] ?>" 
                           class="flex items-center p-2 text-gray-900 rounded-lg <?= $active_page === $key ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-100' ?>">
                            <span class="<?= $active_page === $key ? 'text-blue-600' : 'text-gray-500' ?>">
                                <?= $item['icon'] ?>
                            </span>
                            <span class="ms-3"><?= $item['text'] ?></span>
                            <?php if ($active_page === $key): ?>
                                <span class="inline-flex items-center justify-center w-2 h-2 bg-blue-600 rounded-full ml-auto"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Logout Button (Fixed at bottom) -->
            <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200 bg-white">
                <button onclick="confirmLogout()" 
                        class="flex items-center justify-center w-full p-2 text-red-600 rounded-lg hover:bg-red-50 group transition-colors duration-200">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="ml-3">Logout</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Logout Confirmation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.confirmLogout = function() {
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/ds-cbr/auth/logout.php';
                    }
                });
            };
        });
    </script>
    <?php
}
?>