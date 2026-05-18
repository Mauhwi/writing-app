<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    project: Object,
})

const isDragging = ref(false)

const form = useForm({
    cover_image: null,
})

const uploadFile = (file) => {
    if (!file) {
        return
    }

    form.cover_image = file

    form.post(route('projects.cover.update', props.project.id), {
        preserveScroll: true,
        forceFormData: true,
    })
}

const uploadCover = (event) => {
    uploadFile(event.target.files[0])
}

const handleDrop = (event) => {
    isDragging.value = false

    const file = event.dataTransfer.files[0]

    uploadFile(file)
}

const deleteCover = () => {
    form.delete(route('projects.cover.delete', props.project.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="dashboard-panel p-6 flex items-start justify-between gap-8">

        <!-- Cover Upload -->
        <label
            @dragover.prevent="isDragging = true"
            @drop.prevent="handleDrop"
            :class="[
                'group relative w-28 h-40 rounded-xl overflow-hidden cursor-pointer shrink-0 transition-all duration-200 border',

                isDragging
                    ? 'border-red-500 bg-red-500/10 scale-[1.02] shadow-lg shadow-red-500/20'
                    : 'border-zinc-800 bg-zinc-900/70 hover:border-zinc-700'
            ]"
        >
            <input
                type="file"
                class="hidden"
                accept="image/*"
                @change="uploadCover"
            >

            <img
                v-if="project.cover_image"
                :src="`/storage/${project.cover_image}`"
                class="w-full h-full object-cover"
            >

            <div
                v-else
                class="w-full h-full flex flex-col items-center justify-center gap-2 text-zinc-500 text-sm"
            >
                <div class="text-2xl leading-none">
                    +
                </div>

                <div class="text-xs uppercase tracking-wide">
                    Add Cover
                </div>
            </div>

            <!-- Hover Overlay -->
            <div
                class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors"
            ></div>

            <!-- Drag State Overlay -->
            <div
                v-if="isDragging"
                class="absolute inset-0 flex items-center justify-center bg-black/70 text-white text-xs uppercase tracking-widest"
            >
                Release to Upload
            </div>

            <!-- Delete Button -->
            <button
                v-if="project.cover_image"
                type="button"
                @click.stop.prevent="deleteCover"
                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/70 hover:bg-black text-white rounded-md px-2 py-1 text-xs z-10"
            >
                Delete
            </button>
        </label>

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
                <span>+</span> New Part
            </button>
        </div>

    </div>
</template>