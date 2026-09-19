import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { Globe, Home, Palette, ShieldCheck } from 'lucide-react';
import { useTranslation } from '../lib/i18n';

interface AdminLayoutProps {
    title: string;
    children: React.ReactNode;
}

export default function AdminLayout({ title, children }: AdminLayoutProps) {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const { props } = usePage<{
        flash?: { success?: string; error?: string };
    }>();

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen flex-col font-sans transition-colors duration-200">
            <Head title={`${title} - GGVT`} />

            {/* Top Navigation Bar */}
            <header className="bg-thh-surface border-thh-border sticky top-0 z-40 border-b shadow-xs">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    {/* Brand / Logo */}
                    <div className="flex items-center space-x-4">
                        <Link
                            href="/"
                            className="text-thh-primary flex items-center space-x-3 transition-opacity hover:opacity-90"
                        >
                            <div className="bg-thh-primary flex h-10 w-10 items-center justify-center rounded-xl text-lg font-bold text-white shadow-sm">
                                THH
                            </div>
                            <div>
                                <h1 className="text-thh-text text-base leading-tight font-bold tracking-tight">
                                    {t(
                                        'app.portal.title',
                                        'Tribal Helping Hand',
                                    )}
                                </h1>
                                <p className="text-thh-text-muted text-xs">
                                    {t(
                                        'app.portal.tagline',
                                        'Global Gramin Vikas Trust',
                                    )}
                                </p>
                            </div>
                        </Link>
                    </div>

                    {/* Navigation Tabs */}
                    <nav className="hidden items-center space-x-1 md:flex">
                        <Link
                            href="/admin/theme"
                            className="hover:bg-thh-bg flex items-center space-x-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-colors"
                        >
                            <Palette className="text-thh-accent h-4 w-4" />
                            <span>
                                {t('theme.editor.title', 'Theme Editor')}
                            </span>
                        </Link>

                        <Link
                            href="/admin/localization"
                            className="hover:bg-thh-bg flex items-center space-x-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-colors"
                        >
                            <Globe className="text-thh-secondary h-4 w-4" />
                            <span>
                                {t('localization.editor.title', 'Translations')}
                            </span>
                        </Link>

                        <Link
                            href="/"
                            className="text-thh-text-muted hover:text-thh-text hover:bg-thh-bg flex items-center space-x-2 rounded-lg px-3 py-2 text-sm transition-colors"
                        >
                            <Home className="h-4 w-4" />
                            <span>{t('app.common.back', 'Public Portal')}</span>
                        </Link>
                    </nav>

                    {/* Locale Selector */}
                    <div className="flex items-center space-x-3">
                        <div className="bg-thh-bg border-thh-border flex items-center rounded-lg border p-0.5 text-xs font-medium">
                            {supportedLocales.map((lang) => (
                                <button
                                    key={lang.code}
                                    type="button"
                                    onClick={() => switchLocale(lang.code)}
                                    className={`rounded-md px-2.5 py-1 transition-all ${
                                        locale === lang.code
                                            ? 'bg-thh-surface text-thh-primary font-semibold shadow-xs'
                                            : 'text-thh-text-muted hover:text-thh-text'
                                    }`}
                                >
                                    {lang.native_name || lang.name}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </header>

            {/* Flash Alerts */}
            {props.flash?.success && (
                <div className="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center space-x-2 rounded-xl border border-green-500/30 bg-green-500/10 p-3.5 text-sm font-medium text-green-700 dark:text-green-300">
                        <ShieldCheck className="h-4 w-4 shrink-0" />
                        <span>{props.flash.success}</span>
                    </div>
                </div>
            )}

            {props.flash?.error && (
                <div className="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-xl border border-red-500/30 bg-red-500/10 p-3.5 text-sm font-medium text-red-700 dark:text-red-300">
                        {props.flash.error}
                    </div>
                </div>
            )}

            {/* Main Content Area */}
            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
