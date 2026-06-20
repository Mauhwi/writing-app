<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { Extension } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import { Comment } from '@/Extensions/Comment'
import { remoteSelectionExtension } from '@/Extensions/RemoteSelection'
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
    unreadThreads: Number,
    unreadThreadIds: Array,
})

const form = useForm({
    content: props.chapter.content,
})

const page = usePage()

const currentUser = computed(() => page.props.auth.user)

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
        remoteSelectionExtension(),
        TextAlign.configure({
          types: ['heading', 'paragraph'],
        }),
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

    form.patch(
        route('projects.chapters.updateContent', {
            project: props.chapter.project_id,
            chapter: props.chapter.id,
        }),
        {
            preserveScroll: true,
        }
    )
}

document.addEventListener('keydown', function(event) {
    if (((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') || ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'ы')) {
            event.preventDefault();
        
        save();
    }
});

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

    //save()
    saveCommentMarks()

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

const saveCommentMarks = () => {
    axios.patch(
        route('chapters.updateCommentMarks', {
            project: props.project.id,
            chapter: props.chapter.id,
        }),
        {
            content: editor.value.getJSON(),
        }
    )
}

//comment notifications
watch(activeThread, async (thread) => {

    if (!thread) {
        return
    }

    await axios.post(
        route('comment-threads.read', thread.id)
    )

    unreadIds.value =
        unreadIds.value.filter(
            id => id !== thread.id
        )

    unreadCount.value =
        unreadIds.value.length
})

const unreadCount = ref(props.unreadThreads)

const flashComment = (anchor) => {

    const element =
        editor.value.view.dom.querySelector(
            `[data-comment-anchor="${anchor}"]`
        )

    if (!element) {
        return
    }

    element.classList.add(
        'comment-highlight-flash'
    )

    setTimeout(() => {
        element.classList.remove(
            'comment-highlight-flash'
        )
    }, 2000)
}

const unreadIds = ref([
    ...props.unreadThreadIds,
])

//whispers
const remoteSelections = ref({})
const activeRemoteSelection = ref(null)
const remoteLabelPosition = ref(null)
let channel

onMounted(() => {
    channel = Echo.private(`chapter.${props.chapter.id}`)

    channel.listenForWhisper('selection', (payload) => {
        if (payload.userId === currentUser.value.id) {
            return
        }

        if (payload.cleared) {
            activeRemoteSelection.value = null
            return
        }

        activeRemoteSelection.value = payload

        remoteSelections.value[payload.userId] = payload

        editor.value.view.dispatch(
            editor.value.state.tr.setMeta(
                'remoteSelection',
                remoteSelections.value
            )
        )
    })

    let isRefreshingContent = false

    channel.listen('.comment.message.created', async (e) => {
        let thread = commentThreads.value.find(t => t.id === e.thread_id)

        // Thread doesn't exist locally yet (reader was on the page before it was created)
        if (!thread) {
            try {
                const response = await axios.get(
                    route('comment-threads.show', e.thread_id)
                )
                thread = response.data
                commentThreads.value.push(thread)
            } catch {
                // fallback: thread fetch failed, skip
                return
            }
        } else {
            const exists = thread.messages.some(m => m.id === e.message.id)
            if (!exists) {
                thread.messages.push(e.message)
            }
        }

        // Unread notification logic
        if (e.message.user_id !== currentUser.value.id) {
            if (!unreadIds.value.includes(e.thread_id)) {
                unreadIds.value.unshift(e.thread_id)
                unreadCount.value = unreadIds.value.length
            }
        }

        // Content refresh (scroll-preserving)
        if (isRefreshingContent) return
        isRefreshingContent = true

        const container = editor.value.view.dom.parentElement
        const scrollTop = container.scrollTop

        try {
            const response = await axios.get(
                route('projects.chapters.content', {
                    project: props.project.id,
                    chapter: props.chapter.id,
                })
            )
            editor.value.commands.setContent(response.data.content, false)
            requestAnimationFrame(() => { container.scrollTop = scrollTop })
        } finally {
            setTimeout(() => { isRefreshingContent = false }, 200)
        }
    })

    channel.listen('.comment.message.deleted', (e) => {

        if (e.threadDeleted) {
            commentThreads.value =
                commentThreads.value.filter(t => t.id !== e.threadIdDeleted)

            if (activeThread.value?.id === e.threadIdDeleted) {
                activeThread.value = null
            }

            return
        }

        const thread = commentThreads.value.find(t => t.id === e.threadId)

        if (thread) {
            thread.messages =
                thread.messages.filter(m => m.id !== e.messageId)
        }
    })

    let isRefreshing = false

    channel.listen('.chapter.content.updated', async () => {
        if (!editor.value) return
        if (isRefreshing) return

        isRefreshing = true

        const container = editor.value.view.dom.parentElement
        const scrollTop = container.scrollTop

        try {
            const { data } = await axios.get(
                route('projects.chapters.content', {
                    project: props.project.id,
                    chapter: props.chapter.id,
                })
            )

            editor.value.commands.setContent(data.content, false)

            requestAnimationFrame(() => {
                container.scrollTop = scrollTop
            })

        } finally {
            setTimeout(() => {
                isRefreshing = false
            }, 200)
        }
    })

    editor.value.on('selectionUpdate', () => {
        sendSelection(editor.value)
    })

})

onBeforeUnmount(() => {
    Echo.leave(`chapter.${props.chapter.id}`)
})

function sendSelection(editor) {
    const { from, to } = editor.state.selection

    Echo.private(`chapter.${props.chapter.id}`)
        .whisper('selection', {
            userId: currentUser.value.id,
            userName: currentUser.value.name,
            role: currentUser.value.role,
            from,
            to,
        })
}

let labelTimeout

watch(activeRemoteSelection, (selection) => {
    clearTimeout(labelTimeout)

    if (!selection || !selection.from || !editor.value) {
        remoteLabelPosition.value = null
        return
    }

    const coords = editor.value.view.coordsAtPos(selection.from)

    remoteLabelPosition.value = {
        top: coords.top - 32,
        left: coords.left,
        name: selection.userName,
        role: selection.role,
    }

    labelTimeout = setTimeout(() => {
        remoteLabelPosition.value = null
    }, 1000)
})

document.addEventListener('selectionchange', () => {
    const selection = window.getSelection()

    if (!selection || selection.isCollapsed) {
        channel.whisper('selection', {
            userId: currentUser.value.id,
            cleared: true,
        })
    }
})

const focusCommentAnchor = (anchor) => {

    const doc = editor.value.state.doc

    let foundPos = null

    doc.descendants((node, pos) => {

        const mark = node.marks?.find(
            mark =>
                mark.type.name === 'comment'
                && mark.attrs.anchor === anchor
        )

        if (mark) {
            foundPos = pos
            return false
        }

        return true
    })

    if (!foundPos) {
        return
    }

    editor.value
        .chain()
        .focus()
        .setTextSelection(foundPos)
        .run()

    const coords =
        editor.value.view.coordsAtPos(foundPos)

    window.scrollTo({
        top: coords.top - 200,
        behavior: 'smooth',
    })
}

const goToNextUnread = () => {

    console.log(
        commentThreads.value.map(t => t.id)
    )
    const threadId = unreadIds.value[0]

    if (!threadId) {
        return
    }

    const thread = commentThreads.value.find(
        t => t.id === threadId
    )

    if (!thread) {
        return
    }

    activeThread.value = thread

    focusCommentAnchor(thread.anchor)

    flashComment(thread.anchor)
}
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-zinc-100">
        <ChapterHeader :project="project" :chapter-title="chapter.title" :updated-at="chapter.updated_at"
            :order="chapter.order" :processing="form.processing" @save="save" :canEdit="canEdit"/>

        <div class="flex">
            <ChapterSidebar :parts="project.parts" :project-id="project.id" />

            <div class="flex-1 flex flex-col">

                <ChapterToolbar :editor="editor" />

                <div
                    v-if="remoteLabelPosition"
                    :class="['remote-reader-label', remoteLabelPosition.role]"
                    :style="{
                        top: `${remoteLabelPosition.top}px`,
                        left: `${remoteLabelPosition.left}px`,
                    }"
                >
                    {{ remoteLabelPosition.name }} is here 👀
                </div>

                <ChapterEditor :editor="editor" :chapter="chapter" :canEdit="canEdit"
                    :selectionHasComment="selectionHasComment" @comment="openCommentModal" />

            </div>
            <ChapterComments :thread="activeThread" :unreadCount="unreadCount" :unreadThreadIds="unreadIds" :reply-body="replyBody" @update:reply-body="replyBody = $event"
                @reply="submitReply" @delete-message="deleteMessage" ref="commentsRef" @delete-thread="deleteThread" @go-to-next-unread="goToNextUnread"/>

        </div>
    </div>
    <Modal :show="showCommentModal" @close="showCommentModal = false">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">
                Add Comment
            </h2>

            <textarea v-model="commentBody"
                class="bg-[#131a26] px-4 py-3 text-zinc-100 w-full rounded border p-2 text-black" rows="5" />

            <div class="mt-4 flex justify-end gap-2">
                <button @click="showCommentModal = false">
                    Cancel
                </button>

                <button @click="createComment">
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

@keyframes comment-ripple {
    0% {
        outline: 0px solid rgba(250,204,21,1);
    }

    100% {
        outline: 12px solid rgba(250,204,21,0);
    }
}

.comment-highlight-flash {
    animation: comment-ripple 2s ease;
}

.remote-selection {
    border-radius: 3px;
}

.remote-selection.role-author {
    background: rgba(120, 180, 255, 0.35);
}

.remote-selection.role-reader {
    background: rgba(255, 120, 180, 0.35);
}

.remote-reader-label {
    position: fixed;

    z-index: 9999;

    background: rgb(189, 44, 87);
    color: black;

    padding: 4px 10px;

    border-radius: 999px;

    font-size: 12px;
    font-weight: 600;

    pointer-events: none;

    box-shadow:
        0 4px 12px rgba(0,0,0,.25);
}

.remote-reader-label.author {
    background: rgba(120, 180, 255);
}
</style>