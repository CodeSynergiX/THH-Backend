import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowRight, Lock, Mail } from 'lucide-react';
import { useTranslation } from '../../lib/i18n';
import BrandMark from '../../components/BrandMark';

export default function Login() {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const [mode, setMode] = React.useState<'password' | 'otp'>('password');
    const { props } = usePage<{
        flash?: { status?: string };
        branding?: { public_portal_enabled?: boolean };
    }>();
    const status = props.flash?.status;
    const isPublicPortalEnabled =
        props.branding?.public_portal_enabled !== false;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        code: '',
        remember: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(mode === 'otp' ? '/login/otp/verify' : '/login');
    };

    const sendOtp = () => {
        post('/login/otp/request');
    };

    const deskShortcuts = [
        { role: 'citizen', label: t('auth.login.as_citizen', 'Citizen') },
        { role: 'staff', label: t('auth.login.as_staff', 'Field staff') },
        { role: 'mentor', label: t('auth.login.as_mentor', 'Mentor') },
        { role: 'admin', label: t('auth.login.as_admin', 'Coordinator') },
    ];

    return (
        <div className="bg-thh-bg text-thh-text min-h-screen font-sans lg:grid lg:grid-cols-[1.05fr_0.95fr]">
            <Head title={t('auth.login.sign_in', 'Sign in')} />

            <aside className="bg-thh-secondary relative hidden overflow-hidden px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <span className="shape-blob absolute -top-16 -left-10 h-56 w-56 bg-white/10" />
                <span className="shape-blob bg-thh-accent/40 absolute right-0 bottom-10 h-40 w-40" />
                <div className="relative">
                    <p className="mb-4 text-xs font-bold tracking-[0.22em] text-white/70 uppercase">
                        {t('home.field_desk', 'Village field desk')}
                    </p>
                    <h1 className="max-w-md font-serif text-4xl leading-tight">
                        {t(
                            'auth.login.side_title',
                            'Same case number on the desk and on the phone.',
                        )}
                    </h1>
                    <p className="mt-5 max-w-md text-base leading-7 text-white/85">
                        {t(
                            'auth.login.side_body',
                            'Use the password emailed with your first application, or a one-time code to that email. Mentors and volunteers only see cases assigned to them.',
                        )}
                    </p>
                </div>
                <p className="relative text-sm text-white/70">
                    {t('app.portal.tagline', 'Global Gramin Vikas Trust')}
                </p>
            </aside>

            <div className="relative flex flex-col justify-center px-4 py-12 sm:px-10">
                <div className="bg-thh-surface border-thh-border absolute top-4 right-4 flex items-center rounded-xl border p-1 text-xs">
                    {supportedLocales.map((lang) => (
                        <button
                            key={lang.code}
                            type="button"
                            onClick={() => switchLocale(lang.code)}
                            className={`rounded-lg px-3 py-1 font-semibold transition-all ${
                                locale === lang.code
                                    ? 'bg-thh-secondary text-white shadow-xs'
                                    : 'text-thh-text-muted hover:text-thh-text'
                            }`}
                        >
                            {lang.native_name || lang.name}
                        </button>
                    ))}
                </div>

                <div className="mx-auto w-full max-w-md space-y-6">
                    {isPublicPortalEnabled ? (
                        <Link href="/" className="inline-flex">
                            <BrandMark />
                        </Link>
                    ) : (
                        <div className="inline-flex">
                            <BrandMark />
                        </div>
                    )}
                    <div>
                        <h2 className="font-serif text-3xl">
                            {t('auth.login.sign_in', 'Sign in to the desk')}
                        </h2>
                        <p className="text-thh-text-muted mt-2 text-base">
                            {t(
                                'auth.login.hint',
                                'Password from the first-apply email, or OTP to the same address.',
                            )}
                        </p>
                    </div>

                    <div className="bg-thh-surface border-thh-border space-y-6 rounded-[1.6rem] border px-6 py-8 shadow-sm">
                        {status && (
                            <p className="rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">
                                {status}
                            </p>
                        )}
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="text-thh-text mb-1 block text-sm font-semibold">
                                    {t(
                                        'auth.login.admin_email_label',
                                        'Email Address',
                                    )}
                                </label>
                                <div className="relative">
                                    <Mail className="text-thh-text-muted absolute top-3 left-3 h-4 w-4" />
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        placeholder="e.g. ramesh@village.in"
                                        required
                                        className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-secondary w-full rounded-xl border py-2.5 pr-3.5 pl-9 text-base focus:ring-1 focus:outline-hidden"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-sm font-semibold text-rose-600">
                                        {errors.email}
                                    </p>
                                )}
                            </div>

                            {mode === 'password' ? (
                                <div>
                                    <label className="text-thh-text mb-1 block text-sm font-semibold">
                                        {t(
                                            'auth.login.password_label',
                                            'Password',
                                        )}
                                    </label>
                                    <div className="relative">
                                        <Lock className="text-thh-text-muted absolute top-3 left-3 h-4 w-4" />
                                        <input
                                            type="password"
                                            value={data.password}
                                            onChange={(e) =>
                                                setData(
                                                    'password',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="••••••••"
                                            required
                                            className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-secondary w-full rounded-xl border py-2.5 pr-3.5 pl-9 text-base focus:ring-1 focus:outline-hidden"
                                        />
                                    </div>
                                    {errors.password && (
                                        <p className="mt-1 text-sm font-semibold text-rose-600">
                                            {errors.password}
                                        </p>
                                    )}
                                </div>
                            ) : (
                                <div>
                                    <label className="text-thh-text mb-1 block text-sm font-semibold">
                                        {t('auth.login.otp_label', 'Email OTP')}
                                    </label>
                                    <input
                                        value={data.code}
                                        onChange={(e) =>
                                            setData('code', e.target.value)
                                        }
                                        placeholder="6-digit code"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-xl border px-3 py-2.5 text-base"
                                    />
                                    {errors.code && (
                                        <p className="mt-1 text-sm font-semibold text-rose-600">
                                            {errors.code}
                                        </p>
                                    )}
                                    <button
                                        type="button"
                                        onClick={sendOtp}
                                        className="text-thh-secondary mt-2 text-sm underline"
                                    >
                                        {t(
                                            'auth.login.send_otp',
                                            'Send OTP to email',
                                        )}
                                    </button>
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-thh-secondary inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-base font-semibold text-white shadow-xs transition-all hover:opacity-95 disabled:opacity-50"
                            >
                                <span>
                                    {processing
                                        ? t(
                                              'auth.login.authenticating',
                                              'Signing in...',
                                          )
                                        : t(
                                              'auth.login.admin_login_button',
                                              'Open the desk',
                                          )}
                                </span>
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </form>

                        <div className="flex justify-between text-sm">
                            <button
                                type="button"
                                onClick={() =>
                                    setMode(
                                        mode === 'password'
                                            ? 'otp'
                                            : 'password',
                                    )
                                }
                                className="text-thh-secondary underline"
                            >
                                {mode === 'password'
                                    ? t('auth.login.use_otp', 'Use email OTP')
                                    : t(
                                          'auth.login.use_password',
                                          'Use password',
                                      )}
                            </button>
                            <Link href="/forgot-password" className="underline">
                                {t('auth.login.forgot', 'Forgot password')}
                            </Link>
                        </div>

                        {import.meta.env.DEV && (
                            <div className="border-thh-border space-y-2 border-t pt-4">
                                <span className="text-thh-text-muted block text-[11px] font-bold tracking-wider uppercase">
                                    {t(
                                        'auth.login.desk_shortcuts',
                                        'Demo desk shortcuts',
                                    )}
                                </span>
                                <div className="flex flex-wrap gap-2">
                                    {deskShortcuts.map((d) => (
                                        <a
                                            key={d.role}
                                            href={`/dev/quick-login/${d.role}`}
                                            className="border-thh-border bg-thh-bg hover:border-thh-secondary rounded-full border px-3 py-1.5 text-xs font-semibold"
                                        >
                                            {d.label}
                                        </a>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {isPublicPortalEnabled && (
                        <p className="text-thh-text-muted text-center text-xs">
                            <Link
                                href="/"
                                className="hover:text-thh-text underline"
                            >
                                ←{' '}
                                {t(
                                    'auth.login.back_public',
                                    'Back to public desk',
                                )}
                            </Link>
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
