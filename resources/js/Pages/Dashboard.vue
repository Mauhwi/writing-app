<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const page  = usePage();

const form = useForm({
  title: '',
  description: '',
});

const createProject = () => {
  form.post(route('projects.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};

const props = defineProps({
  projects: Array,
  sharedProjects: Array
});

const projectsLocal = ref([...props.projects]);
const recentlyDeleted = ref(null);

const deleteProject = (project) => {

    if (confirm('Delete this project?')) {
      recentlyDeleted.value = project;

      projectsLocal.value = projectsLocal.value.filter(
          p => p.id !== project
      );
    
      setTimeout(() => {
          if (recentlyDeleted.value === project) {
            form.delete(route('projects.delete', project));
            recentlyDeleted.value = null;
          }
      }, 5000);
    }
};

const undoDelete = () => {
    projectsLocal.value.push(recentlyDeleted.value);

    recentlyDeleted.value = null;
};
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
                <button
                    class="flex items-center gap-2 rounded-lg bg-[#534AB7] px-4 py-2 text-sm font-medium text-[#EEEDFE] transition hover:bg-[#6257cf]"
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

            <!-- Projects -->
            <article
                v-for="project in projects"
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

            <!-- Shared Project -->
            <article v-for="project in sharedProjects" :key="project.id"
                class="overflow-hidden rounded-xl border border-[#2a2d3a] bg-[#171a26]"
            >
            <Link :href="route('projects.show', { project: project.id })">
                <div
                    class="flex h-20 items-center justify-center border-b border-[#2a2d3a] bg-gradient-to-br from-[#2a2550] to-[#1a1830]"
                >
                    <i class="ti ti-book text-3xl text-[#7F77DD]"></i>
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
                            many chapters
                        </span>

                        <span>much words</span>
                    </footer>

                </div>
            </article>

            <!-- Project -->
            <article
                class="overflow-hidden rounded-xl border border-[#2a2d3a] bg-[#171a26]"
            >
                <div
                    class="flex h-80 items-center justify-center border-b border-[#2a2d3a] bg-gradient-to-br from-[#2a2550] to-[#1a1830]"
                >
                    <i class="ti ti-feather text-3xl text-[#D4537E]"></i>
                </div>

                <div class="p-4">

                    <div class="mb-2 flex items-start justify-between">
                        <h2 class="text-base font-medium text-zinc-100">
                            Articles of Faith
                        </h2>

                        <button>
                            <i class="ti ti-dots text-zinc-500"></i>
                        </button>
                    </div>

                    <p class="mb-4 text-sm leading-relaxed text-zinc-400">
                        
                    </p>

                    <footer
                        class="flex items-center justify-between border-t border-[#232633] pt-3 text-xs text-zinc-500"
                    >
                        <span class="flex items-center gap-1">
                            <i class="ti ti-file-text"></i>
                            many chapters
                        </span>

                        <span>much words</span>
                    </footer>

                </div>
            </article>

            <!-- Project -->
            <article
                class="overflow-hidden rounded-xl border border-[#2a2d3a] bg-[#171a26]"
            >
                <div
                    class="flex h-80 items-center justify-center border-b border-[#2a2d3a] bg-gradient-to-br from-[#2a1d22] to-[#1f1419]"
                >
                    <i class="ti ti-feather text-3xl text-[#D4537E]"></i>
                </div>

                <div class="p-4">

                    <div class="mb-2 flex items-start justify-between">
                        <h2 class="text-base font-medium text-zinc-100">
                            The Instruments of Hell
                        </h2>

                        <button>
                            <i class="ti ti-dots text-zinc-500"></i>
                        </button>
                    </div>

                    <p class="mb-4 text-sm leading-relaxed text-zinc-400">
                        
                    </p>

                    <footer
                        class="flex items-center justify-between border-t border-[#232633] pt-3 text-xs text-zinc-500"
                    >
                        <span class="flex items-center gap-1">
                            <i class="ti ti-file-text"></i>
                            many chapters
                        </span>

                        <span>much words</span>
                    </footer>

                </div>
            </article>

            <!-- New Project -->
            <button
                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-[#2e3140] p-8 text-zinc-500 transition hover:border-[#534AB7] hover:text-zinc-300"
            >
                <i class="ti ti-plus text-2xl"></i>

                <span class="text-sm">
                    Start your next project
                </span>
            </button>

        </section>

    </div>

    
</template>