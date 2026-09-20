import { ref, computed, watch } from 'vue'
import axios from 'axios'

/**
 * Unread-thread tracking plus "jump to next unread" navigation.
 */
export function useUnreadThreads({ editor, threads, activeThread, initialIds }) {
    const unreadIds = ref([...initialIds])
    const unreadCount = computed(() => unreadIds.value.length)

    const markRead = async (threadId) => {
        await axios.post(route('comment-threads.read', threadId))
        unreadIds.value = unreadIds.value.filter(id => id !== threadId)
    }

    const addUnread = (threadId) => {
        if (!unreadIds.value.includes(threadId)) {
            unreadIds.value.unshift(threadId)
        }
    }

    /** A message from someone else arrived: read it now if that thread is open, else flag it. */
    const notifyIncoming = async (threadId) => {
        if (activeThread.value?.id === threadId) {
            await markRead(threadId)
        } else {
            addUnread(threadId)
        }
    }

    // Opening a thread marks it read
    watch(activeThread, (thread) => {
        if (thread) markRead(thread.id)
    })

    // ── navigation ──

    const flashComment = (anchor) => {
        const el = editor.value.view.dom.querySelector(`[data-comment-anchor="${anchor}"]`)
        if (!el) return

        el.classList.add('comment-highlight-flash')
        setTimeout(() => el.classList.remove('comment-highlight-flash'), 2000)
    }

    const focusCommentAnchor = (anchor) => {
        let foundPos = null

        editor.value.state.doc.descendants((node, pos) => {
            const hit = node.marks?.find(
                m => m.type.name === 'comment' && m.attrs.anchor === anchor
            )
            if (hit) {
                foundPos = pos
                return false
            }
            return true
        })

        if (foundPos === null) return

        editor.value.chain().focus().setTextSelection(foundPos).run()

        const coords = editor.value.view.coordsAtPos(foundPos)
        window.scrollTo({ top: coords.top - 200, behavior: 'smooth' })
    }

    const goToNextUnread = () => {
        const threadId = unreadIds.value[0]
        if (!threadId) return

        const thread = threads.value.find(t => t.id === threadId)
        if (!thread) return

        activeThread.value = thread
        focusCommentAnchor(thread.anchor)
        flashComment(thread.anchor)
    }

    return { unreadIds, unreadCount, notifyIncoming, goToNextUnread }
}
