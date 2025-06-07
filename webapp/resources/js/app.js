import "./bootstrap";
import "../css/app.css";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

// Vuetify
import "vuetify/styles";
import { createVuetify } from "vuetify";

// Laravel Echo
import "./echo";

// i18n
import i18n from "./i18n";
import { loadLocale } from "./i18n-loader";

const initialLocale = window.__locale || "pt";

// Country Flag
import CountryFlag from "vue-country-flag-next";

const vuetify = createVuetify({
    theme: {
        defaultTheme: "light",
        themes: {
            light: {
                colors: {
                    primary: "#4B5563",
                    secondary: "#9CA3AF",
                    accent: "#F59E0B",
                },
            },
        },
    },
});

// Iconify
import { Icon } from "@iconify/vue";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        loadLocale(initialLocale).then(() => {
            return createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .use(i18n)
                .component("Icon", Icon)
                .component("CountryFlag", CountryFlag)
                .use(vuetify)
                .mount(el);
        });
    },
    progress: {
        color: "#4B5563",
    },
});
