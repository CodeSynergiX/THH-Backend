import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import PublicLayout from './PublicLayout';
import { useTranslation } from '../lib/i18n';

export default function AccountLayout({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    const { t } = useTranslation();
    const { url } = usePage();

    const items = [
        {
            href: '/account',
            label: t('account.overview', 'Overview'),
            match: '/account',
        },
        {
            href: '/account/applications',
            label: t('account.applications', 'My applications'),
            match: '/account/applications',
        },
        {
            href: '/account/appointments',
            label: t('account.appointments', 'Appointments'),
            match: '/account/appointments',
        },
        {
            href: '/account/notifications',
            label: t('account.notifications', 'Notifications'),
            match: '/account/notifications',
        },
    ];

    return (
        <PublicLayout title={title}>
            <div className="mx-auto max-w-6xl px-4 py-8">
                <p className="text-thh-primary mb-1 text-xs font-bold tracking-[0.18em] uppercase">
                    {t('account.kicker', 'Your desk')}
                </p>
                <h1 className="mb-6 font-serif text-3xl">{title}</h1>
                <nav className="mb-8 flex flex-wrap gap-2">
                    {items.map((item) => {
                        const active =
                            item.href === '/account'
                                ? url === '/account' || url === '/account/'
                                : url.startsWith(item.match);
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`rounded-full px-4 py-2 text-sm font-semibold ${
                                    active
                                        ? 'bg-thh-secondary text-white'
                                        : 'bg-thh-surface border-thh-border border'
                                }`}
                            >
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
                {children}
            </div>
        </PublicLayout>
    );
}
