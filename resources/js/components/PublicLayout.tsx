import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { useTranslation } from '../lib/i18n';
import BrandMark from './BrandMark';

export default function PublicLayout({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const { auth, branding } = usePage<{
        auth?: { user?: { name: string; roles?: string[] } | null };
        branding?: { helpline?: string; support_email?: string };
    }>().props;
    const user = auth?.user;
    const helpline = branding?.helpline || '1800-233-5500';

    return (
        <div className="bg-thh-bg text-thh-text min-h-screen overflow-x-hidden text-base">
            <Head title={title} />
            <div className="bg-thh-secondary text-white">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-1.5 text-xs sm:text-sm">
                    <p className="font-semibold tracking-wide">
                        {t('home.field_desk', 'Village field desk')} · GGVT
                    </p>
                    <a
                        href={`tel:${helpline.replace(/\s/g, '')}`}
                        className="rounded-full bg-white/15 px-3 py-1 font-semibold hover:bg-white/25"
                    >
                        {t('app.portal.emergency_contact', 'Helpline')}{' '}
                        {helpline}
                    </a>
                </div>
            </div>
            <header className="border-thh-border bg-thh-surface/95 sticky top-0 z-20 border-b backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                    <Link href="/" className="min-w-0">
                        <BrandMark compact />
                    </Link>
                    <nav className="flex items-center gap-2 text-sm sm:gap-3">
                        <Link
                            href="/"
                            className="hover:text-thh-primary hidden rounded-full px-3 py-1.5 sm:inline"
                        >
                            {t('nav.home', 'Home')}
                        </Link>
                        <Link
                            href="/#services"
                            className="hover:text-thh-primary hidden rounded-full px-3 py-1.5 md:inline"
                        >
                            {t('home.services', 'Services')}
                        </Link>
                        <Link
                            href="/about"
                            className="hover:text-thh-primary rounded-full px-3 py-1.5"
                        >
                            {t('nav.about', 'About')}
                        </Link>
                        {user ? (
                            user.roles?.some((role) =>
                                [
                                    'admin',
                                    'super_admin',
                                    'staff',
                                    'mentor',
                                    'volunteer',
                                    'collector',
                                ].includes(role),
                            ) ? (
                                <Link
                                    href="/admin/dashboard"
                                    className="bg-thh-primary rounded-full px-3 py-1.5 font-semibold text-white"
                                >
                                    {t('nav.desk', 'Desk')}
                                </Link>
                            ) : (
                                <Link
                                    href="/account"
                                    className="bg-thh-primary rounded-full px-3 py-1.5 font-semibold text-white"
                                >
                                    {t('nav.my_desk', 'My desk')}
                                </Link>
                            )
                        ) : (
                            <Link
                                href="/login"
                                className="bg-thh-primary rounded-full px-3 py-1.5 font-semibold text-white"
                            >
                                {t('nav.login', 'Login')}
                            </Link>
                        )}
                        <div className="bg-thh-bg border-thh-border flex rounded-full border p-0.5">
                            {supportedLocales.map((lang) => (
                                <button
                                    key={lang.code}
                                    type="button"
                                    onClick={() => switchLocale(lang.code)}
                                    className={`rounded-full px-2.5 py-1 text-xs font-bold uppercase ${
                                        locale === lang.code
                                            ? 'bg-thh-primary text-white'
                                            : ''
                                    }`}
                                >
                                    {lang.code}
                                </button>
                            ))}
                        </div>
                    </nav>
                </div>
            </header>
            <main>{children}</main>
            <footer className="relative mt-16 overflow-hidden">
                <span className="shape-blob bg-thh-primary/15 -bottom-16 -left-10 h-40 w-40" />
                <span className="shape-blob bg-thh-secondary/20 -right-8 -bottom-10 h-32 w-32" />
                <div className="bg-thh-secondary text-white">
                    <div className="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-3">
                        <div>
                            <BrandMark inverted />
                            <p className="mt-3 text-sm leading-6 text-white/85">
                                {t(
                                    'home.footer_blurb',
                                    'A public desk of Global Gramin Vikas Trust. Apply from the village, follow the same case on phone or web, and keep every update on the email you give us.',
                                )}
                            </p>
                        </div>
                        <div>
                            <p className="mb-3 text-xs font-bold tracking-[0.2em] text-white/70 uppercase">
                                {t('home.services', 'Services')}
                            </p>
                            <div className="flex flex-col gap-2 text-sm">
                                <Link
                                    href="/community/schemes"
                                    className="hover:underline"
                                >
                                    {t(
                                        'content.modules.schemes',
                                        'Government Schemes',
                                    )}
                                </Link>
                                <Link
                                    href="/community/health"
                                    className="hover:underline"
                                >
                                    {t(
                                        'content.modules.health',
                                        'Health camps',
                                    )}
                                </Link>
                                <Link href="/about" className="hover:underline">
                                    {t('nav.about', 'About')}
                                </Link>
                            </div>
                        </div>
                        <div>
                            <p className="mb-3 text-xs font-bold tracking-[0.2em] text-white/70 uppercase">
                                {t('app.portal.emergency_contact', 'Helpline')}
                            </p>
                            <p className="font-serif text-2xl">{helpline}</p>
                            <p className="mt-2 text-sm text-white/80">
                                {branding?.support_email || 'support@ggvt.org'}
                            </p>
                            <div className="mt-4 flex gap-4 text-sm">
                                <Link href="/privacy" className="underline">
                                    {t('nav.privacy', 'Privacy')}
                                </Link>
                                <Link href="/terms" className="underline">
                                    {t('nav.terms', 'Terms')}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
