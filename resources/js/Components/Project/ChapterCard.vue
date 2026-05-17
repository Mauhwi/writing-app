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
            'chapter-card',
            sizeClass
        ]"
    >

        <div class="flex items-start justify-between gap-3">

            <div class="chapter-number">
                {{ chapter.order }}
            </div>

            <button class="drag-handle">
                ⋮⋮
            </button>

        </div>

        <div class="space-y-4 flex-1">

            <div class="space-y-2">
                <h3 class="text-xl font-semibold tracking-tight leading-tight">
                    {{ chapter.title }}
                </h3>

                <p class="chapter-summary">
                    {{ chapter.summary }}
                </p>
            </div>

            <div class="flex items-center justify-between text-sm text-zinc-500 pt-2">
                <span>{{ chapter.word_count }} words</span>
                <span>{{ chapter.updated_at }}</span>
            </div>

        </div>

    </article>
</template>