<script setup>
defineProps({
    show: Boolean,
})

const emit = defineEmits(['close'])
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]"
            @click="emit('close')"
            @keydown.esc="emit('close')"
        >
            <div
                class="form-modal"
                @click.stop
            >
                <slot />
            </div>
        </div>
    </Teleport>
</template>