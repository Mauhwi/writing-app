<script setup>
const comments = [
    {
        author: 'Reader',
        text: "Honestly cringe and I'm here for it",
    },
    {
        author: 'Reader',
        text: 'Maybe expand a bit more here.',
    },
    {
        author: 'Reader',
        text: 'Ewe',
    },
]
</script>

<template>
    <aside
        class="w-96 border-l border-slate-800 overflow-y-auto"
    >
        <div class="p-6">

            <div
                class="flex items-center justify-between mb-6"
            >
                <h2 class="font-semibold">
                    Comments
                </h2>

                <span
                    class="text-sm text-slate-400"
                >
                    3
                </span>
            </div>

            <div class="space-y-4">

                <div
                    v-for="comment in comments"
                    :key="comment.text"
                    class="bg-slate-900 border border-slate-800 rounded-xl p-4"
                >
                    <div
                        class="font-medium mb-2"
                    >
                        {{ comment.author }}
                    </div>

                    <p
                        class="text-slate-300 text-sm"
                    >
                        {{ comment.text }}
                    </p>
                </div>

            </div>

        </div>
    </aside>
</template>