<template>
    <v-app>
        <v-container fluid class="bg-yellow fill-height">
            <v-row justify="center" align="center" class="ma-0 pa-4">
                <v-col cols="12" md="10">
                    <v-card elevation="0" rounded="lg" class="pa-4">
                        <v-card-title class="text-h5 font-weight-bold">
                            Welcome to {{ appName }}
                        </v-card-title>

                        <v-card-subtitle class="text-body-2">
                            This is a starter kit to help you quickly build modern web applications
                        </v-card-subtitle>

                        <v-card-text>
                            <div class="mt-4">
                                Built with:
                                <v-chip v-for="tech in technologies" :key="tech.name" :color="tech.color"
                                    text-color="white" class="ma-1" variant="flat" label>
                                    {{ tech.label }}
                                </v-chip>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
export default {
    name: 'Welcome',
    props: {
        laravelVersion: String,
        phpVersion: String,
        appName: String,
        appEnvironment: String,
        appDebug: Boolean,
    },
    data() {
        return {
            map: null,
            technologies: [
                { name: 'laravel', label: `Laravel ${this.laravelVersion}`, color: '#fb503b' },
                { name: 'php', label: `PHP ${this.phpVersion}`, color: '#8993be' },
                { name: 'vue', label: 'Vue.js: 3.5.14', color: '#42b883' },
                { name: 'inertia', label: 'Inertia.js: 2.0.11', color: 'indigo' },
                { name: 'vuetify', label: 'Vuetify: 3.8.0', color: 'primary' },
            ],
        }
    },
}
</script>

<style>
html,
body {
    overflow: hidden !important;
}
</style>
