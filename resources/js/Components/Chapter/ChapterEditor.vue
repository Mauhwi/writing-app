<script setup>
  import { EditorContent } from '@tiptap/vue-3'
  import { BubbleMenu } from '@tiptap/vue-3/menus'

  const props = defineProps({
    chapter: Object,
    editor: Object,
    canEdit: Boolean,
    selectionHasComment: Boolean 
  })

  defineEmits(['comment'])
  
</script>

<template>
    <div
        class="flex-1"
    >
        <div
            class="max-w-5xl mx-auto p-10"
        >
            <div
                class="p-12 min-h-[900px]"
            >
                <h1
                    class="font-merriweather font-light text-3xl md:text-4xl tracking-wide mb-8"
                >
                    &emsp14; {{ chapter.title }}
                </h1>

                <div
                    class="font-merriweather font-light text-base md:text-lg leading-loose tracking-wide text-slate-300 text-justify mb-6"
                >
                    <BubbleMenu
                        v-if="editor"
                        :editor="editor"
                        :tippy-options="{
                            placement: 'top',
                            duration: 100,
                        }"
                    >
                        <button
                         v-if="!selectionHasComment"
                            @click="$emit('comment')"
                            class="px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-sm hover:bg-slate-700"
                        >
                            Add Comment
                        </button>
                    </BubbleMenu>
                    <editor-content :editor="editor" :spellcheck="canEdit"/>
                </div>
            </div>

            <div
                class="flex justify-end text-sm text-slate-500 mt-4"
            >
                <span>{{ chapter.word_count }} words</span>
            </div>
        </div>
    </div>
</template>

<style>
.ProseMirror {
    outline: none;
    text-indent: 1em;
}
.tiptap p {
  margin-bottom: 1rem;
}

.tiptap p:last-child {
  margin-bottom: 0;
}
</style>