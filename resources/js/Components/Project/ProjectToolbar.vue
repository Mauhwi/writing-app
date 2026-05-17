<script setup>
defineProps({
    cardSize: String,
})

const emit = defineEmits([
    'update:cardSize'
])
</script>

<template>
    <div class="dashboard-panel p-4 flex flex-wrap items-center gap-4 justify-between">

        <div class="flex items-center gap-4 flex-wrap">

            <input
                type="text"
                placeholder="Search chapters..."
                class="search-input"
            >

        </div>

        <div class="flex items-center gap-2">

            <button
                @click="emit('update:cardSize', 'compact')"
                :class="[
                    'size-toggle-button',
                    cardSize === 'compact' && 'size-toggle-button-active'
                ]"
            >
                Compact
            </button>

            <button
                @click="emit('update:cardSize', 'medium')"
                :class="[
                    'size-toggle-button',
                    cardSize === 'medium' && 'size-toggle-button-active'
                ]"
            >
                Medium
            </button>

            <button
                @click="emit('update:cardSize', 'large')"
                :class="[
                    'size-toggle-button',
                    cardSize === 'large' && 'size-toggle-button-active'
                ]"
            >
                Large
            </button>

        </div>

    </div>
</template>