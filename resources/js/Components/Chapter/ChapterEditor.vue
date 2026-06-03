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
            class="max-w-6xl mx-auto p-10"
        >
            <div
                class="p-12 min-h-[900px]"
            >
                <h1
                    class="text-5xl font-serif mb-10"
                >
                    {{ chapter.title }}
                </h1>

                <div
                    class="prose prose-invert max-w-none"
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
</style>