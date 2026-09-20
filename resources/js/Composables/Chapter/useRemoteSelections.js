import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

/**
 * Broadcasts the local selection over Echo whispers and renders other users'
 * selections (highlight via editor meta + a short-lived "X is here" label).
 */
export function useRemoteSelections({ editor, getChannel, currentUser }) {
    const remoteSelections = ref({})
    const remoteLabelPosition = ref(null)
    let labelTimeout

    const hideLabel = () => {
        clearTimeout(labelTimeout)
        remoteLabelPosition.value = null
    }

    const showLabel = (selection) => {
        clearTimeout(labelTimeout)

        if (!selection.from || !editor.value) {
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
    }

    // ── outgoing ──

    const sendSelection = () => {
        if (!editor.value) return
        const { from, to } = editor.value.state.selection

        getChannel().whisper('selection', {
            userId: currentUser.value.id,
            userName: currentUser.value.name,
            role: currentUser.value.role,
            from,
            to,
        })
    }

    // Bound via watch so it doesn't matter when useEditor creates the instance
    watch(editor, (ed, prev) => {
        prev?.off('selectionUpdate', sendSelection)
        ed?.on('selectionUpdate', sendSelection)
    }, { immediate: true })

    const onDomSelectionChange = () => {
        const selection = window.getSelection()
        if (!selection || selection.isCollapsed) {
            getChannel().whisper('selection', {
                userId: currentUser.value.id,
                cleared: true,
            })
        }
    }

    // ── incoming ──

    onMounted(() => {
        getChannel().listenForWhisper('selection', (payload) => {
            if (payload.userId === currentUser.value.id) return

            if (payload.cleared) {
                hideLabel()
                return
            }

            remoteSelections.value[payload.userId] = payload
            showLabel(payload)

            editor.value?.view.dispatch(
                editor.value.state.tr.setMeta('remoteSelection', remoteSelections.value)
            )
        })

        document.addEventListener('selectionchange', onDomSelectionChange)
    })

    onBeforeUnmount(() => {
        clearTimeout(labelTimeout)
        document.removeEventListener('selectionchange', onDomSelectionChange)
    })

    return { remoteLabelPosition }
}
