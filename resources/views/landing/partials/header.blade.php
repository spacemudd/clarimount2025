<header class="fixed top-0 w-full bg-white/95 backdrop-blur-sm z-50 border-b border-gray-200">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between h-20">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center">
                    HR
                </div>
                <span class="text-xl text-gray-900">منصة الموارد البشرية</span>
            </div>

            <nav class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-gray-700 hover:text-blue-600 transition-colors">
                    المميزات
                </a>
                <a href="#pricing" class="text-gray-700 hover:text-blue-600 transition-colors">
                    الأسعار
                </a>
                <a href="#about" class="text-gray-700 hover:text-blue-600 transition-colors">
                    من نحن
                </a>
                <a href="#contact" class="text-gray-700 hover:text-blue-600 transition-colors">
                    اتصل بنا
                </a>
            </nav>

            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                    تسجيل الدخول
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    ابدأ مجاناً
                </a>
            </div>

            <button 
                id="mobile-menu-button"
                class="md:hidden"
                onclick="toggleMobileMenu()"
                aria-label="Toggle menu"
            >
                <svg class="size-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden py-4 border-t border-gray-200">
            <nav class="flex flex-col gap-4">
                <a href="#features" class="mobile-menu-link text-gray-700 hover:text-blue-600 transition-colors">
                    المميزات
                </a>
                <a href="#pricing" class="mobile-menu-link text-gray-700 hover:text-blue-600 transition-colors">
                    الأسعار
                </a>
                <a href="#about" class="mobile-menu-link text-gray-700 hover:text-blue-600 transition-colors">
                    من نحن
                </a>
                <a href="#contact" class="mobile-menu-link text-gray-700 hover:text-blue-600 transition-colors">
                    اتصل بنا
                </a>
                <a href="{{ route('login') }}" class="mobile-menu-link px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors text-center">
                    تسجيل الدخول
                </a>
                <a href="{{ route('register') }}" class="mobile-menu-link px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-center">
                    ابدأ مجاناً
                </a>
            </nav>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }

    function closeMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.add('hidden');
    }

    // Close mobile menu when clicking on any link
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
    });
</script>
@endpush

