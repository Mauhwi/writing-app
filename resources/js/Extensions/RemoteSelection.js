import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from 'prosemirror-state'
import { Decoration, DecorationSet } from 'prosemirror-view'

export const remoteSelectionExtension = () => {
    return Extension.create({
        name: 'remoteSelection',

        addProseMirrorPlugins() {
            return [
                new Plugin({
                    key: new PluginKey('remoteSelection'),

                    state: {
                        init() {
                            return DecorationSet.empty
                        },

                        apply(tr, oldState) {
                            const meta = tr.getMeta('remoteSelection')

                            if (!meta) {
                                return oldState.map(tr.mapping, tr.doc)
                            }

                            const decorations = Object.values(meta)
                            .filter(sel => !sel.cleared)
                            .map(sel => {
                                return Decoration.inline(
                                    sel.from,
                                    sel.to,
                                    {
                                        class: `remote-selection role-${sel.role} user-${sel.userId}`,
                                    }
                                )
                            })

                            return DecorationSet.create(tr.doc, decorations)
                        },
                    },

                    props: {
                        decorations(state) {
                            return this.getState(state)
                        },
                    },
                }),
            ]
        },
    })
}