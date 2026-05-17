<script setup>
defineProps({
    project: Object,
})
</script>

<template>
    <div class="dashboard-panel p-6 flex items-start justify-between gap-8">

        <div class="space-y-3">
            <p class="text-sm text-zinc-500">
                Projects / {{ project.title }}
            </p>

            <h1 class="text-4xl font-semibold tracking-tight">
                {{ project.title }}
            </h1>

            <p class="max-w-2xl text-zinc-400 leading-relaxed">
                {{ project.description }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button class="primary-button">
                New Chapter
            </button>

            <button class="secondary-button">
                New Part
            </button>
        </div>

    </div>
</template>