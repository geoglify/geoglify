import { createI18n } from "vue-i18n";

// Import all translations
const messages = {
    [window.__locale]: window.translations, // Translations for the current locale
};

const i18n = createI18n({
    locale: window.__locale,
    fallbackLocale: "en",
    messages,
});

export default i18n;
