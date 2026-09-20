<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ChapterHeader from '@/Components/Chapter/ChapterHeader.vue'
import ChapterSidebar from '@/Components/Chapter/ChapterSidebar.vue'
import ChapterToolbar from '@/Components/Chapter/ChapterToolbar.vue'
import ChapterEditor from '@/Components/Chapter/ChapterEditor.vue'
import ChapterComments from '@/Components/Chapter/ChapterComments.vue'
import Modal from '@/Components/Modal.vue'

import { useChapterChannel } from '@/Composables/Chapter/useChapterChannel'
import { useChapterEditor } from '@/Composables/Chapter/useChapterEditor'
import { useGlobalShortcuts } from '@/Composables/Chapter/useGlobalShortcuts'
import { useContentRefresh } from '@/Composables/Chapter/useContentRefresh'
import { useCommentPopover } from '@/Composables/Chapter/useCommentPopover'
import { useCommentThreads } from '@/Composables/Chapter/useCommentThreads'
import { useDeleteConfirmation } from '@/Composables/Chapter/useDeleteConfirmation'
import { useUnreadThreads } from '@/Composables/Chapter/useUnreadThreads'
import { useRemoteSelections } from '@/Composables/Chapter/useRemoteSelections'
import { useCommentRealtime } from '@/Composables/Chapter/useCommentRealtime'

const props = defineProps({
    project: Object,
    chapter: Object,
    canEdit: Boolean,
    commentThreads: Array,
    unreadThreads: Number,
    unreadThreadIds: Array,
})

const currentUser = computed(() => usePage().props.auth.user)
const { getChannel } = useChapterChannel(props.chapter.id)

// ── Editor ───────────────────────────────────────────────────────────────────
// The click callbacks reference state declared below; that's fine because they
// only run on user click, after setup has completed.
const { editor, form, save, selectionHasComment } = useChapterEditor({
    chapter: props.chapter,
    canEdit: props.canEdit,
    onCommentClick: (anchor) => { activeThread.value = threadMap.value[anchor] },
    onClickAway: () => { if (showCommentPopover.value) cancelCommentPopover() },
})

const { refreshContent } = useContentRefresh({
    editor,
    projectId: props.project.id,
    chapterId: props.chapter.id,
})

// ── Comments ─────────────────────────────────────────────────────────────────
const {
    showCommentPopover, popoverPosition, commentBody, pendingSelection,
    commentPopoverRef, openCommentPopover, cancelCommentPopover,
} = useCommentPopover(editor)

const {
    threads, activeThread, replyBody, threadMap,
    createThread, submitReply, deleteThread, deleteMessage, removeThreadLocally,
} = useCommentThreads({
    editor,
    projectId: props.project.id,
    chapterId: props.chapter.id,
    initialThreads: props.commentThreads,
})

const submitComment = async () => {
    if (!commentBody.value.trim() || !pendingSelection.value) return

    await createThread(commentBody.value, pendingSelection.value)
    cancelCommentPopover()
}

const {
    pendingDeletion, confirmDeleteThread, confirmDeleteMessage,
    cancelDeletion, confirmDeletion,
} = useDeleteConfirmation({ deleteThread, deleteMessage })

const { unreadIds, unreadCount, notifyIncoming, goToNextUnread } = useUnreadThreads({
    editor,
    threads,
    activeThread,
    initialIds: props.unreadThreadIds,
})

// ── Realtime ─────────────────────────────────────────────────────────────────
const { remoteLabelPosition } = useRemoteSelections({ editor, getChannel, currentUser })

const { newMessageIds } = useCommentRealtime({
    getChannel,
    threads,
    currentUser,
    removeThreadLocally,
    notifyIncoming,
    refreshContent,
})

// ── Shortcuts ────────────────────────────────────────────────────────────────
useGlobalShortcuts({
    onSave: save,
    onEscape: () => { if (showCommentPopover.value) cancelCommentPopover() },
})
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
                    :new-message-ids="newMessageIds"
                    :reply-body="replyBody"
                    @update:reply-body="replyBody = $event"
                    @reply="submitReply"
                    @delete-message="confirmDeleteMessage"
                    @delete-thread="confirmDeleteThread"
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
                    @keydown.meta.enter.prevent="submitComment"
                    @keydown.ctrl.enter.prevent="submitComment"
                />
                <div class="comment-popover__footer">
                    <span class="comment-popover__hint">Ctrl↵ to submit</span>
                    <div class="comment-popover__actions">
                        <button class="comment-popover__btn comment-popover__btn--cancel" @click="cancelCommentPopover">
                            Cancel
                        </button>
                        <button class="comment-popover__btn comment-popover__btn--submit" @click="submitComment">
                            Add comment
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Delete confirmation modal -->
    <Modal :show="!!pendingDeletion" @close="cancelDeletion">
        <h3 class="text-zinc-100 text-base font-semibold mb-2">
            {{ pendingDeletion?.type === 'thread' ? 'Delete this thread?' : 'Delete this comment?' }}
        </h3>
        <p class="text-slate-400 mb-5">
            {{ pendingDeletion?.type === 'thread'
                ? 'This will permanently delete the thread and all its replies.'
                : 'This comment will be permanently deleted.' }}
        </p>
        <div class="flex justify-end gap-2">
            <button class="confirm-modal-btn confirm-modal-btn--cancel" @click="cancelDeletion">
                Cancel
            </button>
            <button class="confirm-modal-btn confirm-modal-btn--danger" @click="confirmDeletion">
                Delete
            </button>
        </div>
    </Modal>
</template>

<style>
.comment-highlight {
    background-color: rgba(21, 204, 250, 0.2);
    cursor: pointer;
}

@keyframes comment-ripple {
    0%   { outline: 0px  solid rgba(21, 185, 250, 0.80); }
    100% { outline: 12px solid rgba(250, 204, 21, 0); }
}

@keyframes message-glow {
    0%, 100% {
        background-color: rgba(139, 92, 246, 0.22);
    }
    50% {
        background-color: rgba(139, 92, 246, 0.08);
    }
}

.comment-highlight-flash {
    animation: comment-ripple 2s ease;
}

.message-new {
    border-color: rgba(139, 92, 246, 0.5);
    animation:
        message-ripple 1.2s ease-out,
        message-glow 2s ease-in-out 2;
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
}
.remote-reader-label.author {
    background: rgba(120, 180, 255);
}

.comment-popover {
    position: fixed;
    z-index: 1000;
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

.confirm-modal-btn {
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    font-family: inherit;
}
.confirm-modal-btn--cancel {
    background: transparent;
    color: #94a3b8;
    border: 1px solid #2a3a5c;
}
.confirm-modal-btn--cancel:hover {
    background: #1e2a3e;
}
.confirm-modal-btn--danger {
    background: #b91c1c;
    color: white;
}
.confirm-modal-btn--danger:hover {
    background: #dc2626;
}
</style>