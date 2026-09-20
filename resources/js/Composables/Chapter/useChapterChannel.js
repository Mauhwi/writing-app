import { onBeforeUnmount } from 'vue'

/**
 * Thin wrapper around the private Echo channel for a chapter.
 * Echo caches channels by name, so calling getChannel() repeatedly returns
 * the same instance. Only call it from onMounted / event handlers (never
 * during setup) so it stays SSR-safe.
 */
export function useChapterChannel(chapterId) {
    const name = `chapter.${chapterId}`

    const getChannel = () => Echo.private(name)

    onBeforeUnmount(() => Echo.leave(name))

    return { getChannel }
}
