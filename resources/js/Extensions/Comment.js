import { Mark, mergeAttributes } from '@tiptap/core'

export const Comment = Mark.create({
    name: 'comment',

    addAttributes() {
        return {
            anchor: {
                default: null,
                parseHTML: element =>
                    element.getAttribute('data-comment-anchor'),
                renderHTML: attributes => ({
                    'data-comment-anchor': attributes.anchor,
                }),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-comment-anchor]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(
                HTMLAttributes,
                {
                    class: 'comment-highlight',
                }
            ),
            0,
        ]
    },

    addCommands() {
        return {
            setComment:
                attributes =>
                ({ commands }) =>
                    commands.setMark(this.name, attributes),

            unsetComment:
                () =>
                ({ commands }) =>
                    commands.unsetMark(this.name),
        }
    },
})