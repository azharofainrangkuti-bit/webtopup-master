<script setup lang="ts">
import { index } from '@/actions/App/Http/Controllers/Main/HomeController';
import AnnouncementBar from '@/components/AnnouncementBar.vue';
import BrandCard from '@/components/BrandCard.vue';
import FlashSaleSection from '@/components/FlashSaleSection.vue';
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import HeroBanner from '@/components/HeroBanner.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import Maintenance from '@/pages/main/Maintenance.vue';
import { check } from '@/routes/transaction';
import { PaginationItem } from '@/types';
import { PPOBBrandDataItem, PPOBCategoryDataItem } from '@/types/cms/ppob';
import { SliderDataItem } from '@/types/cms/web';
import { Head, InfiniteScroll, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, ChevronDown, ChevronLeft, ChevronRight, ChevronUp, CreditCard, Gamepad2, MessageCircle, SearchX, ShieldCheck, Sparkles, UserRound, WalletCards, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatCurrency } from '@/lib/utils';

const props = defineProps<{
    sliders: SliderDataItem[];
    brands: PaginationItem<PPOBBrandDataItem>;
    featured_brands: PPOBBrandDataItem[];
    categories: PPOBCategoryDataItem[];
    faqs: Array<{ id: number; question: string; answer: string }>;
    total_brands?: number;
}>();

const page = usePage();
const setting = page.props.setting;

const brandBatchSize = 12;
const visibleBrandCount = ref(brandBatchSize);
const supportUrl = computed(() => {
    const rawPhone = String(setting?.phone ?? '').replace(/\D/g, '');
    const phone = rawPhone.startsWith('0') ? `62${rawPhone.slice(1)}` : rawPhone;

    return phone ? `https://wa.me/${phone}` : '#';
});
const ecosystemServices = [
    { title: 'Pembayaran Fleksibel', detail: 'Transfer bank, e-wallet, dan QRIS', icon: WalletCards },
    { title: 'Proses Terverifikasi', detail: 'Status order jelas dan bisa dicek kapan saja', icon: ShieldCheck },
    { title: 'Support & Reseller', detail: 'Bantuan cepat untuk gamer dan partner', icon: MessageCircle },
];
const purchaseSteps = [
    { number: '01', title: 'Pilih game', icon: Gamepad2 },
    { number: '02', title: 'Masukkan ID', icon: UserRound },
    { number: '03', title: 'Bayar aman', icon: CreditCard },
];
const storefrontStats = computed(() => [
    { value: `${props.total_brands ?? props.brands?.total ?? props.brands?.data?.length ?? 0}+`, label: 'Game & layanan' },
    { value: `${props.categories?.length ?? 0}`, label: 'Kategori tersedia' },
    { value: '24/7', label: 'Bisa top up kapan saja' },
]);
const quickCategories = computed(() => props.categories?.slice(0, 6) ?? []);
const promoCarouselItems = computed(() =>
    (props.featured_brands || [])
        .filter((brand) => brand.products?.length)
        .slice(0, 4)
        .map((brand) => {
            const product = [...(brand.products || [])].sort(
                (first, second) => first.sell_price - second.sell_price,
            )[0];

            return {
                id: brand.id,
                name: brand.name,
                slug: brand.slug,
                image: brand.image,
                category: brand.category?.name ?? 'Top Up',
                price: product?.sell_price ?? 0,
            };
        }),
);
const promoCarouselIndex = ref(0);
const topSellerBrands = computed(() => {
    const list = [...(props.featured_brands || []), ...((props.brands?.data || []))];

    return list
        .filter((brand, index, array) => (
            brand &&
            array.findIndex((entry) => entry?.id === brand.id) === index
        ))
        .slice(0, 5)
        .map((brand, index) => {
            const prices = (brand.products || []).map((product) => product.sell_price);

            return {
                ...brand,
                rank: index + 1,
                minPrice: prices.length ? Math.min(...prices) : 0,
            };
        });
});
const nextPromoSlide = () => {
    if (!promoCarouselItems.value.length) return;
    promoCarouselIndex.value = (promoCarouselIndex.value + 1) % promoCarouselItems.value.length;
};
const previousPromoSlide = () => {
    if (!promoCarouselItems.value.length) return;
    promoCarouselIndex.value = (promoCarouselIndex.value - 1 + promoCarouselItems.value.length) % promoCarouselItems.value.length;
};
const selectedCategorySlug = computed(() => {
    const url = new URL(page.url, window.location.origin);

    return url.searchParams.get('category') ?? '';
});
const searchQuery = computed(() => {
    const url = new URL(page.url, window.location.origin);

    return url.searchParams.get('search')?.trim() ?? '';
});
const selectedCategory = computed(() => props.categories?.find((category) => category.slug === selectedCategorySlug.value));
const visibleBrands = computed(() =>
    (props.brands?.data || []).slice(0, visibleBrandCount.value),
);
const hasMoreBrands = computed(
    () => (props.brands?.data || []).length > visibleBrandCount.value,
);
const canShowFewerBrands = computed(
    () => visibleBrandCount.value > brandBatchSize,
);
const showMoreBrands = () => {
    visibleBrandCount.value = Math.min(
        props.brands?.data?.length || 0,
        visibleBrandCount.value + brandBatchSize,
    );
};
const showFewerBrands = () => {
    visibleBrandCount.value = Math.max(
        brandBatchSize,
        visibleBrandCount.value - brandBatchSize,
    );
};
</script>

