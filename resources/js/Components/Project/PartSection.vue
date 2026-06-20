<script setup>
import draggable from 'vuedraggable'
import { ref, onMounted, onBeforeUnmount } from 'vue'

import ChapterCard from './ChapterCard.vue'

const props = defineProps({
    project: Object,
    part: Object,
    cardSize: String,
    canEdit: Boolean
})

const activeMenu = ref(null)

const closeMenus = () => {
    activeMenu.value = null
}

onMounted(() => {
    document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeMenus)
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

        <template v-if="part.chapters.length">
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
                    
                <ChapterCard
                    :project="project"
                    :chapter="element"
                    :card-size="cardSize"
                    :menu-open="activeMenu === element.id"
                    :can-edit="canEdit"
                    @toggle-menu="
                        activeMenu = activeMenu === element.id
                            ? null
                            : element.id"
                />
                </template>
            </draggable>
        </template>

        <div v-else
            class="rounded-xl border border-dashed border-zinc-700/70 bg-zinc-900/30 px-8 py-12 text-center">
            <div class="space-y-3">
                <p class="text-lg text-zinc-300">
                    This part is empty
                </p>

                <p class="max-w-md mx-auto text-sm text-zinc-500">
                    Add chapters here to begin drafting this section.
                </p>
            </div>
        </div>

    </section>
</template>