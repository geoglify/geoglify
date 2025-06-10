// Bootstrap and styles
import "./bootstrap";
import "../css/app.css";

// Vue and Inertia
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";

// Ziggy (for route helpers)
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

// Vuetify
import "vuetify/styles";
import { createVuetify } from "vuetify";

// Laravel Echo (real-time events)
import "./echo";

// Internationalization (i18n)
import i18n from "./i18n";
import { loadLocale } from "./i18n-loader";
const initialLocale = window.__locale || "en";

// Country Flag component
import CountryFlag from "vue-country-flag-next";

// Iconify (icon component)
import { Icon } from "@iconify/vue";

// Date formatting filters (UTC to local formats)
import { formatDate, formatDateTime, formatDateTimeSeconds } from "./filters";

// Application name fallback
const appName = import.meta.env.VITE_APP_NAME || "Laravel";

// Fetch interceptor for API requests
import fetchInterceptor from "./fetchInterceptor";

// Vuetify configuration
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
    icons: {
        defaultSet: "iconify",
        sets: {
            iconify: {
                component: (props) => h(Icon, { icon: props.icon }),
            },
        },
    },
});

// Create the Inertia app
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        // Load the initial locale before mounting
        loadLocale(initialLocale).then(() => {
            const app = createApp({ render: () => h(App, props) });

            // Register plugins and components
            app.use(plugin);
            app.use(ZiggyVue);
            app.use(i18n);
            app.use(vuetify);
            app.use(fetchInterceptor);

            app.component("Icon", Icon);
            app.component("CountryFlag", CountryFlag);

            // Register global date formatting functions
            app.config.globalProperties.$formatDate = formatDate;
            app.config.globalProperties.$formatDateTime = formatDateTime;
            app.config.globalProperties.$formatDateTimeSeconds = formatDateTimeSeconds;

            // Mount the application
            app.mount(el);
        });
    },
    progress: {
        color: "#4B5563",
    },
});
