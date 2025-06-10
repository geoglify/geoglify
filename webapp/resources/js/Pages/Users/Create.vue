<script setup>
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    roles: Array,
});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role_id: null,
});

const createUser = () => {
    form.post(route("users.store"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Create User" />

    <AuthenticatedLayout>

        <template #breadcrumbs>
            <v-breadcrumbs :items="[
                { title: 'Home', disabled: false, href: '/' },
                {
                    title: 'Users',
                    disabled: false,
                    href: route('users.index'),
                },
                { title: 'Create', disabled: true },
            ]" divider="/" />
        </template>

        <v-form @submit.prevent="createUser">
            <v-card class="mx-auto" variant="flat" title="Create User">
                <v-card-text class="pt-6">

                    <v-text-field v-model="form.name" label="Name" required variant="outlined"
                        :error-messages="form.errors.name" class="mb-4" />

                    <v-text-field v-model="form.email" label="Email" required variant="outlined"
                        :error-messages="form.errors.email" autocomplete="new-email" class="mb-4" />

                    <v-text-field v-model="form.password" label="Password" required variant="outlined"
                        :error-messages="form.errors.password" type="password" autocomplete="new-password"
                        class="mb-4" />

                    <v-text-field v-model="form.password_confirmation" label="Confirm Password" required
                        variant="outlined" :error-messages="form.errors.password_confirmation" type="password"
                        autocomplete="new-password" class="mb-4" />

                    <v-select v-model="form.role_id" label="Role" outlined dense required variant="outlined"
                        :items="props.roles" item-value="id" item-title="name" class="mb-4"
                        :error-messages="form.errors.role_id"></v-select>

                </v-card-text>
                <v-card-actions class="px-4">
                    <v-btn :href="route('users.index')" color="primary" variant="tonal" class="mr-2">Cancel</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary" variant="flat"
                        v-if="$page.props.auth.can.users_create">Create</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </AuthenticatedLayout>
</template>
