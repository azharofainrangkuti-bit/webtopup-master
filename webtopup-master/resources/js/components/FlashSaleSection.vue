<script setup lang="ts">
import { formatCurrency } from '@/lib/utils';
import { PPOBBrandDataItem } from '@/types/cms/ppob';
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Flame, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    featuredBrands?: PPOBBrandDataItem[];
}>();

const flashSaleItems = computed(() =>
    (props.featuredBrands || [])
        .filter((brand) => brand.products?.length)
        .slice(0, 4)
        .map((brand) => {
            const product = [...(brand.products || [])].sort(
                (first, second) => first.sell_price - second.sell_price,
            )[0];

            return {
                id: product.id,
                brand_name: brand.name,
                brand_slug: brand.slug,
                name: product.name,
                price: product.sell_price,
            };
        }),
);
</script>

<template>
    <section v-if="flashSaleItems.length" class="mb-8 overflow-hidden rounded-2xl border border-[#0071e3]/10 bg-gradient-to-r from-[#f8f9fb] via-white to-[#e6f0ff] p-5 shadow-md md:p-7 backdrop-blur-sm">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#ff6b35]/20 to-[#0071e3]/10 text-[#ff6b35] ring-2 ring-[#ff6b35]/15">
                    <Flame class="h-6 w-6 animate-pulse" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-lg font-bold text-[#1d1d1f] md:text-2xl tracking-tight">Trending Sekarang</h2>
                        <span class="rounded-full bg-gradient-to-r from-[#ff6b35] to-[#0071e3] px-3 py-1 text-[9px] font-black uppercase tracking-[0.15em] text-white shadow-md">Top</span>
                    </div>
                    <p class="text-xs text-[#6e6e73] mt-0.5">Game dan nominal terpopuler minggu ini.</p>
                </div>
            </div>

        </div>

    <!-- Popular Items Grid -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="item in flashSaleItems"
                :key="item.id"
                class="flash-item group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-black/4 bg-white/95 p-4 transition-all duration-400 hover:-translate-y-2 hover:border-[#0071e3]/30 hover:shadow-xl hover:shadow-[#0071e3]/15 hover:bg-white backdrop-blur-sm"
            >
                <div>
                    <div class="absolute right-3 top-3 z-10 rounded-full bg-gradient-to-r from-[#ff6b35] to-[#0071e3] px-2.5 py-1 text-[8px] font-black uppercase text-white shadow-lg">
                        Top Pick
                    </div>

                    <p class="mb-1.5 text-[9px] font-bold uppercase tracking-[0.15em] text-[#6e6e73]">
                        {{ item.brand_name }}
                    </p>
                    <h3 class="mb-3 line-clamp-2 text-sm font-bold text-[#1d1d1f] transition-all duration-300 group-hover:text-[#0071e3]">
                        {{ item.name }}
                    </h3>
                </div>

                <div>
                    <div class="mb-3">
                        <span class="text-sm font-bold text-[#0071e3]">
                            Mulai {{ formatCurrency(item.price) }}
                        </span>
                    </div>

                    <Link
                        :href="`/brand/${item.brand_slug}`"
                        class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-[#0071e3] to-[#0081f5] py-2.5 text-xs font-bold text-white transition-all duration-300 hover:shadow-lg hover:shadow-[#0071e3]/30 group-hover:scale-105"
                    >
                        <Sparkles class="h-3.5 w-3.5" /> Mulai <ArrowUpRight class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-1" />
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.flash-item {
    animation: flash-item-in 520ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.flash-item:nth-child(2) { animation-delay: 70ms; }
.flash-item:nth-child(3) { animation-delay: 140ms; }
.flash-item:nth-child(4) { animation-delay: 210ms; }

@keyframes flash-item-in {
    from { opacity: 0; transform: translateY(14px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (prefers-reduced-motion: reduce) {
    .flash-item { animation: none; }
}
</style>
