<script>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    data() {
        return {
            search: "",
            page: 1,
            itemsPerPage: 10,
            totalItems: 0,
            loading: false,
            deleting: false,
            serverItems: [],

            // Headers for the data table
            headers: [
                { title: "Name", key: "name", align: "start", sortable: false, width: "140px" },
                {
                    title: "Email",
                    key: "email",
                    align: "start",
                    sortable: false,
                    width: "140px"
                },
                {
                    title: "Roles",
                    key: "roles",
                    align: "start",
                    sortable: false,
                },
                {
                    title: "Last Login",
                    key: "last_login_at",
                    align: "start",
                    sortable: true,
                    width: "180px",
                    value: (item) => this.$formatDateTime(item.last_login_at),
                },
                {
                    title: "Last IP",
                    key: "last_login_ip",
                    align: "start",
                    sortable: false,
                    value: (item) => item.last_login_ip || "N/A",
                },
                {
                    title: "Last Device",
                    key: "last_login_user_agent",
                    align: "start",
                    sortable: false,
                    value: (item) => item.last_login_user_agent || "N/A",
                },
                { title: "", key: "actions", align: "end", sortable: false, width: "180px" },
            ],

            deleteModalOpen: false,
            deleteItem: null,
        };
    },
    methods: {
        // Method to load items
        loadItems({ page, itemsPerPage, search }) {
            // Set loading to true
            this.loading = true;

            // Fetch items from the server
            fetch(route("users.list"), {
                page: page,
                itemsPerPage: itemsPerPage,
                search: search,
            })
                .then((response) => {
                    return response.json();
                })
                .then((data) => {
                    this.serverItems = data.items;
                    this.totalItems = data.total;
                    this.loading = false;
                })
                .catch(() => {
                    // Failed to fetch data from the server
                    this.loading = false;
                    this.serverItems = [];
                    this.totalItems = 0;
                });
        },

        // Method to delete a user
        openDeleteModal(id) {
            this.deleteItem = id;
            this.deleteModalOpen = true;
        },

        // Method to close the modal
        closeModal() {
            this.deleteItem = null;
            this.deleteModalOpen = false;
        },

        // Method to delete the user
        async deleteUser() {

            this.deleting = true;

            // Delete the user
            await fetch(route("users.destroy", this.deleteItem), {
                method: "DELETE",
            }).then(() => {

                // Close the modal
                this.deleteModalOpen = false;
                this.deleting = false;

                // Reload the items
                this.loadItems({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                    search: this.search,
                });
            });
        },
    },
};
</script>

<template>

    <Head title="Users" />

    <AuthenticatedLayout>
        <template #breadcrumbs>
            <v-breadcrumbs :items="[
                { title: 'Home', disabled: false, href: '/' },
                { title: 'Users', disabled: true },
            ]" divider="/" />
        </template>

        <v-card class="mx-auto" variant="flat" title="Users">
            <template v-slot:append>
                <v-btn color="blue-grey-lighten-4" class="ml-2" elevation="0" :href="route('users.create')" prepend-icon="mdi-light:account"
                    v-if="$page.props.auth.can.users_create">Create</v-btn>
            </template>

            <v-card-text>
                <v-text-field v-model="search" label="Search" prepend-inner-icon="mdi-light:magnify" variant="outlined"
                    hide-details single-line></v-text-field>

                <v-data-table-server v-model:items-per-page="itemsPerPage" :headers="headers" :items="serverItems"
                    :items-length="totalItems" :loading="loading" :search="search" @update:options="loadItems">

                    <template v-slot:item.roles="{ item }">
                        <v-chip v-for="role in item.roles" :key="role" label class="text-uppercase mr-1 w-32"
                            color="primary" variant="tonal" density="comfortable">
                            {{ role }}
                        </v-chip>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <!-- info -->
                        <v-btn color="blue-grey-lighten-4" class="ml-2" elevation="0" density="comfortable" icon rounded="lg"
                            :href="route('users.show', item.id)" v-if="$page.props.auth.can.users_view">
                            <v-icon>material-symbols-light:info-i</v-icon>
                        </v-btn>
                        <v-btn color="blue-grey-lighten-4" class="ml-2" elevation="0" density="comfortable" icon rounded="lg"
                            :href="route('users.edit', item.id)" v-if="$page.props.auth.can.users_edit">
                            <v-icon>mdi-light:pencil</v-icon>
                        </v-btn>
                        <v-btn color="red-lighten-1" class="ml-2" elevation="0" density="comfortable" icon rounded="lg"
                            @click="openDeleteModal(item.id)" v-if="$page.props.auth.can.users_delete">
                            <v-icon>material-symbols-light:delete-forever-outline</v-icon>
                        </v-btn>
                    </template>
                </v-data-table-server>
            </v-card-text>
        </v-card>

        <v-dialog v-model="deleteModalOpen" width="500">
            <v-card title="Delete User" subtitle="Are you sure you want to delete this account?" :loading="deleting">

                <v-card-text class="text-medium-emphasis text-body-1">
                    Once this account is deleted, all of its resources and
                    data will be permanently deleted.
                </v-card-text>

                <v-card-actions class="px-6">
                    <v-btn @click="closeModal" color="grey">Cancel</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn @click="deleteUser" color="red" v-if="$page.props.auth.can.users_delete"
                        :disabled="deleting">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </AuthenticatedLayout>
</template>
