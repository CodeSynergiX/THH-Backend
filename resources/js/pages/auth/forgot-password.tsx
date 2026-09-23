import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '../../lib/i18n';

export default function ForgotPassword() {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const status = usePage<{ flash?: { status?: string } }>().props.flash
        ?.status;
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen flex-col justify-center px-4 py-12">
            <Head title={t('auth.forgot.title', 'Forgot password')} />
            <div className="bg-thh-surface border-thh-border absolute top-4 right-4 flex items-center rounded-xl border p-1 text-xs">
                {supportedLocales.map((lang) => (
                    <button
                        key={lang.code}
                        type="button"
                        onClick={() => switchLocale(lang.code)}
                        className={`rounded-lg px-3 py-1 font-semibold ${
                            locale === lang.code
                                ? 'bg-thh-primary text-white'
                                : 'text-thh-text-muted'
                        }`}
                    >
                        {lang.native_name || lang.name}
                    </button>
                ))}
            </div>
            <div className="mx-auto w-full max-w-md">
                <h1 className="font-serif text-3xl">
                    {t('auth.forgot.title', 'Reset your password')}
                </h1>
                <p className="text-thh-text-muted mt-2 text-base">
                    {t(
                        'auth.forgot.body',
                        'Enter the email used on your application. We will send a reset link if the account exists.',
                    )}
                </p>
                {status && (
                    <p className="mt-4 rounded-xl bg-emerald-50 p-3 text-emerald-800">
                        {status}
                    </p>
                )}
                <form
                    className="mt-6 space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        post('/forgot-password');
                    }}
                >
                    <input
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        placeholder={t('apply.email', 'Email')}
                        className="border-thh-border w-full rounded-xl border px-3 py-2.5 text-base"
                    />
                    {errors.email && (
                        <p className="text-sm text-rose-700">{errors.email}</p>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-thh-primary w-full rounded-xl py-2.5 font-semibold text-white"
                    >
                        {t('auth.forgot.submit', 'Send reset link')}
                    </button>
                </form>
                <Link
                    href="/login"
                    className="mt-4 inline-block text-base underline"
                >
                    {t('auth.login.back_public', 'Back to login')}
                </Link>
            </div>
        </div>
    );
}
