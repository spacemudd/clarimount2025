import { createI18n } from 'vue-i18n';

// Initialize with empty messages - will be populated from Laravel
export default createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: {
    en: {},
    ar: {},
  },
});

// Function to update i18n messages from Laravel translations
export function updateI18nMessages(locale: string, messages: any) {
  const i18n = createI18n({
    legacy: false,
    locale,
    fallbackLocale: 'en',
    messages: {
      [locale]: messages,
      // Keep fallback for opposite locale
      [locale === 'en' ? 'ar' : 'en']: {},
    },
  });
  
  return i18n;
} 