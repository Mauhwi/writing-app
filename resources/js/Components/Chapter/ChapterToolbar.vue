<script setup>
const props = defineProps({
    editor: Object,
})
</script>
<template>
    <div
        class="sticky top-[72px] bg-[#0b0f17]
        z-40 h-14 border-b border-slate-800 flex items-center gap-2 px-6"
    >
        <button class="toolbar-btn" @click="editor.chain().focus().toggleBold().run()">B</button>
        <button class="toolbar-btn italic" @click="editor.chain().focus().toggleItalic().run()">I</button>
        <button class="toolbar-btn" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">H2</button>
        <button class="toolbar-btn" @click="editor.chain().focus().toggleBlockquote().run()">❝</button>
        <button class="toolbar-btn" @click="editor.chain().focus().setHorizontalRule().run()">―</button>

        <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('left').run()">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4h14v2H3V4zm0 5h10v2H3V9zm0 5h14v2H3v-2z"/>
        </svg>
        </button>
        <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('center').run()">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4h14v2H3V4zm3 5h8v2H6V9zm-3 5h14v2H3v-2z"/>
        </svg>
        </button>
        <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('right').run()">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4h14v2H3V4zm7 5h7v2h-7V9zm-7 5h14v2H3v-2z"/>
        </svg>
        </button>
        

        <div class="w-px h-6 bg-slate-700 mx-2" />

        <button class="toolbar-btn" @click="editor.chain().focus().undo().run()">↶</button>
        <button class="toolbar-btn" @click="editor.chain().focus().redo().run()">↷</button>
    </div>
</template>

<style scoped>
.toolbar-btn {
    @apply px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors;
}
</style>