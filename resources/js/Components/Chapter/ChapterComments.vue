<script setup>
  const props = defineProps({
    thread: Object,
    replyBody: String,
    })

    defineEmits([
        'update:reply-body',
        'reply',
        'delete-message',
        'delete-thread',
    ])
</script>

<template>
    <aside
        class="w-96 border-l border-slate-800 overflow-y-auto"
    >
        <div class="p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="font-semibold text-white">Comments</h2>
                <div class="flex items-center gap-4">
                    <button 
                        v-if="thread"
                        @click="$emit('delete-thread')"
                        class="text-slate-400 hover:text-slate-200 transition-colors" 
                        aria-label="Delete thread"
                    >
                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    </button>
                    <span class="text-sm text-slate-400">3</span>
                </div>
            </div>

            <div
                v-if="thread"
                class="space-y-4"
            >

                
                <div
                    v-for="message in thread.messages"
                    :key="message.id"
                    class="bg-slate-900 border border-slate-800 rounded-xl p-4"
                >
                    <div class="flex justify-between items-center mb-2">
                        <div class="font-medium text-white">{{ message.user.name }}</div>
                        <button 
                            @click="$emit(
                                'delete-message',
                                message.id
                            )"
                            class="text-slate-500 hover:text-slate-300 transition-colors" 
                            aria-label="Delete comment"
                        >
                            <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>

                    <p class="text-slate-300 text-sm">
                        {{ message.body }}
                    </p>
                </div>

                <div
                    class="bg-slate-900 border border-slate-800 rounded-xl p-4"
                >
                    <textarea
                        :value="replyBody"
                        @input="
                            $emit(
                                'update:reply-body',
                                $event.target.value
                            )
                        "
                        rows="4"
                        class="comment-textarea"
                        placeholder="Write a reply..."
                    />

                    <div class="mt-3 flex justify-end">
                        <button
                            @click="$emit('reply')"
                            class="bg-violet-600/40 hover:bg-violet-500/60 rounded-lg px-4 py-2 rounded bg-indigo-600"
                        >
                            Reply
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </aside>
</template>