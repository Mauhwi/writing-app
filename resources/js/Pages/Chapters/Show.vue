<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { computed, ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { Extension } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import { Comment } from '@/Extensions/Comment'
import { remoteSelectionExtension } from '@/Extensions/RemoteSelection'
import axios from 'axios'
import ChapterHeader from '@/Components/Chapter/ChapterHeader.vue'
import ChapterSidebar from '@/Components/Chapter/ChapterSidebar.vue'
import ChapterToolbar from '@/Components/Chapter/ChapterToolbar.vue'
import ChapterEditor from '@/Components/Chapter/ChapterEditor.vue'
import ChapterComments from '@/Components/Chapter/ChapterComments.vue'

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
            Tab: () => {
                const { commands } = this.editor;
                return commands.insertContent('\u2003');
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
    ],
    editable: props.canEdit,
    editorProps: {
        handleClick(view, pos, event) {
            const $pos = view.state.doc.resolve(pos)
            const commentMark = $pos.marks().find(mark => mark.type.name === 'comment')

            if (!commentMark) {
                // Clicking outside a comment mark — close the popover if open
                if (showCommentPopover.value) {
                    cancelCommentPopover()
                }
                return false
            }

            activeThread.value = threadMap.value[commentMark.attrs.anchor]
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
        { preserveScroll: true }
    )
}

document.addEventListener('keydown', function (event) {
    if (
        ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') ||
        ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'ы')
    ) {
        event.preventDefault()
        save()
    }

    // Escape closes the comment popover
    if (event.key === 'Escape' && showCommentPopover.value) {
        cancelCommentPopover()
    }
})

// ─── Comment popover ──────────────────────────────────────────────────────────

const commentBody = ref('')
const pendingSelection = ref(null)
const activeThread = ref(null)
const replyBody = ref('')
const commentThreads = ref([...props.commentThreads])

// Popover state
const showCommentPopover = ref(false)
const popoverPosition = ref({ top: 0, left: 0 })
const commentPopoverRef = ref(null)

const selectionHasComment = computed(() => {
    if (!editor.value) return false
    return editor.value.isActive('comment')
})

const openCommentPopover = () => {
    const { from, to } = editor.value.state.selection

    pendingSelection.value = { from, to }

    // Position the popover above the start of the selection.
    // coordsAtPos returns viewport-relative coords, which is what we want
    // for a position:fixed element.
    const coords = editor.value.view.coordsAtPos(from)

    popoverPosition.value = {
        // 12px gap above the text line
        top: coords.top - 12,
        left: coords.left,
    }

    showCommentPopover.value = true

    // Focus the textarea on next tick so the user can type immediately
    nextTick(() => {
        commentPopoverRef.value?.querySelector('textarea')?.focus()
    })
}

const cancelCommentPopover = () => {
    showCommentPopover.value = false
    commentBody.value = ''
    pendingSelection.value = null
}

const createComment = async () => {
    if (!commentBody.value.trim() || !pendingSelection.value) {
        return
    }

    const { from, to } = pendingSelection.value

    const response = await axios.post(
        route('comment-threads.store', {
            project: props.project.id,
            chapter: props.chapter.id,
        }),
        { body: commentBody.value }
    )

    const anchor = response.data.anchor
    const thread = response.data.thread
    commentThreads.value.push(thread)

    editor.value
        .chain()
        .focus()
        .setTextSelection({ from, to })
        .setComment({ anchor })
        .run()

    saveCommentMarks()

    activeThread.value = thread
    commentBody.value = ''
    showCommentPopover.value = false
    pendingSelection.value = null
}

// ─── Thread map & replies ─────────────────────────────────────────────────────

const threadMap = computed(() => {
    return Object.fromEntries(
        commentThreads.value.map(thread => [thread.anchor, thread])
    )
})

const submitReply = async () => {
    if (!activeThread.value || !replyBody.value.trim()) return

    const response = await axios.post(
        route('comment-messages.store', activeThread.value.id),
        { body: replyBody.value }
    )

    activeThread.value.messages.push(response.data)
    replyBody.value = ''
}

const deleteThread = async () => {
    if (!activeThread.value) return

    const threadId = activeThread.value.id
    const response = await axios.delete(route('comment-threads.destroy', threadId))
    const anchor = response.data.anchor

    commentThreads.value = commentThreads.value.filter(thread => thread.id !== threadId)

    editor.value.chain().focus().unsetCommentByAnchor(anchor).run()
    save()
    activeThread.value = null
}

