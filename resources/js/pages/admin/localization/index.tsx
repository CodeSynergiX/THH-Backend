import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle,
    FileCode,
    FileSpreadsheet,
    Globe,
    Plus,
    Save,
    Scan,
    Search,
    X,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

interface LanguageItem {
    id: number;
    code: string;
    name: string;
    native_name: string;
    is_default: boolean;
    is_enabled: boolean;
}

interface TranslationItem {
    id: number;
    group: string;
    key: string;
    locale: string;
    value: string;
    needs_review: boolean;
}

interface LocalizationIndexProps {
    languages: LanguageItem[];
    groups: string[];
    translations: TranslationItem[];
    selectedLocale: string;
    selectedGroup: string;
    search: string;
    stats: Record<string, { total: number; percent: number }>;
}

export default function LocalizationIndex({
    languages,
    groups,
    translations,
    selectedLocale,
    selectedGroup,
    search: initialSearch,
    stats,
}: LocalizationIndexProps) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(initialSearch || '');
    const [editingValues, setEditingValues] = useState<Record<number, string>>(
        {},
    );
    const [savingId, setSavingId] = useState<number | null>(null);

    // Missing Keys Scanner Modal State
    const [isScanning, setIsScanning] = useState(false);
    const [missingKeys, setMissingKeys] = useState<
        Array<{ group: string; key: string; missing_in: string }>
    >([]);
    const [showMissingModal, setShowMissingModal] = useState(false);

    // Add New Key Modal State
    const [showAddModal, setShowAddModal] = useState(false);
    const [newGroup, setNewGroup] = useState('app');
    const [newKey, setNewKey] = useState('');
    const [newValGu, setNewValGu] = useState('');
    const [newValEn, setNewValEn] = useState('');

    const handleFilter = (locale: string, group: string, q: string) => {
        router.get(
            '/admin/localization',
            { locale, group, search: q },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter(selectedLocale, selectedGroup, search);
    };

    const handleInlineChange = (id: number, val: string) => {
        setEditingValues((prev) => ({ ...prev, [id]: val }));
    };

    const handleSaveRow = (item: TranslationItem) => {
        const newValue =
            editingValues[item.id] !== undefined
                ? editingValues[item.id]
                : item.value;
        setSavingId(item.id);

        router.post(
            '/admin/localization',
            {
                group: item.group,
                key: item.key,
                locale: item.locale,
                value: newValue,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSavingId(null);
                    setEditingValues((prev) => {
                        const copy = { ...prev };
                        delete copy[item.id];
                        return copy;
                    });
                },
                onError: () => setSavingId(null),
            },
        );
    };

    const handleScanMissing = async () => {
        setIsScanning(true);
        try {
            const res = await fetch('/admin/localization/scan-missing');
            const data = await res.json();
            setMissingKeys(data.missing || []);
            setShowMissingModal(true);
        } catch {
            alert('Failed to scan missing keys');
        } finally {
            setIsScanning(false);
        }
    };

    const handleCreateKey = (e: React.FormEvent) => {
        e.preventDefault();
        if (!newGroup || !newKey) return;

        // Save Gujarati
        router.post(
            '/admin/localization',
            { group: newGroup, key: newKey, locale: 'gu', value: newValGu },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Save English
                    router.post(
                        '/admin/localization',
                        {
                            group: newGroup,
                            key: newKey,
                            locale: 'en',
                            value: newValEn,
                        },
                        {
                            preserveScroll: true,
                            onSuccess: () => {
                                setShowAddModal(false);
                                setNewKey('');
                                setNewValGu('');
                                setNewValEn('');
                            },
                        },
                    );
                },
            },
        );
    };

    return (
        <AdminLayout
            title={t(
                'localization.editor.title',
                'Localization & Translations',
            )}
        >
            <div className="space-y-8">
                {/* Header & Actions */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                            <Globe className="text-thh-secondary h-6 w-6" />
                            <span>
                                {t(
                                    'localization.editor.title',
                                    'Language & Translation Manager',
                                )}
                            </span>
                        </h2>
                        <p className="text-thh-text-muted mt-1 text-sm">
                            {t(
                                'localization.editor.subtitle',
                                'Database-driven management for Gujarati and English translations',
                            )}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            onClick={handleScanMissing}
                            disabled={isScanning}
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium shadow-2xs transition-colors"
                        >
                            <Scan className="text-thh-accent h-3.5 w-3.5" />
                            <span>
                                {isScanning
                                    ? 'Scanning...'
                                    : t(
                                          'localization.editor.scan_missing_button',
                                          'Scan Missing Keys',
                                      )}
                            </span>
                        </button>

                        <a
                            href={`/admin/localization/export/json/${selectedLocale}`}
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium shadow-2xs transition-colors"
                        >
                            <FileCode className="text-thh-secondary h-3.5 w-3.5" />
                            <span>
                                {t(
                                    'localization.editor.export_json_button',
                                    'Export JSON',
                                )}
                            </span>
                        </a>

                        <a
                            href="/admin/localization/export/csv"
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium shadow-2xs transition-colors"
                        >
                            <FileSpreadsheet className="h-3.5 w-3.5 text-emerald-600" />
                            <span>
                                {t(
                                    'localization.editor.export_csv_button',
                                    'Export CSV',
                                )}
                            </span>
                        </a>

                        <button
                            type="button"
                            onClick={() => setShowAddModal(true)}
                            className="bg-thh-primary inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition-all hover:opacity-95"
                        >
                            <Plus className="h-3.5 w-3.5" />
                            <span>Add Translation Key</span>
                        </button>
                    </div>
                </div>

                {/* Language Completion Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {languages.map((lang) => {
                        const stat = stats[lang.code] || {
                            total: 0,
                            percent: 0,
                        };
                        return (
                            <div
                                key={lang.code}
                                onClick={() =>
                                    handleFilter(
                                        lang.code,
                                        selectedGroup,
                                        search,
                                    )
                                }
                                className={`cursor-pointer rounded-xl border p-4.5 transition-all ${
                                    selectedLocale === lang.code
                                        ? 'bg-thh-surface border-thh-primary ring-thh-primary shadow-xs ring-1'
                                        : 'bg-thh-surface border-thh-border hover:border-thh-text-muted shadow-2xs'
                                }`}
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2.5">
                                        <span className="text-thh-text text-base font-bold">
                                            {lang.native_name}
                                        </span>
                                        <span className="text-thh-text-muted text-xs">
                                            ({lang.name} - {lang.code})
                                        </span>
                                        {lang.is_default && (
                                            <span className="bg-thh-primary/10 text-thh-primary rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase">
                                                Default
                                            </span>
                                        )}
                                    </div>
                                    <span className="text-thh-text text-sm font-extrabold">
                                        {stat.percent}%
                                    </span>
                                </div>

                                <div className="bg-thh-bg border-thh-border mt-3 h-2 w-full overflow-hidden rounded-full border">
                                    <div
                                        className="bg-thh-primary h-2 rounded-full transition-all duration-500"
                                        style={{ width: `${stat.percent}%` }}
                                    />
                                </div>
                                <span className="text-thh-text-muted mt-2 block text-[11px]">
                                    {stat.total} translated strings in active
                                    catalog
                                </span>
                            </div>
                        );
                    })}
                </div>

                {/* Filter and Search Bar */}
                <div className="bg-thh-surface border-thh-border flex flex-col items-center gap-4 rounded-xl border p-4 shadow-2xs md:flex-row">
                    {/* Group Pills */}
                    <div className="flex w-full items-center gap-1.5 overflow-x-auto pb-1 md:w-auto md:pb-0">
                        <button
                            type="button"
                            onClick={() =>
                                handleFilter(selectedLocale, 'all', search)
                            }
                            className={`rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition-all ${
                                selectedGroup === 'all'
                                    ? 'bg-thh-primary text-white shadow-xs'
                                    : 'text-thh-text-muted hover:text-thh-text hover:bg-thh-bg'
                            }`}
                        >
                            {t('localization.editor.group_all', 'All Groups')}
                        </button>
                        {groups.map((grp) => (
                            <button
                                key={grp}
                                type="button"
                                onClick={() =>
                                    handleFilter(selectedLocale, grp, search)
                                }
                                className={`rounded-lg px-3 py-1.5 text-xs font-semibold tracking-wider whitespace-nowrap uppercase transition-all ${
                                    selectedGroup === grp
                                        ? 'bg-thh-primary text-white shadow-xs'
                                        : 'text-thh-text-muted hover:text-thh-text hover:bg-thh-bg'
                                }`}
                            >
                                {grp}
                            </button>
                        ))}
                    </div>

                    {/* Search Input */}
                    <form
                        onSubmit={handleSearchSubmit}
                        className="flex w-full flex-1 items-center gap-2 md:w-auto"
                    >
                        <div className="relative flex-1">
                            <Search className="text-thh-text-muted absolute top-2.5 left-3 h-4 w-4" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder={t(
                                    'localization.editor.search_placeholder',
                                    'Search by key or text...',
                                )}
                                className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-lg border py-2 pr-4 pl-9 text-xs focus:ring-1 focus:outline-hidden"
                            />
                        </div>
                        <button
                            type="submit"
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-lg border px-3 py-2 text-xs font-semibold transition-colors"
                        >
                            Search
                        </button>
                    </form>
                </div>

                {/* Translation Table */}
                <div className="bg-thh-surface border-thh-border overflow-hidden rounded-xl border shadow-2xs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-thh-bg border-thh-border text-thh-text border-b text-[11px] font-bold tracking-wider uppercase">
                                <tr>
                                    <th className="w-32 px-4 py-3.5">Group</th>
                                    <th className="w-72 px-4 py-3.5">
                                        Key Name
                                    </th>
                                    <th className="w-20 px-4 py-3.5">Locale</th>
                                    <th className="px-4 py-3.5">
                                        Translation Value
                                    </th>
                                    <th className="w-24 px-4 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-thh-border divide-y">
                                {translations.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="text-thh-text-muted py-12 text-center text-sm"
                                        >
                                            {t(
                                                'app.portal.empty_records',
                                                'No translation records found matching query.',
                                            )}
                                        </td>
                                    </tr>
                                ) : (
                                    translations.map((item) => {
                                        const currentValue =
                                            editingValues[item.id] !== undefined
                                                ? editingValues[item.id]
                                                : item.value;
                                        const isModified =
                                            editingValues[item.id] !==
                                            undefined;

                                        return (
                                            <tr
                                                key={item.id}
                                                className="hover:bg-thh-bg/40 transition-colors"
                                            >
                                                <td className="text-thh-accent px-4 py-3 font-mono font-bold">
                                                    {item.group}
                                                </td>
                                                <td className="text-thh-text px-4 py-3 font-mono font-medium">
                                                    {item.key}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="bg-thh-bg border-thh-border text-thh-text rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase">
                                                        {item.locale}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-2.5">
                                                    <input
                                                        type="text"
                                                        value={currentValue}
                                                        onChange={(e) =>
                                                            handleInlineChange(
                                                                item.id,
                                                                e.target.value,
                                                            )
                                                        }
                                                        className={`text-thh-text bg-thh-bg focus:ring-thh-primary w-full rounded-lg border px-3 py-1.5 text-xs transition-colors focus:ring-1 focus:outline-hidden ${
                                                            isModified
                                                                ? 'border-thh-accent ring-thh-accent/30 ring-1'
                                                                : 'border-thh-border'
                                                        }`}
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleSaveRow(item)
                                                        }
                                                        disabled={
                                                            savingId === item.id
                                                        }
                                                        className={`inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold transition-all ${
                                                            isModified
                                                                ? 'bg-thh-primary text-white shadow-2xs hover:opacity-90'
                                                                : 'text-thh-text-muted hover:text-thh-text hover:bg-thh-bg border-thh-border border'
                                                        }`}
                                                    >
                                                        <Save className="h-3 w-3" />
                                                        <span>
                                                            {savingId ===
                                                            item.id
                                                                ? '...'
                                                                : 'Save'}
                                                        </span>
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Missing Keys Modal */}
            {showMissingModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <div className="bg-thh-surface border-thh-border w-full max-w-xl space-y-4 rounded-2xl border p-6 shadow-xl">
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text flex items-center gap-2 text-base font-bold">
                                <AlertCircle className="h-5 w-5 text-amber-500" />
                                <span>
                                    Missing Translation Keys (
                                    {missingKeys.length})
                                </span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowMissingModal(false)}
                                className="text-thh-text-muted hover:text-thh-text rounded-lg p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        {missingKeys.length === 0 ? (
                            <div className="flex flex-col items-center gap-2 py-8 text-center text-sm font-semibold text-emerald-600">
                                <CheckCircle className="h-8 w-8" />
                                <span>
                                    All keys are 100% translated across Gujarati
                                    and English!
                                </span>
                            </div>
                        ) : (
                            <div className="max-h-80 space-y-2 overflow-y-auto">
                                {missingKeys.map((m, idx) => (
                                    <div
                                        key={idx}
                                        className="bg-thh-bg border-thh-border flex items-center justify-between rounded-lg border p-3 text-xs"
                                    >
                                        <div>
                                            <span className="text-thh-accent font-bold">
                                                {m.group}.
                                            </span>
                                            <span className="text-thh-text font-mono">
                                                {m.key}
                                            </span>
                                        </div>
                                        <span className="rounded-full border border-rose-500/20 bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 uppercase">
                                            Missing in {m.missing_in}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}

                        <div className="flex justify-end pt-2">
                            <button
                                type="button"
                                onClick={() => setShowMissingModal(false)}
                                className="border-thh-border bg-thh-bg text-thh-text hover:bg-thh-surface rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Add Translation Key Modal */}
            {showAddModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleCreateKey}
                        className="bg-thh-surface border-thh-border w-full max-w-lg space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text text-base font-bold">
                                Add New Translation Key
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowAddModal(false)}
                                className="text-thh-text-muted hover:text-thh-text rounded-lg p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Group
                                </label>
                                <select
                                    value={newGroup}
                                    onChange={(e) =>
                                        setNewGroup(e.target.value)
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                >
                                    {[
                                        'app',
                                        'auth',
                                        'cases',
                                        'theme',
                                        'localization',
                                        'content',
                                        'notifications',
                                    ].map((g) => (
                                        <option key={g} value={g}>
                                            {g}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Key Name (
                                    {'{subgroup}.{element}_{modifier}'})
                                </label>
                                <input
                                    type="text"
                                    placeholder="e.g. form.submit_button"
                                    value={newKey}
                                    onChange={(e) => setNewKey(e.target.value)}
                                    required
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono"
                                />
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Gujarati Value (ગુજરાતી)
                                </label>
                                <input
                                    type="text"
                                    placeholder="દા.ત. સબમિટ કરો"
                                    value={newValGu}
                                    onChange={(e) =>
                                        setNewValGu(e.target.value)
                                    }
                                    required
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    English Value
                                </label>
                                <input
                                    type="text"
                                    placeholder="e.g. Submit Now"
                                    value={newValEn}
                                    onChange={(e) =>
                                        setNewValEn(e.target.value)
                                    }
                                    required
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 pt-3">
                            <button
                                type="button"
                                onClick={() => setShowAddModal(false)}
                                className="border-thh-border text-thh-text hover:bg-thh-bg rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-semibold text-white shadow-xs hover:opacity-95"
                            >
                                Create Key
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
