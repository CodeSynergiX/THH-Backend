import React, { useMemo, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from '../lib/i18n';

export interface VillageOption {
    id: number;
    name_en: string;
    name_gu?: string;
    lat?: number | null;
    lng?: number | null;
}

export interface TalukaOption {
    id: number;
    name_en: string;
    name_gu?: string;
    villages?: VillageOption[];
}

export interface DistrictOption {
    id: number;
    name_en: string;
    name_gu?: string;
    talukas?: TalukaOption[];
}

interface ApplyHelpFormProps {
    action: string;
    defaultTitle?: string;
    defaultDescription?: string;
    districts?: DistrictOption[];
}

export default function ApplyHelpForm({
    action,
    defaultTitle = '',
    defaultDescription = '',
    districts = [],
}: ApplyHelpFormProps) {
    const { t, loc } = useTranslation();
    const [step, setStep] = useState(1);
    const [isLocating, setIsLocating] = useState(false);
    const [locationMessage, setLocationMessage] = useState<string | null>(null);

    const form = useForm({
        beneficiary_name: '',
        beneficiary_phone: '',
        email: '',
        district_id: '' as string | number,
        taluka_id: '' as string | number,
        village_id: '' as string | number,
        lat: null as number | null,
        lng: null as number | null,
        title: defaultTitle,
        description: defaultDescription,
        urgency: 'medium' as 'low' | 'medium' | 'urgent',
    });

    const selectedDistrict = useMemo(() => {
        if (!form.data.district_id) return undefined;
        return districts.find(
            (d) => String(d.id) === String(form.data.district_id),
        );
    }, [districts, form.data.district_id]);

    const talukaOptions = useMemo(() => {
        return selectedDistrict?.talukas ?? [];
    }, [selectedDistrict]);

    const selectedTaluka = useMemo(() => {
        if (!form.data.taluka_id) return undefined;
        return talukaOptions.find(
            (tk) => String(tk.id) === String(form.data.taluka_id),
        );
    }, [talukaOptions, form.data.taluka_id]);

    const villageOptions = useMemo(() => {
        return selectedTaluka?.villages ?? [];
    }, [selectedTaluka]);

    const selectedVillage = useMemo(() => {
        if (!form.data.village_id) return undefined;
        return villageOptions.find(
            (v) => String(v.id) === String(form.data.village_id),
        );
    }, [villageOptions, form.data.village_id]);

    const handleDetectLocation = () => {
        if (typeof window === 'undefined' || !navigator.geolocation) {
            setLocationMessage('Geolocation is not supported by your browser.');
            return;
        }

        setIsLocating(true);
        setLocationMessage('Detecting current satellite location...');

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                setIsLocating(false);
                const lat = Number(pos.coords.latitude.toFixed(6));
                const lng = Number(pos.coords.longitude.toFixed(6));
                form.setData((prev) => ({
                    ...prev,
                    lat,
                    lng,
                }));
                setLocationMessage(
                    `Live location captured with high accuracy (${pos.coords.accuracy ? Math.round(pos.coords.accuracy) + 'm' : 'GPS'}).`,
                );
            },
            () => {
                setIsLocating(false);
                // Regional fallback: Ahwa, Dang (20.7532, 73.6841)
                const fallbackLat = 20.7532;
                const fallbackLng = 73.6841;
                form.setData((prev) => ({
                    ...prev,
                    lat: fallbackLat,
                    lng: fallbackLng,
                }));
                setLocationMessage(
                    'GPS permission denied. Using tribal regional coordination pin (Ahwa, Dang).',
                );
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 },
        );
    };

    const handleClearLocation = () => {
        form.setData((prev) => ({
            ...prev,
            lat: null,
            lng: null,
        }));
        setLocationMessage(null);
    };

    const next = () => {
        if (
            step === 1 &&
            (!form.data.beneficiary_name ||
                !form.data.email ||
                !form.data.beneficiary_phone)
        ) {
            return;
        }
        if (step === 2 && (!form.data.title || !form.data.description)) {
            return;
        }
        setStep((s) => Math.min(3, s + 1));
    };

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                if (step < 3) {
                    next();
                    return;
                }
                form.post(action);
            }}
            className="border-thh-border bg-thh-surface relative overflow-hidden rounded-[2rem] border p-6 shadow-md sm:p-8"
        >
            <span className="shape-blob -top-10 -right-10 h-36 w-36 bg-amber-500/10 opacity-70" />

            <div className="flex items-center justify-between gap-2">
                <p className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                    {t('apply.kicker', 'Field desk assistance')}
                </p>
                <span className="bg-thh-primary/10 text-thh-primary rounded-full px-3 py-0.5 text-xs font-bold">
                    Step {step} of 3
                </span>
            </div>

            <h2 className="text-thh-text mt-1 font-serif text-2xl font-bold sm:text-3xl">
                {t('apply.heading', 'Apply for help')}
            </h2>
            <p className="text-thh-text-muted mt-2 text-sm leading-relaxed sm:text-base">
                {t(
                    'apply.intro',
                    'Direct citizen assistance. Register your case with live location pin for fast field response.',
                )}
            </p>

            {/* Step Indicators */}
            <ol className="my-6 flex gap-2">
                {[
                    { num: 1, label: 'Contact' },
                    { num: 2, label: 'Details & Location' },
                    { num: 3, label: 'Review & Send' },
                ].map(({ num, label }) => (
                    <li key={num} className="flex-1">
                        <button
                            type="button"
                            onClick={() => setStep(num)}
                            className="group w-full text-left"
                            aria-label={`Step ${num}: ${label}`}
                        >
                            <div
                                className={`h-2 w-full rounded-full transition-all duration-300 ${
                                    num <= step
                                        ? 'bg-thh-primary'
                                        : 'bg-thh-border group-hover:bg-thh-text-muted/30'
                                }`}
                            />
                            <span className="text-thh-text-muted mt-1 hidden text-[11px] font-medium sm:block">
                                {label}
                            </span>
                        </button>
                    </li>
                ))}
            </ol>

            {/* STEP 1: Applicant / Beneficiary Information */}
            {step === 1 && (
                <div className="animate-fade-in space-y-4">
                    <div className="border-thh-border/60 border-b pb-2">
                        <p className="text-thh-text text-sm font-semibold">
                            👤 {t('apply.step1', 'Who should we contact?')}
                        </p>
                        <p className="text-thh-text-muted text-xs">
                            We will send status updates and case verification to
                            this contact.
                        </p>
                    </div>

                    <div>
                        <label className="text-thh-text mb-1 block text-xs font-semibold">
                            {t('apply.name', 'Beneficiary Full Name')} *
                        </label>
                        <input
                            className="border-thh-border focus:ring-thh-primary focus:border-thh-primary w-full rounded-2xl border bg-white/50 px-4 py-3 text-base transition focus:ring-2 focus:outline-hidden"
                            placeholder="e.g. Rajeshbhai Patel"
                            value={form.data.beneficiary_name}
                            onChange={(e) =>
                                form.setData('beneficiary_name', e.target.value)
                            }
                            required
                        />
                    </div>

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label className="text-thh-text mb-1 block text-xs font-semibold">
                                {t('apply.phone', 'Phone Number')} *
                            </label>
                            <input
                                type="tel"
                                className="border-thh-border focus:ring-thh-primary focus:border-thh-primary w-full rounded-2xl border bg-white/50 px-4 py-3 text-base transition focus:ring-2 focus:outline-hidden"
                                placeholder="10-digit mobile number"
                                value={form.data.beneficiary_phone}
                                onChange={(e) =>
                                    form.setData(
                                        'beneficiary_phone',
                                        e.target.value,
                                    )
                                }
                                required
                            />
                        </div>

                        <div>
                            <label className="text-thh-text mb-1 block text-xs font-semibold">
                                {t('apply.email', 'Email Address')} *
                            </label>
                            <input
                                type="email"
                                className="border-thh-border focus:ring-thh-primary focus:border-thh-primary w-full rounded-2xl border bg-white/50 px-4 py-3 text-base transition focus:ring-2 focus:outline-hidden"
                                placeholder="name@example.com"
                                value={form.data.email}
                                onChange={(e) =>
                                    form.setData('email', e.target.value)
                                }
                                required
                            />
                        </div>
                    </div>
                </div>
            )}

            {/* STEP 2: Support Details & Live Location Pin */}
            {step === 2 && (
                <div className="animate-fade-in space-y-4">
                    <div className="border-thh-border/60 border-b pb-2">
                        <p className="text-thh-text text-sm font-semibold">
                            📝 {t('apply.step2', 'What support do you need?')}
                        </p>
                        <p className="text-thh-text-muted text-xs">
                            Describe the problem and specify your village &
                            geographic location.
                        </p>
                    </div>

                    <div>
                        <label className="text-thh-text mb-1 block text-xs font-semibold">
                            {t('apply.title', 'Request Title')} *
                        </label>
                        <input
                            className="border-thh-border focus:ring-thh-primary focus:border-thh-primary w-full rounded-2xl border bg-white/50 px-4 py-3 text-base transition focus:ring-2 focus:outline-hidden"
                            placeholder="e.g. Drinking water pipeline repair in East Faliya"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            required
                        />
                    </div>

                    <div>
                        <label className="text-thh-text mb-1 block text-xs font-semibold">
                            {t('apply.description', 'Problem Description')} *
                        </label>
                        <textarea
                            className="border-thh-border focus:ring-thh-primary focus:border-thh-primary w-full rounded-2xl border bg-white/50 px-4 py-3 text-base transition focus:ring-2 focus:outline-hidden"
                            rows={3}
                            placeholder="Provide full details of the issue, affected households, or urgency."
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                            required
                        />
                    </div>

                    {/* Urgency Selector */}
                    <div>
                        <label className="text-thh-text mb-1.5 block text-xs font-semibold">
                            Urgency Level
                        </label>
                        <div className="flex flex-wrap gap-2">
                            {(['low', 'medium', 'urgent'] as const).map(
                                (level) => (
                                    <button
                                        key={level}
                                        type="button"
                                        onClick={() =>
                                            form.setData('urgency', level)
                                        }
                                        className={`rounded-xl px-4 py-2 text-xs font-bold capitalize transition ${
                                            form.data.urgency === level
                                                ? level === 'urgent'
                                                    ? 'bg-rose-600 text-white shadow-xs'
                                                    : level === 'medium'
                                                      ? 'bg-amber-600 text-white shadow-xs'
                                                      : 'bg-emerald-600 text-white shadow-xs'
                                                : 'border-thh-border bg-thh-bg text-thh-text hover:bg-thh-surface border'
                                        }`}
                                    >
                                        {level === 'urgent' && '🚨 '}
                                        {level === 'medium' && '⚡ '}
                                        {level === 'low' && '🌱 '}
                                        {t(`urgency.${level}`, level)}
                                    </button>
                                ),
                            )}
                        </div>
                    </div>

                    {/* Regional Dropdowns (District, Taluka, Village) */}
                    {districts.length > 0 && (
                        <div className="border-thh-border bg-thh-bg/60 mt-4 space-y-3 rounded-2xl border p-4">
                            <p className="text-thh-primary text-xs font-bold tracking-wider uppercase">
                                🏘️ Village Jurisdiction
                            </p>
                            <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                                <div>
                                    <label className="text-thh-text-muted mb-1 block text-[11px] font-medium">
                                        District
                                    </label>
                                    <select
                                        className="border-thh-border focus:ring-thh-primary text-thh-text w-full rounded-xl border bg-white px-3 py-2 text-xs font-medium"
                                        value={form.data.district_id}
                                        onChange={(e) => {
                                            form.setData((prev) => ({
                                                ...prev,
                                                district_id: e.target.value,
                                                taluka_id: '',
                                                village_id: '',
                                            }));
                                        }}
                                    >
                                        <option value="">
                                            Select District
                                        </option>
                                        {districts.map((d) => (
                                            <option key={d.id} value={d.id}>
                                                {loc(d.name_en, d.name_gu)}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text-muted mb-1 block text-[11px] font-medium">
                                        Taluka
                                    </label>
                                    <select
                                        className="border-thh-border focus:ring-thh-primary text-thh-text w-full rounded-xl border bg-white px-3 py-2 text-xs font-medium disabled:opacity-50"
                                        value={form.data.taluka_id}
                                        disabled={talukaOptions.length === 0}
                                        onChange={(e) => {
                                            form.setData((prev) => ({
                                                ...prev,
                                                taluka_id: e.target.value,
                                                village_id: '',
                                            }));
                                        }}
                                    >
                                        <option value="">Select Taluka</option>
                                        {talukaOptions.map((tk) => (
                                            <option key={tk.id} value={tk.id}>
                                                {loc(tk.name_en, tk.name_gu)}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text-muted mb-1 block text-[11px] font-medium">
                                        Village
                                    </label>
                                    <select
                                        className="border-thh-border focus:ring-thh-primary text-thh-text w-full rounded-xl border bg-white px-3 py-2 text-xs font-medium disabled:opacity-50"
                                        value={form.data.village_id}
                                        disabled={villageOptions.length === 0}
                                        onChange={(e) => {
                                            const vId = e.target.value;
                                            const matchVillage =
                                                villageOptions.find(
                                                    (v) =>
                                                        String(v.id) ===
                                                        String(vId),
                                                );
                                            form.setData((prev) => ({
                                                ...prev,
                                                village_id: vId,
                                                lat:
                                                    prev.lat ??
                                                    (matchVillage?.lat
                                                        ? Number(
                                                              matchVillage.lat,
                                                          )
                                                        : null),
                                                lng:
                                                    prev.lng ??
                                                    (matchVillage?.lng
                                                        ? Number(
                                                              matchVillage.lng,
                                                          )
                                                        : null),
                                            }));
                                        }}
                                    >
                                        <option value="">Select Village</option>
                                        {villageOptions.map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {loc(v.name_en, v.name_gu)}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* LIVE LOCATION / LOCATION PIN CARD */}
                    <div className="border-thh-primary/40 rounded-2xl border-2 border-dashed bg-gradient-to-br from-amber-500/5 via-transparent to-amber-500/10 p-4 transition">
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex items-center gap-2">
                                <span className="bg-thh-primary/15 flex h-9 w-9 items-center justify-center rounded-xl text-lg">
                                    📍
                                </span>
                                <div>
                                    <h4 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                        Live Location Pin (GPS)
                                    </h4>
                                    <p className="text-thh-text-muted text-[11px]">
                                        Attach live coordinates to help village
                                        field teams navigate directly.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={handleDetectLocation}
                                disabled={isLocating}
                                className="bg-thh-primary flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:opacity-90 disabled:opacity-60"
                            >
                                {isLocating ? (
                                    <>
                                        <span className="inline-block h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                        <span>Pinning...</span>
                                    </>
                                ) : (
                                    <>
                                        <span>📍 Detect Pin</span>
                                    </>
                                )}
                            </button>
                        </div>

                        {form.data.lat !== null && form.data.lng !== null ? (
                            <div className="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3">
                                <div className="flex items-center gap-2">
                                    <span className="flex h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500" />
                                    <span className="font-mono text-xs font-bold text-emerald-900">
                                        {form.data.lat.toFixed(5)}° N,{' '}
                                        {form.data.lng.toFixed(5)}° E
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <a
                                        href={`https://www.google.com/maps?q=${form.data.lat},${form.data.lng}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="rounded-lg bg-white/80 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 shadow-2xs transition hover:bg-white"
                                    >
                                        🗺️ Preview on Map
                                    </a>
                                    <button
                                        type="button"
                                        onClick={handleClearLocation}
                                        className="text-[11px] font-medium text-rose-600 hover:underline"
                                    >
                                        Clear Pin
                                    </button>
                                </div>
                            </div>
                        ) : (
                            <p className="text-thh-text-muted mt-2 text-[11px] italic">
                                No GPS coordinates attached yet. Tap "Detect
                                Pin" to capture current coordinates.
                            </p>
                        )}

                        {locationMessage && (
                            <p className="text-thh-primary mt-1.5 text-[11px] font-medium">
                                ℹ️ {locationMessage}
                            </p>
                        )}
                    </div>
                </div>
            )}

            {/* STEP 3: Check and Send */}
            {step === 3 && (
                <div className="animate-fade-in space-y-4">
                    <div className="border-thh-border/60 border-b pb-2">
                        <p className="text-thh-text text-sm font-semibold">
                            ✅ {t('apply.step3', 'Check and send')}
                        </p>
                        <p className="text-thh-text-muted text-xs">
                            Please review the registered details before
                            submitting your case.
                        </p>
                    </div>

                    <div className="border-thh-border bg-thh-bg/40 space-y-3 rounded-2xl border p-4">
                        <div className="border-thh-border/60 flex items-center justify-between border-b pb-2">
                            <span className="text-thh-text-muted text-xs font-semibold tracking-wider uppercase">
                                Applicant Contact
                            </span>
                            <span className="text-thh-text text-xs font-bold">
                                {form.data.beneficiary_name}
                            </span>
                        </div>

                        <div className="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span className="text-thh-text-muted block text-[10px]">
                                    Phone
                                </span>
                                <span className="text-thh-text font-mono">
                                    {form.data.beneficiary_phone}
                                </span>
                            </div>
                            <div>
                                <span className="text-thh-text-muted block text-[10px]">
                                    Email
                                </span>
                                <span className="text-thh-text block truncate">
                                    {form.data.email}
                                </span>
                            </div>
                        </div>

                        {(selectedDistrict || selectedVillage) && (
                            <div className="border-thh-border/60 border-t pt-2 text-xs">
                                <span className="text-thh-text-muted block text-[10px]">
                                    Location Jurisdiction
                                </span>
                                <span className="text-thh-text font-medium">
                                    {[
                                        selectedVillage
                                            ? loc(
                                                  selectedVillage.name_en,
                                                  selectedVillage.name_gu,
                                              )
                                            : null,
                                        selectedTaluka
                                            ? loc(
                                                  selectedTaluka.name_en,
                                                  selectedTaluka.name_gu,
                                              )
                                            : null,
                                        selectedDistrict
                                            ? loc(
                                                  selectedDistrict.name_en,
                                                  selectedDistrict.name_gu,
                                              )
                                            : null,
                                    ]
                                        .filter(Boolean)
                                        .join(', ')}
                                </span>
                            </div>
                        )}

                        {form.data.lat !== null && form.data.lng !== null && (
                            <div className="border-thh-border/60 flex items-center justify-between border-t pt-2">
                                <div>
                                    <span className="text-thh-text-muted block text-[10px]">
                                        GPS Coordinates Pin
                                    </span>
                                    <span className="font-mono text-xs font-bold text-emerald-700">
                                        📍 {form.data.lat.toFixed(5)}°,{' '}
                                        {form.data.lng.toFixed(5)}°
                                    </span>
                                </div>
                                <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">
                                    Pin Attached
                                </span>
                            </div>
                        )}
                    </div>

                    <div className="border-thh-border space-y-2 rounded-2xl border bg-white p-4">
                        <div className="flex items-center justify-between">
                            <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-amber-800 uppercase">
                                {form.data.urgency} Urgency
                            </span>
                        </div>
                        <p className="text-thh-text font-serif text-lg font-bold">
                            {form.data.title}
                        </p>
                        <p className="text-thh-text-muted text-xs leading-relaxed whitespace-pre-wrap">
                            {form.data.description}
                        </p>
                    </div>
                </div>
            )}

            {/* Navigation buttons */}
            <div className="mt-8 flex items-center gap-3">
                {step > 1 && (
                    <button
                        type="button"
                        onClick={() => setStep((s) => s - 1)}
                        className="border-thh-border text-thh-text hover:bg-thh-bg rounded-xl border px-4 py-2.5 text-xs font-semibold transition"
                    >
                        ← {t('app.common.back', 'Back')}
                    </button>
                )}
                <button
                    type="submit"
                    disabled={form.processing}
                    className="bg-thh-primary ml-auto rounded-xl px-6 py-2.5 text-xs font-bold text-white shadow-md transition hover:opacity-95 disabled:opacity-50"
                >
                    {form.processing ? (
                        'Submitting...'
                    ) : step < 3 ? (
                        <>{t('action.next', 'Continue')} →</>
                    ) : (
                        t('apply.submit', 'Submit Help Application')
                    )}
                </button>
            </div>
        </form>
    );
}