const deleteMessage = async (messageId) => {
    const response = await axios.delete(route('comment-messages.destroy', messageId))

    activeThread.value.messages = activeThread.value.messages.filter(
        message => message.id !== messageId
    )

    if (response.data.threadDeleted) {
        commentThreads.value = commentThreads.value.filter(
            thread => thread.id !== response.data.threadId
        )

        editor.value.chain().focus().unsetCommentByAnchor(response.data.anchor).run()
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
        { content: editor.value.getJSON() }
    )
}

// ─── Unread notifications ─────────────────────────────────────────────────────

watch(activeThread, async (thread) => {
    if (!thread) return

    await axios.post(route('comment-threads.read', thread.id))

    unreadIds.value = unreadIds.value.filter(id => id !== thread.id)
    unreadCount.value = unreadIds.value.length
})

const unreadCount = ref(props.unreadThreads)
const unreadIds = ref([...props.unreadThreadIds])

const flashComment = (anchor) => {
    const element = editor.value.view.dom.querySelector(`[data-comment-anchor="${anchor}"]`)
    if (!element) return

    element.classList.add('comment-highlight-flash')
    setTimeout(() => element.classList.remove('comment-highlight-flash'), 2000)
}

// ─── Whispers / remote selections ────────────────────────────────────────────

const remoteSelections = ref({})
const activeRemoteSelection = ref(null)
const remoteLabelPosition = ref(null)
let channel

onMounted(() => {
    channel = Echo.private(`chapter.${props.chapter.id}`)

    channel.listenForWhisper('selection', (payload) => {
        if (payload.userId === currentUser.value.id) return

        if (payload.cleared) {
            activeRemoteSelection.value = null
            return
        }

        activeRemoteSelection.value = payload
        remoteSelections.value[payload.userId] = payload

        editor.value.view.dispatch(
            editor.value.state.tr.setMeta('remoteSelection', remoteSelections.value)
        )
    })

    let isRefreshingContent = false

    channel.listen('.comment.message.created', async (e) => {
        let thread = commentThreads.value.find(t => t.id === e.thread_id)

        // Thread doesn't exist locally yet (reader was on page before it was created)
        if (!thread) {
            try {
                const response = await axios.get(route('comment-threads.show', e.thread_id))
                thread = response.data
                commentThreads.value.push(thread)
            } catch {
                return
            }
        } else {
            const exists = thread.messages.some(m => m.id === e.message.id)
            if (!exists) thread.messages.push(e.message)
        }

        if (e.message.user_id !== currentUser.value.id) {
            if (!unreadIds.value.includes(e.thread_id)) {
                unreadIds.value.unshift(e.thread_id)
                unreadCount.value = unreadIds.value.length
            }
        }

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
            commentThreads.value = commentThreads.value.filter(t => t.id !== e.threadIdDeleted)
            if (activeThread.value?.id === e.threadIdDeleted) activeThread.value = null
            return
        }

        const thread = commentThreads.value.find(t => t.id === e.threadId)
        if (thread) thread.messages = thread.messages.filter(m => m.id !== e.messageId)
    })

    let isRefreshing = false

    channel.listen('.chapter.content.updated', async () => {
        if (!editor.value || isRefreshing) return
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
            requestAnimationFrame(() => { container.scrollTop = scrollTop })
        } finally {
            setTimeout(() => { isRefreshing = false }, 200)
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

    labelTimeout = setTimeout(() => { remoteLabelPosition.value = null }, 1000)
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
            mark => mark.type.name === 'comment' && mark.attrs.anchor === anchor
        )
        if (mark) {
            foundPos = pos
            return false
        }
        return true
    })

    if (!foundPos) return

    editor.value.chain().focus().setTextSelection(foundPos).run()

    const coords = editor.value.view.coordsAtPos(foundPos)
    window.scrollTo({ top: coords.top - 200, behavior: 'smooth' })
}

