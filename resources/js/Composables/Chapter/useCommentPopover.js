import { ref, nextTick } from 'vue'

/**
 * UI state for the "add a comment" popover: visibility, position, draft text,
 * and the editor selection the comment will be attached to.
 */
export function useCommentPopover(editor) {
    const showCommentPopover = ref(false)
    const popoverPosition = ref({ top: 0, left: 0 })
    const commentBody = ref('')
    const pendingSelection = ref(null)
    const commentPopoverRef = ref(null)

    const openCommentPopover = () => {
        const { from, to } = editor.value.state.selection
        pendingSelection.value = { from, to }

        // coordsAtPos is viewport-relative, which is what position:fixed wants
        const coords = editor.value.view.coordsAtPos(from)
        popoverPosition.value = {
            top: coords.top - 12,
            left: coords.left,
        }

        showCommentPopover.value = true

        nextTick(() => {
            commentPopoverRef.value?.querySelector('textarea')?.focus()
        })
    }

    const cancelCommentPopover = () => {
        showCommentPopover.value = false
        commentBody.value = ''
        pendingSelection.value = null
    }

    return {
        showCommentPopover,
        popoverPosition,
        commentBody,
        pendingSelection,
        commentPopoverRef,
        openCommentPopover,
        cancelCommentPopover,
    }
}
