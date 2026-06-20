<script setup>
import { Link } from '@inertiajs/vue3'
import { useTimeAgo } from '@vueuse/core'

const props = defineProps({
    project: Object,
    chapterTitle: String,
    updatedAt: String,
    order: Number,
    processing: Boolean,
    canEdit: Boolean,
})

defineEmits(['save'])

const timeAgo = useTimeAgo(() => {
    const savedAt = new Date(props.updatedAt)
    const now = new Date()

    return savedAt > now ? now : savedAt
}, {
    showSecond: true,
    updateInterval: 15000,
})
</script>

<template>
    <header
    class="
        sticky
        top-0
        z-50
        h-[72px] border-b border-slate-800 
        bg-[#0b0f17]
        px-6 flex items-center justify-between
    "
    >
        <Link
            :href="route('projects.show', project)"
            class="text-slate-300 hover:text-white transition-colors"
        >
            ← {{ project.title }}
        </Link>

        <div class="text-center">
            <h1 class="text-xl font-semibold">
                Chapter {{ order }}: {{ chapterTitle }}
            </h1>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-400">
                Saved {{ timeAgo  }}
            </span>

            <button
                v-if="canEdit"
                class="px-4 py-2 rounded-lg bg-violet-600/40 hover:bg-violet-500/60"
                @click="$emit('save')"
                :disabled="processing"
            >
                {{ processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
    </header>
</template>