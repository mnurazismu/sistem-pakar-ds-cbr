<?php
function renderAdminSidebar($active_page)
{
    $nama_lengkap = $_SESSION['nama_lengkap'];

    // Definisi menu items untuk admin
    $menu_items = [
        'dashboard' => [
            'url' => '/sistem-pakar-ds-cbr/admin/dashboard.php',
            'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 22 21">
                        <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                        <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                      </svg>',
            'text' => 'Dashboard'
        ],
        'users' => [
            'url' => '/sistem-pakar-ds-cbr/admin/users/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                      </svg>',
            'text' => 'Kelola Users'
        ],
        'gejala' => [
            'url' => '/sistem-pakar-ds-cbr/admin/gejala/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                      </svg>',
            'text' => 'Kelola Gejala'
        ],
        'penyakit' => [
            'url' => '/sistem-pakar-ds-cbr/admin/penyakit/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>',
            'text' => 'Kelola Penyakit'
        ],
        'pertanyaan' => [
            'url' => '/sistem-pakar-ds-cbr/admin/pertanyaan/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>',
            'text' => 'Kelola Pertanyaan'
        ],
        'pilihan_jawaban' => [
            'url' => '/sistem-pakar-ds-cbr/admin/pilihan_jawaban/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                      </svg>',
            'text' => 'Kelola Pilihan Jawaban'
        ],
        'rule_ds' => [
            'url' => '/sistem-pakar-ds-cbr/admin/rule_ds/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                      </svg>',
            'text' => 'Kelola Rule DS'
        ],
        'kasus' => [
            'url' => '/sistem-pakar-ds-cbr/admin/kasus/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                      </svg>',
            'text' => 'Kelola Kasus'
        ],
        'fitur_kasus' => [
            'url' => '/sistem-pakar-ds-cbr/admin/fitur_kasus/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                      </svg>',
            'text' => 'Kelola Fitur Kasus'
        ],
        'solusi' => [
            'url' => '/sistem-pakar-ds-cbr/admin/solusi/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>',
            'text' => 'Kelola Solusi'
        ],
        'hasil_diagnosis' => [
            'url' => '/sistem-pakar-ds-cbr/admin/hasil_diagnosis/index.php',
            'icon' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>',
            'text' => 'Hasil Diagnosis'
        ]
    ];

    ?>

<!-- Sidebar Trigger Button -->
<button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar"
    type="button"
    class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
    <span class="sr-only">Open sidebar</span>
    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
        <path clip-rule="evenodd" fill-rule="evenodd"
            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
        </path>
    </svg>
</button>

<!-- Sidebar -->
<aside id="default-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0"
    aria-label="Sidebar">
    <div class="h-full px-3 py-4 overflow-y-auto bg-white border-r border-gray-200">
        <!-- Header/Brand Section -->
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-800 mb-1">Sistem Pakar</h1>
            <p class="text-sm text-gray-600">Admin Panel</p>
            <div class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
        </div>

        <!-- Admin Profile Section -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                        <?= strtoupper(substr($nama_lengkap, 0, 1)) ?>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        <?= htmlspecialchars($nama_lengkap) ?>
                    </p>
                    <p class="text-sm text-gray-500 truncate">
                        Administrator
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="space-y-4">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Menu Utama
            </div>
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
                        <span
                            class="inline-flex items-center justify-center w-2 h-2 bg-blue-600 rounded-full ml-auto"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Logout Button -->
        <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200 bg-white">
            <button onclick="confirmLogout()"
                class="flex items-center justify-center w-full p-2 text-red-600 rounded-lg hover:bg-red-50 group transition-colors duration-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
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
                window.location.href = '/sistem-pakar-ds-cbr/auth/logout.php';
            }
        });
    };
});
</script>
<?php
}
?>