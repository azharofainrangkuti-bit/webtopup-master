<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Crown, Medal, Trophy } from 'lucide-vue-next';

interface Leader {
    rank: number;
    name: string;
    badge: string;
}

defineProps<{ leaders: Leader[] }>();
const page = usePage();
const setting = page.props.setting;
</script>

<template>
    <Head>
        <title>{{ `Leaderboard Gamer - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />
        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-8 md:px-6 lg:px-8">
            <section class="mb-8 rounded-[24px] border border-amber-500/20 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950/30 p-6 text-white shadow-xl md:p-10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/20 text-amber-300">
                        <Trophy class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-amber-300">Gamer Community</p>
                        <h1 class="text-3xl font-black md:text-4xl">Leaderboard</h1>
                        <p class="mt-2 text-sm text-slate-300">Aktivitas gamer terbaru, dengan data pribadi tetap disamarkan.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm md:p-6">
                <div v-if="leaders.length" class="space-y-2">
                    <div
                        v-for="leader in leaders"
                        :key="leader.rank"
                        class="flex items-center gap-4 rounded-2xl border border-border/40 bg-background p-4 transition hover:border-primary/40"
                        :class="leader.rank <= 3 ? 'ring-1 ring-amber-500/20' : ''"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-muted text-lg">
                            {{ leader.badge }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-foreground">{{ leader.name }}</p>
                            <p class="text-xs text-muted-foreground">Gamer aktif</p>
                        </div>
                        <span class="flex items-center gap-1 text-sm font-black text-primary">
                            <Crown v-if="leader.rank === 1" class="h-4 w-4 text-amber-500" />
                            #{{ leader.rank }}
                        </span>
                    </div>
                </div>
                <div v-else class="py-12 text-center">
                    <Medal class="mx-auto h-10 w-10 text-muted-foreground/50" />
                    <p class="mt-3 font-semibold text-foreground">Belum ada aktivitas leaderboard</p>
                    <p class="mt-1 text-sm text-muted-foreground">Jadilah gamer pertama yang tampil di sini.</p>
                </div>
            </section>
        </main>
        <FloatingCsWidget />
        <MainFooter />
    </div>
</template>
