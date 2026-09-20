import { computed } from 'vue'
import { useEditor } from '@tiptap/vue-3'
import { useForm } from '@inertiajs/vue3'
import { Extension } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import { Comment } from '@/Extensions/Comment'
import { remoteSelectionExtension } from '@/Extensions/RemoteSelection'

const FirstLineTabIndent = Extension.create({
    name: 'firstLineTabIndent',
    addKeyboardShortcuts() {
        return {
            Tab: () => this.editor.commands.insertContent('\u2003'),
        }
    },
})

/**
 * Owns the TipTap instance, the save form and the "is the cursor in a comment" flag.
 *
 * The editor needs to react to clicks on comment marks, but the state those
 * clicks touch (active thread, popover) is created *after* the editor. So we
 * take callbacks; they only run on click, long after setup has finished.
 */
export function useChapterEditor({ chapter, canEdit, onCommentClick, onClickAway }) {
    const form = useForm({ content: chapter.content })

    const editor = useEditor({
        content: chapter.content,
        extensions: [
            StarterKit,
            FirstLineTabIndent,
            Comment,
            remoteSelectionExtension(),
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
        ],
        editable: canEdit,
        editorProps: {
            handleClick(view, pos) {
                const $pos = view.state.doc.resolve(pos)
                const commentMark = $pos.marks().find(m => m.type.name === 'comment')

                if (!commentMark) {
                    onClickAway?.()
                    return false
                }

                onCommentClick?.(commentMark.attrs.anchor)
                return true
            },
        },
        parseOptions: { preserveWhitespace: 'full' },
    })

    const selectionHasComment = computed(() => editor.value?.isActive('comment') ?? false)

    const save = () => {
        form.content = editor.value.getJSON()
        form.patch(
            route('projects.chapters.updateContent', {
                project: chapter.project_id,
                chapter: chapter.id,
            }),
            { preserveScroll: true }
        )
    }

    return { editor, form, save, selectionHasComment }
}
