<script setup>
import ChapterCard from './ChapterCard.vue'

const props = defineProps({
    project: Object,
    chapter: Object,
    cardSize: String,
})
</script>

<template>
    <ChapterCard
        :project="project"
        :chapter="chapter"
        :card-size="cardSize"
    />
</template>