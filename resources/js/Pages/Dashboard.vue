<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  projects: Array,
  sharedProjects: Array,
  canEdit: Boolean,
});

const form = useForm({ title: '', description: '' });
const isModalOpen = ref(false);
const openMenuId = ref(null); // which card's kebab menu is open

const projectsLocal = ref([...props.projects]);
const pendingIds = reactive(new Set());
const timers = {};

const deleteProject = (project) => {
  openMenuId.value = null;
  pendingIds.add(project.id);

  timers[project.id] = setTimeout(() => {
    if (pendingIds.has(project.id)) {
      pendingIds.delete(project.id);
      projectsLocal.value = projectsLocal.value.filter(p => p.id !== project.id);
      form.delete(route('projects.delete', project.id));
    }
  }, 5000);
};

const undoDelete = (project) => {
  clearTimeout(timers[project.id]);
  pendingIds.delete(project.id);
};

const createProject = () => {
  form.post(route('projects.store'), {
    preserveScroll: true,
    onSuccess: () => { form.reset(); isModalOpen.value = false; },
  });
};

// A small palette collection for projects without a cover image
const coverPalettes = [
  { from: '#2a2550', to: '#1a1830', icon: '#7F77DD', iconClass: 'ti-book' },   // indigo
  { from: '#2a1d22', to: '#1f1419', icon: '#D4537E', iconClass: 'ti-feather' }, // rose
  { from: '#1d2a28', to: '#141f1d', icon: '#4ECDC4', iconClass: 'ti-scroll' },  // teal
  { from: '#2a2418', to: '#1f1a10', icon: '#E0A458', iconClass: 'ti-flame' },   // amber
  { from: '#241d2a', to: '#19141f', icon: '#B57FDD', iconClass: 'ti-moon' },    // violet
  { from: '#1d2530', to: '#141a22', icon: '#5B9BD5', iconClass: 'ti-droplet' }, // blue
];

// determine a palette for a project based on its ID, so that projects without a cover image have a consistent color scheme
function paletteFor(project) {
  const id = typeof project.id === 'number' ? project.id : hashString(String(project.id));
  return coverPalettes[id % coverPalettes.length];
}

function hashString(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = (hash << 5) - hash + str.charCodeAt(i);
    hash |= 0;
  }
  return Math.abs(hash);
}
</script>

