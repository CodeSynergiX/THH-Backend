import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';
import { useTranslation } from '../../lib/i18n';

interface EventRow {
    id: number;
    event_type?: string;
    body?: string;
    from_status?: string;
    to_status?: string;
    created_at: string;
    actor?: { name: string };
    actor_role?: string;
}

interface ApplicationData {
    id: number;
    case_no: string;
    title: string;
    description: string;
    status: string;
    urgency: string;
    priority?: string;
    lat?: number | null;
    lng?: number | null;
    beneficiary_name?: string;
    beneficiary_phone?: string;
    sla_due_at?: string | null;
    created_at?: string;
    resolved_at?: string | null;
    user?: {
        id: number;
        name: string;
        email?: string;
        phone?: string;
    };
    category?: {
        id: number;
        slug: string;
        name_en: string;
        name_gu?: string;
    };
    sub_category?: {
        id: number;
        slug: string;
        name_en: string;
        name_gu?: string;
    };
    village?: {
        id: number;
        name_en: string;
        name_gu?: string;
        lat?: number | null;
        lng?: number | null;
        taluka?: {
            id: number;
            name_en: string;
            name_gu?: string;
            district?: {
                id: number;
                name_en: string;
                name_gu?: string;
            };
        };
    };
    documents?: Array<{
        id: number;
        type: string;
        path: string;
        created_at: string;
    }>;
    public_timeline_events?: EventRow[];
    timeline_events?: EventRow[];
    follow_ups?: Array<{
        id: number;
        scheduled_for?: string;
        scheduled_at?: string;
        notes?: string;
        status: string;
    }>;
}

