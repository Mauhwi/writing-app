import axios from 'axios'

/**
 * Re-fetches chapter content from the server and swaps it into the editor
 * while preserving scroll position. Single-flight: calls that arrive while a
 * refresh is running (or within 200ms after) are dropped.
 *
 * This replaces the two copy-pasted refresh blocks (message.created and
 * content.updated) which each had their own guard flag.
 */
export function useContentRefresh({ editor, projectId, chapterId }) {
    let busy = false

    const refreshContent = async () => {
        if (!editor.value || busy) return
        busy = true

        const container = editor.value.view.dom.parentElement
        const scrollTop = container.scrollTop

        try {
            const { data } = await axios.get(
                route('projects.chapters.content', { project: projectId, chapter: chapterId })
            )
            editor.value.commands.setContent(data.content, false)
            requestAnimationFrame(() => { container.scrollTop = scrollTop })
        } finally {
            setTimeout(() => { busy = false }, 200)
        }
    }

    return { refreshContent }
}
