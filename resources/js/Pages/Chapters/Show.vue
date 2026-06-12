<script setup>
  import { useEditor } from '@tiptap/vue-3'
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'
  import ChapterHeader from '@/Components/Chapter/ChapterHeader.vue'
  import ChapterSidebar from '@/Components/Chapter/ChapterSidebar.vue'
  import ChapterToolbar from '@/Components/Chapter/ChapterToolbar.vue'
  import ChapterEditor from '@/Components/Chapter/ChapterEditor.vue'
  import ChapterComments from '@/Components/Chapter/ChapterComments.vue'
  import { Extension } from '@tiptap/core'
  import { Comment } from '@/Extensions/Comment'
  import { CharacterCount } from '@tiptap/extensions'

  const props = defineProps({
    project: Object,
    chapter: Object,
    canEdit: Boolean
  })

  const form = useForm({
    content: props.chapter.content,
  })

  const FirstLineTabIndent = Extension.create({
    name: 'firstLineTabIndent',

    addKeyboardShortcuts() {
      return {
        // When pressing Tab, physically insert an Em Space character at the cursor position
        Tab: () => {
          const { state, commands } = this.editor;
          const { selection } = state;
          const { $anchor } = selection;

          // Force a rule: Only insert the indent if the cursor is at the very beginning of the paragraph
          //if ($anchor.parentOffset === 0) {
            return commands.insertContent('\u2003'); // Inserts an Em Space character
          //}

          // Allow normal Tab behavior if the user is typing mid-sentence
          //return false; 
        },
      };
    },
  });

  const editor = useEditor({
    content: props.chapter.content,
    extensions: [
      StarterKit,
      FirstLineTabIndent,
      Comment,
      CharacterCount,
    ],
    editable: props.canEdit,
  })
  const save = () => {
    form.content = editor.value.getJSON()

    form.patch(route('projects.chapters.updateContent', {
        project: props.chapter.project_id,
        chapter: props.chapter.id,
    }))
  }

  const testComment = () => {
    editor.value
        .chain()
        .focus()
        .setMark('comment', {
            anchor: 'test-anchor',
        })
        .run()
}
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-zinc-100">
        <ChapterHeader 
        :project="project"
        :chapter-title="chapter.title"
        :updated-at="chapter.updated_at" 
        :order="chapter.order"
        :processing="form.processing"
        @save="save"
        />

        <div class="flex">
        <button
            type="button"
            @click="testComment"
        >
            Test Comment
        </button>
            <ChapterSidebar />

            <div class="flex-1 flex flex-col">

                <ChapterToolbar />

                <ChapterEditor 
                :editor="editor"
                :chapter="chapter"
                :canEdit="canEdit"
                />

            </div>

            <ChapterComments />

        </div>

    </div>
</template>