export default function AccountShow({
    application,
}: {
    application: ApplicationData;
}) {
    const { t, loc } = useTranslation();
    const [reason, setReason] = useState('');
    const [isReopening, setIsReopening] = useState(false);

    const events =
        application.timeline_events ?? application.public_timeline_events ?? [];
    const awaiting = application.status === 'awaiting_confirmation';

    const getStatusTheme = (status: string) => {
        switch (status) {
            case 'received':
                return {
                    bg: 'bg-blue-50 text-blue-700 border-blue-200',
                    dot: 'bg-blue-500',
                    label: 'Received',
                };
            case 'under_review':
            case 'verification':
                return {
                    bg: 'bg-purple-50 text-purple-700 border-purple-200',
                    dot: 'bg-purple-500',
                    label: 'Under Verification',
                };
            case 'categorised':
            case 'assigned':
                return {
                    bg: 'bg-amber-50 text-amber-800 border-amber-200',
                    dot: 'bg-amber-500',
                    label: 'Assigned to Field Desk',
                };
            case 'in_progress':
                return {
                    bg: 'bg-sky-50 text-sky-800 border-sky-200',
                    dot: 'bg-sky-500',
                    label: 'In Progress',
                };
            case 'awaiting_confirmation':
                return {
                    bg: 'bg-orange-50 text-orange-800 border-orange-200 animate-pulse',
                    dot: 'bg-orange-500',
                    label: 'Awaiting Your Confirmation',
                };
            case 'resolved':
                return {
                    bg: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    dot: 'bg-emerald-500',
                    label: 'Resolved & Closed',
                };
            default:
                return {
                    bg: 'bg-slate-100 text-slate-700 border-slate-200',
                    dot: 'bg-slate-500',
                    label: status.replace('_', ' '),
                };
        }
    };

    const statusStyle = getStatusTheme(application.status);
    const effectiveLat = application.lat ?? application.village?.lat ?? null;
    const effectiveLng = application.lng ?? application.village?.lng ?? null;

    return (
        <AccountLayout title={`Case ${application.case_no}`}>
            {/* Top Navigation */}
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <Link
                    href="/account/applications"
                    className="text-thh-primary inline-flex items-center gap-1.5 text-xs font-bold transition hover:underline"
                >
                    <span>←</span>
                    <span>
                        {t(
                            'account.back_applications',
                            'Back to My Applications',
                        )}
                    </span>
                </Link>

                <div className="flex items-center gap-2">
                    <span className="border-thh-border bg-thh-surface text-thh-text rounded-xl border px-3 py-1 font-mono text-xs font-bold shadow-2xs">
                        ID: #{application.id}
                    </span>
                    {application.created_at && (
                        <span className="text-thh-text-muted text-xs">
                            {new Date(
                                application.created_at,
                            ).toLocaleDateString(undefined, {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                            })}
                        </span>
                    )}
                </div>
            </div>

            {/* HEADER HERO CARD */}
            <div className="border-thh-border via-thh-surface relative mb-6 overflow-hidden rounded-[2rem] border bg-gradient-to-r from-amber-500/10 to-amber-600/5 p-6 shadow-sm sm:p-8">
                <span className="shape-blob bg-thh-primary/10 -top-8 -right-8 h-40 w-40" />

                <div className="relative flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div className="mb-2 flex flex-wrap items-center gap-2">
                            <span className="bg-thh-primary rounded-full px-3 py-1 font-mono text-xs font-bold tracking-wider text-white shadow-xs">
                                {application.case_no}
                            </span>
                            <span
                                className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold capitalize ${statusStyle.bg}`}
                            >
                                <span
                                    className={`h-2 w-2 rounded-full ${statusStyle.dot}`}
                                />
                                {statusStyle.label}
                            </span>
                            <span
                                className={`rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ${
                                    application.urgency === 'urgent'
                                        ? 'bg-rose-100 text-rose-800'
                                        : application.urgency === 'medium'
                                          ? 'bg-amber-100 text-amber-800'
                                          : 'bg-emerald-100 text-emerald-800'
                                }`}
                            >
                                {application.urgency === 'urgent' && '🚨 '}
                                {application.urgency} urgency
                            </span>
                        </div>

                        <h1 className="text-thh-text mt-2 font-serif text-2xl font-bold sm:text-3xl">
                            {application.title}
                        </h1>

                        {application.category && (
                            <p className="text-thh-primary mt-1 flex items-center gap-1 text-xs font-medium">
                                <span>📁</span>
                                <span>
                                    {loc(
                                        application.category.name_en,
                                        application.category.name_gu,
                                    )}
                                </span>
                                {application.sub_category && (
                                    <>
                                        <span className="text-thh-text-muted">
                                            /
                                        </span>
                                        <span className="text-thh-text-muted">
                                            {loc(
                                                application.sub_category
                                                    .name_en,
                                                application.sub_category
                                                    .name_gu,
                                            )}
                                        </span>
                                    </>
                                )}
                            </p>
                        )}
                    </div>

                    {application.sla_due_at && (
                        <div className="border-thh-border rounded-2xl border bg-white/80 p-3.5 text-right shadow-2xs backdrop-blur-xs">
                            <span className="text-thh-text-muted block text-[10px] font-bold tracking-wider uppercase">
                                SLA Resolution Target
                            </span>
                            <span className="text-thh-text font-mono text-sm font-bold">
                                {new Date(
                                    application.sla_due_at,
                                ).toLocaleDateString(undefined, {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                })}
                            </span>
                        </div>
                    )}
                </div>
            </div>

            {/* ACTION BANNER: Awaiting confirmation */}
            {awaiting && (
                <div className="animate-fade-in mb-6 rounded-3xl border-2 border-orange-300 bg-orange-50/90 p-6 shadow-sm">
                    <div className="flex items-start gap-3">
                        <span className="text-2xl">🔔</span>
                        <div className="flex-1">
                            <h3 className="text-base font-bold text-orange-950">
                                {t(
                                    'account.confirm_heading',
                                    'Action Required: Field Desk Resolved This Case',
                                )}
                            </h3>
                            <p className="mt-1 text-sm leading-relaxed text-orange-900">
                                {t(
                                    'account.confirm_prompt',
                                    'The village field desk has reported this issue as complete. Please verify the solution and confirm closure, or tell us what is still pending.',
                                )}
                            </p>

                            <div className="mt-4 flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.post(
                                            `/account/applications/${application.id}/confirm`,
                                        )
                                    }
                                    className="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-700"
                                >
                                    ✅{' '}
                                    {t(
                                        'account.confirm_close',
                                        'Yes, Confirm & Close Case',
                                    )}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setIsReopening(!isReopening)}
                                    className="rounded-xl border border-orange-400 bg-white px-4 py-2.5 text-xs font-bold text-orange-900 shadow-2xs transition hover:bg-orange-100"
                                >
                                    {isReopening
                                        ? 'Cancel'
                                        : '🔄 Issue Still Pending (Reopen)'}
                                </button>
                            </div>

                            {isReopening && (
                                <form
                                    className="mt-4 space-y-3 rounded-2xl border border-orange-200 bg-white p-4"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        router.post(
                                            `/account/applications/${application.id}/reopen`,
                                            { reason },
                                        );
                                    }}
                                >
                                    <label className="block text-xs font-bold text-orange-950">
                                        Explain why you still need assistance:
                                    </label>
                                    <textarea
                                        className="border-thh-border w-full rounded-xl border p-3 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-hidden"
                                        rows={3}
                                        value={reason}
                                        onChange={(e) =>
                                            setReason(e.target.value)
                                        }
                                        placeholder={t(
                                            'account.reopen_reason',
                                            'Describe what work remains or what problems are still unresolved...',
                                        )}
                                        required
                                    />
                                    <button
                                        type="submit"
                                        className="rounded-xl bg-orange-700 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-orange-800"
                                    >
                                        Submit Reopen Request
                                    </button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* 2-COLUMN MAIN CONTENT GRID */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                {/* LEFT COLUMN: Problem Details & Location Pin (7 cols) */}
                <div className="space-y-6 lg:col-span-7">
                    {/* Problem Description Card */}
                    <div className="border-thh-border bg-thh-surface space-y-4 rounded-[1.75rem] border p-6 shadow-sm">
                        <div className="border-thh-border/70 flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                                📝 Detailed Case Requirement
                            </h3>
                            <span className="text-thh-text-muted text-[11px] font-medium">
                                Registered Details
                            </span>
                        </div>

                        <p className="text-thh-text text-sm leading-relaxed whitespace-pre-line">
                            {application.description}
                        </p>

                        {application.documents &&
                            application.documents.length > 0 && (
                                <div className="border-thh-border/70 border-t pt-2">
                                    <h4 className="text-thh-text mb-2 text-xs font-bold">
                                        📎 Attached Evidence & Documents (
                                        {application.documents.length})
                                    </h4>
                                    <div className="flex flex-wrap gap-2">
                                        {application.documents.map((doc) => (
                                            <a
                                                key={doc.id}
                                                href={`/storage/${doc.path}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="border-thh-border bg-thh-bg text-thh-text hover:bg-thh-primary/10 hover:text-thh-primary inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-medium transition"
                                            >
                                                <span>📄</span>
                                                <span>{doc.type}</span>
                                            </a>
                                        ))}
                                    </div>
                                </div>
                            )}
                    </div>

                    {/* LOCATION & LIVE GPS PIN CARD */}
                    <div className="border-thh-border via-thh-surface space-y-4 rounded-[1.75rem] border bg-gradient-to-br from-amber-500/5 to-emerald-500/5 p-6 shadow-sm">
                        <div className="border-thh-border/70 flex items-center justify-between border-b pb-3">
                            <div className="flex items-center gap-2">
                                <span className="bg-thh-primary/15 flex h-8 w-8 items-center justify-center rounded-xl text-base">
                                    📍
                                </span>
                                <div>
                                    <h3 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                        Geographic Location & Live Pin
                                    </h3>
                                    <p className="text-thh-text-muted text-[11px]">
                                        Field coordination mapping
                                    </p>
                                </div>
                            </div>

                            {effectiveLat !== null && effectiveLng !== null ? (
                                <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                    Pin Active
                                </span>
                            ) : (
                                <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                    Village Level
                                </span>
                            )}
                        </div>

                        {/* Jurisdiction badges */}
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div className="border-thh-border rounded-xl border bg-white/70 p-3">
                                <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                    District
                                </span>
                                <span className="text-thh-text text-xs font-bold">
                                    {application.village?.taluka?.district
                                        ?.name_en || 'Dang'}
                                </span>
                            </div>

                            <div className="border-thh-border rounded-xl border bg-white/70 p-3">
                                <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                    Taluka
                                </span>
                                <span className="text-thh-text text-xs font-bold">
                                    {application.village?.taluka?.name_en ||
                                        'Ahwa'}
                                </span>
                            </div>

                            <div className="border-thh-border rounded-xl border bg-white/70 p-3">
                                <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                    Village
                                </span>
                                <span className="text-thh-text text-xs font-bold">
                                    {application.village?.name_en || 'Central'}
                                </span>
                            </div>
                        </div>

                        {/* Interactive GPS Pin Card */}
                        {effectiveLat !== null && effectiveLng !== null ? (
                            <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <span className="block text-[10px] font-bold tracking-wider text-emerald-900 uppercase">
                                            GPS Satellite Coordinates
                                        </span>
                                        <div className="mt-0.5 flex items-center gap-2">
                                            <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-500" />
                                            <span className="font-mono text-sm font-bold text-emerald-950">
                                                {effectiveLat.toFixed(5)}° N,{' '}
                                                {effectiveLng.toFixed(5)}° E
                                            </span>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <a
                                            href={`https://www.google.com/maps?q=${effectiveLat},${effectiveLng}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-800"
                                        >
                                            <span>🗺️ Open in Maps</span>
                                            <span>↗</span>
                                        </a>
                                        <a
                                            href={`https://www.google.com/maps/dir/?api=1&destination=${effectiveLat},${effectiveLng}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1.5 rounded-xl border border-emerald-700/30 bg-white/80 px-3 py-2 text-xs font-semibold text-emerald-900 shadow-2xs transition hover:bg-white"
                                        >
                                            <span>🧭 Directions</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="border-thh-border bg-thh-bg/40 rounded-2xl border border-dashed p-4 text-center">
                                <p className="text-thh-text-muted text-xs">
                                    No GPS coordinates attached to this case.
                                    Village level desk routing applied.
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* RIGHT COLUMN: Beneficiary Demographics & Progress Tracker (5 cols) */}
                <div className="space-y-6 lg:col-span-5">
                    {/* Citizen / Beneficiary Profile */}
                    <div className="border-thh-border bg-thh-surface space-y-4 rounded-[1.75rem] border p-6 shadow-sm">
                        <div className="border-thh-border/70 flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                                👤 Citizen & Beneficiary Details
                            </h3>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div>
                                <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                    Beneficiary Name
                                </span>
                                <span className="text-thh-text text-sm font-bold">
                                    {application.beneficiary_name ||
                                        application.user?.name ||
                                        'Citizen'}
                                </span>
                            </div>

                            <div className="grid grid-cols-2 gap-2">
                                <div>
                                    <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                        Mobile Phone
                                    </span>
                                    <span className="text-thh-text font-mono font-medium">
                                        {application.beneficiary_phone ||
                                            application.user?.phone ||
                                            'Not provided'}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                        Registered Email
                                    </span>
                                    <span className="text-thh-text block truncate font-medium">
                                        {application.user?.email ||
                                            'Not provided'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Follow-ups Card if any */}
                    {application.follow_ups &&
                        application.follow_ups.length > 0 && (
                            <div className="border-thh-border bg-thh-surface space-y-3 rounded-[1.75rem] border p-6 shadow-sm">
                                <h3 className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                                    📅 Scheduled Follow-ups
                                </h3>
                                <div className="space-y-2">
                                    {application.follow_ups.map((fu) => (
                                        <div
                                            key={fu.id}
                                            className="border-thh-border bg-thh-bg/60 rounded-xl border p-3 text-xs"
                                        >
                                            <div className="flex items-center justify-between font-semibold">
                                                <span>
                                                    {fu.scheduled_for ||
                                                    fu.scheduled_at
                                                        ? new Date(
                                                              (fu.scheduled_for ||
                                                                  fu.scheduled_at)!,
                                                          ).toLocaleDateString()
                                                        : 'Upcoming'}
                                                </span>
                                                <span className="text-thh-primary capitalize">
                                                    {fu.status}
                                                </span>
                                            </div>
                                            {fu.notes && (
                                                <p className="text-thh-text-muted mt-1">
                                                    {fu.notes}
                                                </p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                    {/* TIMELINE / PROGRESS STEPPER */}
                    <div className="border-thh-border bg-thh-surface space-y-4 rounded-[1.75rem] border p-6 shadow-sm">
                        <div className="border-thh-border/70 flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                                ⏱️ Case Timeline ({events.length})
                            </h3>
                        </div>

                        {events.length === 0 ? (
                            <p className="text-thh-text-muted text-xs italic">
                                Case newly initiated. Updates will appear as
                                field officers review.
                            </p>
                        ) : (
                            <ol className="before:bg-thh-border relative space-y-4 pl-4 before:absolute before:top-2 before:bottom-2 before:left-1.5 before:w-0.5">
                                {events.map((event, idx) => (
                                    <li
                                        key={event.id ?? idx}
                                        className="relative pl-4"
                                    >
                                        <span className="bg-thh-primary ring-thh-surface absolute top-1 -left-4 flex h-3 w-3 items-center justify-center rounded-full ring-4" />
                                        <div className="flex flex-wrap items-center justify-between gap-1">
                                            <span className="text-thh-text text-xs font-bold">
                                                {event.actor?.name ||
                                                    'Field Desk Officer'}
                                            </span>
                                            <span className="text-thh-text-muted text-[10px]">
                                                {new Date(
                                                    event.created_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        day: 'numeric',
                                                        month: 'short',
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                    },
                                                )}
                                            </span>
                                        </div>

                                        {event.to_status && (
                                            <span className="bg-thh-bg text-thh-primary mt-0.5 inline-block rounded-md px-2 py-0.5 text-[10px] font-semibold capitalize">
                                                Status:{' '}
                                                {event.to_status.replace(
                                                    '_',
                                                    ' ',
                                                )}
                                            </span>
                                        )}

                                        <p className="text-thh-text-muted mt-1 text-xs leading-relaxed">
                                            {event.body}
                                        </p>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </div>
                </div>
            </div>
        </AccountLayout>
    );
}
