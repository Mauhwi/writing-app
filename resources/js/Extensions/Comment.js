import { Mark, mergeAttributes } from '@tiptap/core'

export const Comment = Mark.create({
    name: 'comment',

    inclusive: false,

    addOptions() {
        return {
            HTMLAttributes: {},
        }
    },

    addAttributes() {
        return {
            anchor: { default: null },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-comment-anchor]',
                getAttrs: (el) => ({
                    anchor: el.getAttribute('data-comment-anchor'),
                }),
                preserveWhitespace: 'full',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(
                this.options.HTMLAttributes,
                {
                    'data-comment-anchor': HTMLAttributes.anchor,
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

            unsetCommentByAnchor:
                anchor =>
                ({ state, dispatch }) => {
                    const tr = state.tr

                    state.doc.descendants(
                        (node, pos) => {
                            node.marks.forEach(mark => {
                                if (
                                    mark.type.name === 'comment' &&
                                    mark.attrs.anchor === anchor
                                ) {
                                    tr.removeMark(
                                        pos,
                                        pos + node.nodeSize,
                                        mark.type
                                    )
                                }
                            })
                        }
                    )

                    dispatch(tr)

                    return true
                },
        }
    },
})