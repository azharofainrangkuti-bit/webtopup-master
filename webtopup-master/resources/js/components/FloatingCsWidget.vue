<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { MessageCircle, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const isOpen = ref(false);

const whatsappUrl = computed(() => {
    const rawPhone = String(page.props.setting?.phone ?? '').replace(/\D/g, '');
    const phone = rawPhone.startsWith('0') ? `62${rawPhone.slice(1)}` : rawPhone;
    return phone ? `https://wa.me/${phone}?text=Halo%20CS%20${encodeURIComponent(page.props.setting?.title || 'GameStore')},%20saya%20butuh%20bantuan%20mengenai%20topup...` : '#';
});
</script>

<template>
    <div class="fixed right-4 bottom-4 z-50 flex flex-col items-end gap-3 sm:right-6 sm:bottom-6">
        <!-- Quick Popup Card -->
        <div
            v-if="isOpen"
            id="floating-cs-panel"
            class="w-[calc(100vw-2rem)] max-w-72 rounded-2xl border border-[#0877e8]/15 bg-white p-4 shadow-2xl transition-all duration-300 animate-in fade-in slide-in-from-bottom-5"
        >
            <div class="flex items-center justify-between pb-3 border-b border-border/50">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-bold text-foreground">Customer Service</span>
                </div>
                <button type="button" aria-label="Tutup bantuan customer service" @click="isOpen = false" class="rounded-lg p-1 text-[#6e6e73] transition hover:bg-[#f5f7fa] hover:text-[#1d1d1f]">
                    <X class="h-4 w-4" />
                </button>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-[#6e6e73]">
                Ada kendala atau pertanyaan seputar top up? Tim kami siap membantu.
            </p>
            <a
                :href="whatsappUrl"
                :aria-disabled="whatsappUrl === '#' ? 'true' : undefined"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-emerald-500 aria-disabled:pointer-events-none aria-disabled:opacity-50"
            >
                <MessageCircle class="h-4 w-4" />
                <span>Chat via WhatsApp</span>
            </a>
        </div>

        <!-- Floating Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            :aria-expanded="isOpen"
            aria-controls="floating-cs-panel"
            class="group flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-[0_10px_25px_rgba(16,185,129,0.4)] transition-all duration-300 hover:scale-110 hover:shadow-[0_15px_30px_rgba(16,185,129,0.6)]"
            aria-label="Customer Service WhatsApp"
        >
            <MessageCircle class="h-7 w-7 transition-transform group-hover:rotate-12" />
        </button>
    </div>
</template>
