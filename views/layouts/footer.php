            </main>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="modalContainer" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="modalOverlay"></div>
            <div id="modalContent" class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <!-- Modal content will be injected here -->
            </div>
        </div>
    </div>

    <!-- Main JavaScript -->
    <script src="<?= BASE_URL_JS ?>assets/js/main.js"></script>
    
    <!-- Page specific scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
        <script src="<?= BASE_URL_JS ?>assets/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Auto-hide toast messages after 5 seconds
        document.querySelectorAll('.toast').forEach(toast => {
            setTimeout(() => {
                toast.classList.add('animate-fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        });
        
        // User dropdown toggle
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userDropdown && userDropdownMenu) {
            userDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', () => {
                userDropdownMenu.classList.add('hidden');
            });
        }
        
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay?.classList.toggle('hidden');
            });
            
            sidebarOverlay?.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
