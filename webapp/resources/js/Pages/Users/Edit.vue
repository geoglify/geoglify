<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: "",
    password_confirmation: "",
    role_id: props.user.roles && props.user.roles[0] ? props.user.roles[0].id : null
});

const updateUser = () => {
    form.put(route("users.update", props.user.id), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #breadcrumbs>
            <v-breadcrumbs :items="[
                { title: 'Home', disabled: false, href: '/' },
                {
                    title: 'Users',
                    disabled: false,
                    href: route('users.index'),
                },
                { title: 'Edit', disabled: true },
            ]" divider="/" />
        </template>

        <v-form @submit.prevent="updateUser">
            <v-card class="mx-auto pa-3" variant="flat" title="Edit User" subtitle="Edit user details">
                <v-card-text>

                    <v-text-field v-model="form.name" label="Name" required variant="outlined"
                        :error-messages="form.errors.name" :disabled="form.is_ldap" class="mb-4" />

                    <v-text-field v-model="form.email" label="Email" required variant="outlined"
                        :error-messages="form.errors.email" autocomplete="new-email" :disabled="form.is_ldap"
                        class="mb-4" />

                    <v-text-field v-model="form.password" label="Password" variant="outlined"
                        :error-messages="form.errors.password" type="password" autocomplete="new-password"
                        :disabled="form.is_ldap" class="mb-4" />

                    <v-text-field v-model="form.password_confirmation" label="Confirm Password" variant="outlined"
                        :error-messages="form.errors.password_confirmation" type="password" autocomplete="new-password"
                        :disabled="form.is_ldap" class="mb-4" />

                    <v-select v-model="form.role_id" label="Role" required variant="outlined" :items="props.roles"
                        item-value="id" item-title="name" :error-messages="form.errors.role_id"
                        class="mb-4"></v-select>

                </v-card-text>
                <v-card-actions class="px-4">
                    <v-btn :href="route('users.index')" color="primary" variant="tonal" class="mr-2">Cancel</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary" variant="flat"
                        v-if="$page.props.auth.can.users_edit">Update</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </AuthenticatedLayout>
</template>
