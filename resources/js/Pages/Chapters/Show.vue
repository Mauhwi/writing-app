<script setup>
  import { useEditor, EditorContent } from '@tiptap/vue-3'
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'
  import ChapterHeader from '@/Components/Chapter/ChapterHeader.vue'
  import ChapterSidebar from '@/Components/Chapter/ChapterSidebar.vue'
  import ChapterToolbar from '@/Components/Chapter/ChapterToolbar.vue'
  import ChapterEditor from '@/Components/Chapter/ChapterEditor.vue'
  import ChapterComments from '@/Components/Chapter/ChapterComments.vue'

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
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-zinc-100">

        <ChapterHeader 
        :processing="form.processing"
        @save="save"
        />

        <div class="flex">

            <ChapterSidebar />

            <div class="flex-1 flex flex-col">

                <ChapterToolbar />

                <ChapterEditor 
                :editor="editor"
                :chapter="chapter"
                />

            </div>

            <ChapterComments />

        </div>

    </div>
</template>