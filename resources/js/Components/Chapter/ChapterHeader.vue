<script setup>
defineProps({
    processing: Boolean,
})

defineEmits(['save'])
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
        <button
            class="text-slate-300 hover:text-white transition-colors"
        >
            ← The Last Light
        </button>

        <div class="text-center">
            <h1 class="text-xl font-semibold">
                Chapter 5: Shadows of the Past
            </h1>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-400">
                Saved 2 minutes ago
            </span>

            <button
                class="px-4 py-2 rounded-lg bg-violet-600/40 hover:bg-violet-500/60"
                @click="$emit('save')"
                :disabled="processing"
            >
                {{ processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
    </header>
</template>