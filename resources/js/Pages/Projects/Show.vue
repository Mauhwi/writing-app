<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

import ProjectHeader from '@/Components/Project/ProjectHeader.vue'
import PartSection from '@/Components/Project/PartSection.vue'
import EmptyProject from '@/Components/Project/EmptyProject.vue'

const page  = usePage();

const { project } = defineProps({
  project: Object
});

const chaptersByPart = computed(() => {
    return project.parts.map(part => {
        return {
            ...part,

            chapters: project.chapters.filter(chapter =>
                chapter.part_id === part.id
            )
        }
    })
})

const cardSize = ref('medium')
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-zinc-100">

        <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">

            <ProjectHeader :project="project" v-model:cardSize="cardSize"/>

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
                />
            </div>

            <EmptyProject v-else />

        </div>

    </div>
</template>