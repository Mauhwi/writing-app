<script setup>
import draggable from 'vuedraggable'
import { computed } from 'vue'
import ChapterCard from './ChapterCard.vue'

const props = defineProps({
    project: Object,
    chapters: Array,
    cardSize: String,
    canEdit: Boolean
})

const emit = defineEmits(['update:chapters', 'reordered'])

const localChapters = computed({
    get: () => props.chapters,
    set: (val) => emit('update:chapters', val)
})
</script>

<template>
    <section class="dashboard-panel p-6 space-y-6">
        <h2 class="text-2xl font-semibold tracking-tight text-zinc-400">
            Unassigned Chapters
        </h2>

        <draggable
            v-model="localChapters"
            item-key="id"
            group="chapters"
            class="chapter-grid"
            ghost-class="drag-ghost"
            chosen-class="drag-chosen"
            animation="250"
            handle=".drag-handle"
            :disabled="!canEdit"
            @change="emit('reordered')"
        >
            <template #item="{ element }">
                <ChapterCard
                    :project="project"
                    :chapter="element"
                    :card-size="cardSize"
                    :can-edit="canEdit"
                />
            </template>
        </draggable>
    </section>
</template>