<script setup>
import { usePage, useForm } from "@inertiajs/vue3";
import { ref, computed, nextTick  } from 'vue';

import ProjectHeader from '@/Components/Project/ProjectHeader.vue'
import PartSection from '@/Components/Project/PartSection.vue'
import UnassignedChapters from '@/Components/Project/UnassignedChapters.vue'
import EmptyProject from '@/Components/Project/EmptyProject.vue'

const page  = usePage();

const props = defineProps({
  project: Object,
  canEdit: Boolean
});

const chaptersByPart = computed(() => {
    return props.project.parts.map(part => {
        return {
            ...part,

            chapters: props.project.chapters.filter(chapter =>
                chapter.part_id === part.id
            )
        }
    })
})

const parts = ref(
    props.project.parts.map(part => ({
        ...part,
        chapters: props.project.chapters
            .filter(c => c.part_id === part.id)
            .sort((a, b) => a.order - b.order)
    }))
) 

const unassignedChapters = computed(() =>
    props.project.chapters.filter(chapter => !chapter.part_id)
)

//making sure requests from two different parts don't trigger multiple requests to the server at the same time after reorder.
let flushScheduled = false
function handleReordered() {
    console.log('reordered')
    if (flushScheduled) return
    flushScheduled = true
    nextTick(() => {
        persistArrangement()
        flushScheduled = false
    })
}

const reorderForm = useForm({
    action: 'reorder_chapters',
    chapters: [],
})

function persistArrangement() {
    const chapters = []
    let order = 0

    // Only parts participate in the order sequence.
    for (const part of parts.value) {
        for (const chapter of part.chapters) {
            chapters.push({ id: chapter.id, part_id: part.id, order: order++ })
        }
    }

    // Unassigned chapters only need part_id cleared — order is untouched.
    for (const chapter of unassignedChapters.value) {
        chapters.push({ id: chapter.id, part_id: null, order: null })
    }

    reorderForm.chapters = chapters
    reorderForm.patch(route('projects.update', props.project.id), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            parts.value = buildPartsFromProps()
            unassignedChapters.value = buildUnassignedFromProps()
        }
    })
}

const cardSize = ref('medium')
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-zinc-100">

        <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">

            <ProjectHeader :project="project" :can-edit="canEdit" v-model:cardSize="cardSize"/>

            <div
                v-if="chaptersByPart.length"
                class="space-y-8"
            >
                <PartSection
                    v-for="(part, index) in parts"
                    :key="part.id"
                    :part="part"
                    :chapters="part.chapters"
                    :project="project"
                    :card-size="cardSize"
                    :can-edit="canEdit"
                    @update:chapters="parts[index].chapters = $event"
                    @reordered="handleReordered"
                />

                <UnassignedChapters
                    v-if="unassignedChapters.length"
                    :chapters="unassignedChapters"
                    :project="project"
                    :card-size="cardSize"
                    :can-edit="canEdit"
                    @update:chapters="unassigned = $event"
                    @reordered="handleReordered"
                />
            </div>

            <EmptyProject v-else />

        </div>

    </div>
</template>