import i18n from "./i18n";

export const loadLocale = async (locale) => {
    if (!i18n.global.availableLocales.includes(locale)) {
        try {
            const messages = await fetch(`/lang/${locale}.json`).then((res) =>
                res.json()
            );
            i18n.global.setLocaleMessage(locale, messages);
            console.log(`Locale ${locale} loaded successfully`);
        } catch (e) {
            console.warn(`Error loading locale ${locale}:`, e);
        }
    }

    i18n.global.locale.value = locale;
};
