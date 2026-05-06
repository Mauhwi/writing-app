<template>
  <div class="p-4 bg-white rounded shadow">
    <h4 class="font-semibold">{{ chapter.title }}</h4>
    <p class="text-sm text-gray-500">{{ chapter.summary }}</p>
  </div>
</template>

<script setup>
defineProps({
  chapter: Object
})
</script>