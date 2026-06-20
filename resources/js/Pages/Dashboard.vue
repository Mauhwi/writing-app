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
  projects: Array
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
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Projects</h2>
    <div v-if="page.props.flash.success" class="toast">{{ page.props.flash.success }}</div>
    <div v-if="recentlyDeleted">
      Project deleted.

      <button @click="undoDelete">
        Undo
      </button>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-5">
      <div v-for="project in projects" :key="project.id" class="p-4 bg-white shadow rounded">
        <div class="project-header d-flex p-2 flex-row justify-content-between">
          <h3 class="font-bold">
            <Link :href="route('projects.show', { project: project.id })">
            {{ project.title }}
          </Link>
          </h3>
            <button @click="deleteProject(project.id)">
              x
            </button>
        </div>
        <p class="text-sm text-gray-600">{{ project.description }}</p>

        <div class="mt-2 text-sm">
          {{ project.chapters.length }} chapters
        </div>
      </div>
    </div>
  </div>

  <div class="container">
  <div class="row justify-content-center">
    <div class="col-4">
      <form @submit.prevent="submit">
        <div class="form-group mb-3">
          <input v-model="form.title" type="text" placeholder="Project Name" class="form-control" id="projectName"/>
          <div v-if="form.errors.title">{{ form.errors.title }}</div>
        </div>
        <div class="form-group mb-3">
          <textarea v-model="form.description" placeholder="Description" class="form-control" id="projectDescription"></textarea>
        </div>

        <button @click="createProject" :disabled="form.processing" class="px-4 py-2 bg-blue-500 text-white rounded">
          {{ form.processing ? 'Creating...' : 'Create New Project' }}
        </button>

        <!-- Display Errors -->
        <div v-if="form.errors.title" class="text-red-500">
          {{ form.errors.title }}
        </div>
      </form>
    </div>
  </div>
  </div>
</template>