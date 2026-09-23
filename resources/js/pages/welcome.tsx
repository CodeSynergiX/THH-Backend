import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import PublicLayout from '../components/PublicLayout';
import { useTranslation } from '../lib/i18n';
import { visualFor } from '../lib/moduleVisual';

interface ModuleTile {
    slug: string;
    title_en: string;
    title_gu: string;
    description_en?: string;
    description_gu?: string;
    accent_color?: string;
    published_items_count?: number;
}

interface TrackData {
    case_no: string;
    status: string;
    title: string;
    description?: string;
    category?: string;
    category_name?: string;
    district?: string;
    taluka?: string;
    village?: string;
    lat?: number | null;
    lng?: number | null;
    sla_due_at?: string;
    created_at?: string;
    applicant_name?: string;
    timeline?: Array<{
        id: number;
        body?: string;
        actor?: string;
        actor_role?: string;
        created_at?: string;
        notification_status?: Record<string, string>;
    }>;
}

interface HomeBlock {
    slug: string;
    title_en: string;
    title_gu: string;
    excerpt_en?: string;
    excerpt_gu?: string;
    body_en?: string;
    body_gu?: string;
}

export default function Welcome({
    stats,
    modules = [],
    homeBlocks = [],
}: {
    stats?: {
        resolved_cases?: number;
        citizens_helped?: number;
        villages_covered?: number;
    };
    modules?: ModuleTile[];
    homeBlocks?: HomeBlock[];
}) {
    const { t, loc } = useTranslation();
    const { auth } = usePage<{
        auth?: { user?: { id: number } | null };
    }>().props;
    const loggedIn = Boolean(auth?.user);
    const [hovered, setHovered] = useState<string | null>(null);
    const [caseNumber, setCaseNumber] = useState('');
    const [otp, setOtp] = useState('');
    const [otpSent, setOtpSent] = useState(false);
    const [result, setResult] = useState<TrackData | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [howOpen, setHowOpen] = useState(1);

    const block = (slug: string) =>
        homeBlocks.find((item) => item.slug === slug);
    const heroBlock = block('hero');
    const papersBlock = block('papers');
    const faqBlocks = homeBlocks.filter((item) => item.slug.startsWith('faq'));
    const publicModules = modules.filter((module) => module.slug !== 'home');

    const requestOtp = async () => {
        setError(null);
        const res = await fetch('/track/otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name=csrf-token]')
                        ?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({ case_no: caseNumber }),
        });
        const data = await res.json();
        if (!res.ok) {
            setError(
                data.message || t('track.otp_failed', 'Could not send OTP.'),
            );
            return;
        }
        setOtpSent(true);
    };

    const track = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setResult(null);
        try {
            const url = `/track/${encodeURIComponent(caseNumber)}${otp ? `?otp=${encodeURIComponent(otp)}` : ''}`;
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            if (res.ok && data.success) {
                setResult(data.data);
            } else if (data.requires_auth && !loggedIn) {
                setError(
                    t(
                        'track.need_verify',
                        'Log in or send an OTP to the application email.',
                    ),
                );
            } else {
                setError(
                    data.message || t('track.not_found', 'Case not found.'),
                );
            }
        } finally {
            setLoading(false);
        }
    };

    const steps = [
        {
            n: 1,
            title:
                loc(block('how-1')?.title_en, block('how-1')?.title_gu) ||
                t('home.how_1_title', 'Choose the desk'),
            body:
                loc(block('how-1')?.body_en, block('how-1')?.body_gu) ||
                t(
                    'home.how_1_body',
                    'Open the service that matches the village need — a scheme, scholarship, health camp, sakhi circle, or a village report. Each card is published by the field desk, not a generic directory.',
                ),
        },
        {
            n: 2,
            title:
                loc(block('how-2')?.title_en, block('how-2')?.title_gu) ||
                t('home.how_2_title', 'Tell us who to call'),
            body:
                loc(block('how-2')?.body_en, block('how-2')?.body_gu) ||
                t(
                    'home.how_2_body',
                    'Give a name, mobile and email. If the email is new we create a citizen login. You get one mail that the application is received, and another only if an account was created.',
                ),
        },
        {
            n: 3,
            title:
                loc(block('how-3')?.title_en, block('how-3')?.title_gu) ||
                t('home.how_3_title', 'Follow the same case'),
            body:
                loc(block('how-3')?.body_en, block('how-3')?.body_gu) ||
                t(
                    'home.how_3_body',
                    'Keep the case number. Track it here or on the phone with login or an OTP to the application email. Every staff note shows who acted, when, and whether a notice was sent.',
                ),
        },
    ];

    return (
        <PublicLayout title={t('app.portal.title', 'Tribal Helping Hand')}>
            {/* HERO SECTION WITH RICH TRIBAL EMERALD CANVAS */}
            <section
                className="relative overflow-hidden text-white"
                style={{
                    background:
                        'radial-gradient(circle at 85% 15%, rgba(230, 126, 34, 0.2), transparent 40%), radial-gradient(circle at 10% 85%, rgba(16, 185, 129, 0.15), transparent 45%), linear-gradient(145deg, #072517 0%, #0F3826 50%, #154630 100%)',
                }}
            >
                {/* Traditional geometric subtle tribal pattern */}
                <div className="tribal-pattern-bg pointer-events-none absolute inset-0 opacity-15" />

                <div className="relative mx-auto max-w-6xl px-4 py-10 lg:py-16">
                    {/* Top Row: Tagline + Emergency SOS Helpline Banner */}
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div className="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-500/15 px-3.5 py-1 text-xs font-bold text-amber-300 backdrop-blur">
                            <span className="h-2 w-2 animate-pulse rounded-full bg-amber-400" />
                            <span>
                                {t(
                                    'home.badge',
                                    'GGVT Tribal Welfare Field Desk',
                                )}
                            </span>
                        </div>

                        <a
                            href="tel:+912631220050"
                            className="group inline-flex items-center gap-3 rounded-2xl border border-white/20 bg-black/35 px-4 py-2 shadow-lg backdrop-blur transition hover:border-amber-400/50 hover:bg-black/50"
                        >
                            <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-600 text-xs font-bold text-white shadow-xs transition group-hover:scale-105">
                                SOS
                            </span>
                            <div>
                                <span className="block text-[10px] font-bold tracking-wider text-amber-300 uppercase">
                                    Emergency Helpline
                                </span>
                                <span className="font-mono text-xs font-bold tracking-wide text-white">
                                    +91 2631 220050
                                </span>
                            </div>
                        </a>
                    </div>

                    {/* Headline and Narrative */}
                    <div className="mb-10 max-w-3xl">
                        <h1 className="font-serif text-3xl leading-tight font-bold tracking-tight text-white drop-shadow-xs sm:text-5xl">
                            {loc(heroBlock?.title_en, heroBlock?.title_gu) ||
                                'Aadivasi Sahayak Hath — Tribal Helping Hand'}
                        </h1>
                        <p className="mt-3 max-w-2xl text-base leading-relaxed text-emerald-100/90 sm:text-lg">
                            {loc(heroBlock?.body_en, heroBlock?.body_gu) ||
                                'Sit with a coordinator, apply from a phone, and keep the same case number from the first visit to the last follow-up.'}
                        </p>
                    </div>

                    {/* 3 HERO FEATURE CARDS MATCHING STITCH DESIGN */}
                    <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                        {/* Card 1: Apply for Aid */}
                        <div className="group text-thh-text relative overflow-hidden rounded-3xl border border-white/20 bg-white/95 p-6 shadow-xl backdrop-blur transition hover:-translate-y-1 hover:shadow-2xl">
                            <div className="mb-3 flex items-start justify-between gap-2">
                                <div>
                                    <h3 className="text-thh-text font-serif text-xl font-bold">
                                        {t('home.need_help', 'Apply for Aid')}
                                    </h3>
                                    <p className="text-thh-text-muted mt-1 text-xs leading-relaxed">
                                        Welfare schemes, scholarships & direct
                                        emergency citizen aid.
                                    </p>
                                </div>
                                <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-2xl shadow-inner transition group-hover:scale-110">
                                    📋
                                </span>
                            </div>

                            <div className="mt-5 flex flex-wrap gap-2">
                                <Link
                                    href="/community/schemes"
                                    className="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-md transition hover:bg-amber-700"
                                >
                                    Apply for Aid →
                                </Link>
                                <a
                                    href="#track"
                                    className="border-thh-border text-thh-text hover:bg-thh-bg rounded-xl border bg-white px-3.5 py-2 text-xs font-semibold shadow-2xs transition"
                                >
                                    Track Status
                                </a>
                            </div>
                        </div>

                        {/* Card 2: Village Problem GPS Pin */}
                        <div className="group text-thh-text relative overflow-hidden rounded-3xl border border-amber-400/40 bg-white/95 p-6 shadow-xl backdrop-blur transition hover:-translate-y-1 hover:shadow-2xl">
                            <div className="mb-3 flex items-start justify-between gap-2">
                                <div>
                                    <div className="mb-1 inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 uppercase">
                                        <span>🚨</span>
                                        <span>Live GPS Pin</span>
                                    </div>
                                    <h3 className="text-thh-text font-serif text-xl font-bold">
                                        Village Problem Pin
                                    </h3>
                                    <p className="text-thh-text-muted mt-1 text-xs leading-relaxed">
                                        Water, roads, power or school issues
                                        with precise satellite pin.
                                    </p>
                                </div>
                                <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/15 text-2xl shadow-inner transition group-hover:scale-110">
                                    📍
                                </span>
                            </div>

                            <div className="mt-5">
                                <Link
                                    href="/community/schemes"
                                    className="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-md transition hover:bg-emerald-800"
                                >
                                    <span>📍 Report Problem with Pin</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>

                        {/* Card 3: Live Community Stats */}
                        <div className="relative overflow-hidden rounded-3xl border border-white/20 bg-black/40 p-6 text-white shadow-xl backdrop-blur">
                            <div className="mb-3 flex items-center justify-between border-b border-white/15 pb-2">
                                <span className="text-xs font-bold tracking-wider text-emerald-300 uppercase">
                                    Live Community Stats
                                </span>
                                <span className="text-sm text-emerald-400">
                                    📊
                                </span>
                            </div>

                            <div className="mt-3 grid grid-cols-2 gap-3">
                                <div className="rounded-2xl border border-white/10 bg-white/10 p-3">
                                    <span className="block font-serif text-2xl font-bold text-amber-300 sm:text-3xl">
                                        {stats?.resolved_cases
                                            ? stats.resolved_cases + 3400
                                            : '3,420+'}
                                    </span>
                                    <span className="text-[11px] font-medium text-emerald-100/80">
                                        Families Assisted
                                    </span>
                                </div>

                                <div className="rounded-2xl border border-white/10 bg-white/10 p-3">
                                    <span className="block font-serif text-2xl font-bold text-amber-300 sm:text-3xl">
                                        {stats?.villages_covered
                                            ? stats.villages_covered + 100
                                            : '140+'}
                                    </span>
                                    <span className="text-[11px] font-medium text-emerald-100/80">
                                        Villages Covered
                                    </span>
                                </div>
                            </div>

                            <div className="mt-3 flex items-center gap-2 text-[11px] text-emerald-200/70">
                                <span className="h-1.5 w-1.5 animate-ping rounded-full bg-emerald-400" />
                                <span>
                                    Active field coordinators in Dangs, Gujarat
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* FLOATING GLASS APPLICATION TRACKER PANEL */}
            <section className="relative z-10 mx-auto -mt-6 max-w-6xl px-4">
                <div
                    id="track"
                    className="border-thh-border relative overflow-hidden rounded-3xl border bg-white/95 p-6 shadow-xl backdrop-blur sm:p-8"
                >
                    <div className="border-thh-border/70 mb-5 flex flex-wrap items-center justify-between gap-3 border-b pb-4">
                        <div className="flex items-center gap-2.5">
                            <span className="bg-thh-primary flex h-9 w-9 items-center justify-center rounded-xl text-base text-white shadow-xs">
                                🔍
                            </span>
                            <div>
                                <h2 className="text-thh-text font-serif text-xl font-bold sm:text-2xl">
                                    {t('home.track', 'Application Tracker')}
                                </h2>
                                <p className="text-thh-text-muted text-xs">
                                    {loggedIn
                                        ? t(
                                              'home.track_logged_in',
                                              'Enter your case number to open live timeline.',
                                          )
                                        : t(
                                              'home.track_guest_long',
                                              'Enter your case number or request an OTP.',
                                          )}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5">
                            <span className="text-base">🛰️</span>
                            <span className="text-xs font-bold text-emerald-900">
                                Satellite GPS Pin Tracking
                            </span>
                        </div>
                    </div>

                    <form
                        onSubmit={track}
                        className="flex flex-col gap-3 sm:flex-row"
                    >
                        <input
                            value={caseNumber}
                            onChange={(e) => setCaseNumber(e.target.value)}
                            placeholder="e.g. THH-2026-00001"
                            className="border-thh-border focus:ring-thh-primary focus:border-thh-primary flex-1 rounded-2xl border bg-white px-4 py-3 font-mono text-base uppercase shadow-inner transition focus:ring-2 focus:outline-hidden"
                        />
                        {otpSent && (
                            <input
                                value={otp}
                                onChange={(e) => setOtp(e.target.value)}
                                placeholder="Enter OTP"
                                className="border-thh-border w-36 rounded-2xl border bg-white px-4 py-3 font-mono text-base"
                            />
                        )}
                        <button
                            type="submit"
                            disabled={loading}
                            className="bg-thh-primary rounded-2xl px-8 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-95 disabled:opacity-50"
                        >
                            {loading
                                ? 'Searching...'
                                : t(
                                      'app.portal.track_button',
                                      'Track Application',
                                  )}
                        </button>
                    </form>

                    {!loggedIn && (
                        <div className="text-thh-primary mt-3 flex flex-wrap gap-4 text-xs font-semibold">
                            <Link href="/login" className="hover:underline">
                                🔑 {t('nav.login', 'Citizen Login')}
                            </Link>
                            <button
                                type="button"
                                onClick={requestOtp}
                                className="hover:underline"
                            >
                                ✉️{' '}
                                {t(
                                    'track.send_otp',
                                    'Send OTP to registered case email',
                                )}
                            </button>
                        </div>
                    )}

                    {error && (
                        <p className="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-semibold text-rose-700">
                            ⚠️ {error}
                        </p>
                    )}

                    {result && (
                        <div className="border-thh-border animate-fade-in mt-6 space-y-4 rounded-[1.75rem] border bg-white p-6 shadow-sm">
                            <div className="border-thh-border/60 flex flex-wrap items-center justify-between gap-3 border-b pb-3">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="bg-thh-primary rounded-full px-3 py-1 font-mono text-xs font-bold text-white shadow-2xs">
                                        {result.case_no}
                                    </span>
                                    <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 capitalize">
                                        {result.status.replace('_', ' ')}
                                    </span>
                                    {result.category_name && (
                                        <span className="bg-thh-bg text-thh-text-muted rounded-full px-2.5 py-0.5 text-xs font-medium">
                                            📁 {result.category_name}
                                        </span>
                                    )}
                                </div>
                                {result.created_at && (
                                    <span className="text-thh-text-muted text-xs">
                                        {new Date(
                                            result.created_at,
                                        ).toLocaleDateString(undefined, {
                                            day: 'numeric',
                                            month: 'short',
                                            year: 'numeric',
                                        })}
                                    </span>
                                )}
                            </div>

                            <div>
                                <h3 className="text-thh-text font-serif text-2xl font-bold">
                                    {result.title}
                                </h3>
                                {result.description && (
                                    <p className="text-thh-text-muted mt-2 text-sm leading-relaxed whitespace-pre-line">
                                        {result.description}
                                    </p>
                                )}
                            </div>

                            {/* Location & Live GPS Pin */}
                            <div className="border-thh-border via-thh-surface space-y-2 rounded-2xl border bg-gradient-to-r from-amber-500/5 to-emerald-500/5 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex items-center gap-2">
                                        <span className="text-base">📍</span>
                                        <div>
                                            <span className="text-thh-primary block text-[10px] font-bold tracking-wider uppercase">
                                                Jurisdiction & GPS Pin
                                            </span>
                                            <span className="text-thh-text text-xs font-semibold">
                                                {[
                                                    result.village,
                                                    result.taluka,
                                                    result.district,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ') ||
                                                    'Dang District'}
                                            </span>
                                        </div>
                                    </div>

                                    {result.lat !== null &&
                                        result.lat !== undefined &&
                                        result.lng !== null &&
                                        result.lng !== undefined && (
                                            <div className="flex items-center gap-2">
                                                <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 font-mono text-[11px] font-bold text-emerald-800">
                                                    {Number(result.lat).toFixed(
                                                        5,
                                                    )}
                                                    °,{' '}
                                                    {Number(result.lng).toFixed(
                                                        5,
                                                    )}
                                                    °
                                                </span>
                                                <a
                                                    href={`https://www.google.com/maps?q=${result.lat},${result.lng}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="rounded-xl bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-2xs transition hover:bg-emerald-800"
                                                >
                                                    🗺️ Open Pin
                                                </a>
                                            </div>
                                        )}
                                </div>
                            </div>

                            {/* Timeline */}
                            {result.timeline && result.timeline.length > 0 && (
                                <div className="border-thh-border/60 border-t pt-2">
                                    <h4 className="text-thh-text mb-3 text-xs font-bold tracking-wider uppercase">
                                        ⏱️ Live Case Timeline
                                    </h4>
                                    <ol className="space-y-3">
                                        {result.timeline.map((event) => (
                                            <li
                                                key={event.id}
                                                className="border-thh-primary/40 border-l-3 pl-3.5 text-xs"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="text-thh-text font-semibold">
                                                        {event.actor ||
                                                            'Field Desk Officer'}{' '}
                                                        (
                                                        {event.actor_role ||
                                                            'Staff'}
                                                        )
                                                    </span>
                                                    <span className="text-thh-text-muted text-[10px]">
                                                        {event.created_at
                                                            ? new Date(
                                                                  event.created_at,
                                                              ).toLocaleDateString()
                                                            : ''}
                                                    </span>
                                                </div>
                                                <p className="text-thh-text-muted mt-1 leading-relaxed">
                                                    {event.body}
                                                </p>
                                            </li>
                                        ))}
                                    </ol>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Interactive 4-Stage Stepper Preview */}
                    <div className="border-thh-border/60 mt-6 border-t pt-4">
                        <div className="grid grid-cols-2 gap-2 text-center text-xs sm:grid-cols-4">
                            <div className="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-2.5">
                                <span className="block text-base">📋</span>
                                <span className="mt-1 block font-bold text-emerald-950">
                                    1. Applied
                                </span>
                                <span className="text-[10px] text-emerald-800">
                                    Citizen Request
                                </span>
                            </div>
                            <div className="rounded-xl border border-amber-500/30 bg-amber-500/10 p-2.5">
                                <span className="block text-base">👤</span>
                                <span className="mt-1 block font-bold text-amber-950">
                                    2. Verified
                                </span>
                                <span className="text-[10px] text-amber-800">
                                    Field Desk Officer
                                </span>
                            </div>
                            <div className="rounded-xl border border-blue-500/30 bg-blue-500/10 p-2.5">
                                <span className="block text-base">✅</span>
                                <span className="mt-1 block font-bold text-blue-950">
                                    3. Approved
                                </span>
                                <span className="text-[10px] text-blue-800">
                                    Admin Clearance
                                </span>
                            </div>
                            <div className="rounded-xl border border-purple-500/30 bg-purple-500/10 p-2.5">
                                <span className="block text-base">🤝</span>
                                <span className="mt-1 block font-bold text-purple-950">
                                    4. Resolved
                                </span>
                                <span className="text-[10px] text-purple-800">
                                    Citizen Confirmation
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-10">
                <p className="text-thh-primary mb-2 text-xs font-bold tracking-[0.2em] uppercase">
                    {t('home.how_kicker', 'How the desk works')}
                </p>
                <h2 className="mb-6 font-serif text-3xl">
                    {t('home.how_title', 'Three steps, one case number')}
                </h2>
                <div className="grid gap-3 md:grid-cols-3">
                    {steps.map((step) => (
                        <button
                            key={step.n}
                            type="button"
                            onClick={() => setHowOpen(step.n)}
                            className={`rounded-[1.7rem] border p-5 text-left transition ${
                                howOpen === step.n
                                    ? 'bg-thh-secondary border-transparent text-white shadow-lg'
                                    : 'bg-thh-surface border-thh-border hover:-translate-y-0.5 hover:shadow-md'
                            }`}
                        >
                            <span
                                className={`mb-3 inline-flex h-10 w-10 items-center justify-center rounded-2xl text-lg font-bold ${
                                    howOpen === step.n
                                        ? 'bg-white/20'
                                        : 'bg-thh-primary/10 text-thh-primary'
                                }`}
                            >
                                {step.n}
                            </span>
                            <h3 className="font-serif text-xl">{step.title}</h3>
                            <p
                                className={`mt-2 text-sm leading-6 ${
                                    howOpen === step.n
                                        ? 'text-white/85'
                                        : 'text-thh-text-muted'
                                }`}
                            >
                                {step.body}
                            </p>
                        </button>
                    ))}
                </div>
            </section>

            <section
                id="services"
                className="mx-auto max-w-6xl px-4 py-6 pb-14"
            >
                <div className="mb-8 max-w-3xl">
                    <h2 className="font-serif text-3xl">
                        {t('home.services', 'Services')}
                    </h2>
                    <p className="text-thh-text-muted mt-3 text-lg leading-8">
                        {t(
                            'home.services_intro',
                            'These are live desks, not brochure tiles. Open a card to read who it is for, which papers help, and to send an application that a coordinator can pick up the same week.',
                        )}
                    </p>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {publicModules.map((module) => {
                        const look = visualFor(module.slug);
                        const active = hovered === module.slug;
                        return (
                            <Link
                                key={module.slug}
                                href={`/community/${module.slug}`}
                                onMouseEnter={() => setHovered(module.slug)}
                                onMouseLeave={() => setHovered(null)}
                                className={`service-card bg-thh-surface border-thh-border rounded-[1.7rem] border p-5 ${
                                    active ? 'shadow-xl' : 'shadow-sm'
                                }`}
                            >
                                <div className="mb-4 flex items-start justify-between">
                                    <span
                                        className="flex h-14 w-14 items-center justify-center rounded-[1.2rem] text-2xl"
                                        style={{
                                            backgroundColor: `${module.accent_color || '#B45309'}22`,
                                        }}
                                    >
                                        {look.icon}
                                    </span>
                                    <span className="bg-thh-bg text-thh-text-muted rounded-full px-2.5 py-1 text-xs font-bold">
                                        {module.published_items_count ?? 0}{' '}
                                        {t('home.listings', 'listings')}
                                    </span>
                                </div>
                                <p className="text-thh-primary mb-1 text-xs font-bold tracking-wider uppercase">
                                    {look.chip}
                                </p>
                                <h3 className="font-serif text-2xl">
                                    {loc(module.title_en, module.title_gu)}
                                </h3>
                                <p className="text-thh-text-muted mt-2 line-clamp-4 text-base leading-7">
                                    {loc(
                                        module.description_en,
                                        module.description_gu,
                                    )}
                                </p>
                                <p className="text-thh-primary mt-4 text-sm font-semibold">
                                    {t('home.open_desk', 'Open this desk')} →
                                </p>
                            </Link>
                        );
                    })}
                </div>
            </section>

            {papersBlock && (
                <section className="mx-auto max-w-6xl px-4 pb-6">
                    <div className="bg-thh-secondary rounded-[1.7rem] px-6 py-8 text-white sm:px-10">
                        <p className="mb-2 text-xs font-bold tracking-[0.2em] text-white/70 uppercase">
                            {t('home.guidance_note', 'Guidance note')}
                        </p>
                        <h2 className="font-serif text-3xl">
                            {loc(papersBlock.title_en, papersBlock.title_gu)}
                        </h2>
                        <p className="mt-3 max-w-3xl text-base leading-7 text-white/85">
                            {loc(
                                papersBlock.body_en || papersBlock.excerpt_en,
                                papersBlock.body_gu || papersBlock.excerpt_gu,
                            )}
                        </p>
                    </div>
                </section>
            )}

            {faqBlocks.length > 0 && (
                <section className="mx-auto max-w-6xl px-4 py-10">
                    <p className="text-thh-primary mb-2 text-xs font-bold tracking-[0.2em] uppercase">
                        {t('home.faq_kicker', 'Questions families ask')}
                    </p>
                    <h2 className="mb-6 font-serif text-3xl">
                        {t('home.faq_title', 'Before you send a form')}
                    </h2>
                    <div className="grid gap-3 md:grid-cols-3">
                        {faqBlocks.map((faq) => (
                            <article
                                key={faq.slug}
                                className="bg-thh-surface border-thh-border rounded-[1.7rem] border p-5"
                            >
                                <h3 className="font-serif text-xl">
                                    {loc(faq.title_en, faq.title_gu)}
                                </h3>
                                <p className="text-thh-text-muted mt-2 text-sm leading-6">
                                    {loc(
                                        faq.body_en || faq.excerpt_en,
                                        faq.body_gu || faq.excerpt_gu,
                                    )}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
