<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, Copy, Percent, TicketPercent } from 'lucide-vue-next';
import { ref } from 'vue';

interface VoucherItem {
    id: number;
    code: string;
    name: string;
    description?: string | null;
    type: string;
    fixed_amount?: number | null;
    percentage?: number | string | null;
    start_date?: string | null;
    end_date?: string | null;
    min_purchase_amount: number;
    usage_limit: number;
    used_count: number;
}

const props = defineProps<{
    vouchers: VoucherItem[];
}>();

const page = usePage();
const setting = page.props.setting;
const copiedCode = ref<string | null>(null);

const discountLabel = (voucher: VoucherItem) => {
    if (voucher.type === 'PERCENTAGE') {
        return `Diskon ${voucher.percentage}%`;
    }

    return `Potongan ${formatCurrency(voucher.fixed_amount ?? 0)}`;
};

const copyCode = async (code: string) => {
    if (!navigator.clipboard) return;
    await navigator.clipboard.writeText(code);
    copiedCode.value = code;
    window.setTimeout(() => {
        if (copiedCode.value === code) copiedCode.value = null;
    }, 1800);
};

const formatDate = (date?: string | null) => {
    if (!date) return 'Tanpa batas waktu';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};
</script>

<template>
    <Head>
        <title>{{ `Promo Top Up Game - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />

        <main class="flex-1 px-4 py-8 md:px-6 lg:px-8">
            <section class="mb-8 overflow-hidden rounded-2xl border border-[#0071e3]/15 bg-gradient-to-br from-[#e8f1fb] via-white to-[#f5f5f7] p-6 shadow-sm md:p-10">
                <div class="max-w-2xl">
                    <span class="mb-4 inline-flex items-center gap-2 rounded-full bg-[#0071e3]/10 px-3 py-1 text-xs font-semibold text-[#0071e3]">
                        <TicketPercent class="h-4 w-4" />
                        Promo dan voucher aktif
                    </span>
                    <h1 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] md:text-5xl">Main lebih banyak, bayar lebih hemat.</h1>
                    <p class="mt-4 text-base leading-7 text-[#6e6e73]">Gunakan kode promo yang tersedia saat checkout untuk mendapatkan harga terbaik pada top up game favoritmu.</p>
                </div>
            </section>

            <section>
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-[#0071e3]">Penawaran terbaru</p>
                        <h2 class="text-2xl font-semibold text-[#1d1d1f]">Promo yang tersedia</h2>
                    </div>
                    <Link href="/top-up" class="hidden items-center gap-1 text-sm font-medium text-[#0071e3] hover:underline sm:inline-flex">
                        Lihat semua game <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div v-if="props.vouchers.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article v-for="voucher in props.vouchers" :key="voucher.id" class="flex flex-col justify-between rounded-2xl border border-black/5 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div>
                            <div class="mb-5 flex items-start justify-between gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0071e3]/10 text-[#0071e3]">
                                    <Percent v-if="voucher.type === 'PERCENTAGE'" class="h-5 w-5" />
                                    <TicketPercent v-else class="h-5 w-5" />
                                </div>
                                <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600">Tersedia</span>
                            </div>
                            <h3 class="text-xl font-semibold text-[#1d1d1f]">{{ voucher.name }}</h3>
                            <p class="mt-2 min-h-12 text-sm leading-6 text-[#6e6e73]">{{ voucher.description || discountLabel(voucher) }}</p>
                            <div class="mt-5 rounded-xl bg-[#f5f5f7] p-3">
                                <div class="text-2xl font-semibold text-[#0071e3]">{{ discountLabel(voucher) }}</div>
                                <div class="mt-1 text-xs text-[#6e6e73]">Minimum pembelian {{ formatCurrency(voucher.min_purchase_amount) }}</div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="flex items-center gap-2 text-xs text-[#6e6e73]">
                                <CalendarDays class="h-4 w-4" />
                                Berlaku sampai {{ formatDate(voucher.end_date) }}
                            </div>
                            <button type="button" class="flex min-h-11 w-full items-center justify-between rounded-xl border border-dashed border-[#0071e3]/40 bg-[#0071e3]/5 px-3 text-left transition hover:bg-[#0071e3]/10" @click="copyCode(voucher.code)">
                                <span class="font-mono text-sm font-semibold tracking-wider text-[#1d1d1f]">{{ voucher.code }}</span>
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-[#0071e3]">
                                    <Copy class="h-3.5 w-3.5" />
                                    {{ copiedCode === voucher.code ? 'Tersalin' : 'Salin kode' }}
                                </span>
                            </button>
                            <Link href="/top-up" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0071e3] px-4 text-sm font-semibold text-white transition hover:bg-[#0077ed]">
                                Gunakan di Top Up <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-black/10 bg-white p-12 text-center shadow-sm">
                    <TicketPercent class="mx-auto h-10 w-10 text-[#0071e3]" />
                    <h3 class="mt-4 text-lg font-semibold text-[#1d1d1f]">Belum ada promo aktif</h3>
                    <p class="mt-2 text-sm text-[#6e6e73]">Promo baru akan muncul di halaman ini saat tersedia.</p>
                    <Link href="/top-up" class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#0071e3] px-5 text-sm font-semibold text-white">Jelajahi Game <ArrowRight class="h-4 w-4" /></Link>
                </div>
            </section>
        </main>

        <FloatingCsWidget />
        <MainFooter />
    </div>
</template>
