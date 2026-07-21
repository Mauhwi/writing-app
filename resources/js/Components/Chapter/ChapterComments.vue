<script setup>
import { ref } from 'vue';

const props = defineProps({
    thread: Object,
    replyBody: String,
    unreadCount: Number,
    unreadThreadIds: Array,
    newMessageIds: { type: Array, default: () => [] },
})

defineEmits([
    'update:reply-body',
    'reply',
    'delete-message',
    'delete-thread',
    'go-to-next-unread',
])

const unreadThreadIds = props.unreadThreadIds
</script>

<template>
    <aside class="w-80 border-l border-slate-800 sticky top-[72px] h-[calc(100vh-72px)] overflow-y-auto flex flex-col">

        <!-- Unread banner -->
        <div
            v-if="unreadCount > 0"
            class="mx-4 mt-4 cursor-pointer rounded-md border border-amber-300/60 bg-amber-400/10 px-3 py-2 text-sm text-amber-300 flex items-center gap-2 flex-shrink-0"
            @click="$emit('go-to-next-unread')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            {{ unreadCount }} unread {{ unreadCount === 1 ? 'comment' : 'comments' }}
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pt-5 pb-4 flex-shrink-0">
            <h2 class="font-semibold text-white text-sm tracking-wide">Comments</h2>

            <button
                v-if="thread && thread.canDeleteThread"
                @click="$emit('delete-thread')"
                class="text-slate-500 hover:text-slate-300 transition-colors"
                aria-label="Delete thread"
            >
                <!-- Opossum trash icon — unchanged from original -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50" height="50" fill="none"
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M 39 9.5 Q 39 11 40.5 11 Q 39 11 39 12.5 Q 39 11 37.5 11 Q 39 11 39 9.5 Z" stroke-width="0.5" fill="currentColor" />
                    <path d="M 42 13.5 Q 42 14.25 42.75 14.25 Q 42 14.25 42 15 Q 42 14.25 41.25 14.25 Q 42 14.25 42 13.5 Z" stroke-width="0.5" fill="currentColor" />
                    <path d="M 8 19 Q 8 19.75 8.75 19.75 Q 8 19.75 8 20.5 Q 8 19.75 7.25 19.75 Q 8 19.75 8 19.5 Z" stroke-width="0.5" fill="currentColor" />
                    <g transform="rotate(-15, 21.5, 10.5)">
                        <path d="M 7 10.5 C 7 6, 36 6, 36 10.5" />
                        <path d="M 17.5 6 Q 21.5 2.5 25.5 6" />
                    </g>
                    <g>
                        <path d="M 17 12.5 C 16 10, 19.5 8.5, 20.5 10.5 C 21 11.5, 20 13, 18.5 13" />
                        <path d="M 32 13 C 33 10.5, 29.5 9, 28.5 11 C 28 12, 29 13.5, 30.5 13.5" />
                        <path d="M 18.5 16 C 20 12.5, 24 12.5, 36 17" />
                        <path d="M 36 17 C 33 18 27 21 23 21" />
                        <circle cx="36" cy="17" r="0.75" fill="currentColor" />
                        <circle cx="26.5" cy="14.5" r="1" fill="currentColor" />
                        <path d="M 35 16.5 Q 39 15.5 42 16" stroke-width="0.5" />
                        <path d="M 35.5 17 Q 39.5 17.5 42.5 18.5" stroke-width="0.5" />
                        <path d="M 35 17.5 Q 38.5 19.5 40.5 21.5" stroke-width="0.5" />
                        <path d="M 16 21 Q 16 19 17 19 Q 17.5 19 17.5 21" />
                        <path d="M 17.5 21 Q 17.5 18.5 18.5 18.5 Q 19 18.5 19 21" />
                        <path d="M 19 21 Q 19 19 20 19 Q 20.5 19 20.5 21" />
                        <path d="M 30 21 Q 30 19 31 19 Q 31.5 19 31.5 21" />
                        <path d="M 31.5 21 Q 31.5 18.5 32.5 18.5 Q 33 18.5 33 21" />
                        <path d="M 33 21 Q 33 19 34 19 Q 34.5 19 34.5 21" />
                    </g>
                    <g>
                        <path d="M 11 20.5 C 11 22.5, 39 22.5, 39 20.5" />
                        <path d="M 11 20.5 L 13.5 44 C 13.5 45.5, 36.5 45.5, 36.5 44 L 39 20.5" />
                        <path d="M 16.5 22 L 18 43" /><path d="M 19.5 22.5 L 20.5 43.5" />
                        <path d="M 22.5 22.5 L 23 44" /><path d="M 25.5 22.5 L 25.5 44" />
                        <path d="M 28 22.5 L 27.5 44" /><path d="M 30.5 22.5 L 29.5 43.5" />
                        <path d="M 33.5 22 L 32 43" />
                        <path d="M 11 24.5 Q 8 26 9.5 28.5 Q 10.5 29.5 11.5 28.5" />
                        <path d="M 39 24.5 Q 42 26 40.5 28.5 Q 39.5 29.5 38.5 28.5" />
                    </g>
                    <path d="M 9 47 L 41 47" stroke-width="0.5" />
                </svg>
            </button>
        </div>

        <!-- Thread messages -->
        <div v-if="thread" class="flex-1 flex flex-col px-4 pb-4 gap-3 overflow-y-auto">
            <div
                v-for="message in thread.messages"
                :key="message.id"
                class="bg-slate-900 border border-slate-800 rounded-xl p-4"
                :class="{ 'message-new': newMessageIds.includes(message.id) }"
            >
                <div class="flex justify-between items-start mb-2">
                    <div class="font-medium text-white text-sm">{{ message.user.name }}</div>
                    <button
                        v-if="message.canDelete"
                        @click="$emit('delete-message', message.id)"
                        class="text-slate-600 hover:text-slate-400 transition-colors flex-shrink-0 ml-2"
                        aria-label="Delete comment"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </div>
                <p class="text-slate-300 text-sm leading-relaxed">{{ message.body }}</p>
            </div>

            <!-- Reply box -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 mt-1">
                <textarea
                    :value="replyBody"
                    @input="$emit('update:reply-body', $event.target.value)"
                    @keydown.ctrl.enter.prevent="$emit('reply')"
                    rows="3"
                    class="comment-textarea w-full bg-transparent text-slate-300 text-sm resize-none outline-none placeholder-slate-600"
                    placeholder="Reply…"
                />
                <div class="mt-2 flex justify-end">
                    <button
                        @click="$emit('reply')"
                        class="bg-violet-600/40 hover:bg-violet-500/60 transition-colors rounded-lg px-3 py-1.5 text-sm text-white"
                    >
                        Reply
                    </button>
                </div>
            </div>
        </div>

        <!-- No thread selected -->
        <div v-else class="flex-1 flex items-center justify-center text-slate-600 text-sm px-6 text-center">
            Click a highlighted passage to view its comments
        </div>

    </aside>
</template>