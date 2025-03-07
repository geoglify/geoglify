// resources/js/i18n.js
import { createI18n } from 'vue-i18n';

// Carrega as traduções do objeto global `window.translations`
const messages = {
    [window.__locale]: window.translations, // Usa o idioma atual do Laravel
};

const i18n = createI18n({
    locale: window.__locale, // Idioma atual do Laravel
    fallbackLocale: 'en', // Idioma de fallback
    messages,
});

export default i18n;
