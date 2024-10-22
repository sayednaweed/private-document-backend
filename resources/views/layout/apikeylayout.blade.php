<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master: @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Hide scrollbar for the sidebar */
        #sidebar::-webkit-scrollbar {
            display: none;
        }

        #sidebar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Main Container -->
    <div class="flex min-h-screen" id="main-container">

        <!-- Sidebar -->
        <div id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-300 transform -translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-lg">
            <div class="flex items-center justify-between p-5 border-b border-gray-700">
                <h1 class="text-xl font-bold">Master Dashboard</h1>
                <button id="closeBtn" class="text-gray-400 hover:text-white focus:outline-none lg:hidden">
                    ✕
                </button>
            </div>
            <nav class="mt-6">
                <a href="{{ route('master.dashboard') }}" class="flex items-center py-3 px-5 hover:bg-gray-800 rounded">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m3 18h9m-9-7h9" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('master.key') }}" class="flex items-center py-3 px-5 hover:bg-gray-800 rounded">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A5.002 5.002 0 0115 17h5a2 2 0 100-4h-1M5 9h6a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h4m-4-4h4" />
                    </svg>
                    Key
                </a>
                <a href="#" class="flex items-center py-3 px-5 hover:bg-gray-800 rounded">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M8 16a4 4 0 018 0m-4-8a4 4 0 100 8m0 0a4 4 0 01-8 0" />
                    </svg>
                    Settings
                </a>
                <a href="{{ route('web.logout') }}" class="flex items-center py-3 px-5 hover:bg-gray-800 rounded">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                    Logout
                </a>
            </nav>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navbar -->
            <header class="bg-white shadow-md p-4 flex items-center justify-between lg:hidden">
                <button id="menuBtn" class="text-gray-900 focus:outline-none">
                    ☰
                </button>
                <h1 class="text-xl font-semibold">Dashboard</h1>
            </header>

            <!-- Main Content -->
            <main class="p-6">
                @yield('main-content')
            </main>
        </div>

    </div>

    @yield('script')
    <!-- Script to toggle and collapse sidebar -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.getElementById('menuBtn');
        const closeBtn = document.getElementById('closeBtn');
        const mainContainer = document.getElementById('main-container');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
        });

        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
        });

        // Close sidebar when clicking outside (on mobile)
        mainContainer.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>

</body>

</html>