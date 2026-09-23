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
}

export default function AccountApplications({
    applications,
}: {
    applications: {
        data: ApplicationRow[];
    };
}) {
    const { t } = useTranslation();
    const rows = applications.data ?? [];

    return (
        <AccountLayout title={t('account.applications', 'My applications')}>
            <div className="space-y-3">
                {rows.length === 0 && (
                    <p className="text-thh-text-muted">
                        {t(
                            'account.empty',
                            'No applications yet. Open a service desk to apply.',
                        )}
                    </p>
                )}
                {rows.map((app) => (
                    <Link
                        key={app.id}
                        href={`/account/applications/${app.id}`}
                        className="bg-thh-surface border-thh-border flex items-center justify-between rounded-2xl border p-4"
                    >
                        <div>
                            <p className="text-thh-primary font-mono text-sm font-bold">
                                {app.case_no}
                            </p>
                            <p className="font-semibold">{app.title}</p>
                        </div>
                        <span className="text-xs font-bold uppercase">
                            {app.urgency} · {app.status}
                        </span>
                    </Link>
                ))}
            </div>
        </AccountLayout>
    );
}
