<script setup lang="ts">
import { checkVoucher as checkVoucherRoute } from '@/actions/App/Http/Controllers/Main/TransactionController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCurrency } from '@/lib/utils';
import { PPOBProductDataItem } from '@/types/cms/ppob';
import axios from 'axios';
import {
    BadgeCheck,
    Loader2,
    ShieldCheck,
    TicketPercent,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ContactCS from './ContactCS.vue';

interface ManualBank {
    id?: string;
    name?: string;
    account_number?: string;
    account_name?: string;
    img?: string;
}

interface PaymentMethod {
    id: string;
    name: string;
    fee: number;
    action: 'add' | 'multiply';
    img: string;
}

const props = defineProps<{
    brandName: string;
    selectedProduct: PPOBProductDataItem | null;
    selectedPayment: string | null;
    paymentType: 'manual' | 'automatic';
    totalAmount: number;
    manualBank: ManualBank;
    paymentMethods: PaymentMethod[];
    isLoading?: boolean;
}>();

const emit = defineEmits<{
    checkout: [];
    'voucher-applied': [code: string, discount: number];
    'voucher-removed': [];
}>();

const voucherCode = ref('');
const voucherError = ref<string | null>(null);
const discountAmount = ref(0);
const isCheckingVoucher = ref(false);
const appliedVoucherCode = ref<string | null>(null);

const checkVoucher = async () => {
    if (!voucherCode.value) return;

    if (!props.selectedProduct) {
        voucherError.value = 'Pilih produk terlebih dahulu';
        return;
    }

    isCheckingVoucher.value = true;
    voucherError.value = null;

    try {
        const response = await axios.post(checkVoucherRoute().url, {
            voucher_code: voucherCode.value,
            amount: props.selectedProduct.sell_price,
        });

        if (response.data.data.valid) {
            discountAmount.value = response.data.data.discount_amount;
            appliedVoucherCode.value = response.data.data.voucher_code;
            emit(
                'voucher-applied',
                response.data.data.voucher_code,
                response.data.data.discount_amount,
            );
        }
    } catch (error: any) {
        voucherError.value =
            error.response?.data?.message || 'Voucher tidak valid';
        discountAmount.value = 0;
        appliedVoucherCode.value = null;
        emit('voucher-removed');
    } finally {
        isCheckingVoucher.value = false;
    }
};

const removeVoucher = () => {
    voucherCode.value = '';
    discountAmount.value = 0;
    appliedVoucherCode.value = null;
    voucherError.value = null;
    emit('voucher-removed');
};

const getPaymentName = () => {
    if (!props.selectedPayment) return null;

    if (props.paymentType === 'manual') {
        return props.manualBank.name;
    } else {
        return props.paymentMethods.find((p) => p.id === props.selectedPayment)
            ?.name;
    }
};

// Reset voucher if product changes (optional, but safer due to min purchase checks)
watch(
    () => props.selectedProduct,
    () => {
        if (appliedVoucherCode.value) {
            // Re-check or remove? Better to remove to force re-validation or just re-validate silently?
            // For simplicity, let's remove and ask user to re-apply or keep it if we want to be fancy.
            // Let's remove to ensure validity with new price.
            removeVoucher();
        }
    },
);
</script>

<template>
    <div class="sticky top-24">
        <div class="rounded-2xl border border-[#0877e8]/15 bg-gradient-to-br from-[#061b36] via-[#0b3b72] to-[#0877e8] p-5 text-white shadow-lg shadow-[#0877e8]/20">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-[#7ed7ff]">
                    <ShieldCheck class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#7ed7ff]">Checkout terlindungi</p>
                    <h3 class="mt-1 text-base font-bold">Detail pesanan selalu transparan</h3>
                    <p class="mt-1 text-xs leading-5 text-white/75">Harga, biaya pembayaran, dan status transaksi akan tampil jelas sebelum pesanan diproses.</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2 border-t border-white/15 pt-3 text-center text-[10px] font-semibold text-white/80">
                <span>Harga jelas</span>
                <span>Bayar aman</span>
                <span>Status terlacak</span>
            </div>
        </div>

        <ContactCS />

        <!-- Voucher Section -->
        <div
            class="mt-5 rounded-2xl border border-[#0877e8]/10 bg-white p-5 shadow-md sm:p-6"
        >
            <h3 class="mb-4 text-lg font-bold text-foreground">Kode Voucher</h3>

            <div class="flex gap-2">
                <div class="relative w-full">
                    <div
                        class="absolute top-2.5 left-2.5 text-muted-foreground"
                    >
                        <TicketPercent class="h-4 w-4" />
                    </div>
                    <Input
                        v-model="voucherCode"
                        placeholder="Masukkan kode offer"
                        class="pl-9"
                        :disabled="!!appliedVoucherCode"
                        @keyup.enter="checkVoucher"
                    />
                </div>
                <Button
                    v-if="!appliedVoucherCode"
                    variant="secondary"
                    @click="checkVoucher"
                    :disabled="isCheckingVoucher || !voucherCode"
                >
                    <Loader2
                        v-if="isCheckingVoucher"
                        class="h-4 w-4 animate-spin"
                    />
                    <span v-else>Gunakan</span>
                </Button>
                <Button v-else variant="destructive" @click="removeVoucher">
                    Hapus
                </Button>
            </div>
            <p v-if="voucherError" class="mt-2 text-xs text-red-500">
                {{ voucherError }}
            </p>
            <p v-if="appliedVoucherCode" class="mt-2 text-xs font-medium text-emerald-600">
                Voucher aktif: hemat {{ formatCurrency(discountAmount) }}
            </p>
        </div>

        <div
            class="mt-5 rounded-2xl border border-[#0877e8]/10 bg-white p-5 shadow-md sm:p-6"
        >
            <div class="mb-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600">
                        <ShieldCheck class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-foreground">
                            Transaksi lebih tenang
                        </h3>
                        <p class="mt-1 text-xs leading-5 text-muted-foreground">
                            Detail pesanan, metode pembayaran, dan status transaksi
                            ditampilkan dengan jelas sebelum kamu membayar.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Brand:</span>
                    <span class="font-medium text-foreground">
                        {{ brandName }}
                    </span>
                </div>

                <div v-if="selectedProduct" class="flex justify-between">
                    <span class="text-muted-foreground">Item:</span>
                    <span class="font-medium text-foreground">
                        {{ selectedProduct.name }}
                    </span>
                </div>

                <div v-if="selectedProduct" class="flex justify-between">
                    <span class="text-muted-foreground">Harga:</span>
                    <span class="font-medium text-foreground">
                        {{ formatCurrency(selectedProduct.sell_price) }}
                    </span>
                </div>

                <div
                    v-if="appliedVoucherCode"
                    class="flex justify-between text-green-500"
                >
                    <span class="font-medium">Diskon:</span>
                    <span class="font-medium">
                        -{{ formatCurrency(discountAmount) }}
                    </span>
                </div>

                <div v-if="selectedPayment" class="flex justify-between">
                    <span class="text-muted-foreground">Pembayaran:</span>
                    <span class="font-medium text-foreground">
                        {{ getPaymentName() }}
                    </span>
                </div>

                <div
                    v-if="
                        selectedPayment &&
                        paymentType === 'automatic' &&
                        selectedProduct
                    "
                    class="flex justify-between"
                >
                    <span class="text-muted-foreground">Fee:</span>
                    <span class="font-medium text-foreground">
                        {{
                            formatCurrency(
                                Math.max(
                                    0,
                                    totalAmount +
                                        discountAmount -
                                        selectedProduct.sell_price,
                                ),
                            )
                        }}
                    </span>
                </div>

                <div class="mt-4 rounded-2xl border border-primary/10 bg-primary/5 p-3">
                    <div class="flex items-center justify-between text-base font-bold">
                        <span class="text-foreground">Total bayar</span>
                        <span class="text-primary">
                            {{ formatCurrency(totalAmount) }}
                        </span>
                    </div>
                </div>
            </div>

            <Button
                class="mt-6 w-full rounded-xl bg-gradient-to-r from-[#0877e8] to-[#0757b7] py-3.5 text-white shadow-lg shadow-[#0877e8]/25 hover:opacity-95"
                @click="emit('checkout')"
                :disabled="isLoading"
            >
                <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                <BadgeCheck v-else class="mr-2 h-4 w-4" />
                {{ isLoading ? 'Memproses...' : 'Beli Sekarang' }}
            </Button>
        </div>
    </div>
</template>
