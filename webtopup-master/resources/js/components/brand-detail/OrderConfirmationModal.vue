<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { formatCurrency } from '@/lib/utils';
import { PPOBProductDataItem } from '@/types/cms/ppob';
import { CheckCircle2, ShieldCheck, UserCheck } from 'lucide-vue-next';

defineProps<{
    isOpen: boolean;
    brandName: string;
    accountId: string;
    serverId?: string;
    labelId: string;
    labelServer: string;
    resolvedUsername?: string | null;
    product: PPOBProductDataItem | null;
    paymentMethodName: string;
    contactName: string;
    contactPhone: string;
    totalAmount: number;
    isLoading: boolean;
}>();

const emit = defineEmits<{
    'update:isOpen': [value: boolean];
    confirm: [];
}>();
</script>

<template>
    <Dialog :open="isOpen" @update:open="(val) => emit('update:isOpen', val)">
        <DialogContent class="sm:max-w-md rounded-2xl p-6">
            <DialogHeader class="text-left">
                <div class="mb-2 inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <ShieldCheck class="h-4 w-4" /> konfirmasi Pesanan
                </div>
                <DialogTitle class="text-xl font-extrabold text-[#1d1d1f]">
                    Periksa Kembali Pesanan Anda
                </DialogTitle>
                <DialogDescription class="text-xs leading-5 text-[#6e6e73]">
                    Pastikan data akun dan pilihan nominal sudah sesuai sebelum melanjutkan pembayaran.
                </DialogDescription>
            </DialogHeader>

            <div class="my-4 space-y-3 rounded-2xl border border-[#0877e8]/10 bg-[#f8fbff] p-4 text-sm shadow-sm">
                <!-- Game & Account Info -->
                <div class="flex items-center justify-between border-b border-black/8 py-1.5">
                    <span class="text-xs text-[#6e6e73]">Game</span>
                    <span class="font-bold text-[#1d1d1f]">{{ brandName }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-black/8 py-1.5">
                    <span class="text-xs text-[#6e6e73]">{{ labelId }} {{ serverId ? `/ ${labelServer}` : '' }}</span>
                    <span class="font-mono font-bold text-[#1d1d1f]">{{ accountId }} {{ serverId ? `(${serverId})` : '' }}</span>
                </div>

                <div v-if="resolvedUsername" class="flex justify-between items-center py-1.5 border-b border-border/40 bg-emerald-500/10 -mx-4 px-4 text-emerald-600 dark:text-emerald-400">
                    <span class="text-xs font-semibold flex items-center gap-1">
                        <UserCheck class="h-3.5 w-3.5" /> Nickname
                    </span>
                    <span class="font-bold">{{ resolvedUsername }}</span>
                </div>

                <!-- Item Info -->
                <div class="flex items-center justify-between border-b border-black/8 py-1.5">
                    <span class="text-xs text-[#6e6e73]">Item / Nominal</span>
                    <span class="max-w-[200px] truncate text-right font-bold text-[#1d1d1f]">{{ product?.name }}</span>
                </div>

                <!-- Payment Method -->
                <div class="flex items-center justify-between border-b border-black/8 py-1.5">
                    <span class="text-xs text-[#6e6e73]">Metode Pembayaran</span>
                    <span class="text-xs font-semibold uppercase text-[#1d1d1f]">{{ paymentMethodName }}</span>
                </div>

                <!-- Contact -->
                <div class="flex items-center justify-between border-b border-black/8 py-1.5">
                    <span class="text-xs text-[#6e6e73]">No. WhatsApp</span>
                    <span class="font-semibold text-[#1d1d1f]">{{ contactPhone }}</span>
                </div>

                <!-- Total -->
                <div class="flex justify-between items-center pt-2">
                    <span class="font-bold text-[#1d1d1f]">Total Bayar</span>
                    <span class="text-lg font-black text-[#0877e8]">{{ formatCurrency(totalAmount) }}</span>
                </div>
            </div>

            <DialogFooter class="flex flex-col sm:flex-row gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="emit('update:isOpen', false)"
                    class="w-full sm:w-auto rounded-xl"
                >
                    Periksa Kembali
                </Button>
                <Button
                    type="button"
                    @click="emit('confirm')"
                    :disabled="isLoading"
                    class="w-full rounded-xl bg-gradient-to-r from-[#0877e8] to-[#0757b7] font-bold text-white shadow-md shadow-[#0877e8]/20 hover:opacity-95 sm:w-auto"
                >
                    <CheckCircle2 class="mr-2 h-4 w-4" />
                    {{ isLoading ? 'Memproses...' : 'Konfirmasi & Bayar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
