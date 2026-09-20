import { ref } from 'vue'

/**
 * Drives the "are you sure?" modal for deleting a thread or a single message.
 * pendingDeletion is null | { type: 'thread' } | { type: 'message', id }
 */
export function useDeleteConfirmation({ deleteThread, deleteMessage }) {
    const pendingDeletion = ref(null)

    const confirmDeleteThread = () => {
        pendingDeletion.value = { type: 'thread' }
    }

    const confirmDeleteMessage = (messageId) => {
        pendingDeletion.value = { type: 'message', id: messageId }
    }

    const cancelDeletion = () => {
        pendingDeletion.value = null
    }

    const confirmDeletion = async () => {
        if (!pendingDeletion.value) return

        if (pendingDeletion.value.type === 'thread') {
            await deleteThread()
        } else if (pendingDeletion.value.type === 'message') {
            await deleteMessage(pendingDeletion.value.id)
        }

        pendingDeletion.value = null
    }

    return {
        pendingDeletion,
        confirmDeleteThread,
        confirmDeleteMessage,
        cancelDeletion,
        confirmDeletion,
    }
}
