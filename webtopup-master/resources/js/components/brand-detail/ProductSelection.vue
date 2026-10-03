<script setup lang="ts">
import { formatCurrency } from '@/lib/utils';
import { PPOBProductDataItem } from '@/types/cms/ppob';
import { Check, ChevronDown, ChevronUp, ClockIcon, Sparkles } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    products: PPOBProductDataItem[];
    selectedProduct: number | null;
}>();

const emit = defineEmits<{
    'update:selectedProduct': [value: number];
}>();

const activeTab = ref<string>('all');
const productBatchSize = 12;
const visibleProductCount = ref(productBatchSize);

watch(
    () => activeTab.value,
    () => {
        visibleProductCount.value = productBatchSize;
    },
);

const categoryTabs = computed(() => {
    const categories = new Set<string>();
    categories.add('all');

    props.products?.forEach((p) => {
        const nameLower = p.name.toLowerCase();
        if (nameLower.includes('pass') || nameLower.includes('starlight') || nameLower.includes('membership') || nameLower.includes('weekly')) {
            categories.add('membership');
        } else if (nameLower.includes('promo') || nameLower.includes('bonus') || nameLower.includes('special')) {
            categories.add('promo');
        } else {
            categories.add('diamonds');
        }
    });

    return Array.from(categories);
});

const filteredProducts = computed(() => {
    if (!props.products) return [];
    if (activeTab.value === 'all') return props.products;

    return props.products.filter((p) => {
        const nameLower = p.name.toLowerCase();
        if (activeTab.value === 'membership') {
            return nameLower.includes('pass') || nameLower.includes('starlight') || nameLower.includes('membership') || nameLower.includes('weekly');
        }
        if (activeTab.value === 'promo') {
            return nameLower.includes('promo') || nameLower.includes('bonus') || nameLower.includes('special');
        }
        if (activeTab.value === 'diamonds') {
            return !nameLower.includes('pass') && !nameLower.includes('starlight') && !nameLower.includes('membership') && !nameLower.includes('weekly') && !nameLower.includes('promo');
        }
        return true;
    });
});

const visibleProducts = computed(() => {
    if (!filteredProducts.value) return [];

    return filteredProducts.value.slice(0, visibleProductCount.value);
});

const lowestPriceProductId = computed(() => {
    const availableProducts = props.products?.filter((product) => product.sell_price > 0) ?? [];

    return availableProducts.length
        ? availableProducts.reduce((lowest, product) => product.sell_price < lowest.sell_price ? product : lowest).id
        : null;
});

const hasMoreProducts = computed(() => filteredProducts.value.length > visibleProductCount.value);
const canShowFewerProducts = computed(() => visibleProductCount.value > productBatchSize);

const showMoreProducts = () => {
    visibleProductCount.value = Math.min(
        filteredProducts.value.length,
        visibleProductCount.value + productBatchSize,
    );
};

const showFewerProducts = () => {
    visibleProductCount.value = Math.max(
        productBatchSize,
        visibleProductCount.value - productBatchSize,
    );
};

const getTabLabel = (tab: string) => {
    switch (tab) {
        case 'all': return 'Semua Nominal';
        case 'diamonds': return 'Diamonds / Currency';
        case 'membership': return 'Pass & Membership';
        case 'promo': return 'Special Promo';
        default: return tab;
    }
};
</script>

