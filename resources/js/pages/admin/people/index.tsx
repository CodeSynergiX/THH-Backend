import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Check, Plus, Search, Users, X, Edit2 } from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

interface PersonItem {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    email: string;
    phone?: string;
    is_active: boolean;
    helper_status?: string | null;
    gender?: string | null;
    date_of_birth?: string | null;
    blood_group?: string | null;
    district_id?: number | null;
    taluka_id?: number | null;
    village_id?: number | null;
    roles: Array<{ id: number; name: string }>;
    district?: { id?: number; name_en: string };
    taluka?: { id?: number; name_en: string };
    village?: { id?: number; name_en: string };
    applications_count?: number;
    assigned_applications_count?: number;
}

interface PaginatedPeople {
    data: PersonItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface DistrictItem {
    id: number;
    name_en: string;
    name_gu: string;
    talukas?: Array<{
        id: number;
        name_en: string;
        villages?: Array<{ id: number; name_en: string }>;
    }>;
}

interface PeopleIndexProps {
    people: PaginatedPeople;
    selectedRole: string;
    search: string;
    districtId: string;
    rolesWithPermissions: Array<{
        id: number;
        name: string;
        permissions: Array<{ id: number; name: string }>;
    }>;
    allPermissions: Array<{ id: number; name: string }>;
    districts: DistrictItem[];
}

export default function PeopleIndex({
    people,
    selectedRole: initialRole,
    search: initialSearch,
    districtId: initialDistrict,
    rolesWithPermissions,
    allPermissions,
    districts,
}: PeopleIndexProps) {
    const { t } = useTranslation();
    const [selectedRole, setSelectedRole] = useState(initialRole || 'staff');
    const [search, setSearch] = useState(initialSearch || '');
    const [activeView, setActiveView] = useState<'directory' | 'matrix'>(
        'directory',
    );

    // Add Member Modal
    const [showAddModal, setShowAddModal] = useState(false);
    const {
        data: addData,
        setData: setAddData,
        post: postAdd,
        processing: addProcessing,
        reset: resetAdd,
    } = useForm({
        name: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role: 'staff',
        district_id: '',
        taluka_id: '',
        village_id: '',
        password: '',
        gender: '',
        date_of_birth: '',
        blood_group: '',
    });

    // Edit Member Modal
    const [showEditModal, setShowEditModal] = useState(false);
    const [editingPerson, setEditingPerson] = useState<PersonItem | null>(null);
    const {
        data: editData,
        setData: setEditData,
        put: putEdit,
        processing: editProcessing,
        reset: resetEdit,
    } = useForm({
        first_name: '',
        last_name: '',
        role: 'staff',
        district_id: '',
        taluka_id: '',
        village_id: '',
        gender: '',
        date_of_birth: '',
        blood_group: '',
    });

    const openEditModal = (p: PersonItem) => {
        setEditingPerson(p);
        setEditData({
            first_name: p.first_name || '',
            last_name: p.last_name || '',
            role: p.roles?.[0]?.name || 'staff',
            district_id: p.district_id
                ? String(p.district_id)
                : p.district?.id
                  ? String(p.district.id)
                  : '',
            taluka_id: p.taluka_id
                ? String(p.taluka_id)
                : p.taluka?.id
                  ? String(p.taluka.id)
                  : '',
            village_id: p.village_id
                ? String(p.village_id)
                : p.village?.id
                  ? String(p.village.id)
                  : '',
            gender: p.gender || '',
            date_of_birth: p.date_of_birth || '',
            blood_group: p.blood_group || '',
        });
        setShowEditModal(true);
    };

    const handleUpdateMember = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingPerson) return;
        putEdit(`/admin/people/${editingPerson.id}`, {
            onSuccess: () => {
                setShowEditModal(false);
                resetEdit();
            },
        });
    };

    const handleFilter = (newParams: {
        role?: string;
        search?: string;
        district_id?: string;
    }) => {
        router.get(
            '/admin/people',
            {
                role: selectedRole,
                search,
                district_id: initialDistrict,
                ...newParams,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter({ search });
    };

    const handleHelperStatus = (id: number, helper_status: string) => {
        router.post(
            `/admin/people/${id}/helper-status`,
            { helper_status },
            { preserveScroll: true },
        );
    };

    const handleToggleActive = (id: number) => {
        router.post(
            `/admin/people/${id}/toggle-active`,
            {},
            { preserveScroll: true },
        );
    };

    const handleCreateMember = (e: React.FormEvent) => {
        e.preventDefault();
        postAdd('/admin/people', {
            onSuccess: () => {
                setShowAddModal(false);
                resetAdd();
            },
        });
    };

    const rolesList = [
        { key: 'admin', label: 'Administrators' },
        { key: 'citizen', label: 'Citizens' },
        { key: 'mentor', label: 'Mentors' },
        { key: 'staff', label: 'Field Staff' },
        { key: 'partner', label: 'Partners' },
        { key: 'volunteer', label: 'Volunteers' },
    ];

    return (
        <AdminLayout title="People & Geographic Scoping">
            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                            <Users className="text-thh-secondary h-6 w-6" />
                            <span>People & Roles Directory</span>
                        </h2>
                        <p className="text-thh-text-muted mt-1 text-xs">
                            Manage staff coordinators, field scope, mentors,
                            citizens, and permission boundaries.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <div className="bg-thh-surface border-thh-border flex items-center rounded-xl border p-1">
                            <button
                                type="button"
                                onClick={() => setActiveView('directory')}
                                className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    activeView === 'directory'
                                        ? 'bg-thh-primary text-white shadow-2xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                Directory
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveView('matrix')}
                                className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    activeView === 'matrix'
                                        ? 'bg-thh-primary text-white shadow-2xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                Roles & Permissions
                            </button>
                        </div>

                        <button
                            type="button"
                            onClick={() => setShowAddModal(true)}
                            className="bg-thh-primary inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                        >
                            <Plus className="h-3.5 w-3.5" />
                            <span>Add Team Member</span>
                        </button>
                    </div>
                </div>

                {activeView === 'directory' && (
                    <div className="space-y-6">
                        {/* Role Selector Tabs */}
                        <div className="flex items-center gap-2 overflow-x-auto pb-1">
                            {rolesList.map((r) => (
                                <button
                                    key={r.key}
                                    type="button"
                                    onClick={() => {
                                        setSelectedRole(r.key);
                                        handleFilter({ role: r.key });
                                    }}
                                    className={`rounded-xl border px-3.5 py-2 text-xs font-semibold whitespace-nowrap transition-all ${
                                        selectedRole === r.key
                                            ? 'bg-thh-surface border-thh-primary text-thh-primary ring-thh-primary shadow-2xs ring-1'
                                            : 'bg-thh-surface border-thh-border text-thh-text-muted hover:text-thh-text'
                                    }`}
                                >
                                    {r.label}
                                </button>
                            ))}
                        </div>

                        {/* Search and Filters */}
                        <form
                            onSubmit={handleSearchSubmit}
                            className="bg-thh-surface border-thh-border flex items-center gap-4 rounded-2xl border p-4 shadow-2xs"
                        >
                            <div className="relative flex-1">
                                <Search className="text-thh-text-muted absolute top-3 left-3.5 h-4 w-4" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by Name, Mobile number or Email..."
                                    className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-xl border py-2 pr-4 pl-10 text-xs focus:ring-1 focus:outline-hidden"
                                />
                            </div>
                            <button
                                type="submit"
                                className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-xl border px-4 py-2 text-xs font-semibold transition-colors"
                            >
                                Search
                            </button>
                        </form>

                        {/* People Table */}
                        <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-2xs">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-thh-bg border-thh-border text-thh-text border-b text-[11px] font-bold tracking-wider uppercase">
                                        <tr>
                                            <th className="px-4 py-3.5">
                                                Name
                                            </th>
                                            <th className="w-32 px-4 py-3.5">
                                                Mobile
                                            </th>
                                            <th className="w-48 px-4 py-3.5">
                                                Email
                                            </th>
                                            <th className="w-36 px-4 py-3.5">
                                                Geographic Scope
                                            </th>
                                            <th className="w-28 px-4 py-3.5">
                                                Active Cases
                                            </th>
                                            <th className="w-24 px-4 py-3.5">
                                                Status
                                            </th>
                                            <th className="w-36 px-4 py-3.5">
                                                Sevak
                                            </th>
                                            <th className="w-20 px-4 py-3.5 text-right">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-thh-border divide-y">
                                        {people.data.length === 0 ? (
                                            <tr>
                                                <td
                                                    colSpan={8}
                                                    className="text-thh-text-muted py-12 text-center text-sm"
                                                >
                                                    {t(
                                                        'app.portal.empty_records',
                                                        'No records found for selected filter.',
                                                    )}
                                                </td>
                                            </tr>
                                        ) : (
                                            people.data.map((p) => (
                                                <tr
                                                    key={p.id}
                                                    className="hover:bg-thh-bg/40 transition-colors"
                                                >
                                                    <td className="text-thh-text px-4 py-3.5 font-bold">
                                                        {p.name}
                                                    </td>
                                                    <td className="text-thh-text-muted px-4 py-3.5 font-mono">
                                                        {p.phone || '-'}
                                                    </td>
                                                    <td className="text-thh-text-muted px-4 py-3.5">
                                                        {p.email}
                                                    </td>
                                                    <td className="text-thh-text px-4 py-3.5 text-xs">
                                                        {p.village?.name_en ? (
                                                            <div>
                                                                <span className="block font-semibold">
                                                                    {
                                                                        p
                                                                            .village
                                                                            .name_en
                                                                    }
                                                                </span>
                                                                <span className="text-thh-text-muted text-[11px]">
                                                                    {p.taluka
                                                                        ?.name_en ||
                                                                        ''}{' '}
                                                                    •{' '}
                                                                    {p.district
                                                                        ?.name_en ||
                                                                        ''}
                                                                </span>
                                                            </div>
                                                        ) : p.taluka
                                                              ?.name_en ? (
                                                            <div>
                                                                <span className="block font-semibold">
                                                                    {
                                                                        p.taluka
                                                                            .name_en
                                                                    }
                                                                </span>
                                                                <span className="text-thh-text-muted text-[11px]">
                                                                    {p.district
                                                                        ?.name_en ||
                                                                        ''}
                                                                </span>
                                                            </div>
                                                        ) : p.district
                                                              ?.name_en ? (
                                                            <span className="font-semibold">
                                                                {
                                                                    p.district
                                                                        .name_en
                                                                }
                                                            </span>
                                                        ) : (
                                                            <span className="text-thh-text-muted italic">
                                                                Statewide / All
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="text-thh-primary px-4 py-3.5 font-mono font-bold">
                                                        {p.assigned_applications_count ||
                                                            p.applications_count ||
                                                            0}
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span
                                                            className={`rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                                p.is_active
                                                                    ? 'bg-emerald-500/10 text-emerald-600'
                                                                    : 'bg-rose-500/10 text-rose-600'
                                                            }`}
                                                        >
                                                            {p.is_active
                                                                ? 'Active'
                                                                : 'Disabled'}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span
                                                            className={`rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                                p.helper_status ===
                                                                'approved'
                                                                    ? 'bg-emerald-500/10 text-emerald-700'
                                                                    : p.helper_status ===
                                                                        'rejected'
                                                                      ? 'bg-rose-500/10 text-rose-600'
                                                                      : p.helper_status ===
                                                                          'pending'
                                                                        ? 'bg-amber-500/10 text-amber-700'
                                                                        : 'text-thh-text-muted'
                                                            }`}
                                                        >
                                                            {p.helper_status ||
                                                                '—'}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5 text-right">
                                                        <div className="flex flex-col items-end gap-1">
                                                            <div className="flex items-center gap-2">
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        openEditModal(
                                                                            p,
                                                                        )
                                                                    }
                                                                    className="text-thh-primary flex items-center gap-1 text-xs font-semibold hover:underline"
                                                                >
                                                                    <Edit2 className="h-3 w-3" />
                                                                    <span>
                                                                        Edit
                                                                    </span>
                                                                </button>
                                                                <span className="text-thh-border">
                                                                    |
                                                                </span>
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        handleToggleActive(
                                                                            p.id,
                                                                        )
                                                                    }
                                                                    className="text-thh-text-muted hover:text-thh-text text-xs font-semibold"
                                                                >
                                                                    {p.is_active
                                                                        ? 'Disable'
                                                                        : 'Enable'}
                                                                </button>
                                                            </div>
                                                            {p.helper_status ===
                                                                'pending' && (
                                                                <div className="mt-0.5 flex gap-1.5">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            handleHelperStatus(
                                                                                p.id,
                                                                                'approved',
                                                                            )
                                                                        }
                                                                        className="text-[11px] font-bold text-emerald-700"
                                                                    >
                                                                        Approve
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            handleHelperStatus(
                                                                                p.id,
                                                                                'rejected',
                                                                            )
                                                                        }
                                                                        className="text-[11px] font-bold text-rose-600"
                                                                    >
                                                                        Reject
                                                                    </button>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {/* Roles & Permissions Matrix View */}
                {activeView === 'matrix' && (
                    <div className="bg-thh-surface border-thh-border space-y-6 rounded-2xl border p-6 shadow-2xs">
                        <div className="border-thh-border border-b pb-4">
                            <h3 className="text-thh-text text-base font-bold">
                                Role Permission Matrix
                            </h3>
                            <p className="text-thh-text-muted mt-0.5 text-xs">
                                Granular security and workflow capability
                                boundaries across organizational roles.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead className="bg-thh-bg border-thh-border text-thh-text border-b text-[11px] font-bold tracking-wider uppercase">
                                    <tr>
                                        <th className="px-4 py-3">
                                            Permission Name
                                        </th>
                                        {rolesWithPermissions.map((r) => (
                                            <th
                                                key={r.id}
                                                className="px-4 py-3 text-center"
                                            >
                                                {r.name}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-thh-border divide-y">
                                    {allPermissions.map((perm) => (
                                        <tr
                                            key={perm.id}
                                            className="hover:bg-thh-bg/30"
                                        >
                                            <td className="text-thh-text px-4 py-2.5 font-mono font-medium">
                                                {perm.name}
                                            </td>
                                            {rolesWithPermissions.map((r) => {
                                                const has = r.permissions.some(
                                                    (p) => p.id === perm.id,
                                                );
                                                const locked =
                                                    r.name === 'super_admin';
                                                return (
                                                    <td
                                                        key={r.id}
                                                        className="px-4 py-2.5 text-center"
                                                    >
                                                        <button
                                                            type="button"
                                                            disabled={locked}
                                                            title={
                                                                locked
                                                                    ? 'Super admin cannot be changed'
                                                                    : has
                                                                      ? `Revoke ${perm.name}`
                                                                      : `Grant ${perm.name}`
                                                            }
                                                            onClick={() =>
                                                                router.post(
                                                                    '/admin/people/permissions',
                                                                    {
                                                                        role: r.name,
                                                                        permission:
                                                                            perm.name,
                                                                        granted:
                                                                            !has,
                                                                    },
                                                                    {
                                                                        preserveScroll: true,
                                                                        preserveState: true,
                                                                    },
                                                                )
                                                            }
                                                            className={`inline-flex h-6 w-6 items-center justify-center rounded-full transition-colors ${
                                                                locked
                                                                    ? 'cursor-not-allowed bg-emerald-500/15 text-emerald-600'
                                                                    : has
                                                                      ? 'bg-emerald-500/15 text-emerald-600 hover:bg-rose-500/15 hover:text-rose-600'
                                                                      : 'text-thh-border hover:bg-thh-bg hover:text-thh-text'
                                                            }`}
                                                        >
                                                            {has ? (
                                                                <Check className="h-3.5 w-3.5" />
                                                            ) : (
                                                                <span>—</span>
                                                            )}
                                                        </button>
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>

            {/* Add Team Member Modal */}
            {showAddModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleCreateMember}
                        className="bg-thh-surface border-thh-border w-full max-w-lg space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <h3 className="text-thh-text text-base font-bold">
                                Add Team Member
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowAddModal(false)}
                                className="text-thh-text-muted hover:text-thh-text p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        First name
                                    </label>
                                    <input
                                        type="text"
                                        value={addData.first_name}
                                        onChange={(e) =>
                                            setAddData(
                                                'first_name',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Last name
                                    </label>
                                    <input
                                        type="text"
                                        value={addData.last_name}
                                        onChange={(e) =>
                                            setAddData(
                                                'last_name',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Full Name
                                </label>
                                <input
                                    type="text"
                                    value={addData.name}
                                    onChange={(e) =>
                                        setAddData('name', e.target.value)
                                    }
                                    placeholder="e.g. Sureshbhai Gamit"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>

                            <div className="grid grid-cols-3 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Gender
                                    </label>
                                    <select
                                        value={addData.gender}
                                        onChange={(e) =>
                                            setAddData('gender', e.target.value)
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    >
                                        <option value="">—</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Birth date
                                    </label>
                                    <input
                                        type="date"
                                        value={addData.date_of_birth}
                                        onChange={(e) =>
                                            setAddData(
                                                'date_of_birth',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Blood group
                                    </label>
                                    <input
                                        type="text"
                                        value={addData.blood_group}
                                        onChange={(e) =>
                                            setAddData(
                                                'blood_group',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="B+"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        value={addData.email}
                                        onChange={(e) =>
                                            setAddData('email', e.target.value)
                                        }
                                        required
                                        placeholder="user@ggvt.org"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Phone
                                    </label>
                                    <input
                                        type="tel"
                                        value={addData.phone}
                                        onChange={(e) =>
                                            setAddData('phone', e.target.value)
                                        }
                                        required
                                        placeholder="9825000000"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Role
                                </label>
                                <select
                                    value={addData.role}
                                    onChange={(e) =>
                                        setAddData('role', e.target.value)
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-bold"
                                >
                                    <option value="staff">
                                        Field Staff (Tribal Coordinator)
                                    </option>
                                    <option value="admin">Administrator</option>
                                    <option value="mentor">
                                        Education/Career Mentor
                                    </option>
                                    <option value="partner">
                                        Partner Organization
                                    </option>
                                    <option value="volunteer">Volunteer</option>
                                </select>
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        District Scope
                                    </label>
                                    <select
                                        value={addData.district_id}
                                        onChange={(e) => {
                                            const dId = e.target.value;
                                            setAddData('district_id', dId);
                                            setAddData('taluka_id', '');
                                            setAddData('village_id', '');
                                        }}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    >
                                        <option value="">All Districts</option>
                                        {districts.map((d) => (
                                            <option key={d.id} value={d.id}>
                                                {d.name_en} ({d.name_gu})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Taluka Scope
                                    </label>
                                    <select
                                        value={addData.taluka_id}
                                        onChange={(e) => {
                                            const tId = e.target.value;
                                            setAddData('taluka_id', tId);
                                            setAddData('village_id', '');
                                        }}
                                        disabled={!addData.district_id}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 disabled:opacity-50"
                                    >
                                        <option value="">All Talukas</option>
                                        {(
                                            districts.find(
                                                (d) =>
                                                    String(d.id) ===
                                                    String(addData.district_id),
                                            )?.talukas || []
                                        ).map((t) => (
                                            <option key={t.id} value={t.id}>
                                                {t.name_en}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Village / City
                                    </label>
                                    <select
                                        value={addData.village_id}
                                        onChange={(e) =>
                                            setAddData(
                                                'village_id',
                                                e.target.value,
                                            )
                                        }
                                        disabled={!addData.taluka_id}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 disabled:opacity-50"
                                    >
                                        <option value="">All Villages</option>
                                        {(
                                            (
                                                districts.find(
                                                    (d) =>
                                                        String(d.id) ===
                                                        String(
                                                            addData.district_id,
                                                        ),
                                                )?.talukas || []
                                            ).find(
                                                (t) =>
                                                    String(t.id) ===
                                                    String(addData.taluka_id),
                                            )?.villages || []
                                        ).map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {v.name_en}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div className="border-thh-border flex justify-end gap-3 border-t pt-3">
                            <button
                                type="button"
                                onClick={() => setShowAddModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={addProcessing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                            >
                                {addProcessing
                                    ? 'Creating...'
                                    : 'Create Member'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Edit Team Member Modal */}
            {showEditModal && editingPerson && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleUpdateMember}
                        className="bg-thh-surface border-thh-border w-full max-w-lg space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <div>
                                <h3 className="text-thh-text text-base font-bold">
                                    Edit Member Profile & Scope
                                </h3>
                                <p className="text-thh-text-muted text-xs">
                                    {editingPerson.name} (
                                    {editingPerson.phone || editingPerson.email}
                                    )
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowEditModal(false)}
                                className="text-thh-text-muted hover:text-thh-text p-1"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        First name
                                    </label>
                                    <input
                                        type="text"
                                        value={editData.first_name}
                                        onChange={(e) =>
                                            setEditData(
                                                'first_name',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Last name
                                    </label>
                                    <input
                                        type="text"
                                        value={editData.last_name}
                                        onChange={(e) =>
                                            setEditData(
                                                'last_name',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Gender
                                    </label>
                                    <select
                                        value={editData.gender}
                                        onChange={(e) =>
                                            setEditData(
                                                'gender',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    >
                                        <option value="">—</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Birth date
                                    </label>
                                    <input
                                        type="date"
                                        value={editData.date_of_birth}
                                        onChange={(e) =>
                                            setEditData(
                                                'date_of_birth',
                                                e.target.value,
                                            )
                                        }
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Blood group
                                    </label>
                                    <input
                                        type="text"
                                        value={editData.blood_group}
                                        onChange={(e) =>
                                            setEditData(
                                                'blood_group',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="B+"
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Role Assignment
                                </label>
                                <select
                                    value={editData.role}
                                    onChange={(e) =>
                                        setEditData('role', e.target.value)
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-bold"
                                >
                                    <option value="staff">
                                        Field Staff (Tribal Coordinator)
                                    </option>
                                    <option value="admin">Administrator</option>
                                    <option value="mentor">
                                        Education/Career Mentor
                                    </option>
                                    <option value="partner">
                                        Partner Organization
                                    </option>
                                    <option value="volunteer">Volunteer</option>
                                    <option value="citizen">Citizen</option>
                                </select>
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        District
                                    </label>
                                    <select
                                        value={editData.district_id}
                                        onChange={(e) => {
                                            const dId = e.target.value;
                                            setEditData('district_id', dId);
                                            setEditData('taluka_id', '');
                                            setEditData('village_id', '');
                                        }}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                    >
                                        <option value="">
                                            All / Unrestricted
                                        </option>
                                        {districts.map((d) => (
                                            <option key={d.id} value={d.id}>
                                                {d.name_en} ({d.name_gu})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Taluka
                                    </label>
                                    <select
                                        value={editData.taluka_id}
                                        onChange={(e) => {
                                            const tId = e.target.value;
                                            setEditData('taluka_id', tId);
                                            setEditData('village_id', '');
                                        }}
                                        disabled={!editData.district_id}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 disabled:opacity-50"
                                    >
                                        <option value="">All Talukas</option>
                                        {(
                                            districts.find(
                                                (d) =>
                                                    String(d.id) ===
                                                    String(
                                                        editData.district_id,
                                                    ),
                                            )?.talukas || []
                                        ).map((t) => (
                                            <option key={t.id} value={t.id}>
                                                {t.name_en}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="text-thh-text mb-1 block font-bold">
                                        Village / City
                                    </label>
                                    <select
                                        value={editData.village_id}
                                        onChange={(e) =>
                                            setEditData(
                                                'village_id',
                                                e.target.value,
                                            )
                                        }
                                        disabled={!editData.taluka_id}
                                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 disabled:opacity-50"
                                    >
                                        <option value="">All Villages</option>
                                        {(
                                            (
                                                districts.find(
                                                    (d) =>
                                                        String(d.id) ===
                                                        String(
                                                            editData.district_id,
                                                        ),
                                                )?.talukas || []
                                            ).find(
                                                (t) =>
                                                    String(t.id) ===
                                                    String(editData.taluka_id),
                                            )?.villages || []
                                        ).map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {v.name_en}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div className="border-thh-border flex justify-end gap-3 border-t pt-3">
                            <button
                                type="button"
                                onClick={() => setShowEditModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={editProcessing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                            >
                                {editProcessing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
