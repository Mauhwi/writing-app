<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";
import { ref } from 'vue';

const page  = usePage();

const props = defineProps({
  project: Object
});
</script>

<template>
  <div class="container">{{ props.project.title }}</div>
</template>