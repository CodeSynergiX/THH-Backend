import React from 'react';
import { Link } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';
import { useTranslation } from '../../lib/i18n';

interface ApplicationRow {
    id: number;
    case_no: string;
    title: string;
    status: string;
    urgency: string;
    current_assignee?: { name: string } | null;
}

export default function AccountDashboard({
    status_counts,
    applications,
    next_appointment,
    unread_notifications,
}: {
    status_counts: Record<string, number>;
    applications: ApplicationRow[];
    next_appointment?: {
        scheduled_for: string;
        notes?: string;
        application?: { id: number; case_no: string; title: string };
    } | null;
    unread_notifications: number;
}) {
    const { t } = useTranslation();

    return (
        <AccountLayout title={t('account.dashboard', 'Your applications')}>
            <div className="mb-8 grid gap-3 sm:grid-cols-4">
                {[
                    ['all', t('account.all', 'All')],
                    ['open', t('account.open', 'Open')],
                    [
                        'awaiting_confirmation',
                        t('account.confirm', 'Need confirm'),
                    ],
                    ['resolved', t('account.resolved', 'Closed')],
                ].map(([key, label]) => (
                    <div
                        key={key}
                        className="bg-thh-surface border-thh-border rounded-2xl border p-4"
                    >
                        <p className="text-thh-text-muted text-xs font-bold tracking-wider uppercase">
                            {label}
                        </p>
                        <p className="mt-1 font-serif text-3xl">
                            {status_counts[key] ?? 0}
                        </p>
                    </div>
                ))}
            </div>

            {unread_notifications > 0 && (
                <Link
                    href="/account/notifications"
                    className="bg-thh-accent mb-6 inline-flex rounded-full px-4 py-2 text-sm font-semibold text-white"
                >
                    {unread_notifications}{' '}
                    {t('account.new_alerts', 'new updates')}
                </Link>
            )}

            {next_appointment && (
                <div className="border-thh-secondary/20 bg-thh-secondary/8 mb-8 rounded-2xl border p-5">
                    <p className="text-thh-secondary text-xs font-bold tracking-wider uppercase">
                        {t('account.next_visit', 'Next appointment')}
                    </p>
                    <p className="mt-1 font-semibold">
                        {next_appointment.application?.case_no} ·{' '}
                        {new Date(
                            next_appointment.scheduled_for,
                        ).toLocaleString()}
                    </p>
                    <p className="text-thh-text-muted mt-1 text-sm">
                        {next_appointment.application?.title}
                    </p>
                </div>
            )}

            <div className="space-y-3">
                {applications.map((app) => (
                    <Link
                        key={app.id}
                        href={`/account/applications/${app.id}`}
                        className="bg-thh-surface border-thh-border hover:border-thh-primary block rounded-2xl border p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="text-thh-primary font-mono text-sm font-bold">
                                    {app.case_no}
                                </p>
                                <p className="mt-1 font-semibold">
                                    {app.title}
                                </p>
                            </div>
                            <span className="bg-thh-bg rounded-full px-2.5 py-1 text-xs font-bold uppercase">
                                {app.status}
                            </span>
                        </div>
                    </Link>
                ))}
            </div>
        </AccountLayout>
    );
}
