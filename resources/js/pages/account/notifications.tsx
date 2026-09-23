import React from 'react';
import { router } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';
import { useTranslation } from '../../lib/i18n';

export default function AccountNotifications({
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
        <AccountLayout title={t('account.notifications', 'Notifications')}>
            <div className="space-y-3">
                {rows.map((row) => (
                    <button
                        key={row.id}
                        type="button"
                        onClick={() =>
                            router.post(`/account/notifications/${row.id}/read`)
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
        </AccountLayout>
    );
}
