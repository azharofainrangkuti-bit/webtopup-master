<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { formatCurrency } from '@/lib/utils';
import { ChevronDown, ShieldCheck, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    paymentType: 'manual' | 'automatic';
    selectedPayment: string | null;
    manualBank: ManualBank;
    paymentMethods: PaymentMethod[];
    productPrice?: number;
}>();

const emit = defineEmits<{
    'update:paymentType': [value: 'manual' | 'automatic'];
    'update:selectedPayment': [value: string | null];
}>();

const openCategory = ref<string>('qris');

const selectedPaymentLabel = computed(() => {
    if (props.paymentType === 'manual') {
        return props.manualBank.name || 'Transfer Bank Manual';
    }

    const selectedMethod = props.paymentMethods.find((method) => method.id === props.selectedPayment);
    return selectedMethod?.name || 'QRIS';
});

const handleSelectPayment = (type: 'manual' | 'automatic', methodId: string) => {
    emit('update:paymentType', type);
    emit('update:selectedPayment', methodId);
};

const calculateFinalPrice = (method: PaymentMethod) => {
    const base = props.productPrice || 0;
    if (base <= 0) return 0;

    if (method.action === 'multiply') {
        return Math.round(base + (base * method.fee));
    }
    return base + method.fee;
};

// Categorize automatic methods
const qrisMethods = computed(() => props.paymentMethods.filter((m) => m.id.toLowerCase() === 'qris'));
const vaMethods = computed(() => props.paymentMethods.filter((m) => m.id.toLowerCase() !== 'qris'));
</script>