const goToNextUnread = () => {
    const threadId = unreadIds.value[0]
    if (!threadId) return

    const thread = commentThreads.value.find(t => t.id === threadId)
    if (!thread) return

    activeThread.value = thread
    focusCommentAnchor(thread.anchor)
    flashComment(thread.anchor)
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
            :canEdit="canEdit"
            @save="save"
        />

        <div class="flex">
            <div class="hidden xl:block">
                <ChapterSidebar :parts="project.parts" :project-id="project.id" />
            </div>

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

                <ChapterEditor
                    :editor="editor"
                    :chapter="chapter"
                    :canEdit="canEdit"
                    :selectionHasComment="selectionHasComment"
                    @comment="openCommentPopover"
                />
            </div>

            <div class="hidden xl:block">
                <ChapterComments
                    :thread="activeThread"
                    :unreadCount="unreadCount"
                    :unreadThreadIds="unreadIds"
                    :reply-body="replyBody"
                    @update:reply-body="replyBody = $event"
                    @reply="submitReply"
                    @delete-message="deleteMessage"
                    @delete-thread="deleteThread"
                    @go-to-next-unread="goToNextUnread"
                />
            </div>
        </div>
    </div>

    <!-- Comment popover — fixed above the selection, outside the editor flow -->
    <Teleport to="body">
        <Transition name="popover">
            <div
                v-if="showCommentPopover"
                ref="commentPopoverRef"
                class="comment-popover"
                :style="{
                    top: `${popoverPosition.top}px`,
                    left: `${popoverPosition.left}px`,
                }"
            >
                <textarea
                    v-model="commentBody"
                    class="comment-popover__textarea"
                    placeholder="Add a comment…"
                    rows="3"
                    @keydown.meta.enter.prevent="createComment"
                    @keydown.ctrl.enter.prevent="createComment"
                />
                <div class="comment-popover__footer">
                    <span class="comment-popover__hint">Ctrl↵ to submit</span>
                    <div class="comment-popover__actions">
                        <button class="comment-popover__btn comment-popover__btn--cancel" @click="cancelCommentPopover">
                            Cancel
                        </button>
                        <button class="comment-popover__btn comment-popover__btn--submit" @click="createComment">
                            Add comment
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
/* ── Comment highlight in editor ───────────────────────────────────────────── */
.comment-highlight {
    background-color: rgba(21, 204, 250, 0.2);
    cursor: pointer;
}

@keyframes comment-ripple {
    0%   { outline: 0px  solid rgba(21, 185, 250, 0.80); }
    100% { outline: 12px solid rgba(250, 204, 21, 0); }
}

.comment-highlight-flash {
    animation: comment-ripple 2s ease;
}

/* ── Remote selection decorations ──────────────────────────────────────────── */
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
}
.remote-reader-label.author {
    background: rgba(120, 180, 255);
}

/* ── Comment popover ───────────────────────────────────────────────────────── */
.comment-popover {
    position: fixed;
    z-index: 1000;
    /* Shift up so the bottom of the popover sits at the top of the selection */
    transform: translateY(-100%);
    margin-top: -6px;

    width: 320px;
    background: #131a26;
    border: 1px solid #2a3a5c;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.comment-popover__textarea {
    width: 100%;
    background: #0d1219;
    border: 1px solid #1e2a3e;
    border-radius: 6px;
    color: #e2e8f0;
    font-size: 13px;
    line-height: 1.5;
    padding: 8px 10px;
    resize: none;
    outline: none;
    font-family: inherit;
}
.comment-popover__textarea:focus {
    border-color: #4c5fa8;
    --tw-ring-color: #5032bd;
}
.comment-popover__textarea::placeholder {
    color: #4a5568;
}

.comment-popover__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
}

.comment-popover__hint {
    font-size: 11px;
    color: #4a5568;
}

.comment-popover__actions {
    display: flex;
    gap: 6px;
}

.comment-popover__btn {
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    font-family: inherit;
    transition: background 0.15s;
}
.comment-popover__btn--cancel {
    background: transparent;
    color: #94a3b8;
    border: 1px solid #2a3a5c;
}
.comment-popover__btn--cancel:hover {
    background: #1e2a3e;
}
.comment-popover__btn--submit {
    background: #5b21b6;
    color: white;
}
.comment-popover__btn--submit:hover {
    background: #6d28d9;
}

/* Fade + slight upward slide */
.popover-enter-active,
.popover-leave-active {
    transition: opacity 0.12s ease, transform 0.12s ease;
}
.popover-enter-from {
    opacity: 0;
    transform: translateY(calc(-100% + 6px));
}
.popover-leave-to {
    opacity: 0;
    transform: translateY(calc(-100% + 6px));
}
</style>