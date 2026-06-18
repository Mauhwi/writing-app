<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    parts: Array,
    projectId: Number,
})
</script>

<template>
<aside
    class="
        sticky
        top-[72px]
        h-[calc(100vh-72px)]
        w-80
        border-r
        border-slate-800
        overflow-y-auto
        scrollbar-writing
    "
>
        <div class="p-6">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold tracking-wide">
                    Chapters
                </h2>

                <button
                    class="w-8 h-8 rounded-lg border border-slate-700"
                >
                    ☰
                </button>
            </div>

            <div
                v-for="part in parts"
                :key="part.title"
                class="mb-8"
            >
                <h3
                    class="text-sm uppercase text-slate-400 mb-3"
                >
                    {{ part.title }}
                </h3>

                <div class="space-y-1">
                    <Link  v-for="chapter in part.chapters"
                    :key="chapter"
                    :href="route('projects.chapters.show', { project: projectId, chapter: chapter.id })">
                    <button
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors"
                    >
                        {{ chapter.title }}
                    </button>
                    </Link>
                </div>
            </div>

        </div>
    </aside>
</template>