<template>
    <section class="rounded-2xl border border-[#0877e8]/10 bg-gradient-to-br from-white via-[#f8fbff] to-white p-5 shadow-md sm:p-6">
        <div class="mb-5 flex items-start justify-between gap-3">
            <h2 class="flex items-center gap-3 text-lg font-bold text-[#1d1d1f]">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#0877e8] to-[#0757b7] text-sm font-bold text-white shadow-md">4</span>
                Pilih Metode Pembayaran
            </h2>
            <div class="rounded-full border border-[#0877e8]/20 bg-[#0877e8]/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#0757b7]">
                {{ selectedPaymentLabel }}
            </div>
        </div>

        <div v-if="paymentMethods.length === 0" class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 text-sm text-amber-700 dark:text-amber-300">
            Metode pembayaran Sakurupiah sedang tidak tersedia. Silakan coba lagi beberapa saat lagi.
        </div>

        <div v-else class="space-y-3">
            <!-- Category 1: QRIS Instant -->
            <Collapsible
                :open="openCategory === 'qris'"
                @update:open="openCategory = openCategory === 'qris' ? '' : 'qris'"
                class="overflow-hidden rounded-2xl border border-[#0877e8]/10 bg-white shadow-sm"
            >
                <CollapsibleTrigger class="flex w-full items-center justify-between p-4 text-left font-bold text-foreground hover:bg-muted/40 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500">
                            <Zap class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">QRIS (Proses Instan 24 Jam)</p>
                            <p class="text-[11px] text-muted-foreground font-normal">BCA, Mandiri, GoPay, DANA, OVO, ShopeePay, LinkAja</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200 text-muted-foreground"
                        :class="{ 'rotate-180': openCategory === 'qris' }"
                    />
                </CollapsibleTrigger>

                <CollapsibleContent class="px-4 pb-4 pt-1 space-y-2">
                    <button
                        v-for="method in qrisMethods"
                        :key="method.id"
                        type="button"
                        class="flex w-full items-center justify-between gap-3 rounded-xl border-2 p-3.5 transition-all text-left"
                        :class="
                            paymentType === 'automatic' && selectedPayment === method.id
                                ? 'border-primary bg-primary/10 shadow-sm ring-1 ring-primary/30'
                                : 'border-border/50 hover:border-primary/40 bg-card'
                        "
                        :aria-pressed="paymentType === 'automatic' && selectedPayment === method.id"
                        @click="handleSelectPayment('automatic', method.id)"
                    >
                        <div class="flex items-center gap-3">
                            <img :src="method.img" :alt="method.name" class="h-6 w-12 object-contain" />
                            <span class="text-xs font-bold text-foreground">{{ method.name }}</span>
                        </div>

                        <div class="text-right">
                            <span v-if="productPrice && productPrice > 0" class="block text-sm font-black text-primary">
                                {{ formatCurrency(calculateFinalPrice(method)) }}
                            </span>
                            <span class="text-[11px] text-muted-foreground font-semibold">
                                Fee: {{ (method.fee * 100).toFixed(1) }}%
                            </span>
                        </div>
                    </button>
                </CollapsibleContent>
            </Collapsible>

            <!-- Category 2: Virtual Account -->
            <Collapsible
                :open="openCategory === 'va'"
                @update:open="openCategory = openCategory === 'va' ? '' : 'va'"
                class="overflow-hidden rounded-2xl border border-emerald-500/10 bg-white shadow-sm"
            >
                <CollapsibleTrigger class="flex w-full items-center justify-between p-4 text-left font-bold text-foreground hover:bg-muted/40 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">Virtual Account (Otomatis Verifikasi)</p>
                            <p class="text-[11px] text-muted-foreground font-normal">BCA, Mandiri, BNI, BRI, Permata Bank</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200 text-muted-foreground"
                        :class="{ 'rotate-180': openCategory === 'va' }"
                    />
                </CollapsibleTrigger>

                <CollapsibleContent class="px-4 pb-4 pt-1 space-y-2">
                    <button
                        v-for="method in vaMethods"
                        :key="method.id"
                        type="button"
                        class="flex w-full items-center justify-between gap-3 rounded-xl border-2 p-3.5 transition-all text-left"
                        :class="
                            paymentType === 'automatic' && selectedPayment === method.id
                                ? 'border-primary bg-primary/10 shadow-sm ring-1 ring-primary/30'
                                : 'border-border/50 hover:border-primary/40 bg-card'
                        "
                        :aria-pressed="paymentType === 'automatic' && selectedPayment === method.id"
                        @click="handleSelectPayment('automatic', method.id)"
                    >
                        <div class="flex items-center gap-3">
                            <img :src="method.img" :alt="method.name" class="h-6 w-12 object-contain" />
                            <span class="text-xs font-bold text-foreground">{{ method.name }}</span>
                        </div>

                        <div class="text-right">
                            <span v-if="productPrice && productPrice > 0" class="block text-sm font-black text-primary">
                                {{ formatCurrency(calculateFinalPrice(method)) }}
                            </span>
                            <span class="text-[11px] text-muted-foreground font-semibold">
                                Fee: {{ formatCurrency(method.fee) }}
                            </span>
                        </div>
                    </button>
                </CollapsibleContent>
            </Collapsible>

            <!-- Category 3: Transfer Bank Manual -->
            <Collapsible
                :open="openCategory === 'manual'"
                @update:open="openCategory = openCategory === 'manual' ? '' : 'manual'"
                class="overflow-hidden rounded-2xl border border-amber-500/10 bg-white shadow-sm"
            >
                <CollapsibleTrigger class="flex w-full items-center justify-between p-4 text-left font-bold text-foreground hover:bg-muted/40 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">Transfer Bank Manual (Bebas Biaya Admin)</p>
                            <p class="text-[11px] text-muted-foreground font-normal">Transfer langsung ke rekening bank admin</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200 text-muted-foreground"
                        :class="{ 'rotate-180': openCategory === 'manual' }"
                    />
                </CollapsibleTrigger>

                <CollapsibleContent class="px-4 pb-4 pt-1 space-y-2">
                    <button
                        :key="manualBank.id"
                        type="button"
                        class="flex w-full items-center justify-between gap-3 rounded-xl border-2 p-3.5 transition-all text-left"
                        :class="
                            paymentType === 'manual' && selectedPayment === manualBank.id
                                ? 'border-primary bg-primary/10 shadow-sm ring-1 ring-primary/30'
                                : 'border-border/50 hover:border-primary/40 bg-card'
                        "
                        :aria-pressed="paymentType === 'manual' && selectedPayment === manualBank.id"
                        @click="handleSelectPayment('manual', manualBank.id as any)"
                    >
                        <div class="flex items-center gap-3">
                            <img :src="manualBank.img" :alt="manualBank.name" class="h-6 w-12 object-contain" />
                            <div>
                                <span class="block text-xs font-bold text-foreground">{{ manualBank.name }}</span>
                                <span class="text-[10px] text-muted-foreground">{{ manualBank.account_number }} a.n {{ manualBank.account_name }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span v-if="productPrice && productPrice > 0" class="block text-sm font-black text-primary">
                                {{ formatCurrency(productPrice) }}
                            </span>
                            <span class="text-[11px] font-bold text-emerald-500">Fee: Gratis</span>
                        </div>
                    </button>
                </CollapsibleContent>
            </Collapsible>
        </div>
    </section>
</template>
