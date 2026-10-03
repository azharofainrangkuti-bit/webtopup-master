<script setup lang="ts">
import { getBrandVisual } from '@/lib/brandVisual';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { Loader2, Search, TrendingUp, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

interface QuickSearchResult {
    id: number;
    name: string;
    slug: string;
    category?: string | null;
    image?: string | null;
}

const isOpen = ref(false);
const query = ref('');
const results = ref<QuickSearchResult[]>([]);
const isLoading = ref(false);
const activeIndex = ref(0);
const inputRef = ref<HTMLInputElement | null>(null);
const hasSearched = computed(() => query.value.trim().length > 0);

const fetchResults = useDebounceFn(async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/search/quick', {
            params: { q: query.value.trim() },
        });
        results.value = response.data.results ?? [];
        activeIndex.value = 0;
    } catch {
        results.value = [];
    } finally {
        isLoading.value = false;
    }
}, 300);

watch(query, () => {
    fetchResults();
});

const open = () => {
    isOpen.value = true;
    void nextTick(() => inputRef.value?.focus());
    void fetchResults();
};

const close = () => {
    isOpen.value = false;
    query.value = '';
    results.value = [];
};

const goToBrand = (slug: string) => {
    close();
    router.visit(`/brand/${slug}`);
};

const onKeydown = (event: KeyboardEvent) => {
    const isShortcut = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';

    if (isShortcut) {
        event.preventDefault();
        isOpen.value ? close() : open();
        return;
    }

    if (!isOpen.value) return;

    if (event.key === 'Escape') {
        close();
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, results.value.length - 1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
    } else if (event.key === 'Enter') {
        const selected = results.value[activeIndex.value];
        if (selected) {
            event.preventDefault();
            goToBrand(selected.slug);
        }
    }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

defineExpose({ open });
</script>

<template>
    <button
        type="button"
        class="flex w-full max-w-xl items-center gap-2 rounded-full border border-border/60 bg-background/60 px-4 py-2.5 text-sm text-muted-foreground transition hover:border-primary/50 hover:text-foreground"
        @click="open"
    >
        <Search class="h-4 w-4 shrink-0" />
        <span class="flex-1 text-left">Cari game atau voucher...</span>
        <kbd class="hidden shrink-0 rounded border border-border/60 bg-muted px-1.5 py-0.5 text-[10px] font-semibold text-muted-foreground sm:inline">
            Ctrl K
        </kbd>
    </button>

    <Teleport to="body">
        <Transition name="search-fade">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[100] flex items-start justify-center bg-black/60 p-4 pt-[10vh] backdrop-blur-sm"
                @click.self="close"
            >
                <Transition name="search-pop" appear>
                    <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-border/60 bg-card shadow-2xl">
                        <div class="flex items-center gap-3 border-b border-border/60 px-4 py-3">
                            <Search class="h-5 w-5 shrink-0 text-muted-foreground" />
                            <input
                                ref="inputRef"
                                v-model="query"
                                type="text"
                                placeholder="Cari game atau voucher..."
                                class="w-full bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground"
                            />
                            <Loader2 v-if="isLoading" class="h-4 w-4 shrink-0 animate-spin text-muted-foreground" />
                            <button type="button" class="shrink-0 rounded-md p-1 text-muted-foreground hover:bg-muted" @click="close">
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-2">
                            <p class="flex items-center gap-1.5 px-2 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                <TrendingUp v-if="!hasSearched" class="h-3.5 w-3.5" />
                                {{ hasSearched ? 'Hasil pencarian' : 'Populer' }}
                            </p>

                            <p v-if="!isLoading && hasSearched && results.length === 0" class="px-2 py-6 text-center text-sm text-muted-foreground">
                                Produk yang dicari tidak ditemukan.
                            </p>

                            <button
                                v-for="(item, index) in results"
                                :key="item.id"
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition"
                                :class="index === activeIndex ? 'bg-primary/10' : 'hover:bg-muted/60'"
                                @mouseenter="activeIndex = index"
                                @click="goToBrand(item.slug)"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br"
                                    :class="getBrandVisual(item.name).gradient"
                                >
                                    <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full rounded-lg object-cover" />
                                    <component :is="getBrandVisual(item.name).icon" v-else class="h-5 w-5 opacity-70" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ item.name }}</p>
                                    <p v-if="item.category" class="truncate text-xs text-muted-foreground">{{ item.category }}</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.search-fade-enter-active,
.search-fade-leave-active {
    transition: opacity 0.15s ease;
}
.search-fade-enter-from,
.search-fade-leave-to {
    opacity: 0;
}

.search-pop-enter-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.search-pop-enter-from {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
}
</style>
