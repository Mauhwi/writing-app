<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

const page  = usePage();

const props = defineProps({
    project: Object,
    parts: Array,
})

const cardSize = ref('medium')

// const chaptersByPart = computed(() => {
//     return project.parts.map(part => {
//         return {
//             ...part,

//             chapters: project.chapters.filter(chapter =>
//                 chapter.part_id === part.id
//             )
//         }
//     })
// })
</script>

<template>
  <div class="container">
    <h2>{{ project.title }}</h2>
    <div v-for="part in chaptersByPart" :key="part.id">
      <h3>{{ part.title }}</h3>
      <div
          v-for="chapter in part.chapters"
          :key="chapter.id"
      >
          {{ chapter.title }}
      </div>
    </div>
  </div>
</template>