<template>
    <section class="rounded-2xl border border-[#0071e3]/10 bg-gradient-to-br from-white via-[#f8f9fb] to-white p-6 shadow-md">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="flex items-center gap-3 text-lg font-bold text-[#1d1d1f]">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#0071e3] to-[#0081f5] text-sm font-bold text-white shadow-md">2</span>
                Pilih Nominal Produk
            </h2>

            <!-- Category Tabs -->
            <div class="flex flex-wrap gap-2" v-if="categoryTabs.length > 2" role="tablist" aria-label="Kategori nominal">
                <button
                    v-for="tab in categoryTabs"
                    :key="tab"
                    type="button"
                    @click="activeTab = tab"
                    :aria-selected="activeTab === tab"
                    role="tab"
                    class="rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0071e3] focus-visible:ring-offset-2"
                    :class="activeTab === tab ? 'bg-gradient-to-r from-[#0071e3] to-[#0081f5] text-white shadow-md' : 'bg-[#f0f0f0] text-[#6e6e73] hover:bg-[#e0e0e0]'"
                >
                    {{ getTabLabel(tab) }}
                </button>
            </div>
        </div>

        <div
            v-if="visibleProducts && visibleProducts.length > 0"
            class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
        >
            <button
                v-for="product in visibleProducts"
                :key="product.id"
                type="button"
                :aria-pressed="selectedProduct === product.id"
                class="group hover-lift relative flex h-full flex-col justify-between overflow-hidden rounded-2xl border-2 p-4 text-left transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0071e3] focus-visible:ring-offset-2"
                :class="
                    selectedProduct === product.id
                        ? 'border-[#0071e3] bg-[#0071e3]/8 shadow-lg ring-2 ring-[#0071e3]/20'
                        : 'border-[#e0e0e0] bg-white hover:border-[#0071e3]/40 hover:bg-[#f8f9fb] hover:shadow-md'
                "
                @click="emit('update:selectedProduct', product.id)"
            >
                <div v-if="lowestPriceProductId === product.id" class="absolute right-3 top-3 rounded-full bg-amber-100 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-amber-700 shadow-sm">
                    Terbaik
                </div>
                <div class="mb-3 flex items-start justify-between gap-2">
                    <span class="pr-14 text-sm font-bold text-[#1d1d1f] transition-colors group-hover:text-[#0071e3]">
                        {{ product.name }}
                    </span>
                    <Sparkles
                        v-if="selectedProduct === product.id"
                        class="h-4 w-4 shrink-0 animate-pulse text-[#0071e3]"
                    />
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-[#e0e0e0]">
                        <span class="text-base font-black tracking-tight text-[#0071e3]">
                            {{ formatCurrency(product.sell_price) }}
                        </span>
                        <span v-if="selectedProduct === product.id" class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-[#0071e3] to-[#0081f5] text-white shadow-md">
                            <Check class="h-3.5 w-3.5" />
                        </span>
                    </div>

                    <div
                        class="mt-2 flex items-center gap-1.5 text-[11px] text-amber-600"
                        v-if="product.delay"
                    >
                        <ClockIcon class="h-3.5 w-3.5" />
                        <span>Proses Delay</span>
                    </div>
                </div>
            </button>
        </div>
        <div
            v-if="filteredProducts.length > productBatchSize"
            class="mt-4 flex justify-center gap-3"
        >
            <button
                v-if="hasMoreProducts"
                type="button"
                @click="showMoreProducts"
                class="inline-flex items-center gap-2 rounded-xl border border-[#0877e8]/25 bg-[#0877e8]/5 px-4 py-2.5 text-sm font-semibold text-[#0757b7] transition hover:bg-[#0877e8]/10"
            >
                Tampilkan lebih banyak <ChevronDown class="h-4 w-4" />
            </button>
            <button
                v-if="canShowFewerProducts"
                type="button"
                @click="showFewerProducts"
                class="inline-flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f7fa] px-4 py-2.5 text-sm font-semibold text-[#6e6e73] transition hover:bg-[#eef1f5]"
            >
                Tampilkan lebih sedikit <ChevronUp class="h-4 w-4" />
            </button>
        </div>
        <div v-if="filteredProducts.length === 0" class="rounded-xl border border-dashed border-black/10 bg-[#f8f9fb] px-4 py-8 text-center text-sm text-[#6e6e73]">
            Belum ada nominal tersedia di kategori ini.
        </div>
    </section>
</template>
