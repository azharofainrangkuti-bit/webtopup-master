<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Award, CheckCircle, MessageSquare, ShieldCheck, Star, ThumbsUp } from 'lucide-vue-next';

interface ReviewItem {
    id: number;
    name: string;
    brand_name: string;
    product_name: string;
    rating: number;
    comment: string;
    created_at: string;
}

defineProps<{
    reviews: ReviewItem[];
}>();

const page = usePage();
const setting = page.props.setting;
</script>

<template>
    <Head>
        <title>{{ `Ulasan & Testimoni Pelanggan - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 md:px-6 lg:px-8">
            <!-- Header Banner -->
            <section class="mb-8 rounded-[24px] border border-amber-500/20 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950/30 p-6 text-white shadow-xl md:p-10">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-500/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-amber-300 mb-3">
                        <Award class="h-3.5 w-3.5" /> Reputasi Terpercaya
                    </span>
                    <h1 class="text-3xl font-black md:text-4xl text-white">Ulasan & Kepuasan Pelanggan</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Ribuan gamer di seluruh Indonesia mempercayakan kebutuhan top up game mereka di {{ setting?.title }}.
                    </p>
                </div>

                <!-- Rating Stats -->
                <div class="mt-8 grid gap-4 sm:grid-cols-3 pt-6 border-t border-white/10">
                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                            <Star class="h-6 w-6 fill-amber-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">4.9 / 5.0</p>
                            <p class="text-xs text-slate-300">Rating Kepuasan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                            <ThumbsUp class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">99.8%</p>
                            <p class="text-xs text-slate-300">Selesai Cepat & Instan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500/20 text-sky-400">
                            <ShieldCheck class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">100%</p>
                            <p class="text-xs text-slate-300">Garansi Keamanan</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials Grid -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-foreground flex items-center gap-2">
                        <MessageSquare class="h-5 w-5 text-primary" /> Testimoni Terbaru
                    </h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="rev in reviews"
                        :key="rev.id"
                        class="flex flex-col justify-between rounded-2xl border border-border/60 bg-card p-5 shadow-sm transition hover:shadow-md"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-1 text-amber-400">
                                    <Star v-for="i in rev.rating" :key="i" class="h-4 w-4 fill-amber-400" />
                                </div>
                                <span class="text-xs text-muted-foreground">{{ rev.created_at }}</span>
                            </div>

                            <p class="text-sm text-foreground italic mb-4">"{{ rev.comment }}"</p>
                        </div>

                        <div class="pt-3 border-t border-border/40 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-foreground block">{{ rev.name }}</span>
                                <span class="text-muted-foreground">{{ rev.product_name }} - {{ rev.brand_name }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                <CheckCircle class="h-3 w-3" /> Verifikasi
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <FloatingCsWidget />

        <MainFooter />
    </div>
</template>
