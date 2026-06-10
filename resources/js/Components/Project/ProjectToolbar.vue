<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    project: Object,
    cardSize: String,
})

const emit = defineEmits([
    'update:cardSize'
])

const form = useForm({
  title: '',
  summary: '',
});

const partForm = useForm({
  action: 'create_part',
  title: ''
});

const isChapterModalOpen = ref(false);
const isPartModalOpen = ref(false);

const submit = () => {
    form.post(route('projects.chapters.store', props.project.id), {
        onSuccess: () => {
            isChapterModalOpen.value = false;
            form.reset();
        },
    });
};

const submitPart = () => {
    partForm.patch(route('projects.update', props.project.id), {
        onSuccess: () => {
            isPartModalOpen.value = false;
            partForm.reset();
        },
    });
};
</script>

<template>
    <Modal :show="isChapterModalOpen">
        <h3 class="text-lg font-bold mb-4">
            Create New Chapter
        </h3>

        <form @submit.prevent="submit" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">
                    Chapter Title
                </label>

                <input
                    v-model="form.title"
                    type="text"
                    class="search-input"
                />

                <span
                    v-if="form.errors.title"
                    class="text-red-500 text-sm"
                >
                    {{ form.errors.title }}
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium">
                    Chapter Summary
                </label>

                <textarea
                    v-model="form.summary"
                    class="search-input"
                />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button
                    type="button"
                    @click="isChapterModalOpen = false"
                    class="secondary-button"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="primary-button"
                >
                    Create Chapter
                </button>
            </div>

        </form>
    </Modal>

    <Modal :show="isPartModalOpen">
        <h3 class="text-lg font-bold mb-4">
            Create New Part
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
                    Create Part
                </button>
            </div>

        </form>
    </Modal>

    <div class="flex flex-col items-end gap-4 w-full md:w-auto shrink-0">

        <!-- Search Bar -->
        <div class="relative w-full max-w-xs">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-zinc-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" placeholder="Search chapters or parts..."
                class="search-input w-full text-sm rounded-lg pl-9 pr-8 py-2">
        </div>

        <!-- Actions Row (Buttons, Filters, Options) -->
        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button
                @click="isChapterModalOpen = true"
                class="primary-button"
            >
                <span>+</span> New Chapter
            </button>

            <button
                @click="isPartModalOpen = true"
                class="secondary-button"
            >
                <span>+</span> New Part
            </button>
            <!-- Filter Button -->
            <button
                class="p-2 border border-zinc-800 rounded-lg text-zinc-400 hover:text-zinc-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </button>
            <!-- Context Options Button -->
            <button
                class="p-2 border border-zinc-800 rounded-lg text-zinc-400 hover:text-zinc-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
        </div>

        <!-- Card Layout Controls Toggle Toolbar -->
        <div class="flex items-center gap-3 p-1 rounded-xl border border-zinc-800 rounded-lg text-sm">
            <span class="text-zinc-300 pl-2">Card size:</span>
            <div class="flex items-center p-0.5 border border-zinc-900 rounded-lg">
                <!-- List View -->
                <button 
                    @click="emit('update:cardSize', 'compact')"
                    :class="[
                        'size-toggle-button p-1.5 text-zinc-500 hover:text-zinc-300 rounded',
                        cardSize === 'compact' && 'size-toggle-button-active'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Compact Grid View (Active) -->
                <button 
                    @click="emit('update:cardSize', 'medium')"
                    :class="[
                        'size-toggle-button p-1.5 text-zinc-500 hover:text-zinc-300 rounded',
                        cardSize === 'medium' && 'size-toggle-button-active'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
                <!-- Large Grid View -->
                <button
                    @click="emit('update:cardSize', 'large')"
                    :class="[
                        'size-toggle-button p-1.5 text-zinc-500 hover:text-zinc-300 rounde',
                        cardSize === 'large' && 'size-toggle-button-active'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
            </div>
        </div>

    </div>
</template>