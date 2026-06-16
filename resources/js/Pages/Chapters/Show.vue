<script setup>
  import { useEditor } from '@tiptap/vue-3'
  import { computed, ref } from 'vue';
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'
  import { Extension } from '@tiptap/core'
  import { Comment } from '@/Extensions/Comment'
  //import { CharacterCount } from '@tiptap/extensions'
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
          //const { selection } = state;
          //const { $anchor } = selection;

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
      //CharacterCount,
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
  const replyBody = ref('')
  const commentThreads = ref([...props.commentThreads])

  const selectionHasComment = computed(() => {
    if (!editor.value) {
        return false
    }

    return editor.value.isActive('comment')
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
    if (!commentBody.value.trim()) {
        return
    }
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
    const thread = response.data.thread
    commentThreads.value.push(thread)
    
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

    activeThread.value = thread
    commentBody.value = ''
    showCommentModal.value = false
    pendingSelection.value = null
  }

  const threadMap = computed(() => {
    return Object.fromEntries(
        commentThreads.value.map(thread => [
            thread.anchor,
            thread,
        ])
    )
  })

  const submitReply = async () => {
    if (
        !activeThread.value ||
        !replyBody.value.trim()
    ) {
        return
    }

    const response = await axios.post(
        route(
            'comment-messages.store',
            activeThread.value.id
        ),
        {
            body: replyBody.value,
        }
    )

    activeThread.value.messages.push(
        response.data
    )

    replyBody.value = ''
  }

  const deleteThread = async () => {
    if (!activeThread.value) {
        return
    }

    const threadId = activeThread.value.id

    const response = await axios.delete(
        route(
            'comment-threads.destroy',
            threadId
        )
    )

    const anchor = response.data.anchor

    commentThreads.value =
        commentThreads.value.filter(
            thread => thread.id !== threadId
        )

    editor.value
        .chain()
        .focus()
        .unsetCommentByAnchor(anchor)
        .run()

    save()

    activeThread.value = null
  }

  const deleteMessage = async (messageId) => {
    const response = await axios.delete(
        route(
            'comment-messages.destroy',
            messageId
        )
    )

    activeThread.value.messages =
        activeThread.value.messages.filter(
            message => message.id !== messageId
        )

    if (response.data.threadDeleted) {
        commentThreads.value =
            commentThreads.value.filter(
                thread =>
                    thread.id !== response.data.threadId
            )

        editor.value
            .chain()
            .focus()
            .unsetCommentByAnchor(
                response.data.anchor
            )
            .run()

        save()

        activeThread.value = null
    }
  }

  
//   watch(
//       editor,
//       (newEditor) => {
//           if (!newEditor) return

//           console.log('TEXT:', newEditor.getText())
//           console.log('HTML:', newEditor.getHTML())
//           console.log('JSON:', newEditor.getJSON())
//       },
//       { immediate: true }
//   )
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
          <ChapterSidebar />

          <div class="flex-1 flex flex-col">

              <ChapterToolbar />

              <ChapterEditor 
              :editor="editor"
              :chapter="chapter"
              :canEdit="canEdit"
              :selectionHasComment = "selectionHasComment"
              @comment="openCommentModal"
              />

          </div>

          <ChapterComments 
            :thread="activeThread"
            :reply-body="replyBody"
            @update:reply-body="replyBody = $event"
            @reply="submitReply"
            @delete-message="deleteMessage"
            @delete-thread="deleteThread"
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
            class="bg-[#131a26] px-4 py-3 text-zinc-100 w-full rounded border p-2 text-black"
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