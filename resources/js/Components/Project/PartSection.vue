<script setup>
import draggable from 'vuedraggable'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

import ChapterCard from './ChapterCard.vue'

const props = defineProps({
    project: Object,
    part: Object,
    cardSize: String,
    canEdit: Boolean,
    chapters: Array
})

const activeMenu = ref(null)
const isPartModalOpen = ref(false);

const closeMenus = () => {
    activeMenu.value = null
}

onMounted(() => {
    document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeMenus)
})

const partForm = useForm({
  action: 'update_part',
  part_id: props.part.id,
  title: '',
  summary: ''
});


const submitPart = () => {
    partForm.patch(route('projects.update', props.project.id), {
        onSuccess: () => {
            isPartModalOpen.value = false
        },
    })
}

const emit = defineEmits(['update:chapters', 'reordered'])

const localChapters = computed({
    get: () => props.chapters,
    set: (val) => emit('update:chapters', val)
})
console.log('localChapters', localChapters.value)
</script>

<template>
    <section class="dashboard-panel p-6 space-y-6">

        <div class="flex items-start justify-between gap-6">

            <div class="space-y-2 flex gap-4 group/title">
                <h2 class="text-2xl font-semibold tracking-tight">
                    {{ part.title }}
                </h2>

                <p class="max-w-3xl text-zinc-400 leading-relaxed">
                    {{ part.summary }}
                </p>
                <button @click="isPartModalOpen = true"
                    class="text-zinc-500 hover:text-zinc-300 opacity-0 group-hover/title:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button> 
            </div>

            <div class="text-sm text-zinc-500 whitespace-nowrap">
                {{ part.chapters.length }} chapters
            </div>

        </div>

        <draggable
            v-model="localChapters"
            item-key="id"
            group="chapters"
            class="chapter-grid"
            ghost-class="drag-ghost"
            chosen-class="drag-chosen"
            animation="250"
            handle=".drag-handle"
            @change="emit('reordered')"
        >
            <template #item="{ element }">
                <ChapterCard
                    :project="project"
                    :chapter="element"
                    :card-size="cardSize"
                    :menu-open="activeMenu === element.id"
                    :can-edit="canEdit"
                    @toggle-menu="
                        activeMenu = activeMenu === element.id
                            ? null
                            : element.id"
                />
            </template>

            <template #footer>
                <div
                    v-if="!localChapters.length"
                    class="empty-part-placeholder rounded-xl border border-dashed border-zinc-700/70 bg-zinc-900/30 px-8 py-12 text-center col-span-full"
                >
                    <div class="space-y-3">
                        <p class="text-lg text-zinc-300">This part is empty</p>
                        <p class="max-w-md mx-auto text-sm text-zinc-500">
                            Add chapters here to begin drafting this section.
                        </p>
                    </div>
                </div>
            </template>
        </draggable>

    </section>

    <Modal  v-if="canEdit"
    :show="isPartModalOpen">
        <h3 class="text-lg font-bold mb-4">
            Update Part
        </h3>

        <form @submit.prevent="submitPart" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">
                    Part Title
                </label>

                <input
                    v-model="partForm.title"
                    type="text"
                    class="search-input"
                />

                <span
                    v-if="partForm.errors.title"
                    class="text-red-500 text-sm"
                >
                    {{ partForm.errors.title }}
                </span>

                <label class="block text-sm font-medium">
                    Summary
                </label>
                <input
                    v-model="partForm.summary"
                    type="text"
                    class="search-input"
                />

                <span
                    v-if="partForm.errors.title"
                    class="text-red-500 text-sm"
                >
                    {{ partForm.errors.summary }}
                </span>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button
                    type="button"
                    @click="isPartModalOpen = false"
                    class="secondary-button"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    :disabled="partForm.processing"
                    class="primary-button"
                >
                    Update
                </button>
            </div>

        </form>
    </Modal>
</template>