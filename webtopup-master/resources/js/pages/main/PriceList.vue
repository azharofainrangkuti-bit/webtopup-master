<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Input } from '@/components/ui/input';
import { getBrandVisual } from '@/lib/brandVisual';
import { PPOBBrandDataItem, PPOBCategoryDataItem, PPOBProductDataItem } from '@/types/cms/ppob';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle2, Search, ShoppingBag, Tag, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    categories: PPOBCategoryDataItem[];
}>();

const page = usePage();
const setting = page.props.setting;

const searchQuery = ref('');
const selectedCategory = ref<string>('all');

const filteredCategories = computed(() => {
    return props.categories.map((cat: PPOBCategoryDataItem) => {
        const filteredBrands = cat.active_brands?.map((brand: PPOBBrandDataItem) => {
            const filteredProducts = brand.products?.filter((prod: PPOBProductDataItem) => {
                const matchSearch =
                    !searchQuery.value ||
                    brand.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                    prod.name.toLowerCase().includes(searchQuery.value.toLowerCase());
                return matchSearch;
            });
            return {
                ...brand,
                products: filteredProducts || [],
            };
        }).filter((b: PPOBBrandDataItem) => b.products && b.products.length > 0);

        return {
            ...cat,
            active_brands: filteredBrands || [],
        };
    }).filter((cat: PPOBCategoryDataItem) => {
        const matchCategory = selectedCategory.value === 'all' || String(cat.id) === selectedCategory.value || cat.slug === selectedCategory.value;
        return matchCategory && cat.active_brands && cat.active_brands.length > 0;
    });
});

const formatPrice = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <Head>
        <title>{{ `Daftar Harga & Price List - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 md:px-6 lg:px-8">
            <!-- Header Section -->
            <section class="mb-8 rounded-[24px] border border-primary/20 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 p-6 text-white shadow-xl md:p-10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-500/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-sky-300 mb-3">
                            <Tag class="h-3.5 w-3.5" /> Price List Resmi
                        </span>
                        <h1 class="text-3xl font-black md:text-4xl text-white">Daftar Harga Game</h1>
                        <p class="mt-2 text-sm text-slate-300 max-w-xl">
                            Cek daftar produk dan harga terupdate secara real-time. Dapatkan penawaran terbaik untuk semua game favoritmu.
                        </p>
                    </div>

                    <!-- Search Input -->
                    <div class="w-full md:w-80">
                        <div class="relative">
                            <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                type="search"
                                placeholder="Cari game atau nama produk..."
                                class="pl-10 bg-slate-900/90 border-slate-700 text-white placeholder:text-slate-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="mt-6 flex flex-wrap gap-2 pt-4 border-t border-white/10">
                    <button
                        @click="selectedCategory = 'all'"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition"
                        :class="selectedCategory === 'all' ? 'bg-primary text-primary-foreground shadow' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                    >
                        Semua Kategori
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectedCategory = String(cat.id)"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition flex items-center gap-2"
                        :class="selectedCategory === String(cat.id) ? 'bg-primary text-primary-foreground shadow' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                    >
                        <span>{{ cat.name }}</span>
                    </button>
                </div>
            </section>

            <!-- Price Table List -->
            <section class="space-y-8">
                <div v-if="filteredCategories.length === 0" class="rounded-2xl border border-border/60 bg-card p-12 text-center">
                    <p class="text-base font-semibold text-foreground">Tidak ada produk ditemukan</p>
                    <p class="text-sm text-muted-foreground mt-1">Coba kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
                </div>

                <div
                    v-for="cat in filteredCategories"
                    :key="cat.id"
                    class="rounded-2xl border border-border/60 bg-card overflow-hidden shadow-sm"
                >
                    <div class="bg-muted/60 px-6 py-4 border-b border-border/50 flex items-center gap-3">
                        <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="h-6 w-6 object-contain" />
                        <h2 class="text-lg font-bold text-foreground">{{ cat.name }}</h2>
                    </div>

                    <div class="divide-y divide-border/40">
                        <div v-for="brand in cat.active_brands" :key="brand.id" class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <img
                                    v-if="brand.image"
                                    :src="brand.image"
                                    :alt="brand.name"
                                    class="h-10 w-10 rounded-xl object-cover border border-border/50"
                                />
                                <div
                                    v-else
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-border/50 bg-gradient-to-br"
                                    :class="getBrandVisual(brand.name).gradient"
                                >
                                    <component :is="getBrandVisual(brand.name).icon" class="h-5 w-5 opacity-70" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-foreground">{{ brand.name }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ brand.products?.length || 0 }} varian nominal</p>
                                </div>
                                <Link
                                    :href="`/brand/${brand.slug}`"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-1.5 text-xs font-semibold text-primary-foreground hover:bg-primary/90 transition"
                                >
                                    <ShoppingBag class="h-3.5 w-3.5" /> Beli
                                </Link>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-muted/40 text-xs uppercase font-semibold text-muted-foreground">
                                        <tr>
                                            <th class="px-4 py-2.5 rounded-l-lg">Produk / Nominal</th>
                                            <th class="px-4 py-2.5">Provider</th>
                                            <th class="px-4 py-2.5">Harga</th>
                                            <th class="px-4 py-2.5 rounded-r-lg text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/30">
                                        <tr v-for="prod in brand.products" :key="prod.id" class="hover:bg-muted/20 transition-colors">
                                            <td class="px-4 py-3 font-medium text-foreground">{{ prod.name }}</td>
                                            <td class="px-4 py-3 text-xs text-muted-foreground uppercase">{{ prod.provider || brand.provider }}</td>
                                            <td class="px-4 py-3 font-bold text-sky-600 dark:text-sky-400">{{ formatPrice(prod.sell_price) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <span v-if="prod.status" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                    <CheckCircle2 class="h-3.5 w-3.5" /> Tersedia
                                                </span>
                                                <span v-else class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500">
                                                    <XCircle class="h-3.5 w-3.5" /> Gangguan
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <FloatingCsWidget />

        <MainFooter />
    </div>
</template>
