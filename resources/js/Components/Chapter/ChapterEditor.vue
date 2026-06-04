<script setup>
  import { useEditor, EditorContent } from '@tiptap/vue-3'
  import StarterKit from '@tiptap/starter-kit'
  import { useForm } from '@inertiajs/vue3'

  const props = defineProps({
    chapter: Object,
    editor: Object
  })

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
                    <editor-content :editor="editor" />
                </div>
            </div>

            <div
                class="flex justify-end text-sm text-slate-500 mt-4"
            >
                <span>2,451 words</span>
            </div>
        </div>
    </div>
</template>

<style>
.ProseMirror {
    outline: none;
}
.tiptap p {
  margin-bottom: 1rem;
}

.tiptap p:last-child {
  margin-bottom: 0;
}
</style>