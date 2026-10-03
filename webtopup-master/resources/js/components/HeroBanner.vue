<script setup lang="ts">
import { SliderDataItem } from '@/types/cms/web';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

interface Props {
    slides: SliderDataItem[];
    autoplay?: boolean;
    interval?: number;
}

const props = defineProps<Props>();

const currentSlide = ref(0);
let autoplayTimer: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % props.slides.length;
};

const prevSlide = () => {
    currentSlide.value =
        currentSlide.value === 0
            ? props.slides.length - 1
            : currentSlide.value - 1;
};

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

const startAutoplay = () => {
    if (props.autoplay && props.slides.length > 1) {
        autoplayTimer = setInterval(nextSlide, props.interval);
    }
};

const stopAutoplay = () => {
    if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl bg-[#061b36]"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Slides -->
        <div class="relative aspect-[3/1] w-full">
            <div
                v-for="(slide, index) in slides"
                :key="slide.id"
                class="absolute inset-0 transition-opacity duration-500"
                :class="index === currentSlide ? 'opacity-100' : 'opacity-0'"
            >
                <img
                    :src="slide.image"
                    :alt="slide.title"
                    class="h-full w-full object-cover"
                    :loading="index === 0 ? 'eager' : 'lazy'"
                    :fetchpriority="index === 0 ? 'high' : 'auto'"
                />
            </div>
        </div>

        <!-- Navigation Arrows -->
        <button
            v-if="slides.length > 1"
            type="button"
            aria-label="Banner sebelumnya"
            title="Banner sebelumnya"
            class="absolute top-1/2 left-3 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/25 text-white shadow-md backdrop-blur transition-all hover:bg-white hover:text-[#061b36] sm:left-4"
            @click="prevSlide"
        >
            <ChevronLeft class="h-4 w-4" />
        </button>

        <button
            v-if="slides.length > 1"
            type="button"
            aria-label="Banner berikutnya"
            title="Banner berikutnya"
            class="absolute top-1/2 right-3 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/25 text-white shadow-md backdrop-blur transition-all hover:bg-white hover:text-[#061b36] sm:right-4"
            @click="nextSlide"
        >
            <ChevronRight class="h-4 w-4" />
        </button>

        <!-- Indicators -->
        <div
            v-if="slides.length > 1"
            class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2"
        >
            <button
                v-for="(slide, index) in slides"
                :key="`indicator-${slide.id}`"
                type="button"
                :aria-label="`Buka banner ${index + 1}`"
                :aria-current="index === currentSlide ? 'true' : undefined"
                class="h-1.5 rounded-full transition-all"
                :class="
                    index === currentSlide
                        ? 'w-8 bg-white'
                        : 'w-2 bg-white/50 hover:bg-white/75'
                "
                @click="goToSlide(index)"
            />
        </div>
    </div>
</template>
