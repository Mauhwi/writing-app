import { onMounted, onBeforeUnmount } from 'vue'

/**
 * Ctrl/Cmd+S and Escape. Listener is registered on mount and removed on unmount
 */
export function useGlobalShortcuts({ onSave, onEscape }) {
    const handler = (event) => {
        if ((event.ctrlKey || event.metaKey) && event.code === 'KeyS') {
            event.preventDefault()
            onSave()
        }

        if (event.key === 'Escape') {
            onEscape?.()
        }
    }

    onMounted(() => document.addEventListener('keydown', handler))
    onBeforeUnmount(() => document.removeEventListener('keydown', handler))
}
