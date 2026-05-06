<script setup>
defineProps({
  projects: Array
})
</script>

<template>
  <div>
    <h2 class="text-2xl font-semibold mb-4">Projects</h2>

    <div class="grid grid-cols-3 gap-4">
      <div v-for="project in projects" :key="project.id" class="p-4 bg-white shadow rounded">
        <h3 class="font-bold">{{ project.title }}</h3>
        <p class="text-sm text-gray-600">{{ project.description }}</p>

        <div class="mt-2 text-sm">
          {{ project.chapters.length }} chapters
        </div>
      </div>
    </div>
  </div>
</template>