<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import GlobalSearchModal from '@/components/GlobalSearchModal.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useSwal } from '@/composables/useSwal';
import { Link, router, usePage } from '@inertiajs/vue3';
import { check } from '@/routes/transaction';
import {
    ArrowLeft,
    LogIn,
    Menu,
    MoreHorizontal,
    Monitor,
    Moon,
    Search,
    Sun,
    UserPlus,
    X,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import Button from './ui/button/Button.vue';

interface Props {
    showSearch?: boolean;
    showBackButton?: boolean;
}

const page = usePage();
const mobileMenuOpen = ref(false);
const { confirm } = useSwal();
const { appearance, updateAppearance } = useAppearance();

const getInitialSearch = () => {
    if (typeof window === 'undefined') {
        return '';
    }

    return new URLSearchParams(window.location.search).get('search') ?? '';
};

const searchQuery = ref(getInitialSearch());

// Use setting title or default
const appName = page.props.setting?.title || 'GameStore';
const appLogo = page.props.setting?.logo;

withDefaults(defineProps<Props>(), {
    showSearch: false,
    showBackButton: false,
});

const goBack = () => {
    router.visit('/');
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const isCurrentPath = (path: string) => {
    const currentPath = page.url.split('?')[0];

    return currentPath === path || (path !== '/' && currentPath.startsWith(`${path}/`));
};

const submitSearch = () => {
    const term = searchQuery.value.trim();
    const params = new URLSearchParams(window.location.search);

    if (term) {
        params.set('search', term);
    } else {
        params.delete('search');
    }

    router.get('/', Object.fromEntries(params.entries()));
};

const logout = () => {
    confirm({
        title: 'Apakah Anda yakin ingin keluar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, keluar',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post('/logout');
        }
    });
};

const isScrolled = ref(false);
const onScroll = () => {
    isScrolled.value = window.scrollY > 8;
};
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <header
        @keydown.esc="closeMobileMenu"
        class="sticky top-0 z-40 bg-white/95 text-[#1d1d1f] backdrop-blur transition-all duration-300"
        :class="isScrolled ? 'border-b border-black/8 shadow-md' : 'border-b border-black/4 shadow-sm'"
    >
        <div class="mx-auto max-w-7xl px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2.5 transition-opacity hover:opacity-80">
                    <img
                        v-if="appLogo"
                        :src="appLogo"
                        alt="Logo"
                        class="h-10 w-10 rounded-lg object-cover shadow-sm"
                    />
                    <div
                        v-else
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0071e3] to-[#0081f5] shadow-md"
                    >
                        <span class="text-lg font-bold text-white">{{
                            appName.charAt(0)
                        }}</span>
                    </div>
                    <span
                        class="hidden text-lg font-bold text-[#1d1d1f] md:inline"
                    >
                        {{ appName }}
                    </span>
                </Link>

                <!-- Search Bar (only on home) -->
                <div v-if="showSearch" class="max-w-xl flex-1">
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Cari game atau brand"
                            class="w-full border-black/8 bg-[#f5f5f7] text-[#1d1d1f] placeholder:text-black/40 focus:border-[#0071e3]/30"
                            @keydown.enter.prevent="submitSearch"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            @click="submitSearch"
                            class="shrink-0 border-black/8 hover:bg-[#0071e3] hover:text-white hover:border-[#0071e3]"
                            aria-label="Search game"
                        >
                            <Search class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Spacer when search is hidden -->
                <div v-else class="flex-1"></div>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center gap-8 md:flex">
                    <Link
                        href="/"
                        :class="isCurrentPath('/') ? 'text-[#0071e3] font-semibold' : 'text-[#6e6e73] hover:text-[#0071e3]'"
                        class="text-sm font-medium transition-colors duration-300"
                        :aria-current="isCurrentPath('/') ? 'page' : undefined"
                    >
                        Beranda
                    </Link>
                    <Link
                        href="/top-up"
                        :class="isCurrentPath('/top-up') ? 'text-[#0071e3] font-semibold' : 'text-[#6e6e73] hover:text-[#0071e3]'"
                        class="text-sm font-medium transition-colors duration-300"
                        :aria-current="isCurrentPath('/top-up') ? 'page' : undefined"
                    >
                        Top Up
                    </Link>
                    <Link
                        href="/promo"
                        :class="isCurrentPath('/promo') ? 'text-[#0071e3] font-semibold' : 'text-[#6e6e73] hover:text-[#0071e3]'"
                        class="text-sm font-medium transition-colors duration-300"
                        :aria-current="isCurrentPath('/promo') ? 'page' : undefined"
                    >
                        Promo
                    </Link>
                    <Link
                        :href="check().url"
                        :class="isCurrentPath('/transaction') ? 'text-[#0071e3] font-semibold' : 'text-[#6e6e73] hover:text-[#0071e3]'"
                        class="text-sm font-medium transition-colors duration-300"
                        :aria-current="isCurrentPath('/transaction') ? 'page' : undefined"
                    >
                        Cek Pesanan
                    </Link>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="gap-1 text-sm font-medium text-[#424245] hover:bg-black/5 hover:text-[#0071e3]">
                                Lainnya <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem as-child><Link href="/price-list" class="w-full">Price List</Link></DropdownMenuItem>
                            <DropdownMenuItem as-child><Link href="/calculator" class="w-full">Kalkulator</Link></DropdownMenuItem>
                            <DropdownMenuItem as-child><Link href="/leaderboard" class="w-full">Leaderboard</Link></DropdownMenuItem>
                            <DropdownMenuItem as-child><Link href="/docs" class="w-full">Dokumentasi API</Link></DropdownMenuItem>
                            <DropdownMenuItem as-child><Link href="/reviews" class="w-full">Ulasan</Link></DropdownMenuItem>
                            <DropdownMenuItem as-child><Link href="/profile" class="w-full">Profile</Link></DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </nav>

                <!-- Right side container for mobile -->
                <div class="flex items-center gap-2">
                    <!-- Global Quick Search -->
                    <div class="hidden md:block">
                        <GlobalSearchModal />
                    </div>

                    <!-- Theme Switcher -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 rounded-full border-0 text-[#424245] hover:bg-black/5 hover:text-[#0071e3]"
                                aria-label="Pilih tema tampilan"
                            >
                                <Sun
                                    v-if="appearance === 'light'"
                                    class="h-[1.2rem] w-[1.2rem] scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90"
                                />
                                <Moon
                                    v-else-if="appearance === 'dark'"
                                    class="absolute h-[1.2rem] w-[1.2rem] scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0"
                                />
                                <Monitor v-else class="h-[1.2rem] w-[1.2rem]" />
                                <span class="sr-only">Toggle theme</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem
                                @click="updateAppearance('light')"
                            >
                                <Sun class="mr-2 h-4 w-4" />
                                <span>Terang</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="updateAppearance('dark')">
                                <Moon class="mr-2 h-4 w-4" />
                                <span>Gelap</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                @click="updateAppearance('system')"
                            >
                                <Monitor class="mr-2 h-4 w-4" />
                                <span>Ikuti sistem</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <!-- Mobile Menu Button -->
                    <button
                        v-if="!showBackButton"
                        type="button"
                        :aria-label="mobileMenuOpen ? 'Tutup menu' : 'Buka menu'"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="mobile-navigation"
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-[#424245] transition hover:bg-[#eef6ff] hover:text-[#0877e8] md:hidden"
                        @click="toggleMobileMenu"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>

                    <!-- Back Button -->
                    <button
                        v-if="showBackButton"
                        class="flex items-center gap-2 text-sm font-medium text-foreground hover:text-primary"
                        @click="goBack"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span class="hidden md:inline">Kembali</span>
                    </button>

                    <!-- Auth Buttons -->
                    <div
                        v-if="!page.props.auth.user"
                        class="hidden items-center gap-2 md:flex"
                    >
                        <Link href="/login">
                            <Button variant="outline">
                                <!-- LogIn Icon -->
                                <LogIn class="h-4 w-4" />
                                <span>Masuk</span>
                            </Button>
                        </Link>
                        <Link href="/register">
                            <Button variant="outline">
                                <!-- UserPlus Icon -->
                                <UserPlus class="h-4 w-4" />
                                <span>Daftar</span>
                            </Button>
                        </Link>
                    </div>
                    <Button
                        v-else
                        variant="outline"
                        class="hidden md:flex"
                        @click="logout"
                    >
                        <!-- LogOut Icon -->
                        <LogIn class="h-4 w-4 rotate-180" />
                        <span>Keluar</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div
            v-if="mobileMenuOpen"
            id="mobile-navigation"
            class="border-t border-black/8 bg-white/98 shadow-xl backdrop-blur md:hidden"
        >
            <nav class="mx-auto max-w-7xl space-y-1.5 px-4 py-4">
                <div class="mb-3 rounded-2xl bg-[#f5f7fa] p-1">
                    <GlobalSearchModal />
                </div>
                <Link
                    href="/"
                    :class="isCurrentPath('/') ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted hover:text-primary'"
                    class="block rounded-xl px-3.5 py-3 text-base font-semibold transition"
                    :aria-current="isCurrentPath('/') ? 'page' : undefined"
                    @click="closeMobileMenu"
                >
                    Beranda
                </Link>
                <Link
                    href="/top-up"
                    :class="isCurrentPath('/top-up') ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted hover:text-primary'"
                    class="block rounded-xl px-3.5 py-3 text-base font-semibold transition"
                    :aria-current="isCurrentPath('/top-up') ? 'page' : undefined"
                    @click="closeMobileMenu"
                >
                    Top Up
                </Link>
                <Link
                    href="/promo"
                    :class="isCurrentPath('/promo') ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted hover:text-primary'"
                    class="block rounded-xl px-3.5 py-3 text-base font-semibold transition"
                    :aria-current="isCurrentPath('/promo') ? 'page' : undefined"
                    @click="closeMobileMenu"
                >
                    Promo
                </Link>
                <Link
                    :href="check().url"
                    :class="isCurrentPath('/transaction') ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted hover:text-primary'"
                    class="block rounded-xl px-3.5 py-3 text-base font-semibold transition"
                    :aria-current="isCurrentPath('/transaction') ? 'page' : undefined"
                    @click="closeMobileMenu"
                >
                    Cek Transaksi
                </Link>
                <Link
                    href="/price-list"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Price List
                </Link>
                <Link
                    href="/calculator"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Kalkulator MLBB
                </Link>
                <Link
                    href="/leaderboard"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Leaderboard
                </Link>
                <Link
                    href="/docs"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Dokumentasi API
                </Link>
                <Link
                    href="/reviews"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Ulasan
                </Link>
                <Link
                    href="/profile"
                    class="block rounded-xl px-3.5 py-3 text-base font-medium text-[#424245] transition hover:bg-[#f5f7fa] hover:text-[#0877e8]"
                    @click="closeMobileMenu"
                >
                    Profile
                </Link>

                <!-- Mobile Auth Links -->
                <template v-if="!page.props.auth.user">
                    <Link
                        href="/login"
                        class="block rounded-md px-3 py-2 text-base font-medium text-foreground hover:bg-muted hover:text-primary"
                        @click="closeMobileMenu"
                    >
                        Masuk
                    </Link>
                    <Link
                        href="/register"
                        class="block rounded-md px-3 py-2 text-base font-medium text-foreground hover:bg-muted hover:text-primary"
                        @click="closeMobileMenu"
                    >
                        Daftar
                    </Link>
                </template>
                <button
                    v-else
                    class="block w-full rounded-md px-3 py-2 text-left text-base font-medium text-foreground hover:bg-muted hover:text-primary"
                    @click="logout"
                >
                    Keluar
                </button>
            </nav>
        </div>
    </header>
</template>
