import React from 'react';
import { Link } from '@inertiajs/react';
import {
    LayoutDashboard,
    AlertTriangle,
    Clock,
    CheckCircle2,
    Activity,
    ShieldAlert,
    ArrowRight,
    Star,
    ChevronRight,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

interface UrgentCase {
    id: number;
    case_no: string;
    title: string;
    urgency: string;
    priority: string;
    status: string;
    sla_due_at?: string;
    current_assignee?: { id: number; name: string };
    village?: {
        name_en: string;
        taluka?: {
            name_en: string;
            district?: { name_en: string };
        };
    };
}

interface TimelineEvent {
    id: number;
    title_key?: string;
    body?: string;
    created_at: string;
    actor?: { id: number; name: string };
    application?: { id: number; case_no: string; title: string };
}

interface DashboardProps {
    metrics: {
        total_cases: number;
        in_verification: number;
        in_assistance: number;
        resolved: number;
        sla_breached: number;
        avg_rating?: number;
    };
    urgent_cases: UrgentCase[];
    recent_timeline: TimelineEvent[];
    category_breakdown: Array<{
        id: number;
        slug: string;
        applications_count?: number;
    }>;
    district_breakdown: Array<{
        id: number;
        name_en: string;
        name_gu: string;
        villages_count?: number;
    }>;
}

export default function DashboardIndex({
    metrics,
    urgent_cases: urgentCases,
    recent_timeline: recentTimeline,
    category_breakdown,
    district_breakdown,
}: DashboardProps) {
    const { t } = useTranslation();

    const metricCards = [
        {
            title: 'Total Applications',
            value: metrics.total_cases,
            icon: LayoutDashboard,
            color: 'text-thh-primary',
            bg: 'bg-thh-primary/10 border-thh-primary/20',
        },
        {
            title: 'In Verification',
            value: metrics.in_verification,
            icon: Clock,
            color: 'text-purple-600 dark:text-purple-400',
            bg: 'bg-purple-500/10 border-purple-500/20',
        },
        {
            title: 'In Assistance',
            value: metrics.in_assistance,
            icon: Activity,
            color: 'text-amber-600 dark:text-amber-400',
            bg: 'bg-amber-500/10 border-amber-500/20',
        },
        {
            title: 'SLA Breached',
            value: metrics.sla_breached,
            icon: ShieldAlert,
            color: 'text-rose-600 dark:text-rose-400',
            bg: 'bg-rose-500/10 border-rose-500/20',
        },
        {
            title: 'Resolved Cases',
            value: metrics.resolved,
            icon: CheckCircle2,
            color: 'text-emerald-600 dark:text-emerald-400',
            bg: 'bg-emerald-500/10 border-emerald-500/20',
        },
        {
            title: 'Citizen Feedback',
            value: metrics.avg_rating ? `${metrics.avg_rating} / 5` : '-',
            icon: Star,
            color: 'text-thh-accent',
            bg: 'bg-thh-accent/10 border-thh-accent/20',
        },
    ];

    return (
        <AdminLayout title="Dashboard">
            <div className="space-y-8">
                {/* Metrics Grid */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    {metricCards.map((m, idx) => {
                        const Icon = m.icon;
                        return (
                            <div
                                key={idx}
                                className="bg-thh-surface border-thh-border hover:border-thh-primary space-y-2 rounded-2xl border p-4.5 shadow-2xs transition-colors"
                            >
                                <div className="flex items-center justify-between">
                                    <span className="text-thh-text-muted truncate text-[11px] font-bold tracking-wider uppercase">
                                        {m.title}
                                    </span>
                                    <div
                                        className={`rounded-lg border p-1.5 ${m.bg}`}
                                    >
                                        <Icon
                                            className={`h-4 w-4 ${m.color}`}
                                        />
                                    </div>
                                </div>
                                <span
                                    className={`block text-2xl font-black tracking-tight ${m.color}`}
                                >
                                    {m.value}
                                </span>
                            </div>
                        );
                    })}
                </div>

                {/* Main Middle Row: Urgent Attention Queue (7 Cols) & Recent Timeline Activity (5 Cols) */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Urgent Queue */}
                    <div className="bg-thh-surface border-thh-border space-y-5 rounded-2xl border p-6 shadow-2xs lg:col-span-7">
                        <div className="border-thh-border flex items-center justify-between border-b pb-4">
                            <div className="flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5 text-rose-600" />
                                <h3 className="text-thh-text text-sm font-bold tracking-wider uppercase">
                                    Urgent & SLA Attention Queue
                                </h3>
                            </div>
                            <Link
                                href="/admin/cases?status=verification"
                                className="text-thh-primary flex items-center gap-1 text-xs font-semibold hover:underline"
                            >
                                <span>View all cases</span>
                                <ArrowRight className="h-3.5 w-3.5" />
                            </Link>
                        </div>

                        {urgentCases.length === 0 ? (
                            <div className="text-thh-text-muted py-12 text-center text-xs">
                                {t(
                                    'app.portal.empty_records',
                                    'No cases currently pending urgent action.',
                                )}
                            </div>
                        ) : (
                            <div className="divide-thh-border divide-y">
                                {urgentCases.map((c) => {
                                    const isBreached =
                                        c.sla_due_at &&
                                        new Date(c.sla_due_at).getTime() <
                                            Date.now();
                                    return (
                                        <div
                                            key={c.id}
                                            className="flex items-center justify-between gap-4 py-3.5"
                                        >
                                            <div className="space-y-1 truncate">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-thh-text font-mono text-xs font-bold">
                                                        {c.case_no}
                                                    </span>
                                                    <span
                                                        className={`rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                            isBreached
                                                                ? 'border-rose-500/20 bg-rose-500/10 text-rose-600'
                                                                : 'border-amber-500/20 bg-amber-500/10 text-amber-600'
                                                        }`}
                                                    >
                                                        {isBreached
                                                            ? 'SLA Breached'
                                                            : 'Due Soon'}
                                                    </span>
                                                </div>
                                                <h4 className="text-thh-text truncate text-xs font-semibold">
                                                    {c.title}
                                                </h4>
                                                <p className="text-thh-text-muted text-[11px]">
                                                    Assignee:{' '}
                                                    {c.current_assignee?.name ||
                                                        'Unassigned'}{' '}
                                                    • District:{' '}
                                                    {c.village?.taluka?.district
                                                        ?.name_en || '-'}
                                                </p>
                                            </div>

                                            <Link
                                                href={`/admin/cases/${c.id}`}
                                                className="bg-thh-primary inline-flex shrink-0 items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-white shadow-2xs hover:opacity-95"
                                            >
                                                <span>Resolve</span>
                                                <ChevronRight className="h-3.5 w-3.5" />
                                            </Link>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Recent Activity Feed */}
                    <div className="bg-thh-surface border-thh-border space-y-5 rounded-2xl border p-6 shadow-2xs lg:col-span-5">
                        <div className="border-thh-border flex items-center justify-between border-b pb-4">
                            <h3 className="text-thh-text text-sm font-bold tracking-wider uppercase">
                                Recent Case Activity
                            </h3>
                            <Link
                                href="/admin/audit"
                                className="text-thh-text-muted hover:text-thh-text text-xs font-semibold"
                            >
                                Audit Log
                            </Link>
                        </div>

                        {recentTimeline.length === 0 ? (
                            <div className="text-thh-text-muted py-12 text-center text-xs">
                                {t(
                                    'app.portal.empty_records',
                                    'No recent activity recorded.',
                                )}
                            </div>
                        ) : (
                            <div className="max-h-[380px] space-y-3.5 overflow-y-auto pr-1">
                                {recentTimeline.map((ev) => (
                                    <div
                                        key={ev.id}
                                        className="bg-thh-bg border-thh-border space-y-1 rounded-xl border p-3 text-xs"
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className="text-thh-primary font-mono font-bold">
                                                {ev.application?.case_no}
                                            </span>
                                            <span className="text-thh-text-muted text-[10px]">
                                                {ev.created_at
                                                    ? new Date(
                                                          ev.created_at,
                                                      ).toLocaleTimeString()
                                                    : '-'}
                                            </span>
                                        </div>
                                        <p className="text-thh-text leading-snug font-medium">
                                            {ev.body ||
                                                t(
                                                    ev.title_key ||
                                                        'app.timeline.update',
                                                    'Status changed',
                                                )}
                                        </p>
                                        <span className="text-thh-text-muted block text-[10px]">
                                            by {ev.actor?.name || 'System'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Bottom Row: Category & District Breakdown */}
                <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                    {/* Category Distribution */}
                    <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                        <h3 className="text-thh-text text-sm font-bold tracking-wider uppercase">
                            Applications by Support Category
                        </h3>
                        <div className="grid grid-cols-2 gap-3">
                            {category_breakdown.map((cat) => (
                                <div
                                    key={cat.id}
                                    className="bg-thh-bg border-thh-border flex items-center justify-between rounded-xl border p-3 text-xs"
                                >
                                    <span className="text-thh-text font-bold capitalize">
                                        {cat.slug}
                                    </span>
                                    <span className="text-thh-primary bg-thh-primary/10 rounded-full px-2 py-0.5 font-mono font-extrabold">
                                        {cat.applications_count || 0}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* District Coverage */}
                    <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                        <h3 className="text-thh-text text-sm font-bold tracking-wider uppercase">
                            Geographic Reach (Tribal Districts)
                        </h3>
                        <div className="grid grid-cols-2 gap-3">
                            {district_breakdown.map((d) => (
                                <div
                                    key={d.id}
                                    className="bg-thh-bg border-thh-border flex items-center justify-between rounded-xl border p-3 text-xs"
                                >
                                    <div>
                                        <span className="text-thh-text block font-bold">
                                            {d.name_en}
                                        </span>
                                        <span className="text-thh-text-muted text-[10px]">
                                            {d.name_gu}
                                        </span>
                                    </div>
                                    <span className="text-thh-secondary font-mono text-xs font-bold">
                                        {d.villages_count || 0} villages
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
