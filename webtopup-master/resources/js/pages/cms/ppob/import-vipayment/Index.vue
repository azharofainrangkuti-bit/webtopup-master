<script setup lang="ts">
import { sync } from '@/actions/App/Http/Controllers/Cms/PPOB/ImportVipaymentController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useSwal } from '@/composables/useSwal';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { BreadcrumbItem } from '@/types';
import { VipaymentCategoryGroup } from '@/types/cms/ppob';
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';

defineProps<{
    products: VipaymentCategoryGroup[];
}>();

const { toast } = useSwal();
const title = 'Import ViPayment';
const form = useForm({});
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'PPOB', href: '#' },
    { title, href: '#' },
];

const submit = () => {
    form.post(sync().url, {
        onSuccess: () => toast.fire({ icon: 'success', title: 'Products imported successfully.' }),
        onError: () => toast.fire({ icon: 'error', title: 'Failed to import products from ViPayment.' }),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="title" />
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between gap-4">
                <Heading :title="title" description="Import services available from ViPayment." />
                <Button :disabled="form.processing" @click="submit">
                    <Save class="mr-2 h-4 w-4" />
                    Import All Products
                </Button>
            </div>

            <div v-if="products.length === 0" class="rounded-lg border bg-card p-6 text-center text-sm text-muted-foreground">
                No products returned from ViPayment. Check the API credentials in the environment configuration.
            </div>

            <div v-else class="space-y-2">
                <details v-for="category in products" :key="category.name" class="rounded-lg border bg-card text-card-foreground">
                    <summary class="cursor-pointer p-4 font-medium">
                        {{ category.name }}
                        <span class="ml-2 text-sm font-normal text-muted-foreground">{{ category.brands.length }} brands</span>
                    </summary>
                    <div class="space-y-4 border-t p-4">
                        <div v-for="brand in category.brands" :key="brand.name">
                            <p class="mb-2 font-medium">{{ brand.name }}</p>
                            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                <div v-for="product in brand.products" :key="product.sku" class="rounded-md border p-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-medium">{{ product.name }}</p>
                                        <span :class="product.status ? 'text-emerald-600' : 'text-rose-500'" class="text-xs font-semibold uppercase">
                                            {{ product.status ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ product.sku }}</p>
                                    <p class="mt-2 font-semibold">{{ formatCurrency(product.price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </AppLayout>
</template>