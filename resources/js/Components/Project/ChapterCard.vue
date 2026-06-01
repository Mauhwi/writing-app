<script setup>
import { computed } from 'vue'

const props = defineProps({
    chapter: Object,
    cardSize: String,
})

const sizeClass = computed(() => {
    switch (props.cardSize) {
        case 'compact':
            return 'chapter-card-compact'

        case 'large':
            return 'chapter-card-large'

        default:
            return 'chapter-card-medium'
    }
})
</script>

<template>
    <article
        :class="[
            'chapter-card flex flex-col justify-between w-64 h-80 p-5 bg-[#0e131f] border border-zinc-800 rounded-xl text-center text-zinc-100',
            sizeClass
        ]"
    >

        <div class="flex items-center justify-between w-full">
            <button class="drag-handle text-zinc-500 hover:text-zinc-300 cursor-grab">⋮⋮</button>
            <div class="chapter-number">
                {{ chapter.order}}
            </div>
            <button class="more-options text-zinc-500 hover:text-zinc-300">⋮</button>

        </div>

        <div class="flex-1 flex flex-col justify-center items-center px-2 my-4 space-y-3">
            <h3 class="text-lg font-bold tracking-tight text-white">{{ chapter.title }}</h3>
            <p class="text-sm text-zinc-400 leading-relaxed max-w-[180px]">
            {{ chapter.summary }}
            </p>
        </div>

        <div class="flex items-center justify-between w-full text-xs text-zinc-500 pt-2 border-t border-zinc-800/30">
            <span>{{ chapter.word_count }} words</span>
            <span>{{ chapter.updated_at_human }}</span>
        </div>

    </article>
</template>