<script setup>
  import { useEditor } from '@tiptap/vue-3'
  import { computed, ref, watch } from 'vue';
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'
  import { Extension } from '@tiptap/core'
  import { Comment } from '@/Extensions/Comment'
  import { CharacterCount } from '@tiptap/extensions'
  import axios from 'axios'
  //component
  import ChapterHeader from '@/Components/Chapter/ChapterHeader.vue'
  import ChapterSidebar from '@/Components/Chapter/ChapterSidebar.vue'
  import ChapterToolbar from '@/Components/Chapter/ChapterToolbar.vue'
  import ChapterEditor from '@/Components/Chapter/ChapterEditor.vue'
  import ChapterComments from '@/Components/Chapter/ChapterComments.vue'
  import Modal from '@/Components/Modal.vue'

  const props = defineProps({
    project: Object,
    chapter: Object,
    canEdit: Boolean,
    commentThreads: Array,
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
    editorProps: {
      handleClick(view, pos, event) {
          const $pos = view.state.doc.resolve(pos)

          const commentMark = $pos
              .marks()
              .find(
                  mark =>
                      mark.type.name ===
                      'comment'
              )

          if (!commentMark) {
              return false
          }

          activeThread.value =
              threadMap.value[
                  commentMark.attrs.anchor
              ]

          return true
      },
    },
    parseOptions: {
      preserveWhitespace: 'full',
  },
  })
  const save = () => {
    form.content = editor.value.getJSON()
    console.log('SAVING:', JSON.stringify(form.content, null, 2))

    form.patch(route('projects.chapters.updateContent', {
        project: props.chapter.project_id,
        chapter: props.chapter.id,
    }))
  }

  //everything comment
  const commentBody = ref('')
  const showCommentModal = ref(false)
  const pendingSelection = ref(null)
  const activeThread = ref(null)

  const hasSelection = computed(() => {
    if (!editor.value) {
        return false
  }

    const { from, to } = editor.value.state.selection

    return from !== to
  })

  const openCommentModal = () => {
      const { from, to } = editor.value.state.selection

      pendingSelection.value = {
          from,
          to,
      }

      showCommentModal.value = true
  }

  const createComment = async () => {
    if (!pendingSelection.value) {
        return
    }

    const { from, to } = pendingSelection.value

    const response = await axios.post(
        route('comment-threads.store', {
            project: props.project.id,
            chapter: props.chapter.id,
        }),
        {
            body: commentBody.value,
        }
    )

    const anchor = response.data.anchor
    
    editor.value
    .chain()
    .focus()
    .setTextSelection({
        from,
        to,
    })
    .setComment({
        anchor,
    })
    .run()

    save()

    commentBody.value = ''
    showCommentModal.value = false
    pendingSelection.value = null
  }

  const threadMap = computed(() => {
    return Object.fromEntries(
        props.commentThreads.map(thread => [
            thread.anchor,
            thread,
        ])
    )
  })

  watch(
      editor,
      (newEditor) => {
          if (!newEditor) return

          console.log('TEXT:', newEditor.getText())
          console.log('HTML:', newEditor.getHTML())
          console.log('JSON:', newEditor.getJSON())
      },
      { immediate: true }
  )
</script>

<template>
  <button v-if="hasSelection" @click="openCommentModal">
      Comment
  </button>
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
          <ChapterSidebar />

          <div class="flex-1 flex flex-col">

              <ChapterToolbar />

              <ChapterEditor 
              :editor="editor"
              :chapter="chapter"
              :canEdit="canEdit"
              />

          </div>

          <ChapterComments 
            :thread="activeThread"
          />

      </div>
  </div>
  <Modal :show="showCommentModal" @close="showCommentModal = false">
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">
            Add Comment
        </h2>

        <textarea
            v-model="commentBody"
            class="w-full rounded border p-2 text-black"
            rows="5"
        />

        <div class="mt-4 flex justify-end gap-2">
            <button
                @click="showCommentModal = false"
            >
                Cancel
            </button>

            <button
                @click="createComment"
            >
                Create Comment
            </button>
        </div>
    </div>
</Modal>
</template>

<style>
    .comment-highlight {
        background-color: rgba(250, 204, 21, 0.25);
        cursor: pointer;
    }
</style>