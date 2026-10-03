<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, Headphones, LineChart, Store } from 'lucide-vue-next';

defineProps<{
    support_url: string;
}>();

const page = usePage();
const setting = page.props.setting;

const benefits = [
    { title: 'Produk lengkap', detail: 'Akses katalog top up dan voucher game yang terus diperbarui.', icon: Store },
    { title: 'Operasional ringan', detail: 'Pesanan diproses melalui alur transaksi yang sudah tersedia.', icon: LineChart },
    { title: 'Dukungan langsung', detail: 'Hubungi tim kami untuk diskusi kebutuhan dan pendaftaran partner.', icon: Headphones },
];
</script>

<template>
    <Head>
        <title>{{ `Program Reseller - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />

        <main class="flex-1 px-4 py-8 md:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl border border-[#0071e3]/15 bg-gradient-to-br from-[#e8f1fb] via-white to-[#f5f5f7] p-6 shadow-sm md:p-10">
                <div class="max-w-3xl">
                    <span class="mb-4 inline-flex items-center gap-2 rounded-full bg-[#0071e3]/10 px-3 py-1 text-xs font-semibold text-[#0071e3]">
                        <BadgeCheck class="h-4 w-4" />
                        Partner ecosystem
                    </span>
                    <h1 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] md:text-5xl">Bangun layanan top up game-mu bersama kami.</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-[#6e6e73]">Program reseller untuk partner yang ingin menyediakan produk digital dengan katalog lengkap, proses terstruktur, dan dukungan langsung.</p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a :href="support_url" target="_blank" rel="noreferrer" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#0071e3] px-5 text-sm font-semibold text-white transition hover:bg-[#0077ed]">
                            Hubungi tim kami <ArrowRight class="h-4 w-4" />
                        </a>
                        <Link href="/docs" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-black/10 bg-white px-5 text-sm font-semibold text-[#1d1d1f] transition hover:bg-[#f5f5f7]">
                            Lihat dokumentasi
                        </Link>
                    </div>
                </div>
            </section>

            <section class="mt-8">
                <div class="mb-5">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-[#0071e3]">Untuk partner</p>
                    <h2 class="text-2xl font-semibold text-[#1d1d1f]">Semua yang dibutuhkan untuk mulai</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <article v-for="benefit in benefits" :key="benefit.title" class="rounded-2xl border border-black/5 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-[#0071e3]/10 text-[#0071e3]"><component :is="benefit.icon" class="h-5 w-5" /></div>
                        <h3 class="text-lg font-semibold text-[#1d1d1f]">{{ benefit.title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-[#6e6e73]">{{ benefit.detail }}</p>
                    </article>
                </div>
            </section>
        </main>

        <FloatingCsWidget />
        <MainFooter />
    </div>
</template>