<template>
    <Head>
        <title>{{ setting?.title }}</title>
        <component :is="'script'" type="application/ld+json">
            {{
                JSON.stringify({
                    '@context': 'https://schema.org',
                    '@type': 'WebSite',
                    name: setting?.title,
                    url: index().url,
                    potentialAction: {
                        '@type': 'SearchAction',
                        target: {
                            '@type': 'EntryPoint',
                            urlTemplate: `${index().url}?search={search_term_string}`,
                        },
                        'query-input': 'required name=search_term_string',
                    },
                })
            }}
        </component>
    </Head>

    <div class="public-shell apple-shell flex min-h-screen flex-col bg-background text-foreground">
        <MainHeader :show-search="true" />
        <AnnouncementBar tone="storefront" />

        <template v-if="setting?.maintenance_status === 'active'">
            <Maintenance />
        </template>

        <main class="mx-auto w-full max-w-[1320px] flex-1 px-4 py-5 md:px-6 lg:px-8" v-else>
            <section class="hero-panel relative mb-6 overflow-hidden rounded-[32px] border border-black/4 bg-[radial-gradient(circle_at_top_left,_rgba(90,200,250,0.25),_transparent_40%),linear-gradient(135deg,#f9fafb_0%,#ffffff_50%,#e8f2ff_100%)] text-[#1d1d1f] shadow-xl shadow-[#0071e3]/10 ring-1 ring-white/80">
                <div class="relative z-10 grid gap-8 px-5 py-6 md:grid-cols-[1.15fr_0.85fr] md:px-8 md:py-8 lg:px-10">
                    <div class="flex flex-col justify-center">
                        <div class="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-[#0071e3]/25 bg-[#0071e3]/12 px-3.5 py-1.5 text-[9px] font-bold uppercase tracking-[0.2em] text-[#0071e3] shadow-sm backdrop-blur-sm">
                            <Sparkles class="h-3.5 w-3.5" />
                            {{ setting?.title || 'GameStore' }}
                        </div>

                        <h1 class="max-w-2xl text-3xl font-black leading-[1.15] md:text-5xl lg:text-6xl text-[#1d1d1f]">
                            Top up <span class="bg-gradient-to-r from-[#0071e3] to-[#0081f5] bg-clip-text text-transparent">favorit</span>, lanjut main
                        </h1>

                        <p class="mt-5 max-w-lg text-sm text-[#6e6e73] md:text-base leading-relaxed">
                            Ratusan game, metode bayar beragam, proses instan. Transaksi aman dengan verifikasi akurat dan status real-time untuk pengalaman berbelanja terpercaya.
                        </p>

                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            <Link :href="index().url + '#produk-unggulan'" class="hero-cta inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#0071e3] to-[#0081f5] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#0071e3]/30 transition-all duration-300 hover:shadow-xl hover:shadow-[#0071e3]/40">
                                Mulai Top Up <ArrowRight class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                            </Link>
                            <Link
                                :href="check().url"
                                class="inline-flex items-center gap-2 rounded-lg border border-black/12 bg-white/80 px-6 py-3.5 text-sm font-bold text-[#1d1d1f] transition-all duration-300 hover:bg-white hover:border-[#0071e3]/20 hover:shadow-md"
                            >
                                <span>Cek Pesanan</span>
                            </Link>
                        </div>

                        <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-black/10 pt-5">
                            <div v-for="step in purchaseSteps" :key="step.number" class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1d1d1f] text-[10px] font-bold text-white">{{ step.number }}</span>
                                <component :is="step.icon" class="h-4 w-4 text-[#0071e3]" />
                                <span class="text-xs font-semibold text-[#424245]">{{ step.title }}</span>
                            </div>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-xs text-[#6e6e73]">
                            <span v-for="stat in storefrontStats" :key="stat.label" class="inline-flex items-baseline gap-1.5">
                                <strong class="text-sm font-black text-[#1d1d1f]">{{ stat.value }}</strong>
                                {{ stat.label }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        <div class="w-full max-w-md rounded-2xl border border-black/5 bg-white/80 p-3 shadow-xl shadow-black/10 backdrop-blur-sm">
                            <HeroBanner
                                v-if="sliders.length"
                                class="overflow-hidden rounded-2xl"
                                :slides="sliders"
                                :autoplay="true"
                                :interval="5000"
                            />
                            <div v-else class="relative flex aspect-[3/1] items-center overflow-hidden rounded-xl bg-[linear-gradient(135deg,#071a35,#0d5cab_58%,#54c7ec)] p-6 text-white">
                                <div class="absolute -right-8 -top-10 h-36 w-36 rounded-full border-[18px] border-white/10" />
                                <div class="relative">
                                    <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-200"><Zap class="h-4 w-4" /> Cepat & praktis</div>
                                    <p class="max-w-xs text-xl font-black leading-tight">Isi ulang game favorit tanpa antre.</p>
                                    <p class="mt-2 text-xs text-white/90">Promo dan katalog terbaru tersedia setiap hari.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="quickCategories.length" class="mb-8 rounded-2xl border border-[#0071e3]/10 bg-white/95 p-5 shadow-md md:p-6 backdrop-blur-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5"><Gamepad2 class="h-5 w-5 text-[#0071e3]" /><h2 class="text-base font-bold text-[#1d1d1f]">Mau isi ulang apa hari ini?</h2></div>
                    <Link :href="index().url + '#produk-unggulan'" class="inline-flex items-center gap-1 text-xs font-bold text-[#0071e3] transition-colors hover:text-[#0081f5]">Semua kategori <ArrowRight class="h-3.5 w-3.5" /></Link>
                </div>
                <div class="flex gap-2.5 overflow-x-auto pb-2">
                    <Link v-for="category in quickCategories" :key="category.id" :href="index().url + '?category=' + category.slug" preserve-scroll class="flex shrink-0 items-center gap-2.5 rounded-xl border border-black/6 bg-[#f8f9fb] px-4 py-2.5 text-xs font-semibold text-[#424245] transition-all duration-300 hover:border-[#0071e3]/30 hover:bg-[#eef6ff] hover:text-[#0071e3] hover:shadow-md">
                        <img v-if="category.image" :src="category.image" :alt="category.name" class="h-5 w-5 rounded-md object-contain" loading="lazy" />
                        <Gamepad2 v-else class="h-4 w-4 text-[#0071e3]" />
                        {{ category.name }}
                    </Link>
                </div>
            </section>

            <!-- Flash Sale Section -->
            <FlashSaleSection :featured-brands="featured_brands" />

            <section v-if="promoCarouselItems.length" id="promo" v-reveal class="mb-8 rounded-[28px] border border-[#ff7058]/15 bg-gradient-to-br from-white via-[#fffaf8] to-[#f5f8fc] p-5 shadow-md md:p-7">
                <div class="mb-5 flex items-end justify-between gap-3">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-[#ff7058]">Promo hari ini</p>
                        <h2 class="text-2xl font-black text-foreground">Pilihan terbaik untukmu</h2>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" aria-label="Promo sebelumnya" title="Promo sebelumnya" class="flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-foreground transition hover:border-primary hover:text-primary" @click="previousPromoSlide"><ChevronLeft class="h-4 w-4" /></button>
                        <button type="button" aria-label="Promo berikutnya" title="Promo berikutnya" class="flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-foreground transition hover:border-primary hover:text-primary" @click="nextPromoSlide"><ChevronRight class="h-4 w-4" /></button>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr]">
                    <div class="overflow-hidden rounded-[28px] border border-border/60 bg-slate-950">
                        <div class="relative">
                            <img
                                v-if="promoCarouselItems[promoCarouselIndex]?.image"
                                :src="promoCarouselItems[promoCarouselIndex].image"
                                :alt="promoCarouselItems[promoCarouselIndex].name"
                                class="h-72 w-full object-cover opacity-90"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent" />
                            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                                <div class="mb-2 inline-flex rounded-full bg-[#ff7058] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white shadow-lg shadow-[#ff7058]/20">Promo</div>
                                <h3 class="text-2xl font-black">{{ promoCarouselItems[promoCarouselIndex]?.name }}</h3>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-[10px] uppercase tracking-[0.2em] text-slate-200">{{ promoCarouselItems[promoCarouselIndex]?.category }}</div>
                                        <div class="text-lg font-bold text-[#7ed7ff]">Mulai {{ formatCurrency(promoCarouselItems[promoCarouselIndex]?.price ?? 0) }}</div>
                                    </div>
                                    <Link
                                        :href="`/brand/${promoCarouselItems[promoCarouselIndex]?.slug}`"
                                        class="inline-flex items-center justify-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                                    >
                                        Beli
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <h3 class="text-lg font-black text-foreground">Top Seller</h3>
                            <Link href="#produk-unggulan" class="text-xs font-semibold text-primary hover:underline">Lihat semua</Link>
                        </div>
                        <div v-for="brand in topSellerBrands" :key="brand.id" class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/40 p-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-sm font-black text-primary">#{{ brand.rank }}</div>
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <img v-if="brand.image" :src="brand.image" :alt="brand.name" class="h-10 w-10 rounded-lg object-cover" loading="lazy" />
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-bold text-foreground">{{ brand.name }}</div>
                                    <div class="text-xs text-[#52525b]">Mulai {{ formatCurrency(brand.minPrice) }}</div>
                                </div>
                            </div>
                            <Link :href="`/brand/${brand.slug}`" class="text-sm font-semibold text-primary">Beli</Link>
                        </div>
                    </div>
                </div>
            </section>

            <section v-reveal class="mb-8 grid gap-3 md:grid-cols-3">
                <div
                    v-for="service in ecosystemServices"
                    :key="service.title"
                    class="group rounded-2xl border border-black/5 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#0877e8]/20 hover:shadow-lg"
                >
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0877e8]/15 to-[#ff7058]/15 text-[#0877e8] transition group-hover:bg-[#0877e8] group-hover:text-white">
                        <component :is="service.icon" class="h-5 w-5" />
                    </div>
                    <h2 class="mb-1 text-base font-semibold text-[#1d1d1f]">{{ service.title }}</h2>
                    <p class="text-sm leading-6 text-[#6e6e73]">{{ service.detail }}</p>
                    <a
                        v-if="service.title === 'Support & Reseller'"
                        :href="supportUrl"
                        target="_blank"
                        rel="noreferrer"
                        class="mt-3 inline-flex text-sm font-medium text-[#0071e3] hover:underline"
                    >
                        Hubungi sekarang <ArrowRight class="ml-1 h-3.5 w-3.5" />
                    </a>
                </div>
            </section>

            <section v-if="faqs?.length" v-reveal class="mb-8 rounded-2xl border border-black/5 bg-[#f8fbff] p-5 shadow-sm md:p-7">
                <div class="mb-5">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-[#0071e3]">Bantuan</p>
                    <h2 class="text-2xl font-semibold text-[#1d1d1f]">Pertanyaan yang sering ditanyakan</h2>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <details v-for="faq in faqs" :key="faq.id" class="group rounded-xl border border-black/5 bg-[#f5f5f7] p-4 open:bg-[#eef6ff]">
                        <summary class="cursor-pointer list-none pr-6 text-sm font-semibold text-[#1d1d1f] marker:hidden">
                            <span class="relative block after:absolute after:right-0 after:top-0 after:text-lg after:font-normal after:text-[#0071e3] after:content-['+'] group-open:after:content-['−']">{{ faq.question }}</span>
                        </summary>
                        <p class="pt-3 text-sm leading-6 text-[#6e6e73]">{{ faq.answer }}</p>
                    </details>
                </div>
            </section>

            <section v-reveal class="mb-8 rounded-[28px] border border-[#0877e8]/10 bg-white p-5 shadow-md md:p-7">
                <div class="mb-5 flex items-end justify-between gap-3">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-[#0877e8]">Kategori Vipayment</p>
                        <h2 class="text-2xl font-black text-foreground">Jelajahi layanan berdasarkan kategori</h2>
                    </div>
                    <Link href="/price-list" class="text-sm font-semibold text-primary hover:underline">Lihat Daftar Harga</Link>
                </div>

                <div class="flex snap-x gap-3 overflow-x-auto pb-2 sm:grid sm:grid-cols-2 sm:overflow-visible lg:grid-cols-3 xl:grid-cols-4">
                    <Link :href="index().url" preserve-scroll class="group min-w-[220px] shrink-0 snap-start overflow-hidden rounded-[24px] border border-border/60 bg-gradient-to-br from-primary/10 to-transparent p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md sm:min-w-0 sm:shrink">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 text-primary shadow-inner shadow-primary/15"><Gamepad2 class="h-6 w-6" /></div>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-base font-black text-foreground">Semua layanan</div>
                                <div class="text-xs text-[#52525b]">{{ total_brands || categories?.reduce((sum, c) => sum + (c.active_brands_count || 0), 0) }} pilihan siap dibeli</div>
                            </div>
                            <ArrowRight class="h-5 w-5 text-primary transition group-hover:translate-x-1" />
                        </div>
                    </Link>

                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="index().url + '?category=' + category.slug"
                        preserve-scroll
                        class="group min-w-[220px] shrink-0 snap-start overflow-hidden rounded-[24px] border border-border/60 bg-gradient-to-br from-background to-muted/30 p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md sm:min-w-0 sm:shrink"
                    >
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-2xl">
                            <img v-if="category.image" :src="category.image" :alt="category.name" class="h-8 w-8 object-contain" loading="lazy" />
                            <Gamepad2 v-else class="h-6 w-6 text-primary" />
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-base font-black text-foreground">{{ category.name }}</div>
                                <div class="text-xs text-[#52525b]">{{ category.active_brands_count }} produk</div>
                            </div>
                            <ArrowRight class="h-5 w-5 text-primary transition group-hover:translate-x-1" />
                        </div>
                    </Link>
                </div>
            </section>

            <section id="produk-unggulan" v-reveal class="mb-8" v-if="featured_brands.length > 0">
                <div class="mb-5 flex items-end justify-between gap-3">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-[#0877e8]">Terlaris</p>
                        <h2 class="text-2xl font-black text-foreground">Produk Unggulan</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    <BrandCard
                        v-for="brand in featured_brands"
                        :key="brand.id"
                        :brand="brand"
                    />
                </div>
            </section>

            <section v-reveal>
                <div class="mb-5 flex items-end justify-between gap-3">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-[#0877e8]">Semua game</p>
                        <h2 class="text-2xl font-black text-foreground">
                            {{ searchQuery ? `Hasil pencarian “${searchQuery}”` : selectedCategory ? `Top up ${selectedCategory.name}` : 'Produk Populer' }}
                        </h2>
                    </div>
                </div>

                <InfiniteScroll v-if="visibleBrands.length" data="brands">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        <BrandCard
                            v-for="brand in visibleBrands"
                            :key="brand.id"
                            :brand="brand"
                        />
                    </div>
                </InfiniteScroll>

                <div v-else class="rounded-2xl border border-dashed border-border bg-muted/30 px-5 py-10 text-center">
                    <SearchX class="mx-auto mb-3 h-8 w-8 text-muted-foreground" />
                    <h3 class="text-base font-bold text-foreground">Game belum ditemukan</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-[#52525b]">Coba kata kunci lain atau lihat semua game yang tersedia di katalog kami.</p>
                    <Link :href="index().url" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                        <ArrowLeft class="h-4 w-4" /> Lihat semua game
                    </Link>
                </div>

                <div v-if="(brands?.data?.length || 0) > brandBatchSize" class="mt-6 flex justify-center gap-3">
                    <button
                        v-if="hasMoreBrands"
                        type="button"
                        class="rounded-full border border-primary/30 px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/10"
                        @click="showMoreBrands"
                    >
                        <ChevronDown class="mr-2 inline-block h-4 w-4" />
                        Tampilkan lebih banyak
                    </button>
                    <button
                        v-if="canShowFewerBrands"
                        type="button"
                        class="rounded-full border border-border bg-muted/50 px-5 py-2.5 text-sm font-semibold text-[#52525b] transition hover:bg-muted"
                        @click="showFewerBrands"
                    >
                        <ChevronUp class="mr-2 inline-block h-4 w-4" />
                        Tampilkan lebih sedikit
                    </button>
                </div>
            </section>
        </main>

        <FloatingCsWidget />

        <MainFooter />
    </div>
</template>

<style>
.apple-shell {
    --background: hsl(0 0% 98%);
    --foreground: hsl(240 6% 10%);
    --card: hsl(0 0% 100%);
    --card-foreground: hsl(240 6% 10%);
    --muted: hsl(240 5% 96%);
    --muted-foreground: hsl(240 4% 46%);
    --border: hsl(240 5% 88%);
    --input: hsl(240 5% 94%);
    --primary: hsl(211 100% 50%);
    --primary-foreground: hsl(0 0% 100%);
    --accent: hsl(211 100% 50%);
    --accent-foreground: hsl(0 0% 100%);
    --ring: hsl(211 100% 50%);
    background-image: radial-gradient(circle at 8% 0%, rgba(90, 200, 250, 0.08), transparent 24rem), linear-gradient(180deg, #fbfbfd 0%, #f7f8fa 100%);
}

.apple-shell main > section:not(:first-child) {
    animation: storefront-reveal 500ms ease-out both;
}

.apple-shell main > section:nth-child(3) { animation-delay: 60ms; }
.apple-shell main > section:nth-child(4) { animation-delay: 100ms; }
.apple-shell main > section:nth-child(5) { animation-delay: 140ms; }

.hero-panel::after {
    position: absolute;
    inset: -45%;
    z-index: 0;
    background: linear-gradient(115deg, transparent 38%, rgba(255, 255, 255, 0.55) 49%, transparent 60%);
    content: '';
    pointer-events: none;
    animation: hero-sheen 14s ease-in-out infinite;
}

.hero-cta {
    animation: cta-breathe 4s ease-in-out infinite;
}

@keyframes hero-sheen {
    0%, 35% { transform: translateX(-35%) rotate(8deg); opacity: 0; }
    48% { opacity: 0.7; }
    65%, 100% { transform: translateX(35%) rotate(8deg); opacity: 0; }
}

@keyframes cta-breathe {
    0%, 100% { box-shadow: 0 12px 28px rgba(0, 113, 227, 0.16); }
    50% { box-shadow: 0 16px 34px rgba(0, 113, 227, 0.26); }
}

@keyframes storefront-reveal {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .apple-shell main > section:not(:first-child),
    .hero-panel::after,
    .hero-cta {
        animation: none;
    }
}
</style>
