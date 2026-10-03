<script setup lang="ts">
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, usePage } from '@inertiajs/vue3';
import { Calculator, Sparkles, Trophy, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const setting = page.props.setting;

const activeTab = ref<'winrate' | 'magicwheel'>('winrate');

// Win Rate Calculator State
const totalMatches = ref<number | ''>(500);
const currentWr = ref<number | ''>(52.5);
const targetWr = ref<number | ''>(60);

// Calculation
const requiredWins = computed(() => {
    const M = Number(totalMatches.value) || 0;
    const W = (Number(currentWr.value) || 0) / 100;
    const T = (Number(targetWr.value) || 0) / 100;

    if (M <= 0 || W < 0 || T <= 0 || T >= 1) return 0;
    if (W >= T) return 0;

    const x = (M * (T - W)) / (1 - T);
    return Math.ceil(x);
});

// Magic Wheel Calculator State
const currentPoints = ref<number | ''>(120);

const magicWheelResult = computed(() => {
    const pts = Number(currentPoints.value) || 0;
    const remainingPts = Math.max(0, 200 - pts);

    // 5 draws cost 270 diamonds (giving ~5 points)
    const totalSpins = Math.ceil(remainingPts / 5) * 5;
    const estimatedDiamonds = Math.ceil((totalSpins / 5) * 270);

    return {
        remainingPoints: remainingPts,
        totalSpins,
        estimatedDiamonds,
    };
});
</script>

<template>
    <Head>
        <title>{{ `Kalkulator Game & WR MLBB - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 md:px-6 lg:px-8">
            <!-- Header Banner -->
            <section class="mb-8 rounded-[24px] border border-sky-500/20 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950/40 p-6 text-white shadow-xl md:p-10">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-500/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-sky-300 mb-3">
                        <Calculator class="h-3.5 w-3.5" /> Fitur Spesial Gamer
                    </span>
                    <h1 class="text-3xl font-black md:text-4xl text-white">Kalkulator Game MLBB</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Hitung target Win Rate Mobile Legends dan estimasi Diamond Magic Wheel dengan akurat & cepat.
                    </p>
                </div>

                <!-- Tab Switcher -->
                <div class="mt-8 flex flex-wrap gap-2 pt-6 border-t border-white/10">
                    <button
                        @click="activeTab = 'winrate'"
                        class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold transition"
                        :class="activeTab === 'winrate' ? 'bg-sky-500 text-white shadow-lg' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                    >
                        <Trophy class="h-4 w-4" /> Kalkulator Win Rate MLBB
                    </button>
                    <button
                        @click="activeTab = 'magicwheel'"
                        class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold transition"
                        :class="activeTab === 'magicwheel' ? 'bg-sky-500 text-white shadow-lg' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                    >
                        <Zap class="h-4 w-4" /> Kalkulator Magic Wheel
                    </button>
                </div>
            </section>

            <!-- Calculator Content -->
            <section class="mx-auto max-w-3xl">
                <!-- Win Rate Calculator Form -->
                <div v-if="activeTab === 'winrate'" class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm md:p-8">
                    <h2 class="text-xl font-bold text-foreground mb-2 flex items-center gap-2">
                        <Trophy class="h-5 w-5 text-amber-500" /> Hitung Target Win Rate MLBB
                    </h2>
                    <p class="text-xs text-muted-foreground mb-6">
                        Ketik total match dan win rate saat ini untuk mengetahui berapa kemenangan tanpa kekalahan yang kamu butuhkan.
                    </p>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <Label for="totalMatches">Total Match Saat Ini</Label>
                            <Input
                                id="totalMatches"
                                v-model.number="totalMatches"
                                type="number"
                                placeholder="Contoh: 500"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <Label for="currentWr">Win Rate Saat Ini (%)</Label>
                            <Input
                                id="currentWr"
                                v-model.number="currentWr"
                                type="number"
                                step="0.1"
                                placeholder="Contoh: 52.5"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <Label for="targetWr">Target Win Rate (%)</Label>
                            <Input
                                id="targetWr"
                                v-model.number="targetWr"
                                type="number"
                                step="0.1"
                                placeholder="Contoh: 60"
                                class="mt-1"
                            />
                        </div>
                    </div>

                    <!-- Result Card -->
                    <div class="mt-8 rounded-2xl border border-sky-500/30 bg-sky-500/10 p-6 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wider text-sky-600 dark:text-sky-400">Hasil Perhitungan</p>
                        <p class="mt-2 text-4xl font-black text-foreground">
                            {{ requiredWins }} <span class="text-lg font-bold">Kemenangan Beruntun</span>
                        </p>
                        <p class="mt-2 text-xs text-muted-foreground max-w-md mx-auto">
                            Kamu membutuhkan <span class="font-bold text-foreground">{{ requiredWins }} win tanpa lose</span> untuk mencapai Win Rate target {{ targetWr }}%.
                        </p>
                    </div>
                </div>

                <!-- Magic Wheel Calculator Form -->
                <div v-else class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm md:p-8">
                    <h2 class="text-xl font-bold text-foreground mb-2 flex items-center gap-2">
                        <Zap class="h-5 w-5 text-violet-500" /> Hitung Magic Wheel MLBB
                    </h2>
                    <p class="text-xs text-muted-foreground mb-6">
                        Hitung estimasi Diamond yang dibutuhkan untuk mendapatkan Skin Legend (200 Magic Points).
                    </p>

                    <div class="max-w-md">
                        <Label for="currentPoints">Magic Points Saat Ini</Label>
                        <Input
                            id="currentPoints"
                            v-model.number="currentPoints"
                            type="number"
                            max="200"
                            placeholder="Contoh: 120"
                            class="mt-1"
                        />
                    </div>

                    <!-- Result Grid -->
                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-border/60 bg-muted/40 p-4 text-center">
                            <p class="text-xs text-muted-foreground">Sisa Poin</p>
                            <p class="mt-1 text-2xl font-black text-foreground">{{ magicWheelResult.remainingPoints }} Poin</p>
                        </div>
                        <div class="rounded-2xl border border-border/60 bg-muted/40 p-4 text-center">
                            <p class="text-xs text-muted-foreground">Estimasi Putaran</p>
                            <p class="mt-1 text-2xl font-black text-foreground">{{ magicWheelResult.totalSpins }} Spin</p>
                        </div>
                        <div class="rounded-2xl border border-sky-500/30 bg-sky-500/10 p-4 text-center">
                            <p class="text-xs font-semibold text-sky-600 dark:text-sky-400">Estimasi Diamond</p>
                            <p class="mt-1 text-2xl font-black text-sky-600 dark:text-sky-400">~{{ magicWheelResult.estimatedDiamonds }} DM</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <MainFooter />
    </div>
</template>
