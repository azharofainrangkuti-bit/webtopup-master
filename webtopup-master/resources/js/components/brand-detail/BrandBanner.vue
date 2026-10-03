<script setup lang="ts">
import { getBrandVisual } from '@/lib/brandVisual';
import { PPOBBrandDataItem } from '@/types/cms/ppob';
import { Clock3, MessageCircle, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    brand: PPOBBrandDataItem;
}>();

const visual = computed(() => getBrandVisual(props.brand.name));
</script>

<template>
    <div
        class="relative min-h-72 overflow-hidden bg-[#061b36] sm:min-h-80"
    >
        <img
            v-if="brand.banner"
            :src="brand.banner"
            :alt="brand.name"
            class="h-full w-full object-cover"
            loading="eager"
            fetchpriority="high"
        />
        <div class="absolute inset-0 bg-[linear-gradient(100deg,rgba(3,18,40,.96)_0%,rgba(3,18,40,.72)_42%,rgba(3,18,40,.14)_100%)]"></div>
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full border-[28px] border-[#ff7058]/20"></div>
        <div class="absolute right-16 bottom-8 h-24 w-24 rounded-full bg-[#0877e8]/30 blur-2xl"></div>

        <!-- Brand Info Overlay -->
        <div class="absolute right-0 bottom-0 left-0 p-5 sm:p-8">
            <div class="mx-auto max-w-5xl">
                <div class="flex items-end gap-4 sm:gap-5">
                    <!-- Brand Logo -->
                    <div
                        class="h-16 w-16 overflow-hidden rounded-2xl border-4 border-white/90 bg-white shadow-2xl shadow-black/30 sm:h-24 sm:w-24"
                    >
                        <img
                            v-if="brand.image"
                            :src="brand.image"
                            :alt="brand.name"
                            class="h-full w-full object-cover"
                            loading="eager"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br text-foreground/70"
                            :class="visual.gradient"
                        >
                            <component :is="visual.icon" class="h-9 w-9 opacity-70" />
                        </div>
                    </div>

                    <!-- Brand Name & Features -->
                    <div class="flex-1">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.22em] text-[#7ed7ff]">Top up resmi & aman</p>
                        <h1 class="mb-3 text-2xl font-black text-white drop-shadow-sm md:text-4xl">
                            {{ brand.name }}
                        </h1>
                        <div class="flex flex-wrap gap-2">
                            <div
                                class="flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white backdrop-blur"
                            >
                                <Clock3 class="h-3 w-3" />
                                Proses Cepat
                            </div>
                            <div
                                class="flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white backdrop-blur"
                            >
                                <MessageCircle class="h-3 w-3" />
                                Layanan Chat 24/7
                            </div>
                            <div
                                class="flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white backdrop-blur"
                            >
                                <ShieldCheck class="h-3 w-3" />
                                Pembayaran Aman
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
