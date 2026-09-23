import React from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

export default function AdminNotifications({
    notifications,
}: {
    notifications: {
        data: Array<{
            id: number;
            title: string;
            body: string;
            read_at?: string | null;
            created_at: string;
        }>;
    };
}) {
    const { t } = useTranslation();
    const rows = notifications.data ?? [];

    return (
        <AdminLayout title={t('nav.notifications', 'Notifications')}>
            <div className="mb-4">
                <button
                    type="button"
                    onClick={() => router.post('/admin/notifications/read-all')}
                    className="border-thh-border rounded-full border px-4 py-2 text-sm font-semibold"
                >
                    {t('account.mark_all', 'Mark all read')}
                </button>
            </div>
            <div className="space-y-3">
                {rows.map((row) => (
                    <button
                        key={row.id}
                        type="button"
                        onClick={() =>
                            router.post(`/admin/notifications/${row.id}/read`)
                        }
                        className={`border-thh-border w-full rounded-2xl border p-4 text-left ${
                            row.read_at
                                ? 'bg-thh-surface'
                                : 'bg-thh-secondary/8'
                        }`}
                    >
                        <p className="font-semibold">{row.title}</p>
                        <p className="text-thh-text-muted mt-1 text-sm">
                            {row.body}
                        </p>
                    </button>
                ))}
            </div>
        </AdminLayout>
    );
}
