import { usePage } from '@inertiajs/react';

interface SharedProps {
    locale?: string;
    translations?: Record<string, string>;
    supported_locales?: Array<{
        code: string;
        name: string;
        native_name: string;
        is_default: boolean;
    }>;
    theme?: {
        version: number;
        light: Record<string, string>;
        dark: Record<string, string>;
        meta: Record<string, unknown>;
    };
    auth?: {
        user: {
            id: number;
            name: string;
            phone?: string;
            email?: string;
            locale: string;
            roles: string[];
            scope: string;
            district_id?: number;
            taluka_id?: number;
            village_id?: number;
        } | null;
    };
    flash?: {
        success?: string;
        error?: string;
    };
}

/**
 * Custom hook to access database-driven translations, current locale, and switch languages
 */
export function useTranslation() {
    const { props } = usePage<{ [key: string]: unknown } & SharedProps>();
    const translations = (props.translations as Record<string, string>) || {};
    const locale = (props.locale as string) || 'gu';
    const supportedLocales = props.supported_locales || [];

    /**
     * Translates a given key with optional fallback.
     * Pattern: {group}.{subgroup}.{element}_{modifier}
     */
    const t = (key: string, fallback?: string): string => {
        if (translations[key] !== undefined && translations[key] !== '') {
            return translations[key];
        }
        return fallback !== undefined ? fallback : key;
    };

    /**
     * Set active locale by reloading with locale parameter
     */
    const switchLocale = (newLocale: string) => {
        window.location.href = `/locale/${newLocale}?redirect=${encodeURIComponent(window.location.pathname)}`;
    };

    return {
        t,
        locale,
        supportedLocales,
        switchLocale,
    };
}
