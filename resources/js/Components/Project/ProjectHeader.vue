<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ProjectToolbar from '@/Components/Project/ProjectToolbar.vue'

const props = defineProps({
    project: Object,
    cardSize: String,
})

const emit = defineEmits([
    'update:cardSize'
])

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
    <div class="dashboard-panel p-6 flex flex-col md:flex-row items-start justify-between gap-8">
        <!-- LEFT SECTION: Cover, Breadcrumbs, Title, Stats -->
        <div class="flex items-start gap-6 flex-1 w-full">

            <!-- Cover Upload (Fixed size, no stretching) -->
            <label @dragover.prevent="isDragging = true" @drop.prevent="handleDrop" :class="[
                'group relative w-28 h-40 rounded-xl overflow-hidden cursor-pointer shrink-0 transition-all duration-200 border',

                isDragging
                    ? 'border-red-500 bg-red-500/10 scale-[1.02] shadow-lg shadow-red-500/20'
                    : 'border-zinc-800 bg-zinc-900/70 hover:border-zinc-700'
            ]">
                <input type="file" class="hidden" accept="image/*" @change="uploadCover">

                <img v-if="project.cover_image" :src="`/storage/${project.cover_image}`"
                    class="w-full h-full object-cover">

                <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 text-zinc-500 text-sm">
                    <div class="text-2xl leading-none">
                        +
                    </div>

                    <div class="text-xs uppercase tracking-wide">
                        Add Cover
                    </div>
                </div>

                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors"></div>

                <!-- Drag State Overlay -->
                <div v-if="isDragging"
                    class="absolute inset-0 flex items-center justify-center bg-black/70 text-white text-xs uppercase tracking-widest">
                    Release to Upload
                </div>

                <!-- Delete Button -->
                <button v-if="project.cover_image" type="button" @click.stop.prevent="deleteCover"
                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/70 hover:bg-black text-white rounded-md px-2 py-1 text-xs z-10">
                    Delete
                </button>
            </label>

            <!-- Details Container -->
            <div class="space-y-4 flex-1 min-w-0">
                <!-- Breadcrumbs with chevron arrow -->
                <div class="flex items-center gap-2 text-sm text-zinc-500">
                    <span>Projects</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-zinc-300 font-medium">{{ project.title }}</span>
                </div>

                <!-- Title with Edit Inline Pencil Icon -->
                <div class="flex items-center gap-2 group/title">
                    <h1 class="text-3xl font-bold tracking-tight">{{ project.title }}</h1>
                    <button
                        class="text-zinc-500 hover:text-zinc-300 opacity-0 group-hover/title:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </div>

                <!-- Description -->
                <p class="max-w-xl text-zinc-400 text-sm leading-relaxed">
                    {{ project.description }}
                </p>

                <!-- Stats Bar (Chapters, Words, Updated) -->
                <div
                    class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-zinc-500 border-t border-zinc-900/50 pt-3">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span><strong class="text-zinc-300 font-medium">24</strong> Chapters</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span><strong class="text-zinc-300 font-medium">82,430</strong> Words</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span>Updated 2h ago</span>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- RIGHT SECTION: Search, Action Controls, View Toggles -->
        <ProjectToolbar
            :cardSize="cardSize"
            @update:cardSize="emit('update:cardSize', $event)"
        />
    </div>

</template>