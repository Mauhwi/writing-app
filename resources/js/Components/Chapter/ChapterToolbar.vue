<template>
    <div
        class="sticky top-[72px] bg-[#0b0f17]
        z-40 h-14 border-b border-slate-800 flex items-center gap-2 px-6"
    >
        <button class="toolbar-btn">B</button>
        <button class="toolbar-btn italic">I</button>
        <button class="toolbar-btn">H2</button>
        <button class="toolbar-btn">❝</button>
        <button class="toolbar-btn">―</button>

        <div class="w-px h-6 bg-slate-700 mx-2" />

        <button class="toolbar-btn">↶</button>
        <button class="toolbar-btn">↷</button>
    </div>
</template>

<style scoped>
.toolbar-btn {
    @apply px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors;
}
</style>