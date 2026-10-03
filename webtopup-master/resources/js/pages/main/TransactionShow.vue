<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import DeliveryProgressCard from '@/components/transaction/DeliveryProgressCard.vue';
import OrderDetails from '@/components/transaction/OrderDetails.vue';
import PaymentInstructions from '@/components/transaction/PaymentInstructions.vue';
import { OrderDataItem } from '@/types/cms/main';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    order: OrderDataItem;
    mlAccountNickname?: string;
}>();

const page = usePage();

const csWhatsappUrl = computed(() => {
    const rawPhone = String(page.props.setting?.phone ?? '').replace(/\D/g, '');
    const phone = rawPhone.startsWith('0') ? `62${rawPhone.slice(1)}` : rawPhone;
    return phone ? `https://wa.me/${phone}?text=Halo%20CS,%20saya%20butuh%20bantuan%20mengenai%20pesanan%20ID%20${props.order.reference}` : '#';
});
</script>

<template>
    <Head :title="'Detail Transaksi ' + order.reference" />

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <!-- Header -->
        <MainHeader :show-back-button="true" />

        <!-- Main Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-5xl px-4 py-8">
                <!-- Page Title -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-foreground">
                        Detail Transaksi
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Informasi lengkap pesanan Anda
                    </p>
                </div>

                <!-- Content Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Left Column - Order Details -->
                    <div>
                        <OrderDetails
                            :order="order"
                            :ml-account-nickname="mlAccountNickname"
                        />
                    </div>

                    <!-- Right Column - Payment Instructions -->
                    <div>
                        <PaymentInstructions
                            v-if="order.payment"
                            :payment="order.payment"
                            :order-reference="order.reference"
                            :total-amount="order.total_amount"
                        />
                    </div>
                </div>

                <!-- Delivery Progress Section -->
                <div
                    v-if="
                        order.payment_status !== 0 &&
                        order.payment_status !== -1 &&
                        order.payment_status !== -2 &&
                        (order.product?.provider === 'gift' ||
                            order.product?.provider === 'manual_topup')
                    "
                    class="mt-6"
                >
                    <DeliveryProgressCard :order="order" />
                </div>

                <!-- Help Section -->
                <div
                    class="mt-6 rounded-2xl border border-border/60 bg-card p-5 shadow-sm"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-base font-bold text-foreground">
                                Butuh Bantuan Mengenai Pesanan Ini?
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Tim Customer Service kami siap membantu memverifikasi atau menjawab pertanyaan Anda 24/7.
                            </p>
                        </div>
                        <a
                            :href="csWhatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-500"
                        >
                            <span>Chat CS WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <FloatingCsWidget />

        <!-- Footer -->
        <MainFooter />
    </div>
</template>
