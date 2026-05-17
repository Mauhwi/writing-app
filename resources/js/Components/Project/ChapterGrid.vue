<script setup>
import ChapterCard from './ChapterCard.vue'

const props = defineProps({
    chapter: Object,
    cardSize: String,
})
</script>

<template>
    <ChapterCard
        :chapter="chapter"
        :card-size="cardSize"
    />
</template>