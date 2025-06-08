<script setup>
import { Head } from '@inertiajs/vue3'
import ErrorLayout from '@/Layouts/ErrorLayout.vue'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const status = computed(() => page.props.status)

const title = computed(() => {
    switch (status.value) {
        case 503: return 'Service Unavailable'
        case 500: return 'Server Error'
        case 419: return 'Session Expired'
        case 418: return "I'm a teapot"
        case 404: return 'Page Not Found'
        case 403: return 'Forbidden'
        default: return 'Error'
    }
})

const message = computed(() => {
    switch (status.value) {
        case 503: return 'Sorry, we are doing some maintenance. Please check back soon.'
        case 500: return 'Whoops, something went wrong on our servers.'
        case 419: return 'Sorry, your session expired.'
        case 418: return 'Sorry, I am not a coffee machine.'
        case 404: return 'Sorry, the page you are looking for could not be found.'
        case 403: return 'Sorry, you are forbidden from accessing this page.'
        default: return 'Sorry, something went wrong unexpectedly.'
    }
})
</script>

<template>
    <ErrorLayout>

        <Head :title="title" />

        <div class="text-center py-12">
            <p class="text-h2 mb-2"><b class="font-weight-black">{{ title }}</b> / {{ status }}</p>
            <p class="text-h6 text-medium-emphasis">{{ message }}</p>
        </div>
    </ErrorLayout>
</template>
