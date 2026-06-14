<script setup>
  const props = defineProps({
    thread: Object,
    replyBody: String,
    })

    defineEmits([
        'update:reply-body',
        'reply',
        'deleteMessage',
        'delete-thread',
    ])
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
                <button
                    v-if="thread"
                    @click="$emit('delete-thread')"
                    class="text-red-400 text-sm"
                >
                    Delete Thread
                </button>
                <span
                    class="text-sm text-slate-400"
                >
                    3
                </span>
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
        <div class="font-medium mb-2">
            {{ message.user.name }}
        </div>

        <p class="text-slate-300 text-sm">
            {{ message.body }}
        </p>
    </div>

    <div
        class="border border-slate-800 rounded-xl p-4"
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
            class="w-full rounded border p-2 text-black"
            placeholder="Write a reply..."
        />

        <div class="mt-3 flex justify-end">
            <button
                @click="$emit('reply')"
                class="px-4 py-2 rounded bg-indigo-600"
            >
                Reply
            </button>
        </div>
    </div>
</div>


        </div>
    </aside>
</template>