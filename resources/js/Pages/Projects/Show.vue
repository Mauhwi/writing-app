<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

import ProjectHeader from '@/Components/Project/ProjectHeader.vue'
import PartSection from '@/Components/Project/PartSection.vue'
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

const unassignedChapters = computed(() =>
    props.project.chapters.filter(chapter => !chapter.part_id)
)

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
                    v-for="part in chaptersByPart"
                    :key="part.id"
                    :part="part"
                    :project="project"
                    :card-size="cardSize"
                    :can-edit="canEdit"
                />

                <PartSection
                    v-if="unassignedChapters.length"
                    :part="{
                        id: 'unassigned',
                        title: 'Unassigned Chapters',
                        description: 'Chapters that are not assigned to a part yet.',
                        chapters: unassignedChapters
                    }"
                    :project="project"
                    :card-size="cardSize"
                />
            </div>

            <EmptyProject v-else />

        </div>

    </div>
</template>