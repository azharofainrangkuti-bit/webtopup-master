<script setup lang="ts">
import { home, privacyPolicy, terms } from '@/routes';
import { check } from '@/routes/transaction';
import { Link, usePage } from '@inertiajs/vue3';
import { Instagram, Mail, MessageCircle, Phone, ShieldCheck } from 'lucide-vue-next';
const page = usePage();

const whatsappUrl = () => {
    const rawPhone = String(page.props.setting?.phone ?? '').replace(/\D/g, '');
    const phone = rawPhone.startsWith('0')
        ? `62${rawPhone.slice(1)}`
        : rawPhone;

    return phone ? `https://wa.me/${phone}` : '#';
};
</script>

<template>
    <footer class="border-t border-black/8 bg-gradient-to-b from-[#f9fafb] via-white to-[#f5f7fa] text-[#1d1d1f]">
        <div class="mx-auto max-w-7xl px-4 py-12 md:py-14">
            <div class="grid gap-10 md:grid-cols-4">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <h3 class="mb-4 text-xl font-bold text-[#1d1d1f]">
                        {{ page.props.name }}
                    </h3>
                    <p class="mb-6 text-sm text-[#6e6e73] leading-relaxed">
                        {{ page.props.setting.footer_description }}
                    </p>
                    <div class="flex gap-4">
                        <a
                            :href="whatsappUrl()"
                            target="_blank"
                            rel="noreferrer"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f0f0f0] text-[#6e6e73] transition-all duration-300 hover:bg-[#0071e3] hover:text-white hover:shadow-md"
                            aria-label="Chat WhatsApp"
                        >
                            <MessageCircle class="h-5 w-5" />
                        </a>
                        <a
                            href="https://instagram.com"
                            target="_blank"
                            rel="noreferrer"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f0f0f0] text-[#6e6e73] transition-all duration-300 hover:bg-[#E1306C] hover:text-white hover:shadow-md"
                            aria-label="Instagram"
                        >
                            <Instagram class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="mb-5 font-bold text-[#1d1d1f]">
                        Navigasi
                    </h4>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <Link
                                :href="home().url"
                                class="text-[#6e6e73] transition-colors duration-300 hover:text-[#0071e3] font-medium"
                            >
                                Beranda
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="check().url"
                                class="text-[#6e6e73] transition-colors duration-300 hover:text-[#0071e3] font-medium"
                            >
                                Cek Pesanan
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/price-list"
                                class="text-[#6e6e73] transition-colors duration-300 hover:text-[#0071e3] font-medium"
                            >
                                Price List
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/calculator"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Kalkulator WR MLBB
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/leaderboard"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Leaderboard Gamer
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/docs"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Dokumentasi API
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/reviews"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Ulasan Pelanggan
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="privacyPolicy().url"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Kebijakan Privasi
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="terms().url"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Syarat & Ketentuan
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/reseller"
                                class="text-muted-foreground transition-colors hover:text-primary"
                            >
                                Jadi Reseller
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                        <h4 class="mb-4 font-semibold text-[#1d1d1f]">Kontak</h4>
                    <ul class="space-y-3 text-sm">
                        <li
                            class="flex items-center gap-2 text-muted-foreground"
                        >
                            <Mail class="h-4 w-4" />
                            <span>{{ page.props.setting.email }}</span>
                        </li>
                        <li
                            class="flex items-center gap-2 text-muted-foreground"
                        >
                            <Phone class="h-4 w-4" />
                            <span>{{ page.props.setting.phone }}</span>
                        </li>
                        <li>
                            <a
                                :href="whatsappUrl()"
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center gap-2 font-semibold text-primary transition-colors hover:text-primary/80"
                            >
                                <ShieldCheck class="h-4 w-4" />
                                Hubungi CS sekarang
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div
                class="mt-8 border-t border-border/30 pt-8 text-center text-sm text-muted-foreground"
            >
                <p>
                    &copy; {{ new Date().getFullYear() }} {{ page.props.name }}. Semua hak dilindungi.
                </p>
            </div>
        </div>
    </footer>
</template>
