<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Headphones } from 'lucide-vue-next';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const whatsappUrl = computed(() => {
    const rawPhone = String(page.props.setting?.phone ?? '').replace(/\D/g, '');
    const phone = rawPhone.startsWith('0') ? `62${rawPhone.slice(1)}` : rawPhone;

    return phone ? `https://wa.me/${phone}` : '#';
});
</script>

<template>
    <div class="mt-5 rounded-2xl border border-[#0877e8]/15 bg-gradient-to-br from-[#eef7ff] via-white to-[#fff8f4] p-5 shadow-md">
        <div class="flex items-start gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#0877e8] text-white shadow-md shadow-[#0877e8]/20"
            >
                <Headphones class="h-5 w-5 text-primary" />
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-sm font-bold text-[#1d1d1f]">
                    Perlu bantuan saat top up?
                </h3>
                <p class="mb-3 text-xs leading-5 text-[#6e6e73]">
                    Tim kami siap membantu jika kamu mengalami kendala.
                </p>
                <Button variant="outline" size="sm" class="w-full border-[#0877e8]/25 bg-white font-bold text-[#0757b7] hover:bg-[#0877e8] hover:text-white" as-child>
                    <a
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <Headphones class="mr-2 h-4 w-4" />
                        Hubungi CS
                    </a>
                </Button>
            </div>
        </div>
    </div>
</template>
