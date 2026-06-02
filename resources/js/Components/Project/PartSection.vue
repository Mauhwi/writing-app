<script setup>
import draggable from 'vuedraggable'

import ChapterGrid from './ChapterGrid.vue'

const props = defineProps({
    project: Object,
    part: Object,
    cardSize: String,
})
</script>

<template>
    <section class="dashboard-panel p-6 space-y-6">

        <div class="flex items-start justify-between gap-6">

            <div class="space-y-2">
                <h2 class="text-2xl font-semibold tracking-tight">
                    {{ part.title }}
                </h2>

                <p class="max-w-3xl text-zinc-400 leading-relaxed">
                    {{ part.description }}
                </p>
            </div>

            <div class="text-sm text-zinc-500 whitespace-nowrap">
                {{ part.chapters.length }} chapters
            </div>

        </div>

        <draggable
            :list="part.chapters"
            item-key="id"
            group="chapters"
            class="chapter-grid"
            ghost-class="drag-ghost"
            chosen-class="drag-chosen"
            animation="250"
            handle=".drag-handle"
        >
            <template #item="{ element }">
                <ChapterGrid
                    :project="project"
                    :chapter="element"
                    :card-size="cardSize"
                />
            </template>
        </draggable>

    </section>
</template>