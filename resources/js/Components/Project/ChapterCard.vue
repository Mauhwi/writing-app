<script setup>
    import Modal from '@/Components/Modal.vue'
    import { computed, ref } from 'vue';
    import { Link, useForm } from '@inertiajs/vue3';

    const props = defineProps({
        project: Object,
        chapter: Object,
        cardSize: String,
        menuOpen: Boolean, 
        canEdit: Boolean
    }) 

    defineEmits(['toggle-menu'])

    const sizeClass = computed(() => {
        switch (props.cardSize) {
            case 'compact':
                return 'chapter-card-compact'

            case 'large':
                return 'chapter-card-large'

            default:
                return 'chapter-card-medium'
        }
    })

    const isEditModalOpen = ref(false)

    const form = useForm({
        title: props.chapter.title,
        summary: props.chapter.summary,
    })

    const submit = () => {
        form.patch(
            route('projects.chapters.updateDetails', {
                project: props.project.id,
                chapter: props.chapter.id,
            }),
            {
                preserveScroll: true,

                onSuccess: () => {
                    isEditModalOpen.value = false
                }
            }
        )
    }
</script>

<template>
    <article
        :class="[
            'chapter-card flex flex-col justify-between w-64 h-80 p-5 bg-[#0e131f] border border-zinc-800 rounded-xl text-center text-zinc-100',
            sizeClass
        ]"
    >

        <div class="flex items-center justify-between w-full">
            <button class="drag-handle text-zinc-500 hover:text-zinc-300 cursor-grab">⋮⋮</button>
            <div class="chapter-number">
                {{ chapter.order}}
            </div>
            <div class="relative menu-container">
                <button v-if="canEdit"
                    @click.stop="$emit('toggle-menu')"
                    class="more-options text-zinc-500 hover:text-zinc-300"
                >
                    ⋮
                </button>

                <div
                    v-if="menuOpen"
                    @click.stop
                    class="
                        absolute top-8 right-0 z-50 w-56 overflow-hidden rounded-xl border border-zinc-800 bg-[#0e131f] shadow-xl">
                    <Link
                        :href="route('projects.chapters.show', {
                            project: project.id,
                            chapter: chapter.id
                        })"
                        class="block px-4 py-3 text-sm text-zinc-200 hover:bg-zinc-800/50"
                    >
                        Open Chapter
                    </Link>

                    <button
                        @click="
                            isEditModalOpen = true;
                            $emit('toggle-menu');
                        "
                        class="block w-full px-4 py-3 text-left text-sm text-zinc-200 hover:bg-zinc-800/50"
                    >
                        Edit Details
                    </button>

                    <div class="border-t border-zinc-800"></div>

                    <button
                        class="block w-full px-4 py-3 text-left text-sm text-red-400 hover:bg-red-500/10"
                    >
                        Delete Chapter
                    </button>
                </div>
            </div>

        </div>

        <div class="flex-1 flex flex-col justify-center items-center px-2 my-4 space-y-3">
            <Link
                :href="route('projects.chapters.show', {
                    project: project.id,
                    chapter: chapter.id
                })"
            >
            <h3 class="text-lg font-bold tracking-tight text-white">{{ chapter.title }}</h3>
            </Link>
            <p class="text-sm text-zinc-400 leading-relaxed max-w-[180px]">
            {{ chapter.summary }}
            </p>
        </div>

        <div class="flex items-center justify-between w-full text-xs text-zinc-500 pt-2 border-t border-zinc-800/30">
            <span>{{ chapter.word_count }} words</span>
            <span>{{ chapter.updated_at_human }}</span>
        </div>

            <Modal v-if="canEdit" :show="isEditModalOpen">

        <h3 class="text-lg font-bold mb-4">
            Edit Chapter
        </h3>

        <form
            @submit.prevent="submit"
            class="space-y-4"
        >

            <div>
                <label class="block text-sm font-medium">
                    Chapter Title
                </label>

                <input
                    v-model="form.title"
                    type="text"
                    class="search-input"
                />

                <span
                    v-if="form.errors.title"
                    class="text-red-500 text-sm"
                >
                    {{ form.errors.title }}
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium">
                    Chapter Summary
                </label>

                <textarea
                    v-model="form.summary"
                    class="search-input"
                />
            </div>

            <div class="flex justify-end space-x-2 pt-4">

                <button
                    type="button"
                    @click="isEditModalOpen = false"
                    class="secondary-button"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="primary-button"
                >
                    Save
                </button>

            </div>

        </form>

    </Modal>
    </article>
</template>