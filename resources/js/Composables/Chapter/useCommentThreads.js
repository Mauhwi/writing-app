import { ref, computed } from 'vue'
import axios from 'axios'

/**
 * Thread list + the active thread + all thread/message mutations
 * (create, reply, delete) and keeping the editor's comment marks in sync.
 */
export function useCommentThreads({ editor, projectId, chapterId, initialThreads }) {
    const threads = ref([...initialThreads])
    const activeThread = ref(null)
    const replyBody = ref('')

    const threadMap = computed(() =>
        Object.fromEntries(threads.value.map(thread => [thread.anchor, thread]))
    )

    const saveCommentMarks = () =>
        axios.patch(
            route('chapters.updateCommentMarks', { project: projectId, chapter: chapterId }),
            { content: editor.value.getJSON() }
        )

    /**
     * Drop a thread from local state and strip its mark from the editor.
     * Used by both local deletes and remote (Echo) deletes.
     */
    const removeThreadLocally = (threadId, anchor, { focus = false } = {}) => {
        threads.value = threads.value.filter(t => t.id !== threadId)

        const chain = editor.value.chain()
        if (focus) chain.focus()
        chain.unsetCommentByAnchor(anchor).run()

        if (activeThread.value?.id === threadId) {
            activeThread.value = null
        }
    }

    const createThread = async (body, { from, to }) => {
        const { data } = await axios.post(
            route('comment-threads.store', { project: projectId, chapter: chapterId }),
            { body }
        )

        threads.value.push(data.thread)

        editor.value
            .chain()
            .focus()
            .setTextSelection({ from, to })
            .setComment({ anchor: data.anchor })
            .run()

        saveCommentMarks()
        activeThread.value = data.thread
    }

    const submitReply = async () => {
        if (!activeThread.value || !replyBody.value.trim()) return

        const { data } = await axios.post(
            route('comment-messages.store', activeThread.value.id),
            { body: replyBody.value }
        )

        activeThread.value.messages.push(data)
        replyBody.value = ''
    }

    const deleteThread = async () => {
        if (!activeThread.value) return

        const threadId = activeThread.value.id
        const { data } = await axios.delete(route('comment-threads.destroy', threadId))

        removeThreadLocally(threadId, data.anchor, { focus: true })
        saveCommentMarks()
    }

    const deleteMessage = async (messageId) => {
        const { data } = await axios.delete(route('comment-messages.destroy', messageId))

        activeThread.value.messages = activeThread.value.messages.filter(m => m.id !== messageId)

        if (data.threadDeleted) {
            removeThreadLocally(data.threadId, data.anchor, { focus: true })
            saveCommentMarks()
        }
    }

    return {
        threads,
        activeThread,
        replyBody,
        threadMap,
        createThread,
        submitReply,
        deleteThread,
        deleteMessage,
        removeThreadLocally,
    }
}
