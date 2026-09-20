import { ref, onMounted } from 'vue'
import axios from 'axios'

/**
 * Echo listeners for comment + content events on the chapter channel.
 * Pure orchestration: every state change is delegated to the other composables
 * through the callbacks passed in.
 */
export function useCommentRealtime({
    getChannel,
    threads,
    currentUser,
    removeThreadLocally,
    notifyIncoming,
    refreshContent,
}) {
    const newMessageIds = ref([])

    const flagNewMessage = (messageId) => {
        newMessageIds.value.push(messageId)
        setTimeout(() => {
            newMessageIds.value = newMessageIds.value.filter(id => id !== messageId)
        }, 4000)
    }

    const onMessageCreated = async (e) => {
        let thread = threads.value.find(t => t.id === e.thread_id)

        if (!thread) {
            // Unknown thread (created by someone else): fetch it whole
            try {
                const { data } = await axios.get(route('comment-threads.show', e.thread_id))
                threads.value.push(data)
            } catch {
                return
            }
        } else if (!thread.messages.some(m => m.id === e.message.id)) {
            thread.messages.push(e.message)
        }

        if (e.message.user_id !== currentUser.value.id) {
            flagNewMessage(e.message.id)
            await notifyIncoming(e.thread_id)
        }

        // Re-fetch so the new comment mark renders correctly
        await refreshContent()
    }

    const onMessageDeleted = (e) => {
        if (e.threadDeleted) {
            removeThreadLocally(e.threadIdDeleted, e.anchor)
            return
        }

        const thread = threads.value.find(t => t.id === e.threadId)
        if (thread) {
            thread.messages = thread.messages.filter(m => m.id !== e.messageId)
        }
    }

    const onThreadDeleted = (e) => removeThreadLocally(e.threadId, e.anchor)

    onMounted(() => {
        getChannel()
            .listen('.comment.message.created', onMessageCreated)
            .listen('.comment.message.deleted', onMessageDeleted)
            .listen('.comment.thread.deleted', onThreadDeleted)
            .listen('.chapter.content.updated', refreshContent)
    })

    return { newMessageIds }
}
