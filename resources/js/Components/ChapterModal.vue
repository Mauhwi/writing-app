<script setup>
defineProps({
    show: Boolean,
})
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]"
        >
            <div class="form-modal">
                <slot />
            </div>
        </div>
    </Teleport>
</template>