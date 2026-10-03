<script setup lang="ts">
import { show } from '@/actions/App/Http/Controllers/Main/BrandController';
import { getBrandVisual } from '@/lib/brandVisual';
import { formatCurrency } from '@/lib/utils';
import { PPOBBrandDataItem } from '@/types/cms/ppob';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    brand: PPOBBrandDataItem;
}>();

const visual = computed(() => getBrandVisual(props.brand.name));
const startingPrice = computed(() => {
    const prices = (props.brand.products ?? [])
        .map((product) => product.sell_price)
        .filter((price) => price > 0);

    return prices.length ? Math.min(...prices) : null;
});
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl border border-black/6 bg-white shadow-md transition-all duration-400 hover:-translate-y-2 hover:border-[#0071e3]/30 hover:shadow-2xl hover:shadow-[#0071e3]/10"
    >
        <Link
            :href="
                show({
                    brand: brand.slug,
                }).url
            "
            class="block rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0071e3] focus-visible:ring-offset-2"
        >
            <div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-br from-[#f8f9fa] via-white to-[#e3f0ff]">
                <img
                    v-if="brand.image"
                    :src="brand.image"
                    :alt="brand.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br text-foreground/70"
                    :class="visual.gradient"
                >
                    <component :is="visual.icon" class="h-12 w-12 opacity-70" />
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/88 via-slate-950/15 to-transparent transition-opacity duration-400 group-hover:from-slate-950/96" />
                <div class="pointer-events-none absolute -inset-y-10 -left-1/2 w-1/3 rotate-[20deg] bg-white/30 blur-2xl transition-all duration-700 group-hover:translate-x-[380%] group-hover:opacity-80" />

                <div class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.14em] text-[#0071e3] shadow-md backdrop-blur-sm">
                    Top Up
                </div>

                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/95 to-transparent p-4 pt-6 transition-all duration-400">
                    <p class="mb-1.5 text-[9px] uppercase tracking-[0.2em] font-semibold text-slate-200/80">
                        {{ brand.category?.name || 'Game' }}
                    </p>
                    <h3 class="line-clamp-2 text-base font-bold text-white leading-tight">
                        {{ brand.name }}
                    </h3>
                    <div class="mt-4 flex items-end justify-between gap-2">
                        <div v-if="startingPrice" class="text-[11px] text-white/85">
                            Mulai dari <span class="font-bold text-white text-sm">{{ formatCurrency(startingPrice) }}</span>
                        </div>
                        <span class="ml-auto inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-[#0071e3] transition-all duration-300 group-hover:bg-[#0071e3] group-hover:text-white group-hover:scale-110 shadow-lg">
                            <ArrowRight class="h-4 w-4" />
                        </span>
                    </div>
                </div>
            </div>
        </Link>
    </div>
</template>
