<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputDescription from '../InputDescription.vue';
import InputError from '../InputError.vue';

defineProps<{
    email: string;
    name: string;
    phone: string;
    formErrors: any;
}>();

const emit = defineEmits<{
    'update:email': [value: string];
    'update:name': [value: string];
    'update:phone': [value: string];
}>();
</script>

<template>
    <section class="rounded-2xl border border-[#0877e8]/10 bg-gradient-to-br from-white via-[#f8fbff] to-white p-5 shadow-md sm:p-6">
        <h2
            class="mb-5 flex items-center gap-3 text-lg font-bold text-[#1d1d1f]"
        >
            <span
                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#0877e8] to-[#0757b7] text-sm font-bold text-white shadow-md"
                >3</span
            >
            Detail Kontak
        </h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <Label for="name">Nama</Label>
                <Input
                    id="name"
                    :model-value="name"
                    placeholder="Nama lengkap"
                    class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                    @update:model-value="emit('update:name', String($event))"
                />
                <InputError :message="formErrors.name" class="mt-1" />
            </div>
            <div>
                <Label for="phone">Nomor Telepon</Label>
                <Input
                    id="phone"
                    :model-value="phone"
                    type="tel"
                    placeholder="08xxxxxxxxxx"
                    class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                    @update:model-value="emit('update:phone', String($event))"
                />
                <InputError :message="formErrors.phone" class="mt-1" />
                <InputDescription class="sm:col-span-2">
                    * Jika ada kendala, kami akan menghubungi nomor WA kamu diatas.
                </InputDescription>
            </div>
            <div class="sm:col-span-2">
                <Label for="email">Email (Optional)</Label>
                <Input
                    id="email"
                    :model-value="email"
                    type="email"
                    placeholder="email@example.com"
                    class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                    @update:model-value="emit('update:email', String($event))"
                />
                <InputError :message="formErrors.email" class="mt-1" />
                <InputDescription class="sm:col-span-2">
                    * Kami akan mengirimkan notifikasi transaksi melalui email jika kamu mengisi kolom ini.
                </InputDescription>
            </div>
        </div>
    </section>
</template>
