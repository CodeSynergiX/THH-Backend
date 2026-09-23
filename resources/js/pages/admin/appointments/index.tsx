import React from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

export default function AdminAppointments({
    appointments,
}: {
    appointments: {
        data: Array<{
            id: number;
            scheduled_for: string;
            status: string;
            notes?: string;
            application?: {
                id: number;
                case_no: string;
                title: string;
                user?: { name: string };
            };
            assignee?: { name: string };
        }>;
    };
}) {
    const { t } = useTranslation();
    const rows = appointments.data ?? [];

    return (
        <AdminLayout title={t('nav.appointments', 'Appointments')}>
            <div className="space-y-3">
                {rows.map((row) => (
                    <Link
                        key={row.id}
                        href={`/admin/cases/${row.application?.id}`}
                        className="bg-thh-surface border-thh-border block rounded-2xl border p-4"
                    >
                        <p className="text-thh-primary font-mono text-sm font-bold">
                            {row.application?.case_no}
                        </p>
                        <p className="font-semibold">
                            {row.application?.title}
                        </p>
                        <p className="text-thh-text-muted mt-1 text-sm">
                            {new Date(row.scheduled_for).toLocaleString()} ·{' '}
                            {row.assignee?.name} · {row.status}
                        </p>
                    </Link>
                ))}
            </div>
        </AdminLayout>
    );
}
