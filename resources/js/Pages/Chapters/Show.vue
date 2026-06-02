<script setup>
  import { useEditor, EditorContent } from '@tiptap/vue-3'
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'

  const props = defineProps({
    chapter: Object
  })

  const form = useForm({
    content: props.chapter.content,
  })

  const editor = useEditor({
    content: props.chapter.content,
    extensions: [StarterKit],
  })

  const save = () => {
    form.content = editor.value.getHTML()

    form.patch(route('projects.chapters.update', {
        project: props.chapter.project_id,
        chapter: props.chapter.id,
    }))
  }

  console.log(props.chapter);
</script>

<template>
  <div class="p-4 bg-white rounded shadow">
    <h4 class="font-semibold">{{ chapter.title }}</h4>
  </div>
  <editor-content :editor="editor" />
  <button
    @click="save"
    :disabled="form.processing"
    class="px-4 py-2 mt-4 bg-blue-600 text-white rounded"
  >
    {{ form.processing ? 'Saving...' : 'Save' }}
  </button>
</template>