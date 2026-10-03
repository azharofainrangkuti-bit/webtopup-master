<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Megaphone } from 'lucide-vue-next';

const page = usePage();
const setting = page.props.setting as Record<string, any> | undefined;
const announcementText = setting?.announcement_text || 'Promo top up terbaru tersedia setiap hari. Proses cepat, status pesanan jelas.';
const props = withDefaults(defineProps<{ tone?: 'default' | 'storefront' }>(), {
    tone: 'storefront',
});
</script>

<template>
    <div
        class="relative overflow-hidden border-b border-white/10 py-2 text-white"
        :class="props.tone === 'storefront' ? 'bg-gradient-to-r from-[#061b36] via-[#0757b7] to-[#0877e8]' : 'bg-gradient-to-r from-sky-900 via-indigo-900 to-slate-900'"
    >
        <div class="mx-auto flex max-w-7xl items-center px-4 text-xs font-semibold">
            <div
                class="mr-3 flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.16em]"
                :class="props.tone === 'storefront' ? 'border-[#7ed7ff]/30 bg-[#7ed7ff]/15 text-[#bcecff]' : 'border-sky-400/30 bg-sky-500/20 text-sky-300'"
            >
                <Megaphone class="h-3.5 w-3.5" />
                <span>Info</span>
            </div>
            <div class="relative flex-1 overflow-hidden whitespace-nowrap">
                <div class="inline-block animate-marquee pl-4 text-white/90">
                    {{ announcementText }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes marquee {
    0% { transform: translateX(0%); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: inline-block;
    white-space: nowrap;
    animation: marquee 25s linear infinite;
}

@media (prefers-reduced-motion: reduce) {
    .animate-marquee {
        animation: none;
        white-space: normal;
    }
}
</style>
