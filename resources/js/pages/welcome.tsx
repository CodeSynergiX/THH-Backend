import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    ArrowRight,
    Briefcase,
    ChevronRight,
    Droplet,
    GraduationCap,
    HeartHandshake,
    Landmark,
    Lock,
    PhoneCall,
    Search,
    Shield,
    Users,
} from 'lucide-react';
import { useTranslation } from '../lib/i18n';

interface CategoryItem {
    id: number;
    slug: string;
    icon?: string;
    applications_count?: number;
}

interface WelcomeProps {
    stats?: {
        resolved_cases?: number;
        citizens_helped?: number;
        villages_covered?: number;
    };
    categories?: CategoryItem[];
}

interface TrackResult {
    case_no: string;
    status: string;
    title: string;
    category?: string;
    district?: string;
    created_at?: string;
    resolved_at?: string;
}

export default function Welcome({ stats, categories = [] }: WelcomeProps) {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();

    // Case Tracker State
    const [caseNumber, setCaseNumber] = useState('');
    const [trackLoading, setTrackLoading] = useState(false);
    const [trackResult, setTrackResult] = useState<TrackResult | null>(null);
    const [trackError, setTrackError] = useState<string | null>(null);

    const handleTrackSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const trimmed = caseNumber.trim();
        if (!trimmed) return;

        setTrackLoading(true);
        setTrackError(null);
        setTrackResult(null);

        try {
            const res = await fetch(`/track/${encodeURIComponent(trimmed)}`);
            const data = await res.json();
            if (res.ok && data.success) {
                setTrackResult(data.data);
            } else {
                setTrackError(
                    data.message ||
                        t('app.portal.empty_records', 'Case not found.'),
                );
            }
        } catch {
            setTrackError(
                t('app.portal.empty_records', 'Failed to search case.'),
            );
        } finally {
            setTrackLoading(false);
        }
    };

    // Category icon mapper
    const getCategoryIcon = (slug: string) => {
        switch (slug) {
            case 'education':
                return <GraduationCap className="text-thh-secondary h-6 w-6" />;
            case 'schemes':
                return <Landmark className="text-thh-primary h-6 w-6" />;
            case 'jobs':
                return <Briefcase className="text-thh-accent h-6 w-6" />;
            case 'health':
                return <Activity className="h-6 w-6 text-rose-600" />;
            case 'blood':
                return <Droplet className="h-6 w-6 text-red-600" />;
            case 'mentorship':
                return <Users className="h-6 w-6 text-indigo-600" />;
            default:
                return <HeartHandshake className="text-thh-primary h-6 w-6" />;
        }
    };

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen flex-col font-sans transition-colors duration-200">
            <Head title={t('app.portal.title', 'Tribal Helping Hand')} />

            {/* Platform Top Header */}
            <header className="border-thh-border bg-thh-surface sticky top-0 z-30 border-b shadow-xs">
                <div className="mx-auto flex h-18 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    {/* Brand */}
                    <div className="flex items-center space-x-3.5">
                        <div className="bg-thh-primary flex h-11 w-11 items-center justify-center rounded-2xl text-xl font-black tracking-wider text-white shadow-md">
                            THH
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-thh-text text-lg leading-none font-extrabold tracking-tight">
                                    {t(
                                        'app.portal.title',
                                        'Tribal Helping Hand',
                                    )}
                                </h1>
                                <span className="bg-thh-secondary/15 text-thh-secondary border-thh-secondary/20 rounded-full border px-2 py-0.5 text-[10px] font-bold">
                                    GGVT
                                </span>
                            </div>
                            <p className="text-thh-text-muted mt-1 text-xs leading-none">
                                {t(
                                    'app.portal.tagline',
                                    'Global Gramin Vikas Trust',
                                )}
                            </p>
                        </div>
                    </div>

                    {/* Right Controls: Language Switcher & Admin/Staff Links */}
                    <div className="flex items-center space-x-3">
                        {/* Language Switcher */}
                        <div className="bg-thh-bg border-thh-border flex items-center rounded-xl border p-1 text-xs font-semibold">
                            {supportedLocales.map((lang) => (
                                <button
                                    key={lang.code}
                                    type="button"
                                    onClick={() => switchLocale(lang.code)}
                                    className={`rounded-lg px-3 py-1.5 transition-all ${
                                        locale === lang.code
                                            ? 'bg-thh-surface text-thh-primary font-bold shadow-xs'
                                            : 'text-thh-text-muted hover:text-thh-text'
                                    }`}
                                >
                                    {lang.native_name || lang.name}
                                </button>
                            ))}
                        </div>

                        {/* Admin / Staff Navigation */}
                        <div className="hidden items-center space-x-2 sm:flex">
                            <Link
                                href="/admin/theme"
                                className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg inline-flex items-center gap-1.5 rounded-lg border px-3.5 py-1.5 text-xs font-semibold transition-colors"
                            >
                                <Lock className="text-thh-accent h-3.5 w-3.5" />
                                <span>
                                    {t(
                                        'app.portal.admin_login',
                                        'Admin Console',
                                    )}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            {/* Hero Section */}
            <section className="border-thh-border from-thh-surface to-thh-bg relative overflow-hidden border-b bg-gradient-to-b pt-12 pb-16 lg:pt-16 lg:pb-20">
                <div className="mx-auto max-w-5xl space-y-6 px-4 text-center sm:px-6 lg:px-8">
                    {/* NGO Badge */}
                    <div className="bg-thh-primary/10 text-thh-primary border-thh-primary/25 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-bold shadow-2xs">
                        <Shield className="h-4 w-4" />
                        <span>
                            {t(
                                'app.portal.tagline',
                                'Tribal Empowerment Initiative by Global Gramin Vikas Trust',
                            )}
                        </span>
                    </div>

                    {/* Main Headline */}
                    <h2 className="text-thh-text mx-auto max-w-3xl text-3xl leading-tight font-black tracking-tight sm:text-5xl">
                        {t('app.portal.title', 'Tribal Helping Hand')}
                    </h2>
                    <p className="text-thh-text-muted mx-auto max-w-2xl text-base font-normal sm:text-lg">
                        {t(
                            'app.portal.request_help_sub',
                            'Free, transparent and end-to-end assistance for education, healthcare, government schemes and village welfare.',
                        )}
                    </p>

                    {/* Interactive Case Tracking Bar */}
                    <div className="mx-auto max-w-xl pt-4">
                        <form
                            onSubmit={handleTrackSubmit}
                            className="bg-thh-surface border-thh-border focus-within:border-thh-primary flex flex-col items-center gap-2 rounded-2xl border-2 p-2 shadow-md transition-all sm:flex-row"
                        >
                            <div className="relative w-full flex-1">
                                <Search className="text-thh-text-muted absolute top-3.5 left-3.5 h-5 w-5" />
                                <input
                                    type="text"
                                    value={caseNumber}
                                    onChange={(e) =>
                                        setCaseNumber(e.target.value)
                                    }
                                    placeholder={t(
                                        'app.portal.track_case_placeholder',
                                        'Enter Case ID (e.g. THH-2026-00001)',
                                    )}
                                    className="text-thh-text placeholder:text-thh-text-muted w-full rounded-xl bg-transparent py-3 pr-4 pl-11 text-sm font-medium focus:outline-hidden"
                                />
                            </div>
                            <button
                                type="submit"
                                disabled={trackLoading}
                                className="bg-thh-primary inline-flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-xs transition-all hover:opacity-95 disabled:opacity-50 sm:w-auto"
                            >
                                <span>
                                    {trackLoading
                                        ? t('app.common.loading', 'Loading...')
                                        : t(
                                              'app.portal.track_button',
                                              'Track Now',
                                          )}
                                </span>
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </form>

                        {/* Tracker Search Result Card */}
                        {trackResult && (
                            <div className="bg-thh-surface border-thh-border animate-in fade-in slide-in-from-top-2 mt-4 space-y-3 rounded-2xl border p-5 text-left shadow-md duration-300">
                                <div className="flex items-center justify-between">
                                    <span className="text-thh-text font-mono text-xs font-extrabold">
                                        {trackResult.case_no}
                                    </span>
                                    <span className="bg-thh-secondary/15 text-thh-secondary border-thh-secondary/30 rounded-full border px-3 py-1 text-xs font-bold uppercase">
                                        ●{' '}
                                        {t(
                                            `cases.status.${trackResult.status.toLowerCase()}`,
                                            trackResult.status,
                                        )}
                                    </span>
                                </div>
                                <h4 className="text-thh-text text-base font-bold">
                                    {trackResult.title}
                                </h4>
                                <div className="text-thh-text-muted border-thh-border flex flex-wrap items-center gap-4 border-t pt-1 text-xs">
                                    <span>
                                        District: {trackResult.district || '-'}
                                    </span>
                                    <span>
                                        Category: {trackResult.category || '-'}
                                    </span>
                                </div>
                            </div>
                        )}

                        {/* Tracker Error Card */}
                        {trackError && (
                            <div className="mt-4 flex items-center gap-2 rounded-2xl border border-rose-500/25 bg-rose-500/10 p-4 text-left text-xs font-semibold text-rose-700 dark:text-rose-300">
                                <AlertCircle className="h-4 w-4 shrink-0" />
                                <span>{trackError}</span>
                            </div>
                        )}
                    </div>

                    {/* Live Impact Counters (From Database Props) */}
                    <div className="mx-auto grid max-w-3xl grid-cols-1 gap-4 pt-8 sm:grid-cols-3">
                        <div className="bg-thh-surface border-thh-border space-y-1 rounded-2xl border p-5 text-center shadow-2xs">
                            <span className="text-thh-primary text-3xl font-black">
                                {stats?.citizens_helped !== undefined
                                    ? stats.citizens_helped
                                    : '-'}
                            </span>
                            <p className="text-thh-text-muted text-xs font-bold tracking-wider uppercase">
                                {t(
                                    'app.portal.stat_citizens_helped',
                                    'Citizens Supported',
                                )}
                            </p>
                        </div>

                        <div className="bg-thh-surface border-thh-border space-y-1 rounded-2xl border p-5 text-center shadow-2xs">
                            <span className="text-thh-secondary text-3xl font-black">
                                {stats?.resolved_cases !== undefined
                                    ? stats.resolved_cases
                                    : '-'}
                            </span>
                            <p className="text-thh-text-muted text-xs font-bold tracking-wider uppercase">
                                {t(
                                    'app.portal.stat_cases_resolved',
                                    'Cases Resolved',
                                )}
                            </p>
                        </div>

                        <div className="bg-thh-surface border-thh-border space-y-1 rounded-2xl border p-5 text-center shadow-2xs">
                            <span className="text-thh-accent text-3xl font-black">
                                {stats?.villages_covered !== undefined
                                    ? stats.villages_covered
                                    : '-'}
                            </span>
                            <p className="text-thh-text-muted text-xs font-bold tracking-wider uppercase">
                                {t(
                                    'app.portal.stat_villages_covered',
                                    'Villages Covered',
                                )}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Support Pillars & Categories */}
            <section className="mx-auto w-full max-w-7xl space-y-8 px-4 py-16 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-xl space-y-2 text-center">
                    <h3 className="text-thh-text text-2xl font-black tracking-tight sm:text-3xl">
                        {t(
                            'app.portal.services_heading',
                            'Our Core Support Pillars',
                        )}
                    </h3>
                    <p className="text-thh-text-muted text-xs font-medium sm:text-sm">
                        Comprehensive assistance across education, government
                        schemes, healthcare, and community infrastructure.
                    </p>
                </div>

                {categories.length === 0 ? (
                    <div className="text-thh-text-muted bg-thh-surface border-thh-border rounded-2xl border py-12 text-center text-sm">
                        {t(
                            'app.portal.empty_records',
                            'No categories currently registered in database.',
                        )}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {categories.map((cat) => (
                            <Link
                                key={cat.id}
                                href={`/community/${cat.slug}`}
                                className="bg-thh-surface border-thh-border hover:border-thh-primary group block space-y-4 rounded-2xl border p-6 shadow-2xs transition-all hover:shadow-md"
                            >
                                <div className="bg-thh-bg border-thh-border flex h-12 w-12 items-center justify-center rounded-xl border transition-transform group-hover:scale-105">
                                    {getCategoryIcon(cat.slug)}
                                </div>

                                <div>
                                    <h4 className="text-thh-text text-base font-bold">
                                        {t(
                                            `content.modules.${cat.slug}`,
                                            cat.slug.toUpperCase(),
                                        )}
                                    </h4>
                                    <p className="text-thh-text-muted mt-1 text-xs leading-relaxed">
                                        {cat.applications_count !== undefined
                                            ? `${cat.applications_count} requests processed`
                                            : '-'}
                                    </p>
                                </div>

                                <div className="border-thh-border text-thh-primary flex items-center justify-between border-t pt-2 text-xs font-semibold">
                                    <span>
                                        {t(
                                            'cases.form.submit_button',
                                            'Explore Details & Apply',
                                        )}
                                    </span>
                                    <ChevronRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </section>

            {/* Helpline Banner */}
            <section className="bg-thh-surface border-thh-border border-y py-10">
                <div className="mx-auto flex max-w-5xl flex-col items-center justify-between gap-6 px-4 text-center sm:flex-row sm:px-6 sm:text-left lg:px-8">
                    <div className="space-y-1">
                        <div className="flex items-center justify-center gap-2 text-xs font-bold tracking-wider text-rose-600 uppercase sm:justify-start">
                            <PhoneCall className="h-4 w-4" />
                            <span>
                                {t(
                                    'app.portal.emergency_contact',
                                    'Emergency Helpline',
                                )}
                            </span>
                        </div>
                        <h4 className="text-thh-text text-xl font-black">
                            Need Immediate Assistance in Your Village?
                        </h4>
                        <p className="text-thh-text-muted text-xs">
                            Dedicated tribal citizen coordinators available
                            24/7.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/theme"
                            className="text-thh-text border-thh-border bg-thh-bg hover:bg-thh-surface inline-flex items-center gap-2 rounded-xl border px-5 py-3 text-xs font-bold transition-colors"
                        >
                            <span>Admin Theme Console</span>
                        </Link>
                        <Link
                            href="/admin/localization"
                            className="bg-thh-secondary inline-flex items-center gap-2 rounded-xl px-5 py-3 text-xs font-bold text-white shadow-xs transition-colors hover:opacity-95"
                        >
                            <span>Language Manager</span>
                        </Link>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-thh-border bg-thh-surface text-thh-text-muted mt-auto border-t py-8 text-xs">
                <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
                    <p>
                        © {new Date().getFullYear()} Global Gramin Vikas Trust
                        (GGVT). All Rights Reserved.
                    </p>
                    <div className="flex items-center space-x-6">
                        <Link
                            href="/admin/theme"
                            className="hover:text-thh-text transition-colors"
                        >
                            {t('theme.editor.title', 'Theme')}
                        </Link>
                        <Link
                            href="/admin/localization"
                            className="hover:text-thh-text transition-colors"
                        >
                            {t('localization.editor.title', 'Translations')}
                        </Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}
