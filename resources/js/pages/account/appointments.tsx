import React from 'react';
import AccountLayout from '../../components/AccountLayout';
import { useTranslation } from '../../lib/i18n';

export default function AccountAppointments({
    appointments,
}: {
    appointments: {
        data: Array<{
            id: number;
            scheduled_for: string;
            status: string;
            notes?: string;
            application?: { case_no: string; title: string };
            assignee?: { name: string };
        }>;
    };
}) {
    const { t } = useTranslation();
    const rows = appointments.data ?? [];

    return (
        <AccountLayout title={t('account.appointments', 'Appointments')}>
            <div className="space-y-3">
                {rows.length === 0 && (
                    <p className="text-thh-text-muted">
                        {t(
                            'account.no_appointments',
                            'No visits scheduled yet.',
                        )}
                    </p>
                )}
                {rows.map((row) => (
                    <div
                        key={row.id}
                        className="bg-thh-surface border-thh-border rounded-2xl border p-4"
                    >
                        <p className="text-thh-primary font-mono text-sm font-bold">
                            {row.application?.case_no}
                        </p>
                        <p className="font-semibold">
                            {row.application?.title}
                        </p>
                        <p className="text-thh-text-muted mt-1 text-sm">
                            {new Date(row.scheduled_for).toLocaleString()} ·{' '}
                            {row.assignee?.name || 'Desk'} · {row.status}
                        </p>
                        {row.notes && (
                            <p className="mt-2 text-sm">{row.notes}</p>
                        )}
                    </div>
                ))}
            </div>
        </AccountLayout>
    );
}