<template>
    <div class="w-full min-h-screen bg-[#0d0f17] p-8">

        <!-- Header -->
        <header class="mb-7 flex items-center justify-between">
            <div>
                <p class="mb-1 text-sm text-zinc-500">
                    Your library
                </p>

                <h1 class="text-2xl font-medium text-zinc-100">
                    Projects
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <button v-if="canEdit"
                    class="flex items-center gap-2 rounded-lg bg-[#534AB7] px-4 py-2 text-sm font-medium text-[#EEEDFE] transition hover:bg-[#6257cf]"
                    @click="isModalOpen = true"
                >
                    <i class="ti ti-plus"></i>

                    <span>New project</span>
                </button>

            </div>
        </header>

        <!-- Projects -->
        <section
            class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-4"
        >
            <article
            v-for="project in projectsLocal"
            :key="project.id"
            class="group relative overflow-hidden rounded-xl border border-[#2a2d3a] bg-[#171a26] transition"
            :class="pendingIds.has(project.id) ? 'opacity-40 saturate-0 pointer-events-none' : ''"
            >
            <Link :href="route('projects.show', { project: project.id })">
                <div class="h-80 overflow-hidden border-b border-[#2a2d3a]">
                <img
                    v-if="project.cover_image"
                    :src="`/storage/${project.cover_image}`"
                    :alt="`${project.title} cover`"
                    class="h-full w-full object-cover object-[center_10%]"
                />
                <div
                v-else
                class="flex h-full items-center justify-center"
                :style="{
                    background: `linear-gradient(to bottom right, ${paletteFor(project).from}, ${paletteFor(project).to})`
                }"
                >
                <i
                    class="ti text-3xl"
                    :class="paletteFor(project).iconClass"
                    :style="{ color: paletteFor(project).icon }"
                ></i>
                </div>
                </div>
            </Link>

            <div
                v-if="canEdit"
                class="absolute right-3 top-3 opacity-0 transition-opacity group-hover:opacity-100"
                :class="{ '!opacity-100': openMenuId === project.id }"
            >
                <button
                @click.stop="openMenuId = openMenuId === project.id ? null : project.id"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-zinc-200 backdrop-blur hover:bg-black/70"
                >
                ⋮
                </button>

                <div
                v-if="openMenuId === project.id"
                class="absolute right-0 mt-1 w-36 overflow-hidden rounded-lg border border-[#2a2d3a] bg-[#1c1f2b] shadow-lg"
                >
                <button
                    @click.stop="deleteProject(project)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-400 hover:bg-red-500/10"
                >
                    <i class="ti ti-trash"></i> Delete
                </button>
                </div>
            </div>

            <div class="p-4">
                <div class="mb-2 flex items-start justify-between">
                <h2 class="text-base font-medium text-zinc-100">
                    <Link :href="route('projects.show', { project: project.id })">{{ project.title }}</Link>
                </h2>
                </div>

                <p class="mb-4 text-sm leading-relaxed text-zinc-400">{{ project.description }}</p>

                <footer
                v-if="!pendingIds.has(project.id)"
                class="flex items-center justify-between border-t border-[#232633] pt-3 text-xs text-zinc-500"
                >
                <span class="flex items-center gap-1">
                    <i class="ti ti-file-text"></i> {{ project.chapters.length }} chapters
                </span>
                <span>{{ project.word_count }} words</span>
                </footer>

                <!-- Deletion overlay -->
                <div
                    v-if="pendingIds.has(project.id)"
                    class="pointer-events-auto absolute inset-0 z-50 flex flex-col items-center justify-center gap-4 bg-[#0d0f17]/90 backdrop-blur-sm p-6 text-center"
                >
                    <i class="ti ti-trash text-3xl text-red-400"></i>
                    <p class="text-sm font-medium text-zinc-200">"{{ project.title }}" is being deleted</p>

                    <button
                    type="button"
                    @click.stop.prevent="undoDelete(project)"
                    class="pointer-events-auto relative z-50 flex items-center gap-2 rounded-lg bg-[#534AB7] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#534AB7]/40 transition hover:bg-[#6257cf] hover:scale-105 active:scale-95"
                    >
                    <i class="ti ti-arrow-back-up text-base"></i>
                    Undo
                    </button>

                    <div class="h-1 w-32 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full bg-[#534AB7]" :style="{ animation: 'shrink 5s linear forwards' }"></div>
                    </div>
                </div>
            </div>
            </article>

            <!-- Shared Projects -->
            <article
                v-for="project in sharedProjects"
                :key="project.id"
                class="overflow-hidden rounded-xl border border-[#2a2d3a] bg-[#171a26]"
            >
                <Link :href="route('projects.show', { project: project.id })">
                    <div class="h-80 overflow-hidden border-b border-[#2a2d3a]">

                        <img
                            v-if="project.cover_image"
                            :src="`/storage/${project.cover_image}`"
                            :alt="`${project.title} cover`"
                            class="h-full w-full object-cover object-[center_10%]"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center bg-gradient-to-br from-[#2a2550] to-[#1a1830]"
                        >
                            <i class="ti ti-book text-3xl text-[#7F77DD]"></i>
                        </div>

                    </div>
                </Link>

                <div class="p-4">

                    <div class="mb-2 flex items-start justify-between">
                        <h2 class="text-base font-medium text-zinc-100">
                            <Link :href="route('projects.show', { project: project.id })">
                                {{ project.title }}
                            </Link>
                        </h2>

                        <button>
                            <i class="ti ti-dots text-zinc-500"></i>
                        </button>
                    </div>

                    <p class="mb-4 text-sm leading-relaxed text-zinc-400">
                        {{ project.description }}
                    </p>

                    <footer
                        class="flex items-center justify-between border-t border-[#232633] pt-3 text-xs text-zinc-500"
                    >
                        <span class="flex items-center gap-1">
                            <i class="ti ti-file-text"></i>
                            {{ project.chapters.length }} chapters
                        </span>

                        <span>{{ project.word_count }} words</span>
                    </footer>

                </div>
            </article>

            <!-- New Project -->
            <button v-if="canEdit"
            @click="isModalOpen = true"
                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-[#2e3140] p-8 text-zinc-500 transition hover:border-[#534AB7] hover:text-zinc-300"
            >
                <i class="ti ti-plus text-2xl"></i>

                <span class="text-sm">
                    Start your next project
                </span>
            </button>

        </section>

    </div>

        <Modal  v-if="canEdit"
    :show="isModalOpen">
        <h3 class="text-lg font-bold mb-4">
            Add Project
        </h3>

        <form @submit.prevent="createProject" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">
                    Project Title
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

                <label class="block text-sm font-medium">
                    Description
                </label>
                <input
                    v-model="form.description"
                    type="text"
                    class="search-input"
                />

                <span
                    v-if="form.errors.description"
                    class="text-red-500 text-sm"
                >
                    {{ form.errors.description }}
                </span>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button
                    type="button"
                    @click="isModalOpen = false"
                    class="secondary-button"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="primary-button"
                >
                    Create
                </button>
            </div>

        </form>
    </Modal>
</template>
<style>
@keyframes shrink {
  from { width: 100%; }
  to { width: 0%; }
}
</style>