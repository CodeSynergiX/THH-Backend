import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AdminLayout from '../../../components/AdminLayout';
import {
    MapPin,
    Building2,
    Home,
    Plus,
    Edit2,
    Search,
    X,
    Compass,
    Layers,
} from 'lucide-react';

interface Village {
    id: number;
    taluka_id: number;
    name_en: string;
    name_gu: string;
    pincode?: string | null;
    lat?: number | null;
    lng?: number | null;
    is_active: boolean;
}

interface Taluka {
    id: number;
    district_id: number;
    name_en: string;
    name_gu: string;
    code?: string | null;
    is_active: boolean;
    villages_count?: number;
}

interface District {
    id: number;
    name_en: string;
    name_gu: string;
    code: string;
    is_active: boolean;
    talukas: Taluka[];
}

interface LocationsIndexProps {
    districts: District[];
    activeDistrictId: number | null;
    activeTalukaId: number | null;
    villages: Village[];
    search: string;
    stats: {
        total_districts: number;
        total_talukas: number;
        total_villages: number;
        active_villages: number;
    };
}

export default function LocationsIndex({
    districts,
    activeDistrictId,
    activeTalukaId,
    villages,
    search: initialSearch,
    stats,
}: LocationsIndexProps) {
    const [search, setSearch] = useState(initialSearch || '');

    // Modals
    const [showDistrictModal, setShowDistrictModal] = useState(false);
    const [editingDistrict, setEditingDistrict] = useState<District | null>(
        null,
    );

    const [showTalukaModal, setShowTalukaModal] = useState(false);
    const [editingTaluka, setEditingTaluka] = useState<Taluka | null>(null);

    const [showVillageModal, setShowVillageModal] = useState(false);
    const [editingVillage, setEditingVillage] = useState<Village | null>(null);

    // Active records
    const selectedDistrict =
        districts.find((d) => d.id === activeDistrictId) ||
        districts[0] ||
        null;
    const availableTalukas = selectedDistrict?.talukas || [];
    const selectedTaluka =
        availableTalukas.find((t) => t.id === activeTalukaId) ||
        availableTalukas[0] ||
        null;

    // Forms
    const districtForm = useForm({
        name_en: '',
        name_gu: '',
        code: '',
        is_active: true,
    });

    const talukaForm = useForm({
        district_id: selectedDistrict?.id || '',
        name_en: '',
        name_gu: '',
        code: '',
        is_active: true,
    });

    const villageForm = useForm({
        taluka_id: selectedTaluka?.id || '',
        name_en: '',
        name_gu: '',
        pincode: '',
        lat: '',
        lng: '',
        is_active: true,
    });

    const handleSelectDistrict = (districtId: number) => {
        router.get(
            '/admin/locations',
            { district_id: districtId },
            { preserveState: true, replace: true },
        );
    };

    const handleSelectTaluka = (talukaId: number) => {
        router.get(
            '/admin/locations',
            {
                district_id: selectedDistrict?.id,
                taluka_id: talukaId,
                search: '',
            },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/admin/locations',
            {
                district_id: selectedDistrict?.id,
                taluka_id: selectedTaluka?.id,
                search,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleToggle = (
        type: 'district' | 'taluka' | 'village',
        id: number,
    ) => {
        router.post(
            `/admin/locations/toggle/${type}/${id}`,
            {},
            { preserveScroll: true },
        );
    };

    // Open District Modal
    const openDistrictCreate = () => {
        setEditingDistrict(null);
        districtForm.setData({
            name_en: '',
            name_gu: '',
            code: '',
            is_active: true,
        });
        setShowDistrictModal(true);
    };

    const openDistrictEdit = (d: District) => {
        setEditingDistrict(d);
        districtForm.setData({
            name_en: d.name_en,
            name_gu: d.name_gu,
            code: d.code,
            is_active: d.is_active,
        });
        setShowDistrictModal(true);
    };

    const handleDistrictSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingDistrict) {
            districtForm.put(
                `/admin/locations/district/${editingDistrict.id}`,
                {
                    onSuccess: () => setShowDistrictModal(false),
                },
            );
        } else {
            districtForm.post('/admin/locations/district', {
                onSuccess: () => setShowDistrictModal(false),
            });
        }
    };

    // Open Taluka Modal
    const openTalukaCreate = () => {
        if (!selectedDistrict) return;
        setEditingTaluka(null);
        talukaForm.setData({
            district_id: selectedDistrict.id,
            name_en: '',
            name_gu: '',
            code: '',
            is_active: true,
        });
        setShowTalukaModal(true);
    };

    const openTalukaEdit = (t: Taluka) => {
        setEditingTaluka(t);
        talukaForm.setData({
            district_id: t.district_id,
            name_en: t.name_en,
            name_gu: t.name_gu,
            code: t.code || '',
            is_active: t.is_active,
        });
        setShowTalukaModal(true);
    };

    const handleTalukaSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingTaluka) {
            talukaForm.put(`/admin/locations/taluka/${editingTaluka.id}`, {
                onSuccess: () => setShowTalukaModal(false),
            });
        } else {
            talukaForm.post('/admin/locations/taluka', {
                onSuccess: () => setShowTalukaModal(false),
            });
        }
    };

    // Open Village Modal
    const openVillageCreate = () => {
        if (!selectedTaluka) return;
        setEditingVillage(null);
        villageForm.setData({
            taluka_id: selectedTaluka.id,
            name_en: '',
            name_gu: '',
            pincode: '',
            lat: '',
            lng: '',
            is_active: true,
        });
        setShowVillageModal(true);
    };

    const openVillageEdit = (v: Village) => {
        setEditingVillage(v);
        villageForm.setData({
            taluka_id: v.taluka_id,
            name_en: v.name_en,
            name_gu: v.name_gu,
            pincode: v.pincode || '',
            lat: v.lat !== null && v.lat !== undefined ? String(v.lat) : '',
            lng: v.lng !== null && v.lng !== undefined ? String(v.lng) : '',
            is_active: v.is_active,
        });
        setShowVillageModal(true);
    };

    const handleVillageSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingVillage) {
            villageForm.put(`/admin/locations/village/${editingVillage.id}`, {
                onSuccess: () => setShowVillageModal(false),
            });
        } else {
            villageForm.post('/admin/locations/village', {
                onSuccess: () => setShowVillageModal(false),
            });
        }
    };

    return (
        <AdminLayout title="Locations & Geographic Master Data">
            <Head title="Locations & Jurisdictions" />
            <div className="space-y-6">
                {/* Header Banner */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                            <MapPin className="text-thh-primary h-6 w-6" />
                            <span>Geographic Master Data</span>
                        </h2>
                        <p className="text-thh-text-muted mt-1 text-xs">
                            Manage Districts, Talukas, and Villages / Cities
                            with GPS coordinates for accurate mobile app
                            location matching and volunteer dispatch.
                        </p>
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <div className="bg-thh-surface border-thh-border rounded-xl border px-3 py-2 text-center shadow-xs">
                            <span className="text-thh-text-muted block text-[10px] font-bold tracking-wider uppercase">
                                Districts
                            </span>
                            <span className="text-thh-primary text-base font-extrabold">
                                {stats.total_districts}
                            </span>
                        </div>
                        <div className="bg-thh-surface border-thh-border rounded-xl border px-3 py-2 text-center shadow-xs">
                            <span className="text-thh-text-muted block text-[10px] font-bold tracking-wider uppercase">
                                Talukas
                            </span>
                            <span className="text-thh-secondary text-base font-extrabold">
                                {stats.total_talukas}
                            </span>
                        </div>
                        <div className="bg-thh-surface border-thh-border rounded-xl border px-3 py-2 text-center shadow-xs">
                            <span className="text-thh-text-muted block text-[10px] font-bold tracking-wider uppercase">
                                Villages
                            </span>
                            <span className="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                                {stats.total_villages}
                            </span>
                        </div>
                    </div>
                </div>

                {/* 3-Column Hierarchy Master Explorer */}
                <div className="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">
                    {/* 1. Districts List (3 cols) */}
                    <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-xs lg:col-span-3">
                        <div className="border-thh-border bg-thh-bg/40 flex items-center justify-between border-b p-3.5">
                            <div className="flex items-center gap-2">
                                <Layers className="text-thh-primary h-4 w-4" />
                                <h3 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                    Districts
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={openDistrictCreate}
                                className="bg-thh-primary flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow-xs hover:opacity-90"
                            >
                                <Plus className="h-3.5 w-3.5" />
                                <span>Add</span>
                            </button>
                        </div>

                        <div className="divide-thh-border max-h-[600px] divide-y overflow-y-auto">
                            {districts.map((d) => {
                                const isSelected =
                                    selectedDistrict?.id === d.id;
                                return (
                                    <div
                                        key={d.id}
                                        onClick={() =>
                                            handleSelectDistrict(d.id)
                                        }
                                        className={`group flex cursor-pointer items-center justify-between p-3 transition-colors ${
                                            isSelected
                                                ? 'bg-thh-primary/10 border-thh-primary border-l-4'
                                                : 'hover:bg-thh-bg/50'
                                        }`}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className={`truncate text-xs font-bold ${isSelected ? 'text-thh-primary' : 'text-thh-text'}`}
                                                >
                                                    {d.name_en}
                                                </span>
                                                {!d.is_active && (
                                                    <span className="rounded bg-rose-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-rose-500">
                                                        Inactive
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-thh-text-muted mt-0.5 flex items-center gap-2 text-[11px]">
                                                <span>{d.name_gu}</span>
                                                <span>•</span>
                                                <span className="text-thh-primary font-mono text-[10px]">
                                                    {d.talukas.length} talukas
                                                </span>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                            <button
                                                type="button"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    openDistrictEdit(d);
                                                }}
                                                className="text-thh-text-muted hover:text-thh-text rounded p-1"
                                                title="Edit district"
                                            >
                                                <Edit2 className="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* 2. Talukas List (4 cols) */}
                    <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-xs lg:col-span-4">
                        <div className="border-thh-border bg-thh-bg/40 flex items-center justify-between border-b p-3.5">
                            <div className="flex items-center gap-2">
                                <Building2 className="text-thh-secondary h-4 w-4" />
                                <h3 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                    Talukas{' '}
                                    {selectedDistrict
                                        ? `in ${selectedDistrict.name_en}`
                                        : ''}
                                </h3>
                            </div>
                            {selectedDistrict && (
                                <button
                                    type="button"
                                    onClick={openTalukaCreate}
                                    className="bg-thh-secondary flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow-xs hover:opacity-90"
                                >
                                    <Plus className="h-3.5 w-3.5" />
                                    <span>Add</span>
                                </button>
                            )}
                        </div>

                        <div className="divide-thh-border max-h-[600px] divide-y overflow-y-auto">
                            {availableTalukas.length === 0 ? (
                                <div className="text-thh-text-muted p-8 text-center text-xs">
                                    No talukas configured for this district yet.
                                </div>
                            ) : (
                                availableTalukas.map((t) => {
                                    const isSelected =
                                        selectedTaluka?.id === t.id;
                                    return (
                                        <div
                                            key={t.id}
                                            onClick={() =>
                                                handleSelectTaluka(t.id)
                                            }
                                            className={`group flex cursor-pointer items-center justify-between p-3 transition-colors ${
                                                isSelected
                                                    ? 'bg-thh-secondary/10 border-thh-secondary border-l-4'
                                                    : 'hover:bg-thh-bg/50'
                                            }`}
                                        >
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <span
                                                        className={`truncate text-xs font-bold ${isSelected ? 'text-thh-secondary' : 'text-thh-text'}`}
                                                    >
                                                        {t.name_en}
                                                    </span>
                                                    {!t.is_active && (
                                                        <span className="rounded bg-rose-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-rose-500">
                                                            Inactive
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="text-thh-text-muted mt-0.5 flex items-center gap-2 text-[11px]">
                                                    <span>{t.name_gu}</span>
                                                    <span>•</span>
                                                    <span className="font-mono text-[10px] text-emerald-600 dark:text-emerald-400">
                                                        {t.villages_count ?? 0}{' '}
                                                        villages
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                                <button
                                                    type="button"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        openTalukaEdit(t);
                                                    }}
                                                    className="text-thh-text-muted hover:text-thh-text rounded p-1"
                                                    title="Edit taluka"
                                                >
                                                    <Edit2 className="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>

                    {/* 3. Villages / Cities List (5 cols) */}
                    <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-xs lg:col-span-5">
                        <div className="border-thh-border bg-thh-bg/40 flex items-center justify-between border-b p-3.5">
                            <div className="flex items-center gap-2">
                                <Home className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                <h3 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                    Villages / Cities{' '}
                                    {selectedTaluka
                                        ? `in ${selectedTaluka.name_en}`
                                        : ''}
                                </h3>
                            </div>
                            {selectedTaluka && (
                                <button
                                    type="button"
                                    onClick={openVillageCreate}
                                    className="flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow-xs hover:bg-emerald-700"
                                >
                                    <Plus className="h-3.5 w-3.5" />
                                    <span>Add Village</span>
                                </button>
                            )}
                        </div>

                        {/* Search Filter in Villages */}
                        <div className="border-thh-border bg-thh-bg/20 border-b p-3">
                            <form
                                onSubmit={handleSearchSubmit}
                                className="relative"
                            >
                                <Search className="text-thh-text-muted absolute top-2.5 left-3 h-3.5 w-3.5" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search village name or pincode..."
                                    className="border-thh-border bg-thh-surface text-thh-text focus:border-thh-primary w-full rounded-lg border py-1.5 pr-3 pl-8 text-xs outline-none"
                                />
                            </form>
                        </div>

                        <div className="divide-thh-border max-h-[540px] divide-y overflow-y-auto">
                            {villages.length === 0 ? (
                                <div className="text-thh-text-muted p-8 text-center text-xs">
                                    {search
                                        ? 'No villages match your search query.'
                                        : 'No villages registered for this taluka yet.'}
                                </div>
                            ) : (
                                villages.map((v) => (
                                    <div
                                        key={v.id}
                                        className="hover:bg-thh-bg/40 group flex items-center justify-between p-3 transition-colors"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <span className="text-thh-text truncate text-xs font-bold">
                                                    {v.name_en}
                                                </span>
                                                <span className="text-thh-text-muted text-xs">
                                                    ({v.name_gu})
                                                </span>
                                                {v.pincode && (
                                                    <span className="bg-thh-bg text-thh-text-muted border-thh-border rounded border px-1.5 py-0.5 font-mono text-[10px]">
                                                        PIN: {v.pincode}
                                                    </span>
                                                )}
                                                {!v.is_active && (
                                                    <span className="rounded bg-rose-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-rose-500">
                                                        Inactive
                                                    </span>
                                                )}
                                            </div>

                                            <div className="text-thh-text-muted mt-1 flex items-center gap-2 text-[11px]">
                                                {v.lat !== null &&
                                                v.lng !== null ? (
                                                    <span className="flex items-center gap-1 font-mono text-[10px] text-emerald-600 dark:text-emerald-400">
                                                        <Compass className="h-3 w-3" />
                                                        {Number(v.lat).toFixed(
                                                            4,
                                                        )}
                                                        ,{' '}
                                                        {Number(v.lng).toFixed(
                                                            4,
                                                        )}
                                                    </span>
                                                ) : (
                                                    <span className="text-[10px] text-amber-500">
                                                        GPS coordinates missing
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    handleToggle(
                                                        'village',
                                                        v.id,
                                                    )
                                                }
                                                className={`rounded border px-2 py-0.5 text-[10px] font-semibold transition-colors ${
                                                    v.is_active
                                                        ? 'border-emerald-500/30 text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30'
                                                        : 'border-rose-500/30 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30'
                                                }`}
                                            >
                                                {v.is_active
                                                    ? 'Active'
                                                    : 'Disabled'}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    openVillageEdit(v)
                                                }
                                                className="text-thh-text-muted hover:text-thh-text hover:bg-thh-bg rounded p-1.5"
                                                title="Edit village"
                                            >
                                                <Edit2 className="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal: District */}
            {showDistrictModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleDistrictSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text text-base font-bold">
                                {editingDistrict
                                    ? 'Edit District'
                                    : 'Add New District'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowDistrictModal(false)}
                                className="text-thh-text-muted hover:text-thh-text p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Name (English)
                                </label>
                                <input
                                    type="text"
                                    value={districtForm.data.name_en}
                                    onChange={(e) =>
                                        districtForm.setData(
                                            'name_en',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="e.g. Navsari"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Name (Gujarati)
                                </label>
                                <input
                                    type="text"
                                    value={districtForm.data.name_gu}
                                    onChange={(e) =>
                                        districtForm.setData(
                                            'name_gu',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="e.g. નવસારી"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    District Code
                                </label>
                                <input
                                    type="text"
                                    value={districtForm.data.code}
                                    onChange={(e) =>
                                        districtForm.setData(
                                            'code',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="e.g. NAVSARI"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono uppercase"
                                />
                            </div>
                            <div className="flex items-center gap-2 pt-1">
                                <input
                                    type="checkbox"
                                    id="dist_active"
                                    checked={districtForm.data.is_active}
                                    onChange={(e) =>
                                        districtForm.setData(
                                            'is_active',
                                            e.target.checked,
                                        )
                                    }
                                    className="border-thh-border text-thh-primary rounded"
                                />
                                <label
                                    htmlFor="dist_active"
                                    className="text-thh-text font-bold"
                                >
                                    Active / Enabled in Mobile App
                                </label>
                            </div>
                        </div>

                        <div className="border-thh-border flex justify-end gap-3 border-t pt-3">
                            <button
                                type="button"
                                onClick={() => setShowDistrictModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={districtForm.processing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                            >
                                {districtForm.processing
                                    ? 'Saving...'
                                    : 'Save District'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Modal: Taluka */}
            {showTalukaModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleTalukaSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text text-base font-bold">
                                {editingTaluka
                                    ? 'Edit Taluka'
                                    : `Add Taluka to ${selectedDistrict?.name_en}`}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowTalukaModal(false)}
                                className="text-thh-text-muted hover:text-thh-text p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Taluka Name (English)
                                </label>
                                <input
                                    type="text"
                                    value={talukaForm.data.name_en}
                                    onChange={(e) =>
                                        talukaForm.setData(
                                            'name_en',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="e.g. Chikhli Cluster"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Taluka Name (Gujarati)
                                </label>
                                <input
                                    type="text"
                                    value={talukaForm.data.name_gu}
                                    onChange={(e) =>
                                        talukaForm.setData(
                                            'name_gu',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="e.g. ચીખલી વિસ્તાર"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Taluka Code (Optional)
                                </label>
                                <input
                                    type="text"
                                    value={talukaForm.data.code}
                                    onChange={(e) =>
                                        talukaForm.setData(
                                            'code',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="e.g. CHIKHLI"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono uppercase"
                                />
                            </div>
                            <div className="flex items-center gap-2 pt-1">
                                <input
                                    type="checkbox"
                                    id="taluka_active"
                                    checked={talukaForm.data.is_active}
                                    onChange={(e) =>
                                        talukaForm.setData(
                                            'is_active',
                                            e.target.checked,
                                        )
                                    }
                                    className="border-thh-border text-thh-secondary rounded"
                                />
                                <label
                                    htmlFor="taluka_active"
                                    className="text-thh-text font-bold"
                                >
                                    Active / Enabled in Mobile App
                                </label>
                            </div>
                        </div>

                        <div className="border-thh-border flex justify-end gap-3 border-t pt-3">
                            <button
                                type="button"
                                onClick={() => setShowTalukaModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={talukaForm.processing}
                                className="bg-thh-secondary rounded-lg px-4 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                            >
                                {talukaForm.processing
                                    ? 'Saving...'
                                    : 'Save Taluka'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Modal: Village / City */}
            {showVillageModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleVillageSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text text-base font-bold">
                                {editingVillage
                                    ? 'Edit Village / City'
                                    : `Add Village to ${selectedTaluka?.name_en}`}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowVillageModal(false)}
                                className="text-thh-text-muted hover:text-thh-text p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Name (English)
                                    </label>
                                    <input
                                        type="text"
                                        value={villageForm.data.name_en}
                                        onChange={(e) =>
                                            villageForm.setData(
                                                'name_en',
                                                e.target.value,
                                            )
                                        }
                                        required
                                        placeholder="e.g. Alipore"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Name (Gujarati)
                                    </label>
                                    <input
                                        type="text"
                                        value={villageForm.data.name_gu}
                                        onChange={(e) =>
                                            villageForm.setData(
                                                'name_gu',
                                                e.target.value,
                                            )
                                        }
                                        required
                                        placeholder="e.g. અલીપોર"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Postal PIN Code
                                </label>
                                <input
                                    type="text"
                                    value={villageForm.data.pincode}
                                    onChange={(e) =>
                                        villageForm.setData(
                                            'pincode',
                                            e.target.value,
                                        )
                                    }
                                    maxLength={6}
                                    placeholder="e.g. 396409"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Latitude (GPS)
                                    </label>
                                    <input
                                        type="number"
                                        step="any"
                                        value={villageForm.data.lat}
                                        onChange={(e) =>
                                            villageForm.setData(
                                                'lat',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. 20.7656"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Longitude (GPS)
                                    </label>
                                    <input
                                        type="number"
                                        step="any"
                                        value={villageForm.data.lng}
                                        onChange={(e) =>
                                            villageForm.setData(
                                                'lng',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. 72.9961"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-mono"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-1">
                                <input
                                    type="checkbox"
                                    id="village_active"
                                    checked={villageForm.data.is_active}
                                    onChange={(e) =>
                                        villageForm.setData(
                                            'is_active',
                                            e.target.checked,
                                        )
                                    }
                                    className="border-thh-border rounded text-emerald-600"
                                />
                                <label
                                    htmlFor="village_active"
                                    className="text-thh-text font-bold"
                                >
                                    Active / Enabled for Citizen Selection & GPS
                                    Matching
                                </label>
                            </div>
                        </div>

                        <div className="border-thh-border flex justify-end gap-3 border-t pt-3">
                            <button
                                type="button"
                                onClick={() => setShowVillageModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={villageForm.processing}
                                className="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700"
                            >
                                {villageForm.processing
                                    ? 'Saving...'
                                    : 'Save Village'